<?php namespace App\Http\Helpers;

use Illuminate\Support\Facades\Request;

class LinkHelper {
	public function isActive($route){
        return (Request::is($route . '/*') || Request::is($route)) ? 'active' : '';
	}
}