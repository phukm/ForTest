<?php
namespace Eiken\Form\EikenOrg;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Eiken\Service\ApplyEikenOrgService;
use Doctrine\ORM\EntityManager;

use Zend\Form\Form;

class CreateForm extends Form
{
    public function __construct($serviceLocator)
    {
        parent::__construct('applicationForm');
        
        $config = $serviceLocator->get('config');
        
        $this->setAttribute('method', 'post');
        $this->add(array(
            'type' => 'radio',
            'name' => 'date0',
            'options' => array(
                'value_options' => array(
                    'friday' => array(
                        'label' => '金曜日',
                        'value' => '1',
                        'attributes' => array(
                            'class' => ' classrd padding_radio',
                            'id' => 'date0-first'
                        )
                    )
                    ,
                    'saturday' => array(
                        'label' => '土曜日',
                        'value' => '2',
                        'attributes' => array(
                            'id' => 'date11',
                            'class' => 'classrd padding_radio'
                        )
                    ),
                    'sunday' => array(
                        'label' => '日曜日',
                        'value' => '3',
                        'attributes' => array(
                            'id' => 'date2',
                            'class' => 'classrd padding_radio'
                        )
                    ),
                    'fri_saturday' => array(
                        'label' => '金・土の両日にわたり実施',
                        'value' => '4',
                        'attributes' => array(
                            'id' => 'date3',
                            'class' => 'classrd widthlabel padding_radio'
                        )
                    )
                )
            ),
            'attributes' => array(
                'value' => '0'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'date1',
            'attributes' => array(
                'class' => 'padding_radio',
                'value' => 1
            ),
            'options' => array(
                'value_options' => array(
                    '1' => '金曜日',
                    '2' => '土曜日'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'date2',
            'attributes' => array(
                'value' => 1,
                'class' => 'padding_radio'
            ),
            'options' => array(
                'value_options' => array(
                    '1' => '金曜日',
                    '2' => '土曜日'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'date3',
            'attributes' => array(
                'value' => 1,
                'class' => 'padding_radio'
            )
            ,
            'options' => array(
                'value_options' => array(
                    '1' => '金曜日',
                    '2' => '土曜日'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'date4',
            'attributes' => array(
                'value' => 1,
                'class' => 'padding_radio'
            ),
            'options' => array(
                'value_options' => array(
                    '1' => '金曜日',
                    '2' => '土曜日'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'date5',
            'attributes' => array(
                'value' => 1,
                'class' => 'padding_radio'
            ),
            'options' => array(
                'value_options' => array(
                    '1' => '金曜日',
                    '2' => '土曜日'
                )
            )
        ));
        $this->add(array(
            'name' => 'totalcd',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'totalcd',
                'class' => 'form-control',
                'onkeypress' => 'return EIKEN_ORG.isNumber(event);'
            ),
            'options' => array(
                'label' => ''
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'locationType',
            'attributes' => array(
                'value' => 1,
                'class' => 'padding_radio'
            ),
            'options' => array(
                'value_options' => array(
                    'single' => array(
                        'label' => '単独',
                        'value' => '0',
                        'attributes' => array(
                            'id' => 'location-type-single'
                        )
                    )
                    ,
                    'combination' => array(
                        'label' => '合同',
                        'value' => '1',
                        'attributes' => array(
                            'class' => 'classrd padding_radio',
                            'id' => 'location-type-combination'
                        )
                    )
                )
                
            )
        ));
        $this->add(array(
            'name' => 'locationType1',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'locationType1'
            ),
            'options' => array(
                'label' => '',
                'value_options' => $this->locationType1()
            )
        ));
        $this->add(array(
            'name' => 'EikenOrgNo1',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'EikenOrgNo1',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => ''
            )
        ));
        
        $this->add(array(
            'name' => 'EikenOrgNo12',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'EikenOrgNo12',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => ''
            )
        ));
        
        $this->add(array(
            'name' => 'EikenOrgNo123',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'EikenOrgNo123',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => ''
            )
        ));
        
        $this->add(array(
            'name' => 'EikenOrgNo2',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'EikenOrgNo2',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => ''
            )
        ));
        
        $this->add(array(
            'name' => 'cityId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'cityId',
                'onchange' => 'EIKEN_ORG.loadExamLocation();'
            )
        ));
        
        $this->add(array(
            'name' => 'districtId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'districtId'
            )
        ));
        
//        $invSettingService = new ApplyEikenOrgService($this->getApplicationServiceLocator());
//        $invSetting = $invSettingService->getInvitationSetting();
//        $paymentStatus = $invSetting ? $invSetting['paymentType'] : null;
//        
//        $listRefundOption = $config['refundStatusOption'];
        
//        if($paymentStatus == 0){
//            $this->add(array(
//                'name' => 'refundStatus',
//                'type' => 'Zend\Form\Element\Select',
//                'attributes' => array(
//                    'class' => 'form-control',
//                    'id' => 'refundStatus'
//                ),
//                'options' => array(
//                    'empty_option' => '',
//                    'value_options' => array(
//                        0 => $listRefundOption[0],
//                        2 => $listRefundOption[2]
//                    )
//                )
//            ));
//        }
        
        $this->add(array(
           'name' => 'refundStatus',
           'type' => 'Zend\Form\Element\Select',
           'attributes' => array(
               'class' => 'form-control',
               'id' => 'refundStatus'
           ),
           'options' => array(
               'empty_option' => '',
//               'value_options' => array(
//                   0 => $listRefundOption[0],
//                   1 => $listRefundOption[1],
//                   2 => $listRefundOption[2]
//               )
           )
        ));
    }

    public function locationType1()
    {
        return array(
            '他団体を吸収する',
            '他団体に合流（子）',
            '他団体を吸収（親）'
        );
    }
  
}

