<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Content as Content;
use App\Models\Leaf as Leaf;
use App\Models\Location;
use Faker\Factory as FakerFactory;

class ContentTableSeeder extends Seeder {

    private static $locations;
    public function run()
    {

        Content::truncate();
        DB::table('content_group')->truncate();

        self::$locations = array(
            'cornwallis' => array('Cornwallis building', Location::where('remote_id','=',20)->first()->id,[]), //nice name & location ID
            'darwin' => array('Darwin college', Location::where('remote_id','=',17)->first()->id,[]),
            'eliot' => array('Eliot college', Location::where('remote_id','=',18)->first()->id, ["wav"=>"eliot_1234567890.wav","ogg"=>"eliot_1234567890.ogg"]),
            'gulbenkian' => array('Gulbenkian theatre', Location::where('remote_id','=',30)->first()->id,[]),
            'rutherford' => array('Rutherford college', Location::where('remote_id','=',16)->first()->id,[]),
            'templeman' => array('Templeman library', Location::where('remote_id','=',41)->first()->id,[])
        );


        self::addAudio();
        self::addText();
        self::addImages();

        $faker = FakerFactory::create();

        $content = [];
        $images=[];
        $videos=[];
        $audios =[];
        $panos = [];

        for($i=0;$i<300;$i++){
            $type = $faker->randomElement(array_diff(Content::getTypes(),['gallery','playlist']));
            $value=null;
            $meta=null;
            switch ($type){
                case 'text':
                    $value = implode(' ',$faker->paragraphs(rand(1,4)));
                    $meta = null;
                    break;
                case 'image':
                    $w = 1960;
                    $h = 1307;
                    $value = 'cornwallis_1234567890.jpg';
                    $meta = [
                        'width'=>$w,
                        'height'=>$h,
                        'title'=>$faker->sentence(rand(1,3)),
                        'caption'=>$faker->sentence(rand(5,10)),
                        'copyright'=>$faker->sentence(rand(2,6))
                    ];
                    $images[] = $i;
                    break;
                case 'audio':
                    $value = 'cornwallis_1234567890.mp3';
                    $meta = [
                        'title'=>$faker->sentence(rand(1,3)),
                        'transcription'=>implode(' ',$faker->paragraphs(rand(1,4)))
                    ];
                    $audios[] = $i;
                    break;
                case 'video':
                    $value = "https://www.youtube.com/embed/MBJXoeqXGks";
                    $meta = [
                        'title'=>$faker->sentence(rand(1,3)),
                        'transcription'=>implode(' ',$faker->paragraphs(rand(1,4)))
                    ];
                    $videos[] = $i;
                    break;
                case 'pano':
                    $value = $faker->sentence(rand(1,3));
                    $meta = null;
                    $panos[] = $i;
                    break;
            }
            $content[]  = Content::Create([
                'type'=>$type,
                'name'=>$faker->sentence(rand(1,3)),
                'value'=>$value,
                'meta'=>$meta
            ]);
        }
        for($i=0;$i<30;$i++){
            $type = $faker->randomElement(['gallery','playlist']);
            $value=null;
            $meta=null;
            switch ($type) {
                case 'gallery':
                    $indexes = $faker->randomElements(array_merge($images, $videos, $panos), rand(2, 6));
                    $ids=[];
                    foreach($indexes as $x){
                        $ids[] = $content[$x]->id;
                    }
                    $value = implode(',',$ids);
                    $meta = [
                        'desc' => $faker->sentence(rand(1, 3))
                    ];
                    break;
                case 'playlist':
                    $indexes = $faker->randomElements($audios, rand(2, 6));
                    $ids=[];
                    foreach($indexes as $x){
                        $ids[] = $content[$x]->id;
                    }
                    $value = implode(',',$ids);
                    $meta = [
                        'desc' => $faker->sentence(rand(1, 3))
                    ];
                    break;
            }
            $content[]  = Content::Create([
                'type'=>$type,
                'name'=>$faker->sentence(rand(1,3)),
                'value'=>$value,
                'meta'=>$meta
            ]);
        }

        $leafcount = Leaf::count();
        for($i=1;$i< $leafcount;$i++){
            $l = Leaf::find($i);
            $seq = $l->contents()->count() +1;
            $l->contents()->save($content[$faker->unique()->numberBetween(0,329)],['sequence'=>$seq]);
        }
        try {
            while(true){
                $l = Leaf::find(rand(1,147));
                $seq = $l->contents()->count() +1;
                $l->contents()->save($content[$faker->unique()->numberBetween(0,329)],['sequence'=>$seq]);
            }
        } catch (\OverflowException $e) {
        }
    }


    public static function addText()
    {
        $faker = FakerFactory::create();
        foreach (static::$locations as $location_key => $location_args) {
            $text  = Content::Create([
                'type'=>'text',
                'name'=>ucfirst($location_key).' title',
                'value'=>implode(' ',$faker->paragraphs(rand(1,4))),
                'meta'=> null
            ]);
            $l = Leaf::where('location_id', '=', $location_args[1])->first();
            $l->contents()->save($text,['sequence' => $l->contents()->count() + 1]);
        }
    }
    public static function addAudio()
    {
        $faker = FakerFactory::create();
        foreach (static::$locations as $location_key => $location_args) {
            $audio  = Content::Create([
                'type'=>'audio',
                'name'=>ucfirst($location_key),
                'value'=>$location_key.'_1234567890.mp3',
                'meta'=> [
                    'title'=>$location_args[0],
                    'transcription'=>implode(' ',$faker->paragraphs(rand(1,4))),
                    'variants'=>$location_args[2]
                ]
            ]);
            $l = Leaf::where('location_id', '=', $location_args[1])->first();
            $l->contents()->save($audio,['sequence' => $l->contents()->count() + 1]);
        }
    }
    public static function addImages()
    {
        $faker = FakerFactory::create();
        foreach (static::$locations as $location_key => $location_args) {
            $image  = Content::Create([
                'type'=>'image',
                'name'=>ucfirst($location_key),
                'value'=>$location_key.'_1234567890.jpg',
                'meta'=>[
                    'width'=>1600,
                    'height'=>1200,
                    'title'=>$location_args[0].' image',
                    'caption'=>$faker->sentence(rand(5,10)),
                    'copyright'=>$faker->sentence(rand(2,6))
                ]
            ]);
            $l = Leaf::where('location_id', '=', $location_args[1])->first();
            $l->contents()->save($image,['sequence' => $l->contents()->count() + 1]);
        }
    }
}