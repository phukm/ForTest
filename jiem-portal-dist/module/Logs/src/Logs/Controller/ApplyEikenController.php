<?php
namespace Logs\Controller;

use Dantai\PublicSession;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Dantai\PrivateSession;
use Logs\Service\ServiceInterface\ApplyEikenServiceInterface;
use Logs\Form\ApplyEikenSearchForm;
use Dantai\Utility\JsonModelHelper;
use Dantai\Utility\PHPExcel;
use Dantai\Utility\CharsetConverter;

class ApplyEikenController extends AbstractActionController
{
    use \Application\Controller\ControllerAwareTrait;
    
    /**
     * @var DantaiServiceInterface
     */
    protected $dantaiService;
    
    protected $applyEikenService;
    protected $orgId;
    protected $orgNo;
    protected $orgName;
    protected $userRole;
    
    public function __construct(DantaiServiceInterface $dantaiService, ApplyEikenServiceInterface $applyEikenService) 
    {
        $this->dantaiService = $dantaiService;
        $this->applyEikenService = $applyEikenService;
        $user = PrivateSession::getData('userIdentity');
        $this->orgId = $user['organizationId'];
        $this->orgNo = $user['organizationNo'];
        $this->orgName = $user['organizationName'];
        $this->userRole = $user['roleId'];
    }
    
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
    public function indexAction()
    {
        $em = $this->getEntityManager();
        $form = new ApplyEikenSearchForm();
        $page = $this->params()->fromRoute('page', 1);
        //update for GNCCNCJDR5-778
        $scheId = $this->params()->fromQuery('scheId', 0);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        
        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'organizationNo' => '',
            'organizationName' => '',
            'action' => '',
            'fromDate' => '',
            'toDate' => '',
            'sortKey' => '',
            'sortOrder' => 'asc',
            'searchVisible' => 0,
            'token' => ''
        ));
        $sortOrder = !empty($searchCriteria['sortOrder']) ? $searchCriteria['sortOrder'] : 'asc';
        $sortKey = !empty($searchCriteria['sortKey']) ? $searchCriteria['sortKey'] : '';
        if ($this->isPost() && $searchCriteria['token']) {
            return $this->redirect()->toUrl('/logs/apply-eiken/index/search/' . $searchCriteria['token']);
        }
        list($startOfCurrentKai, $endOfCurrentKai, $sqlStartDate, $sqlEndDate) = $this->applyEikenService->getCurrentAndNextEikenSchedule();
        //update for GNCCNCJDR5-778
        $fStartDate = str_replace('-', '/', $sqlStartDate);
        $fEndDate = str_replace('-', '/', $sqlEndDate);
        $orgObject = $em->getRepository('Application\Entity\Organization')->find($this->orgId);

        // case auto fill search option when role admin + user
        // or redirect from apply eiken screen, list apply screen.
        if(PublicSession::isOrgAdminOrOrgUser() || !empty($scheId)  ){
            $searchCriteria['organizationNo'] = $this->orgNo;
            $searchCriteria['organizationName'] = $this->orgName;
            $form->get("organizationNo")->setAttribute('value', $searchCriteria['organizationNo']);
            $form->get("organizationName")->setAttribute('value', $searchCriteria['organizationName']);

            // if role admin or user then disable search Org.
            if (PublicSession::isOrgAdminOrOrgUser()) {
                $form->get("organizationNo")->setAttribute('disabled', 'disabled');
                $form->get("organizationName")->setAttribute('disabled', 'disabled');
            }

            if (empty($searchCriteria['fromDate']) || $searchCriteria['fromDate'] < $startOfCurrentKai) {
                $searchCriteria['fromDate'] = $fStartDate;
                $form->get("fromDate")->setAttributes(array('value' => $fStartDate));
            } else {
                $form->get("fromDate")->setAttributes(array('value' => $searchCriteria['fromDate']));
            }

            if (empty($searchCriteria['toDate']) || $searchCriteria['toDate'] > $endOfCurrentKai) {
                $searchCriteria['toDate'] = $fEndDate;
                $form->get("toDate")->setAttributes(array('value' => $endOfCurrentKai));
            } else {
                $form->get("toDate")->setAttributes(array('value' => $searchCriteria['toDate']));
            }
        }
        
        $form->get("organizationNo")->setAttributes(array('value' => $searchCriteria['organizationNo']));
        $form->get("organizationName")->setAttributes(array('value' => $searchCriteria['organizationName']));
        $form->get("fromDate")->setAttributes(array('value' => $searchCriteria['fromDate']));
        $form->get("toDate")->setAttributes(array('value' => $searchCriteria['toDate']));

        
        $form->get("action")
            ->setAttributes(array(
                'value' => $searchCriteria['action'],
                'selected' => true,
                'escape' => false
            ));
        $isExportExcel = $this->params()->fromRoute('isExportExcel');
        if ($isExportExcel == 1 || $isExportExcel == 2) {
            if ($isExportExcel == 2) {
                $orgNo = $orgObject->getOrganizationNo();
                $orgName = $orgObject->getOrgNameKanji();
                $searchCriteria['organizationNo'] = $orgNo;
                $searchCriteria['organizationName'] = $orgName;
                if (empty($searchCriteria['fromDate'])) {
                    $searchCriteria['fromDate'] = $fStartDate;
                } elseif ($searchCriteria['fromDate'] < $startOfCurrentKai) {
                    $searchCriteria['fromDate'] = $fStartDate;
                }

                if (empty($searchCriteria['toDate'])) {
                    $searchCriteria['toDate'] = $fEndDate;
                } elseif ($searchCriteria['toDate'] > $endOfCurrentKai) {
                    $searchCriteria['toDate'] = $fEndDate;
                }
            }
        }
        list($paginator, $jsonMessage) = $this->applyEikenService->getApplyEikenLogList($searchCriteria);
        // export excel
        $maxExport = 50000;
        if ($isExportExcel == 1 || $isExportExcel == 2) {
            return $this->exportApplyEikenLog($paginator->getItems(0,$maxExport),$searchCriteria['token'],$scheId);
        }
        // messages when export excel not data
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages ='';
        $flashMessenger = $this->flashMessenger()->getMessages();
        $flagStatus = 0;
        if(!empty($flashMessenger)){
            if(!empty($flashMessenger[0]) && array_key_exists(0, $flashMessenger)){
                if(array_key_exists('statusExport', $flashMessenger[0])){
                    $flagStatus = intval($flashMessenger[0]['statusExport']);
                }
            }
        }
        if ($flagStatus == 1) {
            $messages = $translator->translate('MsgNoRecordExcel');
        }
        $exportLimit = 0;
        if ($this->getRequest()->getHeader('Referer') != false) {
            $redirectUrl = parse_url($this->getRequest()
                            ->getHeader('Referer')
                            ->getUri());
            if ((!empty($redirectUrl) && (strpos($redirectUrl['path'], '/eiken/eikenorg/index') !== false) || (strpos($redirectUrl['path'], '/eiken/eikenorg') !== false))) {
                $exportLimit = 1;
            }
        }
        return new ViewModel(array(
            'jsMessages'    => $jsonMessage,
            'activitylist'  => $paginator ? $paginator->getItems($offset, $limit) : '',
            'form'          => $form,
            'page'          => $page,
            'paginator'     => $paginator,
            'param'         => isset($searchCriteria['token']) ? $searchCriteria['token'] : '',
            'pageLimit'     => $limit,
            'sortOrder'     => $sortOrder,
            'sortKey'       => $sortKey,
            'searchVisible' => isset($searchCriteria['searchVisible']) ? $searchCriteria['searchVisible'] : 0,
            'noRecordExcel'  => $messages,
            'roleId' => $this->userRole,
            'exportLimit' => $exportLimit,
            //GNCCNCJDM-278
            'scheId' =>$scheId,
        ));
    }
    public function exportApplyEikenLog($dataDraft,$token,$scheId)
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array('noRecordExcel' => $translator->translate('MsgNoRecordExcel'));
        $data = $this->applyEikenService->mappingDataForExport($dataDraft);
        if(empty($data)) {
            $this->flashMessenger()->addMessage(array('statusExport'=>1));
            if(empty($token)){
                //GNCCNCJDM-278
                return $this->redirect()->toUrl('/logs/apply-eiken/index?scheId='.$scheId);
            }
            return $this->redirect()->toUrl('/logs/apply-eiken/index/search/'.$token);
        }

        $objFileName = new CharsetConverter();
        $date = date('Ymd');
        $name = sprintf($translator->translate('ExcelLogName'),$date);
        $fileName = $name.'.xlsx';
        $fileName = $objFileName->utf8ToShiftJis($fileName);

        $header = $this->getServiceLocator()->get('Config')['headerExcelExport']['applyEikenLog'];
        $dataExport = $this->applyEikenService->convertToExport($data,$header);

        $export = new PHPExcel();
        $export->export($dataExport, $fileName, 'log-apply-eiken', 1);
        return $this->getResponse();
    }
}