<?php

namespace IBAControllerTest;

class ApplyIBAPolicyRegisterTest extends \Dantai\Test\AbstractHttpControllerTestCase {

    public function getAPIMock()
    {
        $api = $this->getMockBuilder('\Dantai\Api\UkestukeClient')
                ->disableOriginalConstructor()
                ->getMock();

        $api->expects($this->any())
                ->method('callEir2a01')
                ->will($this->returnValue(0));
        return $api;
    }
    public function testWhenAccessPageRegisterThenShowCorrectBreadCrumb() {
        $ibaService = $this->getApplicationServiceLocator()->get('IBA\Service\IBAServiceInterface');
        $ibaService->setUketukeClient($this->getAPIMock());
        $this->login();
        $data = array(
            'firtNameKanji' => '外五',
            'lastNameKanji' => '土八',
            'mailAddress' => 'vuvanphuc.1990@gmail.com',
            'confirmEmail' => 'vuvanphuc.1990@gmail.com'
        );
        \Dantai\PublicSession::setData('IBAPolicyData', $data);
        
        $this->dispatch('/iba/iba/add');
        $this->assertQueryContentRegex('ul.breadcrumb', '/各種検定申込/');
        $this->assertQueryContentRegex('ul.breadcrumb', '/英検IBA申込/');
        $this->assertQueryContentRegex('ul.breadcrumb', '/申込情報登録/');
    }
}
