<?php
namespace AccessKey\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;

class CreateFirstUserForm extends Form implements InputFilterAwareInterface
{
    protected $inputFilter;
    protected $entityManager;
    private $serviceLocator;

    public function translate($mes)
    {
        return $this->serviceLocator->get('MVCTranslator')->translate($mes);
    }

    // create form and validate client form
    public function __construct($serviceLocatior)
    {
        parent::__construct('frm-create-first-user');
        $this->serviceLocator = $serviceLocatior;
        $this->entityManager = $serviceLocatior->get('doctrine.entitymanager.orm_default');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'userId',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'userId',
                'class' => 'form-control input-480',
                'maxlength' => 31,
                'data-rule-required' => 'true',
                'data-msg-required'  => $this->translate('MSG1'),
                'data-rule-characterUser' => 'true',
                'data-msg-characterUser'  => $this->translate('MSG19')
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'firstNameKanji',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'firstNameKanji',
                'class' => 'form-control input-230',
                'maxlength' => 255,
                'data-rule-required' => 'true',
                'data-msg-required'  => $this->translate('MSG1'),
                'placeholder' => $this->translate('add-new-placeholder-first-name')
            ),
            'options' => array(
            ),
        ));
        $this->add(array(
            'name' => 'lastNameKanji',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'lastNameKanji',
                'class' => 'form-control input-230',
                'maxlength' => 255,
                'data-rule-required' => 'true',
                'data-msg-required'  => $this->translate('MSG1'),
                'placeholder' => $this->translate('add-new-placeholder-last-name')
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'emailAddress',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'emailAddress',
                'class' => 'form-control input-300',
                'maxlength' => 255,
                'data-rule-required' => 'true',
                'data-msg-required'  => $this->translate('MSG1'),
                'data-rule-regxEmail' => 'true',
                'data-msg-regxEmail' => $this->translate('MSG21'),
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'confirmEimail',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'confirmEimail',
                'class' => 'form-control input-300',
                'data-rule-required' => 'true',
                'data-msg-required'  => $this->translate('MSG1'),
                'data-rule-equalTo' => "#emailAddress",
                'data-msg-equalTo' => $this->translate('EmailAddressDoesNotMatch')
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'checkBoxPolicy',
            'type' => 'Checkbox',
            'options' => array(
                'use_hidden_element' => false,
            ),
            'attributes' => array(
                'id' => 'checkBoxPolicy',
                'data-msg-required'  => $this->translate('MSG41')
            )
        )); 
    }

    // validate server side form
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name'      => 'userId',
                'required'  => true,
                'filters'   => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('MSG1'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                    'name' => 'AccessKey\Helper\ValidateForm\StringUserId',
                    'options' => array(
                        'object_repository' => $this->entityManager->getRepository('Application\Entity\User'),
                        'fields' => array('userId'),
                        'messages' => array(
                            'objectFound' => $this->translate('MSG19_FREE_STYLE'),
                        )
                    ),
                    'break_chain_on_failure' => true
                    )
                )
            ));
            $inputFilter->add(array(
                'name'      => 'firstNameKanji',
                'required'  => true,
                'filters'   => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('MSG1'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                )
            ));
            $inputFilter->add(array(
                'name'      => 'lastNameKanji',
                'required'  => true,
                'filters'   => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('MSG1'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                )
            ));
            $inputFilter->add(array(
                'name'      => 'emailAddress',
                'required'  => true,
                'filters'   => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('MSG1'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'AccessKey\Helper\ValidateForm\StringEmailAddress',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\User'),
                            'fields' => array('emailAddress'),
                            'messages' => array(
                                'objectFound' => $this->translate('MSG21'),
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'AccessKey\Helper\ValidateForm\EmailExists',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\User'),
                            'fields' => array('emailAddress'),
                            'messages' => array(
                                'objectFound' => $this->translate('EmailIsUse'),
                            )
                        ),
                        'break_chain_on_failure' => true
                    ),
                )
            ));
            $inputFilter->add(array(
                'name'      => 'confirmEimail',
                'required'  => true,
                'filters'   => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('MSG1'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'AccessKey\Helper\ValidateForm\StringConfirmEmail',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\User'),
                            'fields' => array('emailAddress'),
                            'messages' => array(
                                'objectFound' => $this->translate('EmailAddressDoesNotMatch'),
                            )
                        ),
                        'break_chain_on_failure' => true
                    )
                )
            ));

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

}
