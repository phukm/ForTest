<?php

/*
 * Date: 16/06/2015
 * @method Standard : index, show, add, edit, save, update
 */
namespace InvitationMnt\Controller;

use Application\Service\CommonService;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Doctrine\ORM\EntityManager;
use InvitationMnt\Service\ServiceInterface\StandardServiceInterface;
use InvitationMnt\Service\StandardService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use InvitationMnt\Form\SetstandardForm;
use InvitationMnt\Form\SetstandardlevellistForm;
use Application\Entity\StandardLevelSetting;
use Application\Entity\Repository\StandardLevelSettingRepository;
use Application\Entity\OrgSchoolYear;
use Application\Entity\Repository\OrgSchoolYearRepository;
use Zend\Json\Json;
use Dantai\Utility\JsonModelHelper;

class StandardController extends AbstractActionController
{

    protected $org_id;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var StandardServiceInterface
     */
    protected $standardService;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, StandardServiceInterface $standardService, EntityManager $entityManager)
    {
        $this->dantaiService = $dantaiService;
        $this->standardService = $standardService;
        $this->em = $entityManager;
        $user = $this->dantaiService->getCurrentUser();
        $this->org_id = $user['organizationId'];
    }
    
    // function list standard
    public function indexAction()
    {
        $trans = $this->params()->fromQuery('trans');
        if ($trans == 1) {
            $validator = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $this->em->getRepository('Application\Entity\StandardLevelSetting'),
                'fields' => array(
                    'year',
                    'organizationId',
                    'isDelete'
                )
            ));
            $checkValid = $validator->isValid(array(
                $this->getCurrentYear(),
                $this->org_id,
                $isDelete = 0
            ));
            if ($checkValid == true) {
                return $this->redirect()->toRoute(null, array(
                    'module' => 'invitation-mnt',
                    'controller' => 'standard',
                    'action' => 'index'
                ));
            } else {
                return $this->redirect()->toRoute(null, array(
                    'module' => 'invitation-mnt',
                    'controller' => 'standard',
                    'action' => 'add'
                ));
            }
        }
        $mess = false;
        $viewModel = new ViewModel();
        $this->em->clear();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $form = new SetstandardForm();
        $standardLevel_list = "";
        // router search
        $routeMatch = $this->getEvent()
            ->getRouteMatch()
            ->getParam('controller') . '_' . $this->getEvent()
            ->getRouteMatch()
            ->getParam('action');
        
        if ($this->getRequest()->isPost()) {
            $search = $this->params()->fromPost();
            $this->dantaiService->setSearchKeywordToSession($routeMatch, $search);
        }
        $searchArray = $this->dantaiService->getSearchKeywordFromSession($routeMatch);
        if (! empty($searchArray)) {
            $request_year = $searchArray['ddbYear'];
            $request_schoolyear = $searchArray['ddbSchoolYear'];
            $standardLevel_list = $this->em->getRepository('Application\Entity\StandardLevelSetting')->searchStandardLevel($request_year, $request_schoolyear, $this->org_id);
            if (empty($standardLevel_list)) {
                $mess = $translator->translate('MSG013');
            }
            $form->get("ddbYear")->setAttributes(array(
                'value' => $request_year
            ));
            $schoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYearByYear($this->org_id, $request_year);
            $yearschool = array();
            // load schoolyear list
            if (isset($schoolyear)) {
                $yearschool[''] = '';
                foreach ($schoolyear as $key => $value) {
                    $yearschool[$value['id']] = $value['displayName'];
                }
            }
            $form->get("ddbSchoolYear")->setValueOptions($yearschool);
            $form->get("ddbSchoolYear")->setAttributes(array(
                'value' => $request_schoolyear
            ));
        } else {
            // search
            $isset = $this->params()->fromPost();
            if (! empty($isset)) {
                $request_year = $this->params()->fromPost('ddbYear');
                $request_schoolyear = $this->params()->fromPost('ddbSchoolYear');
                $standardLevel_list = $this->em->getRepository('Application\Entity\StandardLevelSetting')->searchStandardLevel($request_year, $request_schoolyear, $this->org_id);
                
                if (empty($standardLevel_list)) {
                    $mess = $translator->translate('MSG013');
                }
                $form->get("ddbYear")->setAttributes(array(
                    'value' => $request_year
                ));
                $schoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYearByYear($this->org_id, $request_year);
                $yearschool = array();
                // load schoolyear list
                if (isset($schoolyear)) {
                    $yearschool[''] = '';
                    foreach ($schoolyear as $key => $value) {
                        $yearschool[$value['id']] = $value['displayName'];
                    }
                }
                $form->get("ddbSchoolYear")->setValueOptions($yearschool);
                $form->get("ddbSchoolYear")->setAttributes(array(
                    'value' => $request_schoolyear
                ));
            } else {
                $standardLevel_list = $this->em->getRepository('Application\Entity\StandardLevelSetting')->searchStandardLevel(date("Y"), $request_schoolyear = '', $this->org_id);
                if (empty($standardLevel_list)) {
                    $mess = $translator->translate('MSG013');
                }
                $schoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYearByYear($this->org_id, date("Y"));
                $yearschool = array();
                // load schoolyear list
                if (isset($schoolyear)) {
                    $yearschool[''] = '';
                    foreach ($schoolyear as $key => $value) {
                        $yearschool[$value['id']] = $value['displayName'];
                    }
                }
                $form->get("ddbSchoolYear")->setValueOptions($yearschool);
            }
        }
        if (! empty($this->flashMessenger()->getMessages())) {
            $messs = $this->flashMessenger()->getMessages();
            $mess = $messs[0];
        }
        
        $viewModel->setVariables(array(
            'form' => $form,
            'items' => $standardLevel_list,
            'mess' => $mess,
            'searchVisible' => empty($search)? 0 : 1
        ));
        
        return $viewModel;
    }

    public function showAction()
    {
        $viewModel = new ViewModel();
        $form = new SetstandardForm();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $id = $this->params()->fromRoute('id', 0);
        $mess = false;
        $checkExist = $this->em->getRepository('Application\Entity\StandardLevelSetting')->checkExist($this->org_id, $id);
        if (empty($checkExist)) {
            $mess = $translator->translate('MSG013');
            $this->flashMessenger()->addMessage($mess);
            return $this->redirect()->toRoute(null, array(
                'module' => 'invitation-mnt',
                'controller' => 'standard'
            ));
        } else {
            $eikenlevel = $this->em->getRepository('Application\Entity\EikenLevel')->ListEikenLevel();
            $levelname = array();
            $form->get("ddbYear")->setAttributes(array(
                'value' => $id,
                'selected' => true
            ));
            
            // load schoolyear list
            $schoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->org_id);
            $yearschool = array();
            if (isset($schoolyear)) {
                foreach ($schoolyear as $key => $value) {
                    $yearschool[$value['id']] = $value['displayName'];
                }
            }
            // load eiken level
            if (isset($eikenlevel)) {
                foreach ($eikenlevel as $key => $value) {
                    $levelname[$value['id']] = $value['levelName'];
                }
            }
            if (! empty($this->flashMessenger()->getMessages())) {
                $messs = $this->flashMessenger()->getMessages();
                $mess = $messs[0];
            }
            // load standard level setting
            $standardlevel = $this->em->getRepository('Application\Entity\StandardLevelSetting')->checkExist($this->org_id, $id);
            $viewModel->setVariables(array(
                'form' => $form,
                'schoolyear' => $yearschool,
                'eikenlevel' => $levelname,
                'standardlevel' => $standardlevel,
                'mess' => $mess,
                'id' => $id
            ));
        }
        
        return $viewModel;
    }

    public function addAction()
    {
        $viewModel = new ViewModel();
        $form = new SetstandardForm();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $mess = false;
        $messNoSchoolYear = false;
        $dataform = false;
        $eikenlevel = $this->em->getRepository('Application\Entity\EikenLevel')->ListEikenLevel();
        $levelname = array();
        $schoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->org_id);
        $yearschool = array();
        
        if (empty($schoolyear)) {
            $mess = $translator->translate('MSG053');
        }
        if (isset($schoolyear)) {
            foreach ($schoolyear as $key => $value) {
                $yearschool[$value['id']] = $value['displayName'];
            }
        }
        // load eiken level
        if (isset($eikenlevel)) {
            $levelname[''] = '';
            foreach ($eikenlevel as $key => $value) {
                $levelname[$value['id']] = $value['levelName'];
            }
        }
        if (! empty($this->flashMessenger()->getMessages())) {
            $messs = $this->flashMessenger()->getMessages();
            $messs = json_decode($messs[0], true);
            $dataform = $messs[2];
            $form->setData($dataform);
            $mess = $messs[1];
            $form->get("ddbYear")->setAttribute('value', $dataform["ddbYear"]);
            // for ($i = 1; $i <= $count; $i ++) {
            // }
        }

        // load standard level setting
        $standardLevel = array();
        $standardLevelList = $this->em->getRepository('Application\Entity\StandardLevelSetting')->checkExist($this->org_id, date('Y'));
        if (isset($standardLevelList)) {
            foreach ($standardLevelList as $key => $value) {
                $standardLevel[$value->getOrgSchoolYear()->getId()] = $value->getEikenLevelId();
            }
        }

        $viewModel->setVariables(array(
            'form' => $form,
            'data' => $dataform,
            'schoolyear' => $yearschool,
            'eikenlevel' => $levelname,
            'standardLevel' => $standardLevel,
            'mess' => $mess
        ));
        
        return $viewModel;
    }

    public function saveAction()
    {
        $app = $this->getServiceLocator()->get('Application');
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $request = $app->getRequest();
        $mess = false;
        $standerlevel = new \Application\Entity\StandardLevelSetting();
        $schoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->org_id);
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["ddbYear"] < date("Y")) {
                $mess[1] = $translator->translate('MSG039');
                $mess[2] = $data;
                $messjon = json_encode($mess);
                $this->flashMessenger()->addMessage($messjon);
                return $this->redirect()->toRoute(null, array(
                    'module' => 'invitation-mnt',
                    'controller' => 'standard',
                    'action' => 'add'
                ));
            }
            $repository = $this->em->getRepository('Application\Entity\StandardLevelSetting');
            $validator = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $repository,
                'fields' => array(
                    'organization',
                    'year',
                    'isDelete'
                )
            ));
            $year_check = $validator->isValid(array(
                $this->org_id,
                $data["ddbYear"],
                $isDelete = 0
            ));
            if ($year_check == true) {
                $mess[1] = sprintf($translator->translate('MSG062'), $data["ddbYear"]);
                $mess[2] = $data;
                $messjon = json_encode($mess);
                $this->flashMessenger()->addMessage($messjon);
                return $this->redirect()->toRoute(null, array(
                    'module' => 'invitation-mnt',
                    'controller' => 'standard',
                    'action' => 'add'
                ));
            }
            
            $count = count($schoolyear);
            for ($i = 1; $i <= $count; $i ++) {
                $this->setStandardLevel($data['ddbYear'], $data['name' . $i], $data['hdname' . $i]);
            }
            $this->em->clear();
            return $this->redirect()->toRoute('invitation-mnt/default', array(
                'controller' => 'standard',
                'action' => 'index'
            ));
        }
    }

    public function editAction()
    {
        $year = $this->params()->fromRoute('id', 0);
        $viewModel = new ViewModel();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $mess = false;
        $checkExist = $this->em->getRepository('Application\Entity\StandardLevelSetting')->checkExist($this->org_id, $year);
        
        if (empty($checkExist)) {
            $mess = $translator->translate('MSG013');
            $this->flashMessenger()->addMessage($mess);
            return $this->redirect()->toRoute(null, array(
                'module' => 'invitation-mnt',
                'controller' => 'standard'
            ));
        } elseif ($year < date("Y")) {
            $mess = sprintf($translator->translate('MSG029-1'), $year);
            $this->flashMessenger()->addMessage($mess);
            return $this->redirect()->toRoute(null, array(
                'module' => 'invitation-mnt',
                'controller' => 'standard',
                'action' => 'show',
                'id' => $year
            ));
        } else {
            
            // Lock for viewing/editing data
            $errorMessage = array();
//            if($this->flashMessenger()->hasMessages('lockMessage')){
//                $flashMessages = $this->flashMessenger()->getMessages('lockMessage');
//                $lockMessage = current($flashMessages);
//                $errorMessage['lockMessage'] = array('id' => 'lockMessage', 'message' => $lockMessage);
//            }
//            else {
//                // Try to get lock current module at current selected Organization and Year
//                $module = get_class($this) . '-' . $this->org_id . '-' . $year;
//                $lockStatus = $this->dantaiService->lockModule($module);
//                if(false === $lockStatus['lockId'])
//                    $errorMessage['lockMessage'] = array('id' => 'lockMessage', 'message' => $lockStatus['lockMessage']);
//                else
//                    $this->dantaiService->registerCleanLock($this->getEvent(), $module);
//            }
            $jsonMessage = JsonModelHelper::getInstance();
            $jsonMessage->setMessages($errorMessage);
            if (count($errorMessage))
                $jsonMessage->setFail();
            
            
            $form = new SetstandardForm();
            $eikenlevel = $this->em->getRepository('Application\Entity\EikenLevel')->ListEikenLevel();
            $levelname = array();
            $form->get("ddbYear")->setAttributes(array(
                'value' => $year,
                'selected' => true
            ));
            // load schoolyear list
            $schoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->org_id);
            // $yearschool = array();
            if (isset($schoolyear)) {
                foreach ($schoolyear as $key => $value) {
                    $yearschool[$value['id']] = $value['displayName'];
                }
            }
            // load eiken level
            if (isset($eikenlevel)) {
                $levelname[''] = '';
                foreach ($eikenlevel as $key => $value) {
                    $levelname[$value['id']] = $value['levelName'];
                }
            }
            // load standard level setting
            $standardlevel = array();
            $standardlevel_list = $this->em->getRepository('Application\Entity\StandardLevelSetting')->checkExist($this->org_id, $year);
            if (isset($standardlevel_list)) {
                foreach ($standardlevel_list as $key => $value) {
                    $standardlevel[$value->getOrgSchoolYear()->getId()] = $value->getEikenLevelId();
                }
            }
            $viewModel->setVariables(array(
                'form' => $form,
                'schoolyear' => $yearschool,
                'eikenlevel' => $levelname,
                'standardlevel' => $standardlevel,
                'id' => $year,
                'error' => $jsonMessage->jsonSerialize()
            ));
        }
        
        return $viewModel;
    }

    public function updateAction()
    {
        $return = array(
            'module' => 'invitation-mnt',
            'controller' => 'standard',
            'action' => 'index'
        );
        $year = $this->params()->fromRoute('id', 0);
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $request = $this->getRequest();
        $schoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->org_id);

        // Try to get lock current module at current selected Organization and year
//        $lockStatus = $this->dantaiService->lockModule(get_class($this) . '-' . $this->org_id . '-' . $year);
//        if(false === $lockStatus['lockId']){
//            $this->flashMessenger()->addMessage($lockStatus['lockMessage'], 'lockMessage');
//            $return['action'] = 'edit';
//            $return['id'] = $year;
//            return $this->redirect()->toRoute(null, $return);
//        }
//        else
            if ($request->isPost()) {
            $data = $request->getPost();
            $count = count($schoolyear);
            for ($i = 1; $i <= $count; $i ++) {

                if ($year == 0)
                    $year = $data['ddbYear'];

                $shyear = $this->em->getReference('Application\Entity\OrgSchoolYear', array(
                    'id' => $data['hdname' . $i]
                ));
                $or = $this->em->getReference('Application\Entity\Organization', array(
                    'id' => $this->org_id
                ));
                $eikenlevel = $this->em->getReference('Application\Entity\EikenLevel', array(
                    'id' => $data['name' . $i]
                ));
                $standardLevelSetting = $this->em->getRepository('Application\Entity\StandardLevelSetting')->findOneBy(array(
                    'organization' => $this->org_id,
                    'year' => $year,
                    'orgSchoolYear' => $data['hdname' . $i]
                ));
                if (! $standardLevelSetting) {
                    $standardLevelSetting = new StandardLevelSetting();
                }
                $standardLevelSetting->setYear($year);
                $standardLevelSetting->setOrganization($or);
                $standardLevelSetting->setEikenLevel($eikenlevel);
                $standardLevelSetting->setOrgSchoolYear($shyear);
                $this->em->persist($standardLevelSetting);
                $this->em->flush();
                $this->em->clear();
            }
        }
        
        return $this->redirect()->toRoute(null, $return);
    }

    public function getStandardLevelAction()
    {
        $year = $this->getRequest()->getPost()['year'];

        // load standard level setting
        $standardlevel = array();
        $standardlevel_list = $this->em->getRepository('Application\Entity\StandardLevelSetting')->checkExist($this->org_id, $year);
        if (isset($standardlevel_list)) {
            foreach ($standardlevel_list as $key => $value) {
                $standardlevel[$value->getOrgSchoolYear()->getId()] = $value->getEikenLevelId();
            }
        }
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($standardlevel));

        return $response;
    }

    function setStandardLevel($year, $eikenlevel, $schooyear)
    {
        $standerlevel = new StandardLevelSetting();
        $EikenLevelObject = $this->em->getReference('Application\Entity\EikenLevel', array(
            'id' => $eikenlevel
        ));
        $shyear = $this->em->getReference('Application\Entity\OrgSchoolYear', array(
            'id' => $schooyear
        ));
        $or = $this->em->getReference('Application\Entity\Organization', array(
            'id' => $this->org_id
        ));
        $standerlevel->setEikenLevel($EikenLevelObject);
        $standerlevel->setYear($year);
        $standerlevel->setOrganization($or);
        $standerlevel->setOrgSchoolYear($shyear);
        $this->em->persist($standerlevel);
        $this->em->flush();
        $this->em->clear();
    }

    public function getSchoolYearAction()
    {
        $year = $this->params()->fromQuery('year');
        $data = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYearByYear($this->org_id, $year);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));
        return $response;
    }

    public function getCurrentYear()
    {
        if (date("m") < 4) {
            return date("Y") - 1;
        } else {
            return date("Y");
        }
    }
}