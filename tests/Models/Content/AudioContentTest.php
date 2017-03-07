<?php

use App\Models\Content;
use App\Models\Content\Audio as AudioContent;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class AudioContentTest extends TestCase {

	use DatabaseTransactions;


	/**
	 * @test
	 */
	function constructor_setsType(){
		$content = new \App\Models\Content\Audio();
		$this->assertEquals('audio',$content->type);
	}

	/**
	 * @test
	 */
	function setType_forcesThisType(){
		$content = new \App\Models\Content\Audio();

		$content->type = 'foobar';
		$this->assertEquals('audio',$content->type);

		foreach(Content::getTypes() as $type){
			$content->type = $type;
			$this->assertEquals('audio',$content->type);
		}
	}

	/**
	 * @test
	 * @group sti
	 */
	function defaultScope_OnlyReturnsAudioContent(){
		factory('App\Models\Content', 2)->create();
		factory('App\Models\Content\Audio', 5)->create();

		$this->assertEquals(5, AudioContent::count());
	}


	/**
	 * @test
	 * @group sti
	 */
	function getForSearch_AddsDetailToReturnedArray()
	{
		$audio = factory('App\Models\Content\Audio')->create();
		$out = $audio->getForSearch();
		$this->assertArrayHasKey('detail', $out);
	}

	/**
	 * @test
	 * @group sti
	 */
	function refineSearch_RefinesSearchWithValueAndMetaColumns()
	{
		factory('App\Models\Content\Audio')->create(['value'=>'something something search term something something']);
		factory('App\Models\Content\Audio')->create(['value'=>'search term something something and another something']);
		factory('App\Models\Content\Audio')->create(['value'=>'something not really a term but something something']);
		factory('App\Models\Content\Audio')->create(['meta'=> ['title'=>"search term and something else",'transcription'=>"some caption"]]);
		factory('App\Models\Content\Audio')->create(['value'=>'something something nothing to search something something']);

		$search = '%search term%';

		$audios = AudioContent::where(function($query) use ($search){
			$query = AudioContent::refineSearch($query, $search);
		})->get();

		$this->assertEquals(3, count($audios));
	}

	/**
	 * @test
	 * @group sti
	 */
	function toArray_AddsSrcToReturnedArray(){
		$audio = factory('App\Models\Content\Audio')->create();
		$out = $audio->toArray();
		$this->assertArrayHasKey('src', $out);
	}

	/**
	 * @test
	 * @group sti
	 */
	function getTypeValidationRules_AddsRequiredRulesOnPost(){
		$audio = factory('App\Models\Content\Audio')->create();

		$rules = $audio->getTypeValidationRules('POST');
		$this->assertTrue(strpos($rules['title'], 'required') === 0);
		$this->assertTrue(strpos($rules['mp3'], 'required') === 0);
	}

	/**
	 * @test
	 * @group sti
	 */
	function getTypeValidationRules_AddsRequiredRulesOnPut(){
		$audio = factory('App\Models\Content\Audio')->create();

		$rules = $audio->getTypeValidationRules('PUT');
		$this->assertTrue(strpos($rules['title'], 'required') === 0);
		$this->assertTrue(strpos($rules['mp3'], 'required') === 0);
	}

	/**
	 * @test
	 * @group sti
	 */
	function getTypeValidationRules_DoesNotAddRequiredRulesOnPatch(){
		$audio = factory('App\Models\Content\Audio')->create();

		$rules = $audio->getTypeValidationRules('PATCH');
		$this->assertTrue(strpos($rules['title'], 'required') === FALSE);
		$this->assertTrue(strpos($rules['mp3'], 'required') === FALSE);
	}

	/**
	 * @test
	 * @group uploads
	 */
	function setMp3Attribute_SavesAnUploadedMp3File()
	{
		$this->cleanup_test_file('mp3-file-to-upload', 'mp3');

		copy(AudioContent::getMediaPath() . '/eliot_1234567890.mp3', AudioContent::getMediaPath() . '/tmp-mp3-file-to-upload.tmp');
		$audio = factory('App\Models\Content\Audio')->create(['value'=>'']);
		$file = new UploadedFile(
			AudioContent::getMediaPath() . '/tmp-mp3-file-to-upload.tmp', 'mp3-file-to-upload.mp3', 'audio/mp3', filesize(AudioContent::getMediaPath() . '/tmp-mp3-file-to-upload.tmp'), UPLOAD_ERR_OK, true
		);
		$audio->setMp3Attribute($file);
		$this->assertTrue(count(glob(AudioContent::getMediaPath() . '/mp3-file-to-upload[0-9]*.mp3')) === 1);
		$this->assertTrue(preg_match("/mp3-file-to-upload[0-9]*.mp3/", $audio->value) === 1);

		$this->cleanup_test_file('mp3-file-to-upload', 'mp3');

	}

	/**
	 * @test
	 * @group uploads
	 */
	function setMp3Attribute_RemovesExistingFile()
	{
		$this->cleanup_test_file('existing', 'mp3');
		$this->cleanup_test_file('mp3-file-to-upload', 'mp3');

		copy(AudioContent::getMediaPath() . '/eliot_1234567890.mp3', AudioContent::getMediaPath() . '/existing.mp3');
		$audio = factory('App\Models\Content\Audio')->create(['value'=>'existing.mp3']);

		copy(AudioContent::getMediaPath() . '/eliot_1234567890.mp3', AudioContent::getMediaPath() . '/tmp-mp3-file-to-upload.tmp');
		$file = new UploadedFile(
			AudioContent::getMediaPath() . '/tmp-mp3-file-to-upload.tmp', 'mp3-file-to-upload.mp3', 'audio/mp3', filesize(AudioContent::getMediaPath() . '/tmp-mp3-file-to-upload.tmp'), UPLOAD_ERR_OK, true
		);
		$audio->setMp3Attribute($file);

		$this->assertFalse(file_exists(AudioContent::getMediaPath() . '/existing.mp3'));

		$this->cleanup_test_file('existing', 'mp3');
		$this->cleanup_test_file('mp3-file-to-upload2', 'mp3');
	}

	/**
	 * @test
	 * @group uploads
	 */
	function setOggAttribute_SavesAnUploadedOggFile()
	{
		$this->cleanup_test_file('ogg-file-to-upload', 'ogg');

		copy(AudioContent::getMediaPath() . '/eliot_1234567890.ogg', AudioContent::getMediaPath() . '/tmp_ogg-file-to-upload.tmp');
		$audio = factory('App\Models\Content\Audio')->create(['value'=>'']);
		$file = new UploadedFile(
			AudioContent::getMediaPath() . '/tmp_ogg-file-to-upload.tmp', 'ogg-file-to-upload.ogg', 'audio/ogg', filesize(AudioContent::getMediaPath() . '/tmp_ogg-file-to-upload.tmp'), UPLOAD_ERR_OK, true
		);
		$audio->setOggAttribute($file);
		$this->assertTrue(count(glob(AudioContent::getMediaPath() . '/ogg-file-to-upload[0-9]*.ogg')) === 1);
		$this->assertTrue(preg_match("/ogg-file-to-upload[0-9]*.ogg/", $audio->variants['ogg']) === 1);

		$this->cleanup_test_file('ogg-file-to-upload', 'ogg');

	}

	/**
	 * @test
	 * @group uploads
	 */
	function setOggAttribute_RemovesExistingFile()
	{
		$this->cleanup_test_file('existing', 'ogg');
		$this->cleanup_test_file('ogg-file-to-upload', 'ogg');

		copy(AudioContent::getMediaPath() . '/eliot_1234567890.ogg', AudioContent::getMediaPath() . '/existing.ogg');
		$audio = factory('App\Models\Content\Audio')->create(['meta'=>['variants'=>['ogg'=>'existing.ogg']]]);

		copy(AudioContent::getMediaPath() . '/eliot_1234567890.ogg', AudioContent::getMediaPath() . '/tmp_ogg-file-to-upload.tmp');
		$file = new UploadedFile(
			AudioContent::getMediaPath() . '/tmp_ogg-file-to-upload.tmp', 'ogg-file-to-upload.ogg', 'audio/ogg', filesize(AudioContent::getMediaPath() . '/tmp_ogg-file-to-upload.tmp'), UPLOAD_ERR_OK, true
		);
		$audio->setOggAttribute($file);

		$this->assertFalse(file_exists(AudioContent::getMediaPath() . '/existing.ogg'));

		$this->cleanup_test_file('existing', 'ogg');
		$this->cleanup_test_file('ogg-file-to-upload2', 'ogg');
	}

		/**
	 * @test
	 * @group uploads
	 */
	function setWavAttribute_SavesAnUploadedWavFile()
	{
		$this->cleanup_test_file('wav-file-to-upload', 'wav');

		copy(AudioContent::getMediaPath() . '/eliot_1234567890.wav', AudioContent::getMediaPath() . '/tmp_wav-file-to-upload.tmp');
		$audio = factory('App\Models\Content\Audio')->create(['value'=>'']);
		$file = new UploadedFile(
			AudioContent::getMediaPath() . '/tmp_wav-file-to-upload.tmp', 'wav-file-to-upload.wav', 'audio/wav', filesize(AudioContent::getMediaPath() . '/tmp_wav-file-to-upload.tmp'), UPLOAD_ERR_OK, true
		);
		$audio->setWavAttribute($file);
		$this->assertTrue(count(glob(AudioContent::getMediaPath() . '/wav-file-to-upload[0-9]*.wav')) === 1);
		$this->assertTrue(preg_match("/wav-file-to-upload[0-9]*.wav/", $audio->variants['wav']) === 1);

		$this->cleanup_test_file('wav-file-to-upload', 'wav');

	}

	/**
	 * @test
	 * @group uploads
	 */
	function setWavAttribute_RemovesExistingFile()
	{
		$this->cleanup_test_file('existing', 'wav');
		$this->cleanup_test_file('wav-file-to-upload', 'wav');

		copy(AudioContent::getMediaPath() . '/eliot_1234567890.wav', AudioContent::getMediaPath() . '/existing.wav');
		$audio = factory('App\Models\Content\Audio')->create(['meta'=>['variants'=>['wav'=>'existing.wav']]]);

		copy(AudioContent::getMediaPath() . '/eliot_1234567890.wav', AudioContent::getMediaPath() . '/tmp_wav-file-to-upload.tmp');
		$file = new UploadedFile(
			AudioContent::getMediaPath() . '/tmp_wav-file-to-upload.tmp', 'wav-file-to-upload.wav', 'audio/wav', filesize(AudioContent::getMediaPath() . '/tmp_wav-file-to-upload.tmp'), UPLOAD_ERR_OK, true
		);
		$audio->setWavAttribute($file);

		$this->assertFalse(file_exists(AudioContent::getMediaPath() . '/existing.wav'));

		$this->cleanup_test_file('existing', 'wav');
		$this->cleanup_test_file('wav-file-to-upload2', 'wav');
	}

	/**
	 * @test
	 * @group uploads
	 */
	function testAllAttributesCanBeSet(){
		$this->cleanup_test_file('mp3-file-to-upload', 'mp3');
		$this->cleanup_test_file('ogg-file-to-upload', 'ogg');
		$this->cleanup_test_file('wav-file-to-upload', 'wav');

		copy(AudioContent::getMediaPath() . '/eliot_1234567890.mp3', AudioContent::getMediaPath() . '/tmp_mp3-file-to-upload.tmp');
		copy(AudioContent::getMediaPath() . '/eliot_1234567890.ogg', AudioContent::getMediaPath() . '/tmp_ogg-file-to-upload.tmp');
		copy(AudioContent::getMediaPath() . '/eliot_1234567890.wav', AudioContent::getMediaPath() . '/tmp_wav-file-to-upload.tmp');
		
		$audio = factory('App\Models\Content\Audio')->create(['value'=>'']);
		
		$audio->setMp3Attribute(new UploadedFile(
			AudioContent::getMediaPath() . '/tmp_mp3-file-to-upload.tmp', 'mp3-file-to-upload.mp3', 'audio/mp3', filesize(AudioContent::getMediaPath() . '/tmp_mp3-file-to-upload.tmp'), UPLOAD_ERR_OK, true
		));

		$audio->setOggAttribute(new UploadedFile(
			AudioContent::getMediaPath() . '/tmp_ogg-file-to-upload.tmp', 'ogg-file-to-upload.ogg', 'audio/ogg', filesize(AudioContent::getMediaPath() . '/tmp_ogg-file-to-upload.tmp'), UPLOAD_ERR_OK, true
		));

		$audio->setWavAttribute(new UploadedFile(
			AudioContent::getMediaPath() . '/tmp_wav-file-to-upload.tmp', 'wav-file-to-upload.wav', 'audio/wav', filesize(AudioContent::getMediaPath() . '/tmp_wav-file-to-upload.tmp'), UPLOAD_ERR_OK, true
		));

		$this->assertTrue(count(glob(AudioContent::getMediaPath() . '/mp3-file-to-upload[0-9]*.mp3')) === 1);
		$this->assertTrue(preg_match("/mp3-file-to-upload[0-9]*.mp3/", $audio->value) === 1);
		$this->assertTrue(count(glob(AudioContent::getMediaPath() . '/ogg-file-to-upload[0-9]*.ogg')) === 1);
		$this->assertTrue(preg_match("/ogg-file-to-upload[0-9]*.ogg/", $audio->variants['ogg']) === 1);
		$this->assertTrue(count(glob(AudioContent::getMediaPath() . '/wav-file-to-upload[0-9]*.wav')) === 1);
		$this->assertTrue(preg_match("/wav-file-to-upload[0-9]*.wav/", $audio->variants['wav']) === 1);

		$this->cleanup_test_file('mp3-file-to-upload', 'mp3');
		$this->cleanup_test_file('ogg-file-to-upload', 'ogg');
		$this->cleanup_test_file('wav-file-to-upload', 'wav');
	}

	function cleanup_test_file($filename='', $type='mp3')
	{
		if (!empty($filename)) {

			if (file_exists(AudioContent::getMediaPath() . '/' . $filename. '.' . $type)) {
				unlink(AudioContent::getMediaPath() . '/' . $filename. '.' . $type);
			}

			if (file_exists(AudioContent::getMediaPath() . '/tmp_'.$filename.'.tmp')) {
				unlink(AudioContent::getMediaPath() . '/tmp_'.$filename.'.tmp');
			}

			File::delete(glob(AudioContent::getMediaPath() . '/'.$filename.'[0-9]*.'.$type));
		}
	}

}