<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

class JsonOutput {

    public function handle($request, Closure $next)
    {
        // Request now wishes for JSON whether it wants to or not.
        $request->headers->set('Accept', 'application/json');

        // Perform action
        return $next($request);
    }
}