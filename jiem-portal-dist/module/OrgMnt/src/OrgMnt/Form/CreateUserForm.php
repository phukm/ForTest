<?php
namespace OrgMnt\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class CreateUserForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('createuser');
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'txtOrgNumber',
            'type' => 'Zend\Form\Element\Text',
            
            'attributes' => array(
                'id' => 'txtOrgNumber',
                'class' => 'form-control'
            ),
            // 'required' => true,
            
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtUserID',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtUserID',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtFullName',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtFullName',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtFistname',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtFistname',
                'class' => 'form-control',
                'placeholder' => '姓'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtlastname',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtlastname',
                'class' => 'form-control',
                'placeholder' => '名'
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'txtEmailAddress',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtEmailAddress',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtPassword',
            'type' => 'Zend\Form\Element\Password',
            'attributes' => array(
                'id' => 'txtPassword',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'Role',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'ddlRole',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => '',
                'value_options' => array(
                    '0' => ' '
                )
            )
        ));
        
        $this->add(array(
            'name' => 'Status',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'ddlStatus',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => '',
                'value_options' => array(
                    '' => '',
                    'Enable' => '有効',
                    'Disable' => '無効'
                )
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'hope',
            'options' => array(
                'label' => '',
                'value_options' => array(
                    '0' => '希望する',
                    '1' => '希望しない'
                )
            ),
            'attributes' => array(
                'value' => '0'
            )
        ));
        
        $this->add(array(
            'name' => 'btnSave',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Save',
                'id' => 'btnSave',
                'class' => 'btn btn-bootstrap2 btn-bootstrap2:hover'
            )
        ));
        
        $this->add(array(
            'name' => 'btnCancel',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Cancel',
                'id' => 'btnCancel',
                'class' => 'btn btn-bootstrap2 btn-bootstrap2:hover',
                'PostBackUrl' => 'http://localhost/JIEM_Portal/public'
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'name' => 'ServiceType',
            'options' => array(
                'value_options' => array(
                    '英検' => '英検',
                    'IBA' => '英検IBA'
                )
            )
        ));
    }
    
    // public function getInputFilter()
    // {
    // return array(
    // 'name' => array(
    // 'required' => true,
    // 'filters' => array(
    // array('name' => 'Zend\Filter\StringTrim'),
    // ),
    // 'validators' => array(
    // array(
    // 'name' => 'Zend\Validator\StringLength',
    // 'options' => array(
    // 'min' => 1,
    // 'max' => 3
    // ),
    // ),
    // ),
    // ),
    // 'txtEmailAddress' => array(
    // 'required' => true,
    // 'filters' => array(
    // array('name' => 'Zend\Filter\StringTrim'),
    // ),
    // 'validators' => array(
    // new Validator\EmailAddress(),
    // ),
    // ),
    // );
    // }
}