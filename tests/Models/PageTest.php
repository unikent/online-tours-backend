<?php

use App\Models\Page;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class PageTest extends TestCase {

    use DatabaseTransactions;

    /**
     * @test
     */
    public function fetchOrFail_WithID_WhenFoundReturnsPage(){
        $page = factory('App\Models\Page')->create();
        $result = Page::fetchOrFail($page->id);
        $this->assertEquals($page->id, $result->id);
    }

    /**
     * @test
     */
    public function fetchOrFail_WithID_WhenNotFoundThrowsException(){
        $page = factory('App\Models\Page')->create();
        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');
        Page::fetchOrFail('123');
    }

    /**
     * @test
     */
    public function fetchOrFail_WithSlug_WhenFoundReturnsPage(){
        $page = factory('App\Models\Page')->create();
        $result = Page::fetchOrFail($page->slug);
        $this->assertEquals($page->id, $result->id);
    }

    /**
     * @test
     */
    public function fetchOrFail_WithSlug_WhenNotFoundThrowsException(){
        $page = factory('App\Models\Page')->create();
        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');
        Page::findOrFail('foobar');
    }


    /**
     * @test
     */
    public function delete_DeletesAssociations(){
        $contents = factory('App\Models\Content\Text', 3)->create();

        $page = factory('App\Models\Page')->create();
        $page->contents()->sync($contents->lists('id')->all());

        $page_id = $page->id;

        $this->assertEquals(3, DB::table('content_group')->where('owner_id', $page_id)->where('owner_type', 'App\Models\Page')->count());
        $page->delete();
        $this->assertEquals(0, DB::table('content_group')->where('owner_id', $page_id)->where('owner_type', 'App\Models\Page')->count());
    }

    /**
     * @test
     */
    public function delete_DeletesPage(){
        $page = factory('App\Models\Page')->create();
        $page_id = $page->id;

        $page->delete();
        $this->assertEquals(0, Page::where('id', $page->id)->count());
    }


    /**
     * @test
     */
    public function forceDelete_actuallyDeletes(){
        $page = factory('App\Models\Page')->create();
        $pid = $page->id;
        $page->forceDelete();

        $this->assertNull(Page::withTrashed()->find($pid));
    }
}