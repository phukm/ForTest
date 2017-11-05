<?php
namespace Eiken\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Eiken\Service\ServiceInterface\EikenIdServiceInterface;
use Eiken\Service\ServiceInterface\ApplyEikenOrgServiceInterface;

class EikenIdController extends AbstractActionController
{

    /**
     * 
     * @var \Eiken\Service\EikenIdService
     */
    protected $eikenIdService;

    /**
     * 
     * @var \Eiken\Service\ApplyEikenOrgService
     */
    protected $eikenOrgService;

    public function __construct(EikenIdServiceInterface $eikenIdService, ApplyEikenOrgServiceInterface $eikenOrgService)
    {
        $this->eikenIdService = $eikenIdService;
        $this->eikenOrgService = $eikenOrgService;
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $validQuery = $this->eikenIdService->isValidQuery($this->params());
        if (!$validQuery)
        {
            $this->getResponse()->setStatusCode(404);
            return false;
        }
        parent::onDispatch($e);
    }
    
    public function registerAction()
    {
        //This functionality is temporarily closed on production GNCCNCJDM-258
        return $this->redirect()->toUrl('/');

        $this->getIndexBreadCumbs($this->params()->fromRoute('levelid'));
        return $this->eikenIdService->registerEiken($this->params(), $this->eikenOrgService->getOrganizationId());
    }

    public function referenceAction()
    {
        //This functionality is temporarily closed on production GNCCNCJDM-258
        return $this->redirect()->toUrl('/');

        $this->getIndexBreadCumbs($this->params()->fromRoute('levelid'));
        return $this->eikenIdService->referEiken($this->params(), $this->eikenOrgService->getOrganizationId());
    }

    /**
     * @todo
     * simulate ukesuke API
     */
    public function saveAction()
    {
        $data = $this->eikenIdService->savePersonalInfo($this->eikenOrgService->getOrganizationId(), $this->eikenOrgService->getEikenScheduleId());
        return $this->getResponse()->setContent($data);
    }

    /**
     * This actin for call Ajax
     */
    public function loadReferenceApiAction()
    {
        // get post data
        $eikenId = $this->params()->fromPost('eikenId');
        $eikenPass = $this->params()->fromPost('pass');

        // get data by API
        $data = $this->eikenIdService->callToApi($eikenId, $eikenPass);

        return $this->getResponse()->setContent($data);
    }

    public function getclassAction()
    {
        $data = $this->eikenIdService->getClass($this->params(), $this->eikenOrgService->getOrganizationId());
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));
        return $response;
    }
    protected function getIndexBreadCumbs ($eikenLevelId)
    {
        $em = $this->getEntityManager();
        $eikenLevel = $em->getRepository('Application\Entity\EikenLevel')->find($eikenLevelId);
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('id', 'app_eik_pupil_list');
        $page->setLabel($translator->translate('FormFirstName'). '：' . $eikenLevel->getLevelName());
        $page->setParams(array('id' => $eikenLevel->getId()));
        // Dynamic org create page breadcumbs
//         $orgPage = $navigation->findBy('id', 'app_eik_org_create');
//         $year = date('Y');
//         $orgPage->setLabel('英検申込–' . $year . '年第' . $this->eikenOrgService->getKaiNumber() . '回');
    }
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}
