<?php

namespace AccessKey\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;

class AccessKeyForm extends Form implements InputFilterAwareInterface
{
    protected $inputFilter;
    protected $entityManager;
    private $serviceLocator;

    public function translate($mes)
    {
        return $this->serviceLocator->get('MVCTranslator')->translate($mes);
    }

    // create form and validate client side form
    public function __construct($serviceLocatior)
    {
        parent::__construct('frm-access-key');
        $this->serviceLocator = $serviceLocatior;
        $this->entityManager = $serviceLocatior->get('doctrine.entitymanager.orm_default');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name'       => 'organizationNo',
            'type'       => 'Text',
            'attributes' => array(
                'id'                 => 'organizationNo',
                'class'              => 'form-control',
                'data-rule-required' => 'true',
                'data-msg-required'  => $this->translate('MSG1'),
                'maxlength'          => 8
            )
        ));

        $this->add(array(
            'name'       => 'accessKey',
            'type'       => 'PassWord',
            'attributes' => array(
                'id'                 => 'accessKey',
                'class'              => 'form-control',
                'data-rule-required' => 'true',
                'data-msg-required'  => $this->translate('MSG1')
            )
        ));

         $this->add(array(
            'type'    => 'Csrf',
            'name'    => 'security',
            'options' => array(
                'csrf_options'  => array(
                    'timeout'   => 600
                )
            )
        ));
    }

    // validate server side form
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name'      => 'organizationNo',
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
                    )
                )
            ));

            $inputFilter->add(array(
                'name'      => 'accessKey',
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
                        'name' => 'AccessKey\Helper\ValidateForm\WrongAccessKey',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\AccessKey'),
                            'serviceLocator' => $this->serviceLocator,
                            'fields' => array('organizationNo', 'accessKey'),
                            'messages' => array(
                                'objectFound' => $this->translate('MSG17_AccessKey')
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'AccessKey\Helper\ValidateForm\ExpiredAccessKey',
                        'options' => array(
                            'object_repository' => $this->entityManager->getRepository('Application\Entity\AccessKey'),
                            'serviceLocator' => $this->serviceLocator,
                            'fields' => array('organizationNo', 'accessKey'),
                            'messages' => array(
                                'objectFound' => $this->translate('MSG18_AccessKey')
                            ),
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
