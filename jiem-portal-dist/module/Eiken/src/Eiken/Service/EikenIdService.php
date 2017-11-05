<?php
namespace Eiken\Service;

use Eiken\Service\ServiceInterface\EikenIdServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Entity\ApplyEikenPersonalInfo;
use Zend\Session\Container as SessionContainer;
use Zend\Json\Json;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Eiken\Form\EikenId\RegisterForm;
use Eiken\Form\EikenId\ReferenceForm;
use Dantai\Utility\DateHelper;
use Application\ApplicationConst;

class EikenIdService implements EikenIdServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    /**
     * @return array|object
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function isValidQuery($params)
    {
        // validate eiken level
        $eikenLevelId = $params->fromRoute('levelid', 0);
        if ($eikenLevelId > 0 && !in_array($eikenLevelId, array(1, 2, 3, 4, 5,6,7)))
        {
            return false;
        }
        $em = $this->getEntityManager();
        $appEikInfoId = $params->fromRoute('id', 0);
        if ($appEikInfoId > 0)
        {
            $appEikInfo = $em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->findBy(array(
                'id' => $appEikInfoId,
                'isDelete' => 0
            ));
            if (empty($appEikInfo))
                return false;
        }
        $appEikLevelId = $params->fromQuery('app_eik_level', 0);
        if ($appEikLevelId > 0)
        {
            $appEikLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->findBy(array(
                'id' => $appEikLevelId,
                'isDelete' => 0
            ));
            if (empty($appEikLevel))
                return false;
        }
        return true;
    }
    public function registerEiken($params, $orgId)
    {
        $form = new RegisterForm();
        $em = $this->getEntityManager();
        $config = $this->getServiceLocator()->get('config');

        $curYear = (int) date("Y");
        $listyear = array(''=>'');
        $fromYear = $curYear - 99;
        for ($i = $curYear; $i >= $fromYear; $i --) {
            $listyear[$i] = DateHelper::gengo($i);
        }
        $form->get("ddlYear")->setValueOptions($listyear);
        $listmonth = array('' => '');
        for ($i = 1; $i <= 12; $i++) {
            $listmonth[$i] = $i;
        }
        $form->get("ddlMonth")->setValueOptions($listmonth);
        $form->get("ddlSchoolCode")->setValueOptions($this->getSchoolCode());
        $form->get("ddlJobCode")->setValueOptions($config['Job_Code']);

        // Get city list
        $form->get("txtCity")->setValueOptions($this->getCityList());
        $classId = false;
        // If has data from session
        $applyEikenPersonalInfoSession = new SessionContainer('applyEikenPersonalInfoSession');
        $dayOfBirth = '';
        if ($applyEikenPersonalInfoSession->offsetExists('infoData'))
        {
            $classId = $this->bindDataToForm($form, $applyEikenPersonalInfoSession->infoData->postData);
        }
        else
        {
            $appEikInfoId = $params->fromRoute('id', 0);
            $em = $this->getEntityManager();
            $appEikInfo = $em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->find($appEikInfoId);
            if (!empty($appEikInfo))
            {
                // Bind data to form
                $classId = $this->bindStoredDataToForm($appEikInfo, $form);
            }
        }
        $dayOfBirth =  $form->get('ddlDay')->getValue();
        
        $jsMessages = $this->getMessages();
        return array(
            'form' => $form,
            'eikenLevelId' => $params->fromRoute('levelid', 0),
            'applyEikLevelId' => $params->fromQuery('app_eik_level', 0),
            'appEikInfoId' => isset($appEikInfoId)? $appEikInfoId : '',
            'jsMessages' => Json::encode($jsMessages),
            'classId' => $classId? : '',
            'dayOfBirth' => $dayOfBirth,

        );
    }

    public function referEiken($params, $orgId)
    {
        $em = $this->getEntityManager();
        $form = new ReferenceForm();
        $config = $this->getServiceLocator()->get('config');

        $curYear = (int) date("Y");
        $listyear = array(''=>'');
        $fromYear = $curYear - 99;
        for ($i = $curYear; $i >= $fromYear; $i --) {
            $listyear[$i] = DateHelper::gengo($i);
        }
        $form->get("ddlYear")->setValueOptions($listyear);
        $listmonth = array('' => '');
        for ($i = 1; $i <=12; $i ++) {
            $listmonth[$i] = $i;
        }
        $form->get("ddlMonth")->setValueOptions($listmonth);
        $form->get("ddlSchoolCode")->setValueOptions($this->getSchoolCode());
		$form->get("ddlJobCode")->setValueOptions($config['Job_Code']);
		// Get city list
		$form->get("txtCity")->setValueOptions($this->getCityList());
        // If has data from session
        $classId = false;
        $applyEikenPersonalInfoSession = new SessionContainer('applyEikenPersonalInfoSession');
        if ($applyEikenPersonalInfoSession->offsetExists('infoData'))
        {
            $data = $applyEikenPersonalInfoSession->infoData->postData;
            $classId = $this->bindDataToForm($form, $data);
        }
        $jsMessages = $this->getMessages();
        $dayOfBirth =  $form->get('ddlDay')->getValue();
        return array(
            'form' => $form,
            'eikenLevelId' => $params->fromRoute('levelid', 0),
            'applyEikLevelId' => $params->fromQuery('app_eik_level', 0),
            'appEikInfoId' => $params->fromRoute('id', 0),
            'jsMessages' => Json::encode($jsMessages),
            'hasData' => isset($data)? 1: 0,
            'classId' => $classId? : '',
            'dayOfBirth' => $dayOfBirth
        );
    }

    public function savePersonalInfo($orgId, $scheduleId, $currentPupil = array())
    {
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        if ($request->isPost())
        {
            $data = $request->getPost();
            // Store data into session
            $applyEikenPersonalInfoSession = new SessionContainer('applyEikenPersonalInfoSession');
            $this->setSessionData($applyEikenPersonalInfoSession, $data);

            $applyEikenPersonalInfoSession->infoData->setOrganization = $orgId;
            $rerturnData['action'] = 'register';
            $rerturnData['isCrossOrgEikenId'] = 0;
            $applyEikenPersonalInfoSession->action = 'register';
            // In case reference must get from post data
            
            if ($data['hidden-eiken-id'])
            {
                // F1GJIEM-2056 - Check one eikenId can be only used for one Org in a kai
                $refEikenId = trim($data['hidden-eiken-id']);
                $em = $this->getEntityManager();
                $isValidEikenId = $em->getRepository('Application\Entity\ApplyEikenLevel')->checkValidEikenIdForOrg(
                    $refEikenId,
                    $scheduleId,
                    $orgId
                );
                if (!$isValidEikenId)
                    return Json::encode(array(
                        'isCrossOrgEikenId' => 1,
                        'eikenId' => $refEikenId
                    ));
                $applyEikenPersonalInfoSession->infoData->setEikenId = $refEikenId;
                $applyEikenPersonalInfoSession->infoData->setEikenPassword = trim($data['hidden-eiken-pass']);
                $rerturnData['action'] = 'reference';
                $applyEikenPersonalInfoSession->action = 'reference';
                // Set password
            }
            // Incase register must call and get from api
            // If eikenId and password do not exist in session ==> get new
            elseif(empty($applyEikenPersonalInfoSession->infoData->setEikenId)
                || $applyEikenPersonalInfoSession->infoData->setEikenId == '00'
                || $applyEikenPersonalInfoSession->infoData->setEikenId == '01')
            {
                // Call API here
                $newEikenId = $this->getNewEikenIdApi ($data, $currentPupil);
                $applyEikenPersonalInfoSession->infoData->setEikenId = $newEikenId;
                $applyEikenPersonalInfoSession->infoData->setEikenPassword = $data['txtEikenPassword'];
            }

            $rerturnData['eikenId'] = $applyEikenPersonalInfoSession->infoData->setEikenId;
            // Check if has valid EikenId ==> store to DB
            if (!empty($applyEikenPersonalInfoSession->infoData->setEikenId)
                && $applyEikenPersonalInfoSession->infoData->setEikenId != '00'
                && $applyEikenPersonalInfoSession->infoData->setEikenId != '01')
            {
                // Save personal info to DB
                $storePersonInfoResult = $this->storeApplyPersonalInfo($applyEikenPersonalInfoSession, $scheduleId);
                $applyEikenPersonalInfoSession->infoData->id = $storePersonInfoResult['infoId'];

                // Validate rules: One EikenId can apply with 2 continuos Kyu
                $em = $this->getEntityManager();
                $isValidEikenLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->checkValidEikenLevel(
                    $applyEikenPersonalInfoSession->infoData->setEikenId,
                    $applyEikenPersonalInfoSession->eikenLevelId,
                    $applyEikenPersonalInfoSession->applyEikenLevelId,
                    $scheduleId
                );
                // Add new record to ApplyEikenLevel
                // Change the logic enable/disable of 2 buttons: check/get EikenId
                if ($isValidEikenLevel)
                {
                    $applyEikenPersonalInfoSession->applyEikenLevelId = $this->updateApplyEikenLevel(
                        $applyEikenPersonalInfoSession->infoData->id,
                        $scheduleId,
                        $applyEikenPersonalInfoSession->eikenLevelId,
                        $applyEikenPersonalInfoSession->applyEikenLevelId, 
                        $storePersonInfoResult['originalEikenId']
                    );
                }
                $rerturnData['isValidEikenLevel'] = $isValidEikenLevel? 1:0;
            }
            return Json::encode($rerturnData);
        }
    }
    protected function updateApplyEikenLevel ($appEikInfoId, $scheduleId, $eikenLevelId, $appEikLevelId, $originalEikenId)
    {
        $em = $this->getEntityManager();
        if ($appEikLevelId > 0)
        {
            $appEikLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->find($appEikLevelId);
            // Set old eikenId
            if ($appEikLevel->isIsSubmit() && $originalEikenId != '' && $appEikLevel->getOldEikenId() == '')
            {
                $appEikLevel->setOldEikenId($originalEikenId);
            }
        }
        else
        {
            $appEikLevel = new \Application\Entity\ApplyEikenLevel();
            // set isRegister = 0
            $appEikLevel->setIsRegister(0);
            $appEikLevel->setIsSateline(0);
        }
        $appEikLevel->setApplyEikenPersonalInfo($em->getReference('Application\Entity\ApplyEikenPersonalInfo', array(
            'id' => $appEikInfoId
        )));
        $appEikLevel->setEikenSchedule($em->getReference('Application\Entity\EikenSchedule', array(
            'id' => $scheduleId
        )));
        $appEikLevel->setEikenLevel($em->getReference('Application\Entity\EikenLevel', array(
            'id' => $eikenLevelId
        )));
        $em->persist($appEikLevel);
        $em->flush();
        $appEikLevelId = $appEikLevel->getId();
        $em->clear();
        return $appEikLevelId;
    }
    protected function setSessionData ($applyEikenPersonalInfoSession, $data)
    {
        if (!isset($applyEikenPersonalInfoSession->infoData))
            $applyEikenPersonalInfoSession->infoData = new \stdClass;
        $applyEikenPersonalInfoSession->infoData->postData = $data;
        $applyEikenPersonalInfoSession->eikenLevelId = (int) $data['eikenLevelId'];
        $applyEikenPersonalInfoSession->applyEikenLevelId = (int) $data['applyEikLevelId'];
    }
    protected function storeApplyPersonalInfo ($applyEikenPersonalInfoSession, $scheduleId)
    {
        $infoData = $applyEikenPersonalInfoSession->infoData;
        $em = $this->getEntityManager();
        $applyEikenPersonalInfo = $em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->findOneBy(array(
            'eikenId' => $infoData->setEikenId,
            'eikenScheduleId' => $scheduleId
        ));
        // Set data from post
        $data = $infoData->postData;
        if (!$applyEikenPersonalInfo)
        {
            /**
             * In case ref/get by click 2 buttons in each row
             * ==> there is one appEikInfoId
             * ==> must check if this record with 2 cases:
             *   1. It has already have EikenId 
             *      - check if it was used by another appEikLevelRecord ==> create new appEikInfoId
             *      - If not update the new get EikenId to this record
             *   2. It has not have EikenId yet ==> update the new get EikenId to this record
             */
            $appInfoId = (int) $data['appEikInfoId'];
            if ($appInfoId > 0)
            {
                $applyEikenPersonalInfo = $em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->find($appInfoId);
                if (!empty($applyEikenPersonalInfo))
                {
                    // Get original pupilId
                    if (!empty($applyEikenPersonalInfo->getPupil()))
                        $pupil = $applyEikenPersonalInfo->getPupil();
                    // Get original schoolYear
                    if (!empty($applyEikenPersonalInfo->getOrgSchoolYear()))
                        $orgSchoolYear = $applyEikenPersonalInfo->getOrgSchoolYear();
                    // Get original class
                    if (!empty($applyEikenPersonalInfo->getClass()))
                        $class = $applyEikenPersonalInfo->getClass();
                    
                    /**
                     * Store this EikenId for comparing and send to Ukesuke if needed
                     */
                    if (trim($applyEikenPersonalInfo->getEikenId()) != '' && $infoData->setEikenId != trim($applyEikenPersonalInfo->getEikenId()))
                        $originalEikenId = $applyEikenPersonalInfo->getEikenId();
                    // check if it was used by another appEikLevelRecord ==> create new appEikInfoId
                    $appEikLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->getApplyEikLevelByInfoId($appInfoId);
                    if (!empty($appEikLevel) && count($appEikLevel) > 1)
                    {
                        $applyEikenPersonalInfo = new \Application\Entity\ApplyEikenPersonalInfo();
                        $applyEikenPersonalInfo->setIsSateline('0');
                        
                        // set Original information
                        if (isset($pupil))
                            $applyEikenPersonalInfo->setPupil($pupil);
                        if (isset($orgSchoolYear))
                            $applyEikenPersonalInfo->setOrgSchoolYear($orgSchoolYear);
                        if (isset($class))
                            $applyEikenPersonalInfo->setClass($class);
                    }
                }
            }
            else 
            {
                $applyEikenPersonalInfo = new \Application\Entity\ApplyEikenPersonalInfo();
                $applyEikenPersonalInfo->setIsSateline('0');
            }
        }
        else 
        {
            $currentInfoId = (int) $data['appEikInfoId'];
            if ($currentInfoId > 0)
            {
                $currentInfo = $em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->find($currentInfoId);
                if (!empty($currentInfo))
                {
                    /**
                     * Store this EikenId for comparing and send to Ukesuke if needed
                     */
                    if (trim($currentInfo->getEikenId()) != '' && $infoData->setEikenId != trim($currentInfo->getEikenId()))
                        $originalEikenId = $currentInfo->getEikenId();
                }
            }
        }

        $applyEikenPersonalInfo->setFirstNameKanji($data['txtFirtNameKanji']);
        $applyEikenPersonalInfo->setLastNameKanji($data['txtLastNameKanji']);
        $applyEikenPersonalInfo->setFirstNameKana($data['txtFirtNameKana']);
        $applyEikenPersonalInfo->setLastNameKana($data['txtLastNameKana']);
        $birthday = new \DateTime(date($data['ddlYear'] . '/' . $data['ddlMonth'] . '/' . $data['ddlDay']));
        $applyEikenPersonalInfo->setBirthday($birthday);
        $applyEikenPersonalInfo->setGender($data['rdGender']);

        $applyEikenPersonalInfo->setPostalCode(trim($data['txtPostalCode1']) . '-'. trim($data['txtPostalCode2']));
        if ((int) $data['txtCity'] > 0 && (int) $data['txtCity'] <= 47)
            $applyEikenPersonalInfo->setCity($em->getReference('Application\Entity\City', array(
                'id' => (int) $data['txtCity']
            )));
        else             
            $applyEikenPersonalInfo->setCity(null);
        $applyEikenPersonalInfo->setDistrict($data['txtArea']);
        $applyEikenPersonalInfo->setBuildingName($data['txtBuilding']);
        $applyEikenPersonalInfo->setHouseNumber($data['txtAreaCode']);
        $applyEikenPersonalInfo->setTown($data['txtVillage']);
        $applyEikenPersonalInfo->setEmail($data['txtMailAddress']);
        $applyEikenPersonalInfo->setPhoneNo(trim($data['txtTelCode1']));
        $applyEikenPersonalInfo->setJobCode($data['ddlJobCode']);
        $applyEikenPersonalInfo->setEikenId($infoData->setEikenId);
        $applyEikenPersonalInfo->setEikenPassword(trim($infoData->setEikenPassword));
        if ((int) $data['ddlJobCode'] == 1)
        {
            $applyEikenPersonalInfo->setOrgSchoolYearName($data['ddlSchoolYear']);
            $applyEikenPersonalInfo->setClassName($data['ddlClass']);
            $applyEikenPersonalInfo->setSchoolCode($data['ddlSchoolCode']);
        }
        else
        {
            $applyEikenPersonalInfo->setOrgSchoolYearName(null);
            $applyEikenPersonalInfo->setClassName(null);
            $applyEikenPersonalInfo->setSchoolCode(null);
        }
        $applyEikenPersonalInfo->setEikenSchedule($em->getReference('Application\Entity\EikenSchedule', array(
            'id' => $scheduleId
        )));

        $applyEikenPersonalInfo->setOrganization($em->getReference('Application\Entity\Organization', array(
            'id' => $infoData->setOrganization
        )));
        $em->persist($applyEikenPersonalInfo);

        // Update to Pupil
        if ($applyEikenPersonalInfo->getPupilId())
        {
            $pupil = $em->getReference('Application\Entity\Pupil', array(
                'id' => $applyEikenPersonalInfo->getPupilId()
            ));
            $pupil->setEikenId(trim($infoData->setEikenId));
            $pupil->setEikenPassword($infoData->setEikenPassword);
            $em->persist($pupil);
        }

        $em->flush();
        $infoId = $applyEikenPersonalInfo->getId();
        $em->clear();
        return array(
            'infoId' => $infoId,
            'originalEikenId' => isset($originalEikenId)? $originalEikenId:''
        );
    }
    protected function bindDataToForm ($form, $data)
    {
        $classId = false;
        foreach ($data as $key => $value)
        {
            if ($form->has($key))
            {
                $form->get($key)->setValue($value);
            }
            if ($key == 'ddlClass')
                $classId = $value;
        }
        return $classId;
    }
    protected function bindStoredDataToForm ($appEikInfo, $form)
    {
        $em = $this->getEntityManager();
        $form->setHydrator(new DoctrineHydrator($em, 'Application\Entity\ApplyEikenPersonalInfo'));

        $form->get('txtFirtNameKanji')->setValue($appEikInfo->getFirstNameKanji());
        $form->get('txtLastNameKanji')->setValue($appEikInfo->getLastNameKanji());
        $form->get('ddlSchoolYear')->setValue($appEikInfo->getOrgSchoolYearName());
        $form->get('ddlClass')->setValue($appEikInfo->getClassName());
        $form->get('txtFirtNameKana')->setValue($appEikInfo->getFirstNameKana());
        $form->get('txtLastNameKana')->setValue($appEikInfo->getLastNameKana());

        $form->get('rdGender')->setValue($appEikInfo->getGender());
        $postalCode = explode('-', trim($appEikInfo->getPostalCode()));
        $form->get('txtPostalCode1')->setValue(isset($postalCode[0])? $postalCode[0]:'');
        $form->get('txtPostalCode2')->setValue(isset($postalCode[1])? $postalCode[1]:'');
        $form->get('txtTelCode1')->setValue(trim($appEikInfo->getPhoneNo()));

        $form->get('txtCity')->setValue(!empty($appEikInfo->getCity())? $appEikInfo->getCity()->getId():'');
        $form->get('txtEikenPassword')->setValue($appEikInfo->getEikenPassword());
        $form->get('txtArea')->setValue($appEikInfo->getDistrict());
        $form->get('txtBuilding')->setValue($appEikInfo->getBuildingName());
        $form->get('txtAreaCode')->setValue($appEikInfo->getHouseNumber());
        $form->get('txtVillage')->setValue($appEikInfo->getTown());
        $form->get('txtMailAddress')->setValue($appEikInfo->getEmail());
        $form->get('ddlSchoolCode')->setValue($appEikInfo->getSchoolCode());
        $form->get('ddlJobCode')->setValue($appEikInfo->getJobCode());

        $birthday = $appEikInfo->getBirthday();
        if (!empty($birthday))
        {
            $form->get('ddlYear')->setValue((int)$birthday->format('Y'));
            $form->get('ddlMonth')->setValue((int)$birthday->format('m'));
            $form->get('ddlDay')->setValue((int) $birthday->format('d'));
        }
        
        return $appEikInfo->getClassId();
    }
    /**
     * @param array $data
     * @author LangDD
     * @uses Get fake data, must be overwritten when available
     */
    protected function getNewEikenIdApi ($data, $currentPupil)
    {
        // Get config
        $config = $this->getServiceLocator()->get('Config')['eiken_config']['api'];

        // Preapare data
        $ukesukeData =$this->apiDataMapping($data, $currentPupil);
        $result = new \stdClass();
        $result->eikenid = '00';
        try {

            $result = \Dantai\Api\UkestukeClient::getInstance()->callEir1c03($config, $ukesukeData);
        }
        catch (Exception $e) {
            // Caught exception regarding to:
            // Connection, Argument, RuntimeException
            // @todo Log error here
        }

        return $result->eikenid;
    }

    /**
     * @param int $eikenId
     * @param string $eikenPass
     * @return \Zend\Json\mixed
     * @author LangDD
     * @uses Get fake data, must be overwritten when available
     */
    public function callToApi($eikenId,$eikenPass)
    {
        // Get config
        $config = $this->getServiceLocator()->get('Config')['eiken_config']['api'];
        $ukesukeData = array(
          'eikenid' => trim($eikenId),
           'eikenpass' => trim($eikenPass)
        );
        try {
            $result = \Dantai\Api\UkestukeClient::getInstance()->callEir1e02($config, $ukesukeData);
        }
        catch (Exception $e) {
            // Caught exception regarding to:
            // Connection, Argument, RuntimeException
            // Do record to systemlog
            // @todo Log error here
            $result->kekka = 10;
        }
        if ($result && $result->kekka == 10)
        {
            return Json::encode($this->apiResultMapping($result));
        }
        else
            return Json::encode((array) $result);
    }

    protected function apiDataMapping ($data,$currentPupil)
    {
        $em = $this->getEntityManager();
        $schoolArray = $this->getSchoolCode();
        $cityName = '';
        if ((int) $data['txtCity'] > 0)
        {            
            $city = $em->getRepository('Application\Entity\City')->find((int) $data['txtCity']);
            if (!empty($city))
                $cityName = trim($city->getCityName()); 
        }
        $grade = array();
        $class = array();
        $firstCharGrade = '';
        $firstCharClass = '';
        if(isset($currentPupil['pupilId'])){
            /*@var $objPupil \Application\Entity\Pupil*/
            $objPupil = $em->getRepository('Application\Entity\Pupil')->find((int) $currentPupil['pupilId']);
            if($objPupil){
                /*@var $objOrgSchoolYear \Application\Entity\OrgSchoolYear*/
                $objOrgSchoolYear = $em->getRepository('Application\Entity\OrgSchoolYear')->find((int) $objPupil->getOrgSchoolYearId());
                if($objOrgSchoolYear){
                    $grade = $objOrgSchoolYear->getDisplayName();
                }
                $class = $objPupil->getClass()->getClassName();
            }
            /*@var $dantaiService \Application\Service\DantaiService*/
            $dantaiService = new \Application\Service\DantaiService();
            $dantaiService->setServiceLocator($this->getServiceLocator());
            
            $firstCharGrade = $dantaiService->convertFullToHaf($dantaiService->cutCharacterWithNumber($grade, ApplicationConst::GRADE_TYPE));
            $firstCharClass = $dantaiService->convertFullToHaf($dantaiService->cutCharacterWithNumber($class, ApplicationConst::CLASS_TYPE));
            if(!preg_match('/^[A-Za-z0-9]*$/', $firstCharGrade)){
                    $firstCharGrade = '';
            }
            if(!preg_match('/^[A-Za-z0-9]*$/', $firstCharClass)){
                    $firstCharClass = '';
            }
        }

        return array(
            'sei_kanji' => $data['txtFirtNameKanji'],
            'mei_kanji' => $data['txtLastNameKanji'],
            'sei_kana' => $data['txtFirtNameKana'],
            'mei_kana' => $data['txtLastNameKana'],
            'gender' => $data['rdGender'],
            'birthday' => $data['ddlYear']. '/'. $data['ddlMonth']. '/'. $data['ddlDay'],
            'zip_code' => trim($data['txtPostalCode1']).trim($data['txtPostalCode2']),
            'prefecture' => $cityName,
            'city' => $data['txtArea'],
            'town' => $data['txtVillage'],
            'street' => $data['txtAreaCode'],
            'building' => $data['txtBuilding'],
            'phone_no' => trim($data['txtTelCode1']),
            'email' => $data['txtMailAddress'],
            'shokugyocd' => $data['ddlJobCode'],
            'gakkoucd' => $data['ddlJobCode'] == 1? $data['ddlSchoolCode']:'',
            'gakunen' => $data['ddlJobCode'] == 1? $data['ddlSchoolYear']:'',
            'gakkoumei' => $data['ddlJobCode'] == 1? $schoolArray[$data['ddlSchoolCode']]:'',
            'kumi' => $data['ddlJobCode'] == 1? $data['ddlClass']:'',
            'eikenpass' => trim($data['txtEikenPassword'])
        );
    }
    protected function apiResultMapping ($data)
    {
        $data = (array) $data;
        $birthDay = explode('/', $data['birthday']);
        $zipCode = explode('-', $data['zip_code']);
        $cityId = '';
        $em = $this->getEntityManager();
        $city = $em->getRepository('Application\Entity\City')->findOneBy(array(
            'cityName' => trim($data['prefecture'])
        ));
        if (!empty($city))
            $cityId = trim($city->getId());
        return array(
            'txtFirtNameKanji' => $data['sei_kanji'],
            'txtLastNameKanji' => $data['mei_kanji'],
            'txtFirtNameKana' => $data['sei_kana'],
            'txtLastNameKana' => $data['mei_kana'],
            'rdGender' => $data['gender'],
            'ddlYear' => $birthDay[0],
            'ddlMonth' => (int) $birthDay[1],
            'ddlDay' => (int) $birthDay[2],
            'txtPostalCode1' => substr(trim($data['zip_code']), 0 , 3)? :'',
            'txtPostalCode2' => substr(trim($data['zip_code']), 3)? :'',
            'txtCity' => $cityId,
            'txtArea' => $data['city'],
            'txtVillage' => $data['town'],
            'txtAreaCode' => $data['street'],
            'txtBuilding' => $data['building'],
            'txtTelCode1' => trim($data['phone_no']),
            'txtMailAddress' => $data['email'],
            'ddlJobCode' => $data['shokugyocd'],
            'ddlSchoolCode' => $data['gakkoucd'],
            'ddlSchoolYear' => $data['gakunen'],
            'ddlClass' => $data['kumi'],
        );
    }
    public function getClass($params, $orgId)
    {
        $em = $this->getEntityManager();
        $schoolyearId = $params()->fromQuery('schoolyear');
        $data = $em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYear($schoolyearId, $orgId);
        return $data;
    }
    protected function getMessages()
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');

        $jsMessages = array(
            'MSG16' => $translator->translate('MSG16'),
            'MSG1' => $translator->translate('MSG1'),
            'InvalidKyu' => $translator->translate('InvalidKyu'),
            'MSG15' => $translator->translate('MSG15'),
            'NoResultFound' => $translator->translate('NoResultFound'),
            'SystemError' => $translator->translate('SystemError'),
            'PassMisMatch' => $translator->translate('PassMisMatch'),
            'ApiSystemError' => $translator->translate('ApiSystemError'),
            'MSG52' => $translator->translate('MSG52'),
            'MSG51' => $translator->translate('MSG51'),
            'MSG34' => $translator->translate('MSG34'),
            'MSG60' => $translator->translate('MSG60'),
            'MSG28' => $translator->translate('MSG28'),
            'MSG70' => $translator->translate('MSG70'),
            'FullWidthFont' => $translator->translate('FullWidthFont'),
            'InvalidBirthday' => $translator->translate('InvalidBirthday'),
            'MSG_Kana_Error' => $translator->translate('MSG_Kana_Error'),
            'EikIdConfirmWithoutAreaCode' => $translator->translate('EikIdConfirmWithoutAreaCode'),
            'CrossOrgEikenId' => $translator->translate('CrossOrgEikenId')
        );
        return $jsMessages;
    }
    public function getSchoolCode()
    {
        return array(
            '6' => '小学',
            '5' => '中学',
            '4' => '高校',
            '3' => '高専',
            '2' => '短大',
            '1' => '大学',
            '8' => '大学院',
            '7' => '専修各種学校'
        );
    }
    /**
     * @return array
     * @author LangDD
     */
    public function getCityList ()
    {
        $em = $this->getEntityManager();
        return $cities = \Eiken\Helper\EikenCommon::generateSelectOptions($em->getRepository('Application\Entity\City')->getApplyEikCitiesList(true, false, true), 'getCityName');
    }
    
}