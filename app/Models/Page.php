<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Tracked;
use App\Models\Traits\Staged;
use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;

class Page extends Model implements SluggableInterface{

    use SluggableTrait;
    use SoftDeletes;
    use Tracked;
    use Staged;



    protected $sluggable = array(
        'build_from' => 'title',
        'save_to'    => 'slug',
        'include_trashed'=>'true'
    );

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'page';

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
    protected $guarded = ['id','deleted_at','created_at','created_by','updated_at','updated_by'];

    protected $hidden = ['deleted_at','created_at','created_by','updated_at','updated_by'];


    public function contents(){
        return $this->morphToMany('App\Models\Content','owner','content_group')->withTimestamps()->withPivot('sequence','created_by','updated_by')->orderBy('sequence');
    }

    public static function fetchOrFail($identifier)
    {
        if(is_numeric($identifier)){
            return Page::findOrFail($identifier);
        } else {
            return Page::where('slug', '=', $identifier)->firstOrFail();
        }
    }

    public function delete(){
        $this->contents()->sync([]);
        return parent::delete();
    }

}