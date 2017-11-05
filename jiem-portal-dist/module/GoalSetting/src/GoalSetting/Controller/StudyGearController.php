<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/GoalSetting for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace GoalSetting\Controller;

use Application\Service\CommonService;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use GoalSetting\Form\StudyGearListForm;
use GoalSetting\Form\listHistoryStudyForm;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Filter\Null;
use Application\Entity\EinaviExam;
use Application\Entity\InquiryMeasure;
use Application\Entity\InquiryStudyGear;
use Application\Entity\LearningHistory;
use Application\Entity\ClassJ;
use Application\Entity\OrgSchoolYear;
use Zend\Json\Json;
use Zend\Validator\File\Count;
use Dantai\Utility\CharsetConverter;
use Zend\Validator\Explode;
use Doctrine\ORM\Query\AST\Functions\TrimFunction;
use GoalSetting\Service\ServiceInterface\StudyGearServiceInterface;
use Zend\Filter\HtmlEntities;
use Dantai\PrivateSession;
use Zend\Session\Container;
use Dantai\Api\EinaviClient;
use Dantai;
use Dantai\Api\UkestukeClient;
use Application\Entity\Application\Entity;
use Dantai\Utility\DateHelper;

class StudyGearController extends AbstractActionController
{
    use \Application\Controller\ControllerAwareTrait;

    protected $org_id;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var StudyGearServiceInterface
     */
    protected $studyGearService;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, StudyGearServiceInterface $studyGearService, EntityManager $entityManager)
    {
        $this->dantaiService = $dantaiService;
        $this->studyGearService = $studyGearService;
        $this->em = $entityManager;
        $user = $this->dantaiService->getCurrentUser();
        $this->org_id = $user['organizationId'];
    }

    // /goalsetting/StudyGear/index
    public function indexAction()
    {
        // TODO Group_GetSGUserHistory
        $form = new StudyGearListForm();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $currentDate = date('Y-m-d');
        $year =DateHelper::getCurrentYear();// $this->getCurrentYear($currentDate);
        $orgSchoolYearId = 0;
        $classId = 0;
        $fullName = '';
        $listYear = array();
        for ($i = $year + 2; $i >= 2010; $i --) {
            $listYear[$i] = $i;
        }

        $searchArray = $this->dantaiService->getSearchCriteria($this->getEvent(), $this->params()
            ->fromPost());
        if ($this->isPost() && $searchArray['token']) {
            return $this->redirect()->toRoute('goal-setting/default', array(
                'controller' => 'studygear',
                'action' => 'index',
                'search' => $searchArray['token']
            ));
        }

        if (! empty($searchArray)) {
            $year = isset($searchArray['ddlYear']) ? $searchArray['ddlYear'] : '';
            $orgSchoolYearId = isset($searchArray['ddlSchoolYear']) ? $searchArray['ddlSchoolYear'] : '';
            $classId = isset($searchArray['ddlClass']) ? $searchArray['ddlClass'] : '';
            $fullName = isset($searchArray['txtFullName']) ? $this->_trimSpaceUnicode($searchArray['txtFullName']) : '';
        }
        $listClass = array();
        if (! empty($year) && ! empty($orgSchoolYearId)) {
            $objClass = $this->em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYearAndYearAjax($year, $orgSchoolYearId, $this->org_id);
            if (! empty($objClass)) {
                foreach ($objClass as $key => $value) {
                    $listClass[$value['id']] = $value['className'];
                }
            }
        }
        $schoolYear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->org_id);
        $yearSchool = array();
        if (isset($schoolYear) && $schoolYear) {
            foreach ($schoolYear as $key => $value) {
                $yearSchool[$value['id']] = $value['displayName'];
            }
        }
        $form->get('ddlSchoolYear')
            ->setValueOptions($yearSchool)
            ->setValue($orgSchoolYearId);
        $form->get("ddlYear")
            ->setValueOptions($listYear)
            ->setValue($year);
        $form->get("txtFullName")->setValue($fullName);
        $form->get("ddlClass")
            ->setValueOptions($listClass)
            ->setValue($classId);
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $paginator = $this->em->getRepository('Application\Entity\Pupil')->getListInquiryProgressPupil($this->org_id, $year, $orgSchoolYearId, $classId, addslashes($fullName), $limit, $offset);
        $pupil = $paginator->getItems($offset, $limit);
        // TODO GET API GROUP SG
        $agent = (object) array(
            'REMOTE_ADDR' => $this->getRequest()->getServer('REMOTE_ADDR'),
            'HTTP_USER_AGENT' => $this->getRequest()->getServer('HTTP_USER_AGENT')
        );
        $personal_id_list = array();
        $sg_history = array();
        $data = array();
        foreach ($pupil as $key => $val) {
            if (! empty($val['personalId'])) {
                $personal_id_list[] = $val['personalId'];
            }
        }
        if (count($personal_id_list) > 0) {
            $GetSGUserHistory = $this->GroupGetSGUserHistory($personal_id_list, $agent);
            if ($GetSGUserHistory->bkeapi->result == 1) {
                $sg_history = empty($GetSGUserHistory->bkeapi->sg_history) ? array() : $GetSGUserHistory->bkeapi->sg_history;
            }
            foreach ($pupil as $p) {
                foreach ($sg_history as $item) {
                    if ($item->personal_id === $p['personalId']) {
                        $p['plan_grade'] = $item->study_history->plan_grade;
                        $p['learning_time'] = $item->study_history->learning_time;
                        $p['pass_fail'] = $item->measure_history->pass_fail;
                        $p['last_used_date'] = $item->study_history->last_used_date;
                    }
                }
                array_push($data, $p);
            }
        } else {
            $data = $pupil;
        }
        return new viewModel(array(
            'pupil' => $data,
            'paginator' => $paginator,
            'form' => $form,
            'page' => $page,
            'numPerPage' => $limit,
            'searchVisible' => isset($searchArray['token']) ? 1 : 0,
            'param' => isset($searchArray['token']) ? $searchArray['token'] : '',
            'noRecord' => $translator->translate('MSG13')
        ));
    }

    public function _trimSpaceUnicode($string)
    {
        return preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $string);
    }

    // /goalsetting/studygear/show
    public function showAction()
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        // Todo api get GroupGetSGHistory
        $agent = (object) array(
            'REMOTE_ADDR' => $this->getRequest()->getServer('REMOTE_ADDR'),
            'HTTP_USER_AGENT' => $this->getRequest()->getServer('HTTP_USER_AGENT')
        );
        $study_history = null;
        $measure_history = null;
        $personal_id = $this->params()->fromQuery('perId', 0); // 3000036414-3000036419
        if ($personal_id > 0) {
            $SGHistory = $this->GroupGetSGHistory($personal_id, $agent);

            if ($SGHistory->bkeapi->result == 1) {

                $study_history = $SGHistory->bkeapi->study_history;
                if (! empty($study_history)) {
                    usort($study_history, function ($a, $b) {
                        $ad = new \DateTime($a->last_used_date);
                        $bd = new \DateTime($b->last_used_date);

                        if ($ad == $bd) {
                            return 0;
                        }

                        return $ad < $bd ? 1 : - 1;
                    });
                }
                $measure_history = $SGHistory->bkeapi->measure_history;
                if (! empty($measure_history)) {
                    usort($measure_history, function ($a, $b) {
                        $ad = new \DateTime($a->last_used_date);
                        $bd = new \DateTime($b->last_used_date);

                        if ($ad == $bd) {
                            return 0;
                        }

                        return $ad < $bd ? 1 : - 1;
                    });
                }
            }
        }
        return new viewModel(array(
            'lstStudyHis' => $study_history,
            'lstTestHis' => $measure_history,
            'noRecord' => $translator->translate('MSG13')
        ));
    }

    function cmp($a, $b)
    {
        $ad = new \DateTime($a->last_used_date);
        $bd = new \DateTime($b->last_used_date);

        if ($ad == $bd) {
            return 0;
        }

        return $ad < $bd ? 1 : - 1;
    }

    public function ajaxGetListClassAction()
    {
        $year = $this->params()->fromPost('year', $this->getYear());
        $schoolyear = $this->params()->fromPost('schoolyear', null);
        $string = array();
        if (isset($schoolyear) && $schoolyear) {
            $objClass = $this->em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYearAndYearAjax($year, $schoolyear, $this->org_id);
            if (! empty($objClass)) {
                foreach ($objClass as $key => $value) {
                    $string['classj'][$key] = $value;
                }
            }
        } else {
            $string['classj'] = '';
        }

        return $this->getResponse()->setContent(Json::encode($string));
    }

    public function remove_special_characters($string)
    {
        $string = str_replace(array(
            "'",
            '"'
        ), array(
            "",
            ""
        ), $string);

        return $string;
    }

    // get api : Group_GetSGUserHistory
    protected function GroupGetSGHistory($personal_id, $agent)
    {
        $apiParams = array(
            'bkeapi' => array(
                'proc_day' => date("YmdHis"),
                'group_id' => $this->org_id,
                'personal_id' => $personal_id
            ),
            'client_user_agent' => $agent->HTTP_USER_AGENT,
            'client_ip_address' => $agent->REMOTE_ADDR
        );
        $config = $this->getServiceLocator()->get('Config')['goalsetting_config']['api_userauthen'];
        try {
            $result = EinaviClient::getInstance()->callGroupGetSGHistory($config, $apiParams);
        } catch (Exception $e) {
            return false;
        }

        return $result;
    }

    protected function GroupGetSGUserHistory($personal_id_list, $agent)
    {
        $apiParams = array(
            'bkeapi' => array(
                'proc_day' => date("YmdHis"),
                'group_id' => $this->org_id,
                'personal_id_list' => $personal_id_list
            ),
            'client_user_agent' => $agent->HTTP_USER_AGENT,
            'client_ip_address' => $agent->REMOTE_ADDR
        );
        $config = $this->getServiceLocator()->get('Config')['goalsetting_config']['api_userauthen'];
        try {
            $result = EinaviClient::getInstance()->callGroupGetSGUserHistory($config, $apiParams);
        } catch (Exception $e) {
            return false;
        }

        return $result;
    }

    function listHistoryStudyAction()
    {
        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'orgSchoolYear' => $this->params()->fromPost('ddlSchoolYear'),
            'class' => $this->params()->fromPost('ddlClass'),
            'eikenGrade' => $this->params()->fromPost('eikenGrade'),
            'fromDate' => $this->params()->fromPost('fromDate'),
            'toDate' => $this->params()->fromPost('toDate'),
            'searchVisible' => 0
        ));
        $searchVisible = isset($searchCriteria['searchVisible']) ? $searchCriteria['searchVisible'] : 0;
        $routeMatch = $this->getEvent()
            ->getRouteMatch()
            ->getParam('controller') . '_' . $this->getEvent()
            ->getRouteMatch()
            ->getParam('action');
        if ($this->isPost() && $searchCriteria['token']) {
            return $this->redirect()->toUrl('/goalsetting/studygear/listhistorystudy/search/' . $searchCriteria['token']);
        }
        $viewModel = new ViewModel($this->studyGearService->getListHistoryStudy($searchVisible, $this->isPost(), $searchCriteria, $routeMatch, $this->getRequest(), $this->params(), $this->dantaiService, $this->redirect()));
        return $viewModel;
    }

    function studyGearDetailAction()
    {
        $type = $this->params()->fromRoute('type', '');
        if ($type == '1') 
        {
            $learningType = '単熟語';
        }
        else if ($type == '2') 
        {
            $learningType = '文法';
        } 
        else if ($type == '3') 
        {
            $learningType = 'リーディング';
        } 
        else if ($type == '4') 
        {
            $learningType = 'リスニング';
        }
        else if ($type == '5')
        {
            $learningType = '英検形式に慣れる';
        }
        else 
        {
            $learningType = '';
        }
        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $date = date('Y-m-d', strtotime($this->params()->fromRoute('date', '')));
        $schoolyear = $this->params()->fromRoute('schoolyear', '');
        $class = $this->params()->fromRoute('class', '');
        $eiken = $this->params()->fromRoute('level', '');
        $search = $this->params()->fromRoute('search', '');
        $query = array(
            'controller' => 'studygear',
            'action' => 'studygeardetail'
        );
        if ($type) 
        {
            $query['type'] = $type;
        }
        if ($date) 
        {
            $query['date'] = $this->params()->fromRoute('date');
        }
        if ($schoolyear) 
        {
            $query['schoolyear'] = $schoolyear;
        }
        if ($type) 
        {
            $query['class'] = $class;
        }
        if ($type) 
        {
            $query['eiken'] = $eiken;
        }
        $paginator = $this->em->getRepository('Application\Entity\LearningHistory')->getDetailHistory($this->org_id, $date, $learningType, $schoolyear, $class, $eiken);
        
        $dateArr = array('日','月','火','水','木','金','土');
        $this->setBreadCumbs($date, $learningType);
        return new viewModel(array(
            'learningType' => $learningType,
            'history' => $paginator->getItems($offset, $limit),
            'paginator' => $paginator,
            'page' => $page,
            'numPerPage' => $limit,
            'query' => $query,
        	'dateArr' => $dateArr,
            'search' => $search
        ));
    }

    function getYear()
    {
        if (date("m") < 4) {
            return date("Y") - 1;
        } else {
            return date("Y");
        }
    }

    function secToTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = round($seconds / 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    protected function setBreadCumbs($date, $type)
    {
    	$dateArr = array('日','月','火','水','木','金','土');
        $type = ($type) ? $type : '合計';
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('id', 'studygeardetail');
        $page->setLabel($translator->translate('スタギア進捗 – '). date('m/d', strtotime($date)).'（'.$dateArr[date('w', strtotime($date))].'）– ' . $type);
    }
}
