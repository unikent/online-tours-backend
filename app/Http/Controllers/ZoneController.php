<?php 
namespace App\Http\Controllers;

use App\Models\Zone;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request as Req;
use Illuminate\Support\Facades\Response;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Illuminate\Support\Facades\Artisan;

class ZoneController extends Controller {

	public function __construct(){
		$this->middleware('auth');
	}

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $zones = Zone::orderBy('name', 'asc')->with('tours')->get();
        return $this->layout('pages.home', array('zones' => $zones));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $zone = new Zone();
        return $this->layout('pages.zone.edit', array('zone' => $zone));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'leaf_id'=>'required|integer|unique:zone'
        ],[
            'leaf_id.unique'=>'This POI is already a Zone, please choose another.'
        ]);


        $zone = new Zone();
        $zone->name = Input::get('name');
        $zone->leaf_id = Input::get('leaf_id');

        if($zone->save()) {
            if($request->ajax() || $request->format()==='json'){
                return response()->json($zone);
            }else {
                Session::flash('alert', ['type' => 'success', 'message' => 'Zone Created']);
                return Redirect::action('ZoneController@index');
            }
        }else{
            return $this->error('Unable to create zone.',500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  mixed  $slug_or_id
     * @return Response
     */
    public function show($slug_or_id)
    {
        return Redirect::action('ZoneController@edit',[$slug_or_id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  mixed  $slug_or_id
     * @return Response
     */
    public function edit($slug_or_id)
    {

        $zone = Zone::fetchOrFail($slug_or_id);

        return $this->layout('pages.zone.edit', array('zone'=>$zone));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  mixed  $slug_or_id
     * @return Response
     */
    public function update(Request $request, $slug_or_id)
    {

        try {
            $zone = Zone::fetchOrFail($slug_or_id);
        } catch (ModelNotFoundException $x){
            return $this->error('This zone does not exist!',404);
        }

        $this->validate($request, [
            'name' => 'required|string|max:255'
        ]);

        $zone->name = Input::get('name');

        if($zone->save()) {
            if($request->ajax() || $request->format()==='json'){
                return response()->json(['success'=>true,'zone'=>$zone]);
            }else {
                Session::flash('alert', ['type' => 'success', 'message' => 'Zone Updated']);
                return Redirect::action('ZoneController@index');
            }
        }else{
            return $this->error('Unable to update zone.',500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  mixed  $slug_or_id
     * @return Response
     */
    public function destroy($slug_or_id)
    {
        try {
            $zone = Zone::fetchOrFail($slug_or_id);
        } catch (ModelNotFoundException $x){
            return $this->error('This zone does not exist!',404);
        }

        if($zone->delete()) {

            Session::flash('alert', ['type' => 'success', 'message' => 'Zone Deleted']);
            if(Req::ajax() || Req::format()==='json'){
                return response()->json([ 'redirect_to' => action('ZoneController@index') ], 200);
            }else{
                return Redirect::action('ZoneController@index');
            }
        }else{
            return $this->error('Unable to delete zone.',500);
        }
    }

	public function orderTours($zone_id){
		$zone = Zone::findOrFail($zone_id);

		DB::beginTransaction();
		try {
			if(Input::has('featured')){
				$s = 1;
				foreach(Input::get('featured') as $id){
					$tour = $zone->tours()->find($id);
					if($tour){
						$tour->featured = 1;
						$tour->sequence = $s;
						$tour->save();
						$s++;
					}
				}
			}

			if(Input::has('standard')){
				$s = 1;
				foreach(Input::get('standard') as $id){
					$tour = $zone->tours()->find($id);
					if($tour){
						$tour->featured = 0;
						$tour->sequence = $s;
						$tour->save();
						$s++;
					}
				}
			}

		    DB::commit();
			if(Req::ajax() || (Req::format() == 'json')) {
				return response()->json([], 200);
			} else {
				return Redirect::action('ZoneController@index');
			}

		} catch(Exception $e) {
		    DB::rollback();
		    return $this->error('An error occurred.', 500);
		}
	}

    public function syncLive(){

        $success = Artisan::call('db:publish');
        if($success!==0){
            Session::flash('alert', ['type' => 'danger', 'message' => 'Database sync failed!']);
        }else{
            Session::flash('alert', ['type' => 'success', 'message' => 'All changes have been published to live.']);
        }

        return Redirect::action('ZoneController@index');
    }

}