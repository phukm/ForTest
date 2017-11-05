<?php

namespace BasicConstruction\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;

class ResetPasswordForm extends Form implements InputFilterAwareInterface {

    protected $inputFilter;
    protected $entityManager;
    private $serviceLocator;

    public function translate($mes) {
        return $this->serviceLocator->get('MVCTranslator')->translate($mes);
    }

    public function __construct($serviceLocatior) {
        parent::__construct();
        $this->entityManager = $serviceLocatior->get('doctrine.entitymanager.orm_default');
        $this->serviceLocator = $serviceLocatior;
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'txtPassword',
            'type' => 'Password',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtPassword',
                'maxlength' => 255,
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('MSG1-forgot-password-validate-required'),
                'data-rule-fieldLength' => 'true',
                'data-msg-fieldLength' => $this->translate('MSG31-forgot-password-validate-password'),
                'data-rule-halfSize' => 'true',
                'data-msg-halfSize' => $this->translate('MSG31-forgot-password-validate-password'),
                'data-rule-checkPwdSameUser' => 'true',
                'data-msg-checkPwdSameUser' => $this->translate('MSG31-forgot-password-validate-password'),
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'txtConfirmPassword',
            'type' => 'Password',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtConfirmPassword',
                'maxlength' => 255,
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('MSG1-forgot-password-validate-required'),
                'data-rule-equalTo' => "#txtPassword",
                'data-msg-equalTo' => $this->translate('MSG10-forgot-password-validate-not-same')
                //'data-rule-fieldLength' => 'true',
                //'data-msg-fieldLength' => $this->translate('MSG31-forgot-password-validate-password'),
                //'data-rule-halfSize' => 'true',
                //'data-msg-halfSize' => $this->translate('MSG31-forgot-password-validate-password'),
                //'data-rule-checkPwdSameUser' => 'true',
                //'data-msg-checkPwdSameUser' => $this->translate('MSG31-forgot-password-validate-password'),
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'txtUserName',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtUserName',
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'txtToken',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtToken',
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtOrganizationNo',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtOrganizationNo',
            ),
            'options' => array()
        ));
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $inputFilter->add(array(
                'name' => 'txtPassword',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('MSG1-forgot-password-validate-required'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'BasicConstruction\Helper\ValidateForm\StringPassword',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\User'),
                            'fields' => array('txtPassword'),
                            'messages' => array(
                                'objectFound' => $this->translate('MSG31-forgot-password-validate-password_FREE_STYLE'),
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'BasicConstruction\Helper\ValidateForm\StringPasswordSameUserId',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\User'),
                            'fields' => array('txtPassword'),
                            'messages' => array(
                                'objectFound' => $this->translate('MSG31-forgot-password-validate-password_FREE_STYLE'),
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'BasicConstruction\Helper\ValidateForm\StringPasswordSameLastest',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\User'),
                            'fields' => array('txtPassword'),
                            'messages' => array(
                                'objectFound' => $this->translate('MSG31-forgot-password-validate-password_FREE_STYLE'),
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => 32,
                            'messages' => array(
                                \Zend\Validator\StringLength::TOO_LONG => $this->translate('MSG1')
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'min' => 6,
                            'messages' => array(
                                \Zend\Validator\StringLength::TOO_SHORT => $this->translate('MSG1')
                            )
                        ),
                        'break_chain_on_failure' => true
                    )
                )
            ));

            $inputFilter->add(array(
                'name' => 'txtConfirmPassword',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('MSG1-forgot-password-validate-required'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'BasicConstruction\Helper\ValidateForm\StringPassword',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\User'),
                            'fields' => array('txtConfirmPassword'),
                            'messages' => array(
                                'objectFound' => $this->translate('MSG31-forgot-password-validate-password_FREE_STYLE'),
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'BasicConstruction\Helper\ValidateForm\StringPasswordSameUserId',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\User'),
                            'fields' => array('password'),
                            'messages' => array(
                                'objectFound' => $this->translate('MSG31-forgot-password-validate-password_FREE_STYLE'),
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => 32,
                            'messages' => array(
                                \Zend\Validator\StringLength::TOO_LONG => $this->translate('MSG31-forgot-password-validate-password_FREE_STYLE')
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'min' => 6,
                            'messages' => array(
                                \Zend\Validator\StringLength::TOO_SHORT => $this->translate('MSG31-forgot-password-validate-password_FREE_STYLE')
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'BasicConstruction\Helper\ValidateForm\StringConfirmPassword',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\User'),
                            'fields' => array('txtConfirmPassword'),
                            'messages' => array(
                                'objectFound' => $this->translate('MSG10-forgot-password-validate-not-same'),
                            )
                        ),
                        'break_chain_on_failure' => true
                    )
                )
            ));

            $inputFilter->add(array(
                'name' => 'txtUserName',
            ));
            $inputFilter->add(array(
                'name' => 'txtOrganizationNo',
            ));

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

}
