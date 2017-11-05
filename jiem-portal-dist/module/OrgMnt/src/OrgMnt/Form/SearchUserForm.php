<?php
namespace OrgMnt\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class SearchUserForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('SearchUser');
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtUserId',
                'class' => 'form-control user-id'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'name',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtUserName',
                'class' => 'form-control user-id txtUserName'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'Role',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'role'
            ),
            'options' => array(
                'label' => '',
                'value_options' => array(
                    '0' => ' '
                )
            )
        ));
    }
}