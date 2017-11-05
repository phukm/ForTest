<?php
namespace GoalSetting\Form\GraduationGoal;

use Zend\Form\Form;

class GraduationGoalAcquisitionRateSearchForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('GraduationGoalAcquisitionRateSearchForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'GraduationGoalAcquisitionRateSearchForm');

        $this->add(array(
            'name' => 'ddbYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow btn-small-100',
                'id' => 'ddbYear',
                'required' => 'required',
                'value' => '',
                'tabindex' => 10
            ),
            'options' => array(
                'value_options' => $this->setYear()
            )
        ));

        $this->add(array(
            'name' => 'rdDisplay',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'id' => 'rdDisplay',
                'value' => '',
            ),
            'options' => array(
                'value_options' => array(
                    array(
                        'value' => '0',
                        'attributes' => array(
                            'tabindex' => 11,
                            'class' => 'radio-box',
                            'style' => 'margin-left: -11px;',
                        ),
                        'label' => 'みなし',
                        'label_attributes' => array('class' => 'text-normal')
                    ),
                    array(
                        'value' => '1',
                        'attributes' => array(
                            'tabindex' => 12,
                            'class' => 'radio-box',
                            'style' => 'margin-left: 20px;',
                        ),
                        'label' => '実数',
                        'label_attributes' => array('class' => 'text-normal')
                    )
                )
            )
        ));
    }

    public function setYear()
    {
        if (date("m") < 4) {
            $currentYear = date("Y") - 1;
        } else {
            $currentYear = date("Y");
        }
        $lst_y = array('');
        for ($i = $currentYear + 2; $i >= 2010; $i --) {
            $lst_y[$i] = $i;
        }
        return $lst_y;
    }
}