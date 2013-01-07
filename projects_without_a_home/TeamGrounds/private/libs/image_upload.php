<?php

class ImageUpload {
    public $image;
    public $image_source;
    public $image_type;
    
    public $image_quality = false;
    public $image_filters = false;
    
    protected function __construct($image, $type) {
        $this->image = $image;
        $this->image_source = $image;
        $this->image_type = $type;
    }
    
    static public function Create($upload_field, &$error, $__CLASS__ = __CLASS__) {
        if(empty($_FILES[$upload_field]) || $_FILES[$upload_field]['error']) {
            $error = 'No file was uploaded.';
            return false;
        }
        
        $file = $_FILES[$upload_field]['tmp_name'];
        
        if(!is_uploaded_file($file)) {
            $error = 'Invalid file uploaded.';
            return false;
        }
        
        $image = false;
        switch(exif_imagetype($file)) {
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($file);
                $type = 'gif';
                break;
            
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file);
                $type = 'jpeg';
                break;
            
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file);
                $type = 'png';
                break;
        }
        
        if(!$image) {
            $error = 'Invalid image uploaded. Image must either be a gif, jpg,
                or png.';
            
            return false;
        }
        
        return new $__CLASS__($image, $type);
    }
    
    public function Resize($width, $height) {
        $source_width = imagesx($this->image_source);
        $source_height = imagesy($this->image_source);
        if(($source_width == $width) && ($source_height == $height))
            return true; //nothing to do
        
        $this->image = imagecreatetruecolor($width, $height);
        
        return imagecopyresampled($this->image, $this->image_source, 0, 0, 0, 0,
            $width, $height, $source_width, $source_height);
    }
    
    /**
     * @credit http://911-need-code-help.blogspot.com/2008/10/resize-images-using-phpgd-library.html
     */
    public function ResizeFitRatio($width, $height, $shrink_only = false, $min_width = 5, $min_height = 5) {
        $source_width = imagesx($this->image_source);
        $source_height = imagesy($this->image_source);
        
        if((($source_width == $width) && ($source_height == $height))
            || ($shrink_only && ($source_width < $width) && ($source_height < $height)))
            return true; //nothing to do
        
        $source_aspect_ratio = $source_width / $source_height;
        $new_aspect_ratio = $width / $height;
        
        if($new_aspect_ratio > $source_aspect_ratio)
            $width = (int)($height * $source_aspect_ratio);
        else
            $height = (int)($width / $source_aspect_ratio);
        
        $width = max($width, $min_width);
        $height = max($height, $min_height);
        
        $this->image = imagecreatetruecolor($width, $height);
        
        return imagecopyresampled($this->image, $this->image_source, 0, 0, 0, 0,
            $width, $height, $source_width, $source_height);
    }
    
    public function ConvertToGIF() {
        $this->image_type = 'gif';
    }
    
    public function ConvertToJPEG($quality = false) {
        $this->image_type = 'jpeg';
        
        $this->image_quality = $quality;
    }
    
    public function ConvertToPNG($quality = false, $filters = false) {
        $this->image_type = 'png';
        
        $this->image_quality = $quality;
        $this->image_filters = $filters;
    }
    
    public function Write($path) {
        $params = array($this->image, $path);
        
        if($this->image_quality !== false)
            $params[] = $this->image_quality;
        
        if($this->image_filters !== false)
            $params[] = $this->image_filters;
        
        return call_user_func_array('image'.$this->image_type, $params);
    }
}