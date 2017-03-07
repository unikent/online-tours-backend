<?php

/**
 * Utility method, like factory(), for getting raw attributes
 */ 
function attributes()
{
    $factory = app('Illuminate\Database\Eloquent\Factory');
    $arguments = func_get_args();
    if (isset($arguments[1]) && is_string($arguments[1])) {
        return $factory->raw($arguments[0], [], $arguments[1]);
    } 

    elseif (isset($arguments[1]) && is_array($arguments[1])){
        return $factory->raw($arguments[0], $arguments[1], (isset($arguments[2]) ? $arguments[2] : 'default'));
    } 

    else {
        return $factory->raw($arguments[0]);
    }
}