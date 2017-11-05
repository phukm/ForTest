<?php
namespace Eiken\Controller;

use Application\Entity\Repository\ApplyEikenLevelRepository;
use Zend\Mvc\Controller\AbstractActionController;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Dantai\PrivateSession;
use Zend\View\Model\ViewModel;
use Eiken\Form\Payment\PaymentStatusForm;
use Zend\Paginator\Adapter\ArrayAdapter;
use Application\Entity\OrgSchoolYear;
use Doctrine\ORM\EntityManager;
use Application\Entity\ApplyEikenLevel;
use Zend\Json\Json;
use Eiken\Service\ServiceInterface\PaymentServiceInterface;
use Application\Entity\RetrieveBillingInfo;
use Application\Entity\ApplyEikenPersonalInfo;
use Application\Entity\Pupil;
use Dantai\Utility\PHPExcel;
use Dantai\Utility\CharsetConverter;
use Application\Entity\Organization;
use History\Service\ExportExcelMapper;

/**
 * PaymentController
 *
 * Uthv
 *
 * 1.0
 */
class PaymentController extends AbstractActionController
{
    use \Application\Controller\ControllerAwareTrait;
    
    protected $id_org = 0;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var PaymentServiceInterface
     */
    protected $paymentService;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, PaymentServiceInterface $paymentService, EntityManager $entityManager)
    {
        $this->dantaiService = $dantaiService;
        $this->paymentService = $paymentService;
        $this->em = $entityManager;
        $user = PrivateSession::getData('userIdentity');
        $this->id_org = $user['organizationId'];
    }

    public function index()
    {}
        
    public function _trimSpaceUnicode($string)
    {
        return preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $string);
    }

    public function getkaiAction()
    {
        $em = $this->getEntityManager();
        $yearId = $this->params()->fromQuery('year');
        $listkai = $em->getRepository('Application\Entity\EikenSchedule')->getKaiByYear($yearId);
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($listkai));
        return $response;
    }

    /**
     * Show all payment status for pupil on sateline with keyword is empty
     *
     * @author UtHV
     * @param
     *
     * @return data of view
     *         Author Modified Start date End date
     *         UTHV Creates 2015-07-11 2015-07-11
     */
    public function paymentstatusAction()
    {
        $em = $this->getEntityManager();
        $config = $this->getServiceLocator()->get('config');
        $form = new PaymentStatusForm();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $paramYear = 0;
        $paramKai = 0;
        $classId = 0;
        $schoolyearId = 0;
        $paymentStatus = null;
        $applyStatus = null;
        $fullName = null;
        $search = null;
        $token = null;
        $query = '';
        $arrQuery = array();
        $eikenLevelId = $this->params()->fromQuery('eikenLevelId', 0);
        $testSite = $this->params()->fromQuery('testSite');
        if ($this->getRequest()->getQuery('year'))
            $paramYear = $this->getRequest()->getQuery('year');
        if ($this->getRequest()->getQuery('kai'))
            $paramKai = $this->getRequest()->getQuery('kai');
        if ($testSite) {
            $arrQuery['testSite'] = $testSite;
            $query .= '&testSite=' . $testSite;
        }
        if ($paramYear) {
            $arrQuery['year'] = $paramYear;
            $query .= '&year=' . $paramYear;
        }
        if ($paramKai) {
            $arrQuery['kai'] = $paramKai;
            $query .= '&kai=' . $paramKai;
        }
        if ($eikenLevelId) {
            $arrQuery['eikenLevelId'] = $eikenLevelId;
            $query .= '&eikenLevelId=' . $eikenLevelId;
        }
        $backUrl = $this->params()->fromQuery('backUrl', 0);
        if ($backUrl) {
            $arrQuery['backUrl'] = $backUrl;
            $query .= '&backUrl=' . $backUrl;
        }
        $idDetails = $this->params()->fromQuery('dtId', 0);
        if ($idDetails) {
            $arrQuery['dtId'] = $idDetails;
            $query .= '&dtId=' . $idDetails;
        }
        $payStatus = $this->params()->fromQuery('pay');
        if ($payStatus != '') {
            $arrQuery['pay'] = $payStatus;
            $paymentStatus = $payStatus;
            $query .= '&pay=' . $payStatus;
        }
        else{
            $regStatus = $this->params()->fromQuery('regist');
            if ($regStatus != '') {
                $arrQuery['regist'] = $regStatus;
                $applyStatus = $regStatus;
                $query .= '&regist=' . $regStatus;
            }
        }

        $query = ltrim($query, '&');
        $search = $this->dantaiService->getSearchCriteria($this->getEvent(), $this->params()->fromPost());
        if ($this->isPost() && $search['token']) {
            return $this->redirect()->toRoute('payment', array( 
                'action' => 'paymentstatus', 
                'search'=> $search['token']),
                    array('query'=>$arrQuery));
        }
        $eikenScheduleId = $this->params()->fromRoute('id', 0);
        if (! empty($search)) {
            if (isset($search['eikenScheduleId'])){
                $eikenScheduleId = $search['eikenScheduleId'];
            }
            if (isset($search['year'])){
                $paramYear = $search['year'];
            }

            if (isset($search['kai'])){
                $paramKai = $search['kai'];
            }
            
            if (isset($search['testSite']))
                $testSite = $search['testSite'];

            if (isset($search['examGrade']))
                $eikenLevelId = $search['examGrade'];

            if (isset($search['ddlClass']))
                $classId = $search['ddlClass'];

            if (isset($search['ddlSchoolYear']))
                $schoolyearId = $search['ddlSchoolYear'];

            if (isset($search['ddlPaymentStatus']))
                $paymentStatus = $search['ddlPaymentStatus'];

            if (isset($search['ddlApplyStatus']))
                $applyStatus = $search['ddlApplyStatus'];

            if (isset($search['txtFullName']))
                $fullName =$this->_trimSpaceUnicode($search['txtFullName']);
        }
        $postRequest = $this->params()->fromPost();
        if ($paramKai === 0 && $eikenScheduleId != 0 || (!empty($postRequest) && $postRequest['isReset'] === 'true')) {
            $objEikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->find($eikenScheduleId);
            if ($objEikenSchedule != NULL) {
                $paramYear = $objEikenSchedule->getYear();
                $paramKai = $objEikenSchedule->getKai();
            }
        }
        $schoolyear = $em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->id_org);
        $yearschool = array();
        if (isset($schoolyear)) {
            foreach ($schoolyear as $key => $value) {
                $yearschool[$value['id']] = $value['displayName'];
            }
        }
        $form->get("examGrade")->setValue($eikenLevelId);
        $form->get("ddlSchoolYear")
                ->setValueOptions($yearschool)
                ->setValue($schoolyearId);
        $form->get("ddlPaymentStatus")
                ->setValueOptions($config['Payment_Status'])
                ->setValue($paymentStatus);
        $form->get("ddlApplyStatus")
                ->setValueOptions($config['Apply_Status'])
                ->setValue($applyStatus);

        $data = $em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYear($schoolyearId, $paramYear, $this->id_org);
        $sourceClass = array();
        if ($data) {
            foreach ($data as $key => $value) {
                $sourceClass[$value['id']] = $value['className'];
            }
        }
        $form->get("testSite")->setValue($testSite);
        $form->get("ddlClass")
                ->setValueOptions($sourceClass)
                ->setValue($classId);
        $form->get("txtFullName")->setValue($fullName);

        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;

        /**
         * @var ApplyEikenLevelRepository $applyeikenrepository
         */
        $applyeikenrepository = $em->getRepository('Application\Entity\ApplyEikenLevel');
        // Fix issue F1GJIEM-1594
        $countbykyu = $applyeikenrepository->getCountPaymentStatusByEikenLevel($this->id_org, $eikenScheduleId);
        $listpupil = $applyeikenrepository->getPagedPaymentStatus($this->id_org, $eikenLevelId, $classId, $schoolyearId, $paymentStatus, $applyStatus, addslashes($fullName), $paramYear, $testSite, intval($eikenScheduleId));
        
        
        $flashMessages = $this->flashMessenger()->getMessages();
        $messages = '';
        if ($flashMessages) {
            $messages = $flashMessages[0]['noRecordExcel'];
        }
        
        $isExportExcel = $this->params()->fromQuery('isExportExcel');
        if ($isExportExcel == 1) {
            return $this->exportPaymentStatus($listpupil->getAllItems(),$paramYear,$paramKai);
        }
        
        $titlePageList = '支払情報';
        if(!empty($paramYear)){
            $titlePageList = $titlePageList.' - '.$paramYear.'年度第';
        }
        if(!empty($paramKai)){
            $titlePageList = $titlePageList.$paramKai.'回';
        }
        $lstData = $listpupil->getItems($offset, $limit);
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'form' => $form,
            'eikenSchedule' => $eikenScheduleId,
            'noRecord' => $translator->translate('MSG13'),
            'paginator' => $listpupil,
            'listpupil' => $lstData,
            'backUrl' => $backUrl,
            'dtId' => $idDetails,
            'data' => $countbykyu,
            'page' => $page,
            'numPerPage' => $limit,
            'searchVisible' => isset($search['token']) ? 1 : 0,
            'param' => isset($search['token']) ? $search['token'] : '',
            'query' =>($query)?$query:'',
            'eikenLevelId' => $eikenLevelId,
            'arrQuery' => $arrQuery,
            'titlePageList' => $titlePageList,
            'year'=>$paramYear,
            'kai'=>$paramKai,
            'eikenScheduleId'=>$eikenScheduleId,
            'noRecordExcel' => $messages
        ));
        return $viewModel;
    }
    
    public function exportPaymentStatus($data,$year,$kai)
    {
        ob_start();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array('noRecordExcel' => $translator->translate('MsgNoRecordExcel'));
      

        if (!$data) {
            $this->flashMessenger()->addMessage($messages);
            $url = str_replace(array('&isExportExcel=1','?isExportExcel=1'),array('',''),$this->getRequest()->getUriString());
            $this->redirect()->toUrl( $url );
            return false;
        }


        $objFileName = new CharsetConverter();
        $fileName = sprintf($translator->translate('FileNameExportPaymentStatus').'.xlsx',$year,$kai);
        $fileName = $objFileName->utf8ToShiftJis($fileName);

        $header = $this->getServiceLocator()->get('Config')['headerExcelExport']['exportPaymentStatus'];
        $exportExcelMapper = new ExportExcelMapper($data,$header,$this->getServiceLocator());
        $arrExamHistoryList = $exportExcelMapper->convertToExport();
        

        $export = new PHPExcel();
        $export->export($arrExamHistoryList, $fileName, 'default', 1);
        return $this->getResponse();
    }

    /**
     *
     * @param string $data
     */
    public function insertpaymentAction($data = NULL)
    {
        $data = "Invalid Url!";
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent($data);
        return $response;
    }

    public function listbyeikenlevelAction()
    {
        $em = $this->getEntityManager();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $request = $this->params()->fromQuery();
        $eikenScheduleId = $request['eikenScheduleId'];
        $eikenLevelId = $request['eikenLevelId'];

        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;

        $applyeikenrepository = $em->getRepository('Application\Entity\ApplyEikenLevel');

        $listpupil = $applyeikenrepository->getListPaymentStatusByEikenLevel($this->id_org, $eikenScheduleId, $eikenLevelId, $limit, $offset);

        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'listpupil' => $listpupil,
            'noRecord' => $translator->translate('MSG001'),
            'page' => $page,
            'param' => "?eikenScheduleId=" . $eikenScheduleId . "&eikenLevelId=" . $eikenLevelId
        ));
        return $viewModel;
    }

    /**
     * function get list class by schoolyear.
     *
     * @author UtHV
     * @param
     *
     * @return data of view
     *         Author Modified Start date End date
     *         UTHV Creates 2015-07-11 2015-07-11
     */
    public function getclassAction()
    {
        $em = $this->getEntityManager();
        $schoolyearId = $this->params()->fromQuery('schoolyear');
        $yearId = (int) $this->params()->fromQuery('yearId');
        $data = $em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYear($schoolyearId, $yearId, $this->id_org);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));
        return $response;
    }

    /**
     * function get details payment status by id
     *
     * @author UtHV
     * @param
     *
     * @return data of view
     *         Author Modified Start date End date
     *         UTHV Creates 2015-07-11 2015-07-11
     */
    public function paymentdetailsAction()
    {
        $param=$this->params()->fromQuery("param",'');
        $page=$this->params()->fromQuery("page",1);
        $em = $this->getEntityManager();
        $id = (int) $this->params()->fromRoute('id', 0);

        $eikenScheduleId = (int) $this->params()->fromQuery('scheduleId', 0);
        $applyId = (int) $this->params()->fromQuery('applyId', 0);
        $applyeikenrepositoy = $em->getRepository('Application\Entity\ApplyEikenLevel');
        $object = $applyeikenrepositoy->getDetailsPaymentStatus($id, $this->id_org, $eikenScheduleId, $applyId);
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'data' => $object,
            'param' => $param,
            'page' => $page,
            'eikenScheduleId' => $eikenScheduleId
        ));

        return $viewModel;
    }

    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}
