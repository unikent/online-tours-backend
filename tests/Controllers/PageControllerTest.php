<?php

use App\Models\Page;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class PageControllerTest extends TestCase {

    use DatabaseTransactions;

    public function setUp(){
        parent::setUp();
        Session::start(); 				// We need to start a session in order to use csrf_token()
        $this->setAdminSession();
    }

    /**
     * @test
     */
    public function index_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        $this->htmlAction('GET', 'PageController@index');
        $this->assertRedirectedToRoute('auth.login');
    }
    
    /**
     * @test
     */
    public function index_Returns200(){
        factory('App\Models\Page')->create();

        $response = $this->htmlAction('GET', 'PageController@index');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function index_WhenAuthorised_SetsPagesVariable(){
        factory('App\Models\Page', 2)->create();

        $response = $this->htmlAction('GET', 'PageController@index');
        $data = $response->original->getData();

        $this->assertCount(2, $data['pages']); 
    }

    /**
     * @test
     */
    public function edit_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $page = factory('App\Models\Page')->create();
        $this->htmlAction('GET', 'PageController@edit', [$page->id]);
        $this->assertRedirectedToRoute('auth.login');
    }

    /**
     * @test
     */
    public function edit_WhenAuthorised_Returns200(){
        $page = factory('App\Models\Page')->create();

        $response = $this->htmlAction('GET','PageController@edit', [$page->id]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function edit_WhenAuthorised_SetsPageVariable(){
        $page = factory('App\Models\Page')->create();

        $response = $this->htmlAction('GET', 'PageController@edit', [$page->id]);

        $data = $response->original->getData();
        $this->assertEquals($page->id, $data['page']->id);
    }

    /**
     * @test
     */
    public function update_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $page = factory('App\Models\Page')->create();
        $response = $this->htmlAction('PATCH', 'PageController@update', [$page->id]);
        $this->assertRedirectedToRoute('auth.login');
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_405WhenUpdateWithNoId(){
        $page = factory('App\Models\Page')->create();

        $response = $this->call('PATCH', url('page'),['_token'=>csrf_token()]);
        $this->assertEquals(405, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_doesNotChangeSlug(){
        $page = factory('App\Models\Page')->create();
        $response = $this->htmlAction('PATCH', 'PageController@update', [$page->id], ['title'=>'foobar']);
        $after = Page::find($page->id);
        $this->assertEquals($page->slug,$after->slug);
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_savesAndRedirectsToIndexWithAlert(){
        $page = factory('App\Models\Page')->create();

        $response = $this->htmlAction('PATCH', 'PageController@update', [$page->id], ['title'=>'foobar']);

        $after = Page::find($page->id);
        $this->assertEquals('foobar',$after->title);

        $this->assertRedirectedToAction('PageController@index');
        $this->assertSessionHas('alert');
    }

    /**
     * @test
     */
    public function update_WhenAuthorised_withAJAX_savesAndReturnsJsonMessage(){
        $page = factory('App\Models\Page')->create();

        $response = $this->ajaxAction('PATCH', 'PageController@update', [$page->id], ['title'=>'foobar']);

        $after = Page::find($page->id);

        $content = $response->getContent();
        $this->assertJson($content);
        $data = json_decode($content, TRUE);

        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertEquals($page->id,$data['page']['id']);
        $this->assertEquals('foobar',$after->title);
    }

    /**
     * @test
     */
    public function create_requiresAuthentication(){
        $this->setUnauthenticatedSession();

        $this->htmlAction('GET', 'PageController@create');
        $this->assertRedirectedToRoute('auth.login');
    }

    /**
     * @test
     */
    public function create_WhenAuthorised_Returns200(){
        $response = $this->htmlAction('GET','PageController@create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function create_WhenAuthorised_SetsPageVariable(){
        $response = $this->htmlAction('GET', 'PageController@create');
        $this->assertViewHas('page');
    }

    /**
     * @test
     */
    public function store_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $response = $this->htmlAction('POST', 'PageController@store',[],['title'=>'foobar']);

        $this->assertRedirectedToRoute('auth.login');
    }

    /**
     * @test
     */
    public function store_WhenAuthorised_savesAndRedirectsToEditWithAlert(){
        $response = $this->htmlAction('POST', 'PageController@store', [], ['title'=>'foobar']);
        $after = Page::all()->last();
        $this->assertEquals('foobar',$after->title);

        $this->assertRedirectedToAction('PageController@edit',[$after->id]);
        $this->assertSessionHas('alert');
        $after->forceDelete();
    }

    /**
     * @test
     */
    public function store_WhenAuthorised_setsSlug(){
        $response = $this->htmlAction('POST', 'PageController@store', [], ['title'=>'FoobAr']);
        $after = Page::all()->last();
        $this->assertEquals('foobar',$after->slug);
        $after->forceDelete();
    }


    /**
     * @test
     */
    public function destroy_requiresAuthentication(){
        $this->setUnauthenticatedSession();
        Session::start(); // setUnathenticatedSession knobbles the session, breaking csrf_token()

        $page = factory('App\Models\Page')->create();

        $this->htmlAction('DELETE', 'PageController@destroy', $page->id);
        $this->assertRedirectedToRoute('auth.login');
    }

    /**
     * @test
     */
    public function destroy_WhenNotFound_Returns404(){
        $response = $this->htmlAction('DELETE', 'PageController@destroy', 123);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorised_deletesAndRedirectsToIndexWithAlert(){
        $page = factory('App\Models\Page')->create();
        $response = $this->htmlAction('DELETE', 'PageController@destroy', $page->id);

        $this->assertNull(Page::find($page->id));
        $this->assertSessionHas('alert');
        $this->assertRedirectedToAction('PageController@index');
    }

    /**
     * @test
     */
    public function destroy_WhenAuthorised_withAJAX_deletesAndReturnsJsonPacket(){
        $page = factory('App\Models\Page')->create();
        $response = $this->ajaxAction('DELETE', 'PageController@destroy', $page->id);

        $this->assertNull(Page::find($page->id));

        $content = $response->getContent();
        $this->assertJson($content);
        $data = json_decode($content, TRUE);

        $this->assertArrayHasKey('redirect_to', $data);
        $this->assertEquals(action('PageController@index'),$data['redirect_to']);
    }
}