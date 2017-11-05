<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use AccessKey\AccessKeyConst;
use Dantai\PrivateSession;

class AcessKeyLogicTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    public function getEikenScheduleByCurrentTime($dataOrg = array()) {
        $data = array();
        $data[0] = array(
            'year' => 2015,
            'kai' => 1
        );
        $eikenSc = $this->getMockBuilder('Application\Entity\Repository\EikenScheduleRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $eikenSc->expects($this->any())
                ->method('getEikenScheduleByCurrentTime')
                ->will($this->returnValue($data));
        return $eikenSc;
    }
    public function getAccessKeyMock() {
        $accessKey = new \Application\Entity\AccessKey();
        $accessKey->setAccessKey('accesskey');
        $accessKey->setOrganizationNo('orgno');
        $accessKey->setYear(2015);
        $accessKey->setKai(1);
        $accessKey->setStatus('Enable');
        $accessKey->setIsDelete(0);
        
        $accessKeyMock = $this->getMockBuilder('Application\Entity\Repository\AccessKeyRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $accessKeyMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($accessKey));
        return $accessKeyMock;
    }
    public function getUserMock() {
        $user = new Application\Entity\User();
        $user->setStatusInit(1);
        
        $useMock = $this->getMockBuilder('Application\Entity\Repository\UserRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $useMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($user));
        return $useMock;
    }
    public function getEntityMock(){
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
    
    public function testDisableAccessKeyFunction(){
        /* @var $basicConstruction \BasicConstruction\Service\UACService */
        $basicConstruction = $this->getApplicationServiceLocator()->get('BasicConstruction\Service\UACServiceInterface');
        
        $basicConstruction->setEikenRepos($this->getEikenScheduleByCurrentTime(),$this->getEntityMock());
        $basicConstruction->setAccessKeyRepos($this->getAccessKeyMock(),$this->getEntityMock());
        $basicConstruction->setUserRepos($this->getUserMock(),$this->getEntityMock());
        $result = $basicConstruction->disableAccessKey('orgNo',1,$this->getEntityMock());
        $this->assertEquals(true,$result);
    }
}
