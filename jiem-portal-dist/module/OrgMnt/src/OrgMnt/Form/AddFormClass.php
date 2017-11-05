<?php
namespace OrgMnt\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class AddFormClass extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('classmanager');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'year',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'year',
                'value' => ''
            )
        )
        );

        $this->add(array(
            'name' => 'school_year_add',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'school_year_add',
                'value' => ''
            )
        ));

        $this->add(array(
            'name' => 'classname',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'classname',
                'class' => 'form-control'
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'sizes',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'sizes',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
    }
}