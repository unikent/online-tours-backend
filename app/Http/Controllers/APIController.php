<?php namespace App\Http\Controllers;


use App\Models\Zone;
use App\Models\Leaf;
use App\Models\Tour;
use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use DateTimeZone;

class APIController extends Controller {

	/**
     * Create a new controller instance.
     */
    public function __construct(){
    }

    public function respond($payload,$code = 200,$headers =array()){
        $expiry = Carbon::now();
        $expiry->addMinutes(5);
        $headers = array_merge($headers,[
            'Expires'=>$expiry->toRfc2822String(),
            'Cache-Control'=>'public. max-age=300, must-revalidate',
            'Pragma'      => 'public'
        ]);
        return response()->json($payload,$code,$headers);
    }

	/**
	 * Index - index of zones in app.
	 * Zone includes name, id and list of avaiable tours
	 *
	 */
	public function index($connection)
	{
		$zones = Zone::orderBy('sequence', 'asc')->with('tours')->get()->toArray();

		$payload = [];
		foreach($zones as $zone){
			 $payload[] = ['id'=> $zone['leaf_id'], 'name'=>$zone['name'],'slug'=>$zone['slug'], 'tours' => $zone['tours']];
		}
		return $this->respond($payload);
	}

	/**
	 * Zone - get a zone by id
	 */
	public function zone($connection, $id)
	{
		$root = Zone::where('leaf_id','=',$id)->firstOrFail();

		if($root){
			$pois = $root->leaf->descendantsAndSelf()->with("location")->get()->sortBy('name')->toHierarchy()->first()->toArray();
			return $this->respond( ["pois" => [$pois] ] );
		}else{
			return $this->respond(["success" => false, "message" => "No zone with id ".$id], 404);
		}
		
	}

	/**
	 * Zone - get a tour
	 */
	public function tour($connection, $id){
		$tour = Tour::where('id','=', $id)->firstOrFail();
		$tour->items = $tour->getSortedItems();
		if($tour){
			return $this->respond($tour);
		}else{
			return $this->respond(["success" => false, "message" => "Tour not found."], 404);
		}
	}

	/**
	 * Zone - get all tours in zone
	 */
	public function tour_content($connection, $zone_id)
	{
		$tours = Tour::with('contents')->where('leaf_id','=', $zone_id)->get();
		$root = Leaf::with('contents')->where('id','=',$zone_id)->first();

		if(!$tours->isEmpty()){
			return $this->respond(['root'=>$root,'tours'=>$tours]);
		}else{
			return $this->respond(["success" => false, "message" => "Tours not found."], 404);
		}
	}

	/**
	 * Get content for a POI
	 */
	public function poi($connection, $id_or_slug)
	{
		$poi = Leaf::with('contents')->with('location');
		$poi = is_numeric($id_or_slug) ? $poi->find($id_or_slug) : $poi->where('slug', $id_or_slug)->first();
		if($poi){
			return $this->respond($poi);
		}else{
			return $this->respond(["success" => false, "message" => "Content not found"], 404);
		}	
	}


	/**
	 * Page - get a page
	 */
	public function page($connection, $id_or_slug){
		$page = Page::with('contents');
		$page = is_numeric($id_or_slug) ? $page->find($id_or_slug) : $page->where('slug', $id_or_slug)->first();
		if(empty($page)){
			throw new ModelNotFoundException;
		}
		return $this->respond($page);
	}
}