<?php
namespace InvitationSetting;

use InvitationMnt\Service\SettingService;
use InvitationMnt\InvitationConst;

class InvitationSettingShowDetailTest extends \Dantai\Test\AbstractHttpControllerTestCase
{  
    public function getService()
    {
        return $this->getApplicationServiceLocator()->get('InvitationMnt\Service\SettingServiceInterface');
    }
    
    public function getInvitationSettingMock($data = array())
    {
        $invSettingMock = $this->getMockBuilder('Application\Entity\Repository\InvitationSettingRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $invSettingMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($data));
        return $invSettingMock;
    }
    
    public function testShowCorrectInvitationSetting()
    {
        $this->login();
        $invSettingObject = new \Application\Entity\InvitationSetting();
        $invSettingObject->setId(1);
        $invSettingService = $this->getService();
        $invSettingService->setInvitationSettingRepository($this->getInvitationSettingMock($invSettingObject));
        $invitationSetting = $invSettingService->getInvitationSetting(1);
        if($invitationSetting){
            $result = 1;
            $this->assertEquals($result, InvitationConst::EXIST);
        }else{
            $result = 0;
            $this->assertEquals($result, InvitationConst::NOT_EXIST);
        }
    }
}