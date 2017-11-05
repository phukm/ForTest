<?php
namespace Logs\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ApplyEikenSearchForm extends Form{
    public function __construct()
    {
        parent::__construct('applyeikensearch');
        
        $this->add(array(
            'name' => 'organizationNo',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'organizationNo',
                'class' => 'form-control',
                'maxlength' => 255,
                'onkeypress' => 'return APPLYEIKEN_LOGS.isNumber(event)'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'organizationName',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'organizationName',
                'class' => 'form-control',
                'maxlength' => 255
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'action',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'action',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    'create' => '登録',
                    'update' => '更新'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'fromDate',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'fromDate',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'toDate',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'toDate',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
    }
}