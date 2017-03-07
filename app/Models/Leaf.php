<?php 
namespace App\Models;

use Baum\Node as BaumNode;
use Illuminate\Support\Facades\DB;
use App\Models\Traits\Tracked;
use App\Models\Traits\Staged;
use Exception;

class Leaf extends BaumNode {

    use Tracked;
    use Staged;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'leaf';

    /**
     * The attributes that not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id','parent_id', 'lft', 'rgt', 'depth','created_at','created_by','updated_at','updated_by'];
    protected $hidden = ['location_id','parent_id', 'lft', 'rgt', 'depth','deleted_at','created_at','created_by','updated_at','updated_by'];

    public function location(){
        return $this->belongsTo('App\Models\Location');
    }

    public function contents(){
        return $this->morphToMany('App\Models\Content','owner','content_group')->withTimestamps()->withPivot('sequence','created_by','updated_by')->orderBy('sequence');
    }

    public function all_children()
    {
        return $this->children()->get();
    }

    public function getNameAttribute(){
        return $this->hasName() ? $this->attributes['name'] : $this->location->name;
    }

    public function hasName(){
        return !empty($this->attributes['name']);
    }

    public function delete(){
        try {
            DB::transaction(function(){
                // Ensure that all content is 'detached'
                $children = $this->getDescendants();
                if($children) {
                    foreach ($children as $child) {
                        $child->contents()->sync([]);
                    }
                }

                $this->contents()->sync([]);
                // Call the default delete method, which will also delete descendants.
                // Note: Baum does not call $child->delete() so we manage to avoid recursion issues.
                parent::delete();
            });
        } catch(Exception $e){
            throw $e;
        }
        return true;
    }

}