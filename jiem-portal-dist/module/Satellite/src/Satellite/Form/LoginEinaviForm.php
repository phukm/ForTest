<?php

namespace Satellite\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class LoginEinaviForm extends Form {

    public function __construct() {
        parent::__construct('loginEinavi');

        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'loginEinavi');

        $this->add(array(
            'name' => 'txtMailAdd',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtMailAdd',
                'maxlength' => '256',
                'autocomplete' => 'off',
                'tabindex' => 1
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'txtPassword',
            'type' => 'Zend\Form\Element\Password',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtPassword',
                'placeholder' => '',
                'maxlength' => '32',
                'autocomplete' => 'off',
                'tabindex' => 2
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf'
        ));
    }

}

?>