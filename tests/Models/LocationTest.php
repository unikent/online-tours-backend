<?php

use App\Models\Leaf;
use App\Models\Location;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class LocationTest extends TestCase {

    use DatabaseTransactions;

    /**
     * @test
     */
    public function getNotInTree_ReturnsLocationIdsOfLeafsNotInTheSpecifiedTree(){
        $location1 = factory('App\Models\Location')->create();
        $leaf1 = factory('App\Models\Leaf')->create([ 'location_id' => $location1->id ]);

        $location2 = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create([ 'location_id' => $location2->id ]); 
        $leaf->makeChildOf($leaf1);

        $location3 = factory('App\Models\Location')->create();
        factory('App\Models\Leaf')->create([ 'location_id' => $location3->id ]); 

        $location4 = factory('App\Models\Location')->create();
        $leaf4 = factory('App\Models\Leaf')->create([ 'location_id' => $location4->id ]);

        $location5 = factory('App\Models\Location')->create();
        $leaf = factory('App\Models\Leaf')->create([ 'location_id' => $location5->id ]); 
        $leaf->makeChildOf($leaf4);

        $results = Location::getNotInTree($leaf1->id)->lists('id')->all();

        $this->assertNotContains($location1->id, $results);
        $this->assertNotContains($location2->id, $results);
        $this->assertContains($location3->id, $results);
        $this->assertContains($location4->id, $results);
        $this->assertContains($location5->id, $results);
    }

    /**
     * @test
     */
    public function forceDelete_ForcesADelete(){
        $location = factory('App\Models\Location')->create();
        $this->assertEquals(1, DB::table('location')->where('id', $location->id)->count());

        $location->forceDelete();
        $this->assertEquals(0, DB::table('location')->where('id', $location->id)->count());
    }
}