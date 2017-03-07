<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Zone;
use App\Models\Leaf;
use App\Models\Location;
use App\Models\Content;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder {

	protected $campus = 'Canterbury Campus';
	protected $locations = [
		'Rutherford College',
		'Darwin College',
		'Eliot College',
		'Keynes College',
		'Woolf College',
		'Turing College',
		'Sports Centre',
		'Sports Pavilion',
		'Colyer-Fergusson Music Building',
		'The Gulbenkian',
		'Templeman Library',
		'Oaks Day Nursery',
		'University Pharmacy',
		'Medical Centre',
		'Careers and Employability Service',
		'Students Centre The Venue',
		'Becket Court',
		'Tyler Court C',
		'Darwin Houses',
		'Park Wood Shop',
		'Cornwallis South East',
		'Ingram Building',
		'Jarman Building',
		'Jennison Building',
		'Grimond Building',
		'Kent Business School',
		'Locke Building',
		'Aphra and Lumley Building',
		'Mandela Building',
		'Marlowe Building',
		'Registry',
		'Senate',
		'Stacey Building',
		'UELT Building',
		// Locations created since don't actually correlate to a building.
		'Essentials',
		'Blackwells bookshop',
		'Park Wood Student Village',
	];
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$this->call('UserTableSeeder');
        $this->call('LocationTableSeeder');

        // Additional locations
        $base = Location::where('name', '=', 'Locke Building')->first();
        Location::create([
        	'name'=> 'Essentials',
        	'polygon'=> $base->polygon,
        	'lat'=> $base->lat,
        	'lng'=> $base->lng,
        ]);
        Location::create([
        	'name'=> 'Blackwells bookshop',
        	'polygon'=> $base->polygon,
        	'lat'=> '51.296329390421434',
        	'lng'=> '1.0678165806637026',
        ]);
        Location::create([
        	'name'=> 'Park Wood Student Village',
        	'lat'=> '51.2967676',
        	'lng'=> '1.0575565',
        ]);

        Zone::truncate();
        Leaf::truncate();
        Content::truncate();
        DB::table('content_group')->truncate();

        // Create leaf's
        $campus = $this->makeLeaf($this->campus);
        foreach($this->locations as $location){
        	$this->makeLeaf($location, $campus);
        }

        Zone::create([
            'leaf_id'=>$campus->id,
            'name'=>'Canterbury',
            'slug'=>'canterbury',
            'sequence'=>1
        ]);
	}

	protected function makeLeaf($location_name, $is_child_of = null){
		$leaf = Leaf::create([
            'location_id' => Location::where('name', '=', $location_name)->first()->id,
            'slug' => str_random(6)
        ]);
        if($is_child_of !== null) $leaf->makeChildOf($is_child_of);

        return $leaf;
	}

}
