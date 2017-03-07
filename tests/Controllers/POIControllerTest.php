<?php

use App\Models\Leaf;
use App\Models\Location;

use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class POIControllerTest extends TestCase {

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

        $this->htmlAction('GET', 'POIController@index');
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function index_WhenAuthenticated_Returns200(){
        $response = $this->htmlAction('GET', 'POIController@index');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function index_WhenAuthenticated_SetsIdVariableToNull(){
        $leafs = factory('App\Models\Leaf', 2)->create()->each(function($self){
            $location = factory('App\Models\Location')->create();
            $self->location()->associate($location);
            $self->save();
        });

        $response = $this->htmlAction('GET', 'POIController@index');
        $data = $response->original->getData();

        $this->assertViewHas('id');
        $this->assertNull($data['id']);
    }

    /**
     * @test
     */
    public function index_WhenAuthenticatedAndWithId_SetsIdVariableToId(){
        $leafs = factory('App\Models\Leaf', 2)->create()->each(function($self){
            $location = factory('App\Models\Location')->create();
            $self->location()->associate($location);
            $self->save();
        });

        $response = $this->htmlAction('GET', 'POIController@index', [ $leafs[0]->id ]);
        $data = $response->original->getData();

        $this->assertViewHas('id');
        $this->assertEquals($leafs[0]->id, $data['id']);
    }



    /**
     * @test
     */
    public function create_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $this->htmlAction('GET', 'POIController@create');
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function create_WhenAuthenticated_Returns200(){
        $response = $this->htmlAction('GET', 'POIController@create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function create_WhenAuthenticatedAndWithoutParentId_SetsVariablesAppropriatelyForNewTree(){
        $leafs = factory('App\Models\Leaf', 2)->create()->each(function($self){
            $location = factory('App\Models\Location')->create();
            $self->location()->associate($location);
            $self->save();
        });

        $response = $this->htmlAction('GET', 'POIController@create');
        $data = $response->original->getData();

        $this->assertViewHas('leaf');
        $this->assertFalse($data['leaf']->exists);

        $this->assertViewHas('locations');
        $this->assertNotEmpty($data['locations']);

        $this->assertViewHas('parent');
        $this->assertNull($data['parent']);
    }

    /**
     * @test
     */
    public function create_WhenAuthenticatedAndWithParentId_SetsVariablesAppropriatelyForExistingTree(){
        $parent = factory('App\Models\Leaf')->create();
        $parent->location()->associate(factory('App\Models\Location')->create());
        $parent->save();

        $leafs = factory('App\Models\Leaf', 2)->create()->each(function($self){
            $location = factory('App\Models\Location')->create();
            $self->location()->associate($location);
            $self->save();
        });

        $response = $this->htmlAction('GET', 'POIController@create', $parent->id);
        $data = $response->original->getData();

        $this->assertViewHas('leaf');
        $this->assertFalse($data['leaf']->exists);

        $this->assertViewHas('locations');
        $this->assertCount(2, $data['locations']);

        $this->assertViewHas('parent');
        $this->assertEquals($parent->id, $data['parent']->id);
    }



    /**
     * @test
     */
    public function store_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        $attrs = attributes('App\Models\Leaf');

        $this->htmlAction('POST', 'POIController@store', [], $attrs);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function store_WhenAuthenticatedPerformsValidation(){
        $attrs = attributes('App\Models\Leaf');

        $mockRequest = Mockery::mock('App\Http\Requests\POIPersistRequest')->makePartial();
        $mockRequest->shouldReceive('validate')->once()->passthru();

        $this->app['App\Http\Requests\POIPersistRequest'] = $mockRequest;

        $response = $this->htmlAction('POST', 'POIController@store', [], $attrs);
    }

    /**
     * @test
     */
    public function store_WhenAuthenticatedValidationFails_RedirectsToReferer(){
        $attrs = attributes('App\Models\Leaf');

        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag ]);

        $mockRequest = Mockery::mock('App\Http\Requests\POIPersistRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);

        $this->app['App\Http\Requests\POIPersistRequest'] = $mockRequest;

        $response = $this->htmlAction('POST', 'POIController@store', [], $attrs, [], [], [ 'HTTP_REFERER' => action('POIController@create') ]);
        $this->assertRedirectedToAction('POIController@create');
    }


    /**
     * @test
     */
    public function store_WhenAuthenticatedAndValid_storesLeafAndRedirectToEdit(){
        $count = Location::count();

        $existing_location = factory('App\Models\Location')->create();

        $attrs = attributes('App\Models\Leaf',['location_id'=>$existing_location->id]);

        $response = $this->htmlAction('POST', 'POIController@store', [], $attrs);

        $leaf = Leaf::all()->last();
        $this->assertEquals($attrs['name'],$leaf->name);
        $this->assertEquals($existing_location->id,$leaf->location->id);
        $this->assertRedirectedToAction('POIController@edit',[$leaf->id]);
    }

    /**
     * @test
     */
    public function store_WhenAuthenticatedAndValidWithLocationIdAndParentId_NestsUnderExistingLeaf(){
        $location = factory('App\Models\Location')->create();

        $existing_leaf = factory('App\Models\Leaf')->create();
        $existing_location = factory('App\Models\Location')->create();
        $existing_leaf->location()->associate($existing_location);
        $existing_leaf->save();

        $attrs = attributes('App\Models\Leaf', [ 'location_id' => $location->id , 'parent_id' => $existing_leaf->id ]);

        $response = $this->htmlAction('POST', 'POIController@store', [], $attrs);

        $leaf = Leaf::all()->last();

        $this->assertEquals($existing_leaf->id, $leaf->parent_id);
    }

    /**
     * @test
     */
    public function edit_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate(factory('App\Models\Location')->create());
        $leaf->save();

        $leafs = factory('App\Models\Leaf', 2)->create()->each(function($self){
            $location = factory('App\Models\Location')->create();
            $self->location()->associate($location);
            $self->save();
        });

        $this->htmlAction('GET', 'POIController@edit', $leaf->id);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function edit_WhenAuthenticatedAndNotFound_Returns404(){
        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate(factory('App\Models\Location')->create());
        $leaf->save();

        $response = $this->htmlAction('GET', 'POIController@edit', 123);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function edit_WhenAuthenticatedAndFound_Returns200(){
        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate(factory('App\Models\Location')->create());
        $leaf->save();

        $leafs = factory('App\Models\Leaf', 2)->create()->each(function($self){
            $location = factory('App\Models\Location')->create();
            $self->location()->associate($location);
            $self->save();
        });

        $response = $this->htmlAction('GET', 'POIController@edit', $leaf->id);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function edit_WhenAuthenticatedAndFound_SetsRequiredVariables(){
        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate(factory('App\Models\Location')->create());
        $leaf->save();

        $response = $this->htmlAction('GET', 'POIController@edit', $leaf->id);
        $data = $response->original->getData();

        $this->assertViewHas('leaf');
        $this->assertEquals($data['leaf']->id, $leaf->id);
    }



    /**
     * @test
     */
    public function destroy_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate(factory('App\Models\Location')->create());
        $leaf->save();

        $this->htmlAction('DELETE', 'POIController@destroy', $leaf->id);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }

    /**
     * @test
     */
    public function destroy_WhenAuthenticatedAndNotFound_Returns404(){
        $response = $this->htmlAction('DELETE', 'POIController@destroy', 123);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function destroy_WhenAuthenticated_DeletesAndRedirectsToIndexWithAlert(){
        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate(factory('App\Models\Location')->create());
        $leaf->save();

        $response = $this->htmlAction('DELETE', 'POIController@destroy', $leaf->id);

        $this->assertNull(Leaf::find($leaf->id));
        $this->assertSessionHas('alert');
        $this->assertRedirectedToAction('POIController@index');
    }

    /**
     * @test
     */
    public function destroy_WhenAjaxAndAuthorised_DeletesAndReturnsJsonWithRedirectTo(){
        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate(factory('App\Models\Location')->create());
        $leaf->save();

        $response = $this->ajaxAction('DELETE', 'POIController@destroy', $leaf->id);
        $data = $response->getContent();
        $json = json_decode($data, TRUE);

        $this->assertNull(Leaf::find($leaf->id));
        $this->assertSessionHas('alert');

        $this->assertJson($data);
        $this->assertEquals($json['redirect_to'], action('POIController@index'));
    }

    /**
     * @test
     */
    public function update_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        $leaf = factory('App\Models\Leaf')->create();
        $location = factory('App\Models\Location')->create();
        $leaf->location()->associate($location);
        $leaf->save();
        $attrs = attributes('App\Models\Leaf');
        $this->htmlAction('PUT', 'POIController@update', $leaf->id, $attrs);
        $this->assertRedirectedToAction('AuthController@getLogin');
    }
    /**
     * @test
     */
    public function update_WhenAuthenticatedPerformsValidation(){
        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate(factory('App\Models\Location')->create());
        $leaf->save();
        $attrs = attributes('App\Models\Leaf');
        $mockRequest = Mockery::mock('App\Http\Requests\POIPersistRequest')->makePartial();
        $mockRequest->shouldReceive('validate')->once()->passthru();
        $this->app['App\Http\Requests\POIPersistRequest'] = $mockRequest;
        $response = $this->htmlAction('PUT', 'POIController@update', $leaf->id, $attrs);
    }
    /**
     * @test
     */
    public function update_WhenAuthenticatedValidationFails_RedirectsToReferer(){
        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate(factory('App\Models\Location')->create());
        $leaf->save();
        $attrs = attributes('App\Models\Leaf');
        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag ]);
        $mockRequest = Mockery::mock('App\Http\Requests\POIPersistRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);
        $this->app['App\Http\Requests\POIPersistRequest'] = $mockRequest;
        $response = $this->htmlAction('PUT', 'POIController@update', $leaf->id, $attrs, [], [], [ 'HTTP_REFERER' => action('POIController@edit', $leaf->id) ]);
        $this->assertRedirectedToAction('POIController@edit', $leaf->id);
    }


    /**
     * @test
     */
    public function update_WhenAuthenticated_WithValidData_savesCorrectly(){
        $locations = factory('App\Models\Location', 2)->create();
        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate($locations[0]);
        $leaf->save();
        $attrs = attributes('App\Models\Leaf',['location_id' => $locations[1]->id]);

        $this->htmlAction('PATCH', 'POIController@update', $leaf->id, $attrs);

        $leaf->reload();
        $this->assertEquals($leaf->location_id, $locations[1]->id);
        $this->assertEquals($attrs['name'], $leaf->name);
        $this->assertRedirectedToAction('POIController@edit', $leaf->id);
    }
    /**
     * @test
     */
    public function update_WhenAjaxANDAuthenticated_WithValidData_savesCorrectly(){
        $locations = factory('App\Models\Location', 2)->create();
        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate($locations[0]);
        $leaf->save();
        $attrs = attributes('App\Models\Leaf',['location_id' => $locations[1]->id]);

        $response = $this->ajaxAction('PATCH', 'POIController@update', $leaf->id, $attrs);

        $leaf->reload();
        $this->assertEquals($leaf->location_id, $locations[1]->id);
        $this->assertEquals($attrs['name'], $leaf->name);

        $data = $response->getContent();
        $json = json_decode($data, TRUE);
        $this->assertJson($data);
        $this->assertArrayHasKey('success', $json);
        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('leaf', $json);
    }

    /**
     * @test
     */
    public function update_WhenAuthenticated_WithParentId_UpdatesParent(){
        $parents = factory('App\Models\Leaf', 2)->create();
        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate(factory('App\Models\Location')->create());
        $leaf->save();
        $leaf->makeChildOf($parents[0]);
        $attrs = attributes('App\Models\Location', [ 'polygon' => 'foobar' ]);
        $attrs['parent_id'] = $parents[1]->id;
        $this->htmlAction('PATCH', 'POIController@update', $leaf->id, $attrs);
        $leaf->reload();
        $this->assertEquals($leaf->parent_id, $parents[1]->id);
        $this->assertRedirectedToAction('POIController@edit', $leaf->id);
    }

    /**
     * @test
     */
    public function update_WhenAuthenticated_WithParentIdZero_MakesRootItem(){
        $parent = factory('App\Models\Leaf')->create();
        $leaf = factory('App\Models\Leaf')->create();
        $leaf->location()->associate(factory('App\Models\Location')->create());
        $leaf->save();
        $leaf->makeChildOf($parent);
        $response = $this->htmlAction('PATCH', 'POIController@update', $leaf->id, [ 'parent_id' => 0 ]);
        $leaf->reload();
        $this->assertEmpty($leaf->parent_id);
    }

}