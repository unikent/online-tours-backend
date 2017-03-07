<?php namespace App\Models\Traits;
/**
 * Trait to add functionality to interact with the live version of this model
 *
 */
trait Staged {

    /**
     * Check if this model exists in the live database
     *
     * @return bool
     */
    public function isLive(){
        //check if this model exists in live and timestamps match
        return false;
    }

}