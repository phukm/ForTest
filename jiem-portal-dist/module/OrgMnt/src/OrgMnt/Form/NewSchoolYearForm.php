<?php
namespace OrgMnt\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class NewSchoolYearForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('form');
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'type' => 'hidden'
        ));
        
        $this->add(array(
            'name' => 'displayName',
            'type' => 'Text',
            'options' => array(),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'displayName',
                'maxlength' => 10
            )
        ));
        
        $this->add(array(
            'name' => 'schoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'schoolYear',
                'value' => ''
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Go',
                'id' => 'submitButton'
            )
        ));
    }
}