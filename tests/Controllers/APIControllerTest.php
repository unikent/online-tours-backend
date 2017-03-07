<?php 

use Illuminate\Foundation\Testing\DatabaseTransactions;

class APIControllerTest extends TestCase {

	use DatabaseTransactions;

	public function setUp(){
		parent::setUp();
        DB::setDefaultConnection('test_live');
	}

	/**
	 * @test
	 */
	public function index_WhenNoZones_ReturnEmptyJson()
	{
		$response = $this->jsonAction('GET', 'APIController@index', [ 'connection' => 'live' ]);
		$this->assertEquals(200, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);

		$this->assertEmpty($data);
	}

	/**
	 * @test
	 */
	public function index_ReturnAllZones()
	{
		factory('App\Models\Zone')->create();
		factory('App\Models\Zone')->create();
		factory('App\Models\Zone')->create();
		factory('App\Models\Zone')->create();

		$response = $this->jsonAction('GET', 'APIController@index', [ 'connection' => 'live' ]);
		$this->assertEquals(200, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);

		$this->assertNotEmpty($data);
		$this->assertTrue(count($data) == 4);
	}

	/**
	 * @test
	 */
	public function index_ReturnZonesWithTours()
	{
		$leaf = factory('App\Models\Leaf')->create();
		$zone = factory('App\Models\Zone')->create(['leaf_id' => $leaf->id]);
		factory('App\Models\Tour')->create(['leaf_id' => $zone->leaf_id]);
		factory('App\Models\Tour')->create(['leaf_id' => $zone->leaf_id]);
		factory('App\Models\Tour')->create(['leaf_id' => $zone->leaf_id]);
		factory('App\Models\Tour')->create(['leaf_id' => $zone->leaf_id]);
		factory('App\Models\Tour')->create(['leaf_id' => $zone->leaf_id]);

		$response = $this->jsonAction('GET', 'APIController@index', [ 'connection' => 'live' ]);
		$this->assertEquals(200, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);

		$this->assertNotEmpty($data);
		$this->assertTrue(count($data[0]['tours']) == 5);
	}

	/**
	 * @test
	 */
	public function zone_WhenInvalidIDGiven_Return404WithFalseSuccess()
	{
		$response = $this->jsonAction('GET', 'APIController@zone', [ 'connection' => 'live', 'id' => 999 ]);
		$this->assertEquals(404, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);

		$this->assertFalse($data['success']);
	}

	/**
	 * @test
	 */
	public function zone_ReturnCorrectZoneLeafWithDescendants()
	{
		$leaf = factory('App\Models\Leaf')->create(['name'=>'A']);
		$leaf2 = factory('App\Models\Leaf')->create(['name'=>'B']); $leaf2->makeChildOf($leaf);
		$leaf3 = factory('App\Models\Leaf')->create(['name'=>'C']); $leaf3->makeChildOf($leaf);
		$leaf4 = factory('App\Models\Leaf')->create(['name'=>'D']); $leaf4->makeChildOf($leaf2);
		$zone = factory('App\Models\Zone')->create(['leaf_id' => $leaf->id]);

		$response = $this->jsonAction('GET', 'APIController@zone', [ 'connection' => 'live', 'id' => $zone->leaf_id]);
		$this->assertEquals(200, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);

		$this->assertEquals($data['pois'][0]['id'], $leaf->id);
		$this->assertEquals($data['pois'][0]['children'][1]['id'], $leaf3->id);
		$this->assertEquals($data['pois'][0]['children'][0]['children'][0]['id'], $leaf4->id);
	}

	/**
	 * @test
	 */
	public function tour_WhenInvalidIDGiven_Return404WithFalseSuccess()
	{
		$response = $this->jsonAction('GET', 'APIController@tour', [ 'connection' => 'live', 'id' => 999 ]);
		$this->assertEquals(404, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);

		$this->assertFalse($data['success']);
	}

	/**
	 * @test
	 */
	public function tour_ReturnCorrectTour()
	{
		$tour = factory('App\Models\Tour')->create();
		$response = $this->jsonAction('GET', 'APIController@tour', [ 'connection' => 'live', 'id' => $tour->id ]);
		$this->assertEquals(200, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);

		$this->assertEquals($data['id'], $tour->id);
	}

	/**
	 * @test
	 */
	public function tour_content_WhenInvalidIDGiven_Return404WithFalseSuccess()
	{
		$response = $this->jsonAction('GET', 'APIController@tour_content', [ 'connection' => 'live', 'id' => 999]);
		$this->assertEquals(404, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);

		$this->assertFalse($data['success']);
	}

	/**
	 * @test
	 */
	public function tour_content_ReturnCorrectToursAndZoneContents()
	{
		$leaf = factory('App\Models\Leaf')->create();
		$zone = factory('App\Models\Zone')->create(['leaf_id' => $leaf->id]);
		$tour1 = factory('App\Models\Tour')->create(['leaf_id' => $zone->leaf_id]);
		$tour2 = factory('App\Models\Tour')->create(['leaf_id' => $zone->leaf_id]);
		$tour3 = factory('App\Models\Tour')->create(['leaf_id' => $zone->leaf_id]);
		$tour4 = factory('App\Models\Tour')->create(['leaf_id' => $zone->leaf_id]);
		$tour5 = factory('App\Models\Tour')->create(['leaf_id' => $zone->leaf_id]);

		$response = $this->jsonAction('GET', 'APIController@tour_content', [ 'connection' => 'live', 'id' => $zone->leaf_id ]);
		$this->assertEquals(200, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);

		$this->assertEquals($data['root']['id'], $zone->leaf_id);
		$this->assertEquals($data['tours'][0]['id'], $tour1->id);
		$this->assertEquals($data['tours'][3]['id'], $tour4->id);
	}

	/**
	 * @test
	 */
	public function poi_WhenInvalidIDGiven_Return404WithFalseSuccess()
	{
		$response = $this->jsonAction('GET', 'APIController@poi', [ 'connection' => 'live', 'id' => 999 ]);
		$this->assertEquals(404, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);

		$this->assertFalse($data['success']);
	}

	/**
	 * @test
	 */
	public function poi_WhenGivenID_ReturnCorrectPOI()
	{
		$location = factory('App\Models\Location')->create();
		$leaf = factory('App\Models\Leaf')->create(['location_id' => $location->id]);
		$content = factory('App\Models\Content\Text')->create();
		$leaf->contents()->attach($content);

		$response = $this->jsonAction('GET', 'APIController@poi', [ 'connection' => 'live', 'id' => $leaf->id ]);
		$this->assertEquals(200, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);
		
		$this->assertEquals($data['id'], $leaf->id);
		$this->assertEquals($data['contents'][0]['id'], $content->id);
		$this->assertEquals($data['location']['name'], $location->name);
	}

	/**
	 * @test
	 */
	public function poi_WhenGivenSlug_ReturnCorrectPOI()
	{
		$location = factory('App\Models\Location')->create();
		$leaf = factory('App\Models\Leaf')->create(['location_id' => $location->id]);
		$content = factory('App\Models\Content\Text')->create();
		$leaf->contents()->attach($content);

		$response = $this->jsonAction('GET', 'APIController@poi', [ 'connection' => 'live', 'id' => $leaf->slug ]);
		$this->assertEquals(200, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);
		
		$this->assertEquals($data['id'], $leaf->id);
		$this->assertEquals($data['slug'], $leaf->slug);
		$this->assertEquals($data['contents'][0]['id'], $content->id);
		$this->assertEquals($data['location']['name'], $location->name);
	}

	/**
	 * @test
	 */
	public function page_WhenInvalidIDGiven_Return404WithFalseSuccess()
	{
		$response = $this->jsonAction('GET', 'APIController@page', [ 'connection' => 'live', 'id_or_slug' => 999 ]);
		$this->assertEquals(404, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);

		$this->assertEquals($data['message'], 'Not Found');
	}

	/**
	 * @test
	 */
	public function page_WhenGivenID_ReturnCorrectPage()
	{
		$page = factory('App\Models\Page')->create();

		$content = factory('App\Models\Content\Text')->create();
		$page->contents()->attach($content);

		$response = $this->jsonAction('GET', 'APIController@page', [ 'connection' => 'live', 'id_or_slug' => $page->id ]);
		$this->assertEquals(200, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);

		$data = json_decode($data, true);
		
		$this->assertEquals($data['id'], $page->id);
		$this->assertEquals($data['contents'][0]['id'], $content->id);
	}

	/**
	 * @test
	 */
	public function page_WhenGivenSlug_ReturnCorrectPage()
	{
		$page = factory('App\Models\Page')->create();
		$content = factory('App\Models\Content\Text')->create();
		$page->contents()->attach($content);

		$response = $this->jsonAction('GET', 'APIController@page', [ 'connection' => 'live', 'id_or_slug' => $page->slug ]);
		$this->assertEquals(200, $response->getStatusCode());

		$data = $response->getContent();
		$this->assertJson($data);
		
		$data = json_decode($data, true);
		
		$this->assertEquals($data['id'], $page->id);
		$this->assertEquals($data['slug'], $page->slug);
		$this->assertEquals($data['contents'][0]['id'], $content->id);
	}
}