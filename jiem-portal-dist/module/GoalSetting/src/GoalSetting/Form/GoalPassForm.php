<?php
namespace GoalSetting\Form;

use Zend\Form\Form;

class GoalPassForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('GoalPassForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'goalPassForm');
        
        $this->add(array(
            'name' => 'ddbYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control  inset-shadow',
                'id' => 'ddbYear',
                'required' => 'required',
                'value' => ''
            ),
            'options' => array(
                'value_options' => $this->setYear()
            )
        ));
        
        $this->add(array(
            'name' => 'ddbSchoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control inset-shadow',
                'id' => 'ddbSchoolYear',
                'value' => ''
            ),
            'options' => array(
                'value_options' => array(
                    '' => ''
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
        $lst_y = array();
        $lst_y[''] = '';
        for ($i = $currentYear - 1; $i >= 2010; $i --) {
            $lst_y[$i] = $i;
        }
        return $lst_y;
    }
}