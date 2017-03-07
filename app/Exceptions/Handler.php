<?php namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException'
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
		return parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e){
		$code = 500;
		$message = 'Internal Server Error'; 

		if($e instanceof ModelNotFoundException){
			$code = 404;
			$message = 'Not Found';

	        if(Request::ajax() || Request::format() == 'json'){
	            return response()->json([ 'success'=>false, 'message' => $message ], $code);
	        } else {
            	return response(view('errors.generic', array('message' => $message)), $code);
	        }
		}

        if($e instanceof TokenMismatchException){
            $code = 422;
            $message = 'Invalid form submission. If issue persists please logout and back in.';

            if(Request::ajax() || Request::format() == 'json'){
                return response()->json(['success'=>false, 'message' => $message ], $code);
            } else {
                return response(view('errors.generic', array('message' => $message)), $code);
            }
        }

        if(Config::get('app.debug')){
            return parent::render($request, $e);
        } else {
            if (Request::ajax() || Request::format() == 'json') {
                return response()->json(['success'=>false, 'message' => $message], $code);
            } else {
                return response(view('errors.generic', array('message' => $message)), $code);
            }
        }

	}

}
