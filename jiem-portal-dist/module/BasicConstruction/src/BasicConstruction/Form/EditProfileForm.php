<?php
namespace BasicConstruction\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class EditProfileForm extends Form
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
                // 'required' => 'required',
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
                'autofocus' => 'autofocus',
                // 'required' => 'required',
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
                // 'required' => 'required',
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
                // 'required' => 'required',
                'value' => ! empty($userEntity) ? $userEntity->getEmailAddress() : ""
            ),
            'options' => array()
        ));
    }
}