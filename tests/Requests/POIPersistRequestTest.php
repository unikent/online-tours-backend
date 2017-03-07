<?php

use App\Http\Requests\POIPersistRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class POIPersistRequestTest extends TestCase {

	use DatabaseTransactions;

	protected function setupRequest(){
		$request = new POIPersistRequest;
		$request->setContainer($this->app);
		return $request;
	}

	protected function setupRequestValidator($attrs, $request = null){
		if(!$request){
			$request = $this->setupRequest();
		}

		$request->query->add($attrs);

		// Use the magic of reflection to make getValidatorInstance accessible
		$reflector = new ReflectionClass('App\Http\Requests\POIPersistRequest');
		$method = $reflector->getMethod('getValidatorInstance');
		$method->setAccessible(true);

		return $method->invoke($request);
	}

	protected function getAttrs($merge = []){
		$location = factory('App\Models\Location')->create();

		$attrs = [ 'location_id' => $location->id ,'name'=>$location->name];
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
	public function locationId_IsRequired(){
		$attrs = self::getAttrs([ 'location_id' => null ]);

		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('location_id'));

		$attrs = self::getAttrs([ 'location_id' => '' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('location_id'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function locationId_WhenPresent_MustExist(){
		$location = factory('App\Models\Location')->create();

		$attrs = self::getAttrs([ 'location_id' => $location->id ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('location_id'));

		$attrs = self::getAttrs([ 'location_id' => '123' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('location_id'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function parentId_WhenPresent_CanBeEmpty(){
		$leaf = factory('App\Models\Leaf')->create();

		$attrs = self::getAttrs([ 'parent_id' => $leaf->id ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('parent_id'));

		$attrs = self::getAttrs([ 'parent_id' => 0 ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('parent_id'));

		$attrs = self::getAttrs([ 'parent_id' => '' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('parent_id'));
	}

	/**
	 * @test
	 * @group validation
	 */
	public function parentId_WhenPresentAndNotEmpty_MustBeInteger(){
		$leaf = factory('App\Models\Leaf')->create();

		$attrs = self::getAttrs([ 'parent_id' => $leaf->id ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(0, $validator->errors()->get('parent_id'));

		$attrs = self::getAttrs([ 'parent_id' => 'abc' ]);
		$validator = $this->setupRequestValidator($attrs);
		$this->assertCount(1, $validator->errors()->get('parent_id'));
	}
}