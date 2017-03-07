<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class DatabaseConnection {

    public function handle($request, Closure $next)
    {
		$connection = $request->route()->parameters()['connection'];
        if(App::environment()=='testing'){
            if(!preg_match('/^test_.*/',$connection)){
                $connection = 'test_' . $connection;
            }
        }
    	if(in_array($connection, array_keys(Config::get('database.connections')))){
		    DB::setDefaultConnection($connection);
    	}

        return $next($request);
    }
}