<?php

use App\Models\Content;
use App\Models\Content\Text as TextContent;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class TextContentTest extends TestCase {

	use DatabaseTransactions;


	/**
	 * @test
	 */
	function constructor_setsType(){
		$content = new \App\Models\Content\Text();
		$this->assertEquals('text',$content->type);
	}


	/**
	 * @test
	 */
	function setType_forcesThisType(){
		$content = new \App\Models\Content\Text();

		$content->type = 'foobar';
		$this->assertEquals('text',$content->type);

		foreach(Content::getTypes() as $type){
			$content->type = $type;
			$this->assertEquals('text',$content->type);
		}
	}


	/**
	 * @test
	 * @group sti
	 */
	function defaultScope_OnlyReturnsImageContent(){
		factory('App\Models\Content\Image', 2)->create();
		factory('App\Models\Content\Text', 5)->create();

		$this->assertEquals(5, TextContent::count());
	}

	/**
	 * @test
	 * @group sti
	 */
	function getForSearch_AddsDetailToReturnedArray()
	{
		$text = factory('App\Models\Content\Text')->create();
		$out = $text->getForSearch();
		$this->assertArrayHasKey('detail', $out);
	}

		/**
	 * @test
	 * @group sti
	 */
	function refineSearch_RefinesSearchWithValueColumn()
	{
		factory('App\Models\Content\Text')->create(['value'=>'something something search term something something']);
		factory('App\Models\Content\Text')->create(['value'=>'search term something something and another something']);
		factory('App\Models\Content\Text')->create(['value'=>'something not really a term but something something']);
		factory('App\Models\Content\Text')->create(['meta'=> ['title'=>"search term and something else",'caption'=>"some caption"]]);
		factory('App\Models\Content\Text')->create(['value'=>'something something nothing to search something something']);

		$search = '%search term%';

		$texts = TextContent::where(function($query) use ($search){
			$query = TextContent::refineSearch($query, $search);
		})->get();

		$this->assertEquals(2, count($texts));
	}


}