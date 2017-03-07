<?php namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;

class AjaxOnly {

    /**
     * Create a new filter instance.
     */
	public function __construct()
	{

	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($request->ajax())
		{
            return $next($request);
		}else{
            return response()->json(['success'=>false,'message'=>'Unsupported request'],406);
        }
	}

}
