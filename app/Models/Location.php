<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Tracked;
use App\Models\Traits\Staged;

class Location extends Model {

    use SoftDeletes;
    use Tracked;
    use Staged;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'location';

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
    protected $fillable = ['name','lat','lng','disabled_go_url'];

    protected $hidden = ['id','deleted_at','created_at','created_by','updated_at','updated_by'];


    public function isRemote(){
        return !empty($this->remote_id);
    }

    public function isLocal(){
        return !$this->isRemote();
    }

    public function scopeLocal($query){
        return $query->where('remote_id','=',0);
    }

    public function scopeRemote($query){
        return $query->where('remote_id','>',0);
    }

    // Get results not in the specified tree.
    public static function getNotInTree($tree){
        $in_tree = Leaf::find($tree)->descendantsAndSelf()->lists('location_id')->all();
        return static::whereNotIn('id', $in_tree)->get();
    }

    public function leaves(){
        return $this->hasMany('Leaf','location_id','id');
    }
}