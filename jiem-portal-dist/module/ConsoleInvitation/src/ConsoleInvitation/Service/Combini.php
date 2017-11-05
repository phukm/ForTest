<?php

namespace ConsoleInvitation\Service;
use Application\Entity\ApplyEikenLevel;
use Application\Service\DantaiService;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use phpseclib\Net\SFTP;

class Combini{
    
    const RECORD_PER_FILE = 1000;
    
    const TYPE_MAIL_EXPIRED_EXECUTE_COMBINI = 8;
    const TYPE_MAIL_EXPIRED_EXECUTE_COMBINI_ADMIN = 9;
    
    const DANTAI_ADMIN = 4;
    const SYSTEM_ADMIN = 1;


    private $serviceManager;
    private $dantaiService;
    
    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        $this->serviceManager = $serviceManager;
    }
    private $entityManager = null;
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager(){
        if(null !== $this->entityManager){
            return $this->entityManager;
        }
        $this->entityManager = $this->serviceManager->get('doctrine.entitymanager.orm_default');
        return $this->entityManager;
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

    public function getConfig(){
        return $this->serviceManager->get('Config')['ConsoleInvitation'];
    }
    
    protected $logger;
    /**
     * @return \Zend\Log\Logger
     */
    public function getLogger(){
        if(null == $this->logger){
            $stream = @fopen(DATA_PATH . '/cronlog/sendAndReceiveCombini.log', 'a', false);
            if ($stream) {
                $writer = new \Zend\Log\Writer\Stream(DATA_PATH . '/cronlog/sendAndReceiveCombini.log');
            }else{
                $writer = new \Zend\Log\Writer\Stream('php://output');
            }
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);
        }
        return $this->logger;
    }

    protected function getPupilOfClass($classId){
        return $this->getEntityManager()
                ->getRepository('\Application\Entity\Pupil')
                ->findBy(array('classId' => $classId,'isDelete' => 0),
                        array(
                            'orgSchoolYearId' => 'ASC',
                            'classId' => 'ASC',
                            'number' => 'ASC',
                        ));
    }
    
    protected function getPupilOfOrg($orgId,$year){
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq ->select('Pupil.id, Pupil.classId, Pupil.organizationId, Pupil.firstNameKanji, Pupil.lastNameKanji, Pupil.orgSchoolYearId')
            ->from('\Application\Entity\Pupil', 'Pupil')
            ->innerJoin('\Application\Entity\ClassJ', 'ClassJ'
                        , \Doctrine\ORM\Query\Expr\Join::WITH,
                    'Pupil.classId = ClassJ.id AND ClassJ.isDelete = 0')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'OrgSchoolYear'
                    ,  \Doctrine\ORM\Query\Expr\Join::WITH,
                    'Pupil.orgSchoolYearId = OrgSchoolYear.id AND OrgSchoolYear.isDelete = 0') 
            ->where('Pupil.isDelete = 0 AND Pupil.organizationId = :orgId AND ClassJ.year = :year')
            ->setParameter('orgId', $orgId)
            ->setParameter('year', $year)
            ->orderBy('OrgSchoolYear.schoolYearId,Pupil.classId,Pupil.number', 'ASC');
        return $dq->getQuery()->getArrayResult();
    }
    
    protected function getPupilOfOrgById($pupilId,$orgId,$year){
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq ->select('Pupil.id, Pupil.classId, Pupil.organizationId, Pupil.firstNameKanji, Pupil.lastNameKanji, Pupil.orgSchoolYearId')
            ->from('\Application\Entity\Pupil', 'Pupil')
            ->innerJoin('\Application\Entity\ClassJ', 'ClassJ'
                        , \Doctrine\ORM\Query\Expr\Join::WITH,
                    'Pupil.classId = ClassJ.id AND ClassJ.isDelete = 0')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'OrgSchoolYear'
                    ,  \Doctrine\ORM\Query\Expr\Join::WITH,
                    'Pupil.orgSchoolYearId = OrgSchoolYear.id AND OrgSchoolYear.isDelete = 0') 
            ->where('Pupil.isDelete = 0 AND Pupil.organizationId = :orgId AND ClassJ.year = :year')
            ->andWhere('Pupil.id = :pupilId')
            ->setParameter('orgId', $orgId)
            ->setParameter('year', $year)
            ->setParameter('pupilId', $pupilId)
            ->orderBy('OrgSchoolYear.schoolYearId,Pupil.classId,Pupil.number', 'ASC');
        return $dq->getQuery()->getArrayResult();
    }
    
    private $invitationSetting;
    /**
     * @param \Application\Entity\Pupil $pupil
     * @return \Application\Entity\InvitationSetting
     */
    protected function getInvitationSetting(\Application\Entity\Pupil $pupil, $scheduleId = null){
        if(!empty($this->invitationSetting)){
            return $this->invitationSetting;
        }
        $em = $this->getEntityManager();
        if(NULL == $scheduleId ){
            $eikenSchedule = $em->getRepository('Application\Entity\EikenSchedule')
                            ->getAvailableEikenScheduleByDate(date('Y'), date('Y-m-d H:i:s'));
            $scheduleId = $eikenSchedule['id'];
        }
        
        $qb = $em->createQueryBuilder();
        $qb->select('inviteSetting')
            ->from('\Application\Entity\InvitationSetting', 'inviteSetting')
            ->join(
                    '\Application\Entity\EikenSchedule', 'eikenSchedule', 
                    \Doctrine\ORM\Query\Expr\Join::WITH, 
                    'inviteSetting.eikenSchedule=eikenSchedule.id'
                    )
            ->where('inviteSetting.eikenSchedule = :eikenScheduleId')
            ->andWhere('inviteSetting.organizationId = :organizationId')
            ->setParameter('eikenScheduleId', $scheduleId)
            ->setParameter('organizationId', $pupil->getClass()->getOrganization()->getId())
            ->setMaxResults(1);
        
        $this->invitationSetting = $qb->getQuery()->getOneOrNullResult();
        return $this->invitationSetting;
    }
    
    /**
     * 
     * @param array $tmpData = Array(
            [id] => int
            [orgId] => int
            [classId] => int
            [pupilId] => int
            [pupilName] => string
            [deadline] => DateTime
            [eikenScheduleId] => int
            [eikenLevelId] => int
            [telNo] => string
            [productName] => string
            [price] => int
            [orderId] => string
            [receiptNo] => string
            [orderResultCode] => string
            [orderResultInfo] => string
            [isCompleted] => boolean
        )
     * @return \Application\Entity\PaymentInfo
     */
    
    private function makePaymentInfo($tmpData){
        $em = $this->getEntityManager();
        if(!empty($tmpData['paymentId'])){
            return $em->getReference('\Application\Entity\PaymentInfo', $tmpData['paymentId']);
        }
        $paymentInfo = new \Application\Entity\PaymentInfo();
        
        $paymentInfo->setPupil($em->getReference('\Application\Entity\Pupil', $tmpData['pupilId']));
        $paymentInfo->setSiteCode($this->getConfig()['econtext_combini_site_code']);
        $paymentInfo->setMailAddress($this->getConfig()['econtext_combini_mail_address']);
        $paymentInfo->setName($tmpData['pupilName']);
        $paymentInfo->setDeadLine($tmpData['deadline']);
        $paymentInfo->setEikenSchedule($em->getReference('\Application\Entity\EikenSchedule',$tmpData['eikenScheduleId']));
        $em->persist($paymentInfo);
        return $paymentInfo;
    }
    
    private $recordIndex = 0;
    protected function generateTelNo( \Application\Entity\InvitationSetting $invitationSetting,$numOfKyu){
        $rowPerFile = 1000 - (1000%$numOfKyu);
        $index = floor( $this->recordIndex/$rowPerFile) + ($this->getLastestTelNoIndex($invitationSetting->getOrganization()->getTelNo()) + 1);
        $subFix = $index % 100000;
        $this->recordIndex ++;
        $orgId = strlen((string)$invitationSetting->getOrganization()->getId()) > 6 
                ? substr((string)$invitationSetting->getOrganization()->getId(), 0, 6) 
                :(string)$invitationSetting->getOrganization()->getId();
        $firstSixNumber = str_pad($orgId, 6, "0", STR_PAD_LEFT);
        $lastFiveNumber = str_pad($subFix, 5, "0", STR_PAD_LEFT);
        return $firstSixNumber. $lastFiveNumber;
    }

    public function generateTelNoForTest($invitationSetting, $numOfKyu)
    {
        return $this->generateTelNo($invitationSetting, $numOfKyu);
    }

    private  $eikenLevels = array();
    protected function getEikenLevel(){
        if(!empty($this->eikenLevels)){
            return $this->eikenLevels;
        }
        $list = $this->getEntityManager()->getRepository('\Application\Entity\EikenLevel')->findAll();
        foreach($list as $eikenLevel){
            $this->eikenLevels[$eikenLevel->getId()] = $eikenLevel;
        }
        return $this->eikenLevels;
    }

    private $prefixOrderId = null;
    private $indexOrderId = 0;
    protected function getOrderId(\Application\Entity\InvitationSetting $invitationSetting){
        if(null === $this->prefixOrderId){
            $this->prefixOrderId = date('Ymd').$invitationSetting->getOrganization()->getOrganizationNo();
        }
        $this->indexOrderId = $this->getNewOrderIndex($this->prefixOrderId);
        return $this->prefixOrderId . sprintf('%06d', $this->indexOrderId );
    }
    
    private $lastTelNoIndex = null;
    protected function getLastestTelNoIndex($telNoPrefix){
        if(null === $this->lastTelNoIndex){
            $orderIndex = $this->getPaymentOrderIndex($telNoPrefix);
            if(empty($orderIndex)){
                $orderIndex = new \Application\Entity\PaymentOrderIndex();
                $orderIndex->setPrefix($telNoPrefix);
                $orderIndex->setIndex(0);
                $orderIndex->setLastTelNoIndex(-1);
                $this->getEntityManager()->persist($orderIndex);
                $this->getEntityManager()->flush();
                $this->lastTelNoIndex = -1;
                return -1;
            }
            $this->getEntityManager()->refresh($orderIndex);
            $this->lastTelNoIndex = $orderIndex->getLastTelNoIndex();
            return $orderIndex->getLastTelNoIndex();
        }
        return $this->lastTelNoIndex;
    }

    protected function getNewOrderIndex($orderPrefix) {
        if($this->indexOrderId > 0){
            return $this->indexOrderId + 1;
        }
        
        $orderIndex = $this->getPaymentOrderIndex($orderPrefix);
        if(empty($orderIndex)){
            $orderIndex = new \Application\Entity\PaymentOrderIndex();
            $orderIndex->setPrefix($orderPrefix);
            $orderIndex->setIndex(1);
            $this->getEntityManager()->persist($orderIndex);
            $this->getEntityManager()->flush();
            return 1;
        }
        $this->getEntityManager()->refresh($orderIndex);
        $orderIndex->addIndex();
        $this->getEntityManager()->persist($orderIndex);
        $this->getEntityManager()->flush();
        return $orderIndex->getIndex();
    }
    
    private function getPaymentOrderIndex($orderPrefix){
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->select('PaymentOrderIndex')
            ->from('\Application\Entity\PaymentOrderIndex','PaymentOrderIndex')
            ->where('PaymentOrderIndex.prefix = :prefix')
            ->setParameter('prefix', $orderPrefix);
        return $dq->getQuery()->getOneOrNullResult();
    }
    
    /**
     * 
     * @param type $filePath
     * @param type $filePrefix
     * @param \Application\Entity\InvitationSetting $invitationSetting
     * @return array
     */
    protected function makeCsvFileFromStoredToFile($filePath,$filePrefix,$invitationSetting, $listKyu = null){
        $countKyu = null;
        if(empty($listKyu)){
            $countKyu = count(\Zend\Json\Decoder::decode($invitationSetting->getListEikenLevel(), \Zend\Json\Json::TYPE_ARRAY));
        }else{
            $countKyu = count($listKyu);
        }
        $rowPerFile = self::RECORD_PER_FILE - (self::RECORD_PER_FILE % $countKyu);
        $listFile = array();
        $countCsvRow = count($this->dataCsv);
        $numOfFileSend = 0;
        $index = 0;
        while ($index < $countCsvRow){
            $csvData = array_slice($this->dataCsv, $index, $rowPerFile);
            $fileName = $filePrefix.'-'. floor($index/$rowPerFile).'.dat';
            $index += $rowPerFile;
            $header = $this->getHeaderCsv(count($csvData));
            array_unshift($csvData, $header);
            $strCsv = \Dantai\Utility\CsvHelper::arrayToStrCsv($csvData,"\t");
            $strCsv = \Dantai\Utility\CharsetConverter::utf8ToShiftJis($strCsv);
            $strCsv = str_replace(\Dantai\Utility\CharsetConverter::utf8ToShiftJis('?')
                             ,\Dantai\Utility\CharsetConverter::utf8ToShiftJis('？')
                             , $strCsv);
            file_put_contents($filePath.$fileName, $strCsv);
            $listFile[$fileName] = $filePath.$fileName;
            $numOfFileSend++;
        }
        $this->updateLastOrderIndex();
        $this->updateLastTelNoIndex($numOfFileSend,$invitationSetting->getOrganization()->getTelNo());
        return $listFile;
    }
    
    private function updateLastTelNoIndex($numOfFileSend,$prefixTelNo){
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq ->update('\Application\Entity\PaymentOrderIndex', 'PaymentOrderIndex')
            ->set('PaymentOrderIndex.lastTelNoIndex', 'PaymentOrderIndex.lastTelNoIndex +'.$numOfFileSend)
            ->where('PaymentOrderIndex.prefix = :prefix')
            ->setParameter('prefix', $prefixTelNo);
        return $dq->getQuery()->execute();
    }
    private function updateLastOrderIndex(){
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq ->update('\Application\Entity\PaymentOrderIndex', 'PaymentOrderIndex')
            ->set('PaymentOrderIndex.index', $dq->expr()->literal($this->indexOrderId))
            ->where('PaymentOrderIndex.prefix = :prefix')
            ->setParameter('prefix', $this->prefixOrderId);
        return $dq->getQuery()->execute();
    }

    protected function getHeaderCsv($count){
        return array(
            date('Ymd'),
            $count,
            $this->getConfig()['econtext_combini_site_code'],
            'I'
        );
    }

    protected $dataCsv = array();
    public function storeDataCsvByPaymentTemp(\Application\Entity\PaymentCombiniTemp $paymentConbiniTemp){
        $pupilName = $paymentConbiniTemp->getPupilName();
        if (mb_strlen($pupilName, 'utf-8') > 10) {
            $pupilName = mb_substr($pupilName, 0, 10, 'utf-8');
        }
        $this->dataCsv[] = array(
            $this->getConfig()['econtext_combini_site_code'],
            $paymentConbiniTemp->getOrderId(),
            $paymentConbiniTemp->getTelNo(),
            $pupilName,
            $this->getConfig()['econtext_combini_mail_address'],
            $paymentConbiniTemp->getProductName(),
            $paymentConbiniTemp->getPrice(),
            $paymentConbiniTemp->getDeadline()->format('Y/m/d')
        );
    }

    protected function sendFileToEcontextFtpServer($csvListFile){
        $configFtp = $this->getConfig()['econtext_combini_ftp_config'];
        $sftp = new SFTP($configFtp['host'], $configFtp['port'], $configFtp['timeout']);
        
        if( !$sftp->login($configFtp['username'],$configFtp['password'])){
            return false;
        }
        
        foreach($csvListFile as $fileName => $filePath){
            $isSuccess = $sftp->put($sftp->pwd().'req/'.$fileName , $filePath, SFTP::SOURCE_LOCAL_FILE);
            $this->getLogger()->info('File send: '.$filePath, $sftp->getErrors());
            if($isSuccess !== false){
                // TODO: remove file after process
            }
        }
        error_log(implode(PHP_EOL,$sftp->getErrors()));
        return $isSuccess;
    }

    public function sendOrgCombiniToEcontext($orgId,$scheduleId, $priceLevels = array(), $pupilId = null, $listKyu = null){
        $em = $this->getEntityManager();
        
        $orgNo = $em->getRepository('\Application\Entity\Organization')->find($orgId)->getOrganizationNo();
        
        /* @var $invitationSetting \Application\Entity\InvitationSetting */
        $invitationSetting = $em->getRepository('\Application\Entity\InvitationSetting')->findOneBy(array(
            'organizationId' => $orgId,
            'eikenScheduleId' => $scheduleId,
            'isDelete' => 0
        ));
        
        // if there are pupilId, only generate for this pupil
        // else generate file for all pupil in org.
        $listPupils = null;
        if(empty($pupilId)){
            $listPupils = $this->getPupilOfOrg($orgId,$invitationSetting->getEikenSchedule()->getYear());   
            
        }else{
            $listPupils = $this->getPupilOfOrgById($pupilId, $orgId, $invitationSetting->getEikenSchedule()->getYear());
        }
        if(empty($listPupils) || empty($orgNo) || empty($invitationSetting)){
                return;
        }
        
        
        // clean junk data
        if(empty($pupilId)){
            $this->deleteTmpData($orgId, $scheduleId);
        }else{
            foreach($listKyu as $kyu){
                $this->deleteTmpDataByPupilId($pupilId, $scheduleId, $kyu);
            }
        }
        
        $filePath = $this->getConfig()['econtext_combini_file_path'] . DIRECTORY_SEPARATOR ;
        $filePrefix = $orgNo . date('Ymd');
        if(!empty($pupilId)){
            $filePrefix = $pupilId . date('YmdHis');
            $filePrefix = 'satellite-'.$filePrefix;
        }
        
        $this->getExistPaymentOfPupil($orgId, $scheduleId);
        
        $priceGrade = $this->getPriceMultiGrade($orgId, $scheduleId);
        // if there are pupilId, only generate for this pupil
        // else generate file for all pupil in org.
        $isSatellite = empty($pupilId) ? false : true;
        foreach ($listPupils as $pupil) {
            $this->savePaymentCombiniTemp($pupil, $invitationSetting, $priceLevels, $listKyu, $isSatellite,$priceGrade);
            $em->flush();
            $em->clear('\Application\Entity\PaymentCombiniTemp');
        }

        if($this->hasPaymentGenerate){
            $listFile = $this->makeCsvFileFromStoredToFile($filePath,$filePrefix,$invitationSetting, $listKyu);
            $this->sendFileToEcontextFtpServer($listFile);
            $processLogManager = empty($pupilId) 
                                    ? ProcessLogManager::getInstance($orgId,$scheduleId,  $this->serviceManager)
                                    : ProcessSatelliteLogManager::getInstance($pupilId,$scheduleId,  $this->serviceManager);
            $processLogManager->getProcessLog()->setSendCombiniAt(new \DateTime('now'));
            $processLogManager->saveProcessLog();
        }elseif(!empty($pupilId)){
            $this->setActiveRunProcessSatelliteLog($pupilId,$scheduleId);
        }else{
            $this->setActiveRunProcessLog($orgId,$scheduleId);        
        }
        
    }
    
    protected function setActiveRunProcessLog($orgId,$scheduleId){
        /* @var $processLog \Application\Entity\ProcessLog */
        $processLog = $this->getEntityManager()->getRepository('\Application\Entity\ProcessLog')
                ->findOneBy(array(
                    'orgId' => $orgId,
                    'scheduleId' => $scheduleId
                ));
        if(empty($processLog)){
            return;
        }
        $processLog->setActive($processLog->getTotal());
        $this->getEntityManager()->persist($processLog);
        $this->getEntityManager()->flush();
    }
    
    protected function setActiveRunProcessSatelliteLog($pupilId,$scheduleId){
        /* @var $processLog \Application\Entity\ProcessLog */
        $processLog = $this->getEntityManager()->getRepository('\Application\Entity\ProcessLog')
                ->findOneBy(array(
                    'pupilId' => $pupilId,
                    'scheduleId' => $scheduleId
                ));
        if(empty($processLog)){
            return;
        }
        $processLog->setActive($processLog->getTotal());
        $this->getEntityManager()->persist($processLog);
        $this->getEntityManager()->flush();
    }

    protected function updateIgnorePaymentProcessLog($orgId,$scheduleId){
        /* @var $processLog \Application\Entity\ProcessLog */
        $processLog = $this->getEntityManager()->getRepository('\Application\Entity\ProcessLog')
                ->findOneBy(array(
                    'orgId' => $orgId,
                    'scheduleId' => $scheduleId
                ));
        if(empty($processLog)){
            return;
        }
        $processLog->setIgnorePayment($processLog->getIgnorePayment() + 1);
        $this->getEntityManager()->persist($processLog);
        $this->getEntityManager()->flush();
    }

    private $hasPaymentGenerate = false;
    /**
     * @param array $pupil
     * @param \Application\Entity\InvitationSetting $invitationSetting
     * @param array $priceLevels
     * @param null $listKyu
     * @param bool $isSatellite
     * @return bool
     */
    public function savePaymentCombiniTemp($pupil,$invitationSetting, $priceLevels=array(), $listKyu = null, $isSatellite = false , $priceGrade = null){
        $em = $this->getEntityManager();
        if(empty($listKyu)){
            $listKyu = \Zend\Json\Json::decode($invitationSetting->getListEikenLevel(),\Zend\Json\Json::TYPE_ARRAY);
        }
        $deadline = clone $invitationSetting->getEikenSchedule()->getCombiniDeadline();

        foreach ($listKyu as $eikenLevelId){
            $eikenLevel = $this->getEikenLevel()[$eikenLevelId];

            // Get price follow invitation setting.
            $hallType = $invitationSetting->getHallType();
            // if $isSemiStudentDiscount = 1 then price is discount (price of standard hall).
            $isSemiStudentDiscount = $this->isSemiStudentDiscount($invitationSetting);
            $hallType = $isSemiStudentDiscount ? 0 : $hallType;
            $price = isset($priceLevels[$hallType][$eikenLevelId]) ? $priceLevels[$hallType][$eikenLevelId]['price'] : 0;
            
            // update price if pupil is in Grade Discound
            if(!empty($priceGrade) && isset($priceGrade[$pupil['orgSchoolYearId'].'_'.$hallType])){
                $price = $priceGrade[$pupil['orgSchoolYearId'].'_'.$hallType][$eikenLevelId]['price'] ? $priceGrade[$pupil['orgSchoolYearId'].'_'.$hallType][$eikenLevelId]['price'] : 0;
            }
            
            // Get price of apply eiken level of request from satellite
            if ($isSatellite) {
                /** @var ApplyEikenLevel $applyEikenLevel */
                $applyEikenLevel = $em->getRepository('\Application\Entity\ApplyEikenLevel')
                    ->findOneBy(array(
                            'pupilId'         => $pupil['id'],
                            'eikenLevelId'    => $eikenLevelId,
                            'eikenScheduleId' => $invitationSetting->getEikenSchedule()->getId())
                    );
                $price = empty($applyEikenLevel) ? $price : $applyEikenLevel->getTuitionFee();
            }

            if ($isSatellite && empty($applyEikenLevel)) {
                continue;
            }

            // not send if issuingPayment is exist.
            if(!$isSatellite && (empty($eikenLevel) || $this->isExistPayment($pupil['id'], $eikenLevelId, $price))){
                continue;
            }
            $telNo = false;
            $paymentId = null;
            if(!empty($this->existPaymentOfPupil[$pupil['id']])){
                $info = reset(reset($this->existPaymentOfPupil[$pupil['id']]));
                $paymentId = $info['id'];
            }
            if($price > 0){
                $paymentConbiniTemp = new \Application\Entity\PaymentCombiniTemp();
                $paymentConbiniTemp->setClassId($pupil['classId']);
                $paymentConbiniTemp->setOrgId($pupil['organizationId']);
                $paymentConbiniTemp->setPupilId($pupil['id']);
                $paymentConbiniTemp->setPupilName($pupil['firstNameKanji'].$pupil['lastNameKanji']);

                $paymentConbiniTemp->setDeadline($deadline);
                $paymentConbiniTemp->setEikenScheduleId($invitationSetting->getEikenScheduleId());

                $paymentConbiniTemp->setEikenLevelId($eikenLevelId);
                $paymentConbiniTemp->setProductName('実用英語技能検定'.$eikenLevel->getLevelName());
                $paymentConbiniTemp->setPrice($price);
                $paymentConbiniTemp->setOrderId($this->getOrderId($invitationSetting));
                $paymentConbiniTemp->setTelNo($telNo ?: $this->generateTelNo($invitationSetting,count($listKyu)));
                $paymentConbiniTemp->setPaymentId($paymentId);

                $em->persist($paymentConbiniTemp);
                $this->storeDataCsvByPaymentTemp($paymentConbiniTemp);
                $this->hasPaymentGenerate = true;
            }
        }
        return $this->hasPaymentGenerate;
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

    protected function isExistPayment($pupilId,$eikenLevelId,$price) {
        if(!array_key_exists($pupilId, $this->existPaymentOfPupil)){
            return false;
        }else if(!array_key_exists($eikenLevelId, $this->existPaymentOfPupil[$pupilId])){
            return false;
        }
        return array_key_exists($price, $this->existPaymentOfPupil[$pupilId][$eikenLevelId]);
    }

    private $existPaymentOfPupil = array();
    public function getExistPaymentOfPupil($orgId,$scheduleId){
        if(!empty($this->existPaymentOfPupil)){
            return $this->existPaymentOfPupil;
        }
        $this->existPaymentOfPupil[-1][-1][-1] = 1;

        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq ->select('PaymentInfo.id, PaymentInfo.pupilId, IssuingPayment.eikenLevelId, IssuingPayment.telNo, IssuingPayment.price')
            ->from('\Application\Entity\PaymentInfo', 'PaymentInfo')
            ->join('\Application\Entity\IssuingPayment', 'IssuingPayment'
                    , \Doctrine\ORM\Query\Expr\Join::WITH
                    , 'PaymentInfo.id = IssuingPayment.paymentInfoId')
            ->join('\Application\Entity\Pupil', 'Pupil'
                    ,\Doctrine\ORM\Query\Expr\Join::WITH
                    ,'PaymentInfo.pupilId = Pupil.id')
            ->where('PaymentInfo.isDelete = 0 '
                    . 'AND IssuingPayment.isDelete = 0 '
                    . 'AND Pupil.isDelete = 0 '
                    . 'AND PaymentInfo.eikenScheduleId = :scheduleId')
            ->setParameter('scheduleId', $scheduleId)
            ->andWhere('Pupil.organizationId = :orgId')
            ->setParameter('orgId', $orgId);
        
        $res = $dq->getQuery()->getArrayResult();

        foreach($res as $row){
            $this->existPaymentOfPupil[$row['pupilId']][$row['eikenLevelId']][$row['price']] = array('id' => $row['id'], 'telNo' => $row['telNo']);
        }
        return $this->existPaymentOfPupil;
    }

    public function receivePaymentInfoFromEcontext(){
        $listFilePaymentInfo = $this->getListFileFromEcontextFtp();
        
        if(empty($listFilePaymentInfo)){
            return;
        }
        $this->getLogger()->info('Receive list file: ', $listFilePaymentInfo);
        foreach ($listFilePaymentInfo as $file){
            echo date('Y-m-d H:i:s e'),' Start process file "',$file,'"',PHP_EOL;
            $this->processPaymentInfoFile($file);
            echo date('Y-m-d H:i:s e'),' End process file "',$file,'"',PHP_EOL;
        }
    }
    
    public function processPaymentInfoFile($file){
        $rawCsvStr = file_get_contents($file);
        $csvStr = \Dantai\Utility\CharsetConverter::shiftJisToUtf8($rawCsvStr);
        $data = \Dantai\Utility\CsvHelper::csvStrToArray($csvStr,"\t");
        $header = array_shift($data);
        $isSatellite = false;
        if (strpos($file, 'satellite') !== false) {
            $isSatellite = true;
        }
        
        if($this->isErrorFile($header)){
            echo 'File error: ',$file;
            $this->sendErrorMailAndDeleteTmpData($data);
            return;
        }
        if(empty($data)){
            return;
        }
        foreach ($data as $row){
            $this->updatePaymentCombiniTemp($this->mapDataPayment($row));
        }
        
        $paymentTmp = $this->getPaymentTmpByFileRow($this->mapDataPayment($data[0]));
        
        if(empty($paymentTmp)){
            return;
        }
        
        $countIncomplete = null;
        if($isSatellite){
            $countIncomplete = $this->countIncompleteByPupil($paymentTmp->getPupilId(),$paymentTmp->getEikenScheduleId());
        }else{
            $countIncomplete = $this->countIncomplete($paymentTmp->getOrgId(),$paymentTmp->getEikenScheduleId());
        }
        
        /* @var $processLog \Application\Entity\ProcessLog */
        $processLog = null;
        if($isSatellite){
            $processLog = $this->getEntityManager()
                ->getRepository('\Application\Entity\ProcessLog')
                ->findOneBy(array(
                    'pupilId' => $paymentTmp->getPupilId(),
                    'scheduleId' => $paymentTmp->getEikenScheduleId()
                ));
        }else{
            $processLog = $this->getEntityManager()
                ->getRepository('\Application\Entity\ProcessLog')
                ->findOneBy(array(
                    'orgId' => $paymentTmp->getOrgId(),
                    'scheduleId' => $paymentTmp->getEikenScheduleId()
                ));
        }
        if(!$processLog){
            return;
        }
        
        $this->getEntityManager()->refresh($processLog);
        
        if($countIncomplete <= 0) {
            if(!$isSatellite){
                $this->createPaymentByTmpData($paymentTmp->getOrgId(), $paymentTmp->getEikenScheduleId());            
                $processLog->setActive($processLog->getTotal());
                $this->getEntityManager()->persist($processLog);
                $this->getEntityManager()->flush();   
            }else{
                $this->createPaymentByTmpDataForPupil($paymentTmp->getPupilId(), $paymentTmp->getEikenScheduleId());   
                if($processLog->getTotal() <= 1){
                    $this->getEntityManager()->remove($processLog);
                }else{
                    $processLog->setTotal($processLog->getTotal() - 1);
                    $this->getEntityManager()->persist($processLog);
                }
                $this->getEntityManager()->flush();    
            }
        }
        // TODO: delete field sent.
    }
    
    public function sendErrorMailAndDeleteTmpData($data) {
        $em = $this->getEntityManager();
        $paymentTmp = $this->getPaymentTmpByFileRow(array('OrderID' => reset($data)[1]));
        if(empty($paymentTmp)){
            return;
        }
        /* @var $processLog \Application\Entity\ProcessLog */
        $processLog = $em->getRepository('\Application\Entity\ProcessLog')
                        ->findOneBy(array(
                            'orgId' => $paymentTmp->getOrgId(),
                            'scheduleId' => $paymentTmp->getEikenScheduleId()
                        ));
        $isSatellite = false;
        if(empty($processLog)){
            $processLog = $em->getRepository('\Application\Entity\ProcessLog')
                        ->findOneBy(array(
                            'pupilId' => $paymentTmp->getPupilId(),
                            'scheduleId' => $paymentTmp->getEikenScheduleId()
                        ));
            $isSatellite = true;
        }
        $processLog->setIsError(1);
        $em->persist($processLog);
        $em->flush();
        
        if($isSatellite){
            $this->deleteTmpDataByPupilId($processLog->getPupilId(),$processLog->getScheduleId());
        }else{
            $this->deleteTmpData($processLog->getOrgId(),$processLog->getScheduleId());
        }
        
        $globalConfig = $this->serviceManager->get('Config');
        $emailSender = isset($globalConfig['emailSender']) ? $globalConfig['emailSender'] : 'dantai@mail.eiken.or.jp';

        $organization = $em->getRepository('\Application\Entity\Organization')->find($processLog->getOrgId());       
        $orgName = $organization !== Null ? $organization->getOrgNameKanji() : '';
        $adminInfo = unserialize($processLog->getAdminInfo());
        foreach ($adminInfo as $adminEmail => $adminName) {
            $dataSendMail = array(
                'orgName' => $orgName,
                'name' => $adminName,
                'url' => $globalConfig['ConsoleInvitation']['login_link']
            );
            \Dantai\Aws\AwsSesClient::getInstance()->deliver($emailSender, array($adminEmail), 3,$dataSendMail);
        }
    }
    
    private function deleteTmpData($orgId,$scheduleId){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('\Application\Entity\PaymentCombiniTemp', 'tmp')
                ->where('tmp.orgId = :orgId')
                ->andWhere('tmp.eikenScheduleId = :eikenScheduleId')
                ->setParameter('orgId', $orgId)
                ->setParameter('eikenScheduleId', $scheduleId);
        $qb->getQuery()->execute();
    }
    
    private function deleteTmpDataByPupilId($pupilId,$scheduleId,$eikenLevelId = null){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('\Application\Entity\PaymentCombiniTemp', 'tmp')
                ->where('tmp.pupilId = :pupilId')
                ->andWhere('tmp.eikenScheduleId = :eikenScheduleId')
                ->setParameter('pupilId', $pupilId)
                ->setParameter('eikenScheduleId', $scheduleId);
        if(!empty($eikenLevelId)){
            $qb->andWhere('tmp.eikenLevelId = :eikenLevelId')
                ->setParameter('eikenLevelId', $eikenLevelId);
        }
        $qb->getQuery()->execute();
    }  

    private function createPaymentByTmpData($orgId,$scheduleId) {
        $listTmp = $this->getTmpPaymentFromOrg($orgId,$scheduleId);
        $em = $this->getEntityManager();
        $dataPaymentIssuing = array();
        foreach($listTmp as $tmp){
            /* @var $tmp \Application\Entity\PaymentCombiniTemp */
            $dataPaymentIssuing[$tmp->getPupilId()]['payment'] = $tmp->toArray();
            $dataPaymentIssuing[$tmp->getPupilId()]['issuing'][] = $tmp->toArray();
            $em->remove($tmp);
        }
        $em->flush();
        
        foreach($dataPaymentIssuing as $paymentIssuing){
            $payment = $this->makePaymentInfo($paymentIssuing['payment']);
            $this->makeIssuingPaymentByTmp($payment,$paymentIssuing['issuing']);
            $em->flush();
        }
    }
    
    private function createPaymentByTmpDataForPupil($pupilId,$scheduleId) {
        $listTmp = $this->getTmpPaymentFromPupil($pupilId,$scheduleId);
        $em = $this->getEntityManager();
        $dataPaymentIssuing = array();
        foreach($listTmp as $tmp){
            /* @var $tmp \Application\Entity\PaymentCombiniTemp */
            $dataPaymentIssuing[$tmp->getPupilId()]['payment'] = $tmp->toArray();
            $dataPaymentIssuing[$tmp->getPupilId()]['issuing'][] = $tmp->toArray();
            $em->remove($tmp);
        }
        $em->flush();
        
        foreach($dataPaymentIssuing as $paymentIssuing){
            $payment = $this->makePaymentInfo($paymentIssuing['payment']);
            $this->makeIssuingPaymentByTmp($payment,$paymentIssuing['issuing']);
            $em->flush();
            $this->unBlockMultiCombini($paymentIssuing['payment']['pupilId'], 
                                    $paymentIssuing['payment']['eikenScheduleId'], 
                                    $paymentIssuing['issuing']);
        }
    }
    
    public function unBlockMultiCombini($pupilId, $eikenScheduleId, $listIssuing) {
        foreach ($listIssuing as $issuing) {
            $this->unBlockGenCombini($pupilId,$eikenScheduleId, $issuing['eikenLevelId']);
        }    
    }
    
    public function unBlockGenCombini($pupilId, $eikenScheduleId, $eikenLevelId) {
        $em = $this->getEntityManager();
        $applyEikenLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')
                ->findOneBy(array(
                    'pupilId' => $pupilId,
                    'eikenScheduleId' => $eikenScheduleId,
                    'eikenLevelId' => $eikenLevelId,
                ));
        if(empty($applyEikenLevel)){
            return false;
        }
        $applyEikenLevel->setBlockCombini(null);
        $em->persist($applyEikenLevel);
        $em->flush();
    }
    
    private function makeIssuingPaymentByTmp($paymentInfo,$listIssuing) {
        $em = $this->getEntityManager();
        foreach($listIssuing as $tmpIssuing){
            $issuingPayment = new \Application\Entity\IssuingPayment();
            
            $issuingPayment->setPaymentInfo($paymentInfo);
            $issuingPayment->setEikenLevel($em->getReference('\Application\Entity\EikenLevel', $tmpIssuing['eikenLevelId']));
            $issuingPayment->setTelNo($tmpIssuing['telNo']);
            $issuingPayment->setProductName($tmpIssuing['productName']);
            $issuingPayment->setPrice($tmpIssuing['price']);
            $issuingPayment->setOrderId($tmpIssuing['orderId']);
            $issuingPayment->setReceiptNo($tmpIssuing['receiptNo']);
            $issuingPayment->setOrderResultCode($tmpIssuing['orderResultCode']);
            $issuingPayment->setOrderResultInfo($tmpIssuing['orderResultInfo']);
            $em->persist($issuingPayment);
        }
    }

    private function getTmpPaymentFromOrg($orgId,$scheduleId) {
        return $this->getEntityManager()
                ->getRepository('\Application\Entity\PaymentCombiniTemp')
                ->findBy(array(
                    'orgId' => $orgId,
                    'eikenScheduleId' => $scheduleId
                ));
    }
    
    private function getTmpPaymentFromPupil($pupilId,$scheduleId) {
        return $this->getEntityManager()
                ->getRepository('\Application\Entity\PaymentCombiniTemp')
                ->findBy(array(
                    'pupilId' => $pupilId,
                    'eikenScheduleId' => $scheduleId
                ));
    }
    
    private function countIncomplete($orgId,$eikenScheduleId){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(tmp.id)')
            ->from('\Application\Entity\PaymentCombiniTemp', 'tmp')
            ->where('tmp.orgId = :orgId')
            ->andWhere('tmp.eikenScheduleId = :eikenScheduleId')
            ->andWhere('tmp.isCompleted = 0')
            ->setParameter('orgId', $orgId)
            ->setParameter('eikenScheduleId', $eikenScheduleId);
        $res = $qb->getQuery()->getSingleScalarResult();
        return (int) $res;
    }
    
    private function countIncompleteByPupil($pupilId,$eikenScheduleId){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(tmp.id)')
            ->from('\Application\Entity\PaymentCombiniTemp', 'tmp')
            ->where('tmp.pupilId = :pupilId')
            ->andWhere('tmp.eikenScheduleId = :eikenScheduleId')
            ->andWhere('tmp.isCompleted = 0')
            ->setParameter('pupilId', $pupilId)
            ->setParameter('eikenScheduleId', $eikenScheduleId);
        $res = $qb->getQuery()->getSingleScalarResult();
        return (int) $res;
    }

    /**
     * @param array $rowData
     * @return \Application\Entity\PaymentCombiniTemp
     */
    private function getPaymentTmpByFileRow($rowData){
        $tmp = $this->getEntityManager()->getRepository('\Application\Entity\PaymentCombiniTemp')->findOneBy(array(
            'orderId' => $rowData['OrderID'],
        ));
        return $tmp;
    }
    
    private function updatePaymentCombiniTemp($rowData) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $qb->update('\Application\Entity\PaymentCombiniTemp','tmp')
            ->set('tmp.receiptNo', $qb->expr()->literal($rowData['ReceiptNo']))
            ->set('tmp.orderResultCode', $qb->expr()->literal($rowData['OrderResultCode']))
            ->set('tmp.orderResultInfo', $qb->expr()->literal($rowData['OrderResultInfo']))
            ->set('tmp.isCompleted', '1')
            ->where('tmp.orderId = :orderID')
            ->andWhere('tmp.telNo = :telNo')
            ->setParameter('orderID', $rowData['OrderID'])
            ->setParameter('telNo', $rowData['TelNo']);
        
        $qb->getQuery()->execute();
    }
    
    protected function mapDataPayment($csvRow) {
        $configRow = array(
            1 => 'SiteCode',
            2 => 'OrderID',
            3 => 'TelNo',
            4 => 'ReceiptNo',
            5 => 'OrderResultCode',
            6 => 'OrderResultInfo',
        );
        $res = array();
        foreach ($configRow as $cell => $field){
            $res[$field] = $csvRow[$cell-1];
        }
        return $res;
    }
    
    public function isErrorFile($header) {
        return strtoupper($header[3]) === 'E';
    }

    protected function getListFileFromEcontextFtp(){
        $configFtp = $this->getConfig()['econtext_combini_ftp_config'];
        $sftp = new SFTP($configFtp['host'], $configFtp['port'], $configFtp['timeout']);
        
        if( !$sftp->login($configFtp['username'],$configFtp['password'])){
            return false;
        }
        $listFilePaymentInfo = array();
        $listFile = $sftp->_list('./res');
        
        foreach ($listFile as $file){
            $lenFileName = mb_strlen($file['filename'], 'UTF-8');
            if(1 != $file['type'] || $lenFileName <= 8){
                continue;
            }
            $fileSavePath = $this->getConfig()['econtext_combini_file_path'] . DIRECTORY_SEPARATOR .$file['filename'];
            if(strtolower(mb_substr($file['filename'], $lenFileName-8, 8, 'UTF-8')) == '-res.dat'){
                $sftp->get('./res/'.$file['filename'], $fileSavePath);
                $listFilePaymentInfo[] = $fileSavePath;
                $sftp->delete('./res/'.$file['filename']);
            }
        }
        return $listFilePaymentInfo;
    }
    
    public function connectToEcontextFtp(){
        $configFtp = $this->getConfig()['econtext_combini_ftp_config'];
        $sftp = new SFTP($configFtp['host'], $configFtp['port'], $configFtp['timeout']);
        
        if( !$sftp->login($configFtp['username'],$configFtp['password'])){
            return false;
        }
    }
    
    public function getListUserByRole($roleId, $orgId = 0) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('user.id, user.userId, user.firstNameKanji, user.lastNameKanji, user.emailAddress')
                ->from('\Application\Entity\User', 'user')
                ->where('user.isDelete = 0')
                ->andWhere('user.status = :status')
                ->andWhere('user.roleId = :roleId')
                ->setParameter(':status', 'Enable')
                ->setParameter(':roleId', intval($roleId));
        if ($orgId > 0) {
            $qb->andWhere('user.organizationId = :organizationId')
                    ->setParameter(':organizationId', $orgId);
        }
        return $qb->getQuery()->getArrayResult();
    }

    public function isExpiredExecuteCombini($orgId, $expiredTime='-1 hours'){
        $deadLockTime = new \DateTime('now');
        $deadLockTime->modify($expiredTime);

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('runningCombini')
           ->from('\Application\Entity\RunningCombini', 'runningCombini')
           ->where('runningCombini.orgId = :orgId')
           ->andWhere('runningCombini.insertAt < :deadLockTime')
           ->setParameter('orgId', intval($orgId))
           ->setParameter('deadLockTime', $deadLockTime->format('Y-m-d H:i:s'));
        $data = $qb->getQuery()->getOneOrNullResult();
        
        return $data ? true : false;
    }
    
    public function sendMailExpiredTimeExecuteCombini(\Application\Entity\ProcessLog $processLog) {
        $awsSes = \Dantai\Aws\AwsSesClient::getInstance();
        $em = $this->getEntityManager();
        $adminInfo = unserialize($processLog->getAdminInfo());
        /* @var $organization \Application\Entity\Organization */
        $organization = $em->getRepository('\Application\Entity\Organization')->find($processLog->getOrgId());
        $orgName = $organization !== Null ? $organization->getOrgNameKanji() : '';
        $timeGenerate = $processLog->getInsertAt() != Null ? $processLog->getInsertAt()->format('Y/m/d H:i:s') : '';
        $urlLogin = $this->serviceManager->get('Config')['ConsoleInvitation']['login_link'];
        
        /* send mail to all dantai admin */
        $listDantaiAdmin = $this->getListUserByRole(self::DANTAI_ADMIN, $processLog->getOrgId());
        if ($listDantaiAdmin) {
            foreach ($listDantaiAdmin as $value) {
                $adminInfo[$value['emailAddress']] = $value['firstNameKanji'] . $value['lastNameKanji'];
            }
        }

        foreach ($adminInfo as $adminEmail => $adminName) {
            $awsSes->deliver('dantai@mail.eiken.or.jp', array(
                $adminEmail
                    ), self::TYPE_MAIL_EXPIRED_EXECUTE_COMBINI, array(
                'name' => $adminName,
                'orgName' => $orgName,
                'orgNo' => $organization->getOrganizationNo(),
                'timeGenerate' => $timeGenerate,
                'url' => $urlLogin
            ));
        }
        
        /* send mail to system admin */
        $listSystemAdmin = $this->getListUserByRole(self::SYSTEM_ADMIN);
        if ($listSystemAdmin) {
            foreach ($listSystemAdmin as $value) {
                $awsSes->deliver('dantai@mail.eiken.or.jp', array(
                    $value['emailAddress']
                        ), self::TYPE_MAIL_EXPIRED_EXECUTE_COMBINI_ADMIN, array(
                    'name' => $value['firstNameKanji'] . $value['lastNameKanji'],
                    'orgName' => $orgName,
                    'orgNo' => $organization->getOrganizationNo(),
                    'timeGenerate' => $timeGenerate,
                    'url' => $urlLogin
                ));
            }
        }

        /* send mail to FPT admin*/
        $awsSes->deliver('dantai@mail.eiken.or.jp', array(
            'minhtn6@fsoft.com.vn'
                ), self::TYPE_MAIL_EXPIRED_EXECUTE_COMBINI_ADMIN, array(
            'name' => 'TRAN NGHIA MINH',
            'orgName' => $orgName,
            'orgNo' => $organization->getOrganizationNo(),
            'timeGenerate' => $timeGenerate,
            'url' => $urlLogin
        ));
    }
    
    public function getPriceMultiGrade($orgId, $scheduleId){
        $data = $this->getPriceMasterData($orgId, $scheduleId);
        if(empty($data)){
            return false;
        }
        $masterData = '';
        foreach ($data as $row) {
            $value = $row['price'];
            //  add array with key is orgSchoolYearId_hallType
            $keyArray = $row['orgSchoolYearId'] . '_' . $value['hallType'];
            $priceLv1 = $value['lev1'];
            $pricePreLv1 = $value['preLev1'];
            if($value['hallType'] == 0){
                foreach ($data as $rowPrice) {
                    $valuePrice = $rowPrice['price'];
                    if($row['orgSchoolYearId'] == $rowPrice['orgSchoolYearId'] 
                        && $valuePrice['hallType'] == 1){
                        
                        $priceLv1 = $valuePrice['lev1'];
                        $pricePreLv1 = $valuePrice['preLev1'];
                        break;
                        
                    }
                }
            }
            $masterData[$keyArray] = array(
                1 => [
                    'price' => $priceLv1,
                    'name' => '1級',
                ],
                2 => [
                    'price' => $pricePreLv1,
                    'name' => '準1級',
                ],
                3 => [
                    'price' => $value['lev2'],
                    'name' => '2級',
                ],
                4 => [
                    'price' => $value['preLev2'],
                    'name' => '準2級',
                ],
                5 => [
                    'price' => $value['lev3'],
                    'name' => '3級',
                ],
                6 => [
                    'price' => $value['lev4'],
                    'name' => '4級',
                ],
                7 => [
                    'price' => $value['lev5'],
                    'name' => '5級',
                ]
            );
        }

        return $masterData;
    }
    
    public function getPriceMasterData($orgId, $scheduleId){
        /* @var $objectSchedule \Application\Entity\EikenSchedule */
        $objectSchedule = $this->getEntityManager()
                ->getRepository('\Application\Entity\EikenSchedule')
                ->find($scheduleId);
        
        if(empty($objectSchedule)){
            return false;
        }
        
        $em =$this->getEntityManager();
        
        $qb = $em->createQueryBuilder();
        
        $data = $qb->select('sp as price','orgSchoolYear.id as orgSchoolYearId','schoolYear.id as schoolYearId')
                        ->from('\Application\Entity\SpecialPrice', 'sp')
                        ->innerJoin('\Application\Entity\SchoolYearMapping', 'schoolYearMapping', 
                                \Doctrine\ORM\Query\Expr\Join::WITH, 'sp.schoolYearCode = schoolYearMapping.schoolYearCode AND sp.schoolClassification = schoolYearMapping.orgCode')
                        ->innerJoin('\Application\Entity\Organization', 'organization', 
                                \Doctrine\ORM\Query\Expr\Join::WITH, 'sp.organizationId = organization.id')
                        ->innerJoin('\Application\Entity\SchoolYear', 'schoolYear', 
                                \Doctrine\ORM\Query\Expr\Join::WITH, 'schoolYearMapping.schoolYearId = schoolYear.id')
                        ->innerJoin('\Application\Entity\OrgSchoolYear', 'orgSchoolYear', 
                                \Doctrine\ORM\Query\Expr\Join::WITH, 'orgSchoolYear.schoolYearId = schoolYearMapping.schoolYearId AND orgSchoolYear.organizationId = organization.id')
                        ->where($qb->expr()->in('sp.schoolYearCode', 
                                        $em->createQueryBuilder()
                                        ->select('sym.schoolYearCode')
                                        ->from('\Application\Entity\SchoolYearMapping', 'sym')
                                        ->where($qb->expr()->in('sym.orgCode', 
                                                        $em->createQueryBuilder()
                                                        ->select('o.organizationCode')
                                                        ->from('\Application\Entity\Organization', 'o')
                                                        ->where('o.id = :organizationId')
                                                        ->andWhere('o.isDelete =:isDelete')
                                                        ->getDQL()
                                        ))
                                        ->andWhere($qb->expr()->in('sym.orgCode',
                                                        $em->createQueryBuilder()
                                                        ->select('org.organizationCode')
                                                        ->from('\Application\Entity\Organization', 'org')
                                                        ->where('org.id = :organizationId')
                                                        ->andWhere('org.isDelete =:isDelete')
                                                        ->getDQL()
                                        ))
                                        ->getDQL()
                        ))
                        ->andWhere(
                                $qb->expr()->in('sp.schoolClassification',
                                            $em->createQueryBuilder()
                                            ->select('orga.organizationCode')
                                            ->from('\Application\Entity\Organization', 'orga')
                                            ->where('orga.id = :organizationId')
                                            ->andWhere('orga.isDelete =:isDelete')
                                            ->getDQL()
                                ))
                        ->andWhere('sp.organizationId = :organizationId')
                        ->andWhere('sp.year = :year')
                        ->andWhere('sp.kai = :kai')
                        ->andWhere('sp.isDelete =:isDelete')
                        ->setParameter(':organizationId', $orgId)
                        ->setParameter(':year', $objectSchedule->getYear())
                        ->setParameter(':kai', $objectSchedule->getKai())
                        ->setParameter(':isDelete', 0)
                        ->orderBy('schoolYear.id','ASC')
                        ->getQuery()->getArrayResult();
        
        return $data;
    }
}

