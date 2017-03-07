<?php

use App\Models\Tour;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class TourTest extends TestCase {

    use DatabaseTransactions;

	/**
	 * @test
	 */
	public function featuredAttribute_IsNotSet_ReturnsFalsy(){
		$tour = new Tour();
		$this->assertNull($tour->featured);
	}

	/**
	 * @test
	 */
	public function featuredAttribute_IsNumeric_ReturnsBoolean(){
		$tour = factory('App\Models\Tour')->make([ 'featured' => 1 ]);
		$this->assertTrue($tour->featured);

		$tour = factory('App\Models\Tour')->make([ 'featured' => 0 ]);
		$this->assertFalse($tour->featured);
	}

	/**
	 * @test
	 */
	public function featuredAttribute_IsString_ReturnsBoolean(){
		$tour = factory('App\Models\Tour')->make([ 'featured' => '1' ]);
		$this->assertTrue($tour->featured);

		$tour = factory('App\Models\Tour')->make([ 'featured' => '0' ]);
		$this->assertFalse($tour->featured);
	}

	/**
	 * @test
	 */
	public function featuredAttribute_IsBoolean_ReturnsBoolean(){
		$tour = factory('App\Models\Tour')->make([ 'featured' => true ]);
		$this->assertTrue($tour->featured);

		$tour = factory('App\Models\Tour')->make([ 'featured' => false ]);
		$this->assertFalse($tour->featured);
	}

	/**
	 * @test
	 */
	public function toArray_ConvertsItemsStringToArray(){
		$tour = factory('App\Models\Tour')->make([ 'items' => '1,2,3' ]);

		$tour = $tour->toArray();
		$this->assertEquals([ 1, 2, 3 ], $tour['items']);
	}

	/**
	 * @test
	 */
	public function forceDelete_ForcesADelete(){
		$tour = factory('App\Models\Tour')->create();
		$this->assertEquals(1, DB::table('tour')->where('id', $tour->id)->count());

		$tour->forceDelete();
		$this->assertEquals(0, DB::table('tour')->where('id', $tour->id)->count());
	}
}
