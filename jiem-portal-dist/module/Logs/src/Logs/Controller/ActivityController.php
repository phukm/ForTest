<?php
namespace Logs\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Logs\Service\ServiceInterface\ActivityServiceInterface;
use Dantai\PrivateSession;
use Zend\Session\Container;
use Zend\View\Model\Zend\View\Model;
use Logs\Form\ActivitySearchForm;
use Dantai\Utility\JsonModelHelper;
use Dantai\Utility\PHPExcel;
use Dantai\Utility\CharsetConverter;
use Dantai\PublicSession;

class ActivityController extends AbstractActionController
{
    use \Application\Controller\ControllerAwareTrait;
    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;
    /**
     *
     * @var \History\Service\EikenHistoryService
     */
    protected $activityService;
    protected $orgId;
    protected $orgNo;
    
    public function __construct(DantaiServiceInterface $dantaiService, ActivityServiceInterface $activityService)
    {
        $this->activityService = $activityService;
        $this->dantaiService = $dantaiService;
        $user = PrivateSession::getData('userIdentity');
        $this->orgId = $user['organizationId'];
        $this->orgNo = $user['organizationNo'];
        $this->roleId = $user['roleId'];
    }
    
    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
    
    public function indexAction()
    {
        $em = $this->getEntityManager();
        $form = new ActivitySearchForm();
        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        
        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'orgno' => '',
            'orgname' => '',
            'userid' => '',
            'actiontype' => '',
            'datetime1' => '',
            'datetime2' => '',
            'sortKey' => '',
            'sortOrder' => 'asc',
            'searchVisible' => 0,
            'token' => ''
        ));
        
        $sortOrder = !empty($searchCriteria['sortOrder']) ? $searchCriteria['sortOrder'] : 'asc';
        $sortKey = !empty($searchCriteria['sortKey']) ? $searchCriteria['sortKey'] : '';
        if ($this->isPost() && $searchCriteria['token']) {
            return $this->redirect()->toUrl('/logs/activity/index/search/' . $searchCriteria['token']);
        }
        $form->get("orgno")->setAttributes(array('value' => $searchCriteria['orgno']));
        $form->get("orgname")->setAttributes(array('value' => $searchCriteria['orgname']));
        $form->get("userid")->setAttributes(array('value' => $searchCriteria['userid']));
        $form->get("actiontype")
        ->setAttributes(array(
            'value' => $searchCriteria['actiontype'],
            'selected' => true,
            'escape' => false
        ));
        $form->get("datetime1")->setAttributes(array('value' => $searchCriteria['datetime1']));
        $form->get("datetime2")->setAttributes(array('value' => $searchCriteria['datetime2']));

        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'MSGdatefomat' => $translator->translate('MSGdatefomat'),
            'MSGdatecompare' => $translator->translate('MSGdatecompare'),
            'MSGpositivenumber' => $translator->translate('MSG1')
        );
        $jsonModelHelper = new JsonModelHelper();
        $jsonMessage = $jsonModelHelper->getInstance();
        $jsonMessage->setFail();
        $jsonMessage->setData($jsMessages);
        
        $paginator = $em->getRepository('Application\Entity\ActivityLog')->getListActivityLog($this->roleId, $this->orgNo, $searchCriteria);
        
        // export excel
        $isExportExcel = $this->params()->fromRoute('isExportExcel');
        $checkShowButtonExport = PublicSession::isSysAdminOrServiceManagerOrOrgSupervisor();
        if ($checkShowButtonExport && $isExportExcel == 1) {
            return $this->exportLog($em->getRepository('Application\Entity\ActivityLog')->exportActivityLog($this->roleId, $this->orgNo, $searchCriteria),$searchCriteria['token']);
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
        return new ViewModel(array(
            'jsMessages'    => $jsonMessage,
            'activitylist'  => $paginator->getItems($offset, $limit),
            'form'          => $form,
            'page'          => $page,
            'paginator'     => $paginator,
            'param'         => isset($searchCriteria['token']) ? $searchCriteria['token'] : '',
            'pageLimit'     => $limit,
            'sortOrder'     => $sortOrder,
            'sortKey'       => $sortKey,
            'searchVisible' => isset($searchCriteria['searchVisible']) ? $searchCriteria['searchVisible'] : 0,
            'noRecordExcel'  => $messages,
            'checkShowButtonExport' => $checkShowButtonExport
        ));
    }
    public function exportLog($dataDraft,$token)
    {
        // @todo refacktor code
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array('noRecordExcel' => $translator->translate('MsgNoRecordExcel'));
        if(empty($dataDraft)) {
            $this->flashMessenger()->addMessage(array('statusExport'=>1));
            if(empty($token)){
                return $this->redirect()->toUrl('/logs/activity/index');
            }
            return $this->redirect()->toUrl('/logs/activity/index/search/'.$token);
        }
        $objFileName = new CharsetConverter();
        $date = date('Ymd');
        $name = sprintf($translator->translate('NAME_FILE_ACTIVITY_LOG'),$date);
        $fileName = $name.'.xlsx';
        $fileName = $objFileName->utf8ToShiftJis($fileName);

        $header = $this->getServiceLocator()->get('Config')['headerExcelExport']['HeaderExportExceilActivityLog'];
        $dataExport = $this->activityService->convertToExport($dataDraft,$header);
        $export = new PHPExcel();
        $export->export($dataExport, $fileName, 'default', 1);
        return $this->getResponse();
    }
}