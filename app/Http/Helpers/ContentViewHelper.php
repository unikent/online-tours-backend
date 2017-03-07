<?php
namespace App\Http\Helpers;

use App\Models\Content;

class ContentViewHelper{

    public static function getContentList($owner, $echo=true){
        $contents = $owner->contents()->get();
        $html = '<ul class="content-list">';
        foreach($contents as $content){
            $html .= '<li class="content-item" id="content_' . $content->id . '">';
            $html .= view('components.content_form_single', ['content'=>$content])->render();
            $html .= '</li>';
        }
        $html .= '</ul>';

        if($echo){
            echo $html;
            return true;
        }else {
            return $html;
        }
    }

    public static function getTypes(){
        return Content::getTypes();
    }

    public static function enqueueAssets(){
        $style = StyleHelper::instance();
        $script = ScriptHelper::instance();
        $style->enqueue("medium-editor-css","css/medium-editor.min.css");
        $style->enqueue("medium-editor-theme-css","css/default.min.css");
        $script->enqueue("content-form-scripts","js/content.min.js");
    }
}