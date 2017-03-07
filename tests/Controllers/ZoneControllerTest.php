<?php

use App\Models\Tour;
use App\Models\Zone;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;

class ZoneControllerTest extends TestCase {

    use DatabaseTransactions;

    public function setUp(){
        parent::setUp();

        Session::start(); // We need to start a session in order to use csrf_token()
        $this->setAdminSession();
    }

    /**
     * @test
     */
    public function index_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $this->htmlAction('GET', 'ZoneController@index');
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function index_Returns200(){
       factory('App\Models\Zone')->create();

        $response = $this->htmlAction('GET', 'ZoneController@index');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function index_WhenAuthorised_SetsZonesVariable(){
        factory('App\Models\Zone', 2)->create();

        $response = $this->htmlAction('GET', 'ZoneController@index');
        $data = $response->original->getData();

        $this->assertCount(2, $data['zones']);
    }

    /**
     * @test
     */
    public function edit_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $zone = factory('App\Models\Zone')->create();
        $this->htmlAction('GET', 'ZoneController@edit', [$zone->leaf_id]);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function edit_WhenAuthorised_Returns200(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);

        $response = $this->htmlAction('GET','ZoneController@edit', [$zone->leaf_id]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function edit_WhenAuthorised_SetsZoneVariable(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);

        $response = $this->htmlAction('GET', 'ZoneController@edit', [$zone->leaf_id]);

        $data = $response->original->getData();
        $this->assertEquals($zone->leaf_id, $data['zone']->leaf_id);
    }

    /**
     * @test
     */
    public function update_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);

        $response = $this->htmlAction('PATCH', 'ZoneController@update', [$zone->leaf_id]);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_405WhenUpdateWithNoId(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);

        $response = $this->call('PATCH', url('zone'),['_token'=>csrf_token()]);
        $this->assertEquals(405, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_doesNotChangeSlug(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);

        $response = $this->htmlAction('PATCH', 'ZoneController@update', [$zone->leaf_id], ['name'=>'foobar']);
        $after = Zone::find($zone->leaf_id);
        $this->assertEquals($zone->slug,$after->slug);
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_doesNotChangeLeafID(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);

        $response = $this->htmlAction('PATCH', 'ZoneController@update', [$zone->leaf_id], ['name'=>'MY_LEAF_ID_SHOULD_NOT_CHANGE','leaf_id'=>666]);
        $after = Zone::where('name','=','MY_LEAF_ID_SHOULD_NOT_CHANGE')->first();
        $this->assertEquals($zone->leaf_id,$after->leaf_id);
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_savesAndRedirectsToIndexWithAlert(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);

        $response = $this->htmlAction('PATCH', 'ZoneController@update', [$zone->leaf_id], ['name'=>'foobar']);

        /** @var $after Zone */
        $after = Zone::find($zone->leaf_id);
        $this->assertEquals('foobar',$after->name);

        $this->assertRedirectedToAction('ZoneController@index');
        $this->assertSessionHas('alert');
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_withAJAX_savesAndReturnsJsonMessage(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);

        $response = $this->ajaxAction('PATCH', 'ZoneController@update', [$zone->leaf_id], ['name'=>'foobar']);

        /** @var $after Zone */
        $after = Zone::find($zone->leaf_id);

        $content = $response->getContent();
        $this->assertJson($content);
        $data = json_decode($content, TRUE);

        $this->assertArrayHasKey('zone', $data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertEquals($zone->leaf_id,$data['zone']['leaf_id']);
        $this->assertEquals('foobar',$after->name);

    }

    /**
     * @test
     */
    public function create_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $this->htmlAction('GET', 'ZoneController@create');
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function create_WhenAuthorised_Returns200(){
        $response = $this->htmlAction('GET','ZoneController@create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function create_WhenAuthorised_SetsZoneVariable(){
        $response = $this->htmlAction('GET', 'ZoneController@create');
        $this->assertViewHas('zone');
    }

    /**
     * @test
     */
    public function store_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);

        $this->htmlAction('POST', 'ZoneController@store',[], [ 'name'=>'foobar', 'leaf_id'=>$leaf->id ]);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function store_WhenAuthorised_savesAndRedirectsToIndexWithAlert(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);

        $response = $this->htmlAction('POST', 'ZoneController@store', [], [ 'name'=>'foobar','leaf_id'=>$leaf->id ]);

        $zone = Zone::all()->last();
        $this->assertEquals('foobar', $zone->name);

        $this->assertRedirectedToAction('ZoneController@index');
        $this->assertSessionHas('alert');
    }

    /**
     * @test
     */
    public function store_WhenAuthorised_setsSlug(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $response = $this->htmlAction('POST', 'ZoneController@store', [], [ 'name'=>'FoobAr', 'leaf_id'=>$leaf->id ]);

        $zone = Zone::all()->last();
        $this->assertEquals('foobar', $zone->slug);
    }

    /**
     * @test
     */
    public function store_requiresValidName(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $response = $this->htmlAction('POST', 'ZoneController@store', [], ['leaf_id'=>$leaf->id],[],[],['HTTP_REFERER'=>action('ZoneController@create')]);

        $this->assertRedirectedToAction('ZoneController@create');
        $this->assertSessionHasErrors(['name']);
    }

    /**
     * @test
     */
    public function store_requiresLeafID(){
        $response = $this->htmlAction('POST', 'ZoneController@store', [], ['name'=>'foobar'],[],[],['HTTP_REFERER'=>action('ZoneController@create')]);

        $this->assertRedirectedToAction('ZoneController@create');
        $this->assertSessionHasErrors(['leaf_id']);
    }

    /**
     * @test
     */
    public function store_requiresValidLeafID(){
        $response = $this->htmlAction('POST', 'ZoneController@store', [], ['leaf_id'=>'arg','name'=>'foobar'],[],[],['HTTP_REFERER'=>action('ZoneController@create')]);

        $this->assertRedirectedToAction('ZoneController@create');
        $this->assertSessionHasErrors(['leaf_id']);
    }

    /**
     * @test
     */
    public function store_requiresUniqueLeafID(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);

        $response = $this->htmlAction('POST', 'ZoneController@store', [], ['leaf_id'=>$leaf->id,'name'=>'foobar'],[],[],['HTTP_REFERER'=>action('ZoneController@create')]);

        $this->assertRedirectedToAction('ZoneController@create');
        $this->assertSessionHasErrors(['leaf_id']);
    }

    /**
     * @test
     */
    public function destroy_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);

        $this->htmlAction('DELETE', 'ZoneController@destroy', $zone->leaf_id);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function destroy_WhenNotFound_Returns404(){
        $response = $this->htmlAction('DELETE', 'ZoneController@destroy', 123);
        $this->assertEquals(404, $response->getStatusCode());
    }


    /**
     * @test
     */
    public function destroy_WhenAuthorised_deletesAndRedirectsToIndexWithAlert(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);
        $response = $this->htmlAction('DELETE', 'ZoneController@destroy', $zone->leaf_id);

        $this->assertNull(Zone::find($zone->leaf_id));
        $this->assertSessionHas('alert');
        $this->assertRedirectedToAction('ZoneController@index');
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorised_withAJAX_deletesAndReturnsJsonPacket(){
        $location = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create(['location_id'=>$location->id]);
        $zone = factory('App\Models\Zone')->create(['leaf_id'=>$leaf->id]);
        $response = $this->ajaxAction('DELETE', 'ZoneController@destroy', $zone->leaf_id);

        $this->assertNull(Zone::find($zone->leaf_id));

        $content = $response->getContent();
        $this->assertJson($content);
        $data = json_decode($content, TRUE);

        $this->assertArrayHasKey('redirect_to', $data);
        $this->assertEquals(action('ZoneController@index'),$data['redirect_to']);
    }
    
    /**
     * @test
     */ 
    public function orderTours_RequiresAuthentication(){
        $this->setUnauthenticatedSession();

        $zone = factory('App\Models\Zone')->create();
        $tour = factory('App\Models\Tour')->create([ 'leaf_id' => $zone->leaf_id ]);

        Session::start();
        $response = $this->htmlAction('PATCH', 'ZoneController@orderTours', [ $zone->leaf_id ]);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */ 
    public function orderTours_WhenAuthenticatedZoneNotFound_Returns404(){
        $response = $this->htmlAction('PATCH', 'ZoneController@orderTours', [ 123 ]);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */ 
    public function orderTours_WhenAuthenticatedAndFound_UpdatesFeaturedState(){
        $zone = factory('App\Models\Zone')->create();

        $t1 = factory('App\Models\Tour')->create([ 'leaf_id' => $zone->leaf_id ]);
        $t2 = factory('App\Models\Tour')->create([ 'leaf_id' => $zone->leaf_id ]);
        $t3 = factory('App\Models\Tour')->create([ 'leaf_id' => $zone->leaf_id ]);

        $params = [
            'featured' => [ $t3->id ],
            'standard' => [ $t1->id, $t2->id ],
        ];

        $response = $this->htmlAction('PATCH', 'ZoneController@orderTours', [ $zone->leaf_id ], $params);

        $t3 = Tour::find($t3->id);
        $this->assertEquals(1, $t3->featured);

        $t1 = Tour::find($t1->id);
        $this->assertEquals(0, $t1->featured);

        $t2 = Tour::find($t2->id);
        $this->assertEquals(0, $t2->featured);
    }

    /**
     * @test
     */ 
    public function orderTours_WhenAuthenticatedAndFound_UpdatesSequence(){
        $zone = factory('App\Models\Zone')->create();

        $t1 = factory('App\Models\Tour')->create([ 'leaf_id' => $zone->leaf_id ]);
        $t2 = factory('App\Models\Tour')->create([ 'leaf_id' => $zone->leaf_id ]);
        $t3 = factory('App\Models\Tour')->create([ 'leaf_id' => $zone->leaf_id ]);
        $t4 = factory('App\Models\Tour')->create([ 'leaf_id' => $zone->leaf_id ]);
        $t5 = factory('App\Models\Tour')->create([ 'leaf_id' => $zone->leaf_id ]);

        $params = [
            'featured' => [ $t3->id, $t1->id ],
            'standard' => [ $t2->id, $t5->id, $t4->id ],
        ];

        $response = $this->htmlAction('PATCH', 'ZoneController@orderTours', [ $zone->leaf_id ], $params);

        $t3 = Tour::find($t3->id);
        $this->assertEquals(1, $t3->sequence);

        $t1 = Tour::find($t1->id);
        $this->assertEquals(2, $t1->sequence);

        $t2 = Tour::find($t2->id);
        $this->assertEquals(1, $t2->sequence);

        $t5 = Tour::find($t5->id);
        $this->assertEquals(2, $t5->sequence);

        $t4 = Tour::find($t4->id);
        $this->assertEquals(3, $t4->sequence);
    }

    /**
     * @test
     */ 
    public function orderTours_WhenAuthenticatedAndFoundAndSuccessful_RedirectsToIndex(){
        $zone = factory('App\Models\Zone')->create();
        $tour = factory('App\Models\Tour')->create([ 'leaf_id' => $zone->leaf_id ]);

        $response = $this->htmlAction('PATCH', 'ZoneController@orderTours', [ $zone->leaf_id ], []);
        $this->assertRedirectedToAction('ZoneController@index');
    }

    /**
     * @test
     */ 
    public function orderTours_WhenAjaxAndAuthenticatedAndFoundAndSuccessful_Returns201(){
        $zone = factory('App\Models\Zone')->create();
        $tour = factory('App\Models\Tour')->create([ 'leaf_id' => $zone->leaf_id ]);

        $response = $this->ajaxAction('PATCH', 'ZoneController@orderTours', [ $zone->leaf_id ], []);
        $content = $response->getContent();
        $data = json_decode($content);

        $this->assertJson($content);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */ 
    public function orderTours_WhenAuthenticatedAndFound_DoesNotUpdateInvalidTours(){
        $z1 = factory('App\Models\Zone')->create();
        $z2 = factory('App\Models\Zone')->create();

        $t1 = factory('App\Models\Tour')->create([ 'leaf_id' => $z1->leaf_id ]);
        $t2 = factory('App\Models\Tour')->create([ 'leaf_id' => $z1->leaf_id ]);
        $t3 = factory('App\Models\Tour')->create([ 'leaf_id' => $z2->leaf_id ]);

        $params = [
            'featured' => [ $t3->id, $t1->id ],
            'standard' => [ $t2->id ],
        ];

        $response = $this->htmlAction('PATCH', 'ZoneController@orderTours', [ $z1->leaf_id ], $params);

        $t1 = Tour::find($t1->id);
        $this->assertEquals(1, $t1->sequence);
        $this->assertEquals(1, $t1->featured);

        $t2 = Tour::find($t2->id);
        $this->assertEquals(1, $t2->sequence);
        $this->assertEquals(0, $t2->featured);

        $t3 = Tour::find($t3->id);
        $this->assertNotEquals(1, $t3->sequence);
        $this->assertNotEquals(1, $t3->featured);
    }

}