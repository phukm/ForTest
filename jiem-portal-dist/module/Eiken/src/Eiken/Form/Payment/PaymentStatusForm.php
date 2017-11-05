<?php
namespace Eiken\Form\Payment;

use Zend\Form\Form;
use Zend\Form\Element;

class PaymentStatusForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('paymentstatus');

        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'testSite',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'testSite',

            ),
           'options' => array(
               'value_options' => array(
                    '' => '',
                    '1' => '本会場',
                    '0' => '準会場',
                )
            )
        ));
        $this->add(array(
            'name' => 'examGrade',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'examGrade',
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    '1' => '1級',
                    '2' => '準1級',
                    '3' => '2級',
                    '4' => '準2級',
                    '5' => '3級',
                    '6' => '4級',
                    '7' => '5級'
                )
            )
        ));
        $this->add(array(
            'name' => 'ddlSchoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'value' => '',
                'class' => 'form-control',
                'id' => 'ddlSchoolYear'
            ),
            'options' => array(
                'empty_option' => ''
            )
        ));

        $this->add(array(
            'name' => 'ddlClass',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlClass'
            ),
            'options' => array(
                'empty_option' => ''
            )
        ));

        $this->add(array(
            'name' => 'ddlApplyStatus',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlApplyStatus'
            ),
            'options' => array(
                'empty_option' => ''
            )
        ));

        $this->add(array(
            'name' => 'ddlPaymentStatus',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlPaymentStatus'
            ),
            'options' => array(
                'empty_option' => ''
            )
        ));

        $this->add(array(
            'name' => 'txtFullName',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'txtFullName'

            )
        ));

        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf'
        ));
    }

    // public function year(){
    // $array =array();
    // $array[0] = '';
    // $array['小学校1年相当'] = '小学校1年相当';
    // $array['小学校2年相当'] = '小学校2年相当';
    // $array['小学校3年相当'] = '小学校3年相当';
    // $array['小学校4年相当'] = '小学校4年相当';
    // $array['小学校5年相当'] = '小学校5年相当';
    // $array['小学校6年相当'] = '小学校6年相当';
    // $array['中学校1年相当'] = '中学校1年相当';
    // $array['中学校2年相当'] = '中学校2年相当';
    // $array['中学校3年相当'] = '中学校3年相当';
    // $array['高学校1年相当'] = '高学校1年相当';
    // $array['高学校2年相当'] = '高学校2年相当';
    // $array['高学校3年相当'] = '高学校3年相当';
    // $array['大学1年相当'] = '大学1年相当';
    // $array['大学2年相当'] = '大学2年相当';
    // $array['大学3年相当'] = '大学3年相当';
    // $array['大学4年相当'] = '大学4年相当';
    // return $array;
    // }
    public function kurasu()
    {
        $array = array();
        $array[0] = '';
        $array['クラス01'] = 'クラス01';
        $array['クラス02'] = 'クラス02';
        $array['クラス03'] = 'クラス03';
        $array['クラス04'] = 'クラス04';
        $array['クラス05'] = 'クラス05';
        $array['クラス06'] = 'クラス06';
        $array['クラス07'] = 'クラス07';
        $array['クラス08'] = 'クラス08';
        $array['クラス09'] = 'クラス09';
        $array['クラス10'] = 'クラス10';
        $array['クラス11'] = 'クラス11';
        $array['クラス12'] = 'クラス12';
        return $array;
    }
}

?>