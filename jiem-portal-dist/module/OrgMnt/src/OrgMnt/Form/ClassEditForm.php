<?php
namespace OrgMnt\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ClassEditForm extends Form
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
        ));
        
        $this->add(array(
            'name' => 'school_year',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'school_year',
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'classname',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'classname',
                'class' => 'form-control',
                'value' => ''
            )
        ));
        
        $this->add(array(
            'name' => 'sizes',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'sizes',
                'class' => 'form-control',
                'value' => ''
            )
        ));
    }
}