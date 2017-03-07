<?php namespace App\Exceptions;

class UnresolvableContentTypeException extends \Exception{

    protected $message = 'Unable to resolve Content Type';
    protected $code = 500;

}