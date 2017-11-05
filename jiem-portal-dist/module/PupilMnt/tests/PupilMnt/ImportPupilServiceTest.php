<?php

namespace PupilMnt;

use Dantai\PrivateSession;
use PupilMnt\PupilConst;

class ImportPupilServiceTest extends \Dantai\Test\AbstractHttpControllerTestCase
{

    /*
     * 1 - Task #319 : Validate Upload File screen
     */
    public function loginFake()
    {
        PrivateSession::setData("111", "");
    }
    
    public function translate($msgKey){
        return $this->getApplicationServiceLocator()->get('MVCTranslator')->translate($msgKey);
    }

    public function testReturnMsgEmptyFileUploadWhenPutParamToFunctionValidateFileImportPupil()
    {
        /* @var $importService \PupilMnt\Service\ImportPupilService*/
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $fileImport = '';
        list($result1, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $message = $this->translate('NotFileSelect');
        $this->assertEquals($message, $result1['message']);
        
        $fileImport = array(
            'name' => '[JIEM_DP_R3]Solution-Unit-Test.docx',
            'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'tmp_name' => '/tmp/phpG43dy3',
            'error' => 4,
            'size' => 1000,
        );
        list($result2, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $message = $this->translate('NotFileSelect');
        $this->assertEquals($message, $result2['message']);
    }
    
    public function testReturnMsgFileNotCsvAndExcelWhenPutParamToFunctionValidateFileImportPupil()
    {
        /* @var $importService \PupilMnt\Service\ImportPupilService*/
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $fileImport = array(
            'name' => '[JIEM_DP_R3]Solution-Unit-Test.docx',
            'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'tmp_name' => '/tmp/phpG43dy3',
            'error' => 0,
            'size' => 1000,
        );
        list($result, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $message = $this->translate('MsgFileNotCSV_28');
        $this->assertEquals($message, $result['message']);
    }
    
    public function testReturnMsgFileNotLikeTemplateWhenPutFileCsvToFunctionValidateFileImportPupil()
    {
        $fileImport = array(
            'name' => 'yatabe2.csv - Copy.csv',
            'type' => 'application/csv',
            'tmp_name' => '/tmp/phpKrbILY',
            'error' => 0,
            'size' => 424,
        );
        
        /* @var $importService \PupilMnt\Service\ImportPupilService*/
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $stringHeaderCsv = '年度（*）,学年一般名称（*）,学年（*）,クラス（*）,番号（*）,氏名（姓）（漢字）（*）,氏名（名）（漢字）（*）,氏名（姓）（カナ）,氏名（名）（カナ）,生年月日,性別,英ナビ！個人ID,英検ID,英検パスワード,取得済級,英検年度,英検回,英検CSEスコアリーディング,英検CSEスコアリスニング,英検CSEスコア作文,英検CSEスコアスピーキング,IBAレベル判定,IBA実施日,IBA CSEスコアリーディング,IBA CSEスコアリスニング,単語レベル,文法レベル';
        $importService->setDataCsv($fileImport['tmp_name'], $stringHeaderCsv);
        list($result, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $message = $this->translate('MsgFileNotLikeTemplate_29_FBR5');
        $this->assertEquals($message, $result['message']);
        
        $stringHeaderCsv = '年度1（*）,学年（*）,クラス（*）,番号（*）,氏名（姓）（漢字）（*）,氏名（名）（漢字）（*）,氏名（姓）（カナ）,氏名（名）（カナ）,生年月日,性別,英ナビ！個人ID,英検ID,英検パスワード,取得済級,英検年度,英検回,英検CSEスコアリーディング,英検CSEスコアリスニング,英検CSEスコア作文,英検CSEスコアスピーキング,IBAレベル判定,IBA実施日,IBA CSEスコアリーディング,IBA CSEスコアリスニング,単語レベル,文法レベル';
        $importService->setDataCsv($fileImport['tmp_name'], $stringHeaderCsv);
        list($result, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $message = $this->translate('MsgFileNotLikeTemplate_29_FBR5');
        $this->assertEquals($message, $result['message']);
        
        $stringHeaderCsv = '年度（*）,学年（*）,クラス（*）,番号（*）,氏名（姓）（漢字）（*）,氏名（名）（漢字）（*）,氏名（姓）（カナ）,氏名（名）（カナ）,生年月日,性別,英ナビ！個人ID,英検ID,英検パスワード,取得済級,英検年度,英検回,英検CSEスコアリーディング,英検CSEスコアリスニング,英検CSEスコア作文,英検CSEスコアスピーキング,IBAレベル判定,IBA実施日,IBA CSEスコアリーディング,IBA CSEスコアリスニング,単語レベル,文法レベル';
        for ($i = 0; $i < 10; $i++) {
            $stringHeaderCsv .= PHP_EOL . '2015,中学1年生,１組,2,田中,次郎,タナカ,ジロウ, 2000/1/2,,,,,,,,,,,,,,,,';
        }
        $importService->setDataCsv($fileImport['tmp_name'], $stringHeaderCsv);
        list($result, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $message = $this->translate('MsgFileNotLikeTemplate_29_FBR5');
        $this->assertEquals($message, $result['message']);
    }
    
    public function testReturnMsgFileNotLikeTemplateWhenPutFileExcel2007ToFunctionValidateFileImportPupil(){
        $fileImport = array(
            'name' => 'yatabe2.csv - Copy.xlsx',
            'type' => 'application/xlsx',
            'tmp_name' => '/tmp/phpKrbILY',
            'error' => 0,
            'size' => 424,
        );
        
        /* @var $importService \PupilMnt\Service\ImportPupilService*/
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $stringHeader = '年度（*）,,学年（*）,クラス（*）,番号（*）,氏名（姓）（漢字）（*）,氏名（名）（漢字）（*）,氏名（姓）（カナ）,氏名（名）（カナ）,生年月日,性別,英ナビ！個人ID,英検ID,英検パスワード,取得済級,英検年度,英検回,英検CSEスコアリーディング,英検CSEスコアリスニング,英検CSEスコア作文,英検CSEスコアスピーキング,IBAレベル判定,IBA実施日,IBA CSEスコアリーディング,IBA CSEスコアリスニング,単語レベル,文法レベル';
        $dataExcel = array(explode(',', $stringHeader));
        $importService->setDataExcel($fileImport['tmp_name'], $dataExcel);
        list($result, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $message = $this->translate('MsgFileNotLikeTemplate_29_FBR5');
        $this->assertEquals($message, $result['message']);
        
        $stringHeader = '年度1（*）,学年（*）,クラス（*）,番号（*）,氏名（姓）（漢字）（*）,氏名（名）（漢字）（*）,氏名（姓）（カナ）,氏名（名）（カナ）,生年月日,性別,英ナビ！個人ID,英検ID,英検パスワード,取得済級,英検年度,英検回,英検CSEスコアリーディング,英検CSEスコアリスニング,英検CSEスコア作文,英検CSEスコアスピーキング,IBAレベル判定,IBA実施日,IBA CSEスコアリーディング,IBA CSEスコアリスニング,単語レベル,文法レベル';
        $dataExcel = array(explode(',', $stringHeader));
        $importService->setDataExcel($fileImport['tmp_name'], $dataExcel);
        list($result, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $message = $this->translate('MsgFileNotLikeTemplate_29_FBR5');
        $this->assertEquals($message, $result['message']);
    }
    
    public function testReturnMsgFileHasNotDataPupilWhenPutParamToFunctionValidateFileImportPupil()
    {
        $fileImport = array(
            'name' => 'yatabe2.csv - Copy.csv',
            'type' => 'application/csv',
            'tmp_name' => '/tmp/phpKrbILY',
            'error' => 0,
            'size' => 424,
        );
        
        /* @var $importService \PupilMnt\Service\ImportPupilService*/
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $stringHeaderCsv = '年度（*）,学年（*）,クラス（*）,番号（*）,氏名（姓）（漢字）（*）,氏名（名）（漢字）（*）,氏名（姓）（カナ）,氏名（名）（カナ）,生年月日,性別,英ナビ！個人ID,英検ID,英検パスワード,取得済級,英検年度,英検回,英検CSEスコアリーディング,英検CSEスコアリスニング,英検CSEスコア作文,英検CSEスコアスピーキング,IBAレベル判定,IBA実施日,IBA CSEスコアリーディング,IBA CSEスコアリスニング,単語レベル,文法レベル';
        $importService->setDataCsv($fileImport['tmp_name'], $stringHeaderCsv);
        list($result, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $message = $this->translate('MsgFileHasNotDataPupil');
        $this->assertEquals($message, $result['message']);
    }
    
    
    public function testReturnMsgFileHasDataPupilGreater5000WhenPutParamToFunctionValidateFileImportPupil()
    {
        $fileImport = array(
            'name' => 'yatabe2.csv - Copy.csv',
            'type' => 'application/csv',
            'tmp_name' => '/tmp/phpKrbILY',
            'error' => 0,
            'size' => 424,
        );
        
        /* @var $importService \PupilMnt\Service\ImportPupilService*/
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $stringHeaderCsv = '年度（*）,学年（*）,クラス（*）,番号（*）,氏名（姓）（漢字）（*）,氏名（名）（漢字）（*）,氏名（姓）（カナ）,氏名（名）（カナ）,生年月日,性別,英ナビ！個人ID,英検ID,英検パスワード,取得済級,英検年度,英検回,英検CSEスコアリーディング,英検CSEスコアリスニング,英検CSEスコア作文,英検CSEスコアスピーキング,IBAレベル判定,IBA実施日,IBA CSEスコアリーディング,IBA CSEスコアリスニング,単語レベル,文法レベル';
        for ($i = 0; $i < 5001; $i++) {
            $stringHeaderCsv .= PHP_EOL . '2015,中学1年生,１組,2,田中,次郎,タナカ,ジロウ, 2000/1/2,,,,,,,,,,,,,,,,,';
        }
        $importService->setDataCsv($fileImport['tmp_name'], $stringHeaderCsv);
        list($result, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $message = $this->translate('MsgFileHasDataPupilGreater1000_30');
        $this->assertEquals($message, $result['message']);
    }
    
    public function testReturnStatusTrueWhenPutParamToFunctionValidateFileImportPupil() {
        $fileImport = array(
            'name' => 'yatabe2.csv - Copy.csv',
            'type' => 'application/csv',
            'tmp_name' => '/tmp/phpKrbILY',
            'error' => 0,
            'size' => 424,
        );

        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');

        $stringHeaderCsv = '年度（*）,学年（*）,クラス（*）,番号（*）,氏名（姓）（漢字）（*）,氏名（名）（漢字）（*）,氏名（姓）（カナ）,氏名（名）（カナ）,生年月日,性別,英ナビ！個人ID,英検ID,英検パスワード,取得済級,英検年度,英検回,英検CSEスコアリーディング,英検CSEスコアリスニング,英検CSEスコア作文,英検CSEスコアスピーキング,IBAレベル判定,IBA実施日,IBA CSEスコアリーディング,IBA CSEスコアリスニング,単語レベル,文法レベル';
        for ($i = 0; $i < 1000; $i++) {
            $stringHeaderCsv .= PHP_EOL . '2015,中学1年生,１組,2,田中,次郎,タナカ,ジロウ, 2000/1/2,,,,,,,,,,,,,,,,,';
        }
        $importService->setDataCsv($fileImport['tmp_name'], $stringHeaderCsv);
        list($result, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $status = 1;
        $this->assertEquals($status, $result['status']);
    }
    
    public function testReturnCorrectDataImportWhenPutParamToFunctionGetDataFromFileImport() {
        $fileImport = array(
            'name' => 'yatabe2.csv - Copy.csv',
            'type' => 'application/csv',
            'tmp_name' => '/tmp/phpKrbILY',
            'error' => 0,
            'size' => 424,
        );

        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');

        $stringHeaderCsv = '年度（*）,学年（*）,クラス（*）,番号（*）,氏名（姓）（漢字）（*）,氏名（名）（漢字）（*）,氏名（姓）（カナ）,氏名（名）（カナ）,生年月日,性別,英ナビ！個人ID,英検ID,英検パスワード,取得済級,英検年度,英検回,英検CSEスコアリーディング,英検CSEスコアリスニング,英検CSEスコア作文,英検CSEスコアスピーキング,IBAレベル判定,IBA実施日,IBA CSEスコアリーディング,IBA CSEスコアリスニング,単語レベル,文法レベル';
        for ($i = 0; $i < 4; $i++) {
            $stringHeaderCsv .= PHP_EOL . '2015,,中学1年生,１組,2,田中,次郎,タナカ,ジロウ, 2000/1/2,,,,,,,,,,,,,,,,,';
        }

        $importService->setDataCsv($fileImport['tmp_name'], $stringHeaderCsv);
        list($result, $dataFile) = $importService->validateFileImportPupil($fileImport);
        $dataImport = $importService->getDataFromFileImport($dataFile);
        $this->assertEquals($i, count($dataImport));
        $this->assertEquals(26, count($dataImport[0]));
    }
    
    public function testReturnErrorWhenPutEmptyParamPupilNumberToFunctionValidatePupilNumber(){
        $pupilNumber = '';
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $error = $importService->validatePupilNumber($pupilNumber);
        $errorExpect[$this->translate('ImportPupilNumber')] = $this->translate('MsgPupilNumberError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutPupilNumberIsNotNumberToFunctionValidatePupilNumber(){
        
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $pupilNumber = -1;
        $error = $importService->validatePupilNumber($pupilNumber);
        $errorExpect[$this->translate('ImportPupilNumber')] = $this->translate('MsgPupilNumberError2');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnEmptyErrorWhenPutPupilNumberToFunctionValidatePupilNumber(){
        
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $pupilNumber = 0;
        $error = $importService->validatePupilNumber($pupilNumber);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutEmptyFirstNameKanjiToFunctionValidatePupilNameKanji(){
        
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $firstName = '';
        $lastName = 'fdsfsdf';
        $error = $importService->validatePupilNameKanji($firstName, $lastName);
        $errorExpect[$this->translate('ImportFirstnameKanji')] = $this->translate('MsgFirstnameKanjiError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutEmptyLastNameKanjiToFunctionValidatePupilNameKanji(){
        
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $firstName = 'fdsfsdf';
        $lastName = '';
        $error = $importService->validatePupilNameKanji($firstName, $lastName);
        $errorExpect[$this->translate('ImportLastnameKanji')] = $this->translate('MsgLastnameKanjiError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutNameKanjiNotFullSizeToFunctionValidatePupilNameKanji(){
        
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $firstName = 'fdsfsd';
        $lastName = 'fdsfsd';
        $error = $importService->validatePupilNameKanji($firstName, $lastName);
        $errorExpect[$this->translate('ImportNameKanji')] = $this->translate('MsgNameKanjiError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutNameKanjiGreater20CharacterToFunctionValidatePupilNameKanji(){
        
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $firstName = '結果が見が見結果が見が見';
        $lastName = '結果が見が見結果が見が見';
        $error = $importService->validatePupilNameKanji($firstName, $lastName);
        $errorExpect[$this->translate('ImportNameKanji')] = $this->translate('MsgNameKanjiError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnEmptyErrorWhenPutNameKanjiToFunctionValidatePupilNameKanji(){
        
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $firstName = 'が見が見見';
        $lastName = '結果が見が';
        $error = $importService->validatePupilNameKanji($firstName, $lastName);
        //$errorExpect[$this->translate('ImportNameKanji')] = $this->translate('MsgNameKanjiError1');
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
    }
    
    
    public function testReturnErrorWhenPutNotNameKanaToFunctionValidatePupilNameKana(){
        
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $firstName = 'が見が見見';
        $lastName = 'が見が見見';
        $error = $importService->validatePupilNameKana($firstName, $lastName);
        $errorExpect[$this->translate('ImportFirstnameKana')] = $this->translate('MsgFirstnameKanaError2');
        $errorExpect[$this->translate('ImportLastnameKana')] = $this->translate('MsgLastnameKanaError2');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnEmptyErrorWhenPutNameKanaToFunctionValidatePupilNameKana(){
        
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $firstName = 'カタカナ';
        $lastName = 'ﾀｶﾀｶﾅ';
        $error = $importService->validatePupilNameKana($firstName, $lastName);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
    }

    
    public function testReturnErrorWhenPutFailedFormatBirthdayAndGenderToFunctionValidatePupilBirthdayAndGender(){
        
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $birthday = '2015-04-04';
        $gender = '1';
        $error = $importService->validatePupilBirthdayAndGender($birthday, $gender);
        $errorExpect[$this->translate('ImportBirthday')] = $this->translate('MsgBirthdayError2');
        $errorExpect[$this->translate('ImportGender')] = $this->translate('MsgGenderError1');
        $this->assertEquals($errorExpect, $error);
        
        $birthday = '2015/4/04';
        $gender = '1';
        $error = $importService->validatePupilBirthdayAndGender($birthday, $gender);
        $errorExpect[$this->translate('ImportBirthday')] = $this->translate('MsgBirthdayError2');
        $errorExpect[$this->translate('ImportGender')] = $this->translate('MsgGenderError1');
        $this->assertEquals($errorExpect, $error);
        
        $birthday = '2015/04/9';
        $gender = '1';
        $error = $importService->validatePupilBirthdayAndGender($birthday, $gender);
        $errorExpect[$this->translate('ImportBirthday')] = $this->translate('MsgBirthdayError2');
        $errorExpect[$this->translate('ImportGender')] = $this->translate('MsgGenderError1');
        $this->assertEquals($errorExpect, $error);
        
        $birthday = '15/04/9';
        $gender = '1';
        $error = $importService->validatePupilBirthdayAndGender($birthday, $gender);
        $errorExpect[$this->translate('ImportBirthday')] = $this->translate('MsgBirthdayError2');
        $errorExpect[$this->translate('ImportGender')] = $this->translate('MsgGenderError1');
        $this->assertEquals($errorExpect, $error);
        
        $birthday = '15/09/2015';
        $gender = '1';
        $error = $importService->validatePupilBirthdayAndGender($birthday, $gender);
        $errorExpect[$this->translate('ImportBirthday')] = $this->translate('MsgBirthdayError2');
        $errorExpect[$this->translate('ImportGender')] = $this->translate('MsgGenderError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutYearBirthdayLessThan1916AndGenderToFunctionValidatePupilBirthdayAndGender(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $birthday = '1915/06/06';
        $gender = '1';
        $error = $importService->validatePupilBirthdayAndGender($birthday, $gender);
        $errorExpect[$this->translate('ImportBirthday')] = $this->translate('MsgBirthdayError3');
        $errorExpect[$this->translate('ImportGender')] = $this->translate('MsgGenderError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutBirthdayGreaterThanNowAndGenderToFunctionValidatePupilBirthdayAndGender(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $birthday = date('Y/m/d', time() + 86400);
        $gender = '1';
        $error = $importService->validatePupilBirthdayAndGender($birthday, $gender);
        $errorExpect[$this->translate('ImportBirthday')] = $this->translate('MsgBirthdayError4');
        $errorExpect[$this->translate('ImportGender')] = $this->translate('MsgGenderError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnEmptyErrorWhenPutBirthdayAndGenderToFunctionValidatePupilBirthdayAndGender(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $birthday = date('Y/m/d');
        $gender = '男';
        $error = $importService->validatePupilBirthdayAndGender($birthday, $gender);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
        
        $gender = '女';
        $error = $importService->validatePupilBirthdayAndGender($birthday, $gender);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutFailedFormatEnaviIdToFunctionValidatePupilEnaviIdAndEikenIdAndPassword(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $enaviId = 'a123456789';
        $eikenId = '';
        $password = '';
        $error = $importService->validatePupilEnaviIdAndEikenIdAndPassword($enaviId, $eikenId, $password);
        $errorExpect[$this->translate('ImportEinaviId')] = $this->translate('MsgEinaviIdError1');
        $this->assertEquals($errorExpect, $error);
        
        $enaviId = '01234567891';
        $eikenId = '';
        $password = '';
        $error = $importService->validatePupilEnaviIdAndEikenIdAndPassword($enaviId, $eikenId, $password);
        $errorExpect[$this->translate('ImportEinaviId')] = $this->translate('MsgEinaviIdError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutFailedFormatEikenIdToFunctionValidatePupilEnaviIdAndEikenIdAndPassword(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $enaviId = '';
        $eikenId = 'a1234567890';
        $password = '';
        $error = $importService->validatePupilEnaviIdAndEikenIdAndPassword($enaviId, $eikenId, $password);
        $errorExpect[$this->translate('ImportEikenId')] = $this->translate('MsgEikenIdError1');
        $this->assertEquals($errorExpect, $error);
        
        $enaviId = '';
        $eikenId = '012345678912';
        $password = '';
        $error = $importService->validatePupilEnaviIdAndEikenIdAndPassword($enaviId, $eikenId, $password);
        $errorExpect[$this->translate('ImportEikenId')] = $this->translate('MsgEikenIdError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutFailedFormatEikenPasswordToFunctionValidatePupilEnaviIdAndEikenIdAndPassword(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $enaviId = '';
        $eikenId = '';
        $password = '!a23b';
        $error = $importService->validatePupilEnaviIdAndEikenIdAndPassword($enaviId, $eikenId, $password);
        $errorExpect[$this->translate('ImportEikenPassword')] = $this->translate('MsgEikenPasswordError1');
        $this->assertEquals($errorExpect, $error);
        
        $enaviId = '';
        $eikenId = '';
        $password = 'ab2';
        $error = $importService->validatePupilEnaviIdAndEikenIdAndPassword($enaviId, $eikenId, $password);
        $errorExpect[$this->translate('ImportEikenPassword')] = $this->translate('MsgEikenPasswordError1');
        $this->assertEquals($errorExpect, $error);
        
        $enaviId = '';
        $eikenId = '';
        $password = 'ab2ab25';
        $error = $importService->validatePupilEnaviIdAndEikenIdAndPassword($enaviId, $eikenId, $password);
        $errorExpect[$this->translate('ImportEikenPassword')] = $this->translate('MsgEikenPasswordError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnEmptyErrorWhenPutParamToFunctionValidatePupilEnaviIdAndEikenIdAndPassword(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $enaviId = '';
        $eikenId = '';
        $password = '';
        $error = $importService->validatePupilEnaviIdAndEikenIdAndPassword($enaviId, $eikenId, $password);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
        
        $enaviId = '0123456789';
        $eikenId = '01234567890';
        $password = 'abc123';
        $error = $importService->validatePupilEnaviIdAndEikenIdAndPassword($enaviId, $eikenId, $password);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
    }
    
    
    public function testReturnErrorWhenPutFailedEikenLevelToFunctionValidatePupilEikenLevelAndYearAndKai(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $eikenLevel = '3d';
        $eikenYear = '';
        $kai = '';
        $error = $importService->validatePupilEikenLevelAndYearAndKai($eikenLevel, $eikenYear, $kai);
        $errorExpect[$this->translate('ImportEikenLevel')] = $this->translate('MsgEikenLevelError1');
        $errorExpect[$this->translate('ImportEikenYear')] = $this->translate('MsgEikenYearError1');
        $errorExpect[$this->translate('ImportKai')] = $this->translate('MsgEikenKaiError1');
        $this->assertEquals($errorExpect, $error);

    }
    
    public function testReturnErrorWhenPutFailedEikenYearToFunctionValidatePupilEikenLevelAndYearAndKai(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $eikenLevel = '準2級';
        $eikenYear = '15';
        $kai = 2;
        $error = $importService->validatePupilEikenLevelAndYearAndKai($eikenLevel, $eikenYear, $kai);
        $errorExpect[$this->translate('ImportEikenYear')] = $this->translate('MsgEikenYearError2');
        $this->assertEquals($errorExpect, $error);
        
        $eikenLevel = '準2級';
        $eikenYear = '2008';
        $kai = 2;
        $error = $importService->validatePupilEikenLevelAndYearAndKai($eikenLevel, $eikenYear, $kai);
        $errorExpect[$this->translate('ImportEikenYear')] = $this->translate('MsgEikenYearError2');
        $this->assertEquals($errorExpect, $error);
        
        $eikenLevel = '準2級';
        $eikenYear = date('Y') + 3;
        $kai = 2;
        $error = $importService->validatePupilEikenLevelAndYearAndKai($eikenLevel, $eikenYear, $kai);
        $errorExpect[$this->translate('ImportEikenYear')] = $this->translate('MsgEikenYearError2');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutFailedKaiToFunctionValidatePupilEikenLevelAndYearAndKai(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $eikenLevel = '準2級';
        $eikenYear = date('Y');
        $kai = 'a';
        $error = $importService->validatePupilEikenLevelAndYearAndKai($eikenLevel, $eikenYear, $kai);
        $errorExpect[$this->translate('ImportKai')] = $this->translate('MsgEikenKaiError2');
        $this->assertEquals($errorExpect, $error);
        
        $eikenLevel = '準2級';
        $eikenYear = date('Y');
        $kai = 4;
        $error = $importService->validatePupilEikenLevelAndYearAndKai($eikenLevel, $eikenYear, $kai);
        $errorExpect[$this->translate('ImportKai')] = $this->translate('MsgEikenKaiError2');
        $this->assertEquals($errorExpect, $error);
        
        $eikenLevel = '準2級';
        $eikenYear = date('Y');
        $kai = 0;
        $error = $importService->validatePupilEikenLevelAndYearAndKai($eikenLevel, $eikenYear, $kai);
        $errorExpect[$this->translate('ImportKai')] = $this->translate('MsgEikenKaiError2');
        $this->assertEquals($errorExpect, $error);
    }

    public function testReturnEmptyErrorWhenPutParamToFunctionValidatePupilEikenLevelAndYearAndKai(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $eikenLevel = '';
        $eikenYear = date('Y');
        $kai = 'a';
        $error = $importService->validatePupilEikenLevelAndYearAndKai($eikenLevel, $eikenYear, $kai);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
        
        $eikenLevel = '準2級';
        $eikenYear = date('Y');
        $kai = 3;
        $error = $importService->validatePupilEikenLevelAndYearAndKai($eikenLevel, $eikenYear, $kai);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutParamNotNumberToFunctionValidatePupilEikenScore(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $reading = 'a';
        $listening = 'b';
        $writing = 'c';
        $speaking = 'd';
        $error = $importService->validatePupilEikenScore($reading, $listening, $writing, $speaking);
        $errorExpect[$this->translate('ImportEikenScoreReading')] = $this->translate('MsgEikenScoreReadingError1');
        $errorExpect[$this->translate('ImportEikenScoreListening')] = $this->translate('MsgEikenScoreListeningError1');
        $errorExpect[$this->translate('ImportEikenScoreWriting')] = $this->translate('MsgEikenScoreWritingError1');
        $errorExpect[$this->translate('ImportEikenScoreSpeaking')] = $this->translate('MsgEikenScoreSpeakingError1');
        $this->assertEquals($errorExpect, $error);
        
        $reading = -1;
        $listening = -2;
        $writing = -3;
        $speaking = -4;
        $error = $importService->validatePupilEikenScore($reading, $listening, $writing, $speaking);
        $errorExpect[$this->translate('ImportEikenScoreReading')] = $this->translate('MsgEikenScoreReadingError1');
        $errorExpect[$this->translate('ImportEikenScoreListening')] = $this->translate('MsgEikenScoreListeningError1');
        $errorExpect[$this->translate('ImportEikenScoreWriting')] = $this->translate('MsgEikenScoreWritingError1');
        $errorExpect[$this->translate('ImportEikenScoreSpeaking')] = $this->translate('MsgEikenScoreSpeakingError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutParamNotScoreToFunctionValidatePupilEikenScore(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $reading = 1000;
        $listening = 1000;
        $writing = 1000;
        $speaking = 1000;
        $error = $importService->validatePupilEikenScore($reading, $listening, $writing, $speaking);
        $errorExpect[$this->translate('ImportEikenScoreReading')] = $this->translate('MsgEikenScoreReadingError2');
        $errorExpect[$this->translate('ImportEikenScoreListening')] = $this->translate('MsgEikenScoreListeningError2');
        $errorExpect[$this->translate('ImportEikenScoreWriting')] = $this->translate('MsgEikenScoreWritingError2');
        $errorExpect[$this->translate('ImportEikenScoreSpeaking')] = $this->translate('MsgEikenScoreSpeakingError2');
        $this->assertEquals($errorExpect, $error);
        
    }
    
    public function testReturnEmptyErrorWhenPutParamToFunctionValidatePupilEikenScore(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $reading = '';
        $listening = '';
        $writing = '';
        $speaking = '';
        $error = $importService->validatePupilEikenScore($reading, $listening, $writing, $speaking);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
        
        $reading = 999;
        $listening = 999;
        $writing = 999;
        $speaking = 999;
        $error = $importService->validatePupilEikenScore($reading, $listening, $writing, $speaking);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutFailedIbaLevelToFunctionValidatePupilIba(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $ibaLevel = 'abc';
        $ibaDate = '';
        $error = $importService->validatePupilIBA($ibaLevel, $ibaDate);
        $errorExpect[$this->translate('ImportIbaLevel')] = $this->translate('MsgIBALevelError1');
        $errorExpect[$this->translate('ImportIbaDate')] = $this->translate('MsgIBADateError1');
        $this->assertEquals($errorExpect, $error);

    }
    
    public function testReturnErrorWhenPutFailedIbaDateToFunctionValidatePupilIba(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $ibaLevel = '3級';
        $ibaDate = '15/09/2015';
        $error = $importService->validatePupilIBA($ibaLevel, $ibaDate);
        $errorExpect[$this->translate('ImportIbaDate')] = $this->translate('MsgIBADateError2');
        $this->assertEquals($errorExpect, $error);
        
        $ibaLevel = '3級';
        $ibaDate = '2015-09-09';
        $error = $importService->validatePupilIBA($ibaLevel, $ibaDate);
        $errorExpect[$this->translate('ImportIbaDate')] = $this->translate('MsgIBADateError2');
        $this->assertEquals($errorExpect, $error);
        
        $ibaLevel = '3級';
        $ibaDate = '2015/9/09';
        $error = $importService->validatePupilIBA($ibaLevel, $ibaDate);
        $errorExpect[$this->translate('ImportIbaDate')] = $this->translate('MsgIBADateError2');
        $this->assertEquals($errorExpect, $error);
        
        $ibaLevel = '3級';
        $ibaDate = '2015/09/9';
        $error = $importService->validatePupilIBA($ibaLevel, $ibaDate);
        $errorExpect[$this->translate('ImportIbaDate')] = $this->translate('MsgIBADateError2');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutIbaScoreNotNumberToFunctionValidatePupilIbaScore(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');  
        $reading = 'a';
        $listening = -1;
        $error = $importService->validatePupilIBAScore($reading, $listening);
        $errorExpect[$this->translate('ImportIbaScoreReading')] = $this->translate('MsgIBAScoreReadingError1');
        $errorExpect[$this->translate('ImportIbaScoreListening')] = $this->translate('MsgIBAScoreListeningError1');
        $this->assertEquals($errorExpect, $error);
        
        $reading = -1;
        $listening = 'asdf';
        $error = $importService->validatePupilIBAScore($reading, $listening);
        $errorExpect[$this->translate('ImportIbaScoreReading')] = $this->translate('MsgIBAScoreReadingError1');
        $errorExpect[$this->translate('ImportIbaScoreListening')] = $this->translate('MsgIBAScoreListeningError1');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutIbaScoreNotScoreToFunctionValidatePupilIbaScore(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');  
        $reading = 1000;
        $listening = 1000;
        $error = $importService->validatePupilIBAScore($reading, $listening);
        $errorExpect[$this->translate('ImportIbaScoreReading')] = $this->translate('MsgIBAScoreReadingError2');
        $errorExpect[$this->translate('ImportIbaScoreListening')] = $this->translate('MsgIBAScoreListeningError2');
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnEmptyErrorWhenPutIbaScoreToFunctionValidatePupilIbaScore(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');   
        $reading = '';
        $listening = '';
        $error = $importService->validatePupilIBAScore($reading, $listening);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
        
        $reading = 0;
        $listening = 0;
        $error = $importService->validatePupilIBAScore($reading, $listening);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
        
        $reading = 999;
        $listening = 999;
        $error = $importService->validatePupilIBAScore($reading, $listening);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutFailedLevelToFunctionValidatePupilWordLevelAndGrammarLevel(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');   
        $wordLevel = 'a';
        $grammarLevel = 'bc';
        $error = $importService->validatePupilWordLevelAndGrammarLevel($wordLevel, $grammarLevel);
        $errorExpect[$this->translate('ImportWordLevel')] = $this->translate('MsgWordLevelError1');
        $errorExpect[$this->translate('ImportGrammarLevel')] = $this->translate('MsgGrammarLevelError1');
        $this->assertEquals($errorExpect, $error);
        
    }
    
    public function testReturnEmptyErrorWhenPutParamToFunctionValidatePupilWordLevelAndGrammarLevel(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');   
        
        $wordLevel = '';
        $grammarLevel = '';
        $error = $importService->validatePupilWordLevelAndGrammarLevel($wordLevel, $grammarLevel);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
        
        $wordLevel = '準1級';
        $grammarLevel = '5級';
        $error = $importService->validatePupilWordLevelAndGrammarLevel($wordLevel, $grammarLevel);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
    }
    
    public function getEntityPupilMock($resultPupil = array()) {
        if (!$resultPupil) {
            for ($i = 0; $i < 5; $i++) {
                $resultPupil[$i] = array(
                    'id' => $i + 1,
                    'firstNameKanji' => '漢字漢字',
                    'lastNameKanji' => '字字字',
                    'firstNameKana' => 'カナ' . $i,
                    'lastNameKana' => 'ナカ' . $i,
                    'number' => $i,
                    'year' => 2015,
                    'className' => '1A',
                    'schoolyearName' => 'Khoi1',
                    'gender' => '男',
                    'birthday' => new \DateTime('now')
                );
            }
            
        }
        $pupilMock = $this->getMockBuilder('Application\Entity\Repository\PupilRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $pupilMock->expects($this->any())
                ->method('getListPupilOfClassByOrg')
                ->will($this->returnValue($resultPupil));
        return $pupilMock;
    }
    
    public function testReturnErrorWhenPutPupilNumberExistInDatabaseToFunctionValidateDataPupilNumberDuplicate(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');     
        
        for ($i = 0; $i < 200; $i++) {
            $key = '2015||-||Khoi1||-||1C||-||' . $i;
            $dataCheckDuplicateInFile[$key] = $i;
        }
        for ($i = 0; $i < 200; $i++) {
            $key = '2015||-||Khoi1||-||1A||-||' . $i;
            $dataCheckDuplicateInDb[$key] = $i;
        }
        $year = 2015;
        $orgSchoolYear = 'Khoi1';
        $class = '1A';
        $pupilNumber = 1;
        $keyCheckDuplicate = $year . '||-||' . $orgSchoolYear . '||-||' . $class . '||-||' . $pupilNumber;
        
        $error = $importService->validateDataPupilNumberDuplicate($keyCheckDuplicate, $dataCheckDuplicateInFile, $dataCheckDuplicateInDb);
        $errorExpect[$this->translate('ImportDuplicatePupilNumber')] = $this->translate('MsgDuplicatePupilError1');
        
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnErrorWhenPutPupilNumberExistInFileImportToFunctionValidateDataPupilNumberDuplicate(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');   

        for ($i = 0; $i < 200; $i++) {
            $key = '2015||-||Khoi1||-||1D||-||' . $i;
            $dataCheckDuplicateInFile[$key] = $i;
        }
        for ($i = 0; $i < 200; $i++) {
            $key = '2015||-||Khoi1||-||1A||-||' . $i;
            $dataCheckDuplicateInDb[$key] = $i;
        }
        
        $year = 2015;
        $orgSchoolYear = 'Khoi1';
        $class = '1D';
        $pupilNumber = 01;
        $keyCheckDuplicate = $year . '||-||' . $orgSchoolYear . '||-||' . $class . '||-||' . $pupilNumber;
        
        $error = $importService->validateDataPupilNumberDuplicate($keyCheckDuplicate, $dataCheckDuplicateInFile, $dataCheckDuplicateInDb);
        $errorExpect[$this->translate('ImportDuplicatePupilNumber')] = $this->translate('MsgDuplicatePupilError1');
        
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnEmptyErrorWhenPutDataPupilToFunctionValidateDataPupilNumberDuplicate(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');    
        
        for ($i = 0; $i < 200; $i++) {
            $key = '2015||-||Khoi1||-||1D||-||' . $i;
            $dataCheckDuplicateInFile[$key] = $i;
        }
        for ($i = 0; $i < 200; $i++) {
            $key = '2015||-||Khoi1||-||1A||-||' . $i;
            $dataCheckDuplicateInDb[$key] = $i;
        }
        
        $year = 2015;
        $orgSchoolYear = 'Khoi1';
        $class = '1E';
        $pupilNumber = 1;
        $keyCheckDuplicate = $year . '||-||' . $orgSchoolYear . '||-||' . $class . '||-||' . $pupilNumber;
        
        $error = $importService->validateDataPupilNumberDuplicate($keyCheckDuplicate, $dataCheckDuplicateInFile, $dataCheckDuplicateInDb);
        $errorExpect = array();
        $this->assertEquals($errorExpect, $error);
    }
    
    public function testReturnEmptyArrayWhenPutParamToFunctionGetDataDuplicatePupil(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $orgId = 10000;
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');    
        for ($i = 0; $i < 200; $i++) {
            $dataImport[$i] = array(
                'firstnameKanji' => '漢字漢字',
                'lastnameKanji' => '字字字' . $i,
                'firstnameKana' => 'カナ' . $i,
                'lastnameKana' => 'ナカ' . $i,
                'birthday' => '2015/09/09',
                'year' => 2015,
                'orgSchoolYear' => 'Khoi 1',
                'class' => '1B',
                'pupilNumber' => 1000,
                'gender' => '男',
            );
        }
        $dataExpect = array();
        list($dataDuplicate, $dataDetailInFile, $dataDetailInOrg) = $importService->getDataDuplicatePupilName($orgId, $dataImport);
        
        $this->assertEquals($dataExpect, $dataDuplicate);

    }
    
    public function testReturnDuplicateArrayInFileWhenPutParamToFunctionGetDataDuplicatePupil(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $orgId = 10000;
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');    
        for ($i = 0; $i < 200; $i++) {
            $dataImport[$i] = array(
                'firstnameKanji' => '漢字漢字',
                'lastnameKanji' => '字字字',
                'firstnameKana' => 'カナ' . ($i % 5 == 0 ? 1 : $i),
                'lastnameKana' => 'ナカ' . ($i % 5 == 0 ? 1 : $i),
                'birthday' => '2015/09/09',
                'year' => 2015,
                'orgSchoolYear' => 'Khoi 1',
                'class' => '1B',
                'pupilNumber' => 1000,
                'gender' => '男',
            );
        }
        
        list($dataDuplicate, $dataDetailInFile, $dataDetailInOrg) = $importService->getDataDuplicatePupilName($orgId, $dataImport);

        $this->assertGreaterThan(0, count($dataDuplicate));
        $statusExpect = PupilConst::DUPLICATE_IN_FILE_IMPORT;
        foreach($dataDuplicate as $value){
            $this->assertEquals($value['status'], $statusExpect);
        }
    }
    
    public function testReturnDuplicateArrayInDbWhenPutParamToFunctionGetDataDuplicatePupil(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $orgId = 10000;
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');    
        for ($i = 0; $i < 200; $i++) {
            $dataImport[$i] = array(
                'firstnameKanji' => '漢字漢字',
                'lastnameKanji' => '字字字',
                'firstnameKana' => 'カナ' . $i,
                'lastnameKana' => 'ナカ' . $i,
                'birthday' => date('Y/m/d'),
                'year' => 2015,
                'orgSchoolYear' => 'Khoi 1',
                'class' => '1B',
                'pupilNumber' => 1000,
                'gender' => '男',
            );
        }

        $entityPupilMock = $this->getEntityPupilMock();
        $importService->setEntityPupil($entityPupilMock);
        
        list($dataDuplicate, $dataDetailInFile, $dataDetailInOrg) = $importService->getDataDuplicatePupilName($orgId, $dataImport);
        $this->assertGreaterThan(0, count($dataDuplicate));
        $statusExpect = PupilConst::DUPLICATE_IN_DATABASE;
        foreach($dataDuplicate as $value){
            $this->assertEquals($value['status'], $statusExpect);
        }
    }
    
    
    public function testReturnDuplicateArrayInDbAndFileWhenPutParamToFunctionGetDataDuplicatePupil(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $orgId = 10000;
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');    
        for ($i = 0; $i < 200; $i++) {
            $dataImport[$i] = array(
                'firstnameKanji' => '漢字漢字',
                'lastnameKanji' => '字字字',
                'firstnameKana' => 'カナ' . ($i % 2 == 0 ? 1 : $i+5),
                'lastnameKana' => 'ナカ' . ($i % 2 == 0 ? 1 : $i+5),
                'birthday' => date('Y/m/d'),
                'year' => 2015,
                'orgSchoolYear' => 'Khoi 1',
                'class' => '1B',
                'pupilNumber' => 1000,
                'gender' => '男',
            );
        }

        $entityPupilMock = $this->getEntityPupilMock();
        $importService->setEntityPupil($entityPupilMock);
        
        list($dataDuplicate, $dataDetailInFile, $dataDetailInOrg) = $importService->getDataDuplicatePupilName($orgId, $dataImport);
        $this->assertGreaterThan(0, count($dataDuplicate));
        $statusExpect = PupilConst::DUPLICATE_IN_FILE_IMPORT_AND_DATABASE;
        foreach($dataDuplicate as $value){
            $this->assertEquals($value['status'], $statusExpect);
        }
    }
    public function testSeperateNameFunctionWithEmptyArray(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */       
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');            
        $result = $importService->seperateFullname(array());        
        $this->assertEquals(count($result), 0);        
    }
    
    public function testSeperateNameFunctionWithPopularName(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */       
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');            
        $sampleData = array(
            array(),
            array(2016,1,1,1,'坂本龍馬','','','','?','',16190093737,'nLqkOD','','','','','','','','','','','','',''),
            array(2016,1,1,1,'織田信長','','','','?','',16190093737,'nLqkOD','','','','','','','','','','','','',''),
        );
        $result = $importService->seperateFullname($sampleData);        
        $this->assertEquals(count($result), 2);
        
        foreach ($result as $key=>$value){
            $this->assertEquals(count($value), 26);
            $this->assertEquals($value[4].$value[5], $sampleData[$key+1][4]);
        }
    }
    public function testHadUseOldNameKanji(){
        /* @var $importService \PupilMnt\Service\ImportPupilService */
        $string = '𠀮';
        $importService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');    
        $return = $importService->isUseOldKanji($string);
        $this->assertEquals(false, $return);
    }
}