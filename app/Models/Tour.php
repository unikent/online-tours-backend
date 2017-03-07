<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Tracked;
use App\Models\Traits\Staged;

class Tour extends Model
{
    use SoftDeletes;
    use Tracked;
    use Staged;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tour';

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

    protected $fillable = ['leaf_id','name','description','duration','items','polyline','sequence','featured'];

    /**
     * The attributes that are hidden from ::all
     *
     * @var array
     */
    protected $hidden = ['deleted_at','sequence','created_at','created_by','updated_at','updated_by'];

    /**
     * Automatic typecasts for attributes
     *
     * @var array
     */
    protected $casts = [
        'featured' => 'boolean',
    ];

    /**
     * A Tour belongs to a Leaf
     *
     * @return mixed
     */
    public function leaf()
    {
        return $this->belongsTo('App\Models\Leaf');
    }

    /**
     * Polymorphic relationship to get tour content
     *
     * @return mixed
     */
    public function contents()
    {
        return $this
            ->morphToMany('App\Models\Content', 'owner', 'content_group')
            ->withTimestamps()
            ->withPivot('sequence', 'created_by', 'updated_by')
            ->orderBy('sequence');
    }

    /**
     * Accessor for the Items attribute to unexplode it from a comma-delimited string
     *
     * @return array
     */
    public function getItemsAttribute($value)
    {
        return array_map(
            function ($item) {
                return intval($item);
            },
            explode(',', $value)
        );
    }


    public function getSortedItems(){
        return implode(',',Leaf::whereIn('id',$this->items)->orderBy('name')->lists('id')->toArray());
    }

}
