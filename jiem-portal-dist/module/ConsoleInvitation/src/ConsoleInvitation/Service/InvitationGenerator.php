<?php

namespace ConsoleInvitation\Service;

use Application\Entity\Repository\IssuingPaymentRepository;
use Application\Service\DantaiService;
use InvitationMnt\InvitationConst;
use Zend\Validator\Explode;

class InvitationGenerator  {

    const CODE_OUT_LAND = 9901;
    protected $htmlGenerator;
    protected $serviceManager;
    private $dantaiService;

    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager, HTMLGenerator $htmlGenerator) {
        $this->htmlGenerator = $htmlGenerator;
        $this->serviceManager = $serviceManager;
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->serviceManager->get('doctrine.entitymanager.orm_default');
    }

    /**
     *
     * @param int $id            
     * @return \Application\Entity\InvitationLetter
     */
    protected function getInvitationLetterById($id) {
        return $this->getEntityManager()
                        ->getRepository('Application\Entity\InvitationLetter')
                        ->find($id);
    }

    /**
     * @return DantaiServiceInterface
     */
    public function getDantaiService(){
        if(null !== $this->dantaiService){
            return $this->dantaiService;
        }
        $this->dantaiService = $this->serviceManager->get('Application\Service\DantaiServiceInterface');
        return $this->dantaiService;
    }

    /**
     * @param type $pupilId
     * @return \Application\Entity\EikenTestResult
     */
    public function getEikenTestResultFirst($pupilId) {
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->select('EikenTestResult')
                ->from('Application\Entity\EikenTestResult', 'EikenTestResult')
                ->where('EikenTestResult.pupilId = :pupilId')
                ->setParameter('pupilId', $pupilId)
                ->andWhere($dq->expr()
                        ->in('EikenTestResult.eikenLevelId', array(
                            6,
                            7
                )))
                ->andWhere('(EikenTestResult.primaryPassFailFlag IS NOT NULL OR EikenTestResult.secondPassFailFlag IS NOT NULL)')
                ->orderBy('EikenTestResult.certificationDate', 'DESC')
                ->setMaxResults(1);
        return $dq->getQuery()->getOneOrNullResult();
    }

    /**
     * @param type $pupilId
     * @return \Application\Entity\EikenTestResult
     */
    public function getEikenTestResultSecond($pupilId) {
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->select('EikenTestResult')
                ->from('Application\Entity\EikenTestResult', 'EikenTestResult')
                ->where('EikenTestResult.pupilId = :pupilId')
                ->setParameter('pupilId', $pupilId)
                ->andWhere($dq->expr()
                        ->in('EikenTestResult.eikenLevelId', array(
                            1,
                            2,
                            3,
                            4,
                            5
                )))
                ->andWhere('(EikenTestResult.primaryPassFailFlag IS NOT NULL OR EikenTestResult.secondPassFailFlag IS NOT NULL)')
                ->orderBy('EikenTestResult.secondCertificationDate', 'DESC')
                ->setMaxResults(1);
        return $dq->getQuery()->getOneOrNullResult();
    }

    /**
     * 
     * @param type $pupilId
     * @return \Application\Entity\IBATestResult
     */
    public function getIBATestResult($pupilId) {
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->select('IBATestResult')
                ->from('\Application\Entity\IBATestResult', 'IBATestResult')
                ->where('IBATestResult.pupilId = :pupilId')
                ->setParameter('pupilId', $pupilId)
                ->andWhere('IBATestResult.correctAnswerNumberTotal IS NOT NULL')
                ->andWhere('IBATestResult.correctAnswerPercentGrammar IS NOT NULL')
                ->andWhere('IBATestResult.correctAnswerPercentStructure IS NOT NULL')
                ->andWhere('IBATestResult.correctAnswerPercentReading IS NOT NULL')
                ->andWhere('IBATestResult.correctAnswerPercentListening IS NOT NULL')
                ->orderBy('IBATestResult.examDate', 'DESC')
                ->setMaxResults(1);
        $query = $dq->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    protected function getLastestDate() {
        $listDateTime = func_get_args();
        if (empty($listDateTime)) {
            return FALSE;
        }
        $max = $listDateTime[0];
        $maxIndex = 0;
        foreach ($listDateTime as $k => $datetime) {
            if (empty($datetime)) {
                continue;
            }
            if (empty($max)) {
                $max = $datetime;
                $maxIndex = $k;
                continue;
            }
            $compare = (int) $max->diff($datetime)->format('%r%a');
            if ($compare > 0) {
                $max = $datetime;
                $maxIndex = $k;
            }
        }
        return $maxIndex;
    }

    private $systemConfig = array();

    public function getSystemConfig() {
        if (!empty($this->systemConfig)) {
            return $this->systemConfig;
        }

        $systemConfig = $this->getEntityManager()
                ->getRepository('\Application\Entity\SystemConfig')
                ->findAll();
        foreach ($systemConfig as $config) {
            $this->systemConfig[$config->getConfigKey()] = $config->getConfigValue();
        }
        return $this->systemConfig;
    }

    public function renderEikenHtmlOne($invitationLetter, $priceLevels = array(), $masterData = null)
    {
        if (!$invitationLetter instanceof \Application\Entity\InvitationLetter) {
            $invitationLetter = $this->getInvitationLetterById($invitationLetter);
            /* @var $invitationLetter \Application\Entity\InvitationLetter */
        }
        $pupil = $invitationLetter->getPupil();
        if (empty($pupil)) {
            return;
        }
        $config = $this->htmlGenerator->getConfig();
        $IBATestResult = null;
        $eikenTestResult = null;
        $helperTestResult = \ConsoleInvitation\Helper\TestResultHelper::getInstance($this->getEntityManager(), $this->serviceManager);
        $testResult = $helperTestResult->getLastestTestResult($pupil->getId());
        if ($testResult instanceof \Application\Entity\EikenTestResult) {
            $eikenTestResult = $testResult;
        }
        if ($testResult instanceof \Application\Entity\IBATestResult) {
            $IBATestResult = $testResult;
        }
        $htmlPayment = $this->renderPaymentHtmlOne($invitationLetter, true, $priceLevels);
        $html = $this->htmlGenerator->render($config['moduleViewPath'], 'console-invitation/eiken-template.phtml', array(
            'isInland1'         => ($eikenTestResult && $eikenTestResult->getExamLocation() == self::CODE_OUT_LAND) ? 0 : 1,
            'isInland2'         => ($eikenTestResult && $eikenTestResult->getSecondExaminationAreas() == self::CODE_OUT_LAND) ? 0 : 1,
            'masterData'        => $masterData,
            'invitationLetter'  => $invitationLetter,
            'IBATestResult'     => $IBATestResult,
            'eikenTestResult'   => $eikenTestResult,
            'htmlPayment'       => $htmlPayment,
            'systemConfig'      => $this->getSystemConfig(),
            'configApplication' => $this->serviceManager->get('Config'),
            'dataEikenSchudule' => $invitationLetter->getEikenSchedule()
        ));
        $dir = $this->makeDir($config['htmlExportDir'], '/invitationLetter/' . $invitationLetter->getInvitationSetting()
                ->getEikenScheduleId() . '/' . $invitationLetter->getPupil()
                ->getClassId());
        $this->htmlGenerator->saveHtmltoFile($dir . '/' . $invitationLetter->getPupilId() . '.html', $html);
    }

    protected function makeDir($sourceDir, $dirMake) {
        $dirs = explode('/', $dirMake);
        $dirMade = rtrim($sourceDir, '/\\');
        foreach ($dirs as $dir) {
            if ('' == $dir) {
                continue;
            }
            $dirMade .= DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($dirMade)) {
                mkdir($dirMade);
            }
        }
        return $dirMade;
    }

    protected function getAuthenKey($pupilId) {
        if (array_key_exists($pupilId, $this->cacheAuthenKey)) {
            return $this->cacheAuthenKey[$pupilId];
        }
        return null;
    }

    public function renderPaymentHtmlOne($invitationLetter, $isGetHtml = false, $priceLevels = array()) {
        if (!$invitationLetter instanceof \Application\Entity\InvitationLetter) {
            $invitationLetter = $this->getInvitationLetterById($invitationLetter); /* @var $invitationLetter \Application\Entity\InvitationLetter */
        }

        if (empty($invitationLetter->getPupil())) {
            return;
        }

        $em = $this->getEntityManager();

        $config = $this->htmlGenerator->getConfig();
        $configApplication = $this->serviceManager->get('Config');
        /* @var $pupil \Application\Entity\Pupil */
        $pupil = $invitationLetter->getPupil();
        $organizationNo = $pupil->getOrganization()->getOrganizationNo();
        /* @var $invitationSetting \Application\Entity\InvitationSetting */
        $invitationSetting = $invitationLetter->getInvitationSetting();
        $listCombini = $invitationLetter->getCombini() != Null ? json_decode($invitationLetter->getCombini(), true) : array();

        $listEikenLevel = json_decode($invitationSetting->getListEikenLevel(), true);

        if ($priceLevels) {
            $priceEikenLevel = $priceLevels;
        } else {
            $priceEikenLevel = $em->getRepository('Application\Entity\EikenLevel')->getPricesByListEikenLevel($listEikenLevel);
        }
        $issuingPayment = array();
        $combini = array();
        $dir = $this->makeDir($config['htmlExportDir'], '/invitationLetter/' . $invitationLetter->getInvitationSetting()
                        ->getEikenScheduleId() . '/' . $invitationLetter->getPupil()
                        ->getClassId());
        $paymentPerson = $invitationSetting->getPersonalPayment() != Null ? json_decode($invitationSetting->getPersonalPayment(), true) : array();

        $hallType = $invitationSetting->getHallType();
        // if $isSemiStudentDiscount = 1 then price is discount (price of standard hall).
        $isSemiStudentDiscount = $this->isSemiStudentDiscount($invitationSetting);
        $listTelNo = array();
        if ($invitationSetting->getPaymentType() == 1) {
            if ($invitationSetting->getOrganizationPayment() == 1) {
                $template = 'console-invitation/payment-organization-template.phtml';
            } else {
                $template = 'console-invitation/payment-pager-template.phtml';
            }
        } else {
            if (in_array(1, $paymentPerson)) {
                // get Receipt No, telNo from entity IssuingPayment
                $hallType = $isSemiStudentDiscount ? 0 : $hallType;
                $priceLevels = $priceEikenLevel[$hallType];
                $issuingPayment = $em->getRepository('Application\Entity\IssuingPayment')->getDataByPupilAndEikenSchedule($pupil->getId(), $invitationLetter->getEikenScheduleId(), $priceLevels);
                $combini = $em->getRepository('\Application\Entity\Combini')->getCombinisByIds($listCombini);
                $listTelNo = $this->getListTelNo($issuingPayment);
            }
            $template = 'console-invitation/payment-personal-template.phtml';
        }
       
        $html = $this->htmlGenerator->render($config['moduleViewPath'], $template, array(
            'invitationLetter' => $invitationLetter,
            'configApplication' => $configApplication,
            'pupil' => $pupil,
            'organizationNo' => $organizationNo,
            'invitationSetting' => $invitationSetting,
            'priceEikenLevel' => $priceEikenLevel,
            'issuingPayment' => $issuingPayment,
            'combini' => $combini,
            'authenKey' => $this->getAuthenKey($invitationLetter->getPupilId()),
            'listTelNo' => $listTelNo,
            'semiMainVenue' => $isSemiStudentDiscount,
            'dataEikenSchudule' => $invitationLetter->getEikenSchedule()
        ));
        if ($isGetHtml) {
            return $html;
        }
        $this->htmlGenerator->saveHtmltoFile($dir . '/' . $pupil->getId() . '.html', $html);
    }

    public function getListTelNo($listIssuingPayment)
    {
        $listTelNo = array();
        foreach($listIssuingPayment as $issuingPayment){
            if(!in_array($issuingPayment['telNo'],$listTelNo)){
                array_push($listTelNo, $issuingPayment['telNo']);
            }
        }
        $data = array(0 => end($listTelNo));
        return $data;
    }

    /**
     * Function check Does Student get discount price for semi Org
     * @param $inv : InvitationSetting Object.
     * @return int
     * 1: apply discount
     * 0: not apply discount
     */
    public function isSemiStudentDiscount($inv){
        $semiMainVenue = $this->getDantaiService()->getSemiMainVenueOrigin($inv->getOrganizationId(), $inv->getEikenScheduleId());
        return $semiMainVenue && $inv->getBeneficiary() == DantaiService::BENEFICIARY_IS_STUDENT ? 1 : 0;
    }

    public function renderEnaviHtmlFile($invitationLetter) {
        if (!$invitationLetter instanceof \Application\Entity\InvitationLetter) {
            $invitationLetter = $this->getInvitationLetterById($invitationLetter); /* @var $invitationLetter \Application\Entity\InvitationLetter */
        }
        $config = $this->htmlGenerator->getConfig();
        $html = $this->htmlGenerator->render($config['moduleViewPath'], 'console-invitation/einavi-template.phtml', array(
            'invitationLetter' => $invitationLetter,
            'authenKey' => $this->getAuthenKey($invitationLetter->getPupilId())
        ));
        $dir = $this->makeDir($config['htmlExportDir'], '/invitationLetter/' . $invitationLetter->getInvitationSetting()
                        ->getEikenScheduleId() . '/' . $invitationLetter->getPupil()
                        ->getClassId());

        $this->htmlGenerator->saveHtmltoFile($dir . '/' . $invitationLetter->getPupilId() . '.html', $html);
    }

    public function renderInvitationLetterOfClass($classId, $orgId, $scheduleId = null, $priceLevels = array()) {
        echo date('Y-m-d H:i:s.u e'), ' Start render class ', $classId, ' of Org ', $orgId, 'with schedule ', $scheduleId, PHP_EOL;
        $invitationSetting = $this->getInvitationSetting($orgId, $scheduleId);

        $createDataInvitation = new \ConsoleInvitation\Service\CreateDataInvitation($this->serviceManager);
        $createDataInvitation->generateInvatationLetter($classId, $invitationSetting);

        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->select('InvitationLetter')
                ->from('\Application\Entity\InvitationLetter', 'InvitationLetter')
                ->innerJoin('\Application\Entity\Pupil', 'pupil', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.id = InvitationLetter.pupilId AND pupil.isDelete = 0')
                ->where('pupil.classId = :classId')
                ->andWhere('InvitationLetter.invitationSettingId = :settingId')
                ->andWhere('InvitationLetter.isDelete = 0')
                ->setParameter('classId', $classId)
                ->setParameter('settingId', $invitationSetting->getId());
        $listInvitation = $dq->getQuery()->execute();

        if (empty($listInvitation)) {
            return;
        }
        $listEikenLevel = json_decode($invitationSetting->getListEikenLevel(), true);
        $newPrice = $this->getPriceByGrade($classId, 
                                            $orgId, 
                                            $invitationSetting->getEikenScheduleId(),
                                            $listEikenLevel);
        
        $priceLevels = $newPrice ? $newPrice : $priceLevels;
        
        $this->cacheAllAuthenKeyOfPupil($listInvitation, $invitationSetting->getEikenScheduleId());
        foreach ($listInvitation as $invitationLetter) {
            switch ($invitationSetting->getInvitationType()) {
                case 1:
                    $this->renderEikenHtmlOne($invitationLetter, $priceLevels, $this->getEikenResultMasterData());
                    break;
                case 2:
                    $this->renderSchoolHtmlFile($invitationLetter, $priceLevels);
                    break;
                case 3:
                    $this->renderEnaviHtmlFile($invitationLetter);
                    break;
                case 4:
                    $this->renderPaymentHtmlOne($invitationLetter, false, $priceLevels);
                    break;
            }
        }
        echo date('Y-m-d H:i:s.u e'), ' End render class ', $classId, ' of Org ', $orgId, 'with chedule ', $scheduleId, PHP_EOL;
    }

    protected $cacheAuthenKey = array();

    protected function cacheAllAuthenKeyOfPupil($result, $eikenScheduleId) {
        $pupilIds = array();
        foreach ($result as $invitationLetter) {
            $pupilIds[] = $invitationLetter->getPupilId();
        }
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->select('AuthenticationKey.pupilId, AuthenticationKey.authenKey')
                ->from('\Application\Entity\AuthenticationKey', 'AuthenticationKey')
                ->where('AuthenticationKey.isDelete = 0 '
                        . 'AND AuthenticationKey.eikenScheduleId = :eikenScheduleId')
                ->andWhere($dq->expr()->in('AuthenticationKey.pupilId', $pupilIds))
                ->setParameter('eikenScheduleId', $eikenScheduleId);
        $res = $dq->getQuery()->getArrayResult();
        foreach ($res as $row) {
            $this->cacheAuthenKey[$row['pupilId']] = $row['authenKey'];
        }
    }

    protected $invitationSetting;

    /**
     * @param int $orgId
     * @return \Application\Entity\InvitationSetting
     */
    public function getInvitationSetting($orgId, $scheduleId = null,$refresh = false) {
        if (!empty($this->invitationSetting) && !$refresh) {
            return $this->invitationSetting;
        }
        $em = $this->getEntityManager();
        if (null == $scheduleId) {
            $eikenSchedule = $em->getRepository('Application\Entity\EikenSchedule')
                    ->getAvailableEikenScheduleByDate(date('Y'), date('Y-m-d H:i:s'));
            if (empty($eikenSchedule)) {
                return null;
            }
            $scheduleId = $eikenSchedule['id'];
        }
        $qb = $em->createQueryBuilder();
        $qb->select('inviteSetting')
                ->from('\Application\Entity\InvitationSetting', 'inviteSetting')
                ->join(
                        '\Application\Entity\EikenSchedule', 'eikenSchedule', \Doctrine\ORM\Query\Expr\Join::WITH, 'inviteSetting.eikenSchedule=eikenSchedule.id'
                )
                ->where('inviteSetting.eikenSchedule = :eikenScheduleId')
                ->andWhere('inviteSetting.organizationId = :organizationId')
                ->setParameter('eikenScheduleId', $scheduleId)
                ->setParameter('organizationId', $orgId)
                ->setMaxResults(1);

        $this->invitationSetting = $qb->getQuery()->getOneOrNullResult();
        return $this->invitationSetting;
    }

    // author : PhucVV3
    // function : renderSchoolHtmlFile
    // param : id for invationLetter
    // Description : process render file html in template school_version
    /**
     * @param \Application\Entity\InvitationLetter $invitationLetter
     */
    public function renderSchoolHtmlFile($invitationLetter, $priceLevels = array()) {
        if (!$invitationLetter instanceof \Application\Entity\InvitationLetter) {
            $invitationLetter = $this->getInvitationLetterById($invitationLetter); /* @var $invitationLetter \Application\Entity\InvitationLetter */
        }
//        $eiken_schedule = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule');
//        $aryEikenSchudule = $eiken_schedule->getAvailableEikenScheduleByDate(date("Y"), date("Y-m-d"));
//        $invitationSetting = $this->getEntityManager()
//            ->getRepository('Application\Entity\InvitationSetting')
//            ->findOneBy(array(
//            'id' => $invitationLetter->getInvitationSetting(),
//            'eikenScheduleId' => $aryEikenSchudule['id']
//        ));
        $invitationSetting = $invitationLetter->getInvitationSetting();
//        $dataEikenSchudule = $this->getEntityManager()
//            ->getRepository('Application\Entity\EikenSchedule')
//            ->findOneBy(array(
//            'id' => $aryEikenSchudule['id']
//        ));
        $dataEikenSchudule = $invitationLetter->getEikenSchedule();
//        $examDate2 = $dataEikenSchudule->getRound2ExamDate()->format('D');
//        $examDate2 = date('D', strtotime($examDate2));
        $examDate2A = $this->changeDay($dataEikenSchudule->getRound2Day1ExamDate()->format('D'));
        $examDate2B = $this->changeDay($dataEikenSchudule->getRound2Day2ExamDate()->format('D'));
//        $recommendLevel = $this->getEntityManager()
//            ->getRepository('Application\Entity\RecommendLevel')
//            ->findOneBy(array(
//            'eikenScheduleId' => $invitationSetting->getEikenScheduleId(),
//            'pupilId' => $invitationLetter->getPupilId()
//        ));
        $recommendLevel = $invitationLetter->getRecommendLevel();
//        $eikenLevel = $this->getEntityManager()
//            ->getRepository('Application\Entity\EikenLevel')
//            ->findOneBy(array(
//            'id' => $recommendLevel->getEikenLevelId()
//        ));
        $eikenLevel = $recommendLevel ? $recommendLevel->getEikenLevel() : null;
//        $schoolYear = $this->getEntityManager()
//            ->getRepository('Application\Entity\EikenSchedule')
//            ->findOneBy(array(
//            'id' => $invitationLetter->getEikenScheduleId()
//        ));
        $schoolYear = $dataEikenSchudule;
//        $pupil = $this->getEntityManager()
//            ->getRepository('Application\Entity\Pupil')
//            ->findOneBy(array(
//            'id' => $invitationLetter->getPupilId()
//        ));
        $pupil = $invitationLetter->getPupil();
//        $classJ = $this->getEntityManager()
//            ->getRepository('Application\Entity\ClassJ')
//            ->findOneBy(array(
//            'id' => $pupil->getClassId()
//        ));
        $classJ = $pupil->getClass();

//        $tempDate = $invitationSetting->getDeadLine()->format('Y-m-d');
//        $day = date('D', strtotime($tempDate));
        // $examDate1 = explode(';', $invitationLetter->getExamDate1());
//        $day = $this->changeDay($day);
        $day = $this->changeDay($invitationSetting->getDeadLine()->format('D'));
//        $examDate1 = $this->changeDay(date('D', strtotime($invitationLetter->getExamDate1()
//            ->format('Y-m-d'))));
        $examDate1 = $this->changeDay($invitationLetter->getExamDate1()->format('D'));
        $examDate12 = '';
        if ($invitationLetter->getExamDate12() != Null) {
            $examDate12 = $this->changeDay($invitationLetter->getExamDate12()->format('D'));
        }
        $resultEikenorIba = $this->getResultEikenorIba($invitationLetter);
//        $message = $this->getEntityManager()
//                ->getRepository('Application\Entity\InvitationSetting')
//                ->outMessage($invitationLetter->getPupilId(),$invitationLetter->getEikenScheduleId());
        $config = $this->htmlGenerator->getConfig();
        $htmlPayment = $this->renderPaymentHtmlOne($invitationLetter, true, $priceLevels);
        $html = $this->htmlGenerator->render($config['moduleViewPath'], 'console-invitation/school-template.phtml', array(
            'invitationLetter' => $invitationLetter,
            'invitationSetting' => $invitationSetting,
            'eikenLevel' => $eikenLevel,
//            'message' => $message,
            'schoolYear' => $schoolYear,
            'classJ' => $classJ,
            'pupil' => $pupil,
            'day' => $day,
            'examDate1' => $examDate1,
            'examDate12' => $examDate12,
            'resultEikenorIba' => $resultEikenorIba,
            'dataEikenSchudule' => $dataEikenSchudule,
            'htmlPayment' => $htmlPayment,
            'examDate2A' => $examDate2A,
            'examDate2B' => $examDate2B
        ));
        $dir = $this->makeDir($config['htmlExportDir'], '/invitationLetter/' . $invitationLetter->getInvitationSetting()
                        ->getEikenScheduleId() . '/' . $invitationLetter->getPupil()
                        ->getClassId());

        $this->htmlGenerator->saveHtmltoFile($dir . '/' . $invitationLetter->getPupilId() . '.html', $html);
    }

    // get result eiken or iba
    /**
     * @param \Application\Entity\InvitationLetter $invitationLetter
     * @return type
     */
    public function getResultEikenorIba($invitationLetter) {
        //$this->em = $this->getEntityManager();
        $pupilId = $invitationLetter->getPupilId();
        $aryData = array();

        $testResultHelper = \ConsoleInvitation\Helper\TestResultHelper::getInstance($this->getEntityManager(), $this->serviceManager);
        $testResult = $testResultHelper->getLastestTestResult($pupilId);
        if ($testResult instanceof \Application\Entity\IBATestResult) {
            return array(
                'pass' => '',
                'level' => ($testResult != Null && $testResult->getEikenLevel() != Null) ? $testResult->getEikenLevel()->getLevelName() : Null
            );
        } else {
            if ($testResult === Null) {
                return array(
                    'pass' => '',
                    'level' => ''
                );
            }
            $eikenTestResult = $testResult;
        }

        $isPass = 0;
        /* @var $eikenTestResult \Application\Entity\EikenTestResult */
        if ($eikenTestResult->getEikenLevelId() >= 6) {
            $isPass = $eikenTestResult->getPrimaryPassFailFlag();
        } else {

            if (($eikenTestResult->getPrimaryPassFailFlag() == 1 && $eikenTestResult->getSecondPassFailFlag() == 1) || ($eikenTestResult->getOneExemptionFlag() == 1 && $eikenTestResult->getSecondPassFailFlag() == 1 && $eikenTestResult->getPrimaryPassFailFlag() != 1)) {
                $isPass = 1;
            }
        }
        return array(
            'pass' => $isPass ? '合格' : '不合格',
            'level' => $eikenTestResult->getEikenLevel()->getLevelName()
        );
    }

    // phucVV3
    // change day E to Japan
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
    
//    function get price by org school year
    public function getPriceByGrade($classId, $orgId ,$scheduleId,$listEikenLevel = null) {
        if(empty($classId) || empty($orgId) || empty($scheduleId)){
            return false;
        }
        
        $em = $this->getEntityManager();
        /* @var $objClassJ \Application\Entity\ClassJ */
        $objClassJ = $em->getRepository('Application\Entity\ClassJ')
                ->find($classId);
        
         /* @var $objEikenSchedule \Application\Entity\EikenSchedule */
        $objEikenSchedule = $em->getRepository('Application\Entity\EikenSchedule')
                ->find($scheduleId);
        
        if(empty($objClassJ) || empty($objEikenSchedule)){
            return false;
        }
        $param = array(
                'orgSchoolYearId' => $objClassJ->getOrgSchoolYearId(),
                'year' => $objEikenSchedule->getYear(),
                'kai' => $objEikenSchedule->getKai()
        ); 
        $orgNo = $objClassJ->getOrganization()->getOrganizationNo();
        $dantaiService  = $this->serviceManager->get('Application\Service\DantaiServiceInterface');

        return $dantaiService->getListPriceOfOrganization($orgNo, $listEikenLevel, $param);
    }

    // end
    public function getEikenResultMasterData($year = 2016, $kai = 1)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e.id, e.listening, e.reading, e.writing, e.speaking, e.cseBand1, e.cseBand2, e.eikenLevelId, CONCAT(e.eikenLevelId, e.isInland, e.dateOfWeek) AS ids')
            ->from('\Application\Entity\EikenResultMasterData', 'e')
            ->where('e.year = :year')
            ->andWhere('e.kai= :kai')
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai);
        $master = $qb->getQuery()->getArrayResult();

        return array_combine(array_column($master, 'ids'), $master);
    }
}
