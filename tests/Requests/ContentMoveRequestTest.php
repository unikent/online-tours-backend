<?php

use App\Models\Content;
use Faker\Factory as Faker;
use App\Http\Requests\ContentMoveRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContentMoveRequestTest extends TestCase {

	use DatabaseTransactions;

	protected function setupRequest(){
		$request = new ContentMoveRequest;
		$request->setContainer($this->app);
		return $request;
	}

	protected function setupRequestValidator($attrs){
		$request = $this->setupRequest();
		$request->query->add($attrs);

		// Use the magic of reflection to make getValidatorInstance accessible
		$reflector = new ReflectionClass('App\Http\Requests\ContentMoveRequest');
		$method = $reflector->getMethod('getValidatorInstance');
		$method->setAccessible(true);

		return $method->invoke($request);
	}

	protected function getAttrs($merge = []){
		$faker = Faker::create();

		$attrs = [
			'content' => [ 1, 2, 3 ],
			'owner' => 10,
			'owner_type' => $faker->randomElement([ 'leaf', 'tour', 'page' ]),
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
	public function content_IsRequired(){
		$attrs = self::getAttrs();
		unset($attrs['content']);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('content'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function content_IsOnlyValidWhenArray(){
		$attrs = self::getAttrs([ 'content' => '123' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('content'));

		$attrs = self::getAttrs([ 'content' => 123 ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('content'));
	}


	/**
	 * @test
	 * @group validation
	 */
	public function owner_IsRequired(){
		$attrs = self::getAttrs();
		unset($attrs['owner']);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('owner'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function owner_IsOnlyValidWhenInteger(){
		$attrs = self::getAttrs([ 'owner' => 'abc' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('owner'));

		$attrs = self::getAttrs([ 'owner' => [ 123 ] ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('owner'));
	}


	/**
	 * @test
	 * @group validation
	 */
	public function ownerType_IsRequired(){
		$attrs = self::getAttrs();
		unset($attrs['owner_type']);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('owner_type'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function ownerType_WhenValueIsWhitelisted_IsValid(){
		$attrs = self::getAttrs([ 'owner_type' => 'leaf' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('owner_type'));

		$attrs = self::getAttrs([ 'owner_type' => 'tour' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('owner_type'));

		$attrs = self::getAttrs([ 'owner_type' => 'page' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('owner_type'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function ownerType_WhenValueIsNotWhitelisted_IsNotValid(){
		$attrs = self::getAttrs([ 'owner_type' => 123 ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('owner_type'));

		$attrs = self::getAttrs([ 'owner_type' => '123' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('owner_type'));
	}

}