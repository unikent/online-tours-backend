<?php

use App\Models\Tour;
use Illuminate\Support\MessageBag;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;

class TourControllerTest extends TestCase {
    use DatabaseTransactions;

    public function setUp(){
        parent::setUp();

        Session::start(); // We need to start a session in order to use csrf_token()
        $this->setAdminSession();
    }

    protected function make_valid_zone(){
    	// zone needs leaf & leaf needs location
    	$loc = factory('App\Models\Location')->create();
    	$leaf = factory('App\Models\Leaf')->create(['location_id' => $loc->id]);
       	return factory('App\Models\Zone')->create(['leaf_id' => $leaf->id]);
    }
    protected function make_valid_tour($tour_data = array()){
    	// zone needs leaf & leaf needs location
    	$zone = $this->make_valid_zone();
       	return factory('App\Models\Tour')->create(['leaf_id' => $zone->leaf_id]);
    }


    /**
     * @test
     */
    public function create_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $this->htmlAction('GET', 'TourController@create');
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function create_Returns200(){
    	// zone needs leaf & leaf needs location
       	$z = $this->make_valid_zone();

       	$response = $this->htmlAction('GET', 'TourController@create', array($z->leaf_id));
        $this->assertEquals(200, $response->getStatusCode());
    }	

   	/**
     * @test
     */
    public function create_WhenAuthorised_SetsPageVariable(){
        $z = $this->make_valid_zone();
        $response = $this->htmlAction('GET', 'TourController@create', [$z->leaf_id]);

        $data = $response->original->getData();
        $this->assertEquals($z->leaf_id, $data['zone_id']);
        $this->assertTrue(isset($data['tour']));
    }

    /**
     * @test
     */
    public function store_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $z = $this->make_valid_zone();

        $response = $this->htmlAction('POST', 'TourController@store',[$z->leaf_id],['title'=>'foobar']);

        $this->assertRedirectedToRoute('auth.login');
    }


    /**
     * @test
     */
    public function store_WhenAuthenticatedPerformsValidation(){
        $z = $this->make_valid_zone();
        $attrs = attributes('App\Models\Tour');

        $mockRequest = Mockery::mock('App\Http\Requests\TourPersistRequest')->makePartial();
        $mockRequest->shouldReceive('validate')->once()->passthru();

        $this->app['App\Http\Requests\TourPersistRequest'] = $mockRequest;

        $response = $this->htmlAction('POST', 'TourController@store', [$z->leaf_id], $attrs, [], [], ['HTTP_REFERER'=>action('TourController@create',[$z->leaf_id])]);

    }

    /**
     * @test
     */
    public function store_WhenAuthenticatedValidationFails_RedirectsToReferer(){
        $z = $this->make_valid_zone();
        $attrs = attributes('App\Models\Tour');

        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag ]);

        $mockRequest = Mockery::mock('App\Http\Requests\TourPersistRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);

        $this->app['App\Http\Requests\TourPersistRequest'] = $mockRequest;

        $response = $this->htmlAction('POST', 'TourController@store', [$z->leaf_id], $attrs, [], [], ['HTTP_REFERER'=>action('TourController@create',[$z->leaf_id])]);
        $this->assertRedirectedToAction('TourController@create',[$z->leaf_id]);
        $this->assertEquals(false, Tour::all()->last());
    }

     /**
     * @test
     */
    public function edit_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        $tour = $this->make_valid_tour();
        $this->htmlAction('GET', 'TourController@edit', [$tour->leaf_id, $tour->id]);
        $this->assertRedirectedToRoute('auth.login');
    }

    /**
     * @test
     */
    public function edit_WhenAuthorised_Returns200(){
        $tour = $this->make_valid_tour();

        $response = $this->htmlAction('GET','TourController@edit', [$tour->leaf_id, $tour->id]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function edit_WhenAuthorised_SetsPageVariable(){
        $tour = $this->make_valid_tour();

        $response = $this->htmlAction('GET', 'TourController@edit', [$tour->leaf_id, $tour->id]);

        $data = $response->original->getData();
        $this->assertEquals($tour->leaf_id, $data['zone_id']);
        $this->assertEquals($tour->id, $data['tour']->id);
    }



    /**
     * @test
     */
    public function update_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $tour = $this->make_valid_tour();
        $response = $this->htmlAction('PATCH', 'TourController@update', [$tour->leaf_id, $tour->id]);
        $this->assertRedirectedToRoute('auth.login');
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_405WhenUpdateWithNoId(){
        $tour = $this->make_valid_tour();
        $response = $this->htmlAction('PATCH', 'TourController@update', [$tour->leaf_id]);
        $this->assertEquals(405, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function update_WhenAuthenticatedPerformsValidation(){
        $tour = $this->make_valid_tour();
        $attrs = attributes('App\Models\Tour');

        $mockRequest = Mockery::mock('App\Http\Requests\TourPersistRequest')->makePartial();
        $mockRequest->shouldReceive('validate')->once()->passthru();

        $this->app['App\Http\Requests\TourPersistRequest'] = $mockRequest;

        $response = $this->htmlAction('PATCH', 'TourController@update', [$tour->leaf_id, $tour->id], $attrs, [], [], ['HTTP_REFERER'=>action('TourController@edit',[$tour->leaf_id,$tour->id])]);

    }

    /**
     * @test
     */
    public function update_WhenAuthenticatedValidationFails_RedirectsToReferer(){
        $tour = $this->make_valid_tour();
        $attrs = attributes('App\Models\Tour');

        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag ]);

        $mockRequest = Mockery::mock('App\Http\Requests\TourPersistRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);

        $this->app['App\Http\Requests\TourPersistRequest'] = $mockRequest;

        $response = $this->htmlAction('PATCH', 'TourController@update', [$tour->leaf_id, $tour->id], $attrs, [], [], ['HTTP_REFERER'=>action('TourController@edit',[$tour->leaf_id,$tour->id])]);
        $this->assertRedirectedToAction('TourController@edit',[$tour->leaf_id, $tour->id]);
    }


     /**
     * @test
     */
    public function destroy_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $tour = $this->make_valid_tour();

        $this->htmlAction('DELETE', 'TourController@destroy', [$tour->leaf_id, $tour->id]);
        $this->assertRedirectedToRoute('auth.login');
    }

    /**
     * @test
     */
    public function destroy_WhenNotFound_Returns404(){
        $response = $this->htmlAction('DELETE', 'TourController@destroy', [123,123]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorised_deletesAndRedirectsToIndexWithAlert(){
        $tour = $this->make_valid_tour();
        $response = $this->htmlAction('DELETE', 'TourController@destroy',  [$tour->leaf_id, $tour->id]);

        $deletedTour = Tour::onlyTrashed()->find($tour->id);

        $this->assertTrue($deletedTour !== false);
        $this->assertRedirectedToAction('ZoneController@index');
    }
}