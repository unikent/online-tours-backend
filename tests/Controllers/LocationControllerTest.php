<?php

use App\Models\Location;

use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LocationControllerTest extends TestCase {

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

        $this->htmlAction('GET', 'LocationController@index');
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function index_WhenAuthenticated_Returns200(){
        $response = $this->htmlAction('GET', 'LocationController@index');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function index_WhenAuthenticated_SetsRequiredVariables(){
        $locations = factory('App\Models\Location', 3)->create();

        $response = $this->htmlAction('GET', 'LocationController@index');
        $this->assertViewHas('locations');
        $this->assertCount(3, $locations);
    }




    /**
     * @test
     */
    public function create_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $this->htmlAction('GET', 'LocationController@create');
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function create_WhenAuthenticated_Returns200(){
        $response = $this->htmlAction('GET', 'LocationController@create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function create_WhenAuthenticated_SetsRequiredVariables(){
        $response = $this->htmlAction('GET', 'LocationController@create');
        $this->assertViewHas('location');
    }


    /**
     * @test
     */
    public function store_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        $attrs = attributes('App\Models\Location');

        $this->htmlAction('POST', 'LocationController@store', [], $attrs);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function store_WhenAuthenticatedPerformsValidation(){
        $attrs = attributes('App\Models\Location');

        $mockRequest = Mockery::mock('App\Http\Requests\LocationPersistRequest')->makePartial();
        $mockRequest->shouldReceive('validate')->once()->passthru();

        $this->app['App\Http\Requests\LocationPersistRequest'] = $mockRequest;

        $response = $this->htmlAction('POST', 'LocationController@store', [], $attrs);
    }

    /**
     * @test
     */
    public function store_WhenAuthenticatedValidationFails_RedirectsToReferer(){
        $attrs = attributes('App\Models\Location');

        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag ]);

        $mockRequest = Mockery::mock('App\Http\Requests\LocationPersistRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);

        $this->app['App\Http\Requests\LocationPersistRequest'] = $mockRequest;

        $response = $this->htmlAction('POST', 'LocationController@store', [], $attrs, [], [], [ 'HTTP_REFERER' => action('LocationController@create') ]);
        $this->assertRedirectedToAction('LocationController@create');
    }

    /**
     * @test
     */
    public function store_WhenAuthenticatedAndValid_CreatesNewLocation(){
        $count = Location::count();

        $attrs = attributes('App\Models\Location');
        $attrs = array_merge($attrs);

        $response = $this->htmlAction('POST', 'LocationController@store', [], $attrs);
        $this->assertEquals($count + 1, Location::count());
    }

    /**
     * @test
     */
    public function store_WhenAuthenticatedAndValid_RedirectsToEditAction(){
        $attrs = attributes('App\Models\Location');
        $attrs = array_merge($attrs);

        $response = $this->htmlAction('POST', 'LocationController@store', [], $attrs);

        $location = Location::all()->last();
        $this->assertRedirectedToAction('LocationController@edit', $location->id);
    }



    /**
     * @test
     */
    public function edit_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $location = factory('App\Models\Location')->create();

        $this->htmlAction('GET', 'LocationController@edit', $location->id);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function edit_WhenAuthenticatedAndNotFound_Returns404(){
        $response = $this->htmlAction('GET', 'LocationController@edit', 123);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function edit_WhenAuthenticatedAndLocationIsRemote_Returns404(){
        $location = factory('App\Models\Location')->create([ 'remote_id' => 456 ]);

        $response = $this->htmlAction('GET', 'LocationController@edit', $location->id);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function edit_WhenAuthenticatedAndFound_Returns200(){
        $location = factory('App\Models\Location')->create();

        $response = $this->htmlAction('GET', 'LocationController@edit', $location->id);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function edit_WhenAuthenticatedAndFound_SetsRequiredVariables(){
        $location = factory('App\Models\Location')->create();

        $response = $this->htmlAction('GET', 'LocationController@edit', $location->id);
        $data = $response->original->getData();

        $this->assertViewHas('location');
        $this->assertEquals($data['location']->id, $location->id);
    }


    /**
     * @test
     */
    public function update_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $location = factory('App\Models\Location')->create();
        $attrs = array_merge($location->toArray(), [ 'name' => 'Foobar!' ]);

        $this->htmlAction('PUT', 'LocationController@update', [ $location->id ], $attrs);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function update_WhenAuthenticatedPerformsValidation(){
        $location = factory('App\Models\Location')->create();
        $attrs = array_merge($location->toArray(), [ 'name' => 'Foobar!' ]);

        $mockRequest = Mockery::mock('App\Http\Requests\LocationPersistRequest')->makePartial();
        $mockRequest->shouldReceive('validate')->once()->passthru();

        $this->app['App\Http\Requests\LocationPersistRequest'] = $mockRequest;

        $this->htmlAction('PUT', 'LocationController@update', [ $location->id ], $attrs);
    }

    /**
     * @test
     */
    public function update_WhenAuthenticatedValidationFails_RedirectsToReferer(){
        $location = factory('App\Models\Location')->create();
        $attrs = array_merge($location->toArray(), [ 'name' => 'Foobar!' ]);

        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag ]);

        $mockRequest = Mockery::mock('App\Http\Requests\LocationPersistRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);

        $this->app['App\Http\Requests\LocationPersistRequest'] = $mockRequest;

        $response = $this->htmlAction('PUT', 'LocationController@update', [ $location->id ], $attrs, [], [], [ 'HTTP_REFERER' => action('LocationController@create') ]);
        $this->assertRedirectedToAction('LocationController@create');
    }

    /**
     * @test
     */
    public function update_WhenAuthenticatedAndValid_UpdatesExistingLocation(){
        $count = Location::count();

        $location = factory('App\Models\Location')->create();
        $attrs = array_merge($location->toArray(), [ 'name' => 'Foobar!' ]);

        $this->htmlAction('PUT', 'LocationController@update', [ $location->id ], $attrs);
        $this->assertEquals($count + 1, Location::count());
    }

    /**
     * @test
     */
    public function update_WhenAuthenticatedAndValid_RedirectsToEditAction(){
        $location = factory('App\Models\Location')->create();
        $attrs = array_merge($location->toArray(), [ 'name' => 'Foobar!' ]);

        $this->htmlAction('PUT', 'LocationController@update', [ $location->id ], $attrs);

        $location = Location::all()->last();
        $this->assertRedirectedToAction('LocationController@edit', $location->id);
    }
}