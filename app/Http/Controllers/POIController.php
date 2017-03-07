<?php namespace App\Http\Controllers;


use App\Models\Leaf;
use App\Models\Location;
use App\Http\Requests\POIPersistRequest;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

use \Exception;


/**
 * POI - Point Of Interest controller
 * Manages creation & managment of POI (A combination of Leaf, Node and Content)
 *
 */
class POIController extends Controller {

	/**
	 * Create a new controller instance.
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Index
	 * Display tree page of all POI's. Accepts an optional ID for highlighting / expanding a given leaf.
	 *
	 * @param $id id to highlight
	 */
	public function index($id = null)
	{
		return $this->layout('pages.poi.index', array('id' => $id));
	}

	/**
	 * Create
	 * Show page to create a new POI (Node/Leaf)
	 * 
	 * @param int $parent_id - ID of parent node. Null = root node
	 */
	public function create($parent_id = null){
        $leaf = new Leaf();
        $leaf->location()->associate(new Location());

        if(!is_null($parent_id)){
        	$parent = Leaf::findOrFail($parent_id);
        	$locations = Location::getNotInTree($parent->getRoot()->id);

			return $this->layout('pages.poi.create', [ 
				'parent' => isset($parent) ? $parent : null, 
				'leaf' => $leaf, 
				'locations' => $locations 
			]);
        } else {
        	$locations = Location::all();
			return $this->layout('pages.poi.create', [ 'leaf' => $leaf, 'locations' => $locations, 'parent' => null ]);
        }

	}

	/**
	 * Store
	 */
	public function store(POIPersistRequest $request){

		$location = Location::findOrFail(Input::get('location_id'));

		// Create leaf and attach location
		$leaf = new Leaf();
		$leaf->location()->associate($location);

		$leaf->name = Input::get('name');

        // Set a slug
		$slug = strtolower(str_random(6));
        $validator = Validator::make([ 'slug' => $slug ], [ 'unique:leaf' ]);
 
 		// Check if the slug is unique... keep trying if not.
        $unique = $validator->passes();
        while(!$unique){
            $slug = strtolower(str_random(6));
            $validator = Validator::make([ 'slug' => $slug ], [ 'unique:leaf' ]);
            $unique = $validator->passes();
        }

        $leaf->slug = $slug;
		$leaf->save();

		// put it in the tree (if not a top level node)
		$parent_id = Input::get('parent_id');
		if($parent_id){
			$parent = Leaf::findOrFail($parent_id);
			$leaf->makeChildOf($parent);
		}

		// send to edit page
		return Redirect::action('POIController@edit', $leaf->id);
	}


	/**
	 * Update
	 * Update a POI
	 */
	public function update(POIPersistRequest $request, $id = null){

		// Grab the leaf we want to update.
		$leaf = Leaf::findOrFail($id);

		$success = true;

		// Update location data
		if(Input::has('location_id') && Input::get('location_id')!== $leaf->location_id ){
			// Change location
			$l = Location::findOrFail(Input::get('location_id'));
			$leaf->location()->associate($l);
		}

		if(Input::has('name')){
			$leaf->name = Input::get('name');
		}
		$success = $success && $leaf->save();

		if(!is_null(Input::get('parent_id'))){ // '' is a legit value
			$parent_id = Input::get('parent_id');
			if(!empty($parent_id)){
				$parent = Leaf::findOrFail($parent_id);
				$success =  $success && ($leaf->makeChildOf($parent) ? true : false);
			} else{
				$success = $leaf->makeRoot() ? true : false;
			}
		}

		if(Request::ajax()){
			return response()->json([ 'success' => $success, 'leaf' => $leaf ]);
		} else {
			Session::flash('alert',['type'=>'success','message'=>'POI Updated']);
			return Redirect::action('POIController@edit', $leaf->id);
		}
	}

	/**
	 * Edit - Show page to edit an existing POI (Node/Leaf)
	 * 
	 * @param int $id - ID of leaf
	 */
	public function edit($id){
        $leaf = Leaf::where('id', $id)->with('location')->firstOrFail();
		$locations = Location::getNotInTree($leaf->getRoot()->id)->all();
		array_unshift($locations,$leaf->location);

		return $this->layout('pages.poi.edit', array('leaf' => $leaf,'locations'=>$locations));
	}


	/**
	 * Delete 
	 */
	public function destroy($id){
		Leaf::findOrFail($id)->delete();
		Session::flash('alert', [ 'type' => 'success', 'message' => 'The POI was deleted.' ]);

		if(Request::ajax()){
			return response()->json([ 'redirect_to' => action('POIController@index') ], 200);
		} else {
			return Redirect::action('POIController@index');
		}
	}

}
