<?php namespace App\Models\Traits\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ScopeInterface;

class TypeScope implements ScopeInterface {

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if($model::TYPE){
            $builder->where('type', '=', $model::TYPE);
        }
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function remove(Builder $builder, Model $model)
    {
        if($model::TYPE){
			$column = $model->getQualifiedDeletedAtColumn();

	        $query = $builder->getQuery();

	        foreach ((array) $query->wheres as $key => $where)
	        {
	            if ($this->isTypeScopeConstraint($where, $column, $model))
	            {
	                unset($query->wheres['type']);
	                $query->wheres = array_values($query->wheres);
	            }
	        }
	    }
    }

    protected function isTypeScopeConstraint($where, $column, $model)
    {
		return $model::TYPE ? (($where['type'] == $model::TYPE) && ($where['column'] == $column)) : false;
	}
}