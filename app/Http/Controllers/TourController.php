<?php namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Leaf;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Requests\TourPersistRequest;
use Illuminate\Support\Facades\Input;

class TourController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return redirect('/');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($leaf_id)
    {
        $leaf = Leaf::find($leaf_id);

        $tour = new Tour();
        $tour->leaf_id = $leaf->id;
        return $this->layout('pages.tour.create', [
            'tour' => $tour,
            'zone_id' => $leaf_id
        ]);
    }

    /**
     * Store the new tour.
     *
     * @return Response
     */
     public function store(TourPersistRequest $request, $zone_id)
     {

        $tour = new Tour();

        $tour->fill(Input::all());
        $tour->items = $tour->getSortedItems();
        $tour->save();

        return Redirect::action('TourController@edit', [$zone_id , $tour->id]);
     }

    /**
     * Show the edit tour form.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($zone_id, $id)
    {
        $tour = Tour::find($id);
        return $this->layout('pages.tour.edit', [  'zone_id' => $zone_id, 'tour' => $tour ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TourPersistRequest $request
     * @param $zone_id
     * @param  int $id
     * @return Response
     */
    public function update(TourPersistRequest $request, $zone_id, $id)
    {
        /** @var  $tour  Tour */
        $tour = Tour::find($id);

        $tour->fill(Input::all());

        $tour->items = $tour->getSortedItems();

        $tour->save();

        return Redirect::action('TourController@edit', [$zone_id , $tour->id]); 

    }

    public function destroy($zone_id, $id){
        $tour = Tour::findOrFail($id);
        if($tour->delete()){
            Session::flash('alert', ['type'=>'success','message'=>'The Tour was deleted.']);

            if(RequestFacade::ajax()){
                return response()->json([ 'redirect_to' => action('ZoneController@index') ], 200);
            } else {
                return Redirect::action('ZoneController@index');
            }
        } else {
            Session::flash('alert', ['type'=>'danger','message'=>'The Tour could not be deleted.']);

            if(RequestFacade::ajax()){
                return response()->json([], 400);
            } else {
                return Redirect::action('TourController@edit', array('id' => Input::get('id')));
            }
        }
    }

}
