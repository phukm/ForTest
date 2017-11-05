<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Eiken;

use Eiken\Service\ApplyEikenOrgService;
use Eiken\EikenConst;

class SaveLogApplyEikenTest extends \Dantai\Test\AbstractHttpControllerTestCase
{
    private $action = 'create';
    private $data = array(
        'MainHallExpectApplyNo7' => '2',
        'MainHallExpectApplyNo6' => '2',
        'MainHallExpectApplyNo5' => '2',
        'MainHallExpectApplyNo4' => '2',
        'MainHallExpectApplyNo3' => '2',
        'MainHallExpectApplyNo2' => '2',
        'MainHallExpectApplyNo1' => '2',
        'ExpectApplyNo7' => '3',
        'ExpectApplyNo6' => '3',
        'ExpectApplyNo5' => '3',
        'ExpectApplyNo4' => '3',
        'ExpectApplyNo3' => '3',
        'refundStatus' => '1'
    );
    private $userId = 'TESTUSER';
    
    private $logMainHall = array(
        'lev1' => 1,
        'preLev1' => 1,
        'lev2' => 1,
        'preLev2' => 1,
        'lev3' => 1,
        'lev4' => 1,
        'lev5' => 1,
        'oldLev1' => 1,
        'oldPreLev1' => 1,
        'oldLev2' => 1,
        'oldPreLev2' => 1,
        'oldLev3' => 1,
        'oldLev4' => 1,
        'oldLev5' => 1
    );
    private $logStandardHall = array(
        'lev2' => 1,
        'preLev2' => 1,
        'lev3' => 1,
        'lev4' => 1,
        'lev5' => 1,
        'oldLev2' => 1,
        'oldPreLev2' => 1,
        'oldLev3' => 1,
        'oldLev4' => 1,
        'oldLev5' => 1
    );
    
    public function getOrganizationMock($dataOrg = array())
    {
        $orgMock = $this->getMockBuilder('Application\Entity\Repository\OrganizationRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $orgMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($dataOrg));
        return $orgMock;
    }
    
    public function getEikenScheduleMock($dataSchedule = array())
    {
        $scheduleMock = $this->getMockBuilder('Application\Entity\Repository\EikenScheduleRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $scheduleMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($dataSchedule));
        return $scheduleMock;
    }
    
    public function getEntityMock()
    {
        $repositoryMock = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'getReference', 'getClassMetadata', 'persist', 'flush','clear'), array(), '', false);
        
        $repositoryMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object)array('name' => 'aClass')));
        $repositoryMock->expects($this->any())
                        ->method('persist')
                        ->will($this->returnValue(null));
        $repositoryMock->expects($this->any())
                        ->method('flush')
                        ->will($this->returnValue(null));
        $repositoryMock->expects($this->any())
                        ->method('getReference')
                        ->will($this->returnValue(null));
        $repositoryMock->expects($this->any())
                        ->method('clear')
                        ->will($this->returnValue(null));
        
        return $repositoryMock;
    }
    
    public function testSaveDataSuccessIntoDatabase()
    {
        $this->login();
        $orgObject = new \Application\Entity\Organization();
        $orgObject->setId(1);
        $orgObject->setOrganizationNo("99991609");
        $orgObject->setOrgNameKanji("Admin");
        $scheduleObject = new \Application\Entity\EikenSchedule();
        $scheduleObject->setId(1);
        $logData = array(
            'action' => $this->action,
            'params' => $this->data,
            // Data from database
            'oldStatusRefund' => 1,
            'userId' => $this->userId
        );
        $applyEikenService = $this->getApplicationServiceLocator()->get('Eiken\Service\ApplyEikenOrgServiceInterface');
        $applyEikenService->setOrgRepo($this->getOrganizationMock($orgObject));
        $applyEikenService->setEikenScheduleRepo($this->getEikenScheduleMock($scheduleObject));
        $result = $applyEikenService->saveLogApplyEiken(
            $this->logMainHall, $this->logStandardHall, $logData, $this->getEntityMock()
        );
        $this->assertEquals($result, EikenConst::SAVE_DATA_INTO_DATABASE_SUCCESS);
    }
}