<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace PupilMnt;

class ImportPupilControllerTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    public function translate($msgKey){
        return $this->getApplicationServiceLocator()->get('MVCTranslator')->translate($msgKey);
    }
    
    public function testShowFormImportPupilWhenAccessToImportPupilScreen(){
        $this->login();
        $this->dispatch('/pupil/import-pupil/index');
        $this->assertQueryContentRegex('a.breadcrumbs-current', '/アップロード/');
        $this->assertQueryContentRegex('body', '/団体サポートシステムでは、姓名を分けて保持する必要があります。/');
        $this->assertQueryContentRegex('body', '/アップロードする名簿の氏名が、姓名を分けずに一つの項目に入力してある場合は、/');
        $this->assertQueryContentRegex('body', '/ここをクリックしてください。大部分の姓名を自動的に分ける機能をご利用頂けます。/');
        $this->assertQueryContentRegex('body', '/（自動的に分割できない姓名もありますので、自動分割後にご確認ください）/');
        $this->assertQueryContentRegex('#csvfilefocus strong', '/参照/');
    }
    
    public function testShowMsgEmptyFileUploadWhenPutParamToFormImportPupil(){
        $this->login();
        $params = array(
            'fileImport' => '',
        );
        $this->dispatch('/pupil/import-pupil/index', \Zend\Http\Request::METHOD_POST, $params);
        $result = json_decode($this->getResponse()->getBody(),true);
        $this->assertEquals($this->translate('NotFileSelect'), $result['message']);
    }
    
    public function testShowMsgFileNotCsvWhenPutParamToFormImportPupil(){
        $this->login();
        $params = array(
            'fileImport' => array(
                'name' => '[JIEM_DP_R3]Solution-Unit-Test.docx',
                'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'tmp_name' => '/tmp/phpG43dy3',
                'error' => 0,
                'size' => 1000,
            ),
            //'unitTestResult' => array('status' => 0, 'message' => 'Success'),
            //'unitTestDataFile' => $dataImport
        );
        $this->dispatch('/pupil/import-pupil/index', \Zend\Http\Request::METHOD_POST, $params);
        $result = json_decode($this->getResponse()->getBody(),true);
        $this->assertEquals($this->translate('MsgFileNotCSV_28'), $result['message']);
    }
    
    public function testShowMsgFileNotLikeTemplateWhenPutParamToFormImportPupil(){
        $this->login();
        $params = array(
            'fileImport' => array(
                'name' => '[JIEM_DP_R3]Solution-Unit-Test.docx',
                'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'tmp_name' => '/tmp/phpG43dy3',
                'error' => 0,
                'size' => 1000,
            ),
            'unitTestResult' => array('status' => 0, 'message' => $this->translate('MsgFileNotCSV_28')),
            'unitTestDataFile' => array()
        );
        $this->dispatch('/pupil/import-pupil/index', \Zend\Http\Request::METHOD_POST, $params);
        $result = json_decode($this->getResponse()->getBody(),true);
        $this->assertEquals($this->translate('MsgFileNotCSV_28'), $result['message']);
    }
    
    public function testShowMsgFileHasDataPupilGreater1000WhenPutParamToFormImportPupil(){
        $this->login();
        $params = array(
            'fileImport' => array(
                'name' => '[JIEM_DP_R3]Solution-Unit-Test.docx',
                'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'tmp_name' => '/tmp/phpG43dy3',
                'error' => 0,
                'size' => 1000,
            ),
            'unitTestResult' => array('status' => 0, 'message' => $this->translate('MsgFileHasDataPupilGreater1000_30')),
            'unitTestDataFile' => array()
        );
        $this->dispatch('/pupil/import-pupil/index', \Zend\Http\Request::METHOD_POST, $params);
        $result = json_decode($this->getResponse()->getBody(),true);
        $this->assertEquals($this->translate('MsgFileHasDataPupilGreater1000_30'), $result['message']);
    }
    
    public function testShowMsgFileHasNotDataPupilWhenPutParamToFormImportPupil(){
        $this->login();
        $params = array(
            'fileImport' => array(
                'name' => '[JIEM_DP_R3]Solution-Unit-Test.docx',
                'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'tmp_name' => '/tmp/phpG43dy3',
                'error' => 0,
                'size' => 1000,
            ),
            'unitTestResult' => array('status' => 0, 'message' => $this->translate('MsgFileHasNotDataPupil')),
            'unitTestDataFile' => array()
        );
        $this->dispatch('/pupil/import-pupil/index', \Zend\Http\Request::METHOD_POST, $params);
        $result = json_decode($this->getResponse()->getBody(),true);
        $this->assertEquals($this->translate('MsgFileHasNotDataPupil'), $result['message']);
    }
    
    
    public function testReturnTrueWhenPutParamToFormImportPupil(){
        $this->login();
        for($i=0;$i<2;$i++){
            $dataImport[$i] = array(
                1 => 2015,
                2 => '',
                3 => '中学1年生',
                4 => '１組',
                5 => '2',
                6 => '田中',
                7 => '次郎',
                8 => 'タナカ',
                9 => 'ジロウ',
                10 => '2000/1/2',
                11 => '',
                12 => '',
                13 => '',
                14 => '',
                15 => '',
                16 => '',
                17 => '',
                18 => '',
                19 => '',
                20 => '',
                21 => '',
                22 => '',
                23 => '',
                24 => '',
                25 => '',
                26 => '',
                27 => '',
            );
        }
        $params = array(
            'fileImport' => array(
                'name' => '[JIEM_DP_R3]Solution-Unit-Test.docx',
                'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'tmp_name' => '/tmp/phpG43dy3',
                'error' => 0,
                'size' => 1000,
            ),
            'unitTestResult' => array('status' => 1, 'message' => 'Success'),
            'unitTestDataFile' => $dataImport
        );
        $this->dispatch('/pupil/import-pupil/index', \Zend\Http\Request::METHOD_POST, $params, true);
        $result = json_decode($this->getResponse()->getBody(),true);
        $this->assertEquals(1, $result['status']);
    }
}