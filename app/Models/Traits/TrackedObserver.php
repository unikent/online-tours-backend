<?php
namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Class TrackedObserver
 *
 * Implements event handlers for Tracked models
 *
 * @package App\Models\Traits
 * @see App\Models\Traits\Tracked
 */
class TrackedObserver
{
    public function creating($model)
    {
        // If there is an authorized user
        if (Auth::check()) {
            $user = Auth::user();
            $primaryKeyName = $user->getKeyName();
            $model->created_by = $user->$primaryKeyName;
        }
    }
    public function saving($model)
    {
        // If there is an authorized user
        if (Auth::check()) {
            $user = Auth::user();
            $primaryKeyName = $user->getKeyName();
            $model->updated_by = $user->$primaryKeyName;
        }
    }
}