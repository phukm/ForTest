<?php

namespace PupilMnt\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class PupilForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('pupilmanager');
    
        $this->setAttribute('method', 'post');
    }
    
    function year( $start, $end, $step = 1)
    {
        $end = date('Y') - 50;
        $start = date('Y');
         
        $range = array();
    
        foreach(range( $start, $end) as $index){
    
            if (!(($index - $end) % $step)) {
                $range[$index] = $index;
            }
        }
        return $range;
    }
    
    public function month()
    {
        $array = array();
        for($i = 1; $i < 13; $i++ ){
            if($i == 1){$array[0] = '';}
            $array[$i] = $i;
        }
        return $array;
    }
    
    public function day()
    {
        $array = array();
        for($i = 1; $i < 32; $i++ ){
            if($i == 1){$array[0] = '';}
            $array[$i] = $i;
        }
        return $array;
    }
}