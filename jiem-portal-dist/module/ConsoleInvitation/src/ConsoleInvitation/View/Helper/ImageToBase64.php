<?php
namespace ConsoleInvitation\View\Helper;

class ImageToBase64 extends \Zend\View\Helper\AbstractHelper {
    
    protected $imagePath;
    
    public function imageToBase64($imagePath){
        
        if(!file_exists($imagePath)){
            $imagePath = $this->imagePath.'/'.ltrim($imagePath,'/');
        }
        
        if(!file_exists($imagePath)){
            return null;
        }
        
        $type = pathinfo($imagePath, PATHINFO_EXTENSION);
        $data = file_get_contents($imagePath);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
    
    public function setImagePath($path){
        $this->imagePath = $path;
    }
    
    public function __invoke($imagePath) {
        return $this->imageToBase64($imagePath);
    }
}
