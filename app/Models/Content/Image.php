<?php 
namespace App\Models\Content;

use App\Models\Traits\SingleFile;
use App\Models\Content;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\Facades\Image as Graphic;
use Illuminate\Support\Facades\File;

class Image extends Content {

	use SingleFile;

	const TYPE = 'image';

	public static $stripped_fields = ['name','caption','copyright'];

	protected static $validation_rules = [
		'caption' => 'string|max:255',
		'copyright'=>'string|max:255',
		'img'=>'image|max:10000'
	];

	protected $allowed_input = ['caption','copyright','img'];

	protected $hidden_meta = ['title'];

	public function toArray(){

		$out =  parent::toArray();
		
		$files = [];
		foreach(array_keys(Config::get('image.sizes')) as $v){
			if(is_file($this->getPath($v))){
				$files[$v] = $this->getUri($v);
			}
		}
		$files['full']  = $this->getFileUri();
		$out['src'] = $files;

		return $out;
	}


	public function getForSearch()
	{
		$out = parent::getForSearch();
		$out['detail'] = $this->caption . '  (' . $this->value .')';
		$out['thumb'] = $this->getUri('thumb');
		return $out;
	}
	
	public static function refineSearch($query, $search){
		$query->orWhere('value', 'LIKE', $search);
		$query->orWhere('meta', 'LIKE', '%caption":"' . $search . '","copyright%');
		return $query;
	}




	/**
	 * Get the path to the image file of the requested size
	 *
	 * @param $size string the size of image to retrieve, must exist in config image.sizes
	 * @return string
	 */
	public function getPath($size){

		if($size=='full'){
			return $this->getFilePath();
		}else {

			if (array_key_exists($size, Config::get('image.sizes'))) {

				$parts = explode('.', $this->value);
				array_pop($parts);

				return static::getMediaPath() . '/' . implode('.', $parts) . '_' . $size . '.' . $this->getExtension();

			} else {

				return false;
			}

		}
	}

	/**
	 * Get the URI to the image file of the requested size
	 *
	 * @return string size of image to retrieve
	 */
	public function getUri($size){
		if($size=='full'){
			return $this->getFileUri();
		}else {
			if(array_key_exists($size,Config::get('image.sizes'))){
				$parts = explode('.',$this->value);
				array_pop($parts);
				return static::getMediaUri() . '/' . implode('.',$parts) . '_' . $size . '.' . $this->getExtension();
			}else{
				return false;
			}
		}
	}



	public static function getTypeValidationRules($method){

		$rules = static::$validation_rules;

		switch(strtoupper($method)){
			case 'POST' :
			case 'PUT' :
				$rules['caption'] = 'required|' . $rules['caption'];
				$rules['copyright'] = 'required|' . $rules['copyright'];
				$rules['img'] = 'required|' . $rules['img'];
				break;
			case 'PATCH':
				break;
		}

		return $rules;

	}


	public function setImgAttribute(UploadedFile $image)
	{
		$existing = [];
		if (!empty($this->value) && is_file($this->getFilePath())) {
			$existing[] = $this->getFilePath();
			foreach (array_keys(Config::get('image.sizes')) as $v) {
				$existing[] = $this->getPath($v);
			}
		}

		$ts = time();
		$fn = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME) . '_';
		$ext = '.' . $image->getClientOriginalExtension();

		$filename = $fn . $ts . $ext;
		$path = static::getMediaPath() . '/' . $filename;

		$graphic = Graphic::make($image->getRealPath());

		$this->width = $graphic->width();
		$this->height = $graphic->height();
		$this->value = $filename;

		$graphic->save($path);
		$graphic->backup();

		foreach (Config::get('image.sizes') as $k => list($width, $height)) {
			$sizepath = static::getMediaPath() . '/' . $fn . $ts . '_' . $k . $ext;
			$graphic->fit($width, $height)->save($sizepath);
			$graphic->reset();
		}
		File::delete($existing);
	}

}