<?php
namespace Satellite\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;

class PayByCreditForm extends Form
{

    protected $inputFilter;

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->add(array(
                'name'       => 'cardFirstName',
                'type'       => 'Zend\Form\Element\Text',
                'required'   => true,
                'attributes' => array(
                    'id'           => 'card-first-name',
                    'placeholder'  => '姓',
                    'class'        => 'form-control inset-shadow wname pull-left',
                    'autofocus'    => 'autofocus',
                    'autocomplete' => 'off',
                    'maxlength'    => 10
                ))
        );
        $this->add(
            array(
                'name'       => 'cardLastName',
                'type'       => 'Zend\Form\Element\Text',
                'required'   => true,
                'attributes' => array(
                    'id'           => 'card-last-name',
                    'placeholder'  => '名',
                    'class'        => 'form-control inset-shadow wname pull-left',
                    'autofocus'    => 'autofocus',
                    'autocomplete' => 'off',
                    'maxlength'    => 10
                ))
        );
        $this->add(
            array(
                'name'       => 'cardNumber',
                'type'       => 'Zend\Form\Element\Text',
                'required'   => true,
                'attributes' => array(
                    'id'           => 'card-number',
                    'class'        => 'form-control inset-shadow wcard',
                    'autofocus'    => 'autofocus',
                    'autocomplete' => 'off',
                    'maxlength'    => 16
                ))
        );
        $this->add(array(
                'name'       => 'cardMonth',
                'type'       => 'Zend\Form\Element\Text',
                'required'   => true,
                'attributes' => array(
                    'id'           => 'card-month',
                    'class'        => 'form-control inset-shadow pull-left col-month',
                    'autofocus'    => 'autofocus',
                    'autocomplete' => 'off',
                    'maxlength'    => 2
                ))
        );
        $this->add(array(
                'name'       => 'cardYear',
                'type'       => 'Zend\Form\Element\Text',
                'required'   => true,
                'attributes' => array(
                    'id'           => 'card-year',
                    'class'        => 'form-control inset-shadow pull-left col-year',
                    'autofocus'    => 'autofocus',
                    'autocomplete' => 'off',
                    'maxlength'    => 2
                ))
        );
        $this->add(array(
            'name'       => 'cardCvv',
            'type'       => 'Zend\Form\Element\Password',
            'required'   => true,
            'attributes' => array(
                'id'           => 'card-cvv',
                'class'        => 'form-control inset-shadow wcvv',
                'autofocus'    => 'autofocus',
                'autocomplete' => 'off',
                'maxlength'    => 4
            )));
        $this->add(array(
            'name'    => 'chooseKyu[]',
            'type'    => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'use_hidden_element' => false
            ),
        ));
        $this->add(array(
            'type'    => 'Csrf',
            'name'    => 'token',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 600
                )
            )
        ));
        $this->add(array(
            'type'       => 'Zend\Form\Element\Hidden',
            'name'    => 'csrfToken',
        ));
    }

    // validate server side form
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $inputFilter->add(array(
                'name'       => 'cardFirstName',
                'required'   => true,
                'filters'    => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name' => 'not_empty',
                    ),
                    array(
                        'name'    => 'string_length',
                        'options' => array(
                            'max' => 50
                        ),
                    ),
                )
            ));
            $inputFilter->add(array(
                'name'       => 'cardLastName',
                'required'   => true,
                'filters'    => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name' => 'not_empty',
                    ),
                    array(
                        'name'    => 'string_length',
                        'options' => array(
                            'max' => 50
                        ),
                    ),
                )
            ));
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
