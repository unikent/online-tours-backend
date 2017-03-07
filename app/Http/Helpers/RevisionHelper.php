<?php
namespace App\Http\Helpers;

class RevisionHelper{

    public static function getLastSynced($format=null){
		date_default_timezone_set('Europe/London');
		
        $files = array();
        foreach (glob(storage_path() . "/app/seeds/__[0-9]*.sql") as $file) {
            $ts = str_replace(storage_path() . "/app/seeds/__",'',$file);
            $ts = str_replace('.sql','',$ts);

            $files[$ts] = $file;
        }
        ksort($files,SORT_NUMERIC);
        $timestamp = array_pop($files);
        $timestamp = str_replace(storage_path() . "/app/seeds/__",'',$timestamp);
        $timestamp = str_replace('.sql','',$timestamp);

        if($format){
            if($timestamp > 0 ) {
                return date($format, (int)$timestamp);
            }else{
                return false;
            }
        }else{
            return $timestamp;
        }
    }

}
