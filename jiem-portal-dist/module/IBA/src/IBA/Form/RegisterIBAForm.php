<?php

namespace IBA\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;

class RegisterIBAForm extends Form implements InputFilterAwareInterface {

    protected $inputFilter;
    protected $entityManager;
    private $serviceLocator;

    public function translate($mes) {
        return $this->serviceLocator->get('MVCTranslator')->translate($mes);
    }

    public function __construct($serviceLocatior) {
        parent::__construct('registeriba');
        $this->entityManager = $serviceLocatior->get('doctrine.entitymanager.orm_default');
        $this->serviceLocator = $serviceLocatior;
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'organizationNo',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtOrganizationNo',
                'class' => 'form-control',
                'disabled' => 'disabled'
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'idDraft',
            'type' => 'Text'
        ));

        $this->add(array(
            'name' => 'pICName',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtPICName',
                'class' => 'form-control',
                'maxlength' => 60,
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'zipCode1',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtZipCode1',
                'class' => 'form-control',
                'maxlength' => 3,
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-rule-maxZipCode' => 'true',
                'data-msg-maxZipCode' => $this->translate('maxZipCode')
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'zipCode2',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtZipCode2',
                'class' => 'form-control',
                'maxlength' => 4,
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-rule-maxZipCode' => 'true',
                'data-msg-maxZipCode' => ''
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'prefectureCode',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'ddlPrefecture',
                'class' => 'form-control',
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required')
            ),
            'options' => array(
                'value_options' => $this->getListCityName()
            )
        ));
        $this->add(array(
            'name' => 'address1',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtAdd1',
                'class' => 'form-control',
                'maxlength' => 60,
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
                'data-rule-checkFullSize' => 'true',
                'data-msg-checkFullSize' => $this->translate('Apply_IBA_FullSize')
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'address2',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtAdd2',
                'class' => 'form-control',
                'maxlength' => 60,
                'data-rule-checkFullSize' => 'true',
                'data-msg-checkFullSize' => $this->translate('Apply_IBA_FullSize')
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'telNo',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtTel',
                'class' => 'form-control',
                'maxlength' => 14,
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
                'data-rule-regexHyphenNumber' => 'true',
                'data-msg-regexHyphenNumber' => $this->translate('zipcode-number-hyphen')
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'fax',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtFax',
                'class' => 'form-control',
                'maxlength' => 14,
//                 'data-rule-required' => 'true',
//                 'data-msg-required' => $this->translate('required'),
                'data-rule-regexHyphenNumber' => 'true',
                'data-msg-regexHyphenNumber' => $this->translate('zipcode-number-hyphen')
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'mailName1',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtMailName1',
                'class' => 'form-control',
                'maxlength' => 40,
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required')
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'mailAddress1',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtMailaddress1',
                'class' => 'form-control',
                'maxlength' => 60,
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
                'data-rule-regxEmail' => 'true',
                'data-msg-regxEmail' => $this->translate('email-format')
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'testDate',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'datetimepicker',
                'class' => 'form-control input-date-1',
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
                'data-rule-checkDateFormat' => 'true',
                'data-msg-checkDateFormat' => $this->translate('MSG11'),
                'data-rule-compareTestDate' => 'true',
                'data-msg-compareTestDate' => $this->translate('MSG2'),
                'data-rule-isRegisterTestDate' => 'true',
                'data-msg-isRegisterTestDate' => $this->translate('test-date-duplicate')
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'purpose',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'ddlPurpose',
                'class' => 'form-control',
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required')
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    'placement' => $this->translate('purpose-placement'),
                    'measurement' => $this->translate('purpose-measurement'),
                    'other' => $this->translate('purpose-other')
                )
            )
        ));

        $this->add(array(
            'name' => 'purposeOther',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'id' => 'txtOthers',
                'class' => 'form-control',
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
                'maxlength' => 40,
                'onKeyDown' => 'APPLY_IBA.maxLengthInput("#txtOthers",40);',
                'onKeyUp' => 'APPLY_IBA.maxLengthInput("#txtOthers",40);',
                'onChange' => 'APPLY_IBA.maxLengthInput("#txtOthers",40);',
                'onPaste' => 'APPLY_IBA.maxLengthInput("#txtOthers",40);'
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'numberPeopleA',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtPeopleA',
                'class' => 'form-control Sum',
                'maxlength' => 4,
                'data-rule-checkEmtyImpl' => 'true',
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-msg-checkEmtyImpl' => $this->translate('MSG3'),
                'data-rule-checkEmtyImplPeopleA' => 'true',
                'data-msg-checkEmtyImplPeopleA' => $this->translate('MSG5')
            )
        ));
        $this->add(array(
            'name' => 'numberPeopleB',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtPeopleB',
                'class' => 'form-control Sum',
                'maxlength' => 4,
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-rule-checkEmtyImpl' => 'true',
                'data-msg-checkEmtyImpl' => '',
                'data-rule-checkEmtyImplPeopleB' => 'true',
                'data-msg-checkEmtyImplPeopleB' => $this->translate('MSG5')
            )
        ));
        $this->add(array(
            'name' => 'numberPeopleC',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtPeopleC',
                'class' => 'form-control Sum',
                'maxlength' => 4,
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-rule-checkEmtyImpl' => 'true',
                'data-msg-checkEmtyImpl' => '',
                'data-rule-checkEmtyImplPeopleC' => 'true',
                'data-msg-checkEmtyImplPeopleC' => $this->translate('MSG5')
            )
        ));
        $this->add(array(
            'name' => 'numberPeopleD',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtPeopleD',
                'class' => 'form-control Sum',
                'maxlength' => 4,
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-rule-checkEmtyImpl' => 'true',
                'data-msg-checkEmtyImpl' => '',
                'data-rule-checkEmtyImplPeopleD' => 'true',
                'data-msg-checkEmtyImplPeopleD' => $this->translate('MSG5')
            )
        ));
        $this->add(array(
            'name' => 'numberPeopleE',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtPeopleE',
                'class' => 'form-control Sum',
                'maxlength' => 4,
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-rule-checkEmtyImpl' => 'true',
                'data-msg-checkEmtyImpl' => '',
                'data-rule-checkEmtyImplPeopleE' => 'true',
                'data-msg-checkEmtyImplPeopleE' => $this->translate('MSG5')
            )
        ));
        // CD
        $this->add(array(
            'name' => 'numberCDA',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtCDA',
                'class' => 'form-control SumCD',
                'maxlength' => 2,
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-rule-checkEmtyImpl' => 'true',
                'data-msg-checkEmtyImpl' => '',
                'data-rule-checkEmtyImplCDA' => 'true',
                'data-msg-checkEmtyImplCDA' => $this->translate('MSG4')
            )
        ));
        $this->add(array(
            'name' => 'numberCDB',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtCDB',
                'class' => 'form-control SumCD',
                'maxlength' => 2,
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-rule-checkEmtyImpl' => 'true',
                'data-msg-checkEmtyImpl' => '',
                'data-rule-checkEmtyImplCDB' => 'true',
                'data-msg-checkEmtyImplCDB' => $this->translate('MSG4')
            )
        ));
        $this->add(array(
            'name' => 'numberCDC',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtCDC',
                'class' => 'form-control SumCD',
                'maxlength' => 2,
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-rule-checkEmtyImpl' => 'true',
                'data-msg-checkEmtyImpl' => '',
                'data-rule-checkEmtyImplCDC' => 'true',
                'data-msg-checkEmtyImplCDC' => $this->translate('MSG4')
            )
        ));
        $this->add(array(
            'name' => 'numberCDD',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtCDD',
                'class' => 'form-control SumCD',
                'maxlength' => 2,
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-rule-checkEmtyImpl' => 'true',
                'data-msg-checkEmtyImpl' => '',
                'data-rule-checkEmtyImplCDD' => 'true',
                'data-msg-checkEmtyImplCDD' => $this->translate('MSG4')
            )
        ));
        $this->add(array(
            'name' => 'numberCDE',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtCDE',
                'class' => 'form-control SumCD',
                'maxlength' => 2,
                'data-rule-number' => 'true',
                'data-msg-number' => $this->translate('zipcode-number'),
                'data-rule-checkEmtyImpl' => 'true',
                'data-msg-checkEmtyImpl' => '',
                'data-rule-checkEmtyImplCDE' => 'true',
                'data-msg-checkEmtyImplCDE' => $this->translate('MSG4')
            )
        ));

        $this->add(array(
            'name' => 'question1',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'id' => 'txtQuestion1',
                'class' => 'form-control',
                'maxlength' => 280,
                'onKeyDown' => 'APPLY_IBA.maxLengthInput("#txtQuestion1",280);',
                'onKeyUp' => 'APPLY_IBA.maxLengthInput("#txtQuestion1",280);',
                'onChange' => 'APPLY_IBA.maxLengthInput("#txtQuestion1",280);',
                'onPaste' => 'APPLY_IBA.maxLengthInput("#txtQuestion1",280);'
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'optionApply',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'id' => 'option',
                'data-rule-required' => 'true',
                'data-msg-required' => $this->translate('required'),
                'onchange' => 'APPLY_IBA.checkValidOptionApplyToSubmit()'
            ),
            'options' => array(
                'value_options' => array(
                    '0' => $this->translate('option-apply-0'),
                    '1' => $this->translate('option-apply-1')
                )
            )
        ));

        $this->add(array(
            'name' => 'questionNo',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'questionNo',
                'class' => 'form-control input-date-1 content-table-input text-right',
                'disabled' => 'disabled',
                'data-rule-emtyOptionMenuQuestionNo' => 'true',
                'data-msg-emtyOptionMenuQuestionNo' => $this->translate('MSG6'),
                'maxlength' => 4
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'rankNo',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'rankNo',
                'class' => 'form-control input-date-1 content-table-input text-right',
                'disabled' => 'disabled',
                'data-rule-emtyOptionMenuRankNo' => 'true',
                'data-msg-emtyOptionMenuRankNo' => $this->translate('MSG7'),
                'maxlength' => 4
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'question2',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'id' => 'txtQuestion2',
                'class' => 'form-control',
                'maxlength' => 280,
                'onKeyDown' => 'APPLY_IBA.maxLengthInput("#txtQuestion2",280);',
                'onKeyUp' => 'APPLY_IBA.maxLengthInput("#txtQuestion2",280);',
                'onChange' => 'APPLY_IBA.maxLengthInput("#txtQuestion2",280);',
                'onPaste' => 'APPLY_IBA.maxLengthInput("#txtQuestion2",280);'
            ),
            'options' => array()
        ));
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
        } else {
            $inputFilter = $this->inputFilter;
        }
        $inputFilter->add(array(
            'name' => 'idDraft',
            'required' => false
        ));
        $inputFilter->add(array(
            'name' => 'zipCode1',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'Digits',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Digits::NOT_DIGITS => $this->translate('zipcode-number')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => 3,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_LONG => $this->translate('maxlenght-3')
                        )
                    )
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'zipCode2',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'Digits',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Digits::NOT_DIGITS => $this->translate('zipcode-number')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => 4,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_LONG => $this->translate('maxlenght-4')
                        )
                    )
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'prefectureCode',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'address1',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => 60,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_LONG => $this->translate('maxlenght-60')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'IBA\Helper\ValidateForm\StringFullSize',
                    'options' => array(
                        'object_repository' => $this->entityManager->getRepository('Application\Entity\ApplyIBAOrg'),
                        'fields' => array('address1'),
                        'messages' => array(
                            'objectFound' => $this->translate('Apply_IBA_FullSize')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'address2',
            'required' => false,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => 60,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_LONG => $this->translate('maxlenght-40')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'IBA\Helper\ValidateForm\StringFullSize',
                    'options' => array(
                        'object_repository' => $this->entityManager->getRepository('Application\Entity\ApplyIBAOrg'),
                        'fields' => array('address2'),
                        'messages' => array(
                            'objectFound' => $this->translate('Apply_IBA_FullSize')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'telNo',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => 14,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_LONG => $this->translate('maxlenght-14')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'IBA\Helper\ValidateForm\NumberHyphen',
                    'options' => array(
                        'object_repository' => $this->entityManager->getRepository('Application\Entity\ApplyIBAOrg'),
                        'fields' => array('telNo'),
                        'messages' => array(
                            'objectFound' => $this->translate('zipcode-number-hyphen')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'fax',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => 14,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_LONG => $this->translate('maxlenght-14')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'IBA\Helper\ValidateForm\NumberHyphen',
                    'options' => array(
                        'object_repository' => $this->entityManager->getRepository('Application\Entity\ApplyIBAOrg'),
                        'fields' => array('fax'),
                        'messages' => array(
                            'objectFound' => $this->translate('zipcode-number-hyphen')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'mailName1',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => 40,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_LONG => $this->translate('maxlenght-40')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
            )
        ));
        $inputFilter->add(array(
            'name' => 'mailAddress1',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => 60,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_LONG => $this->translate('maxlenght-60')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'token' => 'mailAddress1',
                        'messages' => array(
                            \Zend\Validator\EmailAddress::INVALID_FORMAT => $this->translate('email-format')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'testDate',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'Date',
                    'options' => array(
                        'format' => 'Y/m/d',
                        'messages' => array(
                            'dateFalseFormat' => '',
                            'dateInvalidDate' => $this->translate('email-format')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'IBA\Helper\ValidateForm\TestDateDuplicate',
                    'options' => array(
                        'object_repository' => $this->entityManager->getRepository('Application\Entity\ApplyIBAOrg'),
                        'fields' => array('testDate'),
                        'messages' => array(
                            'objectFound' => $this->translate('test-date-duplicate')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'purpose',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required')
                        )
                    ),
                    'break_chain_on_failure' => true
                ),
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => $this->translate('required')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'purposeOther',
            'required' => false,
            'filters' => array(
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => 40,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_LONG => $this->translate('maxlenght-40')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'question1',
            'required' => false,
            'validators' => array(
                array(
                    'name' => 'IBA\Helper\ValidateForm\StringLength',
                    'options' => array(
                        'object_repository' => $this->entityManager->getRepository('Application\Entity\ApplyIBAOrg'),
                        'fields' => array('question1'),
                        'messages' => array(
                            'objectFound' => $this->translate('maxlenght-280')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'question2',
            'required' => false,
            'validators' => array(
                array(
                    'name' => 'IBA\Helper\ValidateForm\StringLength',
                    'options' => array(
                        'object_repository' => $this->entityManager->getRepository('Application\Entity\ApplyIBAOrg'),
                        'fields' => array('question2'),
                        'messages' => array(
                            'objectFound' => $this->translate('maxlenght-280')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'optionApply',
            'required' => true,
            'validators' => array(
               array(
                   'name'    => 'InArray',
                    'options' => array(
                        'haystack' => array('0','1')
                   ),
               ),
           ),
        ));
        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    public function getListCityName() {
        $data = $this->entityManager->getRepository('Application\Entity\City')->getApplyEikCitiesList();
//         $array[''] = '';
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $code = $value->getCityCode();
                if ($code !== '99') {
                    $array[$code] = $value->getCityName();
                }
            }
        }
        return $array;
    }
    /**
     * @author minhbn1 <minhbn1@fsoft.com.vn>
     * 
     * set date current smaller filter
     * 
     */
    function setDateCurrentSmallerFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
        } else {
            $inputFilter = $this->inputFilter;
        }
        $inputFilter->add(array(
            'name' => 'testDate',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'IBA\Helper\ValidateForm\DateCurrentSmaller',
                    'options' => array(
                        'object_repository' => $this->entityManager->getRepository('Application\Entity\ApplyIBAOrg'),
                        'fields' => array('testDate'),
                        'messages' => array(
                            'objectFound' => $this->translate('MSG2')
                        )
                    ),
                    'break_chain_on_failure' => true
                )
            )
        ));
        $this->inputFilter = $inputFilter;
    }

}
