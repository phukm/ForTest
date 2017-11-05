<?php

namespace AccessKey\Service;

use AccessKey\Service\ServiceInterface\AccessKeyServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use AccessKey\AccessKeyConst;

class AccessKeyService implements AccessKeyServiceInterface, ServiceLocatorAwareInterface
{
    const LOGIN_SESSION_KEY = 'LoginSessionKey';
    use ServiceLocatorAwareTrait;
    
    private $userRepos;
    private $orgRepository;
    
    private $sesClient;
    private $user;
    private $accessRepos;
    private $entityManager;


    public function setOrganizationRepository($orgRepos = Null){
        $this->orgRepository = $orgRepos ? $orgRepos : $this->getEntityManager()->getRepository('Application\Entity\Organization');
    }
    
    public function setUserRepository($userRepos = Null){
        $this->userRepos = $userRepos ? $userRepos : $this->getEntityManager()->getRepository('Application\Entity\User');
    }
    
    public function setAccessRepository($accessKeyRepos = Null){
        $this->accessRepos = $accessKeyRepos ? $accessKeyRepos : $this->getEntityManager()->getRepository('Application\Entity\AccessKey');
    }
    
    public function setAwsSesClient($sesClient = Null){
        $this->sesClient = $sesClient ? $sesClient : \Dantai\Aws\AwsSesClient::getInstance();
    }
    
    public function saveFirstUser($params, $organizationNo,$em=false){
        $roleId = 4;
        $userId = isset($params['userId']) ? trim($params['userId']) : '';
        $firstnameKanji = isset($params['firstNameKanji']) ? trim($params['firstNameKanji']) : '';
        $lastnameKanji = isset($params['lastNameKanji']) ? trim($params['lastNameKanji']) : '';
        $emailAddress = isset($params['emailAddress']) ? trim($params['emailAddress']) : '';
        if(empty($userId) || empty($firstnameKanji) || empty($lastnameKanji) ||  empty($emailAddress)){
            return AccessKeyConst::ERROR_EMPTY_PARAMS;
        }
        $em = $em ? $em : $this->getEntityManager();
        
        
        if(!$this->orgRepository){
            $this->setOrganizationRepository();
        }
        /* @var $organization \Application\Entity\Organization */
        $organization = $this->orgRepository->findOneBy(array(
            'organizationNo' => intval($organizationNo),
            'isDelete' => 0
        ));
        if(!$organization){
            return AccessKeyConst::ERROR_EMPTY_ORGNO ;
        }
        
        
        if(!$this->userRepos){
            $this->setUserRepository();
        }
        $userExistOfOrg = $this->userRepos->findOneBy(array(
            'organizationNo' => intval($organizationNo),
            'isDelete' => 0
        ));
        if($userExistOfOrg){
            return AccessKeyConst::ERROR_EXIST_USER_OF_ORG;
        } 
        
        
        $password = rand(10000000, 99999999);
        
        try{
            $user = new \Application\Entity\User();
            $user->setOrganization($organization);
            $user->setUserId($userId);
            $user->setOrganizationNo($organizationNo);
            $user->setEmailAddress($emailAddress);
            $user->setRole($em->getReference('Application\Entity\Role', $roleId));
            $user->setPassword($user->generatePassword($password));
            $user->setFirstNameKanji($firstnameKanji);
            $user->setLastNameKanji($lastnameKanji);
            $user->setCountLoginFailure(0);
            $user->setFirstLogin(1);
            $user->setAgreePolicy(0);
            $user->setStatus('Enable');
            $user->setIsDelete(0);
            $user->setFirstSendPass(0);
            $user->setStatusInit(1);
            $em->persist($user);
            $em->flush();
            
            $this->sendMail($organization, $params , $password);

            return AccessKeyConst::SAVE_DATABASE_SUCCESS;
        } catch (\Exception $ex) {
            return AccessKeyConst::SAVE_DATABASE_FALSE; 
        }
    }
    
    public function disableAccessKey($accessKey, $organizationNo) {
        if(empty($this->entityManager)){
            $this->entityManager = $this->setEntityManager();
        }
        $em = $this->entityManager;
        if(!$this->accessRepos){
            $this->setAccessRepository();
        }
        /* @var $accessKey \Application\Entity\AccessKey */
        $accessKeyEntity = $this->accessRepos->findOneBy(array(
            'organizationNo' => $organizationNo,
            'accessKey' => $accessKey,
            'isDelete' => 0
        ));
        if (!$accessKeyEntity) {
            return AccessKeyConst::ERROR_NOT_EXIST_ACCESS_KEY;
        }
        try {
            $accessKeyEntity->setStatus('Disable');
            $em->persist($accessKeyEntity);
            $em->flush();
            return AccessKeyConst::SAVE_DATABASE_SUCCESS;
        } catch (\Exception $ex) {
            return AccessKeyConst::SAVE_DATABASE_FALSE;
        }
    }
    
    public function sendMail(\Application\Entity\Organization $organization, $params , $password) {
        $request = new \Zend\Http\PhpEnvironment\Request();
        $result = array("status" => 0, "message" => 'User Has Not Email Address');
        if($organization === Null){
            $result['message'] = 'Do Not Exist Organization';
            return $result;
        }
        $globalConfig = $this->getServiceLocator()->get('Config');
        $emailSender = isset($globalConfig['emailSender']) ? $globalConfig['emailSender'] : 'dantai@mail.eiken.or.jp';
        if (!empty($params['emailAddress'])) {
            $toEmail = array($params['emailAddress']);
            $type = 7;
            $protocol = stripos($request->getServer('SERVER_PROTOCOL'), 'https') === true ? 'https://' : 'http://';
            $data = array(
                'name' => $params['firstNameKanji'] . $params['lastNameKanji'],
                'orgName' => $organization->getOrgNameKanji(),
                'orgNo' => $organization->getOrganizationNo(),
                'url' => $protocol . $request->getServer('SERVER_NAME'),
                'userId' => $params['userId'],
                'password' => $password,
                'confirmUrl' => $protocol . $request->getServer('SERVER_NAME') . "/login"
            );

            try {
                if(!$this->sesClient){
                    $this->setAwsSesClient();
                }
                $this->sesClient->deliver($emailSender, $toEmail, $type, $data);
                $result['status'] = 1;
                $result['message'] = 'Send Mail Success';
            } catch (SesException $ex) {
                $result['status'] = 0;
                $result["message"] = 'Change Password Success But Send Mail Error';
            }
        } 
        return $result;
    }
    
    public function buildPasswordForSendMail($password)
    {
        $length = strlen($password);
        $result = "";
        for ($i = 0; $i < $length - 3; $i ++) {
            $result .= '*';
        }
        
        for ($i = $length - 3; $i < $length; $i ++) {
            $result .= $password[$i];
        }
        
        return $result;
    }
//    validate page accesskey
    public function expiredAccessKey($value = array())
    {
        $em = $this->getEntityManager();
        $expiredAccessKey = array();
        $privateSession = new \Dantai\PrivateSession();
        $dataLogin = $privateSession->getData(self::LOGIN_SESSION_KEY);
        $orgNo = '';
        $user = array();
        if(!empty($dataLogin) && array_key_exists('orgNo', $dataLogin)){
            $orgNo = $dataLogin['orgNo'];
        }
        if(array_key_exists('organizationNo', $value)){
            $orgNo = $value['organizationNo'];
            //            not have user create by admin 1,2,3
            $user = $em->getRepository('Application\Entity\User')->findOneBy(array(
                'organizationNo' => $value['organizationNo'],
                'statusInit' => 0
             ));
        }
        if (!empty($value['accessKey']) && !empty($orgNo)) {
//            check accesskey
            $sqlAccessKey = array(
                'accessKey'      => $value['accessKey'],
                'organizationNo' => $orgNo,
                'isDelete'       => 0
             );
//            it is use active
            if(array_key_exists('organizationNo', $value)){
                $sqlAccessKey = array(
                'accessKey'      => $value['accessKey'],
                'organizationNo' => $orgNo,
                'status'         => 'Enable',
                'isDelete'       => 0
             );
            }
            $accessKey = $em->getRepository('Application\Entity\AccessKey')->findOneBy($sqlAccessKey);
            
//            get next kai year
             if (empty($user) && !empty($accessKey) && !empty($accessKey->getKai()) && $accessKey->getYear() && (int) $accessKey->getKai() < 4 && $accessKey->getKai() > 0) {
                $nextKai = (int) $accessKey->getKai() + 1;
                $nextYear = (int) $accessKey->getYear();
                if ((int)$accessKey->getKai() == 3) {
                    $nextKai = 1;
                    $nextYear = (int)  $accessKey->getYear() + 1;
                }
                $expiredAccessKey = $em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
                    'year' => $nextYear,
                    'kai' => $nextKai,
                    'isDelete' => 0
                ));
            }
        }
        return $expiredAccessKey;
    }
    public  function wrongAccessKey($value = array())
    {
        $privateSession = new \Dantai\PrivateSession();
        $dataLogin = $privateSession->getData(self::LOGIN_SESSION_KEY);
        $data = array();
        $orgNo = '';
        if(!empty($dataLogin) && array_key_exists('orgNo', $dataLogin)){
            $orgNo = $dataLogin['orgNo'];
        }
        if(array_key_exists('organizationNo', $value)){
            $orgNo = $value['organizationNo'];
        }
        if (!empty($orgNo) && !empty($value['accessKey'])) {
            
            $em = $this->getEntityManager();
            $org = $em->getRepository('Application\Entity\Organization')->findOneBy(array(
                'organizationNo' => $orgNo,
                'isDelete'       => 0
             ));
             if ($org) {
                $data = $em->getRepository('Application\Entity\AccessKey')->findOneBy(array(
                    'accessKey' => $value['accessKey'],
                    'organizationNo' => $orgNo,
                    'isDelete' => 0
                ));
            }
        }

        return $data;
    }
    public function deleteUser($orgNo){
        if($orgNo){
            $em = $this->getEntityManager();
            $em->getRepository('Application\Entity\User')->deleteAllUserByOrgNo($orgNo);
        }
    }
    public function activateUser($orgNo,$userId){
        if($orgNo && $userId){
            if(empty($this->entityManager)){
                $this->entityManager = $this->setEntityManager();
            }
            $em = $this->entityManager;
            if(!$this->userRepos){
                $this->setUserRepository();
            }
            $user = $this->userRepos->findOneBy(array('organizationNo'=>$orgNo,'userId'=>$userId));
            if(!empty($user)){
                /* @var $user \Application\Entity\User */
                $user->setStatus('Enable');
                $em->flush();
                $em->clear();
            }
            return $user;
        }
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        if(!$this->entityManager){
            $this->setEntityManager();
        }
        return $this->entityManager;
    }
    public function setEntityManager($entityManager = Null) {
        $this->entityManager = $entityManager ? $entityManager : $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

}