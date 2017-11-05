<?php

namespace PupilMnt\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;

class SeperateForm extends Form implements InputFilterAwareInterface
{

    protected $inputFilter;
    protected $entityManager;
    private $serviceLocator;
    
    public function translate($mes)
    {
        return $this->serviceLocator->get('MVCTranslator')->translate($mes);
    }

    // Create form and validate form client side
    public function __construct($serviceLocatior)
    {
        // we want to ignore the name passed
        parent::__construct('seperate-pupil');
        $this->serviceLocator = $serviceLocatior;
        $this->entityManager = $serviceLocatior->get('doctrine.entitymanager.orm_default');

        $this->setAttributes(array(
            'class' => '',
            'method' => 'POST',
            'enctype' => 'multipart/form-data'
        ));

        $this->add(array(
            'name' => 'fileImport',
            'type' => 'File',
            'attributes' => array(
                'class' => 'form-control importstd-textbox',
//                'data-rule-required' => 'true',
//                'data-msg-required' => $this->translate('MsgEmptyFileUpload'),
                //'data-rule-accept' => 'csv',
                //'data-msg-accept' => $this->translate('MsgFileNotCSV_28'),
                'id' => 'fileImport',
            )
        ));
    }

    // Validate form server side
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name' => 'fileImport',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name' => 'Zend\Validator\File\UploadFile',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\File\UploadFile::NO_FILE => $this->translate('MsgEmptyFileUpload'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'Zend\Validator\File\Extension',
                        'options' => array(
                            'extension' => 'csv',
                            'messages' => array(
                                \Zend\Validator\File\Extension::FALSE_EXTENSION => $this->translate('MsgFileNotCSV_28'),
                            ),
                        ),
                        'break_chain_on_failure' => true
                    ),
                ),
            ));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}
