<?php

namespace Logs;

class ActivityLogsUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    /**
     * @var \Application\Entity\ActivityLog 
     */
    private $logEntity;
    public function removeFakeLog(){
        if ($this->logEntity){
            $this->getEntityManager()->remove($this->logEntity);
            $this->getEntityManager()->flush();
            $this->logEntity = null;
        }
    }
    public function tearDown() {
        $this->removeFakeLog();
        parent::tearDown();
    }

    public function updateData($field = '', $value = '')
    {
        $data = array(
            'organizationNo' => $this->getIdentityDataSession()['organizationNo'],
            'organizationName' => $this->getIdentityDataSession()['organizationName'],
            'userID' => $this->getIdentityDataSession()['userId'],
            'userName' => $this->getIdentityDataSession()['firstName'].$this->getIdentityDataSession()['lastName'],
            'screenName' => 'Logs Activity',
            'actionName' => 'Unit Test',
            'insertAt' => new \DateTime('now')
        );
        
        $data[$field] = $value;
        
        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->getEntityManager(), 'Application\Entity\ActivityLog');
        $this->logEntity = $hydrator->hydrate($data, new \Application\Entity\ActivityLog());
        $this->getEntityManager()->persist($this->logEntity);
        $this->getEntityManager()->flush();
    }
    
    // UI Search Form unit test
    public function testShowCorrectSearchFormTitleWhenAccessPageViewActivityLogs() {
        $this->login();
        $this->dispatch('/logs/activity/index');
        $this->assertQueryContentContains('div#search-header', '検索条件');
    }
    
    public function testShowCorrectLabelOnSearchFormWhenAccessPageViewActivityLogs(){
        $this->login();
        $this->dispatch('/logs/activity/index');
        $this->assertQueryContentRegex('div#search-box', '/団体番号/');
        $this->assertQueryContentRegex('div#search-box', '/団体名/');
        $this->assertQueryContentRegex('div#search-box', '/ユーザID/');
        $this->assertQueryContentRegex('div#search-box', '/操作/');
        $this->assertQueryContentRegex('div#search-box', '/から/');
        $this->assertQueryContentRegex('div#search-box', '/まで/');
        $this->assertQueryContentRegex('div#search-box', '/検索/');
        $this->assertQueryContentRegex('div#search-box', '/クリア/');
    }
    
    // UI List Table unit test
    public function testShowCorrectListTitleWhenAccessPageViewActivityLogs(){
        $this->login();
        $this->dispatch('/logs/activity/index');
        $this->assertQueryContentRegex('div#list-header', '/アクセス、操作ログ/');
    }
    
    public function testShowCorrectOrgNoColumnOnListTableWhenAccessPageViewActivityLogs(){
        $this->login();
        $this->dispatch('/logs/activity/index');
        $this->assertQueryContentRegex('div#list-table', '/団体番号/');
        $this->assertQueryContentRegex('div#list-table', '/団体名（漢字）/');
        $this->assertQueryContentRegex('div#list-table', '/日時/');
        $this->assertQueryContentRegex('div#list-table', '/ユーザID/');
        $this->assertQueryContentRegex('div#list-table', '/氏名/');
        $this->assertQueryContentRegex('div#list-table', '/画面/');
        $this->assertQueryContentRegex('div#list-table', '/操作/');
    }
    
    // Function test
    public function testDataOfOrgNoOnListTableWhenAccessPageViewActivityLogs()
    {
        $this->login();
        $this->updateData();
        $this->dispatch('/logs/activity/index');
        $this->assertQueryContentRegex('td.org-no', '/'.$this->getIdentityDataSession()['organizationNo'].'/');
    }
    
    public function testDataOfOrgNameOnListTableWhenAccessPageViewActivityLogs()
    {
        $this->login();
        $this->updateData();
        $this->dispatch('/logs/activity/index');
        $this->assertQueryContentRegex('td.org-name', '/'.$this->getIdentityDataSession()['organizationName'].'/');
    }
    
    public function testDataOfDateTimeOnListTableWhenAccessPageViewActivityLogs()
    {
        $this->login();
        $this->updateData();
        $this->dispatch('/logs/activity/index');
        $dateTest = str_replace('/', '\\/', $this->logEntity->getInsertAt()->format('Y/m/d'));
        $this->assertQueryContentRegex('td.date-time', '/'.$dateTest.'/');
    }
    
    public function testDataOfUserIdOnListTableWhenAccessPageViewActivityLogs()
    {
        $this->login();
        $this->updateData();
        $this->dispatch('/logs/activity/index');
        $this->assertQueryContentRegex('td.user-id', '/'.$this->getIdentityDataSession()['userId'].'/');
    }
    
    public function testDataOfUserNameOnListTableWhenAccessPageViewActivityLogs()
    {
        $this->login();
        $this->updateData();
        $this->dispatch('/logs/activity/index');
        $this->assertQueryContentRegex('td.user-name', '/'.$this->getIdentityDataSession()['firstName'].$this->getIdentityDataSession()['lastName'].'/');
    }
    
    public function testDataOfScreenNameOnListTableWhenAccessPageViewActivityLogs()
    {
        $this->login();
        $this->updateData();
        $this->dispatch('/logs/activity/index');
        $this->assertQueryContentRegex('td.screen', '/Logs Activity/');
    }
    
    public function testDataOfActionNameOnListTableWhenAccessPageViewActivityLogs()
    {
        $this->login();
        $this->updateData();
        $this->dispatch('/logs/activity/index');
        $this->assertQueryContentRegex('td.action', '/Unit Test/');
    }
}
