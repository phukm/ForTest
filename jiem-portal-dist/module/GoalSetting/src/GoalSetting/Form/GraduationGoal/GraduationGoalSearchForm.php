<?php
namespace GoalSetting\Form\GraduationGoal;

use Zend\Form\Form;

class GraduationGoalSearchForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('GraduationGoalSearchForm');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'rdGoalSetting',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'id' => 'rdGoalSetting',
                'value' => '0',
            ),
            'options' => array(
                'value_options' => array(
                    array(
                        'value' => '0',
                        'attributes' => array(
                            'tabindex' => 1,
                            'class' => 'radio-box',
                        ),
                        'label' => '卒業時目標',
                        'label_attributes' => array('class' => 'text-normal')
                    ),
                    array(
                        'value' => '1',
                        'attributes' => array(
                            'tabindex' => 2,
                            'class' => 'radio-box',
                            'style' => 'margin-left: 20px;',
                        ),
                        'label' => '年度目標',
                        'label_attributes' => array('class' => 'text-normal')
                    )
                )
            )
        ));
        
        


        $this->add(array(
            'name' => 'ddbYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow btn-small-100',
                'id' => 'ddbYear',
                'value' => '',
                'tabindex' => 3
            )
        ));
    }
    public function setListYear($YearId = '')
    {
        $this->get("ddbYear")
        ->setValueOptions($this->setYear())
        ->setAttributes(array(
            'value' => $YearId,
            'selected' => true
        ));
        return $this;
    }
    public function setYear()
    {
        if (date("m") < 4) {
            $currentYear = date("Y") - 1;
        } else {
            $currentYear = date("Y");
        }
        for ($i = $currentYear + 2; $i >= 2010; $i --) {
            $lst_y[$i] = $i;
        }
        return $lst_y;
    }
}