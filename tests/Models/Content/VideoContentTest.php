<?php

use App\Models\Content;
use App\Models\Content\Video as VideoContent;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class VideoContentTest extends TestCase {

	use DatabaseTransactions;

	/**
	 * @test
	 * @group sti
	 */
	function defaultScope_OnlyReturnsImageContent(){
		factory('App\Models\Content', 2)->create();
		factory('App\Models\Content\Video', 5)->create();

		$this->assertEquals(5, VideoContent::count());
	}

	/**
	 * @test
	 * @group sti
	 */
	function getTypeValidationRules_AddsRequiredRulesOnPost(){
		$video = factory('App\Models\Content\Video')->create();

		$rules = $video->getTypeValidationRules('POST');
		$this->assertTrue(strpos($rules['value'], 'required') === 0);
	}

	/**
	 * @test
	 * @group sti
	 */
	function getTypeValidationRules_AddsRequiredRulesOnPut(){
		$video = factory('App\Models\Content\Video')->create();

		$rules = $video->getTypeValidationRules('PUT');
		$this->assertTrue(strpos($rules['value'], 'required') === 0);
	}

	/**
	 * @test
	 * @group sti
	 */
	function getTypeValidationRules_DoesNotAddRequiredRulesOnPatch(){
		$video = factory('App\Models\Content\Video')->create();

		$rules = $video->getTypeValidationRules('PATCH');
		$this->assertTrue(strpos($rules['value'], 'required') === FALSE);
	}

}