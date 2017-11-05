<?php
namespace InvitationMnt\Form;

use Zend\Form\Form;

class GenerateForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('');
        
        $this->setAttributes(array(
            'method' => 'post',
            'id' => 'generateForm',
            'name' => 'generateForm'
        ));
        
        $this->add(array(
            'name' => 'ddbYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control recmlvl-select-year inset-shadow',
                'id' => 'ddbYear',
                'value' => date("Y")
            ),
            'options' => array(
                'value_options' => $this->setYear()
            )
        ));
        
        $this->add(array(
            'name' => 'ddbKai',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control recmlvl-select-year inset-shadow',
                'id' => 'ddbKai'
            )
        ));
        
        $this->add(array(
            'name' => 'ddbSchoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control recmlvl-select-year inset-shadow',
                'id' => 'ddbSchoolYear'
            )
        ));
        
        $this->add(array(
            'name' => 'ddbClass',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control recmlvl-select-year inset-shadow',
                'id' => 'ddbClass'
            )
        ));
        
        $this->add(array(
            'name' => 'txtNameKanji',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control'
            )
        ));
        $this->add(array(
            'name' => 'template1',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control  sslv-select',
                'id' => 'template1',
                'value' => ''
            )
        ));
        
        $this->add(array(
            'name' => 'message1',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'class' => 'form-control ',
                'id' => 'message1'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'template2',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control  sslv-select',
                'id' => 'template2',
                'value' => ''
            )
        ));
        
        $this->add(array(
            'name' => 'message2',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'message2'
            ),
            'options' => array()
        ));
    }

    public function setYear()
    {
        $currentYear = date("Y");
        $lst_y = array();
        for ($i = $currentYear + 2; $i >= 2010; $i --) {
            $lst_y[$i] = $i;
        }
        return $lst_y;
    }
}