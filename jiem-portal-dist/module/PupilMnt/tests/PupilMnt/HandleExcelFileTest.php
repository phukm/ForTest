<?php
namespace PupilMnt;

class HandleExcelFileTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    public function testAccessToPupilPageThenShowCorrectExportPopupTitle(){
        $this->login();
        $this->dispatch('/pupil/pupil/index');
        $this->assertQueryContentRegex('#exportModal', '/ダウンロード/');
    }
    
    public function testAccessToPupilPageThenShowCorrectExportExcelOption(){
        $this->login();
        $this->dispatch('/pupil/pupil/index');
        $this->assertQueryContentRegex('#exportModal', '/Excel形式/');
    }
    
    public function testAccessToPupilPageThenShowCorrectExportCsvOption(){
        $this->login();
        $this->dispatch('/pupil/pupil/index');
        $this->assertQueryContentRegex('#exportModal', '/CSV形式/');
    }
}