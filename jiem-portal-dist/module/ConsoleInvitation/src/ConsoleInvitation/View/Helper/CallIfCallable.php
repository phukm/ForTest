<?php
namespace ConsoleInvitation\View\Helper;

class CallIfCallable extends \Zend\View\Helper\AbstractHelper {
    
    public function __invoke($source,$callMethod) {
        if(!is_array($callMethod)){
            $callMethod = array($callMethod);
        }
        foreach ($callMethod as $method){
            if(method_exists($source, $method)){
                $source = $source->{$method}();
            }
            else{
                return (gettype($source)== 'object') ? null : $source;
            }
        }
        return $source;
    }
}
