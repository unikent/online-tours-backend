<?php

use App\Models\Zone;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class ZoneTest extends TestCase {

    use DatabaseTransactions;

	/**
	 * @test
	 *
	 * This test may look stupid, but it would have saved me
	 * an entire afternoon of debugging! -TG
	 */
	public function leafId_ConfiguredCorrectly(){
		$zone = new Zone;
		$this->assertFalse($zone->incrementing);

		$zone = factory('App\Models\Zone')->create();
		$this->assertNotEmpty($zone->leaf_id);
	}


    /**
     * @test
     */
    public function fetchOrFail_WithID_WhenFoundReturnsZone(){
        $zone = factory('App\Models\Zone')->create();
        $result = Zone::fetchOrFail($zone->leaf_id);
        $this->assertEquals($zone->leaf_id, $result->leaf_id);
    }

    /**
     * @test
     */
    public function fetchOrFail_WithID_WhenNotFoundThrowsException(){
        $zone = factory('App\Models\Zone')->create();
        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');
        Zone::fetchOrFail('123');
    }

    /**
     * @test
     */
    public function fetchOrFail_WithSlug_WhenFoundReturnsZone(){
        $zone = factory('App\Models\Zone')->create();
        $result = Zone::fetchOrFail($zone->slug);
        $this->assertEquals($zone->leaf_id, $result->leaf_id);
    }

    /**
     * @test
     */
    public function fetchOrFail_WithSlug_WhenNotFoundThrowsException(){
        $zone = factory('App\Models\Zone')->create();
        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');
        Zone::fetchOrFail('foobar');
    }


    /**
     * @test
     */
    public function delete_DeletesAssociations(){
        $zone = factory('App\Models\Zone')->create();
        factory('App\Models\Tour', 3)->create([ 'leaf_id' => $zone->leaf_id ]);

        $this->assertCount(3, $zone->tours()->get());
        $zone->delete();
        $this->assertCount(0, $zone->tours()->get());
    }

    /**
     * @test
     */
    public function delete_DeletesZone(){
        $zone = factory('App\Models\Zone')->create();
        $zone->delete();

        $deleted = Zone::onlyTrashed()->find($zone->leaf_id);
        $this->assertNotNull($deleted);
        $this->assertEquals($zone->name,$deleted->name);
    }

	/**
	 * @test
	 */
	public function forceDelete_DeletesAssociations(){
        $zone = factory('App\Models\Zone')->create();
        factory('App\Models\Tour', 3)->create([ 'leaf_id' => $zone->leaf_id ]);

        $this->assertEquals(3, DB::table('tour')->where('leaf_id', $zone->leaf_id)->count());
		$zone->forceDelete();
		$this->assertEquals(0, DB::table('tour')->where('leaf_id', $zone->leaf_id)->count());
	}

	/**
	 * @test
	 */
	public function forceDelete_ForcesADelete(){
		$zone = factory('App\Models\Zone')->create();
		$this->assertEquals(1, DB::table('zone')->where('leaf_id', $zone->leaf_id)->count());

		$zone->forceDelete();
		$this->assertEquals(0, DB::table('zone')->where('leaf_id', $zone->leaf_id)->count());
	}
}