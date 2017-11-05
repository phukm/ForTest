<?php

namespace IBA\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;

class PolicyForm extends Form implements InputFilterAwareInterface {

    protected $inputFilter;
    private $serviceLocator;

    public function translate($mes) {
        return $this->serviceLocator->get('MVCTranslator')->translate($mes);
    }

    public function __construct($serviceLocatior) {
        parent::__construct('frm-policy');
        $this->serviceLocator = $serviceLocatior;

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'firtNameKanji',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtFirstName',
                'class' => 'form-control',
                'maxlength' => 20,
                'placeholder' => '姓',
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'lastNameKanji',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtLastName',
                'class' => 'form-control',
                'maxlength' => 20,
                'placeholder' => '名',
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'mailAddress',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtEmailAdd',
                'class' => 'form-control',
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
                'data-rule-regxEmail' => 'true',
                'data-msg-regxEmail' => $this->translate('email-format'),
            ),
            'options' => array()
        ));


        $this->add(array(
            'name' => 'confirmEmail',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtConfirmEmail',
                'class' => 'form-control',
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
                'data-rule-equalTo' => "#txtEmailAdd",
                'data-msg-equalTo' => $this->translate('email-confirm-email-notsame')
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'phoneNumber',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtPhoneNumber',
                'class' => 'form-control',
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
                'data-rule-digits' => 'true',
                'data-msg-digits' => $this->translate('phone-is-number')
            ),
            'options' => array()
        ));
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $inputFilter->add(array(
                'name' => 'firtNameKanji',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                )
            ));
            $inputFilter->add(array(
                'name' => 'lastNameKanji',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                )
            ));
            $inputFilter->add(array(
                'name' => 'mailAddress',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'EmailAddress',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\EmailAddress::INVALID_FORMAT => $this->translate('email-format')
                            )
                        )
                    )
                )
            ));
            $inputFilter->add(array(
                'name' => 'confirmEmail',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'EmailAddress',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\EmailAddress::INVALID_FORMAT => $this->translate('email-format')
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'Identical',
                        'options' => array(
                            'token' => 'mailAddress',
                            'messages' => array(
                                \Zend\Validator\Identical::NOT_SAME => $this->translate('email-confirm-email-notsame')
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                )
            ));
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

}
