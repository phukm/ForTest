<?php
namespace Satellite\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class LoginForm extends Form
{

    public function __construct()
    {
        parent::__construct();
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'organizationNo',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'organizationNo',
                'placeholder' => '',
                'class' => 'form-control',
                'autofocus' => 'autofocus',
                'autocomplete' => 'off',
                'maxlength' => 10
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'authenKey',
            'type' => 'Zend\Form\Element\Password',
            'attributes' => array(
                'id' => 'authenKey',
                'placeholder' => '',
                'autocomplete' => 'off',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
    }
}
