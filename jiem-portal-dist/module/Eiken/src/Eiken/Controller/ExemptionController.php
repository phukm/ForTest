<?php
namespace Eiken\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Eiken\Service\ServiceInterface\ExemptionServiceInterface;
use Zend\View\Model\ViewModel;
use Dantai\PrivateSession;
use Dantai\Api\UkestukeClient;
use Eiken\Service\ExemptionService;
use Zend\Json\Json;

class ExemptionController extends AbstractActionController
{
    protected $user;
    protected $organizationNo;    
    protected $limit;
    protected $em;
    protected $exemptionService;
    protected $dantaiService;

    public function __construct() {     
        
        $privateSession = new PrivateSession();        
        $this->user = $privateSession->getData('userIdentity');
        $this->organizationNo = $this->user['organizationNo'];
        $this->limit = 20;
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e) {
        $this->exemptionService = new ExemptionService();
        $this->exemptionService->setServiceLocator($this->getServiceLocator());
        $this->dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        parent::onDispatch($e);
    }

        public function getEntityManager()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }
    
    public function listAction()
    {
        $data = array();
        $config = $this->getServiceLocator()->get('Config');
        $dataExemptionApi = $this->exemptionService->getExemptionFromAPI($this->organizationNo, $config['orgmnt_config']['api']);
        $eikenId = $this->params()->fromQuery('eikenid', '');
        $name = $this->params()->fromQuery('name', '');
        $dataApi = $dataExemptionApi ? $this->exemptionService->refreshData($dataExemptionApi->eikenArray) : array();
        $dataApi = $this->exemptionService->searchArray($dataApi, 'kekka', 10);
        $dataApi = $this->exemptionService->searchArray($dataApi, 'eikenid', $eikenId);
        $dataApi = $this->exemptionService->searchArray($dataApi, 'name', $name);
        $dataApi = $this->exemptionService->array_orderby($dataApi, 'shimei_kana', SORT_ASC, 'birthdt', SORT_ASC, 'eikenid', SORT_ASC);
        $data['dataApi'] = $dataApi;
        $data['currentYear'] = date('Y');
        $data['eikenid'] = $eikenId;
        $data['name'] = $name;
        list($dataShow, $maxPage) = $this->exemptionService->getDataPagingOfDataImport($dataApi, $eikenId, $name, 1);
        $data['schoolCode'] = $config['School_Code'];
        $data['jobCode'] = $config['Job_Code'];
        $data['dataShow'] = $dataShow;
        $data['maxPage'] = $maxPage;
        $viewModel = new ViewModel($data);

        return $viewModel;
    }

     public function showDataPagingAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $dataAPI = isset($params['dataAPI']) ? json_decode($params['dataAPI']) : array();
            $currentPage = isset($params['currentPage']) ? intval($params['currentPage']) : 1;
            $eikenid = $params['eikenid'];
            $name = $params['name'];

            list($dataShow, $maxPage) = $this->exemptionService->getDataPagingOfDataImport($dataAPI, $eikenid, $name, $currentPage);
            $config = $this->getServiceLocator()->get('Config');
            
            $paramsTemplate = array(
                'dataShow'    => $dataShow,
                'currentPage' => $currentPage,
                'maxPage'     => $maxPage,
                'schoolCode'  => $config['School_Code'],
                'jobCode'     => $config['Job_Code'],
                'eikenId'     => $eikenid,
                'name'        => $name,
            );
            $template = '/eiken/exemption/data_table.phtml';
            $htmlOutput = $this->exemptionService->getHtmlOutPutOfTemplate($template, $paramsTemplate);
            $result['content'] = $htmlOutput;
            $result['status'] = 1;
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function exportAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        if ($request->isPost()) {
            $params = $request->getPost();
            $eikenId = isset($params['eikenId']) ? $params['eikenId'] : '';
            $name = isset($params['name']) ? $params['name'] : '';
            $currentEiken = $this->dantaiService->getEndEikenSchedule();
            $filename = $this->organizationNo . "_" . "一免者情報" . "_" . $currentEiken->year . "年度第" . $currentEiken->kai . "回" . '.xls';
            $exportData = $this->exemptionService->getExportExcelDataExemptionList($this->organizationNo, $eikenId, $name);
            $response = $this->exemptionService->exportToExcel($exportData, $response, $filename);
        }

        return $response;
    }
}
