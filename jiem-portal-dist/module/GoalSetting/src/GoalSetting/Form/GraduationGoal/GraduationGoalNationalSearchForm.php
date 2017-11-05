<?php

namespace GoalSetting\Form\GraduationGoal;

use Zend\Form\Form;

class GraduationGoalNationalSearchForm extends Form {

    public function __construct($name = null) {
        parent::__construct('GraduationGoalNationalSearchForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'GraduationGoalNationalSearchForm');
        $this->add(array(
            'name' => 'ddbOrganization',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow select-width-120',
                'id' => 'ddbOrganization',
                'tabindex' => 6
            ),
            'options' => array(
            )
        ));

        $this->add(array(
            'name' => 'ddbPrefectures',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow select-width-120 disable',
                'id' => 'ddbPrefectures',
                'tabindex' => 7,
                'disabled' => 'disabled'
            ),
            'options' => array(
                ''
            )
        ));

        $this->add(array(
            'name' => 'ddbSchoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow select-width-120',
                'id' => 'ddbSchoolYear',
                'tabindex' => 8
            ),
            'options' => array(
            )
        ));
    }
    public function setListCity($data,$selected='00'){
        $this->get("ddbPrefectures")
        ->setValueOptions($data)->setAttributes(array(
            'value' => $selected,
            'selected' => true
        ));
        return $this;
    }
    public function setListOrg($listOrg,$orgselected=01){
        if(!array_key_exists($orgselected,$listOrg))
            {
                $orgselected=01;
            }
        $this->get("ddbOrganization")
        ->setValueOptions($listOrg)->setAttributes(array(
            'value' => $orgselected,
            'selected' => true
        ));
        return $this;
    }
    public function setListSchoolYear($dataSchoolYear,$SchoolYearselected=0){
        $dataSchoolYear = array();
        $this->get("ddbSchoolYear")
        ->setValueOptions($dataSchoolYear)->setAttributes(array(
            'value' => $SchoolYearselected,
            'selected' => true
        ));
        return $this;
    }
}
