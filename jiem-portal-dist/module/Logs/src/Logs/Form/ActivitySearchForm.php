<?php
namespace Logs\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ActivitySearchForm extends Form{
    public function __construct()
    {
        parent::__construct('activitylogssearch');
        
        $this->add(array(
            'name' => 'orgno',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'orgno',
                'class' => 'form-control',
                'maxlength' => 255,
                'onkeypress' => 'return ACTIVITY_LOGS.isNumber(event)'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'orgname',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'orgname',
                'class' => 'form-control',
                'maxlength' => 255
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'userid',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'userid',
                'class' => 'form-control',
                'maxlength' => 255
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'actiontype',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'actiontype',
                'class' => 'form-control'
            ),
            'options' => array(
                'value_options' => array(
                    '' => '',
                    '登録' => '登録',
                    '更新' => '更新',
                    '確認' => '確認',
                    '削除' => '削除',
                    'ダウンロード' => 'ダウンロード',
                    '案内状作成' => '案内状作成',
                    '印刷' => '印刷',
                    'ログイン' => 'ログイン',
                )
            )
        ));
        
        $this->add(array(
            'name' => 'datetime1',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'datetimepicker1',
                'class' => 'form-control'
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
    }
}