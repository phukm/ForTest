<?php
namespace BasicConstruction\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;

class ForgotPasswordOrUserIDlForm extends Form implements InputFilterAwareInterface
{
    protected $inputFilter;
    protected $entityManager;
    private $serviceLocator;
    
    public function translate($mes) {
        return $this->serviceLocator->get('MVCTranslator')->translate($mes);
    }
    
    public function __construct($serviceLocatior)
    {
        parent::__construct();
        $this->entityManager = $serviceLocatior->get('doctrine.entitymanager.orm_default');
        $this->serviceLocator = $serviceLocatior;
        $this->setAttribute('method', 'post');
               
        $this->add(array(
            'name' => 'radioOption',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'id' => 'radioOption',
                'data-rule-required' => 'true',
                'data-msg-required'  => $this->translate('MSG1-forgot-password-mandatory'),
            ),
            'options' => array(
                'value_options' => array(
                    '0' => $this->translate('radio-forgot-userID'),
                    '1' => $this->translate('radio-forgot-password')
                )
            )
        ));
                
        $this->add(array(
            'name' => 'txtEmail',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtEmail',
                'maxlength' => 255,
                'data-rule-required' => 'true',
                'data-msg-required'  => $this->translate('MSG1-forgot-password-mandatory'),
            ),
            'options' => array()
        ));
    }
    
      // validate server side form
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $inputFilter->add(array(
                'name'      => 'radioOption',
                'required'  => true,
                'validators' => array(
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('MSG1-forgot-password-mandatory'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    )
                )
            ));
            
            $inputFilter->add(array(
                'name'      => 'txtEmail',
                'required'  => true,
                'validators' => array(
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('MSG1-forgot-password-mandatory'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'BasicConstruction\Helper\ValidateForm\EmailDoesNotExist',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\User'),
                            'serviceLocator' => $this->serviceLocator,
                            'fields' => array('txtEmail'),
                            'messages' => array(
                                'objectFound' => $this->translate('MSG7-forgot-password-email-field')
                            ),
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