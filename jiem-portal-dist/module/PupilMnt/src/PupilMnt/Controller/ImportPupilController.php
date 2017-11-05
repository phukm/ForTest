<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace PupilMnt\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use PupilMnt\Service\ServiceInterface\ImportPupilServiceInterface;
use PupilMnt\Service\ImportPupilService;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\ViewModel;
use PupilMnt\Form\ImportPupilForm;
use Zend\Json\Json;
use Dantai\PrivateSession;
use PupilMnt\PupilConst;
use Dantai\PublicSession;
use PupilMnt\Form\SeperateForm;
use Dantai\Utility\PHPExcel;
use Dantai\Utility\CharsetConverter;
use Dantai\Utility\CsvHelper;

ini_set('max_execution_time', 300); //300 seconds = 5 minutes

class ImportPupilController extends AbstractActionController {
    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var ImportPupilService
     */
    protected $importPupilService;

    /**
     *
     * @var EntityManager
     */
    protected $em;
    
    protected $organizationId;
    
    public function __construct(DantaiServiceInterface $dantaiService, ImportPupilServiceInterface $importPupilService, EntityManager $entityManager) {
        $this->dantaiService = $dantaiService;
        $this->importPupilService = $importPupilService;
        $this->em = $entityManager;
        $user = PrivateSession::getData('userIdentity');
        $this->organizationId = $user['organizationId'];
    }
    
    public function indexAction(){
        //role service manager and role org supervisor disable download button template and button download.
        $form = new ImportPupilForm($this->getServiceLocator());
        if ($this->getRequest()->isPost()) {
            $postData = array_merge_recursive(
                    $this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray()
            );
            $fileImport = isset($postData['fileImport']) ? $postData['fileImport'] : array(); 
            list($result, $dataFile) = $this->importPupilService->validateFileImportPupil($fileImport);
            if(isset($postData['unitTestResult']) && isset($postData['unitTestDataFile'])){
                $result = $postData['unitTestResult'];
                $dataFile = $postData['unitTestDataFile'];
            }
            $result['content'] = '';
            if ($result['status'] == 1) {
                $dataImport = $this->importPupilService->getDataFromFileImport($dataFile);
                $htmlOutput = $this->importPupilService->getResultAfterCheckDataImport($dataImport);
                $result['content'] = $htmlOutput;
            }
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'form' => $form,
        ));
        return $viewModel;
    }
    
    public function mappingSchoolYearAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $dataImport = isset($params['dataImport']) ? json_decode($params['dataImport'], true) : array();
            $schoolYearNames = isset($params['schoolYearName']) ? $params['schoolYearName'] : array();
            $orgSchoolYearNames = isset($params['orgSchoolYearName']) ? $params['orgSchoolYearName'] : array();

            list($errors, $mappingData, $orgSchoolYearsNew, $listSchoolYearNotUse, $dataImport) = $this->importPupilService->validateMappingSchoolYear($dataImport, $orgSchoolYearNames, $schoolYearNames);

            if (!$errors) {
                foreach ($dataImport as $keyImport => $value) {
                    if (isset($mappingData[$value['orgSchoolYear']])) {
                        $dataImport[$keyImport]['schoolYear'] = $mappingData[$value['orgSchoolYear']];
                    }
                }
                $htmlOutput = $this->importPupilService->getResultAfterCheckDataImport($dataImport, false);
                $result['status'] = 1;
            } else {
                $paramsTemplate = array(
                    'dataImport' => $dataImport,
                    'orgSchoolYearsNew' => $orgSchoolYearsNew,
                    'listSchoolYearNotUse' => $listSchoolYearNotUse,
                    'mappingData' => $mappingData,
                    'errors' => $errors,
                );
                $template = '/pupil-mnt/import-pupil/mapping-schoolyear.phtml';
                $htmlOutput = $this->importPupilService->getHtmlOutPutOfTemplate($template, $paramsTemplate);

                $result['status'] = 0;
            }
            $result['content'] = $htmlOutput;
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function showDataPagingAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $dataImport = isset($params['dataImport']) ? json_decode($params['dataImport'], true) : '';
            $currentPage = isset($params['currentPage']) ? intval($params['currentPage']) : 1;
            $errors = isset($params['errors']) ? intval($params['errors']) : 1;
            
            list($dataShow, $maxPage) = $this->importPupilService->getDataPagingOfDataImport($dataImport, $currentPage);
            
            $paramsTemplate = array(
                'dataShow' => $dataShow,
                'currentPage' => $currentPage,
                'errors' => $errors,
                'maxPage' => $maxPage
            );
            $template = '/pupil-mnt/import-pupil/partial_show_data.phtml';
            $htmlOutput = $this->importPupilService->getHtmlOutPutOfTemplate($template, $paramsTemplate);
            $result['content'] = $htmlOutput;
            $result['status'] = 1;
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function saveAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            $data = $request->getPost('data');
            $dataImport = $request->getPost('dataImport');
            $confirmSave = $request->getPost('flagConfirmSave');
            if ($data && $dataImport) {
                $json = new Json();
                $data = $json->decode($data, true);
                $dataImport = $json->decode($dataImport, true);
//                check before save
                $reGetMasterData = $this->importPupilService->getListMasterData($dataImport);
                $isvalidMasterData = array();
                if(!empty($reGetMasterData)){
                    $isvalidMasterData = array_diff_key($reGetMasterData , $data);
                }
                $isValidGrade = $this->importPupilService->isValidMappingGrade($reGetMasterData);
                $result['message'] = $translator->translate('MsgImportFailed');
                $result['status'] = 0;
                
                $sl  = $this->getServiceLocator();
                $vhm = $sl->get('viewhelpermanager');
                $url = $vhm->get('url');
                $urlPolicy = $url('org-mnt/default', array(
                                                        'controller' => 'org',
                                                        'action' => 'policy-grade-class'
                                                    ));
                
                $result['statusNotShowPopup'] = 1;
                $result['MSGPopupWarning'] = sprintf($translator->translate('MSGCreateOrgAndClassMasterDataImport'), $urlPolicy);
                
                
                if($isValidGrade == 1 && empty($isvalidMasterData) && count($data) == count($reGetMasterData)){
                    $result['statusNotShowPopup'] = $this->importPupilService->isNotShowMSGGradeClass($reGetMasterData);
                    if($confirmSave == 1 ||  $result['statusNotShowPopup'] === true){
                        $this->importPupilService->saveMasterData($reGetMasterData);
                    }
                    $htmlOutput = $this->importPupilService->getResultAfterCheckDataImport($dataImport, false, false);
                    $result['content'] = $htmlOutput;
                    $result['message'] = $translator->translate('MsgCreateMasterDataSuccess');
                    $result['status'] = 1;
                }
            }else{
                $result['message'] = $translator->translate('MsgImportFailed');
                $result['status'] = 0;
            }
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function detailDuplicateAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $keyDuplicate = isset($params['keyDuplicate']) ? trim($params['keyDuplicate']) : '';
            $dataDetailInFile = isset($params['dataDetailInFile']) ? json_decode($params['dataDetailInFile'], true) : array();
            $dataDetailInDb = isset($params['dataDetailInDb']) ? json_decode($params['dataDetailInDb'], true) : array();
            $status = isset($params['status']) ? $params['status'] : '';
            $detailFile = isset($dataDetailInFile[$keyDuplicate]) ? $dataDetailInFile[$keyDuplicate] : array();
            $detailDb = isset($dataDetailInDb[$keyDuplicate]) ? $dataDetailInDb[$keyDuplicate] : array();
            $paramsTemplate = array(
                'detailFile' => $detailFile,
                'detailDb' => $detailDb,
                'keyDuplicate' => $keyDuplicate,
                'status' => $status
            );
            $template = '/pupil-mnt/import-pupil/detail-duplicate.phtml';
            $htmlOutput = $this->importPupilService->getHtmlOutPutOfTemplate($template, $paramsTemplate);
            $result['content'] = $htmlOutput;
            $result['status'] = 1;
            return $this->getResponse()->setContent(Json::encode($result));
        }else{
            return $this->redirect()->toRoute(null, array(
                'module' => 'pupil-mnt',
                'controller' => 'pupil',
                'action' => 'index'
            ));
        }
    }

    
    public function duplicateAction()
    {
        $request = $this->getRequest();
        if($request->isPost()){
            $params = $request->getPost();
            list($duplicate, $dataDetailInFile, $dataDetailInDb) = $this->importPupilService->getDataDuplicatePupilName($this->organizationId, $params);
            $viewModel = new ViewModel();
            return $viewModel->setVariables(
                array(
                    'duplicate' => $duplicate,
                    'dataDetailInFile' => $dataDetailInFile,
                    'dataDetailInDb' => $dataDetailInDb,
                    'dataImport' => $params
                )
            );
        }else{
            return $this->redirect()->toRoute(null, array(
                'module' => 'pupil-mnt',
                'controller' => 'pupil',
                'action' => 'index'
            ));
        }
    }
    
    public function savePupilAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            if (!empty($params['dataImportPupil'])) {
                $isCheckedDuplicate = isset($params['isCheckedDuplicate']) ? intval($params['isCheckedDuplicate']) : 0;
                $translator = $this->getServiceLocator()->get('MVCTranslator');
                $dataImport = json_decode($params['dataImportPupil'], true);
                if($isCheckedDuplicate == 0){
                    list($duplicate, $dataDetailInFile, $dataDetailInDb) = $this->importPupilService->getDataDuplicatePupilName($this->organizationId, $dataImport);
                    
                    if($duplicate){
                        $paramsTemplate = array(
                            'duplicate' => $duplicate,
                            'dataDetailInFile' => $dataDetailInFile,
                            'dataDetailInDb' => $dataDetailInDb,
                            'dataImport' => $dataImport,
                        );
                        $template = '/pupil-mnt/import-pupil/duplicate.phtml';
                        $htmlOutput = $this->importPupilService->getHtmlOutPutOfTemplate($template, $paramsTemplate);
                        $result['content'] = $htmlOutput;
                        $result['message'] = $translator->translate('MsgCreateMasterDataSuccess');
                        $result['status'] = PupilConst::IMPORT_DUPLICATE_PUPIL_NAME;
                        return $this->getResponse()->setContent(Json::encode($result));
                    }
                }
                $response = $this->importPupilService->saveDataPupil($dataImport);
                $result = array(
                    'status'  => $response ? PupilConst::IMPORT_SUCCESS : PupilConst::IMPORT_FAILED,
                    'message' => $response ? $translator->translate('MsgImportSuccess') : $translator->translate('MsgImportFailed'),
                    'content' => '',
                );

                return $this->getResponse()->setContent(Json::encode($result));
            }
        }
    }   
   
    public function seperateNameAction()
    {
        $form = new SeperateForm($this->getServiceLocator());
        $message = '';
        if ($this->getRequest()->isPost()) {
            $postData = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray()
            );
            $fileImport = isset($postData['fileImport']) ? $postData['fileImport'] : array();
            list($result, $dataFile) = $this->importPupilService->validateFileSeperatePupil($fileImport);
            if($result['status'] == 0){
                $message = isset($result['message']) ? $result['message'] : '';
                $this->flashMessenger()->addMessage($message);

                return $this->redirect()->toRoute('pupil-mnt/default', array('controller' => 'import-pupil', 'action' => 'seperate-name'));
            }
            
            $exportData = $this->importPupilService->seperateFullname($dataFile);
            $objFileName = new CharsetConverter();
            $filenames = $postData['fileImport']['name'];
            $filenames = $objFileName->utf8ToShiftJis($filenames);
            $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
            array_unshift($exportData, $importPupilService->getDataHeaderImport());
            $cookiesToken = $postData['cookiesToken'];
            
            if (strpos($postData['fileImport']['name'], 'xlsx') !== FALSE)
            {
                //export to excel file
                //set cookies for check progress of export file
                
                setcookie(PupilConst::COOKIES_TOKEN, $cookiesToken, time()+3600); 
                $phpExcel = new PHPExcel();
                $phpExcel->export($exportData, $filenames, 'default', 1);   
                return $this->getResponse();
            }
            else if (strpos($postData['fileImport']['name'], 'xls') !== FALSE)
            {
                //export to excel file
                //set cookies for check progress of export file
                setcookie(PupilConst::COOKIES_TOKEN, $cookiesToken, time()+3600); 
                $phpExcel = new PHPExcel();
                $phpExcel->export($exportData, $filenames, 'default', 1, '', 'xls');  
                return $this->getResponse();
            }
            
            else if (strpos($postData['fileImport']['name'], 'csv') !== FALSE)
            {       
                //export to csv file
                //set cookies for check progress of export file
                setcookie(PupilConst::COOKIES_TOKEN, $cookiesToken, time()+3600); 
                $csv = CsvHelper::arrayToStrCsv($exportData);
                $csv = mb_convert_encoding($csv, 'SJIS', 'UTF-8');
                 
                $headers = $this->getResponse()->getHeaders();
                $headers->addHeaderLine('Content-Type', 'application/csv, charset=Shift_JIS');
                $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"$filenames\"");
                $headers->addHeaderLine('Accept-Ranges', 'bytes');
                $headers->addHeaderLine('Content-Length', strlen($csv));
                $headers->addHeaderLine('Content-Transfer-Encoding: Shift_JIS');
               
                $this->getResponse()->setHeaders($headers);
                $this->getResponse()->setContent($csv); 
                
                return $this->getResponse();
            }
            
        }
        return array(
            'form' => $form,
            'message' => $this->flashMessenger()->getMessages()
        );
    }
    
    public function exportTemplateSeperateAction() {
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $exportType = (null !== $this->getRequest()->getPost('exportType')) ?  $this->getRequest()->getPost('exportType') : PupilConst::EXPORT_TEMPLATE_TYPE_XLS;
        $arrayExportType = array(PupilConst::EXPORT_TEMPLATE_TYPE_XLS, PupilConst::EXPORT_TEMPLATE_TYPE_CSV);

        if(!in_array($exportType, $arrayExportType)){
            return $this->redirect()->toRoute(null, array(
                'module' => 'pupil-mnt',
                'controller' => 'import-pupil',
                'action' => 'seperate-name'
            ));
        }
        switch ($exportType){
            case PupilConst::EXPORT_TEMPLATE_TYPE_XLS:
                $response = $importPupilService->exportTemplateToExcel2003($this->getResponse());
                break;
            case PupilConst::EXPORT_TEMPLATE_TYPE_CSV:
                $response = $importPupilService->exportTemplateToCsv($this->getResponse());
                break;
            default:
                break;
        }
        return $response;
    }
}
