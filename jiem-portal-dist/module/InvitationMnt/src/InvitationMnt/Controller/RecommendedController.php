<?php

/*
 * Date: 16/06/2015
 * @method Recommended : index
 */
namespace InvitationMnt\Controller;

use Application\Service\ServiceInterface\DantaiServiceInterface;
use Doctrine\ORM\EntityManager;
use InvitationMnt\Service\RecommendedService;
use InvitationMnt\Service\ServiceInterface\RecommendedServiceInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\RecommendLevel;
use Application\Entity\StandardLevelSetting;
use InvitationMnt\Form\RecommendedForm;
use Dantai\PrivateSession;
use Dantai\Api\EinaviClient;
use Zend\Json\Json;
use Dantai\Utility\JsonModelHelper;

class RecommendedController extends AbstractActionController
{
    /**
     * @var DantaiServiceInterface
     */
    protected $dantaiService;
    /**
     * @var RecommendedService
     */
    protected $recommendedService;
    /**
     * @var EntityManager
     */
    protected $orgId;
    protected $em;
    protected $error;
    protected $total = 0;
    protected $page = 1;
    protected $dateStart;
    protected $dateEnd;

    public function __construct(DantaiServiceInterface $dantaiService, RecommendedServiceInterface $recommendedService, EntityManager $entityManager)
    {
        $this->dantaiService = $dantaiService;
        $this->recommendedService = $recommendedService;
        $this->em = $entityManager;
        $user = $this->dantaiService->getCurrentUser();
        $this->orgId = $user['organizationId'];
        $this->dateStart = 2010;
        $this->dateEnd = date('Y') + 2;
    }

    public function indexAction()
    {
        $app = $this->getServiceLocator()->get('Application');
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $this->page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $paginator = new \stdClass();
        $offset = ($this->page <= 1 && $this->page > 1000) ? 0 : ($this->page - 1) * $limit;
        $searchVisible = 0;
        $REFERER = $this->getRequest()->getServer('HTTP_REFERER');
        if (strpos($REFERER, 'recommended') === false) {
            PrivateSession::clear('checkSetLevel');
        }
        $request = $app->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $searchVisible = 1;
            $data = array(
                'ddbYear'          => $data['ddbYear'],
                'ddbKai'           => $data['ddbKai'],
                'ddbSchoolYear'    => $data['ddbSchoolYear'],
                'ddbRecommenLevel' => $data['ddbRecommenLevel'],
                'txtName'          => $data['txtName'],
                'ddbLevelChange'   => $data['ddbLevelChange'],
                'ddbClass'         => $data['ddbClass']
            );
            PrivateSession::setData('postRecommendLevel', $data);
            PrivateSession::clear('checkSetLevel');

            return $this->redirect()->toRoute(null, array('module' => 'invitation-mnt', 'controller' => 'recommended', 'action' => 'index'));
        }
        $EikenSchedule = $this->recommendedService->getCurrentEikenSchedule();
        if ($this->page > 0 && PrivateSession::getData('postRecommendLevel')) {
            $data = PrivateSession::getData('postRecommendLevel');
            $searchVisible = 1;
        } else {
            $data = array(
                'ddbYear'          => date('Y'),
                'ddbKai'           => $EikenSchedule->kai,
                'ddbSchoolYear'    => '',
                'ddbRecommenLevel' => '',
                'txtName'          => '',
                'recommendlevel'   => '',
                'ddbClass'         => ''
            );
        }
        if (!empty($data)) {
            if (empty($this->orgId) || empty($data['ddbYear']) || empty($data['ddbKai'])) {
                $this->error = $translator->translate('MSG001');
            } else {
                $post = array('orgId' => $this->orgId, 'year' => $data['ddbYear'], 'kai' => $data['ddbKai'], 'OrgSchoolYearId' => $data['ddbSchoolYear'], 'classId' => $data['ddbClass'], 'EikenLevelId' => $data['ddbRecommenLevel'], 'name' => $data['txtName'], 'limit' => $limit, 'offset' => $offset);
                if (PrivateSession::getData('checkSetLevel')) {
                    $result = $this->recommendedService->setRecommendLevel($post);
                } else {
                    $result = $this->recommendedService->getResultRecommend($post);
                }
                $paginator = $result['paginator'];
                $recommendLevel = $result['data'];
            }
        }
        $errorMessage = $this->flashMessenger()->getMessages();
        if (!end($errorMessage)) {
            $errorMessage = array();
        }
        $jsonMessage = JsonModelHelper::getInstance();
        $jsonMessage->setMessages($errorMessage);
        if (count($errorMessage))
            $jsonMessage->setFail();
        //action link
        $action = (object)array(
            'search' => '/invitation/recommended/index',
            'clear'  => '/invitation/recommended/clear',
        );
        $invEikenSchedule = $this->recommendedService->getEikenSchedule($this->dateStart, $this->dateEnd);
        $viewModel = new ViewModel();
        
        $viewModel->setVariables(array(
            'CurrentEikenSchedule' => $EikenSchedule,
            'invYear'              => $invEikenSchedule['year'],
            'data'                 => $data,
            'EikenLevel'           => $this->recommendedService->getEikenLevel(),
            'mapEikenLevel'        => $this->recommendedService->mapEikenLevel(),
            'defaultOrgSchoolYear' => $this->recommendedService->getListSchoolYearByYear($this->orgId, $EikenSchedule->year),
            'defaultClass'         => $this->recommendedService->getListClass($data['ddbYear'], $data['ddbSchoolYear'], $this->orgId),
            'action'               => $action,
            'page'                 => $this->page,
            'paginator'            => $paginator,
            'numPerPage'           => $limit,
            'items'                => empty($recommendLevel) ? '' : $recommendLevel,
            'mes_title'            => ($data['ddbKai']) ? sprintf($translator->translate('mes_title1'), $data['ddbYear'], $data['ddbKai']) : '',
            'messNotFound'         => empty($recommendLevel) ? $translator->translate('MSG013') : '',
            'error'                => $jsonMessage->jsonSerialize(),
            'searchVisible'        => $searchVisible,
            'vaildateStandardlevel'=> isset($result['vaildateStandardlevel']) ? $result['vaildateStandardlevel'] : 1,
            'overWriteTargetKyu' => $translator->translate('OverWriteTargetKyu')
        ));

        return $viewModel;
    }

    public function clearAction()
    {
        // clear session page.
        PrivateSession::clear('postRecommendLevel');
        PrivateSession::clear('checkSetLevel');

        // Redirect screen recommend list.
        return $this->redirect()->toRoute(null, array(
            'module'     => 'invitation-mnt',
            'controller' => 'recommended',
            'action'     => 'index'
        ));
    }

    public function updateAction()
    {
        $app = $this->getServiceLocator()->get('Application');
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $request = $app->getRequest();
        $data = $request->getPost();
        $return = array(
            'module'     => 'invitation-mnt',
            'controller' => 'recommended',
            'action'     => 'index'
        );
        $page = explode('page/', $this->getRequest()->getServer('HTTP_REFERER'));
        if (isset($page[1])) {
            $return['page'] = $page[1];
        }
        if (!empty($data)) {
            $localStorageRecommendLevel = json_decode($data['localStorageRecommendLevel']);
            $eikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array('year' => $data['dbyear'], 'kai' => $data['dbkai']));
            if (!empty($eikenSchedule->getId())) {
                $checkProcessLog = $this->em->getRepository('Application\Entity\ProcessLog')->findOneBy(array('orgId' => $this->orgId, 'scheduleId' => $eikenSchedule->getId()));
            }
            if (!empty($checkProcessLog)) {
                $this->flashMessenger()->addMessage($translator->translate('MSG073'));
            }
            else {
                if (!empty($eikenSchedule->getDeadlineTo()) && date("Y/m/d") <= $eikenSchedule->getDeadlineTo()->format("Y/m/d")) {
                    if (!empty(PrivateSession::getData('checkSetLevel'))) {
                        $post = array('orgId' => $this->orgId, 'year' => $data['dbyear'], 'kai' => $data['dbkai'], 'OrgSchoolYearId' => '', 'classId' => '', 'EikenLevelId' => '', 'name' => '', 'limit' => 20000, 'offset' => 0);
                        $this->recommendedService->setPupilRecommendLevel($post);
                        PrivateSession::clear('checkSetLevel');
                    }
                    if (!empty($localStorageRecommendLevel)) {
                        $data['ddbLevelChange'] = (array)$localStorageRecommendLevel;
                    }
                    if (!empty($data['ddbLevelChange']) && !(count($array_flip = array_flip($data['ddbLevelChange'])) == 1 && isset($array_flip[0]))) {
                        //save button customer choose level.
                        $this->recommendedService->updateRecommendLevel($this->orgId, $data['ddbLevelChange'], $eikenSchedule->getId());
                    }
                }
                else {
                    $this->flashMessenger()->addMessage(sprintf($translator->translate('MSG029'), $data['dbyear'], $data['dbkai']));
                }
            }
        }

        return $this->redirect()->toRoute(null, $return);
    }

    // Todo call api callGroupGetGMResult
    public function simpletestAction()
    {
        $config = $this->getServiceLocator()->get('Config')['invitationmnt_config']['api_einavi'];
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        $data = $request->getPost();
        if ($data) {
            $listPupilId = $this->em->getRepository('Application\Entity\RecommendLevel')->getListPersonalId($this->orgId, $data['dbyear']);
            if (!empty($listPupilId)) {
                $apiParams = array(
                    'bkeapi'            => array(
                        'proc_day'         => date("YmdHis"),
                        'group_id'         => $this->orgId,
                        'personal_id_list' => array_keys($listPupilId)
                    ),
                    'client_user_agent' => $this->getRequest()->getServer('HTTP_USER_AGENT'),
                    'client_ip_address' => $this->getRequest()->getServer('REMOTE_ADDR')
                );
                try {
                    $result = EinaviClient::getInstance()->callGroupGetGMResult($config, $apiParams);
                } catch (Exception $e) {
                    $this->flashMessenger()->addMessage($translator->translate('MSG45'));
                }
                if ($result->bkeapi->result == 1 && count($result->bkeapi->gm_result) > 0) {
                    try {
                        $this->recommendedService->saveSimpleTestLevel($result->bkeapi->gm_result, $listPupilId);
                    } catch (Exception $e) {
                        $this->flashMessenger()->addMessage($translator->translate('MSG45'));
                    }
                }
            }
        }

        return $this->redirect()->toRoute(null, array(
            'module'     => 'invitation-mnt',
            'controller' => 'recommended',
            'action'     => 'index'
        ));
    }

    public function setRecommendAction()
    {
        $app = $this->getServiceLocator()->get('Application');
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $request = $app->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            if (!$this->recommendedService->checkDateLine($data['dbyear'], $data['dbkai'])) {
                $this->flashMessenger()->addMessage(sprintf($translator->translate('MSG029'), $data['dbyear'], $data['dbkai']));
            } else {
                if (!$this->recommendedService->checkStandardLevelSetting($this->orgId, $data['dbyear'])) {
                    $this->flashMessenger()->addMessage(sprintf($translator->translate('MSG031'), $data['dbyear'], $data['dbkai']));
                } else {
                    $post = array('orgId' => $this->orgId, 'year' => $data['dbyear'], 'kai' => $data['dbkai'], 'OrgSchoolYearId' => '', 'classId' => '', 'EikenLevelId' => '', 'name' => '', 'limit' => 20, 'offset' => 0);
                    $this->recommendedService->setRecommendLevel($post);
                    PrivateSession::setData('checkSetLevel', array('recommendLevel' => true));
                }
            }
        } else {
            $this->flashMessenger()->addMessage($translator->translate('MSG404'));
        }

        return $this->redirect()->toRoute(null, array(
            'module'     => 'invitation-mnt',
            'controller' => 'recommended',
            'action'     => 'index'
        ));
    }

    /* Get Class when selected SchoolYear */
    public function getclassAction()
    {
        $schoolyearId = $this->params()->fromQuery('schoolyear');
        $year = $this->params()->fromQuery('year');
        $data = $this->recommendedService->getListClass($year, $schoolyearId, $this->orgId);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));

        return $response;
    }

    public function getKaiAction()
    {
        $year = $this->params()->fromQuery('year');
        $data = $this->em->getRepository('Application\Entity\EikenSchedule')->getKaiByYear($year);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode(empty($data) ? array('') : $data));

        return $response;
    }

    public function getSchoolYearAction()
    {
        $year = $this->params()->fromQuery('year');
        $data = $this->recommendedService->getListSchoolYearByYear($this->orgId, $year);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));

        return $response;
    }
}
