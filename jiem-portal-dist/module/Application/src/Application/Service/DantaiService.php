<?php

namespace Application\Service;

use Application\Entity\InvitationSetting;
use Application\Entity\SemiVenue;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\PrivateSession;
use Zend\Stdlib\ParametersInterface;
use Dantai\Api\UkestukeClient;
use Application\ApplicationConst;

class DantaiService implements DantaiServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    const CROSS_EDITING = 'cross-edit-';
    const CROSS_EDITING_MESG = 'cross-edit-message-';
    const CROSS_EDITING_TYPE = 'cross-edit-type-';
    const CROSS_EDITING_DATA = 'cross-edit-data-';
    const QUEUE_RANGE_LIMIT = 50;
    const PRIVATE_LOCK_LIST = 'private-lock-list';
    const ACHIVEMENT_TIME_RUN = 23;
    const LOCK_ORG_IN_QUEUE_PREFIX = 'lock-org-in-queue';
    const DEFINITION_SPECIAL_ONE = 1;
    const DEFINITION_SPECIAL_TWO = 2;
    const DEFINITION_SPECIAL_THREE = 3;
    const BENEFICIARY_IS_DANTAI = 1;
    const BENEFICIARY_IS_STUDENT = 2;
    
    private $ukestukeClient;
    private $eikenLevelRepos;
    private $em;

    /**
     * {@inheritDoc}
     */
    public function getCurrentUser() {
        $user = PrivateSession::getData('userIdentity');
        return $user;
    }
    
    public function getCurrentUserStl() {
        $user = PrivateSession::getData('satellite');
        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function changeOrganizationId($organizationId) {
        $org = $this->getEntityManager()->getRepository('Application\Entity\Organization')->find($organizationId);
        if (!$org) {
            return false;
        }
        $user = PrivateSession::getData('userIdentity');
        $userByOrgNo = $this->getEntityManager()->getRepository('Application\Entity\User')
                ->findOneBy(
                array(
            'organizationId' => $organizationId,
            'isDelete' => 0,
                ), array('roleId' => 'ASC'));
        if (!empty($userByOrgNo)) {
            $user ['TransformRoleId'] = $userByOrgNo->getRoleId();
        } else {
            $user ['TransformRoleId'] = 5;
        }
        $user ['organizationId'] = $organizationId;
        $user ['organizationNo'] = $org->getOrganizationNo();
        $user ['organizationCode'] = $org->getOrganizationCode();
        $user ['organizationName'] = $org->getOrgNameKanji();
        PrivateSession::setData('userIdentity', $user);

        // Set some infomation of ApplyEikenStatus for displaying top menu
        $em = $this->getEntityManager();
        $eiKenSchedule = $em->getRepository('Application\Entity\EikenSchedule')->getAvailableEikenScheduleByDate(date('Y'), date('Y-m-d H:i:s'));
        $eiKenScheduleFroRole = $em->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();
        if ($eiKenSchedule) {
            // InvitationSetting
            $invitationSetting = $em->getRepository('Application\Entity\InvitationSetting')->getInvitationSetting($user['organizationId'], $eiKenSchedule['id']);
            // ApplyEikenOrg
            $applyEikenOrg = $em->getRepository('Application\Entity\ApplyEikenOrg')->getEikenOrgByParams($user['organizationId'], $eiKenSchedule['id']);
        } else {
            $applyEikenOrg = $em->getRepository('Application\Entity\ApplyEikenOrg')->getEikenOrgByParams($user['organizationId'], $eiKenScheduleFroRole['id']);
        }
        PrivateSession::setData('applyEikenStatus', array(
            'hasInvitationSetting' => !empty($invitationSetting) ? true : false,
            'hasApplyEikenOrg' => !empty($applyEikenOrg) ? true : false,
            'statusApplyEikenOrg' => !empty($applyEikenOrg) ? $applyEikenOrg['status'] : ''
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchKeywordFromSession($routeMatch) {
        $session = PrivateSession::getData($routeMatch);
        return (isset($session)) ? $session : false;
    }

    /**
     * {@inheritDoc}
     */
    public function setSearchKeywordToSession($routeMatch, $searchData) {
        PrivateSession::setData($routeMatch, $searchData);
    }

    // BEGIN Cross editing functions

    /**
     * (non-PHPdoc)
     * @see \Application\Service\ServiceInterface\DantaiServiceInterface::startCrossEditing()
     */
    public function startCrossEditing($entityName, $entitySearchCriteria = null) {
        if (!$entityName instanceof \Application\Entity\Common) {
            $em = $this->getEntityManager();
            $entityName = $em->getRepository($entityName)->findOneBy($entitySearchCriteria);
        }

        $saveKey = str_replace('DoctrineORMModule\\Proxy\\__CG__\\', '', get_class($entityName));

        PrivateSession::setData(self::CROSS_EDITING . $saveKey, $entityName->getUpdateAt());

        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $crossMsgKey = PrivateSession::getData(self::CROSS_EDITING_MESG . $saveKey);
        return array(
            'conflictWarning' => $crossMsgKey ? $translator->translate($crossMsgKey) : '',
            'conflictType' => PrivateSession::getData(self::CROSS_EDITING_TYPE . $saveKey),
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Application\Service\ServiceInterface\DantaiServiceInterface::getCrossEditingMessage()
     */
    public function getCrossEditingMessage($entityName) {
        if ($entityName instanceof \Application\Entity\Common) {
            $entityName = str_replace('DoctrineORMModule\\Proxy\\__CG__\\', '', get_class($entityName));
        }

        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $crossMsgKey = PrivateSession::getData(self::CROSS_EDITING_MESG . $entityName);

        $return = array(
            'conflictWarning' => $crossMsgKey ? $translator->translate($crossMsgKey) : '',
            'conflictType' => PrivateSession::getData(self::CROSS_EDITING_TYPE . $entityName),
        );

        PrivateSession::clear(self::CROSS_EDITING_MESG . $entityName);
        PrivateSession::clear(self::CROSS_EDITING_TYPE . $entityName);
        PrivateSession::clear(self::CROSS_EDITING_DATA . $entityName);

        return $return;
    }

    /**
     * (non-PHPdoc)
     * @see \Application\Service\ServiceInterface\DantaiServiceInterface::checkCrossEditing()
     */
    public function checkCrossEditing($entityName, $entitySearchCriteria, $postData) {
        if (!$entityName instanceof \Application\Entity\Common) {
            $em = $this->getEntityManager();
            $entityName = $em->getRepository($entityName)->findOneBy($entitySearchCriteria);
        }

        $saveKey = str_replace('DoctrineORMModule\\Proxy\\__CG__\\', '', get_class($entityName));
        $messages = array(
            'conflictWarning' => '',
            'conflictType' => '',
            'data' => $entityName
        );

        // Given item does not exist
        if (!$entityName || $entityName->getIsDelete()) {
            // FIXME Please have a look at this when you have issue regarding to cross delete
            PrivateSession::setData(self::CROSS_EDITING_MESG . $saveKey, 'CONFLICTDELETE01');
            PrivateSession::setData(self::CROSS_EDITING_TYPE . $saveKey, 'delete');
            PrivateSession::setData(self::CROSS_EDITING_DATA . $saveKey, $postData);
            $messages['conflictWarning'] = 'CONFLICTDELETE01';
            $messages['conflictType'] = 'delete';

            return $messages;
        }

        // Cross editing
        $modifiedAt = PrivateSession::getData(self::CROSS_EDITING . $saveKey);
        if ($modifiedAt && $entityName->getUpdateAt() != $modifiedAt) {
            // FIXME Please have a look at this when you have issue regarding to cross edit
            PrivateSession::setData(self::CROSS_EDITING_MESG . $saveKey, 'CONFLICTEDIT01');
            PrivateSession::setData(self::CROSS_EDITING_TYPE . $saveKey, 'edit');
            PrivateSession::setData(self::CROSS_EDITING_DATA . $saveKey, $postData);

            $messages['conflictWarning'] = 'CONFLICTEDIT01';
            $messages['conflictType'] = 'edit';

            return $messages;
        }

        PrivateSession::clear(self::CROSS_EDITING . $saveKey);
        PrivateSession::clear(self::CROSS_EDITING_MESG . $saveKey);
        PrivateSession::clear(self::CROSS_EDITING_TYPE . $saveKey);
        PrivateSession::clear(self::CROSS_EDITING_DATA . $saveKey);

        return $messages;
    }

    /**
     * (non-PHPdoc)
     * @see \Application\Service\ServiceInterface\DantaiServiceInterface::restoreCrossEditingForm()
     */
    public function restoreCrossEditingForm($entityName, $form = null, $maping = array()) {
        $saveKey = $entityName;
        if ($entityName instanceof \Application\Entity\Common) {
            $saveKey = str_replace('DoctrineORMModule\\Proxy\\__CG__\\', '', get_class($entityName));
        } elseif (null === $form) {
            //throw new \InvalidArgumentException('You should provide either form or entity object');
        }

        $editingData = PrivateSession::getData(self::CROSS_EDITING_DATA . $saveKey);
        if ((is_array($editingData) || $editingData instanceof ParametersInterface) && count($editingData)) {
            if (null === $form)
                foreach ($maping as $fieldName => $setterName) {
                    if (method_exists($entityName, $setterName) && array_key_exists($fieldName, $editingData))
                        call_user_method($setterName, $entityName, $editingData[$fieldName]);
                } else
                $form->setData($editingData);
        }

        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $crossMsgKey = PrivateSession::getData(self::CROSS_EDITING_MESG . $saveKey);
        $return = array(
            'conflictWarning' => $crossMsgKey ? $translator->translate($crossMsgKey) : '',
            'conflictType' => PrivateSession::getData(self::CROSS_EDITING_TYPE . $saveKey),
            'data' => $form ? : $editingData
        );

        PrivateSession::clear(self::CROSS_EDITING_MESG . $saveKey);
        PrivateSession::clear(self::CROSS_EDITING_TYPE . $saveKey);
        PrivateSession::clear(self::CROSS_EDITING_DATA . $saveKey);

        return $return;
    }

    // END Cross editing functions
    // BEGIN Lock mechanism

    /**
     * (non-PHPdoc)
     * @see \Application\Service\ServiceInterface\DantaiServiceInterface::lockModule()
     */
    public function lockModule($module) {
        if ($module instanceof \Application\Entity\Common) {
            $module = get_class($module) . $module->getId();
        }
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $currentIdentity = $this->getCurrentUser();
        $locker = $currentIdentity['id'];
        $return = array(
            'lockId' => false,
            'lockMessage' => ''
        );

        /**
         * @var \Application\Entity\Repository\DantaiLockRepository
         */
        $dantaiLockRepo = $this->getEntityManager()->getRepository('\Application\Entity\DantaiLock');
        $dantaiLockRepo->purgeExpiredLocks();
        $dantaiLock = $dantaiLockRepo->findOneBy(array('module' => $module));
        if ($dantaiLock && $dantaiLock->getLocker() == $locker) {
            $return['lockId'] = $dantaiLock->getId();
            return $return;
        } elseif ($dantaiLock) {
            $return['lockMessage'] = $translator->translate('LOCKMESSAGE');
            return $return;
        }


        // If the given $module does not have any lock, try to get lock
        $return['lockId'] = $dantaiLockRepo->getLock($module, $locker);

        if ($return['lockId'] === false) {
            $return['lockMessage'] = $translator->translate('LOCKMESSAGE');
        }

        return $return;
    }

    /**
     * (non-PHPdoc)
     * @see \Application\Service\ServiceInterface\DantaiServiceInterface::releaseLockModule()
     */
    public function releaseLockModule($module) {
        if ($module instanceof \Application\Entity\Common) {
            $module = get_class($module) . $module->getId();
        }
        $currentIdentity = $this->getCurrentUser();
        $locker = $currentIdentity['id'];

        /**
         * @var \Application\Entity\Repository\DantaiLockRepository
         */
        $dantaiLockRepo = $this->getEntityManager()->getRepository('\Application\Entity\DantaiLock');
        $dantaiLockRepo->removeLock($module, $locker);
    }

    /**
     * (non-PHPdoc)
     * @see \Application\Service\ServiceInterface\DantaiServiceInterface::purgeExpiredLocks()
     */
    public function purgeExpiredLocks($purgeAll = null) {
        if ($module instanceof \Application\Entity\Common) {
            $module = get_class($module) . $module->getId();
        }
        $currentIdentity = $this->getCurrentUser();
        $locker = ($purgeAll) ? null : $currentIdentity['id'];
        /**
         * @var \Application\Entity\Repository\DantaiLockRepository
         */
        $dantaiLockRepo = $this->getEntityManager()->getRepository('\Application\Entity\DantaiLock');
        $dantaiLockRepo->purgeExpiredLocks($locker);
    }

    /**
     * (non-PHPdoc)
     * @see \Application\Service\ServiceInterface\DantaiServiceInterface::registerCleanLock()
     */
    public function registerCleanLock($event, $module) {
        $lastLocks = PrivateSession::getData(self::PRIVATE_LOCK_LIST);
        if (empty($lastLocks)) {
            $lastLocks = array();
        }

        $routeMatch = $event->getRouteMatch();
        $controller = $routeMatch->getParam('controller');
        $controller = str_replace('\\', '/', $controller);
        $action = $routeMatch->getParam('action');

        // FIXME Please consider route match as controller/action as very detail behavior to auto clean
        $routeMatch = strtolower($controller . '/' . $action);

        if ($module instanceof \Application\Entity\Common) {
            $module = get_class($module) . $module->getId();
        }

        if (!array_key_exists($routeMatch, $lastLocks)) {
            $lastLocks[$routeMatch] = $module;
        }

        PrivateSession::setData(self::PRIVATE_LOCK_LIST, $lastLocks);
    }

    /**
     * (non-PHPdoc)
     * @see \Application\Service\ServiceInterface\DantaiServiceInterface::autoCleanLock()
     */
    public function autoCleanLock($event) {
        $lastLocks = PrivateSession::getData(self::PRIVATE_LOCK_LIST);
        if (empty($lastLocks))
            return;

        $routeMatch = $event->getRouteMatch();
        if (empty($routeMatch))
            return;
        $controller = $routeMatch->getParam('controller');
        $controller = str_replace('\\', '/', $controller);
        $action = $routeMatch->getParam('action');

        // FIXME Please consider route match as controller/action as very detail behavior to auto clean
        $routeMatch = strtolower($controller . '/' . $action);

        foreach ($lastLocks as $route => $module) {
            if ($route != $routeMatch)
                $this->releaseLockModule($module);
        }
    }

    // END Lock mechanism

    /**
     * (non-PHPdoc)
     * @see \Application\Service\ServiceInterface\DantaiServiceInterface::getSearchCriteria()
     */
    public function getSearchCriteria(\Zend\Mvc\MvcEvent $event, array $criteriaDefault) {
        $request = $event->getRequest();

        // Do not process if the even does not come from HTTP requests
        if (!$request instanceof \Zend\Http\Request)
            return $criteriaDefault;

        $routeMatch = $event->getRouteMatch();

        $token = $routeMatch->getParam('search');

        $controller = str_replace('\\', '/', $routeMatch->getParam('controller'));
        $action = $routeMatch->getParam('action');
        $sessionKey = 'FilterCriteria/' . $controller . '/' . $action;

        if ($event->getRequest()->isPost()) {
            foreach ($criteriaDefault as $key => $criteria) {
                $criteriaDefault[$key] = trim($request->getPost($key, $criteria));
            }

            unset($criteriaDefault['token']);
            $token = $criteriaDefault['token'] = $this->getToken($criteriaDefault);
            PrivateSession::setData($sessionKey, $criteriaDefault);
        } else if ($token) {
            $session = PrivateSession::getData($sessionKey);
            if ($session && $session['token'] == $token) {
                if (empty($criteriaDefault))
                    $criteriaDefault = $session;
                foreach ($criteriaDefault as $key => $criteria)
                    if (array_key_exists($key, $session))
                        $criteriaDefault[$key] = $session[$key];
            }
        }

        return $criteriaDefault;
    }

    public function logActivity($moduleName, $controller, $action, $actionsType, $activityData, $isPost = false, $routeMatch = Null) {
        $isSaveLog = 1;
        $em = $this->getEntityManager();
        if (!$em->isOpen()) {
            $em = $em->create($em->getConnection(), $em->getConfiguration());
        }
        // Get general info
        $user = $this->getCurrentUser();
        if ($user['userId']) {
            if ($moduleName == 'iba' && $controller == 'iba' && $action == 'show' && !$isPost)
                return;
            // Verify action in some case
            if (($moduleName == 'eiken' && $controller == 'eikenorg' && $action == 'save') || ($moduleName == 'iba' && $controller == 'iba' && ($action == 'show' || $action == 'savedraft'))) {
                $createFlag = PrivateSession::getData('create-activity-log-flag');
                if (!$createFlag) {
                    $activityData['type'] = $actionsType['update'];
                    if ($action == 'savedraft') {
                        $activityData['screen'] = '申込情報編集';
                    } else {
                        $screen = PrivateSession::getData('iba-confirm-page-title');
                        $confirm = PrivateSession::getData('iba-confirm-action');
                        if (!$screen)
                            $screen = '申込情報確認';
                        $activityData['screen'] = $screen;
                        
                        if ($confirm)
                            $activityData['type'] = $actionsType['confirm'];
                    }
                }
                // Clear session flag
                PrivateSession::clear('create-activity-log-flag');
                PrivateSession::clear('iba-confirm-page-title');
            }
            else if ($moduleName == 'homepage' && $controller == 'homepage' && $action == 'getexportlistattendpupil') {
                $isDetailB = PrivateSession::getData('is-detail-b-activity-flag');
                if ($isDetailB) {
                    $activityData['screen'] = '英検合格実績';
                }
                // Clear session flag
                PrivateSession::clear('is-detail-b-activity-flag');
            } elseif ($moduleName == 'eiken' && $controller == 'eikenorg' && $action == 'save') {
                $kaiNumber = '';
                $eiKenSchedule = $em->getRepository('Application\Entity\EikenSchedule')->getAvailableEikenScheduleByDate(date('Y'), date('Y-m-d H:i:s'));
                if ($eiKenSchedule)
                    $kaiNumber = $eiKenSchedule['kai'];
                $createFlag = PrivateSession::getData('create-activity-log-flag');
                if (!$createFlag) {
                    $activityData['type'] = $actionsType['update'];
                    $activityData['screen'] = '団体申込情報編集：' . date('Y') . '年度第' . $kaiNumber . '回';
                } else
                    $activityData['screen'] = '申込情報登録：' . date('Y') . '年度第' . $kaiNumber . '回';
                // Clear session flag
                PrivateSession::clear('create-activity-log-flag');
            }
            elseif ($moduleName == 'goalsetting' && $controller == 'graduationgoalsetting' && $action == 'updategraduationgoal') {
                $isYearTagetPass = PrivateSession::getData('yeartagetPass-flag');
                if ($isYearTagetPass) {
                    $activityData['screen'] = '卒業時目標編集';
                } else {
                    $activityData['screen'] = '年度目標編集';
                }
                PrivateSession::clear('yeartagetPass-flag');
            } elseif ($moduleName == 'invitationmnt' && $controller == 'recommended') {
                $kaiNumber = '';
                $eiKenSchedule = $em->getRepository('Application\Entity\EikenSchedule')->getAvailableEikenScheduleByDate(date('Y'), date('Y-m-d H:i:s'));
                if ($eiKenSchedule)
                    $kaiNumber = $eiKenSchedule['kai'];
                $activityData['screen'] = '目標級設定：' . date('Y') . '年度第' . $kaiNumber . '回';
            }

            if ($moduleName == 'history' && $controller == 'eiken' && $action == 'pupilachievement') {
                if ($routeMatch == Null || $routeMatch->getParam('isExportExcel') != 1) {
                    $isSaveLog = 0;
                }
            } else if ($moduleName == 'history' && $controller == 'eiken' && $action == 'examhistorylist') {
                if ($routeMatch == Null || $routeMatch->getParam('isExportExcel') != 1) {
                    $isSaveLog = 0;
                }
            } else if ($moduleName == 'history' && $controller == 'iba' && $action == 'pupilachievement') {
                if ($routeMatch == Null || $routeMatch->getParam('isExportExcel') != 1) {
                    $isSaveLog = 0;
                }
            }
            if ($isSaveLog == 1) {
                $activity = new \Application\Entity\ActivityLog();
                $activity->setOrganizationNo($user['organizationNo']);
                $activity->setOrganizationName($user['organizationName']);
                $activity->setUserID($user['userId']);
                $activity->setUserName($user['firstName'] . $user['lastName']);
                $activity->setActionName($activityData['type']);
                $activity->setScreenName($activityData['screen']);
                $em->persist($activity);
                $em->flush();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getToken($search) {
        $hash = hash('crc32', serialize($search));

        return $hash;
    }

    /**
     * @param string $zipcode
     * @return mix null | array('zipCode' => ...,
     *                          'cityName' => ...,
     *                          'districtName' => ...,
     *                          'address' => ...)
     */
    public function zipcode2Address($zipcode) {
        $res = $this->getEntityManager()->getRepository('\Application\Entity\ZipCode')->findBy(['zipCode' => $zipcode]);
        $return = array();
        foreach ($res as $row) {
            $return[] = $row->toArray(true);
        }
        return $return;
    }

    /**
     * Get current year for japan
     * @return number
     */
    public function getCurrentYear() {
        $month = date('m');
        if ($month < 4)
            return (int) date('Y') - 1;
        return (int) date('Y');
    }

    /**
     * @editby minhbn1
     * 
     * Try to lock the given $module for $locker
     * This function returns number if lock is created success fully, false if the current lock is created by another locker
     * 
     * @param string $module
     * @param number $locker
     * 
     * @return number|false
     */
    public function getLock($module, $locker = 0) {
        $em = $this->getEntityManager();
        $dantaiLock = $em->getRepository('\Application\Entity\DantaiLock')->findOneBy(array(
            'module' => $module,
        ));
        if ($dantaiLock) {
            return false;
        }

        $dantaiLock = new \Application\Entity\DantaiLock();
        $dantaiLock->setLocker($locker);
        $dantaiLock->setModule($module);
        try {
            $em->persist($dantaiLock);
            $em->flush();
            $em->refresh($dantaiLock);
        } catch (Exception $e) {
            // Concurent lock, and this locker is luckyless
            return false;
        }
        return true;
    }

    public function ChangeStatusAutoMappingIBA($id, $status = '') {
        $em = $this->getEntityManager();
        $objIba = $em->getRepository('\Application\Entity\ApplyIBAOrg')->find($id);
        if ($objIba) {
            $config = $this->getServiceLocator()->get('config');
            $ibaStatus = $config['IBA_StatusAutoImport'];
            if ($status) {
                $objIba->setStatusAutoImport($ibaStatus['Failure']);
            } else {
                $objIba->setStatusAutoImport($ibaStatus['Running']);
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();
            return true;
        }
        return false;
    }

    public function ChangeStatusAutoMappingEiken($id, $round) {
        $em = $this->getEntityManager();
        $objEikenOrg = $em->getRepository('\Application\Entity\ApplyEikenOrg')->find($id);
        if ($objEikenOrg) {
            $config = $this->getServiceLocator()->get('config');
            $eikenStatus = $config['Eiken_StatusAutoImport'];
            if ($round === 1) {
                $objEikenOrg->setStatusAutoImport($eikenStatus['Round1Running']);
            } else if ($round === 2) {
                $objEikenOrg->setStatusAutoImport($eikenStatus['Round2Running']);
            } else {
                $objEikenOrg->setStatusAutoImport($eikenStatus['Failure']);
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();
            return true;
        }
        return false;
    }

    /**
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * Add Org Id to sqs queue
     *
     * @param type|int $orgId
     * @param int $year
     * @return bool
     */
    function addOrgToQueue($orgId = 0, $year = 0) {
        $orgId = (int) $orgId;
        $year = (int) $year;
        if (!$orgId || !$year) {
            return false;
        }
        $key = $this->getOrgKeyInDantaiLock($orgId, $year);
        if (!$this->getLock($key)) {
            return false;
        }
        \Dantai\Aws\AwsSqsClient::getInstance()->sendMessage(array(
            'MessageBody' => \Zend\Json\Encoder::encode(array(
                'orgId' => $orgId,
                'time' => time(),
                'year' => $year,
            )),
            'QueueUrl' => \Dantai\Aws\AwsSqsClient::QUEUE_ANALYSEACHIEVEMENT_COMBIBI,
        ));
        return true;
    }

    function addAutoMapToQueue($type = 0, $orgId = 0, $orgNo = 0, $paramEiken = null, $paramIBA = null, $host = '', $userId = '') {

        if (empty($orgId)) {
            return false;
        }
        $key = '';
        if ($type == 'EIKEN' && $paramEiken) {
            $key = 'auto-eiken-' . $orgId . '-' . $paramEiken['kaiId'] . '-' . $paramEiken['round'] . '-in-queue';
            $this->ChangeStatusAutoMappingEiken($paramEiken['applyEikenOrgId'], $paramEiken['round']);
        } else if ($type == 'IBA') {
            $key = 'auto-iba-' . $orgId . '-in-queue';
//            $this->ChangeStatusAutoMappingIBA($paramIBA['ibaId']);
        } else {
            return false;
        }
        if (!$this->getLock($key)) {
            return true;
        }
        \Dantai\Aws\AwsSqsClient::getInstance()->sendMessage(array(
            'MessageBody' => \Zend\Json\Encoder::encode(array(
                'type' => $type,
                'orgId' => $orgId,
                'orgNo' => $orgNo,
                'paramEiken' => $paramEiken,
                'paramIBA' => $paramIBA,
                'host' => $host,
                'userId' => $userId
            )),
            'QueueUrl' => \Dantai\Aws\AwsSqsClient::QUEUE_AUTORUN_MAPPING,
        ));
        return true;
    }

    function getAutoMappingInQueue() {
        $result = null;
        $message = \Dantai\Aws\AwsSqsClient::getInstance()->receiveMessage(\Dantai\Aws\AwsSqsClient::QUEUE_AUTORUN_MAPPING);
        if ($message) {
            $message['Body'] = \Zend\Json\Json::decode($message['Body'], \Zend\Json\Json::TYPE_ARRAY);
            if ($message['Body'] && isset($message['Body']['orgId'])) {
                $result = $message;
            }
        }
        return $result;
    }

    function getListAutoMappingInQueue($limit = 0) {
        $limit = (int) $limit;
        $results = array();
        if (!$limit)
            return $results;
        for ($i = 0; $i < $limit; $i++) {
            $objQueueInfo = $this->getAutoMappingInQueue();
            if (!$objQueueInfo)
                break;
            if ($objQueueInfo && isset($objQueueInfo['Body']))
                $results[] = $objQueueInfo;
        }
        return $results;
    }

    function deleteAutoMappingInQueue($type = 0, $orgId = 0, $orgNo = 0, $paramEiken = 0, $paramIBA = 0, $receiptHandle = '') {
        // Delete lock
        $result = false;
        $key = '';
        if ($type == 'EIKEN') {
            $key = 'auto-eiken-' . $orgId . '-' . $paramEiken['kaiId'] . '-' . $paramEiken['round'] . '-in-queue';
        } else if ($type == 'IBA') {
            $key = 'auto-iba-' . $orgId . '-in-queue';
        } else {
            return false;
        }
        $dantaiLockRepo = $this->getEntityManager()->getRepository('\Application\Entity\DantaiLock');
        $dantaiLockRepo->removeLock($key);
        // Delete queue
        if ($receiptHandle) {
            $result = \Dantai\Aws\AwsSqsClient::getInstance()
                    ->deleteMessage(\Dantai\Aws\AwsSqsClient::QUEUE_AUTORUN_MAPPING, $receiptHandle);
        }
        return $result;
    }

    /**
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * get org id in sqs queue
     * 
     * @return mix
     */
    function getOrgInQueue() {
        $message = \Dantai\Aws\AwsSqsClient::getInstance()->receiveMessage(\Dantai\Aws\AwsSqsClient::QUEUE_ANALYSEACHIEVEMENT_COMBIBI);
        $result = null;
        if ($message) {
            $message['Body'] = \Zend\Json\Json::decode($message['Body'], \Zend\Json\Json::TYPE_ARRAY);
            if ($message['Body'] && isset($message['Body']['orgId'])) {
                $result = $message;
            }
        }
        return $result;
    }

    /**
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * get list org in queue
     * @param type $limit
     * @return array
     */
    function getListOrgInQueue($limit = 0) {
        $limit = (int) $limit;
        $results = array();
        if (!$limit)
            return $results;
        for ($i = 0; $i < $limit; $i++) {
            $orgInfo = $this->getOrgInQueue();
            if (!$orgInfo)
                break;
            if ($orgInfo && isset($orgInfo['Body']))
                $results[] = $orgInfo;
        }
        return $results;
    }

    /**
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * get list org in queue
     * @param type $limit
     * @return array
     */
    function getListOrgIdInQueue($limit = 0) {
        $limit = (int) $limit;
        $results = array();
        if (!$limit)
            return $results;
        for ($i = 0; $i < $limit; $i++) {
            $orgInfo = $this->getOrgInQueue();
            if (!$orgInfo)
                break;
            if ($orgInfo && isset($orgInfo['Body']['orgId']))
                $results[] = $orgInfo['Body']['orgId'];
        }
        return $results;
    }

    /**
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * change visibility of Org
     * @param type $receiptHandle
     */
    function changeOrgVisibility($receiptHandle = '') {
        \Dantai\Aws\AwsSqsClient::getInstance()
                ->changeMessageVisibility(\Dantai\Aws\AwsSqsClient::QUEUE_ANALYSEACHIEVEMENT_COMBIBI, $receiptHandle, 1);
    }

    /**
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * Delete Org In Queue
     * @param int $orgId
     * @param int $year
     * @param type|string $receiptHandle
     * @return type
     */
    function deleteOrgQueue($orgId = 0, $year = 0, $receiptHandle = '') {
        // Delete lock
        $key = $this->getOrgKeyInDantaiLock($orgId, $year);
        $dantaiLockRepo = $this->getEntityManager()->getRepository('\Application\Entity\DantaiLock');
        $dantaiLockRepo->removeLock($key);
        // Delete queue
        if ($receiptHandle) {
            \Dantai\Aws\AwsSqsClient::getInstance()
                    ->deleteMessage(\Dantai\Aws\AwsSqsClient::QUEUE_ANALYSEACHIEVEMENT_COMBIBI, $receiptHandle);
        }
    }

    /**
     * get key of Org in Dantai Lock
     * @param type|int $orgId
     * @param int $year
     * @return string
     */
    public function getOrgKeyInDantaiLock($orgId = 0, $year = 0) {
        $key = '';
        if ($orgId && $year) {
            $postKey = (date('H') < self::ACHIVEMENT_TIME_RUN) ? date('d-m-Y') : date('d-m-Y', strtotime("+1 day"));
            $key = self::LOCK_ORG_IN_QUEUE_PREFIX . '-' . $orgId . '-' . $postKey . '-' . $year;
        }
        return $key;
    }

    /*
     * get data price, levelName of list EikenLevelId By OrganizationNo And Array EikenLevelIds 
     * @param int $orgNo
     * @param array $eikenLevelIds
     * @author: minhtn6
     * 
     * @return array
     */

    public function getOldPriceOfOrganization($orgNo, $eikenLevelIds) {
        $mailHallIdx = 1;
        $standardHallIdx = 0;
        $config = $this->getServiceLocator()->get('Config')['orgmnt_config']['api'];
        try {
            if (!$this->ukestukeClient) {
                $this->setUkestukeClient();
            }
            $result = $this->ukestukeClient->callEir2a01($config, array(
                'dantaino' => $orgNo
            ));
        } catch (\Exception $e) {
            return false;
        }
        if ($result->kekka != 10) {
            return false;
        }
        $definition = $result->kyotenkoukbn;
        switch ($definition) {
            case self::DEFINITION_SPECIAL_ONE:
                $field[$mailHallIdx] = 'speciceFeeMainHallOne';
                $field[$standardHallIdx] = 'speciceFeeStandardHallOne';
                break;
            case self::DEFINITION_SPECIAL_TWO:
                $field[$mailHallIdx] = 'speciceFeeMainHallTwo';
                $field[$standardHallIdx] = 'speciceFeeStandardHallTwo';
                break;
            case self::DEFINITION_SPECIAL_THREE:
                $field[$mailHallIdx] = 'speciceFeeMainHallThree';
                $field[$standardHallIdx] = 'speciceFeeStandardHallThree';
                break;
            default:
                $field[$mailHallIdx] = 'mainHallTuitionFee';
                $field[$standardHallIdx] = 'standardHallTuitionFee';
                break;
        }
        if (!$this->eikenLevelRepos) {
            $this->setEikenLevelRepository();
        }
        $eikenLevels = $this->eikenLevelRepos->getListDataByEikenLevelIds($eikenLevelIds);
        if (!$eikenLevels) {
            return false;
        }
        $priceLevel = array();
        foreach ($eikenLevels as $eikenLevel) {
            $priceMainHall = $eikenLevel[$field[$mailHallIdx]];
            $priceLevel[$mailHallIdx][$eikenLevel['id']]['price'] = $priceMainHall;
            $priceLevel[$mailHallIdx][$eikenLevel['id']]['name'] = $eikenLevel['levelName'];

            $priceStandardHall = $eikenLevel[$field[$standardHallIdx]];
            //if eikenLevel is 1kyu and pre1kyu, price of standardHall like price of mailHall
            if (in_array($eikenLevel["id"], array(1, 2))) {
                $priceStandardHall = $eikenLevel[$field[$mailHallIdx]];
            }
            $priceLevel[$standardHallIdx][$eikenLevel["id"]]["price"] = $priceStandardHall;
            $priceLevel[$standardHallIdx][$eikenLevel['id']]['name'] = $eikenLevel['levelName'];
        }
        return $priceLevel;
    }

    public function eikenLevelKeyMapper() {
        return array(
            'lev1' => 1,
            'preLev1' => 2,
            'lev2' => 3,
            'preLev2' => 4,
            'lev3' => 5,
            'lev4' => 6,
            'lev5' => 7,
        );
    }

    public function getNewPriceOfOrganization($conditions = array()) {
        $eikenLevel = $this->getEntityManager()
                ->getRepository('\Application\Entity\EikenLevel')
                ->listEikenLevelName();

        $eikenLevelName = array();
        foreach ($eikenLevel as $level) {
            $eikenLevelName[$level['id']] = $level['levelName'];
        }

        $data = $this->getEntityManager()
                        ->getRepository('\Application\Entity\SpecialPrice')
                        ->getSpecialPrice($conditions);

        $specialPrice = array();
        $keyMapper = $this->eikenLevelKeyMapper();

        $pricelv1 = 0;
        $pricePreLv1 = 0;
        foreach ($data as $item) {
            if($item['hallType'] == 1){
                $pricelv1 = $item['lev1'];
                $pricePreLv1 = $item['preLev1'];
            }
            $specialPrice[$item['hallType']] = array(
                $keyMapper['lev1'] => [
                    'price' => $item['lev1'] ? $item['lev1'] : 0,
                    'name' => $eikenLevelName[$keyMapper['lev1']],
                ],
                $keyMapper['preLev1'] => [
                    'price' => $item['preLev1'] ? $item['preLev1'] : 0,
                    'name' => $eikenLevelName[$keyMapper['preLev1']],
                ],
                $keyMapper['lev2'] => [
                    'price' => $item['lev2'],
                    'name' => $eikenLevelName[$keyMapper['lev2']],
                ],
                $keyMapper['preLev2'] => [
                    'price' => $item['preLev2'],
                    'name' => $eikenLevelName[$keyMapper['preLev2']],
                ],
                $keyMapper['lev3'] => [
                    'price' => $item['lev3'],
                    'name' => $eikenLevelName[$keyMapper['lev3']],
                ],
                $keyMapper['lev4'] => [
                    'price' => $item['lev4'],
                    'name' => $eikenLevelName[$keyMapper['lev4']],
                ],
                $keyMapper['lev5'] => [
                    'price' => $item['lev5'],
                    'name' => $eikenLevelName[$keyMapper['lev5']],
                ],
            );
        }
        
        if(isset($specialPrice[0][$keyMapper['lev1']]['price']) && !empty($pricelv1)){
            $specialPrice[0][$keyMapper['lev1']]['price'] = $pricelv1;
        }
        if(isset($specialPrice[0][$keyMapper['preLev1']]['price']) && !empty($pricePreLv1)){
            $specialPrice[0][$keyMapper['preLev1']]['price'] = $pricePreLv1;
        }

        if (!empty($eikenLevelIds = $conditions['eikenLevelIds'])) {
            return array_map(function($item) use ($eikenLevelIds) {
                foreach (array_keys($item) as $k) {
                    if (!in_array($k, $eikenLevelIds)) {
                        unset($item[$k]);
                    }
                }
                return $item;
            }, $specialPrice);
        }

        return $specialPrice;
    }

    /*
     * @param:  $orgNo
     * @param:  $eikenLevelIds
     * @param:  $options
     * Example:  $options = array(
      'orgSchoolYearId' => 8,
      'year' => 2016,
      'kai' =>2,
      ))
     */

    public function getListPriceOfOrganization($orgNo, $eikenLevelIds, $options = array()) {
        $priceOfOrganization = array();

        if (!empty($options['orgSchoolYearId']) && !empty($options['year']) &&
                !empty($options['kai'])) {
            $priceOfOrganization = $this->getNewPriceOfOrganization(array(
                'orgNo' => $orgNo,
                'eikenLevelIds' => $eikenLevelIds,
                'orgSchoolYearId' => $options['orgSchoolYearId'],
                'year' => $options['year'],
                'kai' => $options['kai'],
            ));
        }

        return !empty($priceOfOrganization) ? $priceOfOrganization :
                $this->getOldPriceOfOrganization($orgNo, $eikenLevelIds);
    }

    /*
     * Author : ManhNH5
     */

    public function getDefinitionSpecial($orgNo) {
        $config = $this->getServiceLocator()->get('Config')['orgmnt_config']['api'];
        try {
            if (!$this->ukestukeClient) {
                $this->setUkestukeClient();
            }
            $result = $this->ukestukeClient->callEir2a01($config, array(
                'dantaino' => $orgNo
            ));
        } catch (\Exception $e) {
            return false;
        }
        if ($result->kekka != 10) {
            return 0;
        }

        return isset($result->kyotenkoukbn) ? $result->kyotenkoukbn : 0;
    }

    public function setUkestukeClient($client = null) {
        $this->ukestukeClient = $client ? $client : UkestukeClient::getInstance();
    }

    public function setEikenLevelRepository($eikenLevelRepos = Null) {
        $this->eikenLevelRepos = $eikenLevelRepos ? $eikenLevelRepos : $this->getEntityManager()->getRepository('\Application\Entity\EikenLevel');
    }

    /**
     * Function get SVM for a dantai in a Kai (value in the last letter generated time)
     * if a dantai is special public funding dantai, which must be non-SVM dantai
     * 1: is SVM dantai, 0,null: non-SVM dantai
     * @param $orgId
     * @param $eikenScheduleId
     * @return int|null
     */
    public function getSemiMainVenue($orgId, $eikenScheduleId) {
        if($this->isSpecialOrg($orgId, $eikenScheduleId)){
            return 0;
        }
        $em = $this->getEntityManager();

        /** @var SemiVenue $semiVenue */
        $semiVenue = $em->getRepository('Application\Entity\SemiVenue')
            ->findOneBy(array(
                         'organizationId'  => $orgId,
                         'eikenScheduleId' => $eikenScheduleId,
                         'isDelete' => 0
                     ));

        if(empty($semiVenue)){
            return null;
        }
        return $semiVenue->getSemiMainVenueTemp();
    }

    /**
     * Function get SVM for a dantai in a Kai (same as value in Org management)
     * if a dantai is special public funding dantai, which must be non-SVM dantai
     * 1: is SVM dantai, 0,null: non-SVM dantai
     * @param $orgId
     * @param $eikenScheduleId
     * @return int|null
     */
    public function getSemiMainVenueOrigin($orgId, $eikenScheduleId){
        if($this->isSpecialOrg($orgId, $eikenScheduleId)){
            return 0;
        }
        $em = $this->getEntityManager();
        $semiVenue = $em->getRepository('Application\Entity\SemiVenue')
            ->findOneBy(array(
                            'organizationId'  => $orgId,
                            'eikenScheduleId' => $eikenScheduleId,
                            'isDelete' => 0
                        ));

        if(empty($semiVenue)){
            return null;
        }
        return $semiVenue->getSemiMainVenue();
    }

    public function getBeneficiaryVenue($orgId, $eikenScheduleId) {
        if($this->isSpecialOrg($orgId, $eikenScheduleId)){
            return null;
        }
        $em = $this->getEntityManager();
        $invitationSetting = $em->getRepository('Application\Entity\InvitationSetting')
                ->findOneBy(array(
            'organizationId' => $orgId,
            'eikenScheduleId' => $eikenScheduleId,
        ));
        if (empty($invitationSetting))
            return null;

        return $invitationSetting->getStatus() == 1 ? $invitationSetting->getTempBeneficiary() : $invitationSetting->getBeneficiary();
    }

    public function getBeneficiaryVenueOrigin($orgId, $eikenScheduleId) {
        if($this->isSpecialOrg($orgId, $eikenScheduleId)){
            return null;
        }
        $em = $this->getEntityManager();
        $invitationSetting = $em->getRepository('Application\Entity\InvitationSetting')
            ->findOneBy(array(
                            'organizationId'  => $orgId,
                            'eikenScheduleId' => $eikenScheduleId,
                        ));
        return empty($invitationSetting) ? null : $invitationSetting->getBeneficiary();
    }

    public function getCurrentEikenSchedule($year = '', $id = '') {
        $em = $this->getEntityManager();
        if (!empty($id)) {
            $val = $em->getRepository('Application\Entity\EikenSchedule')->find($id);

            return (object) array('year' => $val->getYear(), 'kai' => $val->getKai(), 'id' => $val->getId());
        }
        $data = $em->getRepository('Application\Entity\EikenSchedule')->findBy(array('year' => empty($year) ? date('Y') : $year), array('kai' => 'ASC'));
        if (!empty($data)) {
            foreach ($data as $val) {
                if (!empty($val->getDeadlineTo()) && date('Y-m-d') <= $val->getDeadlineTo()->format('Y-m-d')) {
                    return (object) array('year' => $val->getYear(), 'kai' => $val->getKai(), 'id' => ($year != '') ? '' : $val->getId());
                }
            }
        }

        return (object) array('year' => $year, 'kai' => '', 'id' => '');
    }

    public function getEndEikenSchedule() {
        $currentEikenSchedule = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();

        return (object) $currentEikenSchedule;
    }

    function array_orderby() {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);

        return array_pop($args);
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityManager() {
        if (empty($this->em)) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

    public function setEntityManager($em) {
        $this->em = $em;
    }
    
    public function isAlphanumericCharacter($listGradeClass, $type, $dataInput = array(),$flagUpload = false) {
        
        $listGradeClass = !is_array($listGradeClass) ? array() : $listGradeClass;
        $dataInput = !is_array($dataInput) ? array() : $dataInput;
        $keepInput = $dataInput;

        // case update but not change name class or grade
        if (count($dataInput) === 1 && in_array($dataInput[0], $listGradeClass)) {
            $dataInput = array();
        }

        $arrUnique = $this->getUniqueArray($listGradeClass, $type);

        // case show MSG in home page and setting EC , gen EC
        if (!$keepInput && $flagUpload == false) {
            return (count($listGradeClass) == count($arrUnique)) ? true : false;
        }

        $arrUniqueInput = $this->getUniqueArray($keepInput, $type);
        // check unique of name input 
        if (count($keepInput) > count($arrUniqueInput)) {
            return false;
        }
        // check unique nomal case
        if (array_intersect($arrUniqueInput, $arrUnique) && count($keepInput) == count($dataInput)) {
            return false;
        }

        /*
         * case : update not change name Grade Class
         * if Input Name same as data base  then set : $keepInput = $dataInput = name (Grade/Class); $dataInput = empty;
         * continue cut and create $arrUniqueInput and $arrUnique
         * comparisons between two arrays if valuable names of Grade / Class duplicate
         * 
         */
        if (count($keepInput) == 1 && count($dataInput) == 0 && array_intersect($arrUniqueInput, $arrUnique)) {
            return false;
        }

        return true;
    }

    public function generateUniqueOfGrade($string) {
        $firstChar = $this->convertFullToHaf($this->cutCharacterWithNumber($string, ApplicationConst::NUMBER_CUT_OF_GRADE));
        if (!preg_match('/^[A-Za-z0-9]*$/', $firstChar)) {
            return NULL;
        }
        return $firstChar;
    }

    public function generateUniqueOfClass($string) {
        $arrString = explode(ApplicationConst::DELIMITER_VALUE, $string);
        if (count($arrString) != 2) {
            return NULL;
        }
        $firstChar = $this->convertFullToHaf($this->cutCharacterWithNumber($arrString[1], ApplicationConst::NUMBER_CUT_OF_CLASS));
        if (!preg_match('/^[A-Za-z0-9]*$/', $firstChar)) {
            return NULL;
        }
        return $arrString[0] . $firstChar;
    }

    public function cutCharacterWithNumber($value, $numberCharacter) {
        if (!empty($value) || ($numberCharacter)) {
            return mb_substr($value, 0, $numberCharacter, "UTF-8");
        }
        return '';
    }

    public function convertFullToHaf($string) {
        list($alphaHalf, $listNumber) = $this->getCharHafSize();
        list($alphaFull, $listNumberFull) = $this->getCharFullSize();

        $str = str_replace($listNumberFull, $listNumber, trim($string));
        $result = str_replace($alphaFull, $alphaHalf, $str);
        return $result;
    }

    public function getCharHafSize() {
        $alphaHalf = array(" ","a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "k", "q", "r", "s", "t", "v", "w", "s", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "K", "Q", "R", "S", "T", "V", "W", "S", "X", "Y", "Z");
        $listNumberHalf = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        return array($alphaHalf, $listNumberHalf);
    }

    public function getCharFullSize() {
        $alphaFull = array("　","ａ", "ｂ", "ｃ", "ｄ", "ｅ", "ｆ", "ｇ", "ｈ", "ｉ", "ｊ", "ｋ", "ｌ", "ｍ", "ｎ", "ｏ", "ｐ", "ｋ", "ｑ", "ｒ", "ｓ", "ｔ", "ｖ", "ｗ", "ｓ", "ｘ", "ｙ", "ｚ", "Ａ", "Ｂ", "Ｃ", "Ｄ", "Ｅ", "Ｆ", "Ｇ", "Ｈ", "Ｉ", "Ｊ", "Ｋ", "Ｌ", "Ｍ", "Ｎ", "Ｏ", "Ｐ", "Ｋ", "Ｑ", "Ｒ", "Ｓ", "Ｔ", "Ｖ", "Ｗ", "Ｓ", "Ｘ", "Ｙ", "Ｚ");
        $listNumberFull = array('０', '１', '２', '３', '４', '５', '６', '７', '８', '９');
        return array($alphaFull, $listNumberFull);
    }

    public function isNotShowMSGGradeClass($year, $organizationId) {
        if (empty($year)) {
            $year = (int) date('Y');
        }
        if (empty($organizationId)) {
            return false;
        }
        $gradeObj = $this->getEntityManager()->getRepository('Application\Entity\OrgSchoolYear')->findBy(array(
            'organizationId' => $organizationId,
            'isDelete' => 0
        ));

        $classObj = $this->getEntityManager()->getRepository('Application\Entity\ClassJ')->findBy(array(
            'organizationId' => $organizationId,
            'year' => $year,
            'isDelete' => 0
        ));
        $result = true;
        $resultGrade = true;
        $resultClass = true;
        if ($gradeObj) {
            $arrGrade = array();
            /* @var $row \Application\Entity\OrgSchoolYear */
            foreach ($gradeObj as $row) {
                array_push($arrGrade, $row->getDisplayName());
            }

            $resultGrade = $this->isAlphanumericCharacter($arrGrade, ApplicationConst::GRADE_TYPE, array());
        }
        if ($classObj) {
            $arrClass = array();
            /* @var $class \Application\Entity\ClassJ */
            foreach ($classObj as $key => $class) {
                $gradeKey = $class->getOrgSchoolYear() ? $class->getOrgSchoolYear()->getDisplayName() : $key;
                array_push($arrClass, $gradeKey . ApplicationConst::DELIMITER_VALUE . $class->getClassName());
            }
            $resultClass = $this->isAlphanumericCharacter($arrClass, ApplicationConst::CLASS_TYPE);
        }

        if ($resultGrade === false || $resultClass === false) {
            $result = false;
        }

        return $result;
    }

    public function getUniqueArray($listGradeClass, $type) {
        $arrUnique = array();
        if ($listGradeClass) {
            foreach ($listGradeClass as $value) {
                $value = strval(!empty($value) ? trim($value) : '');
                $uniqueKey = $type == 1 ? $this->generateUniqueOfGrade($value) : $this->generateUniqueOfClass($value);
                if ($uniqueKey != NULL) {
                    $arrUnique[$uniqueKey] = $uniqueKey;
                }
            }
        }
        return $arrUnique;
    }

    /**
     * Function: check an org is special or not in specific Kai
     * @param $orgId
     * @param $eikenScheduleId
     * @return bool
     */
    public function isSpecialOrg($orgId, $eikenScheduleId){
        $em = $this->getEntityManager();
        if (empty($eikenScheduleId) || empty($orgId)) {
            return 0;
        }

        $objSchedule = $em->getRepository('Application\Entity\EikenSchedule')->find($eikenScheduleId);
        if (empty($objSchedule)) {
            return 0;
        }

        $objSpecialPrice = $em->getRepository('Application\Entity\SpecialPrice')
            ->findOneBy(array(
                            'organizationId' => $orgId,
                            'year'           => $objSchedule->getYear(),
                            'kai'            => $objSchedule->getKai(),
                            'isDelete'       => 0,
                        ));

        return $objSpecialPrice ? 1 : 0;
    }
    /* 
     * change day E to Japan
     * @var $day have format : format('D')
     */
    public function changeDay($day) {
        switch ($day) {
            case 'Fri':
                $day = '金';
                break;
            case 'Sat':
                $day = '土';
                break;
            case 'Sun':
                $day = '日';
                break;
            case 'Mon':
                $day = '月';
                break;
            case 'Tue':
                $day = '火';
                break;
            case 'Wed':
                $day = '水';
                break;
            case 'Thu':
                $day = '木';
                break;
        }
        return $day;
    }

    /**
    * Function: write Log
    * @param $logname
    * @param $parameters
    * @param $action
    */
    public function writeLog($fileName ,$part ,$parameters, $action, $note) {
        if(ApplicationConst::WRITE_LOG_INVESTIGATE === 1){
            $part = DATA_PATH . '/' . $part;
            $logPath = $part . '/' . $fileName .'.'. date('Ymd') .'.txt';
            
            @mkdir($part, 0777, true);
            
            array_walk_recursive($parameters, function(&$item, $key){
                if(!mb_detect_encoding($item, 'utf-8', true)){
                        $item = \Dantai\Utility\CharsetConverter::shiftJisToUtf8($item);
                }
            });
            
            $stream = @fopen($logPath, 'a', false);
            if ($stream) {
                $writer = new \Zend\Log\Writer\Stream($logPath);
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $time = date('Y-m-d H:i:s');
                $logger->info(' - DATA: ' . \Zend\Json\Json::encode($parameters) 
                        . ' - ACTION: ' . (string)$action
                        . ' - DESCRIPTION: ' . (string)$note
                        . ' - TIME: ' . (string)$time);
            }
        }
	}
    
    public function getDateRound2OfDantai($eikenScheduleId = false) {
        $result = array(1,2);
        
        $user = $this->getCurrentUser() ? $this->getCurrentUser() : $this->getCurrentUserStl();
        $config = $this->getServiceLocator()->get('config');
        $listDay1ExamDateRound2 = $config['listDantaiDay1ExamDateRound2'];
        $orgCode = '';
        $em = $this->getEntityManager();
        /*@var $objOrg \Application\Entity\Organization*/
        $objOrg = $em->getRepository('Application\Entity\Organization')->findOneBy(array('organizationNo'=> $user['organizationNo'] ? $user['organizationNo'] : 0));
        if($objOrg){
            $orgCode = $objOrg->getOrganizationCode();
        }
        if(in_array($orgCode,$listDay1ExamDateRound2) || $this->isDateA($eikenScheduleId)){
            $result = array(1);
        }
        
        return $result;
    }
    public function getDateRound2EachKyu($eikenScheduleId = false, $orgNo = Null) {
        /* key of array is eikenLevelId
         * kyu1 and pre 1 only show date A
         * not show examdate round 2 with kyu 4 and kyu 5
         */
        $result = array(
                        1=>1,
                        2=>1,
                        3=>2,
                        4=>2,
                        5=>2,
                    );

        $config = $this->getServiceLocator()->get('config');
        $listDay1ExamDateRound2 = $config['listDantaiDay1ExamDateRound2']; 
        $orgCode = '';
        $em = $this->getEntityManager();
        if($orgNo){
            $organizationNo = $orgNo;
        }else{
            $user = $this->getCurrentUser() ? $this->getCurrentUser() : $this->getCurrentUserStl();
            $organizationNo = $user['organizationNo'] ? $user['organizationNo'] : 0;
        }
        /* @var $objOrg \Application\Entity\Organization */
        $objOrg = $em->getRepository('Application\Entity\Organization')->findOneBy(array('organizationNo'=> $organizationNo));
        $orgId = Null;
        if($objOrg){
            $orgCode = $objOrg->getOrganizationCode();
            $orgId = $objOrg->getId();
        }
        
        if(in_array($orgCode,$listDay1ExamDateRound2) || $this->isDateA($eikenScheduleId, $orgId)){
            $result = array(
                        1=>1,
                        2=>1,
                        3=>1,
                        4=>1,
                        5=>1,
                    );
        }
        
        return $result;
    }
    
    public function isDateA($eikenScheduleId = false, $orgId = Null) {
        $em = $this->getEntityManager();
        if($orgId){
            $organizationId = $orgId;
        }else{
            $user = $this->getCurrentUser() ? $this->getCurrentUser() : $this->getCurrentUserStl();
            $organizationId = $user['organizationId'] ? $user['organizationId'] : 0;
        }
        $currentSchedule = $em->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();
        $eikenScheduleId = !$eikenScheduleId 
                ? ($currentSchedule['id'] ? $currentSchedule['id'] : 0) 
                : $eikenScheduleId;
        
        /*@var $objApplyEiken \Application\Entity\ApplyEikenOrg*/
        $objApplyEiken = $em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array('organizationId' => $organizationId, 'eikenScheduleId' => $eikenScheduleId));
        $districtName = (!empty($objApplyEiken) && !empty($objApplyEiken->getDistrict())) ? $objApplyEiken->getDistrict()->getName() : '';
        
        if(!empty($districtName) && ($districtName === ApplicationConst::ISLAND || $districtName === ApplicationConst::ABROAD)){
            return true;
        }
        
        return false;
    }
    
    public function isDantaiA($eikenScheduleId = false){
        if(count($this->getDateRound2OfDantai($eikenScheduleId)) ===1)
            return true;
        
        return false;
    }

    public function getIpAddress()
    {

        if (isset($_SERVER["HTTP_CLIENT_IP"]))
        {
            return $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
        {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        elseif (isset($_SERVER["HTTP_X_FORWARDED"]))
        {
            return $_SERVER["HTTP_X_FORWARDED"];
        }
        elseif (isset($_SERVER["HTTP_FORWARDED_FOR"]))
        {
            return $_SERVER["HTTP_FORWARDED_FOR"];
        }
        elseif (isset($_SERVER["HTTP_FORWARDED"]))
        {
            return $_SERVER["HTTP_FORWARDED"];
        }
        else
        {
            return $_SERVER["REMOTE_ADDR"];
        }
    }
    
}
