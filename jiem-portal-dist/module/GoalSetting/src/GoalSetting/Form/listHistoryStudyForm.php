<?php
namespace GoalSetting\Form;

use Zend\Form\Form;

class listHistoryStudyForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('frmStudyGear');
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'frmStudyGear');
        $this->add(array(
            'name' => 'ddlSchoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow spi-select-class width-140',
                'id' => 'ddlSchoolYear',
                'escape' => false,
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));
        $this->add(array(
            'name' => 'ddlClass',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow width-140',
                'id' => 'ddlClass',
                'escape' => false,
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));
        $this->add(array(
            'name' => 'eikenGrade',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow width80',
                'id' => 'eikenGrade',
                'escape' => false,
            ),
            'options' => array(
                'empty_option' => '',
                'value_options' => $this->listlevel()
            )


        ));
        $this->add(array(
            'name' => 'fromDate',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
            	'required' => 'required',
                'class' => 'form-control width-120',
                'id' => 'fromDate'
            )
        ));
        $this->add(array(
            'name' => 'toDate',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
            	'required' => 'required',
                'class' => 'form-control width-120',
                'id' => 'toDate'
            )
        ));
    }

    public function listlevel(){
        $arrclass = array();
        $arrclass[1] = '1級';
        $arrclass[2] = '準1級';
        $arrclass[3] = '2級';
        $arrclass[4] = '準2級';
        $arrclass[5] = '3級';
        $arrclass[6] = '4級';
        $arrclass[7] = '5級';
        return $arrclass;
    }
}