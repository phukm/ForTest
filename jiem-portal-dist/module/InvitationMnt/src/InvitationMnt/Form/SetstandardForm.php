<?php
namespace InvitationMnt\Form;

use Zend\Form\Form;

class Setstandardform extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('setstandard');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'ddbRecommenLevel1',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control sslv-select',
                'id' => 'ddbRecommenLevel1',
                'value' => ''
            )
        ));
        
        $this->add(array(
            'name' => 'ddbYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control recmlvl-select-year inset-shadow',
                'id' => 'ddbYear',
                'value' => date("Y")
            ),
            'options' => array(
                'value_options' => $this->setYear()
            )
        ));
        $this->add(array(
            'name' => 'ddbSchoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control recmlvl-select inset-shadow',
                'id' => 'ddbSchoolYear'
            ),
            'options' => array(
                'value_options' => array(
                    '0' => ''
                )
            )
        ));
        $this->add(array(
            'name' => 'hidden',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => ''
            ),
            'options' => array(
                'label' => ''
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
        for ($i = $currentYear + 2; $i >= 2010; $i --) {
            $lst_y[$i] = $i;
        }
        return $lst_y;
    }
}