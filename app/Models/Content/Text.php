<?php 
namespace App\Models\Content;

use App\Models\Content;

class Text extends Content {

	const TYPE = 'text';

	public static $sanitized_fields = ['value'];

	protected static $validation_rules = [
		'value'=>'required|string'
	];

	public function getForSearch()
	{
		$out = parent::getForSearch();
		$out['detail'] = substr(strip_tags($this->value),0,50);
		return $out;
	}

	public static function refineSearch($query, $search){
		return $query->orWhere('value', 'LIKE', $search);
	}

}