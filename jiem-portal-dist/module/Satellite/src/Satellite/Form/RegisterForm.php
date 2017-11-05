<?php

namespace Satellite\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class RegisterForm extends Form {

    public function __construct() {
        parent::__construct('getEinaviId');

        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'getEinaviId');

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
                'autocomplete' => 'off',
                'maxlength' => '32',
                'tabindex' => 2
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'txtPassword2',
            'type' => 'Zend\Form\Element\Password',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtPassword2',
                'maxlength' => '32',
                'placeholder' => '',
                'tabindex' => 3
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'txtFirstName',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtFirstName',
                'placeholder' => '名',
                'maxlength' => '18',
                'tabindex' => 4
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'txtLastName',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtLastName',
                'placeholder' => '姓',
                'maxlength' => '18',
                'tabindex' => 5
            ),
            'options' => array()
        ));


        $this->add(array(
            'name' => 'rdSex',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'id' => 'rdSex',
                'value' => '',
            ),
            'options' => array(
                'value_options' => array(
                    array(
                        'value' => '0',
                        'attributes' => array(
                            'tabindex' => 6,
                            'class' => 'radio-box',
                        ),
                        'label' => '男',
                        'label_attributes' => array('class' => 'text-normal')
                    ),
                    array(
                        'value' => '1',
                        'attributes' => array(
                            'tabindex' => 7,
                            'class' => 'radio-box',
                        ),
                        'label' => '女',
                        'label_attributes' => array('class' => 'text-normal')
                    )
                )
            )
        ));

        $this->add(array(
            'name' => 'ddlYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlYear',
                'tabindex' => 8
            ),
            'options' => array(
                'value_options' => array()
            )
        ));

        $this->add(array(
            'name' => 'ddlMonth',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlMonth',
                'tabindex' => 9
            )
            ,
            'options' => array(
                'value_options' => array()
            )
        ));

        $this->add(array(
            'name' => 'ddlDay',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlDay',
                'tabindex' => 10
            ),
            'options' => array(
                'value_options' => array()
            )
        ));

        $this->add(array(
            'name' => 'txtPostalCode1',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control postcode',
                'id' => 'txtPostalCode1',
                'maxlength' => '3',
                'tabindex' => 11
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'txtPostalCode2',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control postcode',
                'id' => 'txtPostalCode2',
                'maxlength' => '4',
                'tabindex' => 12
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'rdReceive',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'id' => 'rdReceive',
                'value' => '0'
            ),
            'options' => array(
                'value_options' => array(
                    array(
                        'value' => '0',
                        'attributes' => array(
                            'tabindex' => 13,
                            'class' => 'radio-receive',
                        ),
                        'class' => 'radio-box',
                        'label' => ' はい',
                        'label_attributes' => array('class' => 'lblReceive')
                    ),
                    array(
                        'value' => '1',
                        'attributes' => array(
                            'tabindex' => 14,
                            'class' => 'radio-receive',
                        ),
                        'label' => ' いいえ',
                        'label_attributes' => array('class' => 'lblReceive')
                    )
                )
            )
        ));

        $this->add(array(
            'name' => 'chkAgree',
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'attributes' => array(
                'required' => 'required',
                'value' => 'true',
                'tabindex' => 15,
                'class' => "checkbox-policy-agree",
                'id' => 'chkAgree'
            )
            ,
            'options' => array(
                'label' => '',
                'value_options' => array(
                    'value' => ''
                )
            )
        ));

        $this->add(array(
            'name' => 'txtParent',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtParent',                
                'autocomplete' => 'off',
                'placeholder' => '例）英語 太郎'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf'
        ));
    }

    public function year() {
        $array = array();
        for ($i = 1990; $i < 2020; $i ++) {
            if ($i == 1990) {
                $array [0] = '';
            }
            $array [$i] = $i;
        }
        return $array;
    }

    public function month() {
        $array = array();
        for ($i = 1; $i < 13; $i ++) {
            if ($i == 1) {
                $array [0] = '';
            }
            $array [$i] = $i;
        }
        return $array;
    }

    public function day() {
        $array = array();
        for ($i = 1; $i < 32; $i ++) {
            if ($i == 1) {
                $array [0] = '';
            }
            $array [$i] = $i;
        }
        return $array;
    }

}

?>