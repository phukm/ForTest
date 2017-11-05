<?php

/**
 * View folder : view\class
 */
namespace OrgMnt\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use OrgMnt\Service\ServiceInterface\ClassServiceInterface;
use OrgMnt\Service\ClassService;
use Zend\Json\Json;

class ClassController extends AbstractActionController
{

    protected $id_org = 0;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var ClassService
     */
    protected $classService;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, ClassServiceInterface $classService)
    {
        $this->dantaiService = $dantaiService;
        $this->classService = $classService;
        $user = $this->dantaiService->getCurrentUser();
        $this->id_org = $user['organizationId'];
    }

    public function indexAction()
    {        
        $em = $this->classService->getEntityManager();
        $trans = $this->params()->fromQuery('trans');
        if ($trans == 1) {
            $validator = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $em->getRepository('Application\Entity\ClassJ'),
                'fields' => array(
                    'year',
                    'organizationId',
                    'isDelete'
                )
            ));
            $checkValid = $validator->isValid(array(
                $this->classService->getCurrentYear(),
                $this->id_org,
                $isDelete = 0
            ));
            if ($checkValid == true) {
                return $this->redirect()->toRoute(null, array(
                    'module' => 'org-mnt',
                    'controller' => 'class',
                    'action' => 'index'
                ));
            } else {
                return $this->redirect()->toRoute(null, array(
                    'module' => 'org-mnt',
                    'controller' => 'class',
                    'action' => 'add'
                ));
            }
        }
        $routeMatch = $this->getEvent()
            ->getRouteMatch()
            ->getParam('controller') . '_' . $this->getEvent()
            ->getRouteMatch()
            ->getParam('action');
        return $this->classService->getPagedListClass($this->getEvent(), $routeMatch, $this->getRequest(), $this->params(), $this->flashMessenger(), $this->dantaiService);
    }

    public function showAction()
    {
        $rel = $this->classService->getDetailClass($this->params('id', 0), $this->flashMessenger());
        if ($rel === 1) {
            return $this->redirects();
        } else {
            return $rel;
        }
    }

    public function addAction()
    {
        return $this->classService->getAddClass();
    }

    public function saveAction()
    {
        $this->classService->getSaveClass($this->getRequest());
        return $this->redirects();
    }

    public function editAction()
    {
        $rel = $this->classService->getEditByClass($this->params('id', 0), $this->getRequest(), $this->params(), $this->flashMessenger());
        if ($rel === 1) {
            return $this->redirects();
        } else {
            return $rel;
        }
    }
    // update
    public function updateAction()
    {
        return $this->redirect()->toRoute(null, $this->classService->getUpdateClass($this->getRequest(), $this->params(), $this->flashMessenger()));
    }
    // delete
    public function deleteAction()
    {
        $rel = $this->classService->getDeleteClass($this->params('id', 0), $this->getRequest(), $this->params(), $this->flashMessenger());
        if (isset($rel['redilect'])) {
            return $this->redirect()->toRoute(null, $rel['data']);
        } else {
            return $this->redirects();
        }
    }

    public function checkduplicateAction()
    {
        return $this->classService->getCheckDupliCate($this->params(), $this->getResponse());
    }
    
//    check 2 first character for : #F1GNCJIEMDPR6-8
    public function isFirstCharacterAction()
    {
        return $this->classService->isNotShowMSGGradeClass($this->params(), $this->getResponse());
    }
    
    public function redirects()
    {
        return $this->redirect()->toRoute(null, array(
            'module' => 'org-mnt',
            'controller' => 'class',
            'action' => 'index'
        ));
    }
    
    public function checkDuplicateUpdateAction()
    {
        $result = $this->classService->getCheckDuplicateUpdate($this->params());
        $messages = $this->classService->getMessages();
        $dataPost = $this->params()->fromPost();
        $data = array('status' => $result, 'msg' => str_replace('クラス', $dataPost['classname'] ? $dataPost['classname'] : 'クラス', $messages['MsgDuplicationClassName']));
        return $this->getResponse()->setContent(Json::encode($data));
    }
    
    //    check 2 first character for : #F1GNCJIEMDPR6-8
    public function isFirstCharacterUpdateAction()
    {
        return $this->classService->isNotShowMSGClassForUpdate($this->params(), $this->getResponse());
    }
    
}
