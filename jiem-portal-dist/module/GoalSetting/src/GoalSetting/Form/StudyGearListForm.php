<?php
namespace GoalSetting\Form;

use Zend\Form\Form;

class StudyGearListForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('frmStudyGear');
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'frmStudyGear');
        $this->add(array(
            'name' => 'ddlYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow spi-year width-100',
                'id' => 'ddlYear',
                'value' => ''
            ),
            'options' => array(
            )
        ));

        $this->add(array(
            'name' => 'ddlSchoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow spi-select-class width-140',
                'id' => 'ddlSchoolYear'
            ),'options' => array('empty_option' => ''
            )
        ));

        $this->add(array(
            'name' => 'ddlClass',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow width-140',
                'id' => 'ddlClass'
            ),'options' => array('empty_option' => ''
            )
        ));

        $this->add(array(
            'name' => 'txtFullName',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width-250',
                'id' => 'txtFullName'
            )
        ));
    }


}