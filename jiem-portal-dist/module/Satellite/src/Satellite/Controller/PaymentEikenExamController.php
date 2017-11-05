<?php
/*
 * @author : Huy Manh (ManhNH5)
 */
namespace Satellite\Controller;

use Satellite\Service\PaymentEikenExamService;
use Zend\View\Model\ViewModel;
use Dantai\PrivateSession;
use Application\Service\DantaiService;
use Satellite\Constants;
use Dantai\Utility\CharsetConverter;
use Satellite\Form\PayByCreditForm;

/**
 * Class PaymentEikenExamController
 * @package Satellite\Controller
 */
class PaymentEikenExamController extends BaseController
{

    /**
     * @var PaymentEikenExamService
     */
    protected $paymentEikenExam;

    protected $privateSession;

    protected $dantaiService;

    protected $entityManager;
    
    protected $userIdentity;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->paymentEikenExam = new PaymentEikenExamService($this->getServiceLocator());
        $this->privateSession = new PrivateSession();
        $this->dantaiService = new DantaiService();
        $this->dantaiService->setServiceLocator($this->getServiceLocator());
        $this->entityManager = $this->getEntityManager();
        $this->userIdentity = $this->privateSession->getData(Constants::SESSION_SATELLITE);

        return parent::onDispatch($e);
    }

    public function payByCreditAction()
    {
        $this->privateSession->clear('applyEikenId');
        if ($this->layout()->getVariable('paymentByCreditFlag')) {
            $data = $this->userIdentity;
            $doubleEikenNotSupport = 0;
            $applyEikenId = $this->params('id', null);
            // check payment credit card deadline.
            if ($this->paymentEikenExam->checkCreditDateline($this->userIdentity['deadline'])) {
                return new ViewModel(array(
                                         'errorMessage' => $this->translate('msgCreditDeadlineExpire'),
                                     ));
            }
            if (isset($data['doubleEiken']) && $data['doubleEiken'] == Constants::DOUBLE_EIKEN) {
                $doubleEikenNotSupport = 1;
            }
            $applyEikenLevel = null;
            $applyEikenId = $applyEikenId ? $applyEikenId : $this->privateSession->getData('applyEikenId');
            if (!empty($applyEikenId)) {
                $this->privateSession->setData('applyEikenId', $applyEikenId);
                $applyEikenLevel = $this->entityManager->getRepository('Application\Entity\ApplyEikenLevel')->getEikenLevel($data['pupilId'], $data['eikenScheduleId'], $applyEikenId, 0);
            }
            else {
                $this->privateSession->clear('applyEikenId');
                $applyEikenLevel = $this->entityManager->getRepository('Application\Entity\ApplyEikenLevel')->getEikenLevel($data['pupilId'], $data['eikenScheduleId'], null, 0);
            }
            $applyEikenLevelList = array();
            foreach ($applyEikenLevel as $level) {
                array_push($applyEikenLevelList, array(
                    'eikenLevelId' => $level['eikenLevelId'],
                    'price'        => $level['tuitionFee'],
                    'hallType'     => $level['hallType']
                ));
            }
            // get orgShooYearID
            $orgSchoolYearId = $this->paymentEikenExam->getOrgSchoolYearIDbyPupilId($data['pupilId']);
            $pramPrice = array(
                'orgNo' => $data['organizationNo'],
                'orgSchoolYearId'=>$orgSchoolYearId,
                'year'=>$data['deadline']['year'],
                'kai'=>$data['deadline']['kai']);

            $eikenLevelPrice = $this->dantaiService->getListPriceOfOrganization($data['organizationNo'], array(1, 2, 3, 4, 5, 6, 7),$pramPrice);
            $examDate = $this->entityManager->getRepository('Application\Entity\EikenSchedule')->find($data['eikenScheduleId']);
            $kyu = $this->paymentEikenExam->mappingMultiKyuHallType($eikenLevelPrice[0], $examDate, $applyEikenLevelList, $data['hallTypeExamDay']);
            $this->privateSession->setData(Constants::LIST_KYU_PRICE, $kyu);
            $errorMsg = $this->flashMessenger()->getMessages();
            $data = (!$this->privateSession->isEmpty(Constants::PAYMENT_EIKEN_EXAN)) ? $this->privateSession->getData(Constants::PAYMENT_EIKEN_EXAN) : '';
            $this->privateSession->clear(Constants::PAYMENT_EIKEN_EXAN);
            $form = new PayByCreditForm();
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            $total = array('price' => 0, 'priceName' => '');
            foreach ($kyu as $kyuId => $val)
                $total['price'] += (int)$val['price'];
            $total['priceName'] = sprintf($translator->translate('priceName'), number_format($total['price']));
            $this->privateSession->setData(Constants::TOTAL_KYU, $total);
            if (!$kyu) {
                return $this->redirect()->toUrl('/');
            }
            $this->layout('layout/' . ($this->isMobile ? 'mobile' : 'layout'));
            $paymentInfomationStatus = $this->layout()->getVariable('paymentInfomationStatus');
            $this->privateSession->setData(Constants::CSRF_TOKEN_SERVER, md5(time()));
            $this->privateSession->setData(Constants::CSRF_TOKEN_SERVER_CONFIRM, md5(time()));

            return new ViewModel(array('csrfToken' => $this->privateSession->getData(Constants::CSRF_TOKEN_SERVER_CONFIRM), 'paymentInfomationStatus' => $paymentInfomationStatus, 'doubleEikenNotSupport' => $doubleEikenNotSupport, 'isMobile' => $this->isMobile, 'data' => $data, 'form' => $form, 'kyu' => $kyu, 'translate' => $this->getTranslation(), 'errorMsg' => (!empty($errorMsg) ? current($errorMsg) : ''), 'total' => $total));
        }

        return $this->redirect()->toRoute('satellite');
    }

    public function paymentConfirmAction()
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        $data = ($request->isPost()) ? $request->getPost() : $this->privateSession->getData(Constants::PAYMENT_EIKEN_EXAN);        
        if ($data && $data['csrfToken'] == $this->privateSession->getData(Constants::CSRF_TOKEN_SERVER_CONFIRM)) {
            
            $partFolder = $this->userIdentity['deadline']['year'] . $this->userIdentity['deadline']['kai'] . "/" . $this->userIdentity['organizationNo'];                
            $logData = (array)$data;
            $logData['cardNumber'] = '************';
            $logData['cardCvv'] = '***';
            $this->dantaiService->writeLog(\Satellite\Constants::LOG_PAYMENT_CREDIT, $partFolder, $logData, 'paymentConfirmAction', 'DATA WHENE GO TO PAYMENT CONFIRM PAGE FOR DANTAINO : ' . $this->userIdentity['organizationNo'] . ' PUPILID : ' . $this->userIdentity['pupilId']);
            
            $inputFilter = new PayByCreditForm();
            $inputFilter->setData($data);
            if ($this->paymentEikenExam->validate($data) && $inputFilter->isValid()) {
                $data['cardMonth'] = $this->setCardMonth($data['cardMonth']);
                $data['cardYearShow'] = substr($data['cardYear'], -2, 2);
                $data['cardYear'] = $this->setCardYear($data['cardYear']);
                $data['cardFirstName'] = trim($data['cardFirstName']);
                $data['cardLastName'] = trim($data['cardLastName']);
                $data['cardNumber'] = trim($data['cardNumber']);
                $data['kyu'] = $this->privateSession->getData(Constants::LIST_KYU_PRICE);
                $this->privateSession->setData(Constants::PAYMENT_EIKEN_EXAN, $data);
                $this->privateSession->setData(Constants::CSRF_TOKEN_SERVER_CONFIRM, md5(time()));
                $data['csrfToken'] = $this->privateSession->getData(Constants::CSRF_TOKEN_SERVER_CONFIRM);
                $data['total'] = $this->privateSession->getData(Constants::TOTAL_KYU);
                $econPayByCreditMessage = $this->flashMessenger()->getMessages();
                if ($econPayByCreditMessage) {
                    $econPayByCreditMessage = current($econPayByCreditMessage);
                    $data['errorMsg'] = isset($econPayByCreditMessage['message']) ? $econPayByCreditMessage['message'] : '';
                    $data['redirect'] = $econPayByCreditMessage['status'] == Constants::KYU_PAID_ERROR ? '' : 'payment-eiken-exam/pay-by-credit';
                    $this->flashMessenger()->addMessage($data['errorMsg']);
                    if (isset($econPayByCreditMessage['status']) && $econPayByCreditMessage['status'] == Constants::PAYMENT_STATUS_SUCCESS) {
                        $data['errorMsg'] = $translator->translate('MSG26');
                        $data['redirect'] = '';
                        $this->privateSession->clear(Constants::PAYMENT_EIKEN_EXAN);
                        $this->privateSession->clear(Constants::LIST_KYU_PRICE);
                        $this->privateSession->clear(Constants::TOTAL_KYU);
                        $this->flashMessenger()->clearCurrentMessages();
                    }
                }
                $this->layout('layout/' . ($this->isMobile ? 'mobile' : 'layout'));

                return new ViewModel(array('isMobile' => $this->isMobile, 'data' => $data));
            }
        }
        $this->flashMessenger()->addMessage($translator->translate('MSG0'));

        return $this->redirect()->toRoute('satellite/default', array('controller' => 'payment-eiken-exam', 'action' => 'pay-by-credit'));
    }

    private function setCardMonth($cardMonth)
    {
        if ($cardMonth < 10) {
            return '0' . intval($cardMonth);
        }

        return $cardMonth;
    }

    private function setCardYear($cardYear)
    {
        if ($cardYear < 100) {
            return substr(date('Y'), 0, 2) . intval($cardYear);
        }

        return $cardYear;
    }

    public function getTranslation()
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array(
            'MSG1'  => $translator->translate('MSG1'),
            'MSG2'  => $translator->translate('MSG2'),
            'MSG3'  => $translator->translate('MSG3'),
            'MSG4'  => $translator->translate('MSG4'),
            'MSG5'  => $translator->translate('MSG5'),
            'MSG6'  => $translator->translate('MSG6'),
            'MSG22' => $translator->translate('MSG22'),
            'MSG23b' => $translator->translate('MSG23b'),
            'MSG27' => $translator->translate('MSG27'),
            'MSG42' => $translator->translate('MSG42'),
            'SGHMSG48' => $translator->translate('SGHMSG48'),
            'geKyuPaymentMSG5' => $translator->translate('geKyuPaymentMSG5'),
            'geKyuPaymentMSG56' => $translator->translate('geKyuPaymentMSG56'),
            'MsgRequired3' => $translator->translate('MsgRequired3'),
            'MsgRequired4' => $translator->translate('MsgRequired4'),
            'InvalidBirthday' => $translator->translate('InvalidBirthday'),
            'MsgInvalidEmail' => $translator->translate('MsgInvalidEmail'),
            'MsgRequiredNumber' => $translator->translate('MsgRequiredNumber'),
            'studentHadChangeHallType' => $translator->translate('studentHadChangeHallType'),
            'input20FullSizeNameKanji' => $translator->translate('input20FullSizeNameKanji'),
            'R4_MSG28' => $translator->translate('R4_MSG28'),
            'R4_MSG32' => $translator->translate('R4_MSG32'),
            'MSG_PAYMENT' => $translator->translate('MSG_PAYMENT'),
            'MSG_APPLY_SUCCESS' => $translator->translate('MSG_APPLY_SUCCESS'),
            'PAY_NOW' => $translator->translate('PAY_NOW'),
            'payByCombini' => $translator->translate('payByCombini'),
            'PAY_LATER' => $translator->translate('PAY_LATER'),
            'msgCheckStringKana' => $translator->translate('msgCheckStringKana'),
            'msgInput11DigitOfPhoneNumber' => $translator->translate('msgInput11DigitOfPhoneNumber'),
            'ZipCode_Not_Found' => $translator->translate('ZipCode_Not_Found'),
            'msgDisplayReceiptNo' => $translator->translate('msgDisplayReceiptNo'),
            'msgWaitReceiptNo' => $translator->translate('msgWaitReceiptNo')
        );

        return json_encode($messages);
    }

    public function econPayByCreditAction() {
        $queryToken = $this->params()->fromQuery('token');
        if ($this->privateSession->getData(Constants::CSRF_TOKEN_SERVER)) {
            $params = $this->privateSession->getData(Constants::PAYMENT_EIKEN_EXAN);
            $sessionToken = $this->privateSession->getData(Constants::CSRF_TOKEN_SERVER_CONFIRM);
        }
        // validate token submit
        if($queryToken !== $sessionToken){
            return $this->redirect()->toUrl('/');
        }

        $this->privateSession->clear(Constants::CSRF_TOKEN_SERVER);
        $eikenScheduleId = isset($this->userIdentity['eikenScheduleId']) ? $this->userIdentity['eikenScheduleId'] : 0;
        $pupilId = isset($this->userIdentity['pupilId']) ? $this->userIdentity['pupilId'] : 0;
        $organizationNo = isset($this->userIdentity['organizationNo']) ? $this->userIdentity['organizationNo'] : 0;
        if (!$params || $organizationNo == 0 || $eikenScheduleId == 0 || $pupilId == 0) {
            return $this->redirect()->toRoute('satellite/default', array(
                'controller' => 'payment-eiken-exam',
                'action'     => 'pay-by-credit'
            ));
        }
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        // validate payment status
        $eikenLevels = array_keys($params['kyu']);
        $paidLevels = $this->paymentEikenExam->getListPaid($pupilId, $eikenScheduleId, $eikenLevels);
        // if there aren't paid level, continue process payment
        if(empty($paidLevels)){
            $orderId = $this->paymentEikenExam->generateOrderId($organizationNo);
            $telNo = $this->paymentEikenExam->getTelNoExistInPayment($pupilId, $eikenScheduleId);
            if (empty($telNo)) {
                $telNo = $this->paymentEikenExam->generateTelNo($organizationNo);
            }
            if (strlen($telNo) >= 12 && substr($telNo, 0, 1) == 0) {
                $telNo = substr($telNo, -11, 11);
            }
            $parameterApi = $this->paymentEikenExam->getParameterOfApiCredit($params, $orderId, $telNo);
            
            $partFolder = $this->userIdentity['deadline']['year'] . $this->userIdentity['deadline']['kai'] . "/" . $this->userIdentity['organizationNo'];                
            $logData = (array)$parameterApi;
            $logData['econCardno'] = '************';
            $logData['CVV2'] = '***';
            
            $this->dantaiService->writeLog(\Satellite\Constants::LOG_PAYMENT_CREDIT, $partFolder, $logData, 'econPayByCreditAction', 'DATA SEND API PAY BY CREDIT FOR DANTAINO : ' . $this->userIdentity['organizationNo'] . ' PUPILID : ' . $this->userIdentity['pupilId']);
            $response = $this->paymentEikenExam->callApiCredit($parameterApi);

            if ($response['status'] == Constants::PAYMENT_STATUS_SUCCESS) {
                $paymentStatus = $this->paymentEikenExam->createNewPayment($pupilId, $eikenScheduleId, $params['kyu'], $orderId, $telNo);
                //update payment information status session use layout
                $user = $this->userIdentity;
                $user['paymentInformation'] = $this->paymentEikenExam->paymentInformationStatus($pupilId, $eikenScheduleId);
                PrivateSession::setData(Constants::SESSION_SATELLITE, $user);
                if ($paymentStatus != true) {
                    $response['status'] = 0;
                    $response['message'] = $translator->translate('MSG0');
                }
            }
        }else{
            $response['status'] = Constants::KYU_PAID_ERROR;
            $response['message'] = $translator->translate('msgErrorPaidLevel');
        }
        $this->flashMessenger()->addMessage($response);

        return $this->redirect()->toRoute('satellite/default', array(
            'controller' => 'payment-eiken-exam',
            'action'     => 'payment-confirm'
        ));
    }

    public function paymentInfomationAction()
    {
        $paymentInfo = $this->paymentEikenExam->getPaymentInformationMuntilKyu($this->userIdentity);
        $config = $this->getServiceLocator()->get('config');
        ksort($config['MappingLevel']);
        $sessSTL = PrivateSession::getData(Constants::SESSION_SATELLITE);

        $this->layout('layout/' . ($this->isMobile ? 'mobile' : 'layout'));

        $isSupportCombini = $isSupportCredit = 0;
        $prePersonalPayment = $this->userIdentity['personalPayment'];
        
        if (!empty($prePersonalPayment) && (strpos($prePersonalPayment, '0') !== false)) {
            $isSupportCredit = 1;
        }
        if (!empty($prePersonalPayment) && (strpos($prePersonalPayment, '1') !== false)) {
            $isSupportCombini = 1;
        }
        $translator = $this->getServiceLocator()->get('MVCTranslator');

        $messageDeadLine = '';
        if ($isSupportCombini && $isSupportCredit) {
            $messageDeadLine = sprintf($translator->translate('msgStandardBothCombiniCredit'), date('Y/m/d', strtotime($this->userIdentity['deadline']['combiniDeadline']->format('Y/m/d'))), date('Y/m/d', strtotime($this->userIdentity['deadline']['creditDeadline']->format('Y/m/d'))));
        } elseif ($isSupportCombini) {
            $messageDeadLine = sprintf($translator->translate('msgStandardOnlyCombiniOrCredit'), date('Y/m/d', strtotime($this->userIdentity['deadline']['combiniDeadline']->format('Y/m/d'))));
        } elseif ($isSupportCredit) {
            $messageDeadLine = sprintf($translator->translate('msgStandardOnlyCombiniOrCredit'), date('Y/m/d', strtotime($this->userIdentity['deadline']['creditDeadline']->format('Y/m/d'))));
        }

        $messagePaymentKyu = $msgCombiniDeadline = $msgCreditDeadline = '';
        if ($this->paymentEikenExam->checkCurrentDate($this->userIdentity['deadline'])) {
            $messagePaymentKyu = sprintf($translator->translate('eikenApplicationEndDateLt8'), $this->userIdentity['deadline']['year'], $this->userIdentity['deadline']['kai']);
        }
        if ($this->paymentEikenExam->checkCombiniDateline($this->userIdentity['deadline'])) {
            $msgCombiniDeadline = sprintf($translator->translate('msgCombiniDeadlineExpire'));
        }
        if ($this->paymentEikenExam->checkCreditDateline($this->userIdentity['deadline'])) {
            $msgCreditDeadline = sprintf($translator->translate('msgCreditDeadlineExpire'));
        }

        return new ViewModel(
            array(
                'isSupportCredit'    => $isSupportCredit,
                'isSupportCombini'   => $isSupportCombini,
                'data'               => $paymentInfo,
                'isMobile'           => $this->isMobile,
                'translate'          => $this->getTranslation(),
                'messageDeadLine'    => $messageDeadLine,
                'messagePaymentKyu'  => $messagePaymentKyu,
                'msgCombiniDeadline' => $msgCombiniDeadline,
                'msgCreditDeadline'  => $msgCreditDeadline,
                'mappingLevel' => $config['MappingLevel'],
                'kyuIdAppliedNonDS' =>$sessSTL['kyuIdAppliedNonDS']
            ));
    }
    // function : sendMessageToSqsAction
    // param :
    // Description : process action to send message to amazon sqs for regening receiptNo
    public function sendMessageToSqsAction()
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $data = $this->userIdentity;
        
        $orgId = $this->userIdentity['organizationId'];
        $eikenScheduleId = $this->userIdentity['eikenScheduleId'];
        $pupilId = $this->userIdentity['pupilId'];
        $em = $this->getEntityManager();
        $listKyu = $this->params()->fromPost('listKyu', 0);

        // check deadline
        if ($this->paymentEikenExam->checkCombiniDateline($this->userIdentity['deadline'])) {
            $message = sprintf($translator->translate('msgCombiniDeadlineExpire'));
            return $response->setContent(json_encode($message));
        }

        // check block combini flag
        if(count($listKyu) == 1 && $this->paymentEikenExam->isBlockGenCombini($pupilId, $eikenScheduleId, $listKyu[0])){
            $message = $translator->translate('msgWaitReceiptNo');
            return $response->setContent(json_encode($message));
        }

        // get orgShooYearID
        $orgSchoolYearId = $this->paymentEikenExam->getOrgSchoolYearIDbyPupilId($pupilId);
        $pramPrice = array(
            'orgNo' => $data['organizationNo'],
            'orgSchoolYearId'=>$orgSchoolYearId,
            'year'=>$data['deadline']['year'],
            'kai'=>$data['deadline']['kai']);

        // not need to gen new ReceiptNo if price not change.
        $listKyu2 = $this->paymentEikenExam->getKyuNeedGenCombini($eikenScheduleId, $pupilId, $listKyu);
        if(count($listKyu2) == 0){
            $eikenLevelPrice = $this->dantaiService->getListPriceOfOrganization($this->userIdentity['organizationNo'], $listKyu,$pramPrice);
            $message = $translator->translate('msgDisplayReceiptNo1');
            $messageReceiptNo = $translator->translate('receiptNoLabel').':';
            $messageTelNo = '';
            foreach ($listKyu as $kyu) {
                $receiptNoTelNo = $this->paymentEikenExam->getReceiptNoTelNo($pupilId, $eikenScheduleId, $kyu);
                $messageReceiptNo .= '　'.$eikenLevelPrice[1][$kyu]['name'].': '.$receiptNoTelNo['receiptNo'];
                $messageTelNo = $translator->translate('telNoLabel').':　'.$receiptNoTelNo['telNo'];
            }
            $message = $message.'<br>'.$messageReceiptNo.'<br>'.$messageTelNo.'<br>'.$translator->translate('msgDisplayReceiptNo2');
            return $response->setContent(json_encode($message));
        }else{
            foreach ($listKyu2 as $kyu) {
                $this->paymentEikenExam->blockGenCombini($pupilId, $eikenScheduleId, $kyu);
            }
        }

        // Send SQS when template is not eiken, shool version        
        // Add one process log record
        $processExists = $this->paymentEikenExam->addProcessLogRecord($pupilId, $eikenScheduleId, 1, array(
            'active' => 0
        ));

        $pupil = $em->getRepository('\Application\Entity\Pupil')->findOneBy(array(
            'id' => $pupilId,
        ));

        // send message queue gen combini to AWS.
        $listEikenLevel = json_decode($data['listEikenLevel'], true);
        // get orgShooYearID
        $priceLevels = $this->dantaiService->getListPriceOfOrganization($this->userIdentity['organizationNo'], $listEikenLevel,$pramPrice);
        $messages[] = array(
            'Id' => $pupil->getClassId(),
            'MessageBody' => \Zend\Json\Encoder::encode(array(
                'classId' => $pupil->getClassId(),
                'orgId' => $orgId,
                'scheduleId' => $eikenScheduleId,
                'startIndex' => 1,
                'priceLevels' => $priceLevels,
                'pupilId' => $pupilId,
                'listKyu' => $listKyu2,
            ))
        );

        $sqsMessages = array(
            'QueueUrl' => \Dantai\Aws\AwsSqsClient::QUEUE_GEN_COMBIBI,
            'Entries' => array(
                reset($messages)
            )
        );
        \Dantai\Aws\AwsSqsClient::getInstance()->sendMessageBatch($sqsMessages);

       
        return $response->setContent(json_encode('true'));
    }
    
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function translate($key){
        return $this->getServiceLocator()->get('MVCTranslator')->translate($key);
    }
}
