<?php
namespace Satellite\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilter;

class TestSideExemptionForm extends Form implements InputFilterAwareInterface
{
    protected $inputFilter;
    protected $entityManager;
    private $serviceLocator;

    public function __construct($serviceLocator)
    {
        parent::__construct('TestSideExemptionForm');
        $this->serviceLocator = $serviceLocator;
        $this->entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'examGrade1',
            'attributes' => array(
                    'value' => '5'
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'examGrade2',
            'attributes' => array(
                    'value' => '7'
            )
        ));
        
        $this->add(array(
            'name' => 'exemption1',
            'type' => 'Zend\Form\Element\Radio',
            'value' => 0,
            'options' => array(
                'value_options' => array(
                    array(
                        'value' => 1,
                        'attributes' => array(
                            'class' => 'radio-box',
                            'tabindex' => '1',
                            'id' => 'exemption1_1'
                        ),
                        'label' => 'あり',
                        'label_attributes' => array('class' => 'text-normal mr20')
                    ),
                    array(
                        'value' => 0,
                        'attributes' => array(
                            'class' => 'radio-box',
                            'tabindex' => '2',
                            'id' => 'exemption1_0'
                        ),
                        'label' => 'なし',
                        'label_attributes' => array('class' => 'text-normal')
                    )
                )
            )
        ));

        $this->add(array(
            'name' => 'exemption2',
            'type' => 'Zend\Form\Element\Radio',
            'value' => 0,
            'options' => array(
                'value_options' => array(
                    array(
                        'value' => 1,
                        'attributes' => array(
                            'class' => 'radio-box',
                            'tabindex' => '3',
                            'id' => 'exemption2_1'
                        ),
                        'label' => 'あり',
                        'label_attributes' => array('class' => 'text-normal mr20')
                    ),
                    array(
                        'value' => 0,
                        'attributes' => array(
                            'class' => 'radio-box',
                            'tabindex' => '4',
                            'id' => 'exemption2_0'
                        ),
                        'label' => 'なし',
                        'label_attributes' => array('class' => 'text-normal')
                    )
                )
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'passedKai1',
            'attributes' => array(
                'id' => 'passedKai1',
                'class' => 'form-control styled-select',
                'tabindex' => '5'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'passedKai2',
            'attributes' => array(
                'id' => 'passedKai2',
                'class' => 'form-control',
                'tabindex' => '6'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'passedPlace1',
            'attributes' => array(
                'id' => 'passedPlace1',
                'class' => 'form-control'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'passedPlace2',
            'attributes' => array(
                'id' => 'passedPlace2',
                'class' => 'form-control'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'passedCity1',
            'attributes' => array(
                'id' => 'passedCity1',
                'class' => 'form-control',
                'tabindex' => '7'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'passedCity2',
            'attributes' => array(
                'id' => 'passedCity2',
                'class' => 'form-control',
                'tabindex' => '7'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'personalId1',
            'attributes' => array(
                'id' => 'personalId1',
                'class' => 'form-control',
                'maxlength' => '7',
                'tabindex' => '9'
            ),
            'options' => array(
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'personalId2',
            'attributes' => array(
                'id' => 'personalId2',
                'class' => 'form-control',
                'maxlength' => '7',
                'tabindex' => '10'
            ),
            'options' => array(
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'firstTestCity1',
            'attributes' => array(
                'id' => 'firstTestCity1',
                'class' => 'form-control',
                'tabindex' => '11'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'firstTestCity2',
            'attributes' => array(
                'id' => 'firstTestCity2',
                'class' => 'form-control',
                'tabindex' => '12'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'firstExamPlace1',
            'attributes' => array(
                'id' => 'firstExamPlace1',
                'class' => 'form-control',
                'tabindex' => '13'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'firstExamPlace2',
            'attributes' => array(
                'id' => 'firstExamPlace2',
                'class' => 'form-control',
                'tabindex' => '14'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'secondTestCity1',
            'attributes' => array(
                'id' => 'secondTestCity1',
                'class' => 'form-control',
                'tabindex' => '15'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'secondTestCity2',
            'attributes' => array(
                'id' => 'secondTestCity2',
                'class' => 'form-control',
                'tabindex' => '16'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'secondExamPlace1',
            'attributes' => array(
                'id' => 'secondExamPlace1',
                'class' => 'form-control',
                'tabindex' => '17'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'secondExamPlace2',
            'attributes' => array(
                'id' => 'secondExamPlace2',
                'class' => 'form-control',
                'tabindex' => '18'
            ),
            'options' => array(
                'empty_option' => '',
            )
        ));
    }
    
    public function getInputFilter() {
        
        if(!$this->inputFilter){
            $inputFilter = new InputFilter();
            
            $inputFilter->add(array(
                'name' => 'personalId1',
                'required' => false,
                'filters'   => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name' => 'Digits',
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => 7,
                        ),
                        'break_chain_on_failure' => true
                    ),
                ),
                
            ));
            
            $inputFilter->add(array(
                'name' => 'personalId2',
                'required' => false,
                'filters'   => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name' => 'Digits',
                        'break_chain_on_failure' => true
                    ),
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => 7,
                        ),
                        'break_chain_on_failure' => true
                    ),
                ),
                
            ));            
            
            $this->inputFilter = $inputFilter;
        }
        
        return $this->inputFilter;
    }
    
    public function translate($mes)
    {
        return $this->serviceLocator->get('MVCTranslator')->translate($mes);
    }

}
