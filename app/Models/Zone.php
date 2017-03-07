<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Tracked;
use App\Models\Traits\Staged;
use Illuminate\Support\Facades\DB;
use Exception;
use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;

class Zone extends Model implements SluggableInterface{

    use SluggableTrait;
    use SoftDeletes;
    use Tracked;
    use Staged;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zone';

    /**
     * The attribute used as a primary key.
     *
     * @var string
     */
    protected $primaryKey = 'leaf_id';
    public $incrementing = false;


    protected $sluggable = array(
        'build_from' => 'name',
        'save_to'    => 'slug',
        'include_trashed'=>'true'
    );

    /**
     * The attributes that should be cast to Carbon objects.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['leaf_id','deleted_at','created_at','created_by','updated_at','updated_by'];

    protected $hidden = ['deleted_at','created_at','created_by','updated_at','updated_by'];

    public function tours(){
        return $this->hasMany('App\Models\Tour', 'leaf_id', 'leaf_id')->orderBy('sequence');
    }

    public function leaf(){
        return $this->hasOne('App\Models\Leaf', 'id', 'leaf_id');
    }

    public static function fetchOrFail($identifier)
    {
        if(is_numeric($identifier)){
            return Zone::findOrFail($identifier);
        } else {
            return Zone::where('slug', '=', $identifier)->firstOrFail();
        }
    }

    public function delete(){
        try {
            DB::transaction(function(){
                // Ensure that all dependent content is deleted
                $this->tours()->delete();

                // Call the default delete method, which will also delete descendants.
                parent::delete();
            });
        } catch(Exception $e){
            throw $e;
        }
        return true;
    }

    public function forceDelete(){
        $this->forceDeleting = true;

        try {
            DB::transaction(function(){
                $this->tours()->forceDelete();
                $this->delete();
            });
        } catch(Exception $e){
            throw $e;
        }

        $this->forceDeleting = false;
        return true;
    }
}