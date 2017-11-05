<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Report for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Report\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Report\Service\ServiceInterface\ReportServiceInterface;
use Doctrine\ORM\EntityManager;

class ReportController extends AbstractActionController {

    protected $id_org = 0;
    protected $currentUser;

    /**
     *
     * @var $dantaiService Application\Service\DantaiService
     */
    protected $dantaiService;

    /**
     *
     * @var $reportService \Report\Service\ReportService
     */
    protected $reportService;

    /**
     *
     * @var $em \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, ReportServiceInterface $reportService, EntityManager $entityManager) {
        $this->dantaiService = $dantaiService;
        $this->reportService = $reportService;
        $user = $this->dantaiService->getCurrentUser();
        $this->currentUser = $user;
        $this->id_org = $user['organizationId'];
        $this->em = $entityManager;
    }

    public function indexAction() {
        return array();
    }

    public function fooAction() {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /report/report/foo
        return array();
    }

    // result actual compared goal
    public function actualgoallevelAction() {
        $listYear = \Dantai\Utility\DateHelper::getListYearToSearch();
        $objSchoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->id_org);
        $listSchoolYear = array();
        if ($objSchoolyear) {
            foreach ($objSchoolyear as $value) {
                $listSchoolYear[$value['id']] = $value['displayName'];
            }
        }
        $search = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'year' => date('Y'),
            'orgSchoolYearId' => '',
            'type' => 'Deem',
            'searchVisible' => 0
        ));

        if ($this->getRequest()->isPost() && $search['token']) {
            return $this->redirect()->toUrl('/report/report/actualgoallevel/search/' . $search['token']);
        }
        
        $lastOrgSchoolYear = end($objSchoolyear);
        $lastOrgSchoolYearId = $lastOrgSchoolYear ? $lastOrgSchoolYear['id'] : 0;
        list($goalResultSchoolYear, $goalResultClass, $graduationGoal, $class) = $this->reportService->getDataGoalResult($this->id_org, $search, $lastOrgSchoolYearId);
        $resultSchoolYear = isset($goalResultSchoolYear[$search['year']]) ? $goalResultSchoolYear[$search['year']] : array();
        $resultClass = isset($goalResultClass[$search['year']]) ? $goalResultClass[$search['year']] : array();
        $resultGoal = isset($graduationGoal[$search['year']]) ? $graduationGoal[$search['year']] : array();
        return new ViewModel(array(
            'listYear' => $listYear,
            'listSchoolYear' => $listSchoolYear,
            'search' => $search,
            'resultSchoolYear' => $resultSchoolYear,
            'resultClass' => $resultClass,
            'resultGoal' => $resultGoal,
            'class' => $class,
            'searchVisible' => isset($search['searchVisible']) ? $search['searchVisible'] : 0
        ));
    }

    public function actualgoalyearAction() {

        $objSchoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->id_org);
        $listSchoolYear = array();
        if ($objSchoolyear) {
            foreach ($objSchoolyear as $value) {
                $listSchoolYear[$value['id']] = $value['displayName'];
            }
        }

        $search['yearTo'] = date('Y');
        $search['yearFrom'] = $search['yearTo'] - 2;
        $search['objectType'] = 'OrgSchoolYear';
        $search['type'] = 'Deem';
        
        $lastOrgSchoolYear = end($objSchoolyear);
        $lastOrgSchoolYearId = $lastOrgSchoolYear ? $lastOrgSchoolYear['id'] : 0;

        list($resultSchoolYear, $resultGoal) = $this->reportService->getDataGoalResultOfOrgSchoolYears($this->id_org, $search, $lastOrgSchoolYearId);
        return new ViewModel(array(
            'resultSchoolYear' => $resultSchoolYear,
            'resultGoal' => $resultGoal,
            'search' => $search,
            'listSchoolYear' => $listSchoolYear,
        ));
    }

    public function cseScoreTotalAction() {
        $listYear = \Dantai\Utility\DateHelper::getListYearToSearch();
        $objSchoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->id_org);
        $listSchoolYear = array();
        if ($objSchoolyear) {
            foreach ($objSchoolyear as $value) {
                $listSchoolYear[$value['id']] = $value['displayName'];
            }
        }

        $search = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'objectType' => 'Organization',
            'orgSchoolYearId' => '',
            'classIdSelected' => '',
            'classId' => '',
            'yearFrom' => (date('Y')),
            'yearTo' => date('Y'),
            'type' => '',
            'searchVisible' => 0
        ));
        if ($this->getRequest()->isPost() && $search['token']) {
            return $this->redirect()->toUrl('/report/report/csescoretotal/search/' . $search['token']);
        }
        $classj = Null;
        if (!empty($search['classId'])) {
            $classj = $this->em->getRepository('Application\Entity\ClassJ')->find(intval($search['classId']));
        }

        list($cseResult, $cseScore, $cseResultTitle) = $this->reportService->getDataCseResult($this->id_org, $search);

        $jMessages = array(
            'MSG30_FromDate_Greater_ToDate' => $this->translate('MSG30_FromDate_Greater_ToDate')
        );
        return new ViewModel(array(
            'listYear' => $listYear,
            'listSchoolYear' => $listSchoolYear,
            'search' => $search,
            'searchVisible' => isset($search['searchVisible']) ? $search['searchVisible'] : 0,
            'cseResult' => $cseResult,
            'cseScore' => $cseScore,
            'cseResultTitle' => $cseResultTitle,
            'classj' => $classj,
            'jMessages' => $jMessages,
            'currentUser' => $this->currentUser,
        ));
    }

    public function loadClassAction() {
        $class = array();
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();

            $orgSchoolYearId = isset($params['orgSchoolYearId']) ? intval($params['orgSchoolYearId']) : 0;
            $yearFrom = isset($params['yearFrom']) ? intval($params['yearFrom']) : 0;
            $yearTo = isset($params['yearTo']) ? intval($params['yearTo']) : 0;

            if ($orgSchoolYearId > 0 && ($yearFrom > 0 || $yearTo > 0)) {
                $classRepo = $this->em->getRepository('Application\Entity\ClassJ');
                $class = $classRepo->getListClassBySchoolYearAndBetweenYear($this->id_org, $orgSchoolYearId, $yearFrom, $yearTo);
            }

            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            $response->setContent(json_encode($class));

            return $response;
        }
    }

    public function translate($msgKey) {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        return $translator->translate($msgKey);
    }

}
