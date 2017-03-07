<?php
namespace App\Models\Traits;

trait SingleFile
{

    /**
     * Get the path to this Content models associated file
     *
     * @return string
     */
    public function getFilePath(){
        return $this->getMediaPath() . '/' . $this->value;
    }

    /**
     * Get the URI to this Content models associated file
     *
     * @return string
     */
    public function getFileUri(){
        return $this->getMediaUri() . '/' . $this->value;
    }

    /**
     * Get the extension of the file
     *
     * @return string
     */
    public function getExtension(){
        $parts = explode('.',$this->value);
        return array_pop($parts);
    }

}
