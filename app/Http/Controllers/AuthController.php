<?php namespace App\Http\Controllers;

use KentAuth\Http\Controllers\AuthController as KentAuthController;

class AuthController extends KentAuthController {

    protected $redirectPath = '/';

	protected function view($view, $options = array()){
        return $this->layout($view, $options);
    }
}