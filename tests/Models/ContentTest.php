<?php

use App\Models\Content;
use App\Models\Content\Image as ContentImage;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContentTest extends TestCase {

    use DatabaseTransactions;

    /**
     * @test
     */
    function setType_whenInvalidType_defaultsToText(){
        $content = new \App\Models\Content();
        $content->type = 'foobar';
        $this->assertEquals('text',$content->type);
    }

    /**
     * @test
     */
    function setType_whenValidType_setsType(){
        $content = new \App\Models\Content();

        foreach(Content::getTypes() as $type){
            $content->type = $type;
            $this->assertEquals($type,$content->type);
        }
    }

    /**
     * @test
     */
    function setMeta_SerializesToJson(){
        $content = new Content();
        $content->meta = [ 'foo' => 'bar' ];

        $attrs = $content->getAttributes();
        $this->assertJson($attrs['meta']);

        $data = json_decode($attrs['meta'], TRUE);
        $this->assertEquals($data['foo'], 'bar');
    }

    /**
     * @test
     */
    function setMeta_CachesJson(){
        $content = new Content();
        $content->meta = [ 'foo' => 'bar' ];
        $this->assertEquals($content->_decoded_meta['foo'], 'bar');
    }


    /**
     * @test
     */
    function getMeta_DeserializesJson(){
        $content = new Content();
        $content->setRawAttributes([ 'meta' => json_encode([ 'foo' => 'bar' ]) ]);
        $this->assertEquals($content->meta['foo'], 'bar');
    }

    /**
     * @test
     */
    function getMeta_CachesJson(){
        $content = new Content();
        $content->setRawAttributes([ 'meta' => json_encode([ 'foo' => 'bar' ]) ]);

        $content->meta;
        $this->assertEquals($content->_decoded_meta['foo'], 'bar');
    }


    /**
     * @test
     * @group sti
     */
    function magicGetter_ReturnsDataFromMeta(){
        $content = new Content();
        $content->setRawAttributes(['meta' => json_encode([ 'foo' => 'bar' ])]);
        $this->assertEquals($content->foo, 'bar');
    }


    /**
     * @test
     * @group sti
     */
    function magicSetter_KeyIsAModelAttribute_SetsDataOnModel(){
        $content = new Content();
        $content->value = 'bar';
        $this->assertEquals('bar', $content->getAttribute('value'));
    }

    /**
     * @test
     * @group sti
     */
    function magicSetter_KeyIsNotAModelAttribute_SetsDataOnMeta(){
        $content = new Content();
        $content->foo = 'bar';

        $meta = $content->getAttribute('meta');
        $this->assertEquals($meta['foo'], 'bar');
    }


    /**
     * @test
     * @group sti
     */
    function toArray_SerializesModelWithMetaAttributes(){
        $content = new Content();

        $content->setRawAttributes([ 'value'=> 'fizzbuzz', 'meta' => json_encode([ 'foo' => 'bar' ]) ]);

        $data = $content->toArray();
        $this->assertEquals($data['value'], 'fizzbuzz');
        $this->assertEquals($data['foo'], 'bar');
    }

    /**
     * @test
     * @group sti
     */
    function toArray_ModelAttributesDoNotGetTrumpedByMeta(){
        $content = new Content();
        $content->setRawAttributes(['value'=>'boofar', 'meta' => json_encode([ 'value' => 'foobar' ]) ]);

        $data = $content->toArray();
        $this->assertEquals($data['value'], 'boofar');
    }

    /**
     * @test
     * @group sti
     */
    function toArray_ExcludesModelHidden(){
        $content = new Content();
        $content->setRawAttributes([ 'pivot' => 'foobar', 'meta' => json_encode([ 'value' => 'foobar' ]) ]);

        $data = $content->toArray();
        $this->assertArrayNotHasKey('pivot',$data);
        $this->assertArrayNotHasKey('meta',$data);
    }


    /**
     * @test
     * @group sti
     */
    function toArray_ExcludesMetaHidden(){
        $content = new Content();
        $content->setRawAttributes([ 'meta' => json_encode([ 'meh' => 'foobar', 'arg'=>'blarg' ]) ]);
        $content->setHiddenMeta(['arg']);
        $data = $content->toArray();

        $this->assertArrayHasKey('meh',$data);
        $this->assertArrayNotHasKey('arg',$data);
    }


    /**
     * @test
     * @group sti
     */
    function defaultScope_ReturnsAllContent(){
        factory('App\Models\Content', 2)->create();
        factory('App\Models\Content\Image', 5)->create();

        $this->assertEquals(7, Content::count());
    }


    /**
     * @test
     * @group sti
     */
    function defaultScope_CreatesContentTypedModels(){
        factory('App\Models\Content', 2)->create([ 'type' => '' ]);
        factory('App\Models\Content\Image', 5)->create();

        $c = 0; $i = 0;
        $content = Content::all();
        foreach($content as $content){
            if($content instanceof ContentImage){
                $i++;
            } elseif($content instanceof Content){
                $c++;
            }  
        }

        $this->assertEquals(2, $c);
        $this->assertEquals(5, $i);
    }

    /**
     * @test
     */
    function getMediaPath_returnsTypeSpecificPath(){

        $text = factory('App\Models\Content\Text')->create();
        $audio = factory('App\Models\Content\Audio')->create();
        $video = factory('App\Models\Content\Video')->create();
        $image = factory('App\Models\Content\Image')->create();

        $this->assertRegExp('/.*\/text$/',$text->getMediaPath());
        $this->assertRegExp('/.*\/image$/',$image->getMediaPath());
        $this->assertRegExp('/.*\/audio$/',$audio->getMediaPath());
        $this->assertRegExp('/.*\/video$/',$video->getMediaPath());

    }

    /**
     * @test
     */
    function getMediaUri_returnsTypeSpecificPath(){

        $text = factory('App\Models\Content\Text')->create();
        $audio = factory('App\Models\Content\Audio')->create();
        $video = factory('App\Models\Content\Video')->create();
        $image = factory('App\Models\Content\Image')->create();

        $this->assertRegExp('/.*\/text$/',$text->getMediaUri());
        $this->assertRegExp('/.*\/image$/',$image->getMediaUri());
        $this->assertRegExp('/.*\/audio$/',$audio->getMediaUri());
        $this->assertRegExp('/.*\/video$/',$video->getMediaUri());

    }


    /**
     * @test
     */
    function getValidationRules_includesBaseRules(){

        $text = factory('App\Models\Content\Text')->create();
        $rules = $text::getValidationRules();
        $this->assertArrayHasKey('name',$rules);
        $this->assertArrayHasKey('type',$rules);

    }

    /**
     * @test
     */
    function getValidationRules_whenMethodPost_hasRequirements(){

        $text = factory('App\Models\Content\Text')->create();
        $rules = $text::getValidationRules('POST');
        $this->assertRegExp('/^required\|.*/',$rules['name']);
        $this->assertRegExp('/^required\|.*/',$rules['type']);

    }

    /**
     * @test
     */
    function getValidationRules_whenMethodPut_hasRequirements(){

        $text = factory('App\Models\Content\Text')->create();
        $rules = $text::getValidationRules('PUT');
        $this->assertRegExp('/^required\|.*/',$rules['name']);
        $this->assertRegExp('/^required\|.*/',$rules['type']);

    }

    /**
     * @test
     */
    function getValidationRules_whenMethodPatch_doesNotHaveRequirements(){

        $text = factory('App\Models\Content\Text')->create();
        $rules = $text::getValidationRules('PATCH');
        $this->assertNotRegExp('/^required\|.*/',$rules['name']);
        $this->assertNotRegExp('/^required\|.*/',$rules['type']);

    }

    /**
     * @test
     */
    function getValidationRules_mergesTypeValidation(){

        $content = factory('App\Models\Content')->create();
        $ref = new ReflectionObject($content);
        $prop = $ref->getProperty('validation_rules');
        $prop->setAccessible(true);
        $prop->setValue($content,['foo'=>'bar']);

        $rules = $content::getValidationRules('PATCH');
        $this->assertArrayHasKey('name',$rules);
        $this->assertArrayHasKey('type',$rules);
        $this->assertArrayHasKey('foo',$rules);
        $this->assertEquals('bar',$rules['foo']);
    }

    /**
     * @test
     */
    function attachTo_WhenContentDoesNotExist_ReturnsFalse(){
        $leaf = factory('App\Models\Leaf')->create();
        $content = factory('App\Models\Content')->make();

        $this->assertFalse($content->attachTo($leaf->id, 'leaf'));
    }

    /**
     * @test
     */
    function attachTo_WhenContentExistsAndLeafAndNotFound_ReturnsFalse(){
        $content = factory('App\Models\Content')->create();

        $this->assertFalse($content->attachTo(123, 'leaf'));
    }

    /**
     * @test
     */
    function attachTo_WhenContentExistsAndLeafAndFound_AttachesContentToLeafAndReturnsTrue(){
        $leaf = factory('App\Models\Leaf')->create();
        $content = factory('App\Models\Content')->create();

        $this->assertTrue($content->attachTo($leaf->id, 'leaf'));
        $this->assertEquals($leaf->contents()->first()->id, $content->id);
    }

    /**
     * @test
     */
    function attachTo_WhenContentExistsAndTourAndNotFound_ReturnsFalse(){
        $content = factory('App\Models\Content')->create();


        $this->assertFalse($content->attachTo(123, 'tour'));
    }

    /**
     * @test
     */
    function attachTo_WhenContentExistsAndTourAndFound_AttachesContentToTour(){
        $tour = factory('App\Models\Tour')->create();
        $content = factory('App\Models\Content')->create();


        $content->attachTo($tour->id, 'tour');

        $this->assertEquals($tour->contents()->first()->id, $content->id);
    }

    /**
     * @test
     */
    function attachTo_WhenContentExistsAndPageAndNotFound_ReturnsFalse(){
        $content = factory('App\Models\Content')->create();


        $this->assertFalse($content->attachTo(123, 'page'));
    }

    /**
     * @test
     */
    function attachTo_WhenContentExistsAndPageAndFound_AttachesContentToPage(){
        $page = factory('App\Models\Page')->create();
        $content = factory('App\Models\Content')->create();


        $content->attachTo($page->id, 'page');

        $this->assertEquals($page->contents()->first()->id, $content->id);
    }



    /**
     * @test
     */
    function detachFrom_WhenContentDoesNotExist_ReturnsFalse(){
        $leaf = factory('App\Models\Leaf')->create();
        $content = factory('App\Models\Content')->make();


        $this->assertFalse($content->detachFrom($leaf->id, 'leaf'));
    }

    /**
     * @test
     */
    function detachFrom_WhenContentExistsAndLeafAndNotFound_ReturnsFalse(){
        $content = factory('App\Models\Content')->create();


        $this->assertFalse($content->detachFrom(123, 'leaf'));
    }

    /**
     * @test
     */
    function detachFrom_WhenContentExistsAndLeafAndFound_AttachesContentToLeafAndReturnsTrue(){
        $content = factory('App\Models\Content')->create();

        $leaf = factory('App\Models\Leaf')->create();
        $leaf->contents()->attach($content);



        $this->assertTrue($content->detachFrom($leaf->id, 'leaf'));
        $this->assertEmpty($leaf->contents);
    }

    /**
     * @test
     */
    function detachFrom_WhenContentExistsAndTourAndNotFound_ReturnsFalse(){
        $content = factory('App\Models\Content')->create();


        $this->assertFalse($content->detachFrom(123, 'tour'));
    }

    /**
     * @test
     */
    function detachFrom_WhenContentExistsAndTourAndFound_AttachesContentToTour(){
        $content = factory('App\Models\Content')->create();

        $tour = factory('App\Models\Tour')->create();
        $tour->contents()->attach($content);


        $content->detachFrom($tour->id, 'tour');

        $this->assertEmpty($tour->contents);
    }

    /**
     * @test
     */
    function detachFrom_WhenContentExistsAndPageAndNotFound_ReturnsFalse(){
        $content = factory('App\Models\Content')->create();


        $this->assertFalse($content->detachFrom(123, 'page'));
    }

    /**
     * @test
     */
    function detachFrom_WhenContentExistsAndPageAndFound_AttachesContentToPage(){
        $content = factory('App\Models\Content')->create();

        $page = factory('App\Models\Page')->create();
        $page->contents()->attach($page);


        $content->detachFrom($page->id, 'page');

        $this->assertEmpty($page->contents);
    }

    /**
     * @test
     */
    function getForSearch_HasIDandTextInReturnedArray()
    {
        $content = factory('App\Models\Content')->create();
        $out = $content->getForSearch();
        $this->assertArrayHasKey('id', $out);
        $this->assertArrayHasKey('text', $out);
    }

}