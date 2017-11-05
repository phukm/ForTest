<?php
namespace OrgMnt\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
class OrgSearchForm extends Form implements InputFilterAwareInterface
{
    
       
    public function __construct($serviceLocatior)
    {
        parent::__construct('OrgSearch');
        $config = $serviceLocatior->get('config');
        
        $listRefundOption = $config['simpleRefundStatus'];
        $paymentBillOption = $config['paymentBillStatus'];
        $publicFundingOption = $config['publicFundingStatus'];
        $listRefundOption = $config['refundStatusOption'];
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'txtOrgNumber',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'txtOrgNumber',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtOrgName1',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtOrgName1',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtOrgName2',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'txtOrgName2',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'datetime1',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'datetimepicker1',
                'class' => 'form-control input-date-1'
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'datetime2',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'datetimepicker2',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'txteet',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'txteet',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    '英検' => '英検',
                    'IBA' => '英検IBA'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'yearfrom',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'yearfrom',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    '0' => '1993',
                    '1' => '1994',
                    '2' => '1995',
                    '3' => '1996',
                    '4' => '1997',
                    '5' => '1998',
                    '6' => '1999',
                    '7' => '2000',
                    '8' => '2001',
                    '9' => '2002',
                    '10' => '2003',
                    '11' => '2004',
                    '12' => '2005',
                    '13' => '2006',
                    '14' => '2007',
                    '15' => '2008',
                    '16' => '2009',
                    '17' => '2010',
                    '18' => '2011',
                    '19' => '2012',
                    '20' => '2013',
                    '21' => '2014',
                    '22' => '2015'
                )
            )
        ));
        $this->add(array(
            'name' => 'monthfrom',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'monthfrom',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    '0' => '1',
                    '1' => '2',
                    '2' => '3',
                    '3' => '4',
                    '4' => '5',
                    '5' => '6',
                    '6' => '7',
                    '7' => '8',
                    '8' => '9',
                    '9' => '10',
                    '10' => '11',
                    '11' => '12'
                )
            )
        ));
        $this->add(array(
            'name' => 'dayfrom',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'dayfrom',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    '0' => '1',
                    '1' => '2',
                    '2' => '3',
                    '3' => '4',
                    '4' => '5',
                    '5' => '6',
                    '6' => '7',
                    '7' => '8',
                    '8' => '9',
                    '9' => '10',
                    '10' => '11',
                    '11' => '12',
                    '12' => '13',
                    '13' => '14',
                    '14' => '15',
                    '15' => '16',
                    '16' => '17',
                    '17' => '18',
                    '18' => '19',
                    '19' => '20',
                    '20' => '21',
                    '21' => '22',
                    '22' => '23',
                    '23' => '24',
                    '24' => '25',
                    '25' => '26',
                    '26' => '27',
                    '27' => '28',
                    '28' => '29',
                    '29' => '30',
                    '30' => '31'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'yearto',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'yearto',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    '0' => '1993',
                    '1' => '1994',
                    '2' => '1995',
                    '3' => '1996',
                    '4' => '1997',
                    '5' => '1998',
                    '6' => '1999',
                    '7' => '2000',
                    '8' => '2001',
                    '9' => '2002',
                    '10' => '2003',
                    '11' => '2004',
                    '12' => '2005',
                    '13' => '2006',
                    '14' => '2007',
                    '15' => '2008',
                    '16' => '2009',
                    '17' => '2010',
                    '18' => '2011',
                    '19' => '2012',
                    '20' => '2013',
                    '21' => '2014',
                    '22' => '2015'
                )
            )
        ));
        $this->add(array(
            'name' => 'monthto',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'monthto',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    '0' => '1',
                    '1' => '2',
                    '2' => '3',
                    '3' => '4',
                    '4' => '5',
                    '5' => '6',
                    '6' => '7',
                    '7' => '8',
                    '8' => '9',
                    '9' => '10',
                    '10' => '11',
                    '11' => '12'
                )
            )
        ));
        $this->add(array(
            'name' => 'dayto',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'dayto',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    '0' => '1',
                    '1' => '2',
                    '2' => '3',
                    '3' => '4',
                    '4' => '5',
                    '5' => '6',
                    '6' => '7',
                    '7' => '8',
                    '8' => '9',
                    '9' => '10',
                    '10' => '11',
                    '11' => '12',
                    '12' => '13',
                    '13' => '14',
                    '14' => '15',
                    '15' => '16',
                    '16' => '17',
                    '17' => '18',
                    '18' => '19',
                    '19' => '20',
                    '20' => '21',
                    '21' => '22',
                    '22' => '23',
                    '23' => '24',
                    '24' => '25',
                    '25' => '26',
                    '26' => '27',
                    '27' => '28',
                    '28' => '29',
                    '29' => '30',
                    '30' => '31'
                )
            )
        ));
        $this->add(array(
            'name' => 'search',
            'type' => 'Zend\Form\Element\Button',
            'attributes' => array(
                'value' => 'Search',
                'id' => 'btnSearch',
                'class' => 'btn'
            )
        ));
        $this->add(array(
            'name' => 'reset',
            'type' => 'Zend\Form\Element\Button',
            'attributes' => array(
                'value' => 'Reset',
                'id' => 'btnReset',
                'class' => 'btn'
            )
        ));
        $this->add(array(
            'name' => 'close',
            'type' => 'Zend\Form\Element\Button',
            'attributes' => array(
                'value' => 'Close',
                'id' => 'btnClose',
                'class' => 'btn'
            )
        ));
        
        $this->add(array(
            'name'       => 'refundStatus',
            'type'       => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id'                 => 'refundStatus',
                'class'              => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    0 => $listRefundOption[0],
                    1 => $listRefundOption[1],
                    2 => $listRefundOption[2]
                )
            )
        ));
        
        $this->add(array(
            'name'       => 'paymentBill',
            'type'       => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id'                 => 'paymentBill',
                'class'              => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    0 => $paymentBillOption[0],
                    1 => $paymentBillOption[1]
                )
            )
        ));
        
        $this->add(array(
            'name'       => 'publicFunding',
            'type'       => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id'                 => 'publicFunding',
                'class'              => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    0 => $publicFundingOption[0],
                    1 => $publicFundingOption[1]
                )
            )
        ));
        
        $this->add(array(
            'name'    => 'semiMainVenue[]',
            'type'    => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'use_hidden_element' => false
            ),
        ));
    }
}
