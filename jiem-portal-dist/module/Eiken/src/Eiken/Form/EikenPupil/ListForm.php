<?php

namespace Eiken\Form\EikenPupil;
use Zend\Form\Element;
use Zend\Form\Form;
class ListForm extends Form{
    
    public function __construct($name = null){
        
        parent::__construct('listform');
        $this->setAttribute('method', 'post');
        
        
        $this->add(array(
            'name' => 'eikenIdAdd',
            'type' => 'Zend\Form\Element\Button',
            'attributes' => array(
                'id' => 'eikenIdAdd',
                'class' => 'form-control',
                'value' => '英検ID取得生徒を追加'                
                
            )
        ));
        

        $this->add(array(
            'name' => 'newEikenIdAdd',
            'type' => 'Zend\Form\Element\Button',
            'attributes' => array(
                'id' => 'eikenIdAdd',
                'class' => 'form-control',
                'value' => '新規に英検IDを取得している生徒を追加'
        
            )
        ));
        
        $this->add(array(
            'name' => 'sakujo',
            'type' => 'Zend\Form\Element\Button',
            'attributes' => array(
                'id' => 'eikenIdAdd',
                'class' => 'form-control',
                'value' => '削除'
        
            )
        ));
        
        
    }
    
    
    
}