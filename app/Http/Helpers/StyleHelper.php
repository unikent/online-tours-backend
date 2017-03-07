<?php namespace App\Http\Helpers;

class StyleHelper extends AssetHelper {
	public function printItem($key, $url){
		return "<link href=\"{$url}\" name=\"{$key}\" rel=\"stylesheet\">\n";
	}
}