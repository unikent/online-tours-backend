<?php

use App\Models\Content;
use App\Models\Content\Image as ImageContent;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Models\Content\Image;

class ImageContentTest extends TestCase {

	use DatabaseTransactions;

	private static $test_image = 'test_1234567890';

    /**
     * @test
     */
    function constructor_setsType(){
        $content = new \App\Models\Content\Image();
        $this->assertEquals('image',$content->type);
    }

    /**
     * @test
     */
    function setType_forcesThisType(){
        $content = new \App\Models\Content\Image();

        $content->type = 'foobar';
        $this->assertEquals('image',$content->type);

        foreach(Content::getTypes() as $type){
            $content->type = $type;
            $this->assertEquals('image',$content->type);
        }
    }

	/**
	 * @test
	 * @group sti
	 */
	function defaultScope_OnlyReturnsImageContent(){
		factory('App\Models\Content', 2)->create();
		factory('App\Models\Content\Image', 5)->create();

		$this->assertEquals(5, ImageContent::count());
	}

	/**
	 * @test
	 * @group sti
	 */
	function getForSearch_AddsDetailToReturnedArray()
	{
		$image = factory('App\Models\Content\Image')->create();
		$out = $image->getForSearch();
		$this->assertArrayHasKey('detail', $out);
	}
	
	/**
	 * @test
	 * @group sti
	 */
	function getForSearch_AddsThumbToReturnedArray()
	{
		$image = factory('App\Models\Content\Image')->create();
		$out = $image->getForSearch();
		$this->assertArrayHasKey('thumb', $out);
	}

	/**
	 * @test
	 * @group sti
	 */
	function refineSearch_RefinesSearchWithValueAndMetaColumns()
	{
		factory('App\Models\Content\Image')->create(['value'=>'something something search term something something']);
		factory('App\Models\Content\Image')->create(['value'=>'search term something something and another something']);
		factory('App\Models\Content\Image')->create(['value'=>'something not really a term but something something']);
		factory('App\Models\Content\Image')->create(['meta'=> ['caption'=>"search term and something else",'copyright'=>"some caption"]]);
		factory('App\Models\Content\Image')->create(['value'=>'something something nothing to search something something']);

		$search = '%search term%';

		$images = ImageContent::where(function($query) use ($search){
			$query = ImageContent::refineSearch($query, $search);
		})->get();

		$this->assertEquals(3, count($images));
	}

	/**
	 * @test
	 * @group sti
	 */
	function toArray_AddsSrcAndFullPathToReturnedArray(){
		$image = factory('App\Models\Content\Image')->create();
		$out = $image->toArray();
		$this->assertArrayHasKey('src', $out);
		$this->assertNotEmpty('src', $out);
		$this->assertArrayHasKey('full', $out['src']);
		$this->assertNotEmpty('full', $out['src']);
	}

	/**
	 * @test
	 * @group sti
	 */
	function getTypeValidationRules_AddsRequiredRulesOnPost(){
		$image = factory('App\Models\Content\Image')->create();

		$rules = $image->getTypeValidationRules('POST');
		$this->assertTrue(strpos($rules['caption'], 'required') === 0);
		$this->assertTrue(strpos($rules['copyright'], 'required') === 0);
		$this->assertTrue(strpos($rules['img'], 'required') === 0);
	}

	/**
	 * @test
	 * @group sti
	 */
	function getTypeValidationRules_AddsRequiredRulesOnPut(){
		$image = factory('App\Models\Content\Image')->create();

		$rules = $image->getTypeValidationRules('PUT');
		$this->assertTrue(strpos($rules['caption'], 'required') === 0);
		$this->assertTrue(strpos($rules['copyright'], 'required') === 0);
		$this->assertTrue(strpos($rules['img'], 'required') === 0);
	}

	/**
	 * @test
	 * @group sti
	 */
	function getTypeValidationRules_DoesNotAddRequiredRulesOnPatch(){
		$image = factory('App\Models\Content\Image')->create();

		$rules = $image->getTypeValidationRules('PATCH');
		$this->assertTrue(strpos($rules['caption'], 'required') === FALSE);
		$this->assertTrue(strpos($rules['copyright'], 'required') === FALSE);
		$this->assertTrue(strpos($rules['img'], 'required') === FALSE);
	}

	/**
	 * @test
	 * @group sti
	 */
	function getPath_ReturnsFalseForInvalidSize(){
		$image = factory('App\Models\Content\Image')->create();
		foreach (['big', 'huge', 'tiny', 'mid', 'smallerthansmall', ';-P'] as $size) {
			$path = $image->getPath($size);
			$this->assertFalse($path);
		}
	}

	/**
	 * @test
	 * @group sti
	 */
	function getPath_GetsRightPathForFull(){
		$image_name = 'img.jpg';
		$image = factory('App\Models\Content\Image')->create(['value' => $image_name]);
		
		$path = $image->getPath('full');

		$this->assertTrue(($temp = strlen($path) - strlen($image_name)) >= 0 && strpos($path, $image_name, $temp) !== FALSE);
	}

	/**
	 * @test
	 * @group sti
	 */
	function getPath_GetsRightPathForValidSize(){
		$image_name = 'img.jpg';
		$image = factory('App\Models\Content\Image')->create(['value' => $image_name]);
		
		foreach ([
			'medium' => 'img_medium.jpg', 
			'thumb' => 'img_thumb.jpg',
			'largethumb' => 'img_largethumb.jpg',
			'large' => 'img_large.jpg'
		] as $size => $size_image_name) {
			$path = $image->getPath($size);
			$this->assertTrue(($temp = strlen($path) - strlen($size_image_name)) >= 0 && strpos($path, $size_image_name, $temp) !== FALSE);
		}
		
	}


	/**
	 * @test
	 * @group sti
	 */
	function getUri_ReturnsFalseForInvalidSize(){
		$image = factory('App\Models\Content\Image')->create();
		foreach (['big', 'huge', 'tiny', 'mid', 'smallerthansmall', ';-P'] as $size) {
			$path = $image->getUri($size);
			$this->assertFalse($path);
		}
	}

	/**
	 * @test
	 * @group sti
	 */
	function getUri_GetsRightUriForFull(){
		$image_name = 'img.jpg';
		$image = factory('App\Models\Content\Image')->create(['value' => $image_name]);
		
		$uri = $image->getUri('full');

		$this->assertTrue(($temp = strlen($uri) - strlen($image_name)) >= 0 && strpos($uri, $image_name, $temp) !== FALSE);
	}

	/**
	 * @test
	 * @group sti
	 */
	function getUri_GetsRightUriForValidSize(){
		$image_name = 'img.jpg';
		$image = factory('App\Models\Content\Image')->create(['value' => $image_name]);
		
		foreach ([
			'medium' => 'img_medium.jpg', 
			'thumb' => 'img_thumb.jpg',
			'largethumb' => 'img_largethumb.jpg',
			'large' => 'img_large.jpg'
		] as $size => $size_image_name) {
			$uri = $image->getUri($size);
			$this->assertTrue(($temp = strlen($uri) - strlen($size_image_name)) >= 0 && strpos($uri, $size_image_name, $temp) !== FALSE);
		}
		
	}


	/**
	 * @test
	 * @group uploads
	 */
	function setImgAttribute_removesExistingImageFiles(){

		$image = $image = factory('App\Models\Content\Image')->create(['value'=>'existingImg.jpg']);


		//clearup incase test previously failed
		$oldFiles = glob(Image::getMediaPath().'/existingImage_*');
		File::delete($oldFiles);

		//create exiting images that should be removed
		copy(Image::getMediaPath() . '/' .self::$test_image . '.jpg' ,Image::getMediaPath() . '/existingImg.jpg');
		foreach(array_keys(Config::get('image.sizes')) as $size){
			copy(Image::getMediaPath() . '/' .self::$test_image . '_' . $size . '.jpg' ,Image::getMediaPath() . '/existingImg_' . $size  . '.jpg');
		}

		$path = Image::getMediaPath() . '/' . self::$test_image . '.jpg';

		$file = new UploadedFile(
			$path,
			'newImage.jpg',
			'image/jpg',
			filesize($path),
			UPLOAD_ERR_OK,
			true
		);

		$image->img = $file;

		$oldFiles = glob(Image::getMediaPath().'/existingImage_*');

		$this->assertCount(0,$oldFiles);

		$newFiles = glob(Image::getMediaPath().'/newImage_*');
		File::delete($newFiles);
	}


	/**
	 * @test
	 * @group uploads
	 */
	function setImgAttribute_savesAllImageSizes(){

		$path = Image::getMediaPath() . '/' . self::$test_image . '.jpg';

		$file = new UploadedFile(
			$path,
			'newImage.jpg',
			'image/jpg',
			filesize($path),
			UPLOAD_ERR_OK,
			true
		);

		$image = $image = factory('App\Models\Content\Image')->create(['value'=>'']);

		$image->img = $file;

		$this->assertRegExp('/newImage_/',$image->value);
		$this->assertEquals(1960,$image->width);
		$this->assertEquals(1307,$image->height);

		$newFiles = glob(Image::getMediaPath().'/newImage_*');

		$this->assertCount(count(Config::get('image.sizes'))+1,$newFiles);

		foreach(array_keys(Config::get('image.sizes')) as $size){
			$this->assertCount(1,preg_grep('/.*_' . $size . '\.jpg$/',$newFiles));
		}

		File::delete($newFiles);

	}

}