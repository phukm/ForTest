<?php
namespace BasicConstruction\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ChangePasswordFirstForm extends Form
{

    public function __construct($name = null, $userEntity)
    {
        parent::__construct();
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'txtUserID',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtUserID',
                'autofocus' => 'autofocus',
                'value' => ! empty($userEntity) ? $userEntity->getUserId() : ""
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtFistname',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtFistname',
                'value' => ! empty($userEntity) ? $userEntity->getFirstNameKanji() : "",
                'placeholder' => '姓'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtlastname',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtlastname',
                'value' => ! empty($userEntity) ? $userEntity->getLastNameKanji() : "",
                'placeholder' => '名'
            ),
            'options' => array()
        )
        );
        
        $this->add(array(
            'name' => 'txtEmailAddress',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtEmailAddress',
                'value' => ! empty($userEntity) ? $userEntity->getEmailAddress() : ""
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'oldPassword',
            'type' => 'Zend\Form\Element\Password',
            'attributes' => array(
                'class' => 'form-control inset-shadow text-size',
                'id' => 'oldPassword',
                'style' => 'width:220px'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'newPassword',
            'type' => 'Zend\Form\Element\Password',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'newPassword',
                'style' => 'width:220px'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'confirmNewPassword',
            'type' => 'Zend\Form\Element\Password',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'confirmNewPassword',
                'style' => 'width:220px'
            ),
            'options' => array()
        ));
    }
}