<?php

use App\Models\Content;
use App\Http\Requests\ContentSearchRequest;

use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContentSearchRequestTest extends TestCase {

	use DatabaseTransactions;

	protected function setupRequest(){
		$request = new ContentSearchRequest;
		$request->setContainer($this->app);
		return $request;
	}

	protected function setupRequestValidator($attrs, $request = null){
		if(!$request){
			$request = $this->setupRequest();
		}

		$request->query->add($attrs);

		// Use the magic of reflection to make getValidatorInstance accessible
		$reflector = new ReflectionClass('App\Http\Requests\ContentSearchRequest');
		$method = $reflector->getMethod('getValidatorInstance');
		$method->setAccessible(true);

		return $method->invoke($request);
	}

	protected function getAttrs($merge = []){
		$faker = Faker::create();

		$attrs = [
            'search' => $faker->text(255),
            'page' => 1,
		];

		return array_merge($attrs, $merge);
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
	public function search_WhenLongerThan255Chars_IsNotValid(){
		$attrs = self::getAttrs([ 'search' => str_repeat('f', 256) ]);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('search'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function search_WhenNotAString_IsNotValid(){
		$attrs = self::getAttrs([ 'search' => 123 ]);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('search'));
	}


	/**
	 * @test
	 * @group validation
	 */
	public function type_WhenPresent_IsRequired(){
		$attrs = self::getAttrs();
		$attrs['type'] = '';

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('type'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function type_IsOnlyValidWhenWhitelisted(){
		$attrs = self::getAttrs();

		foreach(Content::getTypes() as $type){
			$attrs['type'] = $type;

			$validator = $this->setupRequestValidator($attrs);
			$this->assertCount(0, $validator->errors()->get('type'));
		}

		$attrs['type'] = 'foobar';
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('type'));
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
	public function owner_MustBeAnInteger(){
		$attrs = self::getAttrs([ 'owner_type' => 'leaf' ]);

		$attrs['owner'] = 'foobar';
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('owner'));

		$attrs['owner'] = '10';
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('owner'));

		$attrs['owner'] = 10;
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
}