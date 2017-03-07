<?php

use App\Models\Content;
use App\Http\Requests\ContentPersistRequest;

use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContentPersistRequestTest extends TestCase {

	use DatabaseTransactions;

	protected function setupRequest(){

        $mock = Mockery::mock(new stdClass())->shouldReceive('parameters')->andReturn(['content'=>1])->getMock();
        Route::shouldReceive('current')->andReturn($mock);

		$request = Mockery::mock("App\Http\Requests\ContentPersistRequest[isMethod,method,getMethod]");

		$request->shouldReceive('isMethod')->andReturn(Input::method() === 'POST');
		$request->shouldReceive('method')->andReturn(Input::method());
		$request->shouldReceive('getMethod')->andReturn(Input::method());

		$request->setContainer($this->app);

		return $request;
	}

	protected function setupRequestValidator($attrs, $request = null){

		if(!$request){
			$request = $this->setupRequest();
		}

		// input seems to need this to be set in again explicity
		if(Input::method() === 'POST'){
			$request->request->replace($attrs);
		}else{
			$request->query->replace($attrs);
		}

		// Use the magic of reflection to make getValidatorInstance accessible
		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		$method = $reflector->getMethod('getValidatorInstance');
		$method->setAccessible(true);

		return $method->invoke($request);
	}

	protected function getAttrs($merge = [], $method = 'POST'){

		Input::setMethod($method);

		$faker = Faker::create();

		$attrs = [
            'name' => $faker->text(255),
            'value' => $faker->word(),
            'type' => 'text'
		];

		$payload = array_merge($attrs, $merge);

		// kill type, if requested
		if(isset($merge['type']) && $merge['type'] == false){
			unset($payload['type']);
		}

		// set facade to use same values
		Input::replace($payload);

		return $payload;
	}

	protected function fakeRequestParameters($params = array()){

		$req_p = Mockery::mock();
		$req_p->shouldReceive('parameters')->andReturn($params);
		Route::shouldReceive('current')->andReturn($req_p);	
	}

	/**
	 * @test
	 * @group validation
	 */
	public function isValidWithValidAttributes(){

		$attrs = self::getAttrs();
		
		$validator = $this->setupRequestValidator($attrs);

		$this->assertEmpty($validator->errors());
	}

	/**
	 * @test
	 * @group validation
	 */
	public function name_IsRequired(){
		$attrs = self::getAttrs();
		unset($attrs['name']);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('name'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function name_LongerThan255Chars_IsNotValid(){
		$attrs = self::getAttrs([ 'name' => str_repeat('f', 256) ]);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('name'));
	}


	/**
	 * @test
	 * @group validation
	 */
	public function value_IsRequired(){
		$attrs = self::getAttrs();
		unset($attrs['value']);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('value'));
	}


	/**
	 * @test
	 * @group validation
	 */
	public function owner_IsRequiredWhenOwnerTypeIsPresent(){
		$attrs = self::getAttrs([ 'owner_type' => 'leaf' ]);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('owner'));

		$attrs['owner'] = '10';
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('owner'));
	}


	/**
	 * @test
	 * @group validation
	 */
	public function ownerType_IsRequiredWhenOwnerIsPresent(){
		$attrs = self::getAttrs([ 'owner' => 10 ]);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('owner_type'));

		$attrs['owner_type'] = 'leaf';
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('owner_type'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function ownerType_IsOnlyValidWhenWhitelisted(){
		$attrs = self::getAttrs([ 'owner' => 10 ]);

		$attrs['owner_type'] = 'leaf';
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('owner_type'));

		$attrs['owner_type'] = 'tour';
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('owner_type'));

		$attrs['owner_type'] = 'page';
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('owner_type'));

		$attrs['owner_type'] = 'foobar';
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('owner_type'));
	}


	/**
	 * @test
	 * @group validation
	 */
	public function type_OnStore_IsRequired(){
		$attrs = self::getAttrs(array('type'=>false));

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('type'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function rules_OnStore_TextRules(){

		$attrs = self::getAttrs();
		$request = $this->setupRequest('POST');
		$request->query->add($attrs);
				
		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		// get rules
		$method = $reflector->getMethod('rules');
		$method->setAccessible(true);
				
		$rules_returned = $method->invoke($request);
		$text_rules = App\Models\Content\Text::getValidationRules('POST');

		$this->assertEquals($rules_returned, $text_rules);
	}
	/**
	 * @test
	 * @group validation
	 */
	public function rules_OnStore_ImageRules(){

		$attrs = self::getAttrs(array('type'=>'image'));
		$request = $this->setupRequest();
		$request->query->add($attrs);

		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		// get rules
		$method = $reflector->getMethod('rules');
		$method->setAccessible(true);
		
		$rules_returned = $method->invoke($request);
		$text_rules = App\Models\Content\Image::getValidationRules('POST');

		$this->assertEquals($rules_returned, $text_rules);
	}

	/**
	 * @test
	 * @group validation
	 */
	public function rules_OnUpdate_ImageRules(){

		$attrs = self::getAttrs(array('type'=>'image'),'PATCH');
		$request = $this->setupRequest();
		$request->query->add($attrs);

		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		// get rules
		$method = $reflector->getMethod('rules');
		$method->setAccessible(true);
		
		$rules_returned = $method->invoke($request);
		$text_rules = App\Models\Content\Image::getValidationRules('PATCH');

		$this->assertEquals($rules_returned, $text_rules);
	}

	/**
	 * @test
	 * @group validation
	 */
	public function rules_OnStore_AudioRules(){

		$attrs = self::getAttrs(array('type'=>'audio'));
		$request = $this->setupRequest();
		$request->query->add($attrs);

		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		// get rules
		$method = $reflector->getMethod('rules');
		$method->setAccessible(true);
		
		$rules_returned = $method->invoke($request);
		$text_rules = App\Models\Content\Audio::getValidationRules('POST');

		$this->assertEquals($rules_returned, $text_rules);
	}


	/**
	 * @test
	 * @group validation
	 */
	public function rules_OnStore_VideoRules(){

		$attrs = self::getAttrs(array('type'=>'video'));
		$request = $this->setupRequest();
		$request->query->add($attrs);

		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		// get rules
		$method = $reflector->getMethod('rules');
		$method->setAccessible(true);
		
		$rules_returned = $method->invoke($request);
		$text_rules = App\Models\Content\Video::getValidationRules('POST');

		$this->assertEquals($rules_returned, $text_rules);
	}

	/**
	 * @test
	 * @group validation
	 */
	public function getAllowedInput_GET_CheckInvalidFieldIsRemoved(){

		$attrs = self::getAttrs(['wafflehawk'=> '1'], 'POST' );
		$request = $this->setupRequest();

		$input = Input::all();
		$this->assertArrayNotHasKey('wafflehawk', $input);
	}


	/**
	 * @test
	 * @group validation
	 */
	public function construct_POST_CheckStrippedFieldIsStripped(){
		// type image = name is stipped
		$attrs = self::getAttrs(['name'=> '<strong>Llamabacon</strong>', 'type' => 'image'], 'POST' );
		$request = $this->setupRequest();

		$this->assertEquals('Llamabacon', Input::get('name'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function construct_POST_CheckStrippedFieldIsSanitised(){
		// type text = value is sanitised
		$attrs = self::getAttrs(['value'=> '<div><b>Llamabacon</b></div>', 'type' => 'text'], 'POST' );
		$request = $this->setupRequest();
		// b -> strong, div -> removed
		$this->assertEquals('<strong>Llamabacon</strong>', Input::get('value'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function type_OnStore_IsOnlyValidWhenWhitelisted(){
		
		$attrs = self::getAttrs();

        $mock = Mockery::mock(new stdClass())->shouldReceive('parameters')->andReturn(['content'=>1])->getMock();
        Route::shouldReceive('current')->andReturn($mock);
		$request = Mockery::mock('App\Http\Requests\ContentPersistRequest[method,getMethod]');
		$request->shouldReceive('method')->andReturn('POST');
		$request->shouldReceive('getMethod')->andReturn('POST');
		$request->setContainer($this->app);

		foreach(Content::getTypes() as $type){
		
			$attrs['type'] = $type;
			$validator = $this->setupRequestValidator($attrs, clone $request);
			$this->assertCount(0, $validator->errors()->get('type'));
		}

		$attrs['type'] = 'foobar';
		$validator = $this->setupRequestValidator($attrs, clone $request);
		$this->assertCount(1, $validator->errors()->get('type'));
	}

	/**
	 * @test
	 * @group sanitization
	 */
	public function stripTagsContent_RemovesSpecifiedTagsAndTheirContents(){

		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		$method = $reflector->getMethod('strip_tags_content');
		$method->setAccessible(true);

		$this->assertEquals('foobar',$method->invoke(null,'foo<script>abc123</script><style>body{background:black;}</style>bar','<script><style>'));

	}

	/**
	 * @test
	 * @group sanitization
	 */
	public function stripTagsContent_PreservesOtherTagsAndTheirContents(){

		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		$method = $reflector->getMethod('strip_tags_content');
		$method->setAccessible(true);

		$this->assertEquals('foo<b>OOO</b>bar',$method->invoke(null,'foo<script>abc123</script><b>OOO</b><style>body{background:black;}</style>bar','<script><style>'));

	}

	/**
	 * @test
	 * @group sanitization
	 */
	public function stripTagsContent_DoesNothingIfNoTagsSpecified(){

		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		$method = $reflector->getMethod('strip_tags_content');
		$method->setAccessible(true);

		$this->assertEquals('foo<script>abc123</script><style>body{background:black;}</style>bar',$method->invoke(null,'foo<script>abc123</script><style>body{background:black;}</style>bar',''));

	}

	/**
	 * @test
	 * @group sanitization
	 */
	public function stripHtml_RemovesScriptandStyleTagsAndTheirContentsAndStripsAllOtherTags(){

		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		$method = $reflector->getMethod('strip_html');
		$method->setAccessible(true);

		$this->assertEquals('fooOOObar',$method->invoke(null,'<p>foo<script>abc123</script><b>OOO</b><style>body{background:black;}</style>bar</p>'));

	}

	/**
	 * @test
	 * @group sanitization
	 */
	public function sanitizeHtml_RemovesScriptandStyleTagsAndTheirContentsAndStripsAllButAllowedTags(){

		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		$method = $reflector->getMethod('sanitize_html');
		$method->setAccessible(true);

		$this->assertEquals('foo<strong>OOO</strong>bar',$method->invoke(null,'<table><tr><td>foo<script>abc123</script><strong>OOO</strong><style>body{background:black;}</style>bar</td></tr></table>'));

	}

	/**
	 * @test
	 * @group sanitization
	 */
	public function sanitizeHtml_ReplacesSpecifiedTags(){

		$reflector = new ReflectionClass('App\Http\Requests\ContentPersistRequest');
		$method = $reflector->getMethod('sanitize_html');
		$method->setAccessible(true);

		$this->assertEquals('<h3>foo<strong>OOO</strong><em>bar</em></h3>',$method->invoke(null,'<h1>foo<b>OOO</b><i>bar</i></h1>'));

	}
}