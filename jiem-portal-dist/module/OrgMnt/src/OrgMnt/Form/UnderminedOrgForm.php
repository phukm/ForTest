<?php

namespace OrgMnt\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;

class UnderminedOrgForm extends Form implements InputFilterAwareInterface
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
        parent::__construct('frm-undetermined');
        $this->serviceLocator = $serviceLocatior;
        $this->entityManager = $serviceLocatior->get('doctrine.entitymanager.orm_default');

        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name'       => 'year',
            'type'       => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id'                 => 'year',
                'class'              => 'form-control',
                'data-rule-required' => 'true',
                'data-msg-required'  => $this->translate('MSG1'),
                'value' => $this->setYear()['currentYear'],
                'tabindex' => 1,
            ),
            'options' => array(
                'value_options' => $this->setYear()['lst_y']
            )
        ));
        
        $this->add(array(
            'name'       => 'kai',
            'type'       => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id'                 => 'kai',
                'class'              => 'form-control',
                'tabindex' => 2,
            )
        ));
        
        $this->add(array(
            'name'       => 'status',
            'type'       => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id'                 => 'status',
                'class'              => 'form-control',
                'tabindex' => 3,
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    'DRAFT' => $this->translate('UndiminedOrgStatusDraft'),
                    'SUBMITTED' => $this->translate('UndiminedOrgStatusSubmited')
                )
            )
        ));
        
        $this->add(array(
            'name'       => 'organizationNo',
            'type'       => 'Text',
            'attributes' => array(
                'id'                 => 'organizationNo',
                'class'              => 'form-control',
                'maxlength'          => 8,
                'tabindex' => 4,
            )
        ));
        
        $this->add(array(
            'name'       => 'organizationName',
            'type'       => 'Text',
            'attributes' => array(
                'id'                 => 'organizationName',
                'class'              => 'form-control',
                'tabindex' => 5,
            )
        ));
    }
    
    public function setYear()
    {
        $setYear = array();
        $currentYear = date('Y');
        if (date('m') < 4) {
            $currentYear = date('Y') - 1;
        } 
        $setYear['currentYear'] = $currentYear;
        $setYear['lst_y'] = array();
        for ($i = $currentYear + 2; $i >= 2010; $i --) {
            $setYear['lst_y'][$i] = $i;
        }
        return $setYear;
    }
}
