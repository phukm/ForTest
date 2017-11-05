<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use AccessKey\AccessKeyConst;
use Dantai\PrivateSession;

class AccessKeyServiceFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    public function getOrganizationMock($dataOrg = array()) {
        $orgMock = $this->getMockBuilder('Application\Entity\Repository\OrganizationRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $orgMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($dataOrg));
        return $orgMock;
    }
    public function getAccessKeyMock() {
        $accessKey = new \Application\Entity\AccessKey();
        $accessKey->setAccessKey('accesskey');
        $accessKey->setOrganizationNo('orgno');
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
    public function getUserMock($user = array(),$status) {
        if ($status == '1') {
            $user = array(
                'id' => 113
            );
        }
        $useMock = $this->getMockBuilder('Application\Entity\Repository\UserRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $useMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($user));
        return $useMock;
    }
    public function getSesClientMock(){
        $sesClient = $this->getMockBuilder('Dantai\Aws\AwsSesClient')
                ->disableOriginalConstructor()
                ->getMock();
        
        $sesClient->expects($this->any())
                ->method('deliver')
                ->will($this->returnValue(null));
        return $sesClient;
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
    
    public function testReturnFalseWhenPutEmptyParamToFunctionSaveFirstUser(){
        $accessKey = rand(10000000, 99999999);
        $organizationNo = 0;
        $paramsDefault = array(
            'userId' => 'abc123',
            'firstNameKanji' => 'ズン',
            'lastNameKanji' => 'ズン', 
            'emailAddress' => 'abc@gmail.com',
        );
        /* @var $accessKeyService \AccessKey\Service\AccessKeyService */
        $accessKeyService = $this->getApplicationServiceLocator()->get('AccessKey\Service\AccessKeyServiceInterface');
        foreach ($paramsDefault as $file => $value){
            $params = $paramsDefault;
            $params[$file] = '';
            $result = $accessKeyService->saveFirstUser($params, $organizationNo);
            $this->assertEquals(AccessKeyConst::ERROR_EMPTY_PARAMS, $result);
        }
    }
    
    public function testReturnFalseWhenPutEmptyOrgNoToFunctionSaveFirstUser(){
        $organizationNo = '';
        $params = array(
            'userId' => 'abc123',
            'firstNameKanji' => 'ズン',
            'lastNameKanji' => 'ズン',
            'password' => '123456',
            'emailAddress' => 'abc@gmail.com',
        );
        /* @var $accessKeyService \AccessKey\Service\AccessKeyService */
        $accessKeyService = $this->getApplicationServiceLocator()->get('AccessKey\Service\AccessKeyServiceInterface');
        $result = $accessKeyService->saveFirstUser($params, $organizationNo);
        $this->assertEquals(AccessKeyConst::ERROR_EMPTY_ORGNO, $result);  
    }
    
    public function testReturnFalseWhenPutOrgExistUserToFunctionSaveFirstUser(){
        $organizationNo = 0;
        $params = array(
            'userId' => 'abc123',
            'firstNameKanji' => 'ズン',
            'lastNameKanji' => 'ズン',
            'password' => '123456',
            'emailAddress' => 'abc@gmail.com',
        );
        /* @var $accessKeyService \AccessKey\Service\AccessKeyService */
        $accessKeyService = $this->getApplicationServiceLocator()->get('AccessKey\Service\AccessKeyServiceInterface');
        $accessKeyService->setOrganizationRepository($this->getOrganizationMock(array('id'=>123)));
        $accessKeyService->setUserRepository($this->getUserMock('',1));
        $result = $accessKeyService->saveFirstUser($params, $organizationNo);
        $this->assertEquals(AccessKeyConst::ERROR_EXIST_USER_OF_ORG, $result);
    }
    
    public function testReturnTrueWhenPutParamToFunctionSaveFirstUser(){
        $accessKeyStr = rand(10000000, 99999999);
        $params = array(
            'userId' => 'abc123',
            'firstNameKanji' => 'ズン',
            'lastNameKanji' => 'ズン',
            'password' => '123456',
            'emailAddress' => 'abc@gmail.com',
        );
        $organizationNo = 99999991999;
        
//        set mock for find org
        $objectOrg = new \Application\Entity\Organization;
        $objectOrg->setId(1);
        /* @var $accessKeyService \AccessKey\Service\AccessKeyService */
        $accessKeyService = $this->getApplicationServiceLocator()->get('AccessKey\Service\AccessKeyServiceInterface');
        $accessKeyService->setOrganizationRepository($this->getOrganizationMock($objectOrg));
        
        $accessKeyService->setUserRepository($this->getUserMock('',2));
        
        $accessKeyService->setAwsSesClient($this->getSesClientMock());
        $result = $accessKeyService->saveFirstUser($params, $organizationNo,$this->getEntityMock());
        $this->assertEquals(AccessKeyConst::SAVE_DATABASE_SUCCESS, $result);
    }
    
    public function testReturnFalseWhenPutWrongParamToFunctionDisableAccessKey(){
        /* @var $accessKeyService \AccessKey\Service\AccessKeyService */
        $accessKey = 'xdfsf';
        $organizationNo = 99965200;
        $accessKeyService = $this->getApplicationServiceLocator()->get('AccessKey\Service\AccessKeyServiceInterface');
        $result = $accessKeyService->disableAccessKey($accessKey, $organizationNo);
        $this->assertEquals(AccessKeyConst::ERROR_NOT_EXIST_ACCESS_KEY, $result);
    }
    
    public function testReturnTrueWhenPutParamToFunctionDisableAccessKey(){
        /* @var $accessKeyService \AccessKey\Service\AccessKeyService */
        $accessKeyService = $this->getApplicationServiceLocator()->get('AccessKey\Service\AccessKeyServiceInterface');
        $accessKeyService->setAccessRepository($this->getAccessKeyMock());
        $accessKeyService->setEntityManager($this->getEntityMock());
        $result = $accessKeyService->disableAccessKey('accesskey','orgno');
        $this->assertEquals(AccessKeyConst::SAVE_DATABASE_SUCCESS, $result);
    }
    
    public function testActiveUserFucntion(){
        $user = new \Application\Entity\AccessKey();
        $user->setOrganizationNo('orgno');
        $user->setStatus('Disable');
        $user->setIsDelete(0);
        
        
        /* @var $accessKeyService \AccessKey\Service\AccessKeyService */
        $accessKeyService = $this->getApplicationServiceLocator()->get('AccessKey\Service\AccessKeyServiceInterface');
        $accessKeyService->setUserRepository($this->getUserMock($user,2));
        $accessKeyService->setEntityManager($this->getEntityMock());
        $result = $accessKeyService->activateUser('accesskey','orgno');
        $this->assertEquals(1, count($result));
    }
}
