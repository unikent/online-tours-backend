<?php

use App\Models\Leaf;
use App\Models\Location;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class LeafTest extends TestCase {

    use DatabaseTransactions;

    /**
     * @test
     */
    public function hasName_WhenHasNameAttribute_ReturnsTrue(){
        $location = factory('App\Models\Location')->create();

        $leaf = factory('App\Models\Leaf')->create([ 'name' => 'Boofar' ]);
        $leaf->location()->associate($location);
        $leaf->save();

        $this->assertTrue($leaf->hasName());
    }

    /**
     * @test
     */
    public function hasName_WhenDoesNotHaveNameAttribute_ReturnsFalse(){
        $location = factory('App\Models\Location')->create();

        $leaf = factory('App\Models\Leaf')->create(['name'=>'']);
        $leaf->location()->associate($location);
        $leaf->save();

        $this->assertFalse($leaf->hasName());
    }

    /**
     * @test
     */
    public function getNameAttribute_WhenHasNameAttribute_ReturnsAttribute(){
        $location = factory('App\Models\Location')->create();

        $leaf = factory('App\Models\Leaf')->create([ 'name' => 'Boofar' ]);
        $leaf->location()->associate($location);
        $leaf->save();

        $this->assertEquals('Boofar', $leaf->name);
    }

    /**
     * @test
     */
    public function getNameAttribute_WhenDoesNotHaveNameAttribute_ReturnsLocationAttribute(){
        $location = factory('App\Models\Location')->create();

        $leaf = factory('App\Models\Leaf')->create(['name'=>'']);
        $leaf->location()->associate($location);
        $leaf->save();

        $this->assertEquals($leaf->name, $location->name);
    }
}