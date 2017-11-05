<?php
namespace IBA\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class SearchIBAOrgForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('searchibaorg');
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'ddlTestName',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'ddlTestName',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => ''
                )
            )
        ));
        
        $this->add(array(
            'name' => 'ddlYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'ddlYear',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    '2015' => '2015'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'ddlKai',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'ddlKai',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => ''
                )
            )
        ));
        
        $this->add(array(
            'name' => 'textdatetime1',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'textdatetime1',
                'class' => 'form-control input-date-1'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'textdatetime2',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'textdatetime2',
                'class' => 'form-control input-date-2'
            ),
            'options' => array()
        ));
    }
}