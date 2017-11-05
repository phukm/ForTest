<?php
namespace History;

class EmptyNameKanaUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
      
    public function testDisplayCorrectTitleTableInEmptyNameKanaScreen()
    {
        $this->login();
        $this->dispatch('/history/iba/empty-name-kana');
        $this->assertQueryContentRegex('body', '/生徒情報/');
    }
    
    public function testDisplayCorrectHeaderTableInEmptyNameKanaScreen()
    {
        $this->login();
        $this->dispatch('/history/iba/empty-name-kana');
        $this->assertQueryContentRegex('body', '/年度/');
        $this->assertQueryContentRegex('body', '/学年/');
        $this->assertQueryContentRegex('body', '/クラス/');
        $this->assertQueryContentRegex('body', '/番号/');
        $this->assertQueryContentRegex('body', '/氏名（漢字）/');
        $this->assertQueryContentRegex('body', '/氏名（カナ）/');
        $this->assertQueryContentRegex('body', '/生年月日/');
        $this->assertQueryContentRegex('body', '/性別/');
        $this->assertQueryContentRegex('body', '/詳細/');
    }
}