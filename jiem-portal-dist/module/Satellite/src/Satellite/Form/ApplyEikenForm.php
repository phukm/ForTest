<?php

namespace Satellite\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Dantai\Utility\DateHelper;

class ApplyEikenForm extends Form {
    protected $entityManager;
    
    public function __construct($serviceLocatior) {
        parent::__construct('frmApplyEiken');
        $this->entityManager = $serviceLocatior->get('doctrine.entitymanager.orm_default');
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'frmApplyEiken');
        
        $this->add(array(
            'name' => 'hallType1',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'hallType1',
                'class' => 'form-control',
                'value' => '',
                'disabled' => 'disabled'
            ),
            'options' => array(
                'value_options' => array(
                    '1' => '本会場'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'hallType2',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'hallType2',
                'class' => 'form-control',
                'value' => '',
                'disabled' => 'disabled'
            ),
            'options' => array(
                'value_options' => array(
                    '1' => '本会場'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'hallType3',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'hallType3',
                'class' => 'form-control',
                'value' => '',
                'disabled' => 'disabled',
                'onChange','APPLY_EIKEN.showApplyInfo()'
            ),
            'options' => array(
                'value_options' => array(
                    '0' => '準会場',
                    '1' => '本会場'
                )
            )
        ));
        $this->add(array(
            'name' => 'hallType4',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'hallType4',
                'class' => 'form-control',
                'value' => '',
                'disabled' => 'disabled',
                'onChange','APPLY_EIKEN.showApplyInfo()'
            ),
            'options' => array(
                'value_options' => array(
                    '0' => '準会場',
                    '1' => '本会場'
                )
            )
        ));
        $this->add(array(
            'name' => 'hallType5',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'hallType5',
                'class' => 'form-control',
                'value' => '',
                'disabled' => 'disabled',
                'onChange','APPLY_EIKEN.showApplyInfo()'
            ),
            'options' => array(
                'value_options' => array(
                    '0' => '準会場',
                    '1' => '本会場'
                )
            )
        ));
        $this->add(array(
            'name' => 'hallType6',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'hallType6',
                'class' => 'form-control',
                'value' => '',
                'disabled' => 'disabled',
                'onChange','APPLY_EIKEN.showApplyInfo()'
            ),
            'options' => array(
                'value_options' => array(
                    '0' => '準会場',
                    '1' => '本会場'
                )
            )
        ));
        $this->add(array(
            'name' => 'hallType7',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'hallType7',
                'class' => 'form-control',
                'value' => '',
                'disabled' => 'disabled',
                'onChange','APPLY_EIKEN.showApplyInfo()'
            ),
            'options' => array(
                'value_options' => array(
                    '0' => '準会場',
                    '1' => '本会場'
                )
            )
        ));
        $this->add(array(
            'name' => 'txtFirstNameKanji',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtFirstNameKanji',
                'placeholder' => '姓',
                'maxlength' => '18',
            )
        ));

        $this->add(array(
            'name' => 'txtLastNameKanji',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtLastNameKanji',
                'placeholder' => '名',
                'maxlength' => '18',
            )
        ));
        
        $this->add(array(
            'name' => 'txtFirstNameKana',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtFirstNameKana',
                'placeholder' => '姓',
                'maxlength' => '18',
            )
        ));

        $this->add(array(
            'name' => 'txtLastNameKana',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtLastNameKana',
                'placeholder' => '名',
                'maxlength' => '18'
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'rdSex',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'id' => 'rdSex',
                'value' => '',
            ),
            'options' => array(
                'value_options' => array(
                    array(
                        'value' => '1',
                        'attributes' => array(
                            'class' => 'radio-box',
                        ),
                        'label' => '男',
                        'label_attributes' => array('class' => 'text-normal')
                    ),
                    array(
                        'value' => '0',
                        'attributes' => array(
                            'class' => 'radio-box',
                        ),
                        'label' => '女',
                        'label_attributes' => array('class' => 'text-normal rd-pleft20')
                    )
                )
            )
        ));

        $this->add(array(
            'name' => 'ddlYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'ddlYear',
            ),
            'options' => array(
                'value_options' => array()
            )
        ));

        $this->add(array(
            'name' => 'ddlMonth',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'ddlMonth',
            )
            ,
            'options' => array(
                'value_options' => array(
                    '' => ''
                )
            )
        ));

        $this->add(array(
            'name' => 'ddlDay',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'ddlDay',
            ),
            'options' => array(
                'value_options' => array(
                    '' => ''
                )
            )
        ));

        $this->add(array(
            'name' => 'txtPostalCode1',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control postcode',
                'id' => 'txtPostalCode1',
                'maxlength' => '3',
            )
        ));

        $this->add(array(
            'name' => 'txtPostalCode2',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control postcode',
                'id' => 'txtPostalCode2',
                'maxlength' => '4',
            )
        ));
        
        $this->add(array(
            'name' => 'ddlCity',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'ddlCity',
            ),
            'options' => array(
                'value_options' => $this->getListCityName()
            )
        ));
        
        $this->add(array(
            'name' => 'txtDistrict',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'txtDistrict',
            )
        ));
        
        $this->add(array(
            'name' => 'txtTown',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'txtTown',
            )
        ));
        
        $this->add(array(
            'name' => 'txtPhoneNo1',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'txtPhoneNo1',
                'maxlength' => '5',
            )
        ));
        $this->add(array(
            'name' => 'txtPhoneNo2',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'txtPhoneNo2',
                'maxlength' => '5',
            )
        ));
        $this->add(array(
            'name' => 'txtPhoneNo3',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'txtPhoneNo3',
                'maxlength' => '5',
            )
        ));
        
        $this->add(array(
            'name' => 'txtEmail',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'txtEmail',
            )
        ));
        
        $this->add(array(
            'name' => 'ddlJobName',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'ddlJobName',
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    '1' => '学生生徒',
                    '2' => '教職員',
                    '3' => '公務員',
                    '4' => '会社員'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'ddlSchoolCode',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control inset-shadow',
                'id' => 'ddlSchoolCode',
            ),
            'options' => array(
                'value_options' => array(
                    '' => ''
                )
            )
        ));
        $this->add(array(
            'name'    => 'chooseKyu[]',
            'type'    => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'use_hidden_element' => false
            ),
        ));
    }

    public function year() {
        $array = array();
        for ($i = 1990; $i < 2020; $i ++) {
            if ($i == 1990) {
                $array [0] = '';
            }
            $array [$i] = $i;
        }
        return $array;
    }

    public function month() {
        $array = array();
        for ($i = 1; $i < 13; $i ++) {
            if ($i == 1) {
                $array [0] = '';
            }
            $array [$i] = $i;
        }
        return $array;
    }

    public function day() {
        $array = array();
        for ($i = 1; $i < 32; $i ++) {
            if ($i == 1) {
                $array [0] = '';
            }
            $array [$i] = $i;
        }
        return $array;
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
    
    public function setListBirthDay(\DateTime $birthday = null)
    {
        $y = !empty($birthday)? $birthday->format('Y'):'';
        $m = !empty($birthday)? $birthday->format('m'):'';
        $d = !empty($birthday)? $birthday->format('d'):'';

        $listyear = array();
        $yearTo = (int) date('Y');
        $fromYear = $yearTo - 99;
        $listyear[''] = '';
        for ($i = $yearTo; $i >= $fromYear; $i --) {
            $listyear[$i] = DateHelper::gengo($i);
        }
        $listmonth = array();
        $listday = array();
        for ($i = 1; $i < 32; $i ++) {
            if ($i == 1) {
                $listday[''] = '';
                $listmonth[''] = '';
            }
            if($i < 13){
                $listmonth[$i] = $i;
            }
            $listday[$i] = $i;
        }
        $this->get('ddlYear')->setValueOptions($listyear)
                                ->setAttributes(array(
                                    'value' => $y,
                                    'selected' => true
                                ));
        $this->get('ddlMonth')->setValueOptions($listmonth)
                                ->setAttributes(array(
                                    'value' => $m,
                                    'selected' => true
                                ));
        $this->get('ddlDay')->setValueOptions($listday)
                                ->setAttributes(array(
                                    'value' => $d,
                                    'selected' => true
                                ));

        return $this;
    }

}

?>