<?php

namespace GoalSetting\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use GoalSetting\Service\ServiceInterface\EikenScheduleInquiryServiceInterface;

/**
 * EikenScheduleInquiryController
 *
 * @author
 * 
 * @version
 *
 */
class EikenScheduleInquiryController extends AbstractActionController {

    protected $eikenScheduleService;

    public function __construct(EikenScheduleInquiryServiceInterface $eikenScheduleService) {
        $this->eikenScheduleService = $eikenScheduleService;
    }

    /**
     * The default action - show the home page
     */
    public function indexAction() {
        return new ViewModel();
    }

    public function getEikenSchedulesAction() {
        $year = $this->params()->fromQuery('year');
        $yearFrom = $year - 1;
        $yearTo = $year + 1;
        $eikenSchedules = $this->eikenScheduleService->getEikenSchedulesByYear($yearFrom, $yearTo, '英検');
        $scheduleArrays = array();
        foreach ($eikenSchedules as $eikenSchedule) {
            $event = ['friDate' => $eikenSchedule['friDate'] && $this->getOptionDisplayExamDateRoundOne() == 1 ? $eikenSchedule['friDate']->format('Y-m-d') : '',
                'satDate' => $eikenSchedule['satDate'] ? $eikenSchedule['satDate']->format('Y-m-d') : '',
                'sunDate' => $eikenSchedule['sunDate'] ? $eikenSchedule['sunDate']->format('Y-m-d') : '',
                'round2Day1ExamDate' => $eikenSchedule['round2Day1ExamDate'] ? $eikenSchedule['round2Day1ExamDate']->format('Y-m-d') : '',
                'round2Day2ExamDate' => $eikenSchedule['round2Day2ExamDate'] ? $eikenSchedule['round2Day2ExamDate']->format('Y-m-d') : '',
                'day1stTestResult' => $eikenSchedule['day1stTestResult'] ? $eikenSchedule['day1stTestResult']->format('Y-m-d') : '',
                'day2ndTestResult' => $eikenSchedule['day2ndTestResult'] ? $eikenSchedule['day2ndTestResult']->format('Y-m-d') : '',
                'year' => $eikenSchedule['year'] ? $eikenSchedule['year'] : '',
                'kai' => $eikenSchedule['kai'] ? $eikenSchedule['kai'] : '',
                'title' => '',
                'start' => $eikenSchedule['deadlineFrom'] ? $eikenSchedule['deadlineFrom']->format('Y-m-d H:i:s') : '',
                'end' => $eikenSchedule['deadlineTo'] ? $eikenSchedule['deadlineTo']->format('Y-m-d H:i:s') : '',
                'strStart' => $eikenSchedule['deadlineFrom'] ? $eikenSchedule['deadlineFrom']->format('Y-m-d H:i:s') : '',
                'strEnd' => $eikenSchedule['deadlineTo'] ? $eikenSchedule['deadlineTo']->format('Y-m-d H:i:s') : '',
                'allDay' => true];
            array_push($scheduleArrays, $event);
        }
        $jsonData = Json::encode($scheduleArrays);
        return $this->getResponse()->setContent($jsonData);
    }

    public function getEikenScheduleHolidaysAction() {
        $year = $this->params()->fromQuery('year');
        $dateFrom = $year . '-04-01 00:00:00';
        $dateTo = $year + 1 . '-03-31 23:59:59';
        $holidays = $this->eikenScheduleService->getHolidaysByDate($dateFrom, $dateTo);
        $jsonData = Json::encode($holidays);
        return $this->getResponse()->setContent($jsonData);
    }

    public function getOptionDisplayExamDateRoundOne() {
        $dantaiService = new \Application\Service\DantaiService();
        $currentUser = $dantaiService->getCurrentUser();
        if ($currentUser['organizationNo']) {
            $orgNumber = $currentUser['organizationNo'];
            $organization = $this->eikenScheduleService->getOrganizationByNo($orgNumber);
            $orgGroupOne = $this->getServiceLocator()->get('Config')['goalsetting_config']['organization_group_1'];
            $orgGroupTwo = $this->getServiceLocator()->get('Config')['goalsetting_config']['organization_group_2'];
            if ($organization[0] && $organization[0]['organizationCode']) {
                if (in_array($organization[0]['organizationCode'], $orgGroupOne)) {
                    return 1;
                }
                if (in_array($organization[0]['organizationCode'], $orgGroupTwo)) {
                    return 2;
                }
            }
            return 0;
        }
    }

}
