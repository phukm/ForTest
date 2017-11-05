<?php
namespace Eiken\Form\EikenId;

use Zend\Form\Form;

class ReferenceForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('refeikenid');
        
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'refeikenid');
        
        $this->add(array(
            'name' => 'txtEikenId',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'txtEikenId',
                'class' => 'form-control',
                'tabindex' => 1,
                'onkeypress' => 'EIKEN_ID.handleKeyPress(event)'
            )
        ));
        
        $this->add(array(
            'name' => 'txtPass',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'txtPass',
                'class' => 'form-control',
                'tabindex' => 2,
                'onkeypress' => 'EIKEN_ID.handleKeyPress(event)'
            )
        ));
        
        $this->add(array(
            'name' => 'txtFirtNameKanji',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtFirtNameKanji',
                'tabindex' => 4,
                'placeholder' => '姓'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtLastNameKanji',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtLastNameKanji',
                'tabindex' => 5,
                'placeholder' => '名'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtFirtNameKana',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtFirtNameKana',
                'tabindex' => 6,
                'placeholder' => '姓'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtLastNameKana',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtLastNameKana',
                'tabindex' => 7,
                'placeholder' => '名'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'rdGender',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'class' => 'padding-radio',
                'id' => 'rdGender',
                'value' => '0',
                'tabindex' => 7,
                'onchange' => "$('input[name=rdGender]').removeClass('errorRadio error');"
            ),
            'options' => array(
                'value_options' => array(
                    '1' => '男',
                    '2' => '女'
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
            ),
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
                'class' => 'form-control width58',
                'id' => 'txtPostalCode1',
                'tabindex' => 11
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtPostalCode2',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width58',
                'id' => 'txtPostalCode2',
                'tabindex' => 12
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtArea',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtArea',
                'tabindex' => 14
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtAreaCode',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtAreaCode',
                'tabindex' => 16
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtTelCode1',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtTelCode1',
                'maxlength' => 13,
                'tabindex' => 18
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtCity',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtCity',
                'tabindex' => 13
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtVillage',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtVillage',
                'tabindex' => 15
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtBuilding',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtBuilding',
                'tabindex' => 17
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtMailAddress',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtMailAddress',
                'tabindex' => 19
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'ddlSchoolYear',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width58',
                'id' => 'ddlSchoolYear',
                'tabindex' => 24
            ),
            'options' => array(
                'value_options' => array()
            )
        ));
        
        $this->add(array(
            'name' => 'ddlClass',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width58',
                'id' => 'ddlClass',
                'tabindex' => 25
            ),
            'options' => array(
                'value_options' => array()
            )
        ));
        $this->add(array(
            'name' => 'ddlJobCode',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlJobCode',
                'tabindex' => 22
            ),
            'options' => array(
                'empty_option' => ''
            )
        ));
        $this->add(array(
            'name' => 'ddlSchoolCode',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlSchoolCode',
                'tabindex' => 23
            ),
            'options' => array(
                'empty_option' => ''
            )
        ));
        $this->add(array(
            'name' => 'hidden-eiken-id',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'hidden-eiken-id',
                'type' => 'hidden'
            )
        ));
        $this->add(array(
            'name' => 'hidden-eiken-pass',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'hidden-eiken-pass',
                'type' => 'hidden'
            )
        ));
    }
}

?>