<?php

namespace History\Form\Eiken;

use Zend\Form\Form;
use Zend\Form\Element;

class ExamHistoryListForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('historylist');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'year',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'year',
                'value' => ''
            )
        ));

        $this->add(array(
            'name' => 'orgSchoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'orgSchoolYear',
                'value' => ''
            )
        ));

        $this->add(array(
            'name' => 'classj',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'classj',
                'value' => ''
            ),
            'options' => array(
                'value_options' => array(
                    '' => ''
                )
            )
        ));

        $this->add(array(
            'name' => 'name',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'name'
            )
        ));
    }

}
