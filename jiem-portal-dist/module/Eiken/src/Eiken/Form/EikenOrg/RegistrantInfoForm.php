<?php
namespace Eiken\Form\EikenOrg;

use Zend\Form\Form;

class RegistrantInfoForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('registrantInfoForm');
        $this->add(array(
            'name' => 'txtFirstName',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'txtFirstName',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => ''
            )
        ));
        $this->add(array(
            'name' => 'txtLastName',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'txtLastName',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => ''
            )
        ));
        $this->add(array(
            'name' => 'txtEmailAddress',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'txtEmailAddress',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => ''
            )
        ));
        $this->add(array(
            'name' => 'txtPhoneNumber',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'txtPhoneNumber',
                'class' => 'form-control'
            )
            // 'pattern' => '^0[1-68]([-. ]?[0-9]{2}){4}$'
            ,
            'options' => array(
                'label' => ''
            )
        ));
    }
}