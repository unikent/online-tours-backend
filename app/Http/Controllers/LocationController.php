<?php namespace App\Http\Controllers;


use App\Models\Location;
use App\Http\Requests\LocationPersistRequest;

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
class LocationController extends Controller {

	/**
	 * Create a new controller instance.
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Index
	 * @param $id id to highlight
	 */
	public function index(){
		$locations = Location::local()->orderBy('name')->get();
		return $this->layout('pages.location.index', array('locations' => $locations));
	}


	/**
	 * Create
	 * @param int $parent_id - ID of parent node. Null = root node
	 */
	public function create(){
		$location = new Location;
		return $this->layout('pages.location.create', array('location' => $location));
	}


	/**
	 * Store
	 */
	public function store(LocationPersistRequest $request){
		$location = Location::create(Input::all());
		Session::flash('alert', [ 'type' => 'success', 'message' => 'Location saved.' ]);
		return Redirect::action('LocationController@edit', $location->id);
	}	


	/**
	 * Edit
	 */
	public function edit($id){
        $location = Location::local()->where('id', $id)->firstOrFail();
		return $this->layout('pages.location.edit', array('location' => $location));
	}


	/**
	 * Update
	 */
	public function update(LocationPersistRequest $request, $id){
        $location = Location::local()->where('id', $id)->firstOrFail();
        $location->fill(Input::all());
        $location->save();

		Session::flash('alert', [ 'type' => 'success', 'message' => 'Location updated.' ]);
		return Redirect::action('LocationController@edit', $location->id);
	}

	/**
	 * Destroy
	 */
	public function destroy($id){
		$location = Location::local()->where('id', $id)->firstOrFail();
		$leaves = $location->leaves()->count();
		if($leaves > 0){
			Session::flash('alert', [ 'type' => 'danger', 'message' => 'Location cannot be deleted as still in use.' ]);
			return Redirect::action('LocationController@index');
		}else{
			$location->delete();
			Session::flash('alert', [ 'type' => 'success', 'message' => 'Location deleted.' ]);
			return Redirect::action('LocationController@index');
		}

	}

}
