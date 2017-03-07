<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Config;

use Illuminate\Support\Facades\Request;

abstract class Controller extends BaseController {

	use DispatchesJobs, ValidatesRequests;

    public function layout($view, $data = array()){
        return view(Config::get('view.layout'), $data)->nest('content', $view, $data);
    }


    public function error($message,$code=404){
        if(Request::ajax() || Request::format() == 'json'){
            return response()->json(['success'=>false,'message'=>$message],$code);
        }else{
            return response(view('errors.generic', array('message' => $message)), $code);
        }
    }

}
