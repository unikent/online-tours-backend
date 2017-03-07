<?php 
namespace App\Models\Content;

use App\Models\Content;

class Video extends Content {

	const TYPE = 'video';

	public static $stripped_fields = ['name','title'];

	protected static $validation_rules = [
		'value'=>'url'
	];

	protected $allowed_input = ['title'];

	public static function getTypeValidationRules($method){

		$rules = static::$validation_rules;

		switch(strtoupper($method)){
			case 'POST' :
			case 'PUT' :
				$rules['value'] = 'required|' . $rules['value'];
				break;
			case 'PATCH':
				break;
		}

		return $rules;

	}
}