<?php namespace App\Http\Helpers;

abstract class AssetHelper {

	// Allow use as singleton
	protected static $instance = array();

	public static function instance(){
		$classname = get_called_class();
		if(isset(static::$instance[$classname])){
			return static::$instance[$classname];
		}
		return static::$instance[$classname] = new static();
	} 

	// Store queued assets
	protected $assets = array();

	// Add asset to queue
	public function enqueue($name, $path){
		return ($this->assets[$name] = asset($path));
	}

	// Remove asset from queue by name
	public function dequeue($name){
		unset($this->assets[$name]);
	}

	// Get queued assets
	public function getQueued(){
		return $this->assets;
	}

	// Get queued assets
	public function isQueued($name){
		return isset($this->assets[$name]);
	}

	// Format for html output
	public function printItem($key, $url){}

	// to string
	public function __tostring(){
		$output = '';
		foreach(array_reverse($this->assets) as $key => $url){
			$output .= $this->printItem($key, $url);
		}
		return $output;
	}
}

