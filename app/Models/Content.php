<?php 
/**
 * Content
 * 
 * This model uses Single Table Inheritance, see:
 *  @url http://codebyjeff.com/blog/2014/07/single-table-inheritence-in-laravel
 *  @url http://www.colorfultyping.com/single-table-inheritance-in-laravel-4
 */
namespace App\Models;

use App\Models\Traits\STI;
use App\Models\Traits\Staged;
use App\Models\Traits\Tracked;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Content extends Model {

    use STI;
    use SoftDeletes;

    use Tracked;
    use Staged;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'content';

    protected static $base_validation_rules = [
        'name'=>'string|max:255',
        'type'=>'string|max:255',
        'owner' => 'required_with:owner_type|integer',
        'owner_type' => 'required_with:owner|in:leaf,tour,page'
    ];

    protected static $validation_rules = [];

    protected $allowed_input = [];


    public static $sanitized_fields = [];

    public static $stripped_fields = ['name'];


    const TYPE = null;

    /**
     * The attributes that should be cast to Carbon objects.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected static $columns = null;

    /**
     * The attributes that not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id','deleted_at','created_at','created_by','updated_at','updated_by'];
    protected $hidden = ['deleted_at','created_at','created_by','updated_at','updated_by', 'pivot','meta','name'];

    protected $hidden_meta = [];

    /**
     * The supported content types
     *
     * @var array
     */
    protected static $types = [
        'text',
        'image',
        'audio',
        'video'
    ];

    protected $default_fields = ['type','name','value', 'owner','owner_type'];

    /**
     * The decoded JSON meta array
     *
     * @var bool|Array
     */
    public $_decoded_meta = false;


    public function __construct($attributes = []){
        if(is_null(static::$columns)){
            static::$columns = array_values(Schema::getColumnListing('content'));
        }

        if(!is_null(static::TYPE)){
            $this->type = static::TYPE;
        }
        
        parent::__construct($attributes);
    }


    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        // Attempt to instantiate a class based on 'Type', STI.
        if($attributes->type) {
            $class = 'App\\Models\\Content\\' . ucwords($attributes->type);

            $model = new $class([], true);
            $model->exists = true;
            $model->setRawAttributes((array) $attributes, true);
            $model->setConnection($connection ?: $this->connection);
        } else {
            $model = parent::newFromBuilder($attributes, $connection);
        }

        return $model;
    }


    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        $this->getMetaAttribute(); // Trigger decoding of meta

        if(array_key_exists($key, $this->attributes) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        } 

        if(isset($this->_decoded_meta[$key])) {
            if(is_object($this->_decoded_meta[$key])){
                return (array)$this->_decoded_meta[$key];
            }else {
                return $this->_decoded_meta[$key];
            }
        }

        return $this->getRelationValue($key);
    }


    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->getMetaAttribute(); // Trigger decoding of meta

        if(!in_array($key, static::$columns) && !$this->hasSetMutator($key)) {
            $this->_decoded_meta[$key] = $value;
        } else {
            return $this->setAttribute($key, $value);
        }
    }


    /**
     * Get the supported content types
     * @return array
     */
    public static function getTypes(){
        return static::$types;
    }

    public function leaves(){
        return $this->morphedByMany('App\Models\Leaf','owner','content_group')->withTimestamps()->withPivot('sequence','created_by','updated_by')->orderBy('id')->orderBy('sequence');
    }

    public function pages(){
        return $this->morphedByMany('App\Models\Page','owner','content_group')->withTimestamps()->withPivot('sequence','created_by','updated_by')->orderBy('id')->orderBy('sequence');
    }

    public function tours(){
        return $this->morphedByMany('App\Models\Tour','owner','content_group')->withTimestamps()->withPivot('sequence','created_by','updated_by')->orderBy('id')->orderBy('sequence');
    }


    public function setTypeAttribute($type){
        if(!is_null(static::TYPE)){
                $this->attributes['type'] = static::TYPE;
        }else {
            $type = in_array($type, static::getTypes()) ? $type : (empty(static::TYPE) ? 'text' : static::TYPE);
            $this->attributes['type'] = $type;
        }
    }

    public function getMetaAttribute(){
        if(!$this->_decoded_meta && isset($this->attributes['meta'])) {
            $this->_decoded_meta = (array) json_decode($this->attributes['meta']);
            if(!is_array($this->_decoded_meta)){
                $this->_decoded_meta = [];
            }
        }

        if(!isset($this->attributes['meta'])){
            $this->attributes['meta'] = json_encode([]);
        }

        return $this->_decoded_meta;
    }


    public function setMetaAttribute($meta){
        $this->_decoded_meta = (array) $meta;
        $this->attributes['meta'] = json_encode($meta);
    }


    public function toArray(){
        $attrs = parent::toArray();

        return array_merge(array_diff_key($this->meta,array_flip($this->hidden_meta)), $attrs);
    }


    /**
     * Get the path to this content types media directory
     *
     * @return string
     */
    public static function getMediaPath(){
        return storage_path() . Config::get('filesystems.disks.local.media') .'/'. static::TYPE;
    }

    /**
     * Get the URI to this content types media directory
     *
     * @return string
     */
    public static function getMediaUri(){
        return url('/') . Config::get('filesystems.disks.local.media') .'/'. static::TYPE;
    }

    /**
     * Provide means to refine/extend search for each content type
     *
     * @param $query \Illuminate\Database\Query\Builder
     * @param $search string the search string with added wildcard characters
     * @return \Illuminate\Database\Query\Builder
     */
    public static function refineSearch($query, $search){
        return $query;
    }

    /**
     * Provide validation rules for this content.
     *
     * @return array
     */
    public static function getValidationRules($method = 'POST'){

        $base = static::$base_validation_rules;
        $base['type'] = $base['type'] . '|in:' . implode(',',static::getTypes());


        switch(strtoupper($method)){
            case 'POST' :
            case 'PUT' :
                $base['name'] = 'required|' . $base['name'];
                $base['type'] = 'required|' . $base['type'];
                break;
            case 'PATCH':
                break;
            default :
                break;
        }


        return array_merge($base, static::getTypeValidationRules($method));

    }

    protected static function getTypeValidationRules($method){

        return static::$validation_rules;

    }

    public function getAllowedInput($method = 'GET'){

        return array_merge($this->default_fields, $this->getTypeAllowedInput($method));
    }

    protected function getTypeAllowedInput($method){

        return $this->allowed_input;

    }

    /**
     * Attach the Content model to an owner (Leaf, Page or Tour)
     *
     * @param $owner integer id of a Leaf, Page or Tour
     * @param $owner_type string the (non fully-qualified) class name of the Leaf, Page or Tour
     * @return bool true if on success, false on failure or if owner does not exist or this model has not yet been saved.
     */
    public function attachTo($owner,$owner_type){
        $class= 'App\\Models\\' . ucfirst($owner_type);
        if($this->exists) {
            $owner = $class::find($owner);
            if(!empty($owner)) {
                $owner->contents()->attach($this->id,['sequence'=>(count($owner->contents)+1)]);
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Detach the Content model to an owner (Leaf, Page or Tour)
     *
     * @param $owner integer id of a Leaf, Page or Tour
     * @param $owner_type string the fully qualified class name of the Leaf, Page or Tour
     * @return bool true if on success, false on failure or if owner does not exist or this model has not yet been saved.
     */
    public function detachFrom($owner,$owner_type){
        $class= 'App\\Models\\' . ucfirst($owner_type);
        if($this->exists) {
            $owner = $class::find($owner);
            if(!empty($owner)) {
                $owner->contents()->detach($this->id);
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    /**
     * Return the Content model formatted for search results (select2)
     * Implementing classes should extend this result rather than replacing it completely.
     *
     * @return array
     */
    public function getForSearch()
    {
        $out = [
            'id'=>$this->id,
            'text'=>$this->name
        ];
        return $out;
    }

    /**
     * @return array
     */
    public function getHiddenMeta()
    {
        return $this->hidden_meta;
    }

    /**
     * @param array $hidden_meta
     */
    public function setHiddenMeta($hidden_meta)
    {
        $this->hidden_meta = $hidden_meta;
    }

    public function handleUpload(UploadedFile $file){

        $ts = time();
        $fn = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .'_';
        $ext = '.' . $file->getClientOriginalExtension();

        $filename  = $fn . $ts . $ext;

        Storage::put($this::TYPE . '/' . $filename,$file);

    }

    public function save(Array $options = []){
        if($this->_decoded_meta){
            $this->meta = $this->_decoded_meta;
        }
        return parent::save($options);
    }
}