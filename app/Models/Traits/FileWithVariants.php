<?php namespace App\Models\Traits;

trait FileWithVariants
{
    /**
     * Get the path to the file associated to the provided key
     *
     * @param $key string the key of a file
     * @return string
     */
    public function getFilePath($key)
    {
        $filename = $this->getFilename($key);
        if($filename) {
            return $this->getMediaPath() . '/' . $filename;
        }else{
            return false;
        }
    }

    /**
     * Get the URI to the file associated to the provided key
     *
     * @param $key string the key of a file
     * @return string
     */
    public function getFileUri($key)
    {
        $filename = $this->getFilename($key);
        if($filename) {
            return $this->getMediaUri() . '/' . $filename;
        }else{
            return false;
        }
    }

    /**
     * Get the filename of the file associated to the provided key
     *
     * @param $key string the key of a file
     * @return string|bool
     */
    public function getFileName($key)
    {
        if ($key == static::$primary) {
            return $this->value;
        } else {
            $v = (array)$this->variants;
            if (array_key_exists($key, $v)) {
                return $v[$key];
            } else {
                return false;
            }
        }
    }

}