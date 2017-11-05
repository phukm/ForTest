<?php

namespace BasicConstruction\Service;

use BasicConstruction\Service\ServiceInterface\UACServiceInterface;
use Dantai\DantaiConstants;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Entity\User;
use Dantai\PrivateSession;
use Aws\Ses\Exception\SesException;
use History\HistoryConst;

class UACService implements UACServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;
    private $eikenScheduleRepos;
    private $accessKeyRepos;
    private $userRepos;
    
    const USER_HAS_CREATE_BY_ACCESS_KEY = 1;
    const USER_HAS_CREATE_BY_DP = 0;

    private $applyEikenOrgRepo = null;
    private $eikenScheduleRepo = null;
    private $applyIBAOrgRepo = null;

    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function setApplyEikenOrgRepo($applyEikenMock) {
        $this->applyEikenOrgRepo = $applyEikenMock;
    }

    public function getApplyEikenOrgRepo() {
        if (null === $this->applyEikenOrgRepo) {
            $this->applyEikenOrgRepo = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenOrg');
        } return $this->applyEikenOrgRepo;
    }

    public function setEikenScheduleRepo($eikenScheduleMock) {
        $this->eikenScheduleRepo = $eikenScheduleMock;
    }

    public function getEikenScheduleRepo() {
        if (null === $this->eikenScheduleRepo) {
            $this->eikenScheduleRepo = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule');
        }return $this->eikenScheduleRepo;
    }

    public function setApplyIBAOrgRepo($applyIBAOrgMock) {
        $this->applyIBAOrgRepo = $applyIBAOrgMock;
    }

    public function getApplyIBAOrgRepo() {
        if (null === $this->applyIBAOrgRepo) {
            $this->applyIBAOrgRepo = $this->getEntityManager()->getRepository('Application\Entity\ApplyIBAOrg');
        } return $this->applyIBAOrgRepo;
    }

    public function authentication($user, $params, $em)
    {
        $response = array(
            "status"     => 0,
            "error"      => "",
            "wrong_pass" => false,
            "identity"   => null
        );
        $auth = $this->getEntityAuthenticateManager();
        $auth->getAdapter()->setIdentityValue($user->getId());
        $auth->getAdapter()->setCredentialValue($params['password']);
        $result = $auth->authenticate();
        //$currentKai = $em->getRepository('Application\Entity\EikenSchedule')->getCurrentKai();
        PrivateSession::setData('applyEikenShowPopup', array(
            'status' => 0
        ));
        PrivateSession::setData('applyIBAShowPopup', array(
            'status' => 0
        ));
        $currentDate = date(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
        $year = Date('Y');
        if (Date('m') < 4) {
            $year = Date('Y') - 1;
        }
        $getCurrentKaiByYear = $em->getRepository('Application\Entity\EikenSchedule')->getCurrentKaiByYear($year);
        foreach ($getCurrentKaiByYear as $key => $value) {
            if (!empty($value['deadlineFrom']) && $value['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) <= $currentDate) {
                $currentEikenScheduleId = $value['id'];
                $currentKai = $value['kai'];
                break;
            }
        }
        if (!$result->isValid()) {
            $response["wrong_pass"] = true;
            $response["error"] = 'Msg_Error_Wrong_Account';
        }
        else {
            $identity = $result->getIdentity();
            // save indentity to session
            $userIdentity = array(
                'id'               => $user->getId(),
                'userId'           => $user->getUserId(),
                'password'         => $user->getPassword(),
                'firstName'        => $user->getFirstNameKanji(),
                'lastName'         => $user->getLastNameKanji(),
                'emailAddress'     => $user->getEmailAddress(),
                'roleId'           => $user->getRole()->getId(),
                'role'             => $user->getRole()->getRoleName(),
                'organizationNo'   => $user->getOrganizationNo(),
                'organizationId'   => $user->getOrganization()->getId(),
                'organizationName' => $user->getOrganization()->getOrgNameKanji(),
                'organizationCode' => $user->getOrganization()->getOrganizationCode(),
                'currentKai'       => $currentKai
            );
            $user->setFirstSendPass(0);
            $user->setCountLoginFailure(0);
            $em->flush();
            $em->clear();
            // save action-role to session
            $roldeId = $userIdentity['roleId'];
            $actionRoles = $em->getRepository('Application\Entity\RoleAction')->getActionsByRole($roldeId);
            $userIdentity['actionRoles'] = $actionRoles;
            PrivateSession::setData('userIdentity', $userIdentity);
            //get Current Kai
            // Set some infomation of ApplyEikenStatus for displaying top menu
            if ($currentKai) {
                // InvitationSetting
                $invitationSetting = $em->getRepository('Application\Entity\InvitationSetting')->getInvitationSetting($user->getOrganization()->getId(), $currentEikenScheduleId);
                // ApplyEikenOrg
                $applyEikenOrg = $em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
                    'organizationId'  => $user->getOrganization()->getId(),
                    'eikenScheduleId' => $currentEikenScheduleId
                ));
            }
            $kaiShowPopup = false;
            foreach ($getCurrentKaiByYear as $key => $value) {
                if (!empty($value['day1stTestResult']) && $value['day1stTestResult']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) <= $currentDate) {
                    $eikenScheduleIdShowPopup = $value['id'];
                    $kaiShowPopup = $value['kai'];
                    break;
                }
            }
            if (!empty($kaiShowPopup)) {
                $applyEikenOrgShowPopup = $em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
                    'organizationId'  => $user->getOrganization()->getId(),
                    'eikenScheduleId' => $eikenScheduleIdShowPopup
                ));
                if ($applyEikenOrgShowPopup && ($roldeId === 4 || $roldeId === 5)) {
                    $this->getApplyEikenOrgShowPopup($applyEikenOrgShowPopup, $year, $kaiShowPopup, $em->getRepository('Application\Entity\Session'), $em);
                }
            }
            //check Apply IBA Org show popup
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            if ($roldeId === 4 || $roldeId === 5) {
                $applyIBAList = $em->getRepository('Application\Entity\ApplyIBAOrg')->getApplyIBAOrgShowPopup($user->getOrganization()->getId(), $year);
                if ($applyIBAList) {
                    $messageIBA = $translator->translate('SHOW_POPUP_MSG15').'<br>';
                    foreach ($applyIBAList as $applyIBA){
                        $this->getApplyIBAOrgShowPopup($applyIBA, $year, $em->getRepository('Application\Entity\Session'), $em, $messageIBA);
                    }
                };
                $em->flush();
                $em->clear();
            }
            PrivateSession::setData('applyEikenStatus', array(
                'hasInvitationSetting' => !empty($invitationSetting) ? true : false,
                'hasApplyEikenOrg'     => !empty($applyEikenOrg) ? true : false
            ));
            $response["status"] = 1;
            $response["identity"] = $identity;
        }

        return $response;
    }

    public function updateFailedLogin($wrong_pass, $user, $em) {
        /* Cap nhat lan dang nhap sai neu user co status = Enable */
        if ($wrong_pass && $user->getStatus() == 'Enable') {
            $em->getRepository('Application\Entity\User')->incCountLoginFailureById($user->getId());
            if ($user->getCountLoginFailure() == 5) {
                // da sai 5 lan va lan nay la lan 6
                $user->setCountLoginFailure(0);
                $user->setStatus('Disable');
                $em->flush();
            }
        }
    }

    public function sendMail($user, $newPassword) {
        /* @var $user \Application\Entity\User */
        $result = array(
            "status" => 1,
            "msg" => 0
        );
        /* begin send mail */
        $globalConfig = $this->getServiceLocator()->get('config');
        $source = isset($globalConfig['emailSender']) ? $globalConfig['emailSender'] : 'dantai@mail.eiken.or.jp';
        if (!empty($user->getEmailAddress())) {
            $to = array(
                $user->getEmailAddress()
            );
            $type = 1;
            $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
            $data = array(
                'name' => $user->getFirstNameKanji() . $user->getLastNameKanji(),
                'url' => $protocol . $_SERVER["SERVER_NAME"] . "/login",
                'userId' => $user->getUserId(),
                'organizationNo' => $user->getOrganizationNo(),
                'orgName' => $user->getOrganization()->getOrgNameKanji(),
                'password' => $newPassword
            );

            try {
                \Dantai\Aws\AwsSesClient::getInstance()->deliver($source, $to, $type, $data);
            } catch (SesException $e) {
                $result['status'] = 0;
                $result["msg"] = 'Change Password Success But Send Mail Error';
            }
        } else {
            $result['status'] = 0;
            $result["msg"] = 'User Has Not Email Address';
        }
        return $result;
    }

    public function validateProfile($params, $user, $em, $type = 0) {
        $errors = array();
        if (empty($params['txtUserID'])) {
            $errors["txtUserID"] = 'Msg_Error_Field_Is_Blank';
        } else {
            if (!$this->validateFormatUsername($params['txtUserID'])) {
                $errors["txtUserID"] = 'Msg_Error_Vaild_UserId';
            }
            if ($params['txtUserID'] != $user->getUserId()) {
                $userValidator = new \DoctrineModule\Validator\ObjectExists(array(
                    'object_repository' => $em->getRepository('Application\Entity\User'),
                    'fields' => array(
                        'userId',
                        'organizationNo',
                        'isDelete'
                    )
                ));
                $valueExistObj = array(
                    'userId' => $params['txtUserID'],
                    'organizationNo' => $user->getOrganizationNo(),
                    'isDelete' => 0
                );
                if ($userValidator->isValid($valueExistObj)) {
                    $errors["txtUserID"] = 'Msg_Error_Exist_UserId';
                }
            }
        }
        if (empty($params['txtFistname'])) {
            $errors["txtFistname"] = 'Msg_Error_Field_Is_Blank';
        }
        if (empty($params['txtlastname'])) {
            $errors["txtlastname"] = 'Msg_Error_Field_Is_Blank';
        }
        if (empty($params['txtEmailAddress'])) {
            $errors["txtEmailAddress"] = 'Msg_Error_Field_Is_Blank';
        } else if((!$this->validateFormatEmail($params['txtEmailAddress']))){
            $errors["txtEmailAddress"] = ($type == 0) ? 'Msg_Error_Vaild_Email' : 'Msg_Error_Vaild_Email_Edit_Profile';
        }
        else{
            $objEmailCheck = $em->getRepository('Application\Entity\User')->findBy(array(
                'emailAddress' => $params['txtEmailAddress']
            ));
            if(!empty($objEmailCheck)){
                foreach ($objEmailCheck as $value){
                    if($value->getId() != $user->getId()){
                        $errors["txtEmailAddress"] = 'EmailIsUse';
                    }
                }
            }
        }
        return $errors;
    }

    public function validatePassword($user, $params) {
        $errors = array();
        $newPasswordMd5 = User::generatePassword($params['newPassword']);
        if (empty($params['oldPassword'])) {
            $errors["oldPassword"] = 'Msg_Error_Field_Is_Blank';
        } else {
            if ($user->getPassword() != User::generatePassword($params['oldPassword'])) {
                $errors["oldPassword"] = 'Msg_Error_Wrong_Old_Password';
            }
        }
        if (empty($params['newPassword'])) {
            $errors["newPassword"] = 'Msg_Error_Field_Is_Blank';
        } else {
            $arrPasswordNotMatch = array(
                User::generatePassword($user->getUserId()),
                $user->getPassword(),
                $user->getOldPasswordFirst(),
                $user->getOldPasswordSecond()
            );
            if (in_array($newPasswordMd5, $arrPasswordNotMatch) || !$this->validateFormatPassword($params['newPassword'])) {
                $errors["newPassword"] = 'Msg_Error_Vaild_Password';
            }
        }

        if (empty($params['confirmNewPassword'])) {
            $errors["confirmNewPassword"] = 'Msg_Error_Field_Is_Blank';
        } else {
            if ($params['newPassword'] != $params['confirmNewPassword']) {
                $errors["confirmNewPassword"] = 'Msg_Error_Not_Match_Password_And_Confirm_Password';
            }
        }

        return $errors;
    }

    public function validateFormatUsername($userId) {
        // ﾃｽﾀｶﾀｶﾅ
        // テスタカタカナ
        // ｾ ｿ ﾀ ﾁ ﾂ
        $valid = true;

        $pattern = "/^[a-zA-Z0-9-_\x{FF61}-\x{FFDC}\x{FFE8}-\x{FFEE}]*$/u";
        if (!preg_match($pattern, $userId)) {
            $valid = false;
        }

        $pattern = "/^[a-zA-Z]{1}/";
        if (!preg_match($pattern, $userId)) {
            $valid = false;
        }

        if (mb_strlen($userId, 'utf-8') < 4 || mb_strlen($userId, 'utf-8') > 31) {
            $valid = false;
        }

        return $valid;
    }

    public function validateFormatPassword($password) {
        $valid = true;

        $checkFullSize = \Dantai\Utility\CharsetConverter::checkFullSize($password);
        if ($checkFullSize == true) {
            $valid = false;
        }

        $pattern = '/[A-Z]/';
        if (!preg_match_all($pattern, $password)) {
            $valid = false;
        }

        $pattern = '/[a-z]/';
        if (!preg_match_all($pattern, $password)) {
            $valid = false;
        }
        $pattern = '/[0-9]/';
        if (!preg_match_all($pattern, $password)) {
            $valid = false;
        }

        if (mb_strlen($password, 'utf-8') < 6 || mb_strlen($password, 'utf-8') > 32) {
            $valid = false;
        }

        return $valid;
    }

    public function validateFormatEmail($email) {
        $validator = new \Zend\Validator\EmailAddress();
        if ($validator->isValid($email)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkBrowserIE() {
        if (preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT'])) {
            $ub = "ie";
        }
        return !empty($ub) ? true : false;
    }

    public function getEntityAuthenticateManager() {
        return $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
    }
    public function setEikenRepos($eikenScheduleRepos = null,$em) {
        $this->eikenScheduleRepos = $eikenScheduleRepos ? $eikenScheduleRepos : $em->getRepository('Application\Entity\EikenSchedule');
    }
    public function setAccessKeyRepos($accessKeyRepos = null,$em) {
        $this->accessKeyRepos = $accessKeyRepos ? $accessKeyRepos : $em->getRepository('Application\Entity\AccessKey');
    }
    public function setUserRepos($userRepos = null,$em) {
        $this->userRepos = $userRepos ? $userRepos : $em->getRepository('Application\Entity\User');
    }
    public function disableAccessKey($orgNo,$userId,$em) {
        
        if(empty($this->eikenScheduleRepos)){
            $this->setEikenRepos('',$em);
        }
        if(empty($this->accessKeyRepos)){
            $this->setAccessKeyRepos('',$em);
        }
        if(empty($this->userRepos)){
            $this->setUserRepos('',$em);
        }
        /*
         * @var $accessKey \Application\Entity\AccessKey
         */
        $eikenSchedule = $this->eikenScheduleRepos->getEikenScheduleByCurrentTime();
        if ($eikenSchedule && $orgNo) {
            $kai = $eikenSchedule[0]['kai'] ? intval($eikenSchedule[0]['kai']) : '';
            $year = $eikenSchedule[0]['year'] ? intval($eikenSchedule[0]['year']) : '';
            if (!empty($kai) && !empty($year)) {
                $accessKey = $this->accessKeyRepos->findOneBy(array(
                    'organizationNo' => $orgNo,
                    'kai' => $kai,
                    'year' => $year,
                    'status' => 'Enable',
                    'isDelete' => 0,
                ));
                $user = $this->userRepos->findOneBy(array(
                    'organizationNo' => $orgNo,
                    'userId' => $userId,
                    'statusInit' => self::USER_HAS_CREATE_BY_ACCESS_KEY,
                ));
                if(!empty($user) && !empty($accessKey)){
                    $accessKey->setStatus('Disable');
                    $user->setStatusInit(self::USER_HAS_CREATE_BY_DP);
                    $em->flush();
                    $em->clear();
                    return true;
                }
            }
        }
        return false;
    }
    public function getApplyIBAOrgShowPopup($applyIBA, $year, $getRepo,$em,&$messageIBA) {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        if($applyIBA->getStatusAutoImport() == HistoryConst::STATUS_AUTO_IMPORT_IBA_COMPLETE 
                && $applyIBA->getSession() != null)
        {
            $session = $getRepo->findOneBy(array('id' => $applyIBA->getSession()));
            if (!$session) {
                try {
                    $testDate = $applyIBA->getTestDate();
                    $month = $testDate->format('n');
                    $day = $testDate->format('j');
                    $year = $testDate->format('Y');

                    if ($applyIBA->getExamType() == '01' || $applyIBA->getExamType() == '02')
                        $examType = HistoryConst::EXAM_TYPE_NAME_IBA;
                    else $examType = '';

                    $messageIBA .= '<br>' . $examType . '_' . $applyIBA->getSetName() . '_' . $year. '年' . $month . '月' . $day . '日';

                    PrivateSession::setData('applyIBAShowPopup', array(
                        'msg' => $messageIBA,
                        'status' => 1,
                        'year' => $year,
                        'moshikomiId' => $applyIBA->getmoshikomiId()
                    ));
                    $applyIBA->setSession(session_id());
                    return HistoryConst::SAVE_DATABASE_SUCCESS;
                } catch (\Exception $ex) {
                    return HistoryConst::SAVE_DATABASE_FALSE;
                }
            } else {
                return HistoryConst::EXISTING_SESSION;
            }
        }
        else if($applyIBA->getStatusAutoImport() == HistoryConst::STATUS_AUTO_IMPORT_IBA_FAILURE)
        {
            try 
            {
                PrivateSession::setData('applyIBAShowPopup', array(
                    'msg' => $translator->translate('SHOW_POPUP_MSG59'),
                    'status' => 2
                ));
                $applyIBA->setStatusAutoImport(HistoryConst::STATUS_AUTO_IMPORT_NOT_RUN);           
                $em->flush();
                $em->clear();
                $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
                $url = $protocol . $_SERVER["SERVER_NAME"];
                $this->checkImportIBATestResult($applyIBA->getOrganizationId(), $applyIBA->getOrganization()->getOrganizationNo(), $url);
                return HistoryConst::SAVE_DATABASE_SUCCESS;
            } catch (\Exception $ex) {
                return HistoryConst::SAVE_DATABASE_FALSE;
            }
        }

    }

    public function getApplyEikenOrgShowPopup($applyEikenOrg, $year, $currentKai, $getRepo, $em) {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        if (($applyEikenOrg->getStatusAutoImport() == HistoryConst::STATUS_AUTO_IMPORT_EIKEN_ROUND1_COMPLETE || 
                $applyEikenOrg->getStatusAutoImport() == HistoryConst::STATUS_AUTO_IMPORT_EIKEN_ROUND2_COMPLETE) 
                && $applyEikenOrg->getSession() != null) 
        {
            $session = $getRepo->findOneBy(array('id' => $applyEikenOrg->getSession()));
            if (!$session) {
                try {
                    PrivateSession::setData('applyEikenShowPopup', array(
                        'msg' => sprintf($translator->translate('SHOW_POPUP_MSG14'), $year, $currentKai),
                        'status' => 1,
                        'year' => $year,
                        'kai' => $currentKai
                    ));
                    $applyEikenOrg->setSession(session_id());
                    $em->flush();
                    $em->clear();
                    return HistoryConst::SAVE_DATABASE_SUCCESS;
                } catch (\Exception $ex) {
                    return HistoryConst::SAVE_DATABASE_FALSE;
                }
            } else {
                return HistoryConst::EXISTING_SESSION;
            }
        }
        else if($applyEikenOrg->getStatusAutoImport() == HistoryConst::STATUS_AUTO_IMPORT_EIKEN_FAILURE)
        {
            try {
                    PrivateSession::setData('applyEikenShowPopup', array(
                        'msg' => $translator->translate('SHOW_POPUP_MSG59'),
                        'status' => 2
                    ));
                    $applyEikenOrg->setStatusAutoImport(HistoryConst::STATUS_AUTO_IMPORT_NOT_RUN);
                    $em->flush();
                    $em->clear();
                    $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
                                $url = $protocol . $_SERVER["SERVER_NAME"];
                                $this->checkImportEikenTestResult($applyEikenOrg->getOrganizationId(), $applyEikenOrg->getOrganization()->getOrganizationNo(), $url);
                    return HistoryConst::SAVE_DATABASE_SUCCESS;
                } catch (\Exception $ex) {
                    return HistoryConst::SAVE_DATABASE_FALSE;
                }
        }
    }

    public function checkImportEikenTestResult($orgId, $orgNo, $url) {
        
        $result = false;
        $config = $this->getServiceLocator()->get('config');
        $eikenStatus = $config['Eiken_StatusAutoImport'];
        $lstKai = $this->getEikenScheduleRepo()->getListEikenScheduleByTestDateResult();
        if (empty($lstKai)) {
            return false;
        }
        $curDate = new \DateTime(date('Y-m-d H:i:s'));
        $kaiId = 0;
        $round = 0;
        $year = 0;
        $kai = 0;

        for ($i = 0; $i < count($lstKai); $i++) {
            $item = $lstKai[$i];
            $nextIndex = $i + 1;
            $day1 = $item->getDay1stTestResult() != NULL ? new \DateTime($item->getDay1stTestResult()->format('Y-m-d H:i:s')) : $curDate;
            $day2 = $item->getDay2ndTestResult() != NULL ? new \DateTime($item->getDay2ndTestResult()->format('Y-m-d H:i:s')) : $curDate;
            $day3 = $curDate;
            if ($nextIndex < count($lstKai)) {
                $day3 = $lstKai[$nextIndex]->getDay1stTestResult() != NULL ? new \DateTime($lstKai[$nextIndex]->getDay1stTestResult()->format('Y-m-d H:i:s')) : $curDate;
            }
            if ($day1 <= $curDate && $curDate < $day2) {
                $kaiId = $item->getId();
                $round = 1;
                $year = $item->getYear();
                $kai = $item->getKai();
                break;
            } else if ($curDate >= $day2 && $curDate < $day3) {
                $kaiId = $item->getId();
                $round = 2;
                $year = $item->getYear();
                $kai = $item->getKai();
                break;
            }
        }

        if ($kaiId != 0) {
            
            $objEikenOrg = $this->getApplyEikenOrgRepo()->findOneBy(array(
                'organizationId' => $orgId,
                'isDelete' => 0,
                'eikenScheduleId' => $kaiId
            ));

            if ($objEikenOrg == NULL) {
                return false;
            }
            $statusAutoImport = $objEikenOrg->getStatusAutoImport();
            if (empty($statusAutoImport) || ($round == 2 && $statusAutoImport === $eikenStatus['Round1Confirmed'])) {
                $paramEiken = array();
                $paramEiken['kaiId'] = $kaiId;
                $paramEiken['kai'] = $kai;
                $paramEiken['year'] = $year;
                $paramEiken['round'] = $round;
                $paramEiken['applyEikenOrgId'] = $objEikenOrg->getId();
                $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
                $result = $dantaiService->addAutoMapToQueue('EIKEN', $orgId, $orgNo, $paramEiken, null, $url);
            }
        }
        return $result;
    }

    public function checkImportIBATestResult($orgId, $orgNo, $userId, $url) {
        $result = false;
        $paramIBA = array();
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $result = $dantaiService->addAutoMapToQueue('IBA', $orgId, $orgNo, null, $paramIBA, $url, $userId);
        return $result;
    }

    public function deleteSession($sesionId){
        $em = $this->getEntityManager();
//        $result = $em->getRepository('Application\Entity\Session')->deleteSession($sesionId);
        $session = $em->getRepository('Application\Entity\Session')->findOneBy(array('id' => $sesionId));
        if (!empty($session))
        {
            $em->remove($session);
            $em->flush();
        }

    }
}
