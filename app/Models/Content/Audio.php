<?php 
namespace App\Models\Content;

use App\Models\Content;
use App\Models\Traits\FileWithVariants;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\File;

class Audio extends Content {

	use FileWithVariants;

	const TYPE = 'audio';

	public static $primary = 'mp3';
	public static $secondary = ['ogg','wav'];


	public static $stripped_fields  = ['name','title'];

	public static $sanitized_fields = ['transcription'];

	protected $hidden_meta = ['variants'];

	protected static $validation_rules = [
		'title'=>'string|max:255',
		'transcription'=>'string',
		'mp3'=>'mimetypes:audio/mp3,application/mpeg,audio/mpeg,audio/mpeg3,audio/x-mpeg-3|max:10000',
		'ogg'=>'mimetypes:audio/ogg,application/ogg,audio/x-ogg,application/x-ogg|max:10000',
		'wav'=>'mimetypes:audio/wav,audio/x-wav,audio,audio/x-pn-wav/wave|max:10000',
		'remove'=>'array|max:2'
	];

	protected $allowed_input = ['title','transcription','mp3','ogg','wav','remove'];

	public function getForSearch()
	{
		$out = parent::getForSearch();
		$out['detail'] = $this->title . ' - ' . substr(strip_tags($this->transcription),0,40);
		return $out;
	}


	public function toArray(){

		$out =  parent::toArray();

		$files = [];
		$possible = array_merge([static::$primary],static::$secondary);
		foreach($possible as $v){
			if(is_file($this->getFilePath($v))){
				$files[$v] = $this->getFileUri($v);
			}
		}
		$out['src'] = $files;

		return $out;
	}


	/**
	 * Extend search to also search `value` (mp3 filename) and `title`
	 *
	 * @param $query \Illuminate\Database\Query\Builder
	 * @param $search string the search string with added wildcard characters
	 * @return \Illuminate\Database\Query\Builder
	 */
	public static function refineSearch($query, $search){
		$query->orWhere('value', 'LIKE', $search);
		$query->orWhere('meta', 'LIKE', '%title":"' . $search . '","transcription%');
		return $query;
	}

	public static function getTypeValidationRules($method){

		$rules = static::$validation_rules;

		switch(strtoupper($method)){
			case 'POST' :
			case 'PUT' :
				$rules['title'] = 'required|' . $rules['title'];
				$rules['mp3'] = 'required|' . $rules['mp3'];
				break;
			case 'PATCH':
				break;
		}

		return $rules;

	}

	public function setMp3Attribute(UploadedFile $audio){

		$existing = [];

		if(!empty($this->value)){$existing[] = static::getMediaPath() .'/'.$this->value;}

		//construct filename
		$ts = time();
		$fn = pathinfo($audio->getClientOriginalName(), PATHINFO_FILENAME) .'_';
		$ext = '.' . $audio->getClientOriginalExtension();

		$filename  = Str::slug($fn) . $ts . $ext;

		//save the file to the correct media directory
		$audio->move(static::getMediaPath(), $filename);

		$this->value = $filename;

		File::delete($existing);
	}

	public function setOggAttribute(UploadedFile $audio){
		$this->handleVariant($audio,'ogg');
	}

	public function setWavAttribute(UploadedFile $audio){
		$this->handleVariant($audio,'wav');
	}

	protected function handleVariant(UploadedFile $audio, $key){

		if(in_array($key,$this::$secondary)){
			$existing = [];
			//construct filename
			$ts = time();
			$fn = pathinfo($audio->getClientOriginalName(), PATHINFO_FILENAME) . '_';
			$ext = '.' . $audio->getClientOriginalExtension();

			$filename = Str::slug($fn) . $ts . $ext;

			//save the file to the correct media directory
			$audio->move(static::getMediaPath(), $filename);


			if (!is_array($this->variants)) {
				$this->variants = [];
			}

			$v = $this->variants;
			if (!empty($v) && isset($v[$key])) {
				$existing[] = static::getMediaPath() .'/'.$v[$key];
			}
			$v[$key] = $filename;
			$this->variants = $v;

			File::delete($existing);
		}

	}

}