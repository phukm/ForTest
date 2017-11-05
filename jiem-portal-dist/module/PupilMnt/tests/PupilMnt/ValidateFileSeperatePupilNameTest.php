<?php

namespace PupilMnt;

use Dantai\PrivateSession;
use PupilMnt\PupilConst;

class ValidateFileSeperatePupilNameTest extends \Dantai\Test\AbstractHttpControllerTestCase
{
    public function translate($msgKey){
        return $this->getApplicationServiceLocator()->get('MVCTranslator')->translate($msgKey);
    }
    
    public function testShowErrorMessageWhenFileIsEmptyExcel()
    {
        $this->login();
        $fileImportExcel = array(
            'name' => 'orginal-names.xls',
            'type' => 'application/vnd.ms-excel',
            'tmp_name' => '/tmp/phpEoiXig',
            'error' => 4,
            'size' => 22528
        );        
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        list($result, $dataFile) = $importService->validateFileSeperatePupil($fileImportExcel);
        $this->assertEquals($result['status'], 0);
        $this->assertEquals($result['message'], $this->translate('NotFileSelect'));
    }
    
    public function testShowErrorMessageWhenFileIsEmptyCSV()
    {
        $this->login();
        $fileImportCSV = array(
            'name' => 'orginal-names.csv',
            'type' => 'application/csv',
            'tmp_name' => '/tmp/phpKrbILY',
            'error' => 4,
            'size' => 424,
        );
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        list($result, $dataFile) = $importService->validateFileSeperatePupil($fileImportCSV);
        $this->assertEquals($result['status'], 0);
        $this->assertEquals($result['message'], $this->translate('NotFileSelect'));
    }
    
    public function testShowErrorMessageWhenInvalidFileExtensionExcel()
    {
        $this->login();
        $fileImportExcel = array(
            'name' => 'orginal-names.wrongextension',
            'type' => 'application/vnd.ms-excel',
            'tmp_name' => '/tmp/phpEoiXig',
            'error' => 0,
            'size' => 22528
        );
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        list($result, $dataFile) = $importService->validateFileSeperatePupil($fileImportExcel);
        $this->assertEquals($result['status'], 0);
        $this->assertEquals($result['message'], $this->translate('MsgFileNotCSV_28'));
    }
    
    public function testShowErrorMessageWhenInvalidFileExtensionCSV()
    {
        $this->login();
        $fileImportCSV = array(
            'name' => 'orginal-names.wrongextension',
            'type' => 'application/csv',
            'tmp_name' => '/tmp/phpKrbILY',
            'error' => 0,
            'size' => 424,
        );
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        list($result, $dataFile) = $importService->validateFileSeperatePupil($fileImportCSV);
        $this->assertEquals($result['status'], 0);
        $this->assertEquals($result['message'], $this->translate('MsgFileNotCSV_28'));
    }
    
    public function testShowErrorMessageWhenWrongHeaderExcel()
    {
        $this->login();
        $fileImportExcel = array(
            'name' => 'orginal-names.xls',
            'type' => 'application/vnd.ms-excel',
            'tmp_name' => '/tmp/phpEoiXig',
            'error' => 0,
            'size' => 22528
        );
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $stringHeader = 'Wrong header';
        $importService->setDataExcel($fileImportExcel['tmp_name'], $stringHeader);
        list($result, $dataFile) = $importService->validateFileSeperatePupil($fileImportExcel);
        $this->assertEquals($result['status'], 0);
        $this->assertEquals($result['message'], $this->translate('MsgFileNotLikeTemplate_29_FBR5'));
    }
    
    public function testShowErrorMessageWhenWrongHeaderCSV()
    {
        $this->login();
        $fileImportCSV = array(
            'name' => 'orginal-names.csv',
            'type' => 'application/csv',
            'tmp_name' => '/tmp/phpKrbILY',
            'error' => 0,
            'size' => 424,
        );
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $stringHeader = 'Wrong header';
        $importService->setDataCsv($fileImportCSV['tmp_name'], $stringHeader);
        list($result, $dataFile) = $importService->validateFileSeperatePupil($fileImportCSV);
        $this->assertEquals($result['status'], 0);
        $this->assertEquals($result['message'], $this->translate('MsgFileNotLikeTemplate_29_FBR5'));
    }
}