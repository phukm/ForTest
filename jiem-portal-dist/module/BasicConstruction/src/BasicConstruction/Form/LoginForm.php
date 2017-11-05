<?php
namespace BasicConstruction\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class LoginForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct();
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'orgNo',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'orgNo',
                'class' => 'form-control',
                'autofocus' => 'autofocus',
                'maxlength' => 10
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'userId',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'userId',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'password',
            'type' => 'Zend\Form\Element\Password',
            'attributes' => array(
                'id' => 'password',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
    }
}