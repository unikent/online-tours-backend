<?php

use App\Models\Content;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;

class ContentControllerTest extends TestCase {

    use DatabaseTransactions;

    public function setUp(){
        parent::setUp();
        Session::start(); 				// We need to start a session in order to use csrf_token()
        $this->setAdminSession();
    }

    /**
     * @test
     */
    public function edit_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $content = factory('App\Models\Content\Text')->create();
        $response = $this->ajaxAction('GET', 'ContentController@edit', [$content->id]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function edit_WhenAuthorised_Return406_forHTMLRequests(){
        $content = factory('App\Models\Content\Text')->create();

        $response = $this->htmlAction('GET','ContentController@edit', [$content->id]);
        $this->assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function edit_WhenAuthorised_Returns200_forAjaxRequests()
    {
        $content = factory('App\Models\Content\Text')->create();

        $response = $this->ajaxAction('GET', 'ContentController@edit', [$content->id]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function edit_WhenAuthorised_returnsCorrectJSON(){
        $content = factory('App\Models\Content\Text')->create();

        $response = $this->ajaxAction('GET', 'ContentController@edit', [$content->id]);

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('success',$data);
        $this->assertTrue($data->success);
        $this->assertObjectHasAttribute('html',$data);
        $this->assertTrue(strstr($data->html,'id="content-' . $content->id . '"')!==false);
        $this->assertObjectHasAttribute('type',$data);
        $this->assertEquals('text',$data->type);
    }

    /**
     * @test
     */
    public function update_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $content = factory('App\Models\Content\Text')->create();
        $response = $this->ajaxAction('PATCH', 'ContentController@update', [$content->id],attributes('App\Models\Content\Text'));
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_Return406_forHTMLRequests(){
        $content = factory('App\Models\Content\Text')->create();

        $response = $this->htmlAction('PATCH', 'ContentController@update', [$content->id], attributes('App\Models\Content\Text'));

        $this->assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_405WhenUpdateWithNoId(){
        $content = factory('App\Models\Content\Text')->create();

        $response = $this->call('PATCH', url('content'),['_token'=>csrf_token()]);
        $this->assertEquals(405, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_savesAndReturnsCorrectJSON(){
        $content = factory('App\Models\Content\Text')->create();


        $response = $this->ajaxAction('PATCH', 'ContentController@update', [$content->id], attributes('App\Models\Content\Text',['name'=>'foobar','value'=>'boofar']));
        $after = Content::find($content->id);
        $this->assertEquals('foobar',$after->name);

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('success',$data);
        $this->assertTrue($data->success);
        $this->assertObjectHasAttribute('html',$data);
        $this->assertTrue(strstr($data->html,'id="content-' . $after->id . '"')!==false);
    }

    /**
     * @test
     */
    public function update_whenInvalid_Returns422WithErrors(){
        $content = factory('App\Models\Content\Text')->create();

        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag(['test'=>'foobar']) ]);

        $mockRequest = Mockery::mock('App\Http\Requests\ContentPersistRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);

        $this->app['App\Http\Requests\ContentPersistRequest'] = $mockRequest;

        $response = $this->ajaxAction('PATCH', 'ContentController@update', [$content->id]);

        $this->assertEquals(422,$response->getStatusCode());
        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);
        $this->assertObjectHasAttribute('test',$data);
        $this->assertEquals('foobar',$data->test[0]);
    }

    /**
     * @test
     */
    public function create_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $response = $this->ajaxAction('GET', 'ContentController@create');
        $this->assertEquals(401, $response->getStatusCode());
    }


    /**
     * @test
     */
    public function create_WhenAuthorised_Return406_forHTMLRequests(){
        $response = $this->htmlAction('GET', 'ContentController@create');
        $this->assertEquals(406, $response->getStatusCode());
    }
    /**
     * @test
     */
    public function create_WhenAuthorised_Returns200(){
        $response = $this->ajaxAction('GET','ContentController@create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function create_WhenAuthorised_ReturnsCorrectJson(){
        $response = $this->ajaxAction('GET', 'ContentController@create');

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('success',$data);
        $this->assertTrue($data->success);
        $this->assertObjectHasAttribute('html',$data);
        $this->assertTrue(strstr($data->html,'id="content-new"')!==false);
        $this->assertObjectHasAttribute('type',$data);
        $this->assertEquals('text',$data->type);

    }

    /**
     * @test
     */
    public function store_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $response = $this->ajaxAction('POST', 'ContentController@store',[],attributes('App\Models\Content\Text',['name'=>'foobar','value'=>'boofar']));

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function store_WhenAuthorised_Return406_forHTMLRequests(){

        $response = $this->htmlAction('POST', 'ContentController@store',[],attributes('App\Models\Content\Text',['name'=>'foobar','value'=>'boofar']));

        $this->assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function store_WhenAuthorised_savesAndReturnsCorrectJSON(){
        $response = $this->ajaxAction('POST', 'ContentController@store', [], attributes('App\Models\Content\Text',['name'=>'foobar','value'=>'boofar']));
        $after = Content::all()->last();
        $this->assertEquals('foobar',$after->name);

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('success',$data);
        $this->assertTrue($data->success);
        $this->assertObjectHasAttribute('html',$data);
        $this->assertTrue(strstr($data->html,'id="content-' . $after->id . '"')!==false);
        $this->assertObjectHasAttribute('type',$data);
        $this->assertEquals('text',$data->type);

    }

    /**
     * @test
     */
    public function store_WhenAuthorised_attachesContentToOwnerIfProvided(){
        $page = factory('App\Models\Page')->create();
        $response = $this->ajaxAction('POST', 'ContentController@store', [], attributes('App\Models\Content\Text',['name'=>'foobar','value'=>'boofar','owner'=>$page->id,'owner_type'=>'page']));
        $after = Content::all()->last();

        $this->assertTrue($page->contents()->get()->contains($after->id));

    }

    /**
     * @test
     */
    public function store_whenInvalid_Returns422WithErrors(){
        $content = factory('App\Models\Content\Text')->create();

        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag(['test'=>'foobar']) ]);

        $mockRequest = Mockery::mock('App\Http\Requests\ContentPersistRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);

        $this->app['App\Http\Requests\ContentPersistRequest'] = $mockRequest;

        $response = $this->ajaxAction('POST', 'ContentController@store', [$content->id]);

        $this->assertEquals(422,$response->getStatusCode());
        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);
        $this->assertObjectHasAttribute('test',$data);
        $this->assertEquals('foobar',$data->test[0]);
    }

    /**
     * @test
     */
    public function destroy_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $content = factory('App\Models\Content\Text')->create();

        $response = $this->ajaxAction('DELETE', 'ContentController@destroy', $content->id);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorised_Return406_forHTMLRequests(){

        $content = factory('App\Models\Content\Text')->create();

        $response = $this->htmlAction('DELETE', 'ContentController@destroy', $content->id);

        $this->assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function destroy_WhenNotFound_Returns404(){
        $response = $this->ajaxAction('DELETE', 'ContentController@destroy', 123);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorised_deletesAndReturnsCorrectJSON(){
        $content = factory('App\Models\Content\Text')->create();
        $response = $this->ajaxAction('DELETE', 'ContentController@destroy', $content->id);

        $this->assertNull(Content::find($content->id));

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('success',$data);
        $this->assertTrue($data->success);

    }


    /**
     * @test
     */
    public function attach_requiresAuthentication(){
        $owner = factory('App\Models\Leaf')->create();

        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $content = factory('App\Models\Content\Text')->create();

        $response = $this->ajaxAction('POST', 'ContentController@attach', $content->id,['owner'=>$owner->id,'owner_type'=>'leaf']);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function attach_WhenAuthorised_Return406_forHTMLRequests(){
        $owner = factory('App\Models\Leaf')->create();
        $content = factory('App\Models\Content\Text')->create();

        $response = $this->htmlAction('POST', 'ContentController@attach', $content->id,['owner'=>$owner->id,'owner_type'=>'leaf']);

        $this->assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function attach_WhenNotFound_Returns404(){
        $owner = factory('App\Models\Leaf')->create();
        $response = $this->ajaxAction('POST', 'ContentController@attach', 123, ['owner'=>$owner->id,'owner_type'=>'leaf']);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function attach_WhenAuthorised_attachesAndReturnsCorrectJSON(){
        $owner = factory('App\Models\Leaf')->create();
        $content = factory('App\Models\Content\Text')->create();
        $response = $this->ajaxAction('POST', 'ContentController@attach', $content->id, ['owner'=>$owner->id,'owner_type'=>'leaf']);


        $this->assertTrue($owner->contents()->get()->contains($content->id));

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('success',$data);
        $this->assertTrue($data->success);
        $this->assertObjectHasAttribute('html',$data);
        $this->assertTrue(strstr($data->html,'id="content-' . $content->id . '"')!==false);
        $this->assertObjectHasAttribute('id',$data);
        $this->assertEquals($content->id,$data->id);
        $this->assertObjectHasAttribute('type',$data);
        $this->assertEquals('text',$data->type);
    }

    /**
     * @test
     */
    public function attach_WhenAuthorised_returns404_whenNonExistentOwner(){
        $content = factory('App\Models\Content\Text')->create();
        $response = $this->ajaxAction('POST', 'ContentController@attach', $content->id, ['owner'=>123,'owner_type'=>'leaf']);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function attach_whenInvalid_Returns422WithErrors(){
        $content = factory('App\Models\Content\Text')->create();

        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag(['test'=>'foobar']) ]);

        $mockRequest = Mockery::mock('App\Http\Requests\ContentRelationshipRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);

        $this->app['App\Http\Requests\ContentRelationshipRequest'] = $mockRequest;

        $response = $this->ajaxAction('POST', 'ContentController@attach', [$content->id]);

        $this->assertEquals(422,$response->getStatusCode());
        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);
        $this->assertObjectHasAttribute('test',$data);
        $this->assertEquals('foobar',$data->test[0]);
    }

    /**
     * @test
     */
    public function detach_requiresAuthentication(){
        $owner = factory('App\Models\Leaf')->create();

        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $content = factory('App\Models\Content\Text')->create();

        $response = $this->ajaxAction('POST', 'ContentController@detach', $content->id,['owner'=>$owner->id,'owner_type'=>'leaf']);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function detach_WhenAuthorised_Return406_forHTMLRequests(){
        $owner = factory('App\Models\Leaf')->create();
        $content = factory('App\Models\Content\Text')->create();

        $response = $this->htmlAction('POST', 'ContentController@detach', $content->id,['owner'=>$owner->id,'owner_type'=>'leaf']);

        $this->assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function detach_WhenNotFound_Returns404(){
        $owner = factory('App\Models\Leaf')->create();
        $response = $this->ajaxAction('POST', 'ContentController@detach', 123, ['owner'=>$owner->id,'owner_type'=>'leaf']);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function detach_WhenAuthorised_returns404_whenNonExistentOwner(){
        $content = factory('App\Models\Content\Text')->create();
        $response = $this->ajaxAction('POST', 'ContentController@detach', $content->id, ['owner'=>123,'owner_type'=>'leaf']);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function detach_WhenAuthorised_detachesAndReturnsCorrectJSON(){
        $owner = factory('App\Models\Leaf')->create();
        $content = factory('App\Models\Content\Text')->create();

        $owner->contents()->attach($content);

        $response = $this->ajaxAction('POST', 'ContentController@detach', $content->id, ['owner'=>$owner->id,'owner_type'=>'leaf']);

        $this->assertFalse($owner->contents()->get()->contains($content->id));

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('success',$data);
        $this->assertTrue($data->success);
    }

    /**
     * @test
     */
    public function detach_whenInvalid_Returns422WithErrors(){
        $content = factory('App\Models\Content\Text')->create();

        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag(['test'=>'foobar']) ]);

        $mockRequest = Mockery::mock('App\Http\Requests\ContentRelationshipRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);

        $this->app['App\Http\Requests\ContentRelationshipRequest'] = $mockRequest;

        $response = $this->ajaxAction('POST', 'ContentController@detach', [$content->id]);

        $this->assertEquals(422,$response->getStatusCode());
        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);
        $this->assertObjectHasAttribute('test',$data);
        $this->assertEquals('foobar',$data->test[0]);
    }


    /**
     * @test
     */
    public function order_requiresAuthentication(){
        $owner = factory('App\Models\Leaf')->create();

        $i=1;
        $contents = factory('App\Models\Content',3)->create()->each(function($self) use ($owner,&$i) {
            $owner->contents()->attach($self->id,['sequence'=>$i]);
            $i++;
        });

        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $response = $this->ajaxAction('POST', 'ContentController@order', [],['owner'=>$owner->id,'owner_type'=>'leaf','content'=>[$contents[2]->id,$contents[0]->id,$contents[1]->id]]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function order_WhenAuthorised_Return406_forHTMLRequests(){
        $owner = factory('App\Models\Leaf')->create();

        $i=1;
        $contents = factory('App\Models\Content',3)->create()->each(function($self) use ($owner,&$i) {
            $owner->contents()->attach($self->id,['sequence'=>$i]);
            $i++;
        });

        $response = $this->htmlAction('POST', 'ContentController@order', [],['owner'=>$owner->id,'owner_type'=>'leaf','content'=>[$contents[2]->id,$contents[0]->id,$contents[1]->id]]);

        $this->assertEquals(406, $response->getStatusCode());
    }


    /**
     * @test
     */
    public function order_WhenAuthorised_returns404_whenNonExistentOwner(){
        $contents = factory('App\Models\Content',3)->create();
        $response = $this->ajaxAction('POST', 'ContentController@order', [], ['owner'=>123,'owner_type'=>'leaf','content'=>[$contents[2]->id,$contents[0]->id,$contents[1]->id]]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function order_WhenAuthorised_ordersAndReturnsCorrectJSON(){
        $owner = factory('App\Models\Leaf')->create();

        $i=1;
        $contents = factory('App\Models\Content',3)->create()->each(function($self) use ($owner,&$i) {
            $owner->contents()->attach($self->id,['sequence'=>$i]);
            $i++;
        });

        $response = $this->ajaxAction('POST', 'ContentController@order', [], ['owner'=>$owner->id,'owner_type'=>'leaf','content'=>[$contents[2]->id,$contents[0]->id,$contents[1]->id]]);

        $sequence = $owner->contents()->get()->modelKeys();

        $this->assertEquals($contents[2]->id, $sequence[0]);
        $this->assertEquals($contents[0]->id, $sequence[1]);
        $this->assertEquals($contents[1]->id, $sequence[2]);

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('success',$data);
        $this->assertTrue($data->success);
    }

    /**
     * @test
     */
    public function order_WhenAuthorised_syncsContent(){
        $owner = factory('App\Models\Leaf')->create();

        $i=1;
        $contents = factory('App\Models\Content',3)->create()->each(function($self) use ($owner,&$i) {
            $owner->contents()->attach($self->id,['sequence'=>$i]);
            $i++;
        });

        $response = $this->ajaxAction('POST', 'ContentController@order', [], ['owner'=>$owner->id,'owner_type'=>'leaf','content'=>[$contents[2]->id,$contents[0]->id]]);

        $sequence = $owner->contents()->get()->modelKeys();

        $this->assertCount(2,$sequence);
        $this->assertEquals($contents[2]->id, $sequence[0]);
        $this->assertEquals($contents[0]->id, $sequence[1]);

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('success',$data);
        $this->assertTrue($data->success);
    }

    /**
     * @test
     */
    public function order_whenInvalid_Returns422WithErrors(){
        $content = factory('App\Models\Content\Text')->create();

        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag(['test'=>'foobar']) ]);

        $mockRequest = Mockery::mock('App\Http\Requests\ContentMoveRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);

        $this->app['App\Http\Requests\ContentMoveRequest'] = $mockRequest;

        $response = $this->ajaxAction('POST', 'ContentController@order', [$content->id]);

        $this->assertEquals(422,$response->getStatusCode());
        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);
        $this->assertObjectHasAttribute('test',$data);
        $this->assertEquals('foobar',$data->test[0]);
    }

    /**
     * @test
     */
    public function search_requiresAuthentication(){

        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $content = factory('App\Models\Content\Text')->create();

        $response = $this->ajaxAction('POST', 'ContentController@search');

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function search_WhenAuthorised_Return406_forHTMLRequests(){

        $response = $this->htmlAction('POST', 'ContentController@search');
        $this->assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function search_WhenAuthorised_searchesAndReturnsCorrectJSON_withJustTerm(){
        $owner = factory('App\Models\Leaf')->create();
        $owner2 = factory('App\Models\Page')->create();

        $content = factory('App\Models\Content\Text')->create(['name' => 'foobar']);
        $content2 = factory('App\Models\Content\Text')->create(['name' => 'boofar']);
        $content3 = factory('App\Models\Content\Image')->create(['name'=>'mwoar foobared things']);
        $content4 = factory('App\Models\Content\Image')->create();

        $owner->contents()->attach($content);
        $owner->contents()->attach($content2);
        $owner->contents()->attach($content3);
        $owner->contents()->attach($content4);

        $owner2->contents()->attach($content3);
        $owner2->contents()->attach($content4);

        $response = $this->ajaxAction('POST', 'ContentController@search',['search'=>'foobar']);

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('more',$data);
        $this->assertFalse($data->more);
        $this->assertObjectHasAttribute('items',$data);
        $this->assertCount(2,$data->items);
        $this->assertTrue($this->search_array_for_object($data->items,$content));
        $this->assertTrue($this->search_array_for_object($data->items,$content3));
    }

    /**
     * @test
     */
    public function search_WhenAuthorised_searchesAndReturnsCorrectJSON_withJustType(){
        $owner = factory('App\Models\Leaf')->create();
        $owner2 = factory('App\Models\Page')->create();

        $content = factory('App\Models\Content\Text')->create(['name' => 'foobar']);
        $content2 = factory('App\Models\Content\Text')->create(['name' => 'boofar']);
        $content3 = factory('App\Models\Content\Image')->create(['name'=>'mwoar foobared things']);
        $content4 = factory('App\Models\Content\Image')->create();

        $owner->contents()->attach($content);
        $owner->contents()->attach($content2);
        $owner->contents()->attach($content3);
        $owner->contents()->attach($content4);

        $owner2->contents()->attach($content3);
        $owner2->contents()->attach($content4);

        $response = $this->ajaxAction('POST', 'ContentController@search',['type'=>'text']);

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('more',$data);
        $this->assertFalse($data->more);
        $this->assertObjectHasAttribute('items',$data);
        $this->assertCount(2,$data->items);
        $this->assertTrue($this->search_array_for_object($data->items,$content));
        $this->assertTrue($this->search_array_for_object($data->items,$content2));
    }

    /**
     * @test
     */
    public function search_WhenAuthorised_searchesAndReturnsCorrectJSON_withJustOwner(){
        $owner = factory('App\Models\Leaf')->create();
        $owner2 = factory('App\Models\Page')->create();

        $content = factory('App\Models\Content\Text')->create(['name' => 'foobar']);
        $content2 = factory('App\Models\Content\Text')->create(['name' => 'boofar']);
        $content3 = factory('App\Models\Content\Image')->create(['name'=>'mwoar foobared things']);
        $content4 = factory('App\Models\Content\Image')->create();

        $owner->contents()->attach($content);
        $owner->contents()->attach($content2);
        $owner->contents()->attach($content3);
        $owner->contents()->attach($content4);

        $owner2->contents()->attach($content3);
        $owner2->contents()->attach($content4);

        $response = $this->ajaxAction('POST', 'ContentController@search',['owner'=>$owner2->id,'owner_type'=>'page']);

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('more',$data);
        $this->assertFalse($data->more);
        $this->assertObjectHasAttribute('items',$data);
        $this->assertCount(2,$data->items);
        $this->assertTrue($this->search_array_for_object($data->items,$content));
        $this->assertTrue($this->search_array_for_object($data->items,$content2));
    }

    /**
     * @test
     */
    public function search_WhenAuthorised_searchesAndReturnsCorrectJSON_withAllOptions(){
        $owner = factory('App\Models\Leaf')->create();
        $owner2 = factory('App\Models\Page')->create();

        $content = factory('App\Models\Content\Text')->create(['name' => 'foobar']);
        $content2 = factory('App\Models\Content\Text')->create(['name' => 'boofar']);
        $content3 = factory('App\Models\Content\Image')->create(['name'=>'mwoar foobared things']);
        $content4 = factory('App\Models\Content\Image')->create();

        $owner->contents()->attach($content);
        $owner->contents()->attach($content2);
        $owner->contents()->attach($content4);

        $owner2->contents()->attach($content3);
        $owner2->contents()->attach($content4);

        $response = $this->ajaxAction('POST', 'ContentController@search',['search'=>'foobar','type'=>'image','owner'=>$owner->id,'owner_type'=>'leaf']);

        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);

        $this->assertObjectHasAttribute('more',$data);
        $this->assertFalse($data->more);
        $this->assertObjectHasAttribute('items',$data);
        $this->assertCount(1,$data->items);
        $this->assertTrue($this->search_array_for_object($data->items,$content3));
    }

    /**
     * @test
     */
    public function search_whenInvalid_Returns422WithErrors(){
        $content = factory('App\Models\Content\Text')->create();

        $mockValidator = Mockery::mock('Illuminate\Validation\Validator')->makePartial();
        $mockValidator->shouldReceive([ 'passes' => false, 'errors' => new MessageBag(['test'=>'foobar']) ]);

        $mockRequest = Mockery::mock('App\Http\Requests\ContentSearchRequest')->makePartial()->shouldAllowMockingProtectedMethods();
        $mockRequest->shouldReceive('getValidatorInstance')->andReturn($mockValidator);

        $this->app['App\Http\Requests\ContentSearchRequest'] = $mockRequest;

        $response = $this->ajaxAction('POST', 'ContentController@search', [$content->id]);

        $this->assertEquals(422,$response->getStatusCode());
        $data = $response->getContent();
        $this->assertJson($data);

        $data = json_decode($data);
        $this->assertObjectHasAttribute('test',$data);
        $this->assertEquals('foobar',$data->test[0]);
    }
    
}