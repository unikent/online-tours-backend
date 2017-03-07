<?php namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Http\Helpers\ContentViewHelper;

class ContentComposer {

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('contentHelper', new ContentViewHelper());
    }
}