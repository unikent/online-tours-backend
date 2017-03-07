<?php namespace App\Models\Traits;

use App\Models\Traits\Scopes\TypeScope;

/**
 * Trait to add functionality to interact with the live version of this model
 *
 */
trait STI {

    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootSTI()
    {
        static::addGlobalScope(new TypeScope);
    }

}