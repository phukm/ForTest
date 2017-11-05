<?php
/*
 * @author : Huy Manh (ManhNH5)
 */
namespace Satellite\Service;

use Application\Entity\Repository\ApplyEikenLevelRepository;
use Dantai\Utility\DateHelper;
use Satellite\Constants;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\Api\UkestukeClient;
use Dantai\Utility\CharsetConverter;

class PaymentEikenExamService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected $sl;
    protected $em;
    protected $translate;
    protected $charsetConverter;
    const FORMAT_DATE = 'Y-m-d';

    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager)
    {
        $this->setServiceLocator($serviceManager);
        $this->sl = $this->getServiceLocator();
        $this->em = $this->sl->get('doctrine.entitymanager.orm_default');
        $this->translate = $this->sl->get('MVCTranslator');
        $this->charsetConverter = new CharsetConverter();
    }
    
    public function mappingKyu($eikenLevelPrice, $examDate, $listEikenLevel, $hallTypeExamDay, $tuitionfee = array(), $hallType = false)
    {
        $listEikenLevel = $listEikenLevel ? json_decode($listEikenLevel) : array();
        
        $listKyu = array();
        $hallTypeExamDate = Constants::HALL_TYPE_EXAM_DATE;
        foreach ($listEikenLevel as $kyu) {
            $listKyu[$kyu]['priceName'] = sprintf($this->translate->translate('priceName'), number_format(empty($tuitionfee[$kyu]) ? $eikenLevelPrice[$kyu]['price'] : $tuitionfee[$kyu]));
            $listKyu[$kyu]['price'] = empty($tuitionfee[$kyu]) ? $eikenLevelPrice[$kyu]['price'] : $tuitionfee[$kyu];
            $listKyu[$kyu]['name'] = $eikenLevelPrice[$kyu]['name'];
            $listKyu[$kyu]['examDate'] = '';
            $listKyu[$kyu]['examDate2Round'] = '';
            if(!empty($eikenLevelPrice[$kyu]) && array_key_exists('paymentStatus', $eikenLevelPrice[$kyu])){
                $listKyu[$kyu]['paymentStatus'] = $eikenLevelPrice[$kyu]['paymentStatus'];
            }
            if(!empty($eikenLevelPrice[$kyu]) && array_key_exists('eikenLevelId', $eikenLevelPrice[$kyu])){
               $listKyu[$kyu]['eikenLevelId'] = $eikenLevelPrice[$kyu]['eikenLevelId'];
            }
            if(!empty($eikenLevelPrice[$kyu]) && array_key_exists('applyEikenLevelId', $eikenLevelPrice[$kyu])){
               $listKyu[$kyu]['applyEikenLevelId'] = $eikenLevelPrice[$kyu]['applyEikenLevelId'];
            }
            
            if ($hallTypeExamDay == $hallTypeExamDate['sunDate'] || $kyu == 1 || $kyu == 2 || $hallType) {
                $listKyu[$kyu]['examDate'] = $this->getExamDate($examDate->getSunDate(), 'sunDate');
            }
            else if ($hallTypeExamDay == $hallTypeExamDate['friDate']) {
                $listKyu[$kyu]['examDate'] = $this->getExamDate($examDate->getFriDate(), 'friDate');
            }
            else if ($hallTypeExamDay == $hallTypeExamDate['satDate']) {
                $listKyu[$kyu]['examDate'] = $this->getExamDate($examDate->getSatDate(), 'satDate');
            }
            else if ($hallTypeExamDay == $hallTypeExamDate['friDateAndSatDate']) {
                $listKyu[$kyu]['examDate'] = $this->getExamDate($examDate->getFriDate(), 'friDate') . $this->translate->translate('comma') . $this->getExamDate($examDate->getSatDate(), 'satDate');
            }
            
            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');

            $dateconvertA = $dantaiService->changeDay(($examDate->getRound2Day1ExamDate() ? $examDate->getRound2Day1ExamDate()->format('D') : '') ? $examDate->getRound2Day1ExamDate()->format('D'): '');
            $dateconvertB = $dantaiService->changeDay(($examDate->getRound2Day2ExamDate() ? $examDate->getRound2Day2ExamDate()->format('D') : '') ? $examDate->getRound2Day2ExamDate()->format('D') : '');
            
            $listKyu[$kyu]['examDate2Round'] = $this->translate->translate('round1') . $listKyu[$kyu]['examDate'];

            if(! isset($dantaiService->getDateRound2EachKyu()[$kyu])) continue;
            
            if($dantaiService->getDateRound2EachKyu()[$kyu] == 1){
                $listKyu[$kyu]['examDate2Round'] .= '<br>'
                    . $this->translate->translate('round2') 
                    .$this->getExamDate($examDate->getRound2Day1ExamDate(), 'Round2Day1ExamDate') 
                    . '（'. $dateconvertA .'）' ;
            }else{
                $listKyu[$kyu]['examDate2Round'].= '<br>'
                    . $this->translate->translate('round2')
                    . $this->getExamDate($examDate->getRound2Day2ExamDate(), 'Round2Day2ExamDate') 
                    . '（'. $dateconvertB .'）';
            }  
        }
        
        return $listKyu;
    }

    public function mappingMultiKyuHallType($eikenLevelPrice, $examDate, $listEikenLevel, $hallTypeExamDay)
    {
        $listKyu = array();
        $hallTypeExamDate = Constants::HALL_TYPE_EXAM_DATE;
        /*@var $examDate \Application\Entity\EikenSchedule*/
        foreach ($listEikenLevel as $kyu) {
            $listKyu[$kyu['eikenLevelId']]['priceName'] = sprintf($this->translate->translate('priceName'), number_format($kyu['price']));
            $listKyu[$kyu['eikenLevelId']]['price'] = $kyu['price'];
            $listKyu[$kyu['eikenLevelId']]['name'] = $eikenLevelPrice[$kyu['eikenLevelId']]['name'];
            $listKyu[$kyu['eikenLevelId']]['examDate'] = '';
            if(!empty($eikenLevelPrice[$kyu['eikenLevelId']]) && array_key_exists('paymentStatus', $eikenLevelPrice[$kyu['eikenLevelId']])){
                $listKyu[$kyu['eikenLevelId']]['paymentStatus'] = $eikenLevelPrice[$kyu['eikenLevelId']]['paymentStatus'];
            }
            if(!empty($eikenLevelPrice[$kyu['eikenLevelId']]) && array_key_exists('eikenLevelId', $eikenLevelPrice[$kyu['eikenLevelId']])){
                $listKyu[$kyu['eikenLevelId']]['eikenLevelId'] = $eikenLevelPrice[$kyu['eikenLevelId']]['eikenLevelId'];
            }
            if(!empty($eikenLevelPrice[$kyu['eikenLevelId']]) && array_key_exists('applyEikenLevelId', $eikenLevelPrice[$kyu['eikenLevelId']])){
                $listKyu[$kyu['eikenLevelId']]['applyEikenLevelId'] = $eikenLevelPrice[$kyu['eikenLevelId']]['applyEikenLevelId'];
            }

            if ($hallTypeExamDay == $hallTypeExamDate['sunDate'] || $kyu['hallType']) {
                $listKyu[$kyu['eikenLevelId']]['examDate'] = $this->getExamDate($examDate->getSunDate(), 'sunDate');
            }
            else if ($hallTypeExamDay == $hallTypeExamDate['friDate']) {
                $listKyu[$kyu['eikenLevelId']]['examDate'] = $this->getExamDate($examDate->getFriDate(), 'friDate');
            }
            else if ($hallTypeExamDay == $hallTypeExamDate['satDate']) {
                $listKyu[$kyu['eikenLevelId']]['examDate'] = $this->getExamDate($examDate->getSatDate(), 'satDate');
            }
            else if ($hallTypeExamDay == $hallTypeExamDate['friDateAndSatDate']) {
                $listKyu[$kyu['eikenLevelId']]['examDate'] = $this->getExamDate($examDate->getFriDate(), 'friDate') . $this->translate->translate('comma') . $this->getExamDate($examDate->getSatDate(), 'satDate');
            }
            
            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
            $dateconvertA = $dantaiService->changeDay($examDate->getRound2Day1ExamDate()->format('D'));
            $dateconvertB = $dantaiService->changeDay($examDate->getRound2Day2ExamDate()->format('D'));
            $listKyu[$kyu['eikenLevelId']]['examDateRound2'] = $this->translate->translate('round1') 
                    .$listKyu[$kyu['eikenLevelId']]['examDate'];
            if(!isset($dantaiService->getDateRound2EachKyu()[$kyu['eikenLevelId']]))                
                continue;

            if($dantaiService->getDateRound2EachKyu()[$kyu['eikenLevelId']] == 1){
                $listKyu[$kyu['eikenLevelId']]['examDateRound2'] .= '<br>'
                    .$this->translate->translate('round2')
                    .$this->getExamDate($examDate->getRound2Day1ExamDate(), 'Round2Day1ExamDate'). '（'. $dateconvertA .'）';
            }else{
                $listKyu[$kyu['eikenLevelId']]['examDateRound2'] .= '<br>'
                    .$this->translate->translate('round2')
                    .$this->getExamDate($examDate->getRound2Day2ExamDate(), 'Round2Day2ExamDate'). '（'. $dateconvertB .'）';
            }
        }   
        return $listKyu;
    }

    private function getExamDate($examDate, $date)
    {
        return !empty($examDate) ? sprintf($this->translate->translate($date), $examDate->format('n'), $examDate->format('j')) : '';
    }

    public function choosePaymentKyu($chooseKyu, $listKyu)
    {
        $paymentKyu = array();
        if ($listKyu && $chooseKyu) {
            foreach ($chooseKyu as $kyuId) {
                $paymentKyu[$kyuId] = $listKyu[$kyuId];
            }
        }

        return $paymentKyu;
    }

    public function validate($data)
    {
        if (!$data['cardFirstName'] || !$data['cardLastName'] || !$data['cardNumber'] || !$data['cardMonth'] || !$data['cardYear'] || !$data['cardCvv']) {
            return false;
        }
        //Client ask no need to check from "Econtext validate" !!!
        //if (!$this->checkEcontextCardNo($data['cardNumber'])) {
        //    return false;
        //}
        if (strlen(trim($data['cardNumber'])) > 16 || !is_numeric($data['cardNumber'])) {
            return false;
        }
        if (($data['cardMonth'] < 0 && $data['cardMonth'] > 12) || ($data['cardYear'] < date('Y') && $data['cardYear'] > date('Y') + 5)) {
            return false;
        }
        if (strlen($data['cardCvv']) < 3 && strlen($data['cardCvv']) > 4) {
            return false;
        }
        return true;
    }
    
    public function getParameterOfApiCredit($params, $orderId, $telNo) {
        $creditCardConfig = $this->sl->get('Config')['creditcard_config'];
        $shopID = isset($creditCardConfig['site_code']) ? $creditCardConfig['site_code'] : '078010';
        $cardExpdate = '';
        if (!empty($params['cardMonth']) && !empty($params['cardYear'])) {
            $cardExpdate = trim($params['cardYear']) . trim($params['cardMonth']);
        }
        $parameterApi = array(
            'shopID' => $shopID,
            'orderID' => $orderId,
            'telNo' => $telNo,
            'kanjiName1_1' => isset($params['cardFirstName']) ? trim($this->charsetConverter->utf8ToShiftJis($params['cardFirstName'])) : '',
            'kanjiName1_2' => isset($params['cardLastName']) ? trim($this->charsetConverter->utf8ToShiftJis($params['cardLastName'])) : '',
            'email' => isset($creditCardConfig['email']) ? $creditCardConfig['email'] : 'kojin@mail.eiken.or.jp',
            'econCardno' => isset($params['cardNumber']) ? trim($params['cardNumber']) : '',
            'cardExpdate' => $cardExpdate,
            'CVV2' => isset($params['cardCvv']) ? '0' . trim($params['cardCvv']) : '',
        );
        if (isset($params['kyu']) && count($params['kyu']) > 0) {
            $i = 0;
            $ordAmount = 0;
            foreach ($params['kyu'] as $eikenLevel) {
                $i++;
                $parameterApi['itemName' . $i] = $this->charsetConverter->utf8ToShiftJis($eikenLevel['name']);
                $parameterApi['unitPrice' . $i] = $eikenLevel['price'];
                $parameterApi['ordUnit' . $i] = 1;
                $parameterApi['unitChar' . $i] = $this->charsetConverter->utf8ToShiftJis('件');
                $parameterApi['dtlAmount' . $i] = $eikenLevel['price'];
                $parameterApi['goodsCode' . $i] = 0;
                $ordAmount += $eikenLevel['price'];
            }
            $parameterApi['ordItemNo'] = count($params['kyu']);
            $parameterApi['ordAmount'] = $ordAmount;
        }

        return $parameterApi;
    }

    private $ukestukeClient;

    public function callApiCredit($parameterApi)
    {
        $response = array('status' => 0, 'message' => '');
        $config = $this->sl->get('Config')['creditcard_config']['api'];
        try {
            if (!$this->ukestukeClient) {
                $this->setUkestukeClient();
            }
            $result = $this->ukestukeClient->callNEconRcvOdr($config, $parameterApi);
            $messages = $this->getArrayMessageApi();
            $response['status'] = $result['status'];
            $response['message'] = isset($messages[$result['status']]) ? $messages[$result['status']] : $this->charsetConverter->shiftJisToUtf8($result['message']);
        }
        catch (\Exception $ex) {
            $response['message'] = $ex->getMessage();
        }

        return $response;
    }
    
    public function setUkestukeClient($client = null)
    {
        $ukestukeClient = new UkestukeClient();
        $this->ukestukeClient = $client ? $client : $ukestukeClient->getInstance();
    }

    public function generateOrderId($organizationNo)
    {
        $orderPrefix = date('Ymd') . $organizationNo;
        $orderIndex = $this->em->getRepository('Application\Entity\PaymentOrderIndex')->getPaymentOrderIndexByPrefix($orderPrefix);
        if (empty($orderIndex)) {
            $orderIndex = new \Application\Entity\PaymentOrderIndex();
            $orderIndex->setPrefix($orderPrefix);
            $orderIndex->setIndex(1);
            $this->em->persist($orderIndex);
            $this->em->flush($orderIndex);

            return $orderPrefix . sprintf('%06d', 0);
        }
        $indexOrderId = $orderIndex->getIndex();
        $this->em->refresh($orderIndex);
        $orderIndex->addIndex();
        $this->em->persist($orderIndex);
        $this->em->flush($orderIndex);

        return $orderPrefix . sprintf('%06d', $indexOrderId);
    }
    
    private $orgRepo;
    
    public function generateTelNo($organizationNo)
    {
        /* @var $organization \Application\Entity\Organization */
        if (!$this->orgRepo) {
            $this->setOrgRepository();
        }
        $organization = $this->orgRepo->findOneBy(array(
            'organizationNo' => $organizationNo
        ));
        if (!$organization) {
            return null;
        }
        $telNoPrefix = $organization->getTelNo();
        $orderIndex = $this->em->getRepository('Application\Entity\PaymentOrderIndex')->getPaymentOrderIndexByPrefix($telNoPrefix);
        if (empty($orderIndex)) {
            $orderIndex = new \Application\Entity\PaymentOrderIndex();
            $orderIndex->setPrefix($telNoPrefix);
            $orderIndex->setIndex(0);
            $orderIndex->setLastTelNoIndex(1);
            $this->em->persist($orderIndex);
            $this->em->flush($orderIndex);

            return $telNoPrefix . '00';
        }
        $lastTelNoIndex = $orderIndex->getLastTelNoIndex();
        $this->em->refresh($orderIndex);
        $orderIndex->setLastTelNoIndex($lastTelNoIndex >= 100 ? 0 : $lastTelNoIndex + 1);
        $this->em->persist($orderIndex);
        $this->em->flush($orderIndex);

        return $telNoPrefix . str_pad($lastTelNoIndex, 2, "0", STR_PAD_LEFT);
    }
    
    public function setOrgRepository($orgRepo = null)
    {
        $this->orgRepo = $orgRepo ? $orgRepo : $this->em->getRepository('Application\Entity\Organization');
    }

    public function createNewPayment($pupilId, $eikenScheduleId, $eikenLevels, $orderId, $telNo)
    {
        $config = $this->sl->get('Config')['creditcard_config'];
        $pupil = $this->em->getRepository('\Application\Entity\Pupil')->findOneBy(array(
            'id'       => $pupilId,
            'isDelete' => 0
        ));
        $eikenSchedule = $this->em->getRepository('\Application\Entity\EikenSchedule')->findOneBy(array(
            'id'       => $eikenScheduleId,
            'isDelete' => 0
        ));
        if (!$pupil || !$eikenSchedule) {
            return false;
        }
        $paymentInfoRepo = $this->em->getRepository('Application\Entity\PaymentInfo');
        $issuingPaymentRepo = $this->em->getRepository('Application\Entity\IssuingPayment');
        $paymentInfo = $paymentInfoRepo->createPaymentInfo($pupil, $eikenSchedule, $config);
        if ($paymentInfo === null) {
            return false;
        }
        $issuingPaymentRepo->createIssuingPayment($paymentInfo, $eikenLevels, $orderId, $telNo);
        $ordAmount = 0;
        foreach ($eikenLevels as $eikenLevel) {
            $ordAmount += $eikenLevel['price'];
        }
        /* @var $dantaiApiService \DantaiApi\Service\DantaiApiService */
        $dantaiApiService = $this->sl->get('DantaiApi\Service\DantaiApiServiceInterface');
        $dataPayment = array(
            'cvsCode'   => '',
            'inputID'   => 0,
            'kssspCode' => '',
            'ordAmount' => $ordAmount,
            'orderID' => $orderId,
            'payBy' => 1,
            'payDate' => date('y/m/d H:i:s'),
            'shopID' => $config['site_code'],
            'id' => 0

        );
        $statusPayment = $dantaiApiService->processPaymentCredit($pupilId, $paymentInfo->getId(), $eikenLevels, $dataPayment);

        return $statusPayment;
    }
    
    public function getTelNoExistInPayment($pupilId, $eikenScheduleId)
    {
        $telNo = null;
        $issuingPaymentRepo = $this->em->getRepository('Application\Entity\IssuingPayment');
        $issuingPayment = $issuingPaymentRepo->getDataByPupilIdAndEikenScheduleId($pupilId, $eikenScheduleId);
        if ($issuingPayment) {
            foreach ($issuingPayment as $value) {
                if (!empty($telNo)) {
                    $telNo = $value['telNo'];
                    break;
                }
            }
        }

        return $telNo;
    }


    public function getArrayMessageApi(){
        $message = array(
            -1 => 'クレジットカードの決済時にエラーが発生しました。',
            -2 => 'クレジットカードの決済時にエラーが発生しました。',
            -3 => 'クレジットカードの決済時にエラーが発生しました。',
            -4 => 'クレジットカードの決済時にエラーが発生しました。',
            -5 => 'クレジットカードの決済時にエラーが発生しました。',
            -6 => 'クレジットカードの決済時にエラーが発生しました。',
            -7 => 'ご入力いただいたクレジットカードはお取り扱いできません。',
            -8 => 'クレジットカードの決済時にエラーが発生しました。',
        );
        return $message;
    }

    public function getPaymentInformation($data)
    {
        if (empty($data['eikenScheduleId']) || empty($data['pupilId'])) {
            return false;
        }
        $applyEikenLevel = $this->em->getRepository('Application\Entity\ApplyEikenLevel')->findBy(array('pupilId' => $data['pupilId'], 'eikenScheduleId' => $data['eikenScheduleId'], 'isDelete' => 0, 'isRegister' => 1), array('eikenLevelId' => 'ASC'));
        if (empty($applyEikenLevel)) {
            return false;
        }
        $examDate = $this->em->getRepository('Application\Entity\EikenSchedule')->find($data['eikenScheduleId']);
        foreach ($applyEikenLevel as $val) {
            $paymentStatus = $val->getPaymentStatus();
            $paymentInfo[$val->getEikenLevelId()]['price'] = $val->getTuitionFee();
            $paymentInfo[$val->getEikenLevelId()]['name'] = $val->getEikenLevel()->getLevelName();
            $paymentInfo[$val->getEikenLevelId()]['paymentStatus'] = '未支払';
            $paymentInfo[$val->getEikenLevelId()]['eikenLevelId'] = $val->getEikenLevelId();
            $paymentInfo[$val->getEikenLevelId()]['applyEikenLevelId'] = $val->getId();
            if($paymentStatus == 1)
            { 
                $paymentInfo[$val->getEikenLevelId()]['paymentStatus'] = '支払済';
            }
            $listEikenLevel[] = $val->getEikenLevelId();
        }
        return $this->mappingKyu($paymentInfo, $examDate, json_encode($listEikenLevel), $data['hallTypeExamDay']);
    }
    
    public function getPaymentInformationMuntilKyu($data)
    {
        if (empty($data['pupilId']) || empty($data['organizationId']) || empty($data['eikenScheduleId'])) {
            return false;
        }
        /**
         * @var ApplyEikenLevelRepository $applyEikenLevelRepo
         */
        $applyEikenLevelRepo = $this->em->getRepository('Application\Entity\ApplyEikenLevel');
        $applyEikenLevel = $applyEikenLevelRepo->getPaymentInformationMuntilKyu($data['pupilId'],$data['organizationId'],$data['eikenScheduleId']);
        if (empty($applyEikenLevel)) {
            return false;
        }
        
        $eikenLevelName = $this->getServiceLocator()->get('Config')['MappingLevel'];
        $hallTypeExamDate = Constants::HALL_TYPE_EXAM_DATE;
        $data = array();

        foreach ($applyEikenLevel as $val) {
            $data[$val['id']]['priceName'] = sprintf($this->translate->translate('priceName'), number_format($val['tuitionFee'] ? $val['tuitionFee'] : 0));
            $data[$val['id']]['price'] = $val['tuitionFee'] ? $val['tuitionFee'] : 0;
            $data[$val['id']]['paymentStatus'] = '未支払';
            $data[$val['id']]['name'] = '';
            $data[$val['id']]['applyEikenLevelId'] = $val['id'];
            $data[$val['id']]['examDate'] = '';
            $data[$val['id']]['eikenLevelId'] = $val['eikenLevelId'];
            $data[$val['id']]['examDate2Round'] = '';

            if($val['paymentStatus'] == 1)
            { 
                $data[$val['id']]['paymentStatus'] = '支払済';
            }
            if(array_key_exists($val['eikenLevelId'], $eikenLevelName)){
                $data[$val['id']]['name'] = $eikenLevelName[$val['eikenLevelId']];
            }
            $data[$val['id']]['examDate'] = '';
            if ($val['examDay'] == $hallTypeExamDate['sunDate'] || $val['hallType'] == 1) {
                $data[$val['id']]['examDate'] = $this->getExamDate($val['sunDate'], 'sunDate');
            }
            else if ($val['examDay'] == $hallTypeExamDate['friDate']) {
                $data[$val['id']]['examDate'] = $this->getExamDate($val['friDate'], 'friDate');
            }
            else if ($val['examDay'] == $hallTypeExamDate['satDate']) {
                $data[$val['id']]['examDate'] = $this->getExamDate($val['satDate'], 'satDate');
            }
            else if ($val['examDay'] == $hallTypeExamDate['friDateAndSatDate']) {
                $data[$val['id']]['examDate'] = $this->getExamDate($val['friDate'], 'friDate') . $this->translate->translate('comma') . $this->getExamDate($val['satDate'], 'satDate');
            }
            
            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
            $dateconvertA = $dantaiService->changeDay($val['round2Day1ExamDate']->format('D'));
            $dateconvertB = $dantaiService->changeDay($val['round2Day2ExamDate']->format('D'));
            $data[$val['id']]['examDate2Round'] = $this->translate->translate('round1') . $data[$val['id']]['examDate'];

            if(! isset($dantaiService->getDateRound2EachKyu()[$val['eikenLevelId']])) continue;

            if($dantaiService->getDateRound2EachKyu()[$val['eikenLevelId']] == 1){
                $data[$val['id']]['examDate2Round'] .= '<br>'
                    . $this->translate->translate('round2') 
                    .$this->getExamDate($val['round2Day1ExamDate'], 'Round2Day1ExamDate') 
                    .  '（'. $dateconvertA .'）';
            }else{
                $data[$val['id']]['examDate2Round'] .= '<br>'
                    . $this->translate->translate('round2') 
                    . $this->getExamDate($val['round2Day2ExamDate'], 'Round2Day2ExamDate')
                    . '（'. $dateconvertB .'）';
            }
        }
        return $data;
    }

    public function paymentInformationStatus($pupilId, $eikenScheduleId)
    {
        if (empty($pupilId) || empty($eikenScheduleId)) {
            return 0;
        }
        $applyEikenLevel = $this->em->getRepository('Application\Entity\ApplyEikenLevel')->findBy(array('pupilId' => $pupilId, 'eikenScheduleId' => $eikenScheduleId, 'isDelete' => 0, 'isRegister' => 1), array('eikenLevelId' => 'ASC'));

        return empty($applyEikenLevel) ? 0 : count($applyEikenLevel);
    }
    
    /**
     *
     * @param int $orgId            
     * @param int $total            
     * @param array $requester
     *            Array(
     *            'email' => 'someone@somehwere.com',
     *            'name' => 'requester name',
     *            'active' => 'If sqs type is combinni then 0 else total'
     *            )
     * @return int
     *          1 When record exists
     *          -1 When record does not exists
     */
    public function addProcessLogRecord($pupilId, $scheduleId, $total, $requester = array('email' => '', 'name' => '', 'active' => 0))
    {
        $em = $this->em;
        
        $plRepo = $em->getRepository('Application\Entity\ProcessLog');
        $processLog = $plRepo->findOneBy(array(
            'pupilId' => $pupilId,
            'scheduleId' => $scheduleId
        ));
        if ($processLog) {
            $processLog->setTotal($processLog->getTotal() + $total);
            $em->persist($processLog);
            $em->flush();
            $em->clear();
            
            return 1;
        } else {
            $processLog = new \Application\Entity\ProcessLog();
            $processLog->setPupilId($pupilId);
            $processLog->setScheduleId($scheduleId);
            $processLog->setTotal($total);
            $processLog->setActive($requester['active']);
            
            $em->persist($processLog);
            $em->flush();
            $em->clear();
            
            return -1;
        }
        
        return -1;
    }
    
    public function getKyuNeedGenCombini($scheduleId, $pupilId, $listKyu)
    {
        $listKyu2 = array();
        
        foreach ($listKyu as $kyu) {
            $result = $this->getReceiptNoTelNo($pupilId, $scheduleId, $kyu);
            if (!$result) {
                array_push($listKyu2, $kyu);
            }
        }
        
        return $listKyu2;
    }
    
    public function getReceiptNoTelNo($pupilId, $eikenScheduleId, $eikenLevelId) {
        $paymentInfo = $this->em->getRepository('Application\Entity\PaymentInfo')->findOneBy(array(
            'pupilId' => $pupilId,
            'eikenScheduleId' => $eikenScheduleId,
        ));
        
        if (empty($paymentInfo)) {
            return false;
        }
        $applyEikenLevel = $this->em->getRepository('Application\Entity\ApplyEikenLevel')
                ->findOneBy(
                    array('pupilId'         => $pupilId,
                          'eikenScheduleId' => $eikenScheduleId,
                          'isDelete'        => 0,
                          'eikenLevelId'    => $eikenLevelId),
                    array('eikenLevelId' => 'ASC')
                );
        if (empty($applyEikenLevel)) {
            return false;
        }
        $issuingPayment = $this->em->getRepository('Application\Entity\IssuingPayment')->findOneBy(array(
            'paymentInfoId' => $paymentInfo->getId(),
            'eikenLevelId' => $eikenLevelId,
            'price' => $applyEikenLevel->getTuitionFee()
            ),
            array('updateAt' => 'Desc')
        );
        if (empty($issuingPayment)) {
            return false;
        }
        return array('telNo' => $issuingPayment->getTelNo(), 'receiptNo' => $issuingPayment->getReceiptNo());
    }

    public function checkCurrentDate($deadline)
    {
        $formatDatetime = DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT;
        if (!empty($deadline['deadlineForm']) && !empty($deadline['deadlineTo']) && date($formatDatetime) >= $deadline['deadlineForm']->format($formatDatetime) && date($formatDatetime) <= date($formatDatetime, strtotime($deadline['deadlineTo']->format($formatDatetime)))) {
            return false;
        }

        return true;
    }

    public function checkCombiniDateline($deadline)
    {
        $formatDatetime = DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT;
        if (!empty($deadline['deadlineForm']) && !empty($deadline['combiniDeadline']) && date($formatDatetime) >= $deadline['deadlineForm']->format($formatDatetime) && date($formatDatetime) <= date($formatDatetime, strtotime($deadline['combiniDeadline']->format($formatDatetime)))) {
            return false;
        }

        return true;
    }

    public function checkCreditDateline($deadline)
    {
        $formatDatetime = DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT;
        if (!empty($deadline['deadlineForm']) && !empty($deadline['creditDeadline']) && date($formatDatetime) >= $deadline['deadlineForm']->format($formatDatetime) && date($formatDatetime) <= date($formatDatetime, strtotime($deadline['creditDeadline']->format($formatDatetime)))) {
            return false;
        }

        return true;
    }
    
    public function blockGenCombini($pupilId, $eikenScheduleId, $eikenLevelId) {
        $applyEikenLevel = $this->em->getRepository('Application\Entity\ApplyEikenLevel')
                ->findOneBy(array(
                    'pupilId' => $pupilId,
                    'eikenScheduleId' => $eikenScheduleId,
                    'eikenLevelId' => $eikenLevelId,
                    'isRegister' => 1,
                    'isDelete' => 0,
                ));
        if(empty($applyEikenLevel)){
            return false;
        }
        $applyEikenLevel->setBlockCombini(1);
        $this->em->persist($applyEikenLevel);
        $this->em->flush();
    }
    
    public function isBlockGenCombini($pupilId, $eikenScheduleId, $eikenLevelId) {
        $applyEikenLevel = $this->em->getRepository('Application\Entity\ApplyEikenLevel')
                ->findOneBy(array(
                    'pupilId' => $pupilId,
                    'eikenScheduleId' => $eikenScheduleId,
                    'eikenLevelId' => $eikenLevelId,
                    'isRegister' => 1,
                    'isDelete' => 0,
                ));
        if(empty($applyEikenLevel)){
            return false;
        }
        
        return empty($applyEikenLevel->getBlockCombini()) ? false : true ;
    }
    public function getOrgSchoolYearIDbyPupilId($pupilId)
    {
        $objPupil= $this->em->getRepository('Application\Entity\Pupil')->findOneBy(array(
            'id' => $pupilId
        ));
        if(empty($objPupil)){
            return false;
        }
        return $objPupil->getOrgSchoolYearId();
    }

    public function getListPaid($pupilId, $eikenScheduleId, $eikenLevelIds){
        return $this->em->getRepository('Application\Entity\ApplyEikenLevel')->getListPaid($pupilId, $eikenScheduleId, $eikenLevelIds);
    }
}