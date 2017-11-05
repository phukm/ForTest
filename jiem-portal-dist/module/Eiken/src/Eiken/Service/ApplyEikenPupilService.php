<?php

namespace Eiken\Service;

use Eiken\Service\ServiceInterface\ApplyEikenPupilServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Entity\ApplyEikenLevel;
use Eiken\Form\EikenPupil\CreateForm;
use Zend\Json\Json;
use Eiken\Helper\PaginationHelper;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Session\Container as SessionContainer;
use Zend\Filter\Encrypt;
use zend\filter\Decrypt;
use Dantai\PrivateSession;
use Zend\Http\Request;

class ApplyEikenPupilService implements ApplyEikenPupilServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    private $encryptFilter;
    private $decryptFilter;
    const KRYPT_KEY = "JIEMKRYPT";
    const CROSS_EDITING = 'cross-edit-apply-eiken';
    const CROSS_EDITING_MESG = 'cross-edit-apply-eiken-message';
    const CROSS_EDITING_DATA = 'cross-edit-apply-eiken-data';

    public function __construct()
    {
        $this->encryptFilter = new \Zend\Filter\Encrypt(array(
            'adapter' => 'BlockCipher',
            'key'     => self::KRYPT_KEY
        ));
        $this->decryptFilter = new \Zend\Filter\Decrypt(array(
            'adapter' => 'BlockCipher',
            'key'     => self::KRYPT_KEY
        ));
    }
    public function getPagedApplyEikenLevel($page, $orgId, $eikenLevel, $isHallMaain, $eikenScheduleId, $kaiNumber, $isPaymentOrg)
    {
        // clear session first

        $applyEikenPersonalInfoSession = new SessionContainer('applyEikenPersonalInfoSession');
        $applyEikenPersonalInfoSession->getManager()->getStorage()->clear('applyEikenPersonalInfoSession');
        $em = $this->getEntityManager();
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $apllyEikenPupil = $em->getRepository('Application\Entity\ApplyEikenLevel')->getPagedApplyEikenLevel($orgId, $eikenLevel, $isHallMaain, $eikenScheduleId, $limit, $offset);
        
        $jsMessages = $this->getMessages();
        /**
         * @var \Application\Service\ServiceInterface\DantaiServiceInterface
         */
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        //taivh cmt $crossMessages
//         $crossMessages = $dantaiService->getCrossEditingMessage('Application\Entity\ApplyEikenLevel');
//         $jsMessages['conflictWarning'] = $crossMessages['conflictWarning'];
//         $jsMessages['conflictType'] = $crossMessages['conflictType'];

        // Generate encrypt id
        if (!empty($apllyEikenPupil))
            foreach ($apllyEikenPupil as $pupil)
                $pupil->setEncryptId($this->encryptFilter->filter($pupil->getId()));
        return array(
            'eikenPupils' => $apllyEikenPupil,
            'jsMessages' => Json::encode($jsMessages),
            'eikenLevel' => $em->getRepository('Application\Entity\EikenLevel')->find($eikenLevel),
            'kaiNumber' => $kaiNumber,
            'page' => $page,
            'isPaymentOrg' => $isPaymentOrg
        );
    }

    public function editApplyEikenLevel($id, $orgId)
    {
        $form = new CreateForm();
        $em = $this->getEntityManager();
        $apllyEikenLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->getApplyEikLevelDetail ($id, $orgId);
        if (empty($apllyEikenLevel))
            return array(
                'status' => '00'
            );
        
        /**
         * @var \Application\Service\ServiceInterface\DantaiServiceInterface
         */
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $dantaiService->startCrossEditing($apllyEikenLevel);

        $crossMessages = $dantaiService->restoreCrossEditingForm($apllyEikenLevel, null, array(
            'feeFirstTime' => 'setFeeFirstTime',
            'firstPassedTime' => 'setFirstPassedTime',
            'areaNumber1' => 'setAreaNumber1',
            'areaPersonal1' => 'setAreaPersonal1',
            'cityId1' => 'setCityId1',
            'mainHallAddressId1' => 'setDistrictId1',
            'cityId2' => 'setCityId2',
            'mainHallAddressId2' => 'setDistrictId2'
        ));
        
        $jsMessages = $this->getMessages();
//         $jsMessages['conflictWarning'] = $crossMessages['conflictWarning'];
//         $jsMessages['conflictType'] = $crossMessages['conflictType'];

        $form->setAttribute('action', '/eiken/eikenpupil/update');
        $form->get('app_eik_id')->setValue($id);
        // Get data for filling into form
        $districtSorting = array(
            'code' => 'ASC'
        );
        $mainHallAddressesCondition = array(
            'isDelete' => 0,
        );
        if ($apllyEikenLevel->getCityId1())
            $mainHallAddressesCondition['cityId'] = $apllyEikenLevel->getCityId1();
        $conditionField = $this->getExamLocationCondition($apllyEikenLevel->getEikenLevelId());
        $cities1 = \Eiken\Helper\EikenCommon::generateSelectOptions($em->getRepository('Application\Entity\City')->getApplyEikCitiesList(false, $conditionField), 'getCityName');
        $mainHallAddressesCondition[$conditionField] = 1;
        unset($mainHallAddressesCondition[$conditionField]);
        $mainHallAddresses2 = \Eiken\Helper\EikenCommon::generateSelectOptions($em->getRepository('Application\Entity\District')->findBy($mainHallAddressesCondition, $districtSorting), 'getName');

        if ($apllyEikenLevel->getCityId2())
            $mainHallAddressesCondition['cityId'] = $apllyEikenLevel->getCityId2();
        else 
            unset($mainHallAddressesCondition['cityId']);
        $conditionField = $this->getExamLocationCondition($apllyEikenLevel->getEikenLevelId(), 0);
        $cities2 = \Eiken\Helper\EikenCommon::generateSelectOptions($em->getRepository('Application\Entity\City')->getApplyEikCitiesList(false, $conditionField), 'getCityName');
        $mainHallAddressesCondition[$conditionField] = 1;
        $mainHallAddresses3 = \Eiken\Helper\EikenCommon::generateSelectOptions($em->getRepository('Application\Entity\District')->findBy($mainHallAddressesCondition, $districtSorting), 'getName');

        $translator = $this->getServiceLocator()->get('MVCTranslator');
       
        $kaiOptions = $this->getKaiOptions($apllyEikenLevel->getApplyEikenPersonalInfo()->getEikenScheduleId());
        
         $form->get("firstPassedTime")->setValueOptions($kaiOptions);
        $form->get("cityId1")->setValueOptions($cities1);
        $form->get("cityId2")->setValueOptions($cities2);

        $form->get("mainHallAddressId1")->setValueOptions($mainHallAddresses2);
        $form->get("mainHallAddressId2")->setValueOptions($mainHallAddresses3);
        // Check if free at first time is enabled or disabled
        $hasInterviewExam = in_array($apllyEikenLevel->getEikenLevelId(), array(1, 2, 3, 4, 5)) ? 1 : 0;
        $form->setHydrator(new DoctrineHydrator($em, 'Application\Entity\ApplyEikenLevel'));
        
        $form->bind($apllyEikenLevel);
        if ($apllyEikenLevel->getFirstPassedTime())
        { 
          $key = array_search($apllyEikenLevel->getFirstPassedTime(), $kaiOptions);
          $form->get("firstPassedTime")->setValue($key);                 
        }     
        if ($apllyEikenLevel->getDistrictId1())
            $form->get("mainHallAddressId1")->setValue($apllyEikenLevel->getDistrictId1());
        if ($apllyEikenLevel->getDistrictId2())
            $form->get("mainHallAddressId2")->setValue($apllyEikenLevel->getDistrictId2());      
         
        if ($hasInterviewExam != 1)
        {
            $form->get("feeFirstTime")->setAttribute('disabled', 'disabled');
            // Set default falue = 1
            $form->get("feeFirstTime")->setValue(0);
        }
        
        
        return array(
            'form' => $form,
            'appEikLevelId' => $id,
            'hasInterviewExam' => $hasInterviewExam,
            'jsMessages' => Json::encode($jsMessages),
            'eikenLevel' => $apllyEikenLevel->getEikenLevel()? $apllyEikenLevel->getEikenLevel() : ''
        );
    }

    public function updateApplyEikenLevel(\Zend\Mvc\Controller\Plugin\FlashMessenger $flashMsgr = null)
    {
        $em = $this->getEntityManager();
        $app = $this->getServiceLocator()->get('Application');
        /**
         * 
         * @var Request
         */
        $request = $app->getRequest();
        $em = $this->getEntityManager();
        if ($request->isPost()) {
            $data = $request->getPost();
            $applyEikenLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->findOneBy(array(
                'id' => $data->app_eik_id
            ));
            
            /**
             * @var \Application\Service\ServiceInterface\DantaiServiceInterface
             */
            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
            $crossMessages = $dantaiService->checkCrossEditing($applyEikenLevel, null, $data);
            if ($crossMessages['conflictWarning']) {
                $return = array(
                    'module' => 'eiken',
                    'controller' => 'eikenorg',
                    'action' => 'index'
                );
                if ('edit' == $crossMessages['conflictType']) {
                    $return['controller'] = 'eikenpupil';
                    $return['action'] = 'edit';
                    $return['id'] =  $applyEikenLevel->getId();
                    return $return;
                }
                if($applyEikenLevel){
                    $return['controller'] = 'eikenpupil';
                    $return['action'] = 'index';
                    $return['id'] =  $applyEikenLevel->getEikenLevelId();
                    
                    return $return;
                }
                
                return $return;
            }
            
            $applyEikenLevel->setFeeFirstTime($data->feeFirstTime);
            
            if ($data->feeFirstTime == 1)
            {
                if ((int) $data->firstPassedTime)
                {
                    $applyEikenLevel->setFirstPassedTime($data->firstPassedTime);
                }
                else
                    $applyEikenLevel->setFirstPassedTime(null);
                $applyEikenLevel->setAreaNumber1($data->areaNumber1);
                $applyEikenLevel->setAreaPersonal1($data->areaPersonal1);
            }
            else
            {
                $applyEikenLevel->setFirstPassedTime(null);
                $applyEikenLevel->setAreaNumber1(null);
                $applyEikenLevel->setAreaPersonal1(null);
            }
            
            if ($data->cityId1)
                $applyEikenLevel->setAreaNumber2($em->getReference('Application\Entity\City', array(
                    'id' => $data->cityId1
                )));
            if ($data->mainHallAddressId1)
                $applyEikenLevel->setAreaPersonal2($em->getReference('Application\Entity\District', array(
                    'id' => $data->mainHallAddressId1
                )));
            if ($data->cityId2)
                $applyEikenLevel->setAreaNumber3($em->getReference('Application\Entity\City', array(
                    'id' => $data->cityId2
                )));
            else
                $applyEikenLevel->setAreaNumber3(null);
            if ($data->mainHallAddressId2)
                $applyEikenLevel->setAreaPersonal3($em->getReference('Application\Entity\District', array(
                    'id' => $data->mainHallAddressId2
                )));
            else
                $applyEikenLevel->setAreaPersonal3(null);

            $applyEikenLevel->setIsRegister(1);
            $em->flush();
            $em->clear();
        }
        return array(
            'module' => 'eiken',
            'controller' => 'eikenpupil',
            'action' => 'index',
            'id' => $applyEikenLevel->getEikenLevelId()
        );
    }

    public function createApplyEikenLevel($params, $eikenScheduleId)
    {
        // Get data from session
        $applyEikenPersonalInfoSession = new SessionContainer('applyEikenPersonalInfoSession');
        if (empty($applyEikenPersonalInfoSession->infoData))
        {
            return array('status' => 'redirect');
        }
        $em = $this->getEntityManager();
        $eikenLevel = $applyEikenPersonalInfoSession->eikenLevelId;

        $form = new CreateForm();
        // Get data for filling into form
        $conditionField = $this->getExamLocationCondition($eikenLevel);
        $cities1 = \Eiken\Helper\EikenCommon::generateSelectOptions($em->getRepository('Application\Entity\City')->getApplyEikCitiesList(false, $conditionField), 'getCityName');
        $form->get("cityId1")->setValueOptions($cities1);
        $conditionField = $this->getExamLocationCondition($eikenLevel, 0);
        $cities2 = \Eiken\Helper\EikenCommon::generateSelectOptions($em->getRepository('Application\Entity\City')->getApplyEikCitiesList(false, $conditionField), 'getCityName');
        $form->get("cityId2")->setValueOptions($cities2);
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $kaiOptions = $this->getKaiOptions($eikenScheduleId);
        $form->get("firstPassedTime")->setValueOptions($kaiOptions);

        // Check if free at first time is enabled or disabled
        $hasInterviewExam = in_array($eikenLevel, array(1, 2, 3, 4, 5)) ? 1 : 0;
        if ($hasInterviewExam != 1)
        {
            $form->get("feeFirstTime")->setAttribute('disabled', 'disabled');
        }
        // Set default falue = 1
        $form->get("feeFirstTime")->setValue(0);
        $jsMessages = $this->getMessages();
        return array(
            'form' => $form,
            'hasInterviewExam' => $hasInterviewExam,
            'jsMessages' => Json::encode($jsMessages),
            'eikenLevel' => $em->getRepository('Application\Entity\EikenLevel')->find($eikenLevel),
            // check valid kyu by condition: one eikenId can apply with 2 continuous kyus
            'validEikenLevel' => $em->getRepository('Application\Entity\ApplyEikenLevel')->checkValidEikenLevel(
                $applyEikenPersonalInfoSession->infoData->setEikenId,
                $eikenLevel,
                $applyEikenPersonalInfoSession->applyEikenLevelId,
                $eikenScheduleId
             )? 1:'',
            'appEikLevelId' => $applyEikenPersonalInfoSession->applyEikenLevelId,
            'previousAction' => $applyEikenPersonalInfoSession->action,
            'infoId' => $applyEikenPersonalInfoSession->infoData->id
        );
    }
    public function saveApplyEikenLevel($eikenScheduleId)
    {
        $em = $this->getEntityManager();
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        if ($request->isPost()) {
            // Process personal info first
            // Get data from session
            $applyEikenPersonalInfoSession = new SessionContainer('applyEikenPersonalInfoSession');
            $data = $request->getPost();
            if ((int) $applyEikenPersonalInfoSession->applyEikenLevelId)
            {
                $appEikLevel = $em->getReference('Application\Entity\ApplyEikenLevel', array(
                    'id' => (int) $data['appEikLevelId']
                ));
            }
            else
            {
                $appEikLevel = new \Application\Entity\ApplyEikenLevel();
                $appEikLevel->setIsSateline(0);
            }

            $appEikLevel->setFeeFirstTime($data->feeFirstTime ?  : 0);
            if ($data->feeFirstTime == 1)
            {
                if ((int) $data->firstPassedTime)
                {
                    $appEikLevel->setFirstPassedTime(trim($data->firstPassedTime));
                }
                else
                    $appEikLevel->setFirstPassedTime(null);
                $appEikLevel->setAreaNumber1($data->areaNumber1);
                $appEikLevel->setAreaPersonal1($data->areaPersonal1);
            }
            else
            {
                $appEikLevel->setFirstPassedTime(null);
                $appEikLevel->setAreaNumber1(null);
                $appEikLevel->setAreaPersonal1(null);
            }
            if ($data->cityId1)
                $appEikLevel->setAreaNumber2($em->getReference('Application\Entity\City', array(
                    'id' => $data->cityId1
                )));
            if ($data->mainHallAddressId1)
                $appEikLevel->setAreaPersonal2($em->getReference('Application\Entity\District', array(
                    'id' => $data->mainHallAddressId1
                )));
            if ($data->cityId2)
                $appEikLevel->setAreaNumber3($em->getReference('Application\Entity\City', array(
                    'id' => $data->cityId2
                )));
            else
                $appEikLevel->setAreaNumber3(null);
            if ($data->mainHallAddressId2)
                $appEikLevel->setAreaPersonal3($em->getReference('Application\Entity\District', array(
                    'id' => $data->mainHallAddressId2
                )));
            else
                $appEikLevel->setAreaPersonal3(null);
            $appEikLevel->setApplyEikenPersonalInfo($em->getReference('Application\Entity\ApplyEikenPersonalInfo', array(
                'id' => $applyEikenPersonalInfoSession->infoData->id
            )));
            $appEikLevel->setEikenSchedule($em->getReference('Application\Entity\EikenSchedule', array(
                'id' => $eikenScheduleId
            )));
            $appEikLevel->setEikenLevel($em->getReference('Application\Entity\EikenLevel', array(
                'id' => $applyEikenPersonalInfoSession->eikenLevelId
            )));
            // set isRegister = 1
            $appEikLevel->setIsRegister(1);
            $em->persist($appEikLevel);
            $em->flush();
            $em->clear();
            // clear session
            $applyEikenPersonalInfoSession = null;
        }

        return array(
            'module' => 'eiken',
            'controller' => 'eikenpupil',
            'action' => 'index',
            'id' => $data->eikenLevelId
        );
    }
    public function viewApplyEikenLevel($id, $isValidTime, $orgId)
    {
        $em = $this->getEntityManager();
        $apllyEikenLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->getApplyEikLevelDetail ($id, $orgId);
        if (empty($apllyEikenLevel))
            return array(
                'status' => '00'
            );
        $jsMessages = $this->getMessages();

        // set encrypted id
        $apllyEikenLevel->setEncryptId($this->encryptFilter->filter($apllyEikenLevel->getId()));
        
        $firstPassedTimeFree = explode('|', $apllyEikenLevel->getFirstPassedTime());
        if (isset($firstPassedTimeFree[0]) && isset($firstPassedTimeFree[1]))
        {
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            $apllyEikenLevel->setFirstPassedTime($firstPassedTimeFree[0]. $translator->translate('PassedKai1').$firstPassedTimeFree[1]. $translator->translate('PassedKai2'));
        }
        return array(
            'apllyEikenLevel' => $apllyEikenLevel,
            'hasInterviewExam' => in_array($apllyEikenLevel->getEikenLevel()->getId(), array(1, 2, 3, 4, 5)) ? 1 : 0,
            'jsMessages' => Json::encode($jsMessages),
            'isValidTime' => $isValidTime
        );
    }
    public function loadMainHall($cityId, $eikenLevelId, $isFirstTime = true)
    {
        $em = $this->getEntityManager();
        $condition = array(
            'isDelete' => 0
        );
        if ((int) $cityId)
        {
            $condition['cityId'] = $cityId;
            $conditionField = $this->getExamLocationCondition($eikenLevelId, $isFirstTime);
            $condition[$conditionField] = 1;
            $mainHallAddresses = \Eiken\Helper\EikenCommon::generateSelectOptions($em->getRepository('Application\Entity\District')->findBy($condition), 'getName');
        }
        else
            $mainHallAddresses = array();
        return $mainHallAddresses;
    }
    public function destroyApplyEikenLevel($strIds, $orgId)
    {
        if (! empty($strIds))
            $arrIds = explode('|', trim($strIds, '|'));

        $em = $this->getEntityManager();
        if (isset($arrIds)) {
            foreach ($arrIds as $id) {
                $id = $this->decryptFilter->filter($id);
                if ($id)
                {
                    $appEikPupil = $em->getRepository('Application\Entity\ApplyEikenLevel')->getApplyEikLevelDetail ($id, $orgId);
                    if (! empty($appEikPupil)) {
                        $appEikPupil->setIsDelete(1);
                        $em->flush();
                    }
                }
            }
        }
        return false;
    }
    private function getExamLocationCondition ($eikenLevelId, $isFirtTime = true)
    {
        switch ($eikenLevelId) {
            case 1:
                if ($isFirtTime)
                    $field = 'kyuOneFirstTime';
                else
                    $field = 'kyuOneSecondTime';
                break;
            case 2:
                if ($isFirtTime)
                    $field = 'kyuPreOneFirstTime';
                else
                    $field = 'kyuPreOneSecondTime';
                break;
            case 3:
                if ($isFirtTime)
                    $field = 'kyuTwoFirstTime';
                else
                    $field = 'kyuTwoSecondTime';
                break;
            case 4:
                if ($isFirtTime)
                    $field = 'kyuPreTwoFirstTime';
                else
                    $field = 'kyuPreTwoSecondTime';
                break;
            case 5:
                if ($isFirtTime)
                    $field = 'kyuThreeFirstTime';
                else
                    $field = 'kyuThreeSecondTime';
                break;
            case 6:
                $field = 'kyuFourFirstTime';
                break;
            case 7:
                $field = 'kyuFiveFirstTime';
                break;
            default:
                return false;
        }
        return $field;
    }
    /**
     * @param int $eikenScheduleId
     * @return multitype:string
     * @author LangDD
     * Get Kai values list for first time free 
     */
    public function getKaiOptions ($eikenScheduleId) 
    {
        $eikenSchedule = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->find($eikenScheduleId);
        $currentKai = $eikenSchedule->getKai();
        $currentYear = $eikenSchedule->getYear();
        $kaiOptions = array(
            '' => ''
        );
        $translator = $this->getServiceLocator()->get('MVCTranslator');

        $year = $currentYear;
        $j = 1;
        $flag = false;
        for ($i = 1; $i <= 3; $i++)
        {
            if (!$flag)
            {
                $kai = $currentKai - $i;
            }
            else 
            {
                $kai = 3 - $j;
                $j = $j + 1;
            }
            if ($kai <= 0)
            {
                $flag = true;
                $kai = 3;
                $year = $currentYear - 1;
            }
            $kaiOptions[$year.'|'.$kai] = $year. $translator->translate('PassedKai1').$kai. $translator->translate('PassedKai2');
        }
        return $kaiOptions;
    }
    protected function getMessages()
    {
        return array(
            'MSG16' => $this->translate('MSG16'),
            'MSG1' => $this->translate('MSG1'),
            'InvalidKyu' => $this->translate('InvalidKyu'),
            'MSG15' => $this->translate('MSG15'),
            'SystemError' => $this->translate('SystemError'),
            'MSG34' => $this->translate('MSG34'),
            'MSG70' => $this->translate('MSG70'),
            'HalfWidthFont' => $this->translate('HalfWidthFont')
        );
    }
    
    /**
     * 
     * @param string $key
     * @return string
     */
    protected function translate($key)
    {
        return $this->getServiceLocator()->get('MVCTranslator')->translate($key);
    }
    
    /**
     * @return array|object
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}