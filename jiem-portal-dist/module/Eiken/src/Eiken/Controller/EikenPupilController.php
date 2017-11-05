<?php
namespace Eiken\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Eiken\Service\ServiceInterface\ApplyEikenPupilServiceInterface;
use Eiken\Service\ServiceInterface\ApplyEikenOrgServiceInterface;
use Eiken\Service\ApplyEikenPupilService;
use Dantai\PrivateSession;
use Zend\View\Model\ViewModel;

class EikenPupilController extends AbstractActionController
{

    /**
     * 
     * @var \Eiken\Service\ApplyEikenPupilService
     */
    protected $eikenPupilService;

    protected $eikenOrgService;

    protected $organizationNo;

    const mainHallType = 1;

    public function __construct(ApplyEikenPupilServiceInterface $eikenPupilService, ApplyEikenOrgServiceInterface $eikenOrgService)
    {
        $user = PrivateSession::getData('userIdentity');
        $this->organizationNo = $user['organizationNo'];
        $this->eikenPupilService = $eikenPupilService;
        $this->eikenOrgService = $eikenOrgService;
    }

    public function indexAction()
    {
        //This functionality is temporarily closed on production GNCCNCJDM-258
        return $this->redirect()->toUrl('/');
        
        // Get condition parameters
        $page = $this->params()->fromRoute('page', 1);
        $eikenLevel = $this->params()->fromRoute('id', 0);

        if (!$eikenLevel || !in_array($eikenLevel, array(1, 2, 3, 4, 5,6,7)))
        {
            $this->getResponse()->setStatusCode(404);
            return false;
        }

        $orgId = $this->eikenOrgService->getOrganizationId();
        $isHallMaain = $this->eikenOrgService->isTheMainHall();
        $eikenScheduleId = $this->eikenOrgService->getEikenScheduleId();
        $kaiNumber = $this->eikenOrgService->getKaiNumber();
        $isPaymentOrg = $this->eikenOrgService->isPaymentOrg();

        // Dynamic bread cumbs
        $em = $this->getEntityManager();
        $eikenLevelEntiny = $em->getRepository('Application\Entity\EikenLevel')->find($eikenLevel);
        $this->getIndexBreadCumbs($eikenLevelEntiny);

        return $this->eikenPupilService->getPagedApplyEikenLevel($page, $orgId, $eikenLevel, $isHallMaain, $eikenScheduleId, $kaiNumber, $isPaymentOrg);

    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $editResult = $this->eikenPupilService->editApplyEikenLevel($id, $this->eikenOrgService->getOrganizationId());
        if (isset($editResult['status'])) {
            return $this->redirect()->toRoute('eikenorg', array('action' => 'create'));
        }
        $editResult['priceEikenLevelId'] = $this->getPriceEikenLevelId($editResult['eikenLevel']->getId());
        $this->getIndexBreadCumbs($editResult['eikenLevel']);

        return $editResult;
    }

    public function updateAction()
    {
        return $this->redirect()->toRoute(null, $this->eikenPupilService->updateApplyEikenLevel());
    }

    public function createAction()
    {
        $createResult = $this->eikenPupilService->createApplyEikenLevel($this->params(), $this->eikenOrgService->getEikenScheduleId());
        if (!is_object($createResult) && isset($createResult['status'])) {
            return $this->redirect()->toRoute('eikenorg', array('action' => 'create'));
        }
        $createResult['priceEikenLevelId'] = $this->getPriceEikenLevelId($createResult['eikenLevel']->getId());
        $this->getIndexBreadCumbs($createResult['eikenLevel']);

        return $createResult;
    }

    public function saveAction()
    {
        return $this->redirect()->toRoute(null, $this->eikenPupilService->saveApplyEikenLevel($this->eikenOrgService->getEikenScheduleId()));
    }

    public function destroyAction()
    {
        return $this->eikenPupilService->destroyApplyEikenLevel($this->params()->fromPost('selectedItems'), $this->eikenOrgService->getOrganizationId());
    }

    public function viewAction()
    {
        $viewResult = $this->eikenPupilService->viewApplyEikenLevel($this->params()->fromRoute('id', 0), $this->eikenOrgService->isValidTime(), $this->eikenOrgService->getOrganizationId());
        if (!is_object($viewResult) && isset($viewResult['status'])) {
            return $this->redirect()->toRoute('eikenorg', array('action' => 'create'));
        }
        $viewResult['priceEikenLevelId'] = $this->getPriceEikenLevelId($viewResult['apllyEikenLevel']->getEikenLevel()->getId());
        $this->getIndexBreadCumbs($viewResult['apllyEikenLevel']->getEikenLevel());

        return $viewResult;
    }

    public function loadMainHallAction()
    {
        $cityId = $this->params()->fromPost('cityId', 0);
        $eikenLevelId = $this->params()->fromPost('eikenLevelId', 0);
        $isFirstTime = $this->params()->fromPost('isFirstTime', 0) == 1? true : false;
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariable('mainHallAddresses', $this->eikenPupilService->loadMainHall($cityId, $eikenLevelId, $isFirstTime));
        return $view;
    }

    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    protected function getIndexBreadCumbs ($eikenLevel)
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('id', 'app_eik_pupil_list');
        $page->setLabel($translator->translate('FormFirstName'). '：' . $eikenLevel->getLevelName());
        $page->setParams(array('id' => $eikenLevel->getId()));
        // Dynamic org create page breadcumbs
        //$orgPage = $navigation->findBy('id', 'app_eik_org_create');
        //$year = date('Y');
        //$orgPage->setLabel('英検申込–' . $year . '年第' . $this->eikenOrgService->getKaiNumber() . '回');
    }

    function getPriceEikenLevelId($eikenLevelId)
    {
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $eikenLevelPrice = $dantaiService->getListPriceOfOrganization($this->organizationNo, array($eikenLevelId));
        if (!empty($eikenLevelPrice)) {
            return $eikenLevelPrice[self::mainHallType][$eikenLevelId]['price'];
        }

        return false;
    }
}
