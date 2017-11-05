<?php
namespace InvitationMnt\Form;

use Zend\Form\Form;

class SetstandardlevellistForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('setstandardlevellist');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'ddbSchoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control recmlvl-select inset-shadow',
                'id' => 'ddbSchoolYear'
            ),
            'options' => array(
                'value_options' => array(
                    '0' => '',
                    '1' => '小学校１年相当',
                    '2' => '小学校2年相当',
                    '3' => '小学校3年相当',
                    '4' => '小学校4年相当',
                    '5' => '小学校5年相当',
                    '6' => '小学校6年相当',
                    '7' => '中学校1年相当',
                    '8' => '中学校2年相当',
                    '9' => '中学校3年相当',
                    '10' => '高校1年相当',
                    '11' => '高校2年相当',
                    '12' => '高校3年相当',
                    '13' => '大学1年相当',
                    '14' => '大学2年相当',
                    '15' => '大学3年相当',
                    '16' => '大学4年相当'
                )
            )
        ));
        $this->add(array(
            'name' => 'ddbYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control recmlvl-select-year inset-shadow',
                'id' => 'ddbYear'
            ),
            'options' => array(
                'value_options' => $this->year()
            )
        ));
    }

    public function year()
    {
        if (date("m") < 4) {
            $currentYear = date("Y") - 1;
        } else {
            $currentYear = date("Y");
        }
        $lst_y = array();
        $lst_y[''] = '';
        for ($i = $currentYear + 2; $i >= 2010; $i --) {
            $lst_y[$i] = $i;
        }
        return $lst_y;
    }
}