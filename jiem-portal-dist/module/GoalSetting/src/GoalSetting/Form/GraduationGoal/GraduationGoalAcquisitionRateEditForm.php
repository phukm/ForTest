<?php

namespace GoalSetting\Form\GraduationGoal;

use Zend\Form\Form;

class GraduationGoalAcquisitionRateEditForm extends Form {

    public function __construct($name = null) {
        parent::__construct('GraduationGoalAcquisitionRateEditForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'GraduationGoalAcquisitionRateEditForm');

        $this->add(array(
            'name' => 'ddbKyuYearGoal',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow w-80',
                'id' => 'ddbKyuYearGoal',
                'required' => 'required',
                'value' => '',
                'tabindex' => 3
            ),
            'options' => array(
                'value_options' => array('',
                    '1級',
                    '準1級',
                    '2級',
                    '準2級',
                    '3級',
                    '4級',
                    '5級')
            )
        ));

        $this->add(array(
            'name' => 'txtYearGoal',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtYearGoal',
                'required' => 'required',
                'value' => '',
                'tabindex' => 3
            )
        ));
    }

    public function setYear() {
        if (date("m") < 4) {
            $currentYear = date("Y") - 1;
        } else {
            $currentYear = date("Y");
        }
        $lst_y = array('');
        for ($i = $currentYear; $i >= 2010; $i --) {
            $lst_y[$i] = $i;
        }
        return $lst_y;
    }

}
