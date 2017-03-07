<?php namespace App\Http\ViewComposers;

use App\Http\Helpers\RevisionHelper;
use Illuminate\View\View;

use App\Http\Helpers\ScriptHelper;
use App\Http\Helpers\StyleHelper;
use App\Http\Helpers\LinkHelper;

class BaseComposer {

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('style', StyleHelper::instance());
        $view->with('script', ScriptHelper::instance());

        $view->with('linkHelper', new LinkHelper());

        $view->with('revisionHelper', new RevisionHelper());
    }
}