<?php

use App\Models\Content;
use App\Http\Requests\LocationPersistRequest;

use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LocationPersistRequestTest extends TestCase {

	use DatabaseTransactions;

	protected function setupRequest(){
		$request = new LocationPersistRequest;
		$request->setContainer($this->app);
		return $request;
	}

	protected function setupRequestValidator($attrs, $request = null){
		if(!$request){
			$request = $this->setupRequest();
		}

		$request->query->add($attrs);

		// Use the magic of reflection to make getValidatorInstance accessible
		$reflector = new ReflectionClass('App\Http\Requests\LocationPersistRequest');
		$method = $reflector->getMethod('getValidatorInstance');
		$method->setAccessible(true);

		return $method->invoke($request);
	}

	protected function getAttrs($merge = []){
		$attrs = attributes('App\Models\Location');
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
	public function id_WhenPresent_IsRequired(){
		$attrs = self::getAttrs([ 'id' => null ]);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('id'));

		$attrs = self::getAttrs([ 'id' => '' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('id'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function id_WhenNotPresent_IsNotRequired(){
		$attrs = self::getAttrs();

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('id'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function id_WhenPresent_MustExist(){
		$location = factory('App\Models\Location')->create();

		$attrs = self::getAttrs([ 'id' => $location->id ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('id'));

		$attrs = self::getAttrs([ 'id' => '123' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('id'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function name_IsRequired(){
		$attrs = self::getAttrs([ 'name' => null ]);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('name'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function lat_IsRequired(){
		$attrs = self::getAttrs([ 'lat' => null ]);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('lat'));
	}
	
	/**
	 * @test
	 * @group validation
	 */
	public function lng_IsRequired(){
		$attrs = self::getAttrs([ 'lng' => null ]);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('lng'));
	}


}