<?php

namespace OrgMnt\Controller;

use Application\Service\CommonService;
use Application\Service\ServiceInterface\CommonServiceInterface;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Doctrine\ORM\EntityManager;
use OrgMnt\Service\SchoolYearService;
use OrgMnt\Service\ServiceInterface\SchoolYearServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use OrgMnt\Form\NewSchoolYearForm;
use Application\Entity\SchoolYear;
use Zend\Json\Json;

class SchoolYearController extends AbstractActionController
{
    /**
     * @var DantaiServiceInterface
     */
    protected $dantaiService;
    /**
     * @var SchoolYearServiceInterface
     */
    protected $schoolYearService;

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, SchoolYearServiceInterface $schoolYearService, EntityManager $entityManager)
    {
        $this->schoolYearService = $schoolYearService;
        $this->dantaiService = $dantaiService;
        $this->em = $entityManager;
    }

    public function indexAction()
    {
        $page = $this->params()->fromRoute('page', 1);
    
        $limit = 10;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        
        $em = $this->getEntityManager();
        $schoolyears = $em->getRepository('Application\Entity\SchoolYear')->getPagedSchoolYearList($limit, $offset);
        //$crossMessages = $this->dantaiService->getCrossEditingMessage('Application\Entity\SchoolYear');
    
        //return new ViewModel(array('schoolyears' => $schoolyears, 'jsMessages' => Json::encode($crossMessages)));
        return new ViewModel(array('schoolyears' => $schoolyears));
    }
    
    public function showAction()
    {
        $id = $this->params('id');
        $em = $this->getEntityManager();
        $schoolyear = $em->getRepository('Application\Entity\SchoolYear')->showSchoolYearDetail($id);
        
        return new ViewModel(array('schoolyear' => $schoolyear));
    }
    
    public function addAction()
    {
        $form = new NewSchoolYearForm();
        return array('form' => $form);
    }
    
    public function saveAction()
    {
        $em = $this->getEntityManager();
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        $schoolYear = new \Application\Entity\SchoolYear();
        
        if($request->isPost()) {
            $repository = $em->getRepository('Application\Entity\SchoolYear');
            $validator = new \DoctrineModule\Validator\ObjectExists(array(
                    'object_repository' => $repository,
                    'fields' => array('name'),
            ));
            $check =  $validator->isValid($_POST['name']);
            if($check){
                $this->redirect()->toRoute('org-mnt/default', array('controller' => 'schoolyear', 'action' => 'add'));
            }else {
                
                $schoolYear->setName($_POST['name']);
                $em->persist($schoolYear);
                $em->flush();
                $this->redirect()->toRoute('org-mnt/default', array('controller' => 'schoolyear', 'action' => 'index'));
            }
        }
        
        
    }
    
    public function editAction()
    {
        $form = new NewSchoolYearForm();
        $form->get('submit')->setAttribute('value', 'Edit');
        
        $id = $this->params()->fromRoute('id',0);
        $em = $this->getEntityManager();
        
        $schoolyear = $em->getRepository('Application\Entity\SchoolYear')->findOneBy(array('id' => $id));
        
        //$this->dantaiService->startCrossEditing($schoolyear);
        
        $form->setHydrator(new DoctrineHydrator($em, 'Application\Entity\SchoolYear'));
        $form->bind($schoolyear);
        $form->get('displayName')->setValue($schoolyear->getName());
        
//         $crossMessages = $this->dantaiService->restoreCrossEditingForm($schoolyear, $form);
//         $jsMessages = array(
//             'conflictWarning' => $crossMessages['conflictWarning'],
//             'conflictType' => $crossMessages['conflictType']
//         );
        
        return array(
            'form' => $form,
            'id' => $id,
            'schoolyearName' => $schoolyear->getName(),
            //'jsMessages' => Json::encode($jsMessages)
        );
    }
    
    public function updateAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $em = $this->getEntityManager();
        $schoolyear = $em->getRepository('Application\Entity\SchoolYear')->findOneBy(array(
            'id' => $id
        ));
        
        $route = array(
            'controller' => 'schoolyear',
            'action' => 'index'
        );
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
//             $crossMessages = $this->dantaiService->checkCrossEditing($schoolyear, null, $data);
            
//             if ($crossMessages['conflictWarning']) {
//                 if($crossMessages['conflictType'] == 'edit'){
//                     $route['action'] = 'edit';
//                     $route['id'] = $id;
//                 }
                
//                 return $this->redirect()->toRoute('org-mnt/default', $route);
//             }
            
            $schoolyear->setName($data['displayName']);
            $em->persist($schoolyear);
            $em->flush();
        }
        
        $this->redirect()->toRoute('org-mnt/default', $route);
        
    }
    
    public function deleteAction()
    {
        $id = $this->params('id');
        $em = $this->getEntityManager();
        $schoolyear = $em->getRepository('Application\Entity\SchoolYear')->findOneBy(array('id' => $id));
    
        $em->remove($schoolyear);
        $em->flush();
    
        $this->redirect()->toRoute('org-mnt/default', array('controller' => 'schoolyear', 'action' => 'index'));
    }
    
    public function deletemultiAction()
    {
        if(isset($_POST["listid"])){
            $array = array();
            $array = explode(',', $_POST["listid"]);
            foreach ($array as $k=>$v){
                $em = $this->getEntityManager();
                $schoolyear = $em->getRepository('Application\Entity\SchoolYear')->findOneBy(array('id' => $v));
                
                $em->remove($schoolyear);
                $em->flush();
            }
            $this->redirect()->toRoute('org-mnt/default', array('controller' => 'schoolyear', 'action' => 'index'));
        }else{
            $this->redirect()->toRoute('org-mnt/default', array('controller' => 'schoolyear', 'action' => 'index'));
        }
    }
    

    /**
     * @return array|object
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}