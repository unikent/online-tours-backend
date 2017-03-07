<?php namespace App\Http\Helpers;

class ScriptHelper extends AssetHelper {
	public function printItem($key, $url){
		return "<script src=\"{$url}\" name=\"{$key}\"></script>\n";
	}
}