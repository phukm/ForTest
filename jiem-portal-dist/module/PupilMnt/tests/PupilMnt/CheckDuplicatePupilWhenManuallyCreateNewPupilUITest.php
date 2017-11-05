<?php
namespace PupilMnt;

class CheckDuplicatePupilWhenManuallyCreateNewPupilUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    public function testDisplayCorrectTitleOfCreateScreen()
    {
        $this->login();
        $this->dispatch('/pupil/pupil/add');
        $this->assertQueryContentRegex('body', '/重複生徒一覧/');
    }
    
    public function testDisplayCorrectMessageOfCreateScreen()
    {
        $this->login();
        $this->dispatch('/pupil/pupil/add');
        $this->assertQueryContentRegex('body', '/システム上に、同一の可能性がある生徒情報があります。/');
    }
    
    public function testDisplayCorrectHeaderInTableOfCreateScreen()
    {
        $this->login();
        $this->dispatch('/pupil/pupil/add');
        $this->assertQueryContentRegex('body', '/年度/');
        $this->assertQueryContentRegex('body', '/学年/');
        $this->assertQueryContentRegex('body', '/クラス/');
        $this->assertQueryContentRegex('body', '/番号/');
        $this->assertQueryContentRegex('body', '/氏名（漢字）/');
        $this->assertQueryContentRegex('body', '/氏名（カナ）/');
        $this->assertQueryContentRegex('body', '/生年月日/');
        $this->assertQueryContentRegex('body', '/性別/');
    }
}
    