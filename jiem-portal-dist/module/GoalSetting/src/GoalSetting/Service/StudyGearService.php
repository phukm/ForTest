<?php
namespace GoalSetting\Service;

use GoalSetting\Service\ServiceInterface\StudyGearServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Dantai\PrivateSession;
use History\Form\SearchInquiryEikenForm;
use Dantai;
use Zend\View\Model\ViewModel;
use Zend\Validator\File\Count;
use Dantai\Utility\CharsetConverter;
use Zend\Validator\Explode;
use Doctrine\ORM\Query\AST\Functions\TrimFunction;
use Zend\Filter\HtmlEntities;
use Zend\Json\Json;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Session\Container;
use Application\Controller\ControllerAwareTrait;
use GoalSetting\Form\StudyGearListForm;
use GoalSetting\Form\listHistoryStudyForm;

class StudyGearService implements StudyGearServiceInterface, ServiceLocatorAwareInterface
{
    use \Application\Controller\ControllerAwareTrait;
    use ServiceLocatorAwareTrait;
    protected $org_id;
    
    public function __construct()
    {
        $user = PrivateSession::getData('userIdentity');
        $this->org_id = $user['organizationId'];
    }
    
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
    
    public function getListHistoryStudy($searchVisible, $post, $searchCriteria, $routeMatch, $request, $params, $dantaiService, $redirect)
    {
        $em = $this->getEntityManager();
        $form = new listHistoryStudyForm();
        
        $listSchoolYear = $em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->org_id,$year = $this->getYear());
        $schoolYear = array();
        if (isset($listSchoolYear)) {
            foreach ($listSchoolYear as $key => $value) {
                $schoolYear[$value['id']] = $value['displayName'];
            }
            $form->get("ddlSchoolYear")->setValueOptions($schoolYear);
        }
        $toDate = new \DateTime('-1 days');
        $toDate = $toDate->format('Y/m/d');
        $fromDate = new \DateTime('-7 days');
        $fromDate = $fromDate->format('Y/m/d');
        $toDate = ($searchCriteria['toDate']) ? $searchCriteria['toDate'] : $toDate;
        $fromDate = ($searchCriteria['fromDate']) ? $searchCriteria['fromDate'] : $fromDate;
    
        $form->get("fromDate")->setAttribute('value',$fromDate);
        $form->get("toDate")->setAttribute('value',$toDate);
        $schoolYearId = ($searchCriteria['orgSchoolYear']) ? $searchCriteria['orgSchoolYear'] : null;
        $class = ($searchCriteria['class']) ? $searchCriteria['class'] : null;
        $eikenGrade = ($searchCriteria['eikenGrade']) ? $searchCriteria['eikenGrade'] : null;
        $form->get("ddlSchoolYear")->setAttribute('value',$schoolYearId);
        $urlParam = '';
        if($searchCriteria['orgSchoolYear'])
        {
            $objClass = $em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYearAndYearAjax($this->getYear(), $schoolYearId, $this->org_id);
            if (! empty($objClass)) {
                foreach ($objClass as $value) {
                    $classArr[$value['id']] = $value['className'];
                }
                $form->get("ddlClass")->setAttribute('value',$class)->setValueOptions($classArr);
            }          
            if($searchCriteria['orgSchoolYear']) $urlParam = $urlParam.'/schoolyear/'.$searchCriteria['orgSchoolYear'];
            if($searchCriteria['class']) $urlParam = $urlParam.'/class/'.$searchCriteria['class'];
        }
        if($searchCriteria['eikenGrade']) $urlParam = $urlParam.'/level/'.$searchCriteria['eikenGrade'];
        if($params->fromRoute('search')) $urlParam = $urlParam.'/search/'.$params->fromRoute('search');
        $form->get("eikenGrade")->setAttribute('value',$eikenGrade);
        $numDays = floor((strtotime($toDate)-strtotime($fromDate))/(60*60*24));
        for ($i=0; $i<=$numDays; $i++) {
            $date = date('Ymd', strtotime($fromDate . "+ $i days"));
            $listHistory[$date]['vocabularyTime'] = '00:00';
            $listHistory[$date]['grammarTime'] = '00:00';
            $listHistory[$date]['readingTime'] = '00:00';
            $listHistory[$date]['listeningTime'] = '00:00';
            $listHistory[$date]['totalTime'] = '00:00';
            $listHistory[$date]['vocabularyPeople'] = 0;
            $listHistory[$date]['grammarPeople'] = 0;
            $listHistory[$date]['readingPeople'] = 0;
            $listHistory[$date]['listeningPeople'] = 0;
            $listHistory[$date]['totalPeople'] = 0;
            $listHistory[$date]['eikenTime'] = '00:00';
            $listHistory[$date]['eikenPeople'] = 0;
    
        }
        $history = $em->getRepository('Application\Entity\InquiryStudyGear')->getInquiryStudyGear($this->org_id, $fromDate,$toDate, $schoolYearId,$class,$eikenGrade);
        foreach ($history as $key => $value) {
            $date = date_format($value['inquiryDate'], 'Ymd');
            $listHistory[$date]['vocabularyTime'] = ($value['vocabularyTime'])? $this->minutesToTime($value['vocabularyTime']):'00:00';
            $listHistory[$date]['grammarTime'] = ($value['grammarTime'])? $this->minutesToTime($value['grammarTime']):'00:00';
            $listHistory[$date]['readingTime'] = ($value['readingTime'])? $this->minutesToTime($value['readingTime']):'00:00';
            $listHistory[$date]['listeningTime'] = ($value['listeningTime'])? $this->minutesToTime($value['listeningTime']):'00:00';
            $listHistory[$date]['totalTime'] = ($value['totalTime'])? $this->minutesToTime($value['totalTime']):'00:00';
            $listHistory[$date]['vocabularyPeople'] = ($value['vocabularyPeople'])?(int)$value['vocabularyPeople']:0;
            $listHistory[$date]['grammarPeople'] = ($value['grammarPeople'])?(int)$value['grammarPeople']:0;
            $listHistory[$date]['readingPeople'] = ($value['readingPeople'])?(int)$value['readingPeople']:0;
            $listHistory[$date]['listeningPeople'] = ($value['listeningPeople'])?(int)$value['listeningPeople']:0;
            $listHistory[$date]['totalPeople'] = ($value['totalPeople'])?(int)$value['totalPeople']:0;
            $listHistory[$date]['eikenTime'] = ($value['eikenTime'])?$this->minutesToTime($value['eikenTime']):'00:00';
            $listHistory[$date]['eikenPeople'] = ($value['eikenPeople'])?(int)$value['eikenPeople']:0;
        }
    
        $measureKind = "";
        $listExam = array();
        $exam = $em->getRepository('Application\Entity\InquiryMeasure')
                ->getInquiryMeasure($this->org_id, $fromDate,$toDate, $schoolYearId,$class,$eikenGrade);
        for ($i=0; $i<=$numDays; $i++) {
            $date = date('Ymd', strtotime($fromDate . "+ $i days"));
            foreach ($exam as $key => $value) {
                $examDate = date_format($value['inquiryDate'], 'Ymd');
                if ($examDate == $date) {
                    $listExam[$value['measureTime']][$date]['pass'] = (int) $value['pass'];
                    $listExam[$value['measureTime']][$date]['fail'] = (int) $value['fail'];
                } else {
                    $listExam[$value['measureTime']][$date]['pass'] = !isset($listExam[$value['measureTime']][$date]['pass']) ? 0 : $listExam[$value['measureTime']][$date]['pass'];
                    $listExam[$value['measureTime']][$date]['fail'] = !isset($listExam[$value['measureTime']][$date]['fail']) ? 0 : $listExam[$value['measureTime']][$date]['fail'];
                }
            }
        }
        
        $dateArr = array('日','月','火','水','木','金','土');
        return array(
            'form' => $form,
            'listHistory'=>$listHistory,
            'listExam' => $listExam,
            'numDays' => $numDays,
            'fromDate' => $fromDate,
            'urlParam' => $urlParam,
            'searchVisible'  => $searchVisible,
        	'dateArr' => $dateArr
        );
    }
    
    function getYear()
    {
        if (date("m") < 4) {
            return date("Y") - 1;
        } else {
            return date("Y");
        }
    }
    
    function minutesToTime($minutes)
    {
        $hours = floor($minutes / 60);
        $minutes  = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }
    
}
