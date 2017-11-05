<?php

/**
 * View folder : view\pupil
 */

namespace PupilMnt\Controller;

use Dantai\PrivateSession;
use Zend\Mvc\Controller\AbstractActionController;
use PupilMnt\Service\ServiceInterface\PupilServiceInterface;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use PupilMnt\Service\PupilService;
use Zend\Json\Json;
use Dantai\Utility\CsvHelper;
use PupilMnt\PupilConst;
use Dantai\PublicSession;
use Zend\View\Model\ViewModel;

class PupilController extends AbstractActionController {

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var PupilService
     */
    protected $pupilService;
    
    protected $organizationId;
    
    public function __construct(DantaiServiceInterface $dantaiService, PupilServiceInterface $pupilService) {
        $this->dantaiService = $dantaiService;
        $this->pupilService = $pupilService;
        $user = $this->dantaiService->getCurrentUser();
        $this->organizationId = $user['organizationId'];
    }

    public function indexAction() {
        $routeMatch = $this->getEvent()
                        ->getRouteMatch()
                        ->getParam('controller') . '_' . $this->getEvent()
                        ->getRouteMatch()
                        ->getParam('action');
        return $this->pupilService->getPagedListPupil($this->getEvent(), $routeMatch, $this->getRequest(), $this->params(), $this->flashMessenger(), $this->dantaiService);
    }

    public function showAction() {
        $rel = $this->pupilService->getDetailPupil($this->params('id', 0), $this->flashMessenger());                
        if ($rel === 1) {
            return $this->redirects();
        } else {
            return $rel;
        }
    }

    public function addAction() {
        $rel = $this->pupilService->getAddPupil($this->params('id', 0), $this->getRequest(), $this->params(), $this->flashMessenger());        
        if ($rel === 1) {
            return $this->redirects();
        }  
        return $rel;
    }

    public function saveAction() {
        $this->pupilService->UpdatePupil($this->params('id', 0), $this->getRequest(), $this->params(), $this->flashMessenger());        
        return $this->redirect()->toRoute(null, array(
                    'module' => 'pupil-mnt',
                    'controller' => 'pupil',
                    'action' => 'add'
        ));
    }

    public function editAction() {
        $rel = $this->pupilService->getEditByPupil($this->params('id', 0), $this->getRequest(), $this->params(), $this->flashMessenger());
        if ($rel === 1) {
            return $this->redirects();
        } else {
            return $rel;
        }
    }

    public function updateAction() {        
        $this->pupilService->UpdatePupil($this->params('id', 0), $this->getRequest(), $this->params(), $this->flashMessenger());        
        return $this->redirect()->toRoute(null, array(
                    'module' => 'pupil-mnt',
                    'controller' => 'pupil',
                    'action' => 'edit',
                    'id' => $this->params('id', 0)
        ));
    }

    public function ajaxGetListClassAction() {
        return $this->pupilService->getAjaxListClass($this->params(), $this->getResponse());
    }

    public function ajaxGetListKaiAction() {
        return $this->pupilService->getAjaxListKai($this->params(), $this->getResponse());
    }

    public function deleteAction()
    {
        $pupilListId = $this->params('id', 0);
        if (empty($pupilListId)) {
            $pupilListId = $this->params()->fromPost('exportItem');
        }
        PrivateSession::clear(PupilConst::TEST_RESULT_PUPIL_LIST);
        $resultPupilList = $this->pupilService->checkResultEikenAndIbaPupil($pupilListId);
        if (empty($resultPupilList)) {
            $this->pupilService->getDeletePupil($this->params('id', 0), $this->params(), $this->flashMessenger());
        }
        PrivateSession::setData(PupilConst::TEST_RESULT_PUPIL_LIST, $resultPupilList);

        return $this->redirects();
    }

    public function saveImportAction() {
        $this->pupilService->getSaveImportPupil($this->getRequest(), $this->params());
        $this->redirects();
    }

    // export file
    public function exportTemplateAction() {
        
        /*@var $exportPupilService \PupilMnt\Service\ExportPupilService*/
        $exportPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ExportPupilServiceInterface');
        $exportType = (null !== $this->getRequest()->getPost('exportType')) ?  $this->getRequest()->getPost('exportType') : PupilConst::EXPORT_TEMPLATE_TYPE_XLSX;
        $exportTypeTemplate = (null !== $this->getRequest()->getPost('teamplateFile')) ?  $this->getRequest()->getPost('teamplateFile') : PupilConst::EXPORT_TEMPLATE_TYPE_NORMAL;
        $arrayExportType = array(PupilConst::EXPORT_TEMPLATE_TYPE_XLSX, PupilConst::EXPORT_TEMPLATE_TYPE_XLS, PupilConst::EXPORT_TEMPLATE_TYPE_CSV);
        $arrayExportTypeTemplate = array(PupilConst::EXPORT_TEMPLATE_TYPE_NORMAL, PupilConst::EXPORT_TEMPLATE_TYPE_SEPERATE);
        if(!in_array($exportType, $arrayExportType) || !in_array($exportTypeTemplate, $arrayExportTypeTemplate)){
            return $this->redirect()->toRoute(null, array(
                'module' => 'pupil-mnt',
                'controller' => 'pupil',
                'action' => 'index'
            ));
        }
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        if($exportTypeTemplate ==  PupilConst::EXPORT_TEMPLATE_TYPE_NORMAL){
            switch ($exportType){
                case PupilConst::EXPORT_TEMPLATE_TYPE_XLSX:
                    $response = $exportPupilService->exportTemplateToExcel2007($this->getResponse());
                    break;
                case PupilConst::EXPORT_TEMPLATE_TYPE_XLS:
                    $response = $exportPupilService->exportTemplateToExcel2003($this->getResponse());
                    break;
                case PupilConst::EXPORT_TEMPLATE_TYPE_CSV:
                    $response = $exportPupilService->exportTemplateToCsv($this->getResponse());
                    break;
                default:
                    break;
            }
        }else if($exportTypeTemplate ==  PupilConst::EXPORT_TEMPLATE_TYPE_SEPERATE){
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
        }else{
            $response = $importPupilService->exportTemplateToExcel2003($this->getResponse());
        }
        return $response;
    }
    
    public function exportAction(){
        //role service manager and role org supervisor disable download button template and button download.
        if(PublicSession::isDisableDownloadButtonRole()){
            return $this->redirects('pupil/pupil');
        }        
        $token = $this->params()->fromRoute('search');
        $searchArray = PrivateSession::getData(PupilConst::SESSION_KEY_SEARCH_INDEX);
        $search['exportIds'] = $this->getRequest()->getPost('exportItem') != Null ? explode(',', $this->getRequest()->getPost('exportItem')) : array();
        if($token == $searchArray['token'])
        {
            $search['year'] = !empty($searchArray['year']) ? intval($searchArray['year']) : '';        
            $search['orgSchoolYearId'] = !empty($searchArray['orgSchoolYear']) ? intval($searchArray['orgSchoolYear']) : '';
            $search['className'] = !empty($searchArray['classj']) ? $searchArray['classj'] : '';
            $search['name'] = !empty($searchArray['name']) ? trim($searchArray['name']) : '';
        }
        $exportType = (null !== $this->getRequest()->getPost('exportType')) ?  $this->getRequest()->getPost('exportType') : PupilConst::EXPORT_TYPE_XLSX;

        /*@var $exportPupilService \PupilMnt\Service\ExportPupilService*/
        $exportPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ExportPupilServiceInterface');
        $pupils = $exportPupilService->getExportPupilData($search);
        $arrayExportType = array(PupilConst::EXPORT_TYPE_XLSX, PupilConst::EXPORT_TYPE_XLS, PupilConst::EXPORT_TYPE_CSV);
        if(!$pupils || !in_array($exportType, $arrayExportType)){
            return $this->redirect()->toRoute(null, array(
                        'module' => 'pupil-mnt',
                        'controller' => 'pupil',
                        'action' => 'index'
            ));
        }
        switch ($exportType){
            case PupilConst::EXPORT_TYPE_XLSX:
                $response = $exportPupilService->exportDataToExcel2007($this->getResponse(), $pupils);
                break;
            case PupilConst::EXPORT_TYPE_XLS:
                $response =  $exportPupilService->exportDataToExcel2003($this->getResponse(), $pupils);
                break;
            case PupilConst::EXPORT_TYPE_CSV:
                $response =  $exportPupilService->exportDataToCsv($this->getResponse(), $pupils);
                break; 
        }
        return $response;
    }

    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function checkconditionAction() {
        $data = $this->pupilService->checkCountPupil();
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));
        return $response;
    }

    public function redirects() {
        return $this->redirect()->toRoute(null, array(
                    'module' => 'pupil-mnt',
                    'controller' => 'pupil',
                    'action' => 'index'
        ));
    }

    public function checkDuplicatePupilNumberAction() {
        $results = array(
            'status' => 0, 'error' => array()
        );
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();

            $year = !empty($params['year']) ? intval($params['year']) : 0;
            $classId = !empty($params['classj']) ? intval($params['classj']) : 0;
            $orgSchoolYearId = !empty($params['orgSchoolYear']) ? intval($params['orgSchoolYear']) : 0;
            $pupilNumber = !empty($params['Number']) ? intval($params['Number']) : 0;
            $pupilId = !empty($params['pupilId']) ? intval($params['pupilId']) : 0;

            if ($year > 0 && $classId > 0 && $orgSchoolYearId > 0 && $pupilNumber > 0) {
                $checkDuplicate = $this->pupilService->checkDuplicateNumberOfPupil($year, $classId, $orgSchoolYearId, $pupilNumber, $pupilId);
                if ($checkDuplicate == true) {
                    $translator = $this->getServiceLocator()->get('MVCTranslator');
                    $results['error'][0]['message'] = $translator->translate('MsgDuplicatePupilError1');
                    $results['error'][0]['id'] = 'Number';
                } else {
                    $results['status'] = 1;
                }
            }
        }
        return $this->getResponse()->setContent(Json::encode($results));
    }
    public function checkNameKanjiAction() {
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();

            $firstNameKanji = !empty($params['firstNameKanji']) ? trim($params['firstNameKanji']) : '';
            $lastNameKanji = !empty($params['lastNameKanji']) ? trim($params['lastNameKanji']) : '';
            $nameKanji = $firstNameKanji.$lastNameKanji;
            if (!empty($nameKanji) && (strlen(utf8_decode($nameKanji))*3 < strlen($nameKanji))) {
                $translator = $this->getServiceLocator()->get('MVCTranslator');
                $results['error'][0]['message'] = $translator->translate('MsgUseOldKanji');
                $results['error'][0]['id'] = 'firstNameKanji';
                $results['error'][0]['id'] = 'lastNameKanji';
                $results['status'] = 0;
            } else {
                $results['status'] = 1;
            }
        }
        return $this->getResponse()->setContent(Json::encode($results));
    }
    
    public function checkDuplicatePupilAction() {
        $em = $this->getEntityManager();
        $results = $this->pupilService->getAjaxCheckDuplicatePupil($this->getRequest()->isPost(), $this->getRequest()->getPost(), $em);
        return $this->getResponse()->setContent(Json::encode($results));
    }
    public function ajaxGetListClassNameAction() {
        return $this->pupilService->getAjaxListClassName($this->params(), $this->getResponse());
    }

    public function checkPupilHadApplyEikenToDeleteAction()
    {
        $listPupilId = json_decode($this->request->getContent())->pupilId;
        $listPupilId = explode(',',$listPupilId);
        $listPupilHadApply = $this->pupilService->checkPupilApplyEikenOrPaidBefore($listPupilId);
        $jsonModel = \Dantai\Utility\JsonModelHelper::getInstance();
        if(!empty($listPupilHadApply)) {
            $jsonModel->setFail();
            $msg = $this->getServiceLocator()->get('MvcTranslator')->translate('NotDeleteOneStudent');

            // create message when teacher delete multi students
            if (count($listPupilId) > 1) {
                $msg = $this->getServiceLocator()->get('MvcTranslator')->translate('NotDeleteManyStudent') . '<br>';
                foreach ($listPupilHadApply as $item) {
                    $msg .= $item['name'] . '、';
                }
                $msg = substr($msg, 0 , strlen($msg) - 3);
            }

            $jsonModel->addMessage($msg);
        }else{
            $jsonModel->setSuccess();
        }
        return new \Zend\View\Model\JsonModel($jsonModel->toArray());
    }
    
    public function cannotDeleteAction() {
        $data = $this->pupilService->getLisIdPupilCanNotDelete($this->params());
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));
        return $response;
    }
}
