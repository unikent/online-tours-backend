<?php namespace App\Http\Requests;

use App\Models\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Input;

class ContentPersistRequest extends FormRequest {

    private static $banned_tags_and_contents = '<script><style>';
    private static $allowed_tags = '<p><h3><h4><strong><em><a><ul><ol><li>';
    private static $replace_tags = [
        'b'=>'strong',
        'i'=>'em',
        'h1'=>'h3',
        'h2'=>'h4'
    ];

    private $type = null;
    private $content_class = null;

    public function __construct()
    {
        parent::__construct();

        if(Input::has('type')) {
            $type = Input::get('type');
        } else {
            $cont = Content::find(Route::current()->parameters()['content']);
            $type = $cont ? $cont->type : null;
        }
        if($type) {
            /** @var $class Content */
            $class = 'App\\Models\\Content\\' . ucfirst($type);

            $instance = new $class;
            $valid_fields = $instance->getAllowedInput(Input::method());

            $sanitized_fields = $class::$sanitized_fields;
            $stripped_fields = $class::$stripped_fields;

            $this->type = $type;
            $this->content_class = $class;

           
            // Replace Input array of values that appear in the valid list
            Input::replace( array_intersect_key(Input::all(), array_flip($valid_fields)) );

            foreach ($sanitized_fields as $field) {
                if (Input::has($field)) {
                    Input::merge([$field => self::sanitize_html(Input::get($field))]);
                }
            }

            foreach ($stripped_fields as $field) {
                if (Input::has($field)) {
                    Input::merge([$field => self::strip_html(Input::get($field))]);
                }
            }
        }
    }



    private static function strip_html($html){
        $html = self::strip_tags_content($html,self::$banned_tags_and_contents);
        $html = strip_tags($html);
        return $html;
    }

    private static function sanitize_html($html){
        $html = self::strip_tags_content($html,self::$banned_tags_and_contents);
        foreach(self::$replace_tags as $from=>$to){
            $html = preg_replace(['/<'.$from .'>/','/<\/'.$from .'>/'],['<'.$to.'>','</'.$to.'>'],$html);
        }
        $html = strip_tags($html,self::$allowed_tags);
        if(preg_match('/^(<p>)*(<\/p>)*$/',$html)){
            $html='';
        }
        return $html;
    }

    private static function strip_tags_content($text, $tags = '') {

        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
        $tags = array_unique($tags[1]);

        if(is_array($tags) AND count($tags) > 0) {
            $text = preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
        }
        return $text;
    }

	/**
     * Validation rules.
     * Rules are automatically grabbed from the relevent content types Model. If type is not found, throw missing "type" error.
     */
    public function rules()
	{	

		// If type could not be resolved, throw error (or at least show type is missing)
		if(!$this->content_class){
			return ['type' => 'required'];
		}

	 	$class = $this->content_class;
	 	return $class::getValidationRules(Input::method());
	}

	// No auth at this point
    public function authorize()
    {
        return true; //handled by middleware
    }

}
