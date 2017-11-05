<?php
namespace HomePage\Service;

use HomePage\Service\ServiceInterface\HomeServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Json\Json;
use Composer\Autoload\ClassLoader;
use Dantai\PrivateSession;
use Zend\View\Model\ViewModel;

class HomeService implements HomeServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    protected $colorGraphPageC;

    protected $pointWidthGraphPageC;

    protected $pointPaddingGraphPageC;

    protected $pointPlacementGraphPageC;

    protected $limit;

    protected $serviceManager;
    
    protected $organizationId = 0;
    protected $organizationNo;
    protected $dantaiService;

    public function __construct(\Doctrine\ORM\EntityManager $em, \Zend\ServiceManager\ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $this->entityManager = $em;
        $this->colorGraphPageC = array(
            "#a5d98e",
            "#5db932",
            "#308d00"
        );
        $this->pointWidthGraphPageC = "20";
        $this->pointPaddingGraphPageC = "0";
        $this->pointPlacementGraphPageC = array(
            "0.05",
            "0",
            "-0.05"
        );
        $this->limit = 20;
        $user = PrivateSession::getData('userIdentity');
        $this->organizationId = $user['organizationId'];
        $this->organizationNo = $user['organizationNo'];
        $this->dantaiService  = $this->serviceManager->get('Application\Service\DantaiServiceInterface');        
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Get number from string
     *
     * @author taivh
     */
    public function getNumerics($str)
    {
        preg_match_all('/\d+/', $str, $matches);

        return $matches[0];
    }

    /**
     *
     * @author TaiVH
     * @param number $numStudentPass
     * @param number $totalStudent
     * @param number $target
     * @return number
     */
    public function passRate($numStudentPass, $totalStudent, $target)
    {
        if ($totalStudent == 0)
            return 0;
        if ($target == 0)
            return 0;
        $rate = ($numStudentPass / $totalStudent) * 100;

        return (round($rate, 0) / $target) * 100;
    }

    /**
     * Get Value color css
     *
     * @author TaiVH
     */
    public function getValueBigWithColor($compa, $k = 0, $text, $checkClass)
    {
        $compa = round($compa);
        $c = '';
        if ($compa < 0) {
            $c = ' red';
        }
        if ($compa == 0) {
            $c = ' gray';
            $compa = '±0';
        }
        if ($compa > 0) {
            $compa = $compa;
        }

        return '<span class=\'box-l ' . $checkClass . $c . '\'>' . $k . '<span> ' . $text . '</span></span>';
    }

    /**
     * Get Value color css
     *
     * @author TaiVH
     */
    public function getValueWithColor($k = 0, $text)
    {
        $k = round($k);
        $c = '';
        if ($k < 0) {
            $c = ' red';
        }
        if ($k == 0) {
            $c = ' gray';
            $k = '±0';
        }
        if ($k > 0) {
            $k = '+' . $k;
        }

        return '<p class=\'p2' . $c . '\'>' . $k . '<span> ' . $text . '</span></p>';
    }

    /**
     * Get Value color css
     *
     * @author TaiVH
     */
    public function getValueWithColorHomeDetailA($k = 0, $text)
    {
        $k = round($k);
        $c = '';
        if ($k < 0) {
            $c = ' red-1';
        }
        if ($k == 0) {
            $c = ' gray-1';
            $k = '±' . $k;
        }
        if ($k > 0) {
            $k = '+' . $k;
        }

        return '<span class="box-l-2' . $c . '">' . $k . '<span>' . $text . '</span></span>';
    }

    /**
     *
     * @author taivh
     *         Get sum student registered
     * @param unknown $orgId
     * @param unknown $eikenScheduleId
     * @param unknown $curentDate
     * @param string $isSUBITTED
     * @return multitype:NULL number
     */
    public function sumReExam($orgId = 0, $eikenScheduleId = 0, $curentDate = null, $isSUBITTED = 'SUBMITTED')
    {
        $res = $this->entityManager->getRepository('Application\Entity\ApplyEikenOrg')->getTotalApplyEikenOrg($orgId, $eikenScheduleId, $isSUBITTED);
        if ($res) {
            return array(
                $res[0]['total'],
                1,
                $res[0]['updateAt']->format('n/j')
            );
        }
        else {
            $res = $this->entityManager->getRepository('Application\Entity\ApplyEikenLevel')->getTotalApplyEikenByOrgId($orgId, $eikenScheduleId, $curentDate);
            if ($res)
                return array(
                    $res[0]['total'],
                    0
                );
        }
    }

    public function getCurrentYear()
    {
        $month = date('m');
        if ($month < 4)
            return (int)date('Y') - 1;

        return (int)date('Y');
    }

    /**
     * Get Data Eiken Schedule by current year
     *
     * @author TaiVH
     */
    public function getDataEikenScheduleByCurrentDate($year)
    {
        return $this->entityManager->getRepository('Application\Entity\EikenSchedule')->getDataEikenScheduleByCurrentDate($year);
    }

    /**
     * get Data for Notication HomePage
     */
    public function getEikenScheduleByCurrentTime()
    {
        return $this->entityManager->getRepository('Application\Entity\EikenSchedule')->getEikenScheduleByCurrentTime();
    }

    public function getCurrentEikenSchedule()
    {
        return $this->entityManager->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();
    }
    
    /*
     * DucNA17
     * get data graph
     * @return array
     */
    public function getDataDetailC($orgNo, $year)
    {
        $result = $item = array();
        $result = $this->entityManager->getRepository('Application\Entity\EikenTestResult')->getCountPeopleByYearAndTime($orgNo, $year);
        $max = 0;
        foreach ($result as $key => $val) {
            $item[$val['year']][$val['kai']] = $val;
            if ($max < $val[1]) {
                $max = $val[1];
            }
        }
        $max = ceil($max / 10) * 10;
        $dataConvert = $this->formatDataByYear($item, $year);
        $dataConvert = $this->formatDataOfDetailC($dataConvert);
        $dataConvert = array_values($dataConvert);
        $dataConvert = $this->formatDataByColGraph($dataConvert);
        $return['data'] = $dataConvert;
        $return['max'] = $max;
        
        return $return;
    }

    /*
     * DucNA17
     * get data detail by Class (orgClassificationId and orgSchoolYearId)
     * @return array
     */
    public function getDetailByYearAndTime($orgNo, $year, $kai)
    {
        return $this->entityManager->getRepository('Application\Entity\EikenTestResult')->getCountDetailCodeByYearAndTime($orgNo, $year, $kai);
    }

    /*
     * DucNA17
     * get data Graph
     * @return array
     */
    public function getDataGraphBSv($orgId, $orgNo, $year, $orgCode = null)
    {
        $returnPass = $this->entityManager->getRepository('Application\Entity\EikenTestResult')->getDataGraphB($orgNo, $year, 'pass');
        $return = $returnList = array();
        $listEikenLv = $this->entityManager->getRepository('Application\Entity\EikenLevel')->ListEikenLevel();
        $arrEikenLv = array();
        foreach ($listEikenLv as $item) {
            $arrEikenLv[$item["id"]] = $item['levelName'];
        }
        foreach ($returnPass as $key => $itemPass) {
            $return[$itemPass['year']][$itemPass['id']] = $itemPass;
        }
        $cityId = $this->entityManager->getRepository('Application\Entity\Organization')->getCityIdByOrgId($orgId);
        $return = $this->formatDataByYear($return, $year);

        foreach ($return as $key => $item) {
            $total = 0;
            //TODO re-confirm with TaiVH
            $goalPasss = $this->entityManager->getRepository('Application\Entity\GoalPass')->getCountPupilOfClass($key, $orgId);
            $totalAll = empty($goalPasss[1]) ? 1 : $goalPasss[1];
            $cityRate = $this->entityManager->getRepository('Application\Entity\GoalPass')->getRateByCityId($cityId, $key, $orgCode);
            $nationRate = $this->entityManager->getRepository('Application\Entity\GoalPass')->getRateNation($key, $orgCode);
            for ($i = 7; $i > 0; $i--) {
                if (empty($item[$i])) {
                    $return[$key][$i]['year'] = $key;
                    $return[$key][$i]['totalPassed'] = 0;
                    $return[$key][$i]['id'] = $i;
                    $return[$key][$i]['levelName'] = $arrEikenLv[$i];
                }
                $return[$key][$i]['totalAll'] = (!empty($totalAll)) ? $totalAll : 0;
                $return[$key][$i]['orgRate'] = ($totalAll !== null and $return[$key][$i]['totalPassed'] !== 0) ? number_format(($return[$key][$i]['totalPassed'] / $totalAll) * 100) : 0;
                $return[$key][$i]['cityRate'] = (!empty($cityRate[$i]['ratePass'])) ? number_format($cityRate[$i]['ratePass']) : 0;
                $return[$key][$i]['nationRate'] = (!empty($nationRate[$i]['ratePass'])) ? number_format($nationRate[$i]['ratePass']) : 0;
                $total += $return[$key][$i]['orgRate'] + $return[$key][$i]['cityRate'] + $return[$key][$i]['nationRate'];
                $return[$key][$i]['checkEmptyChart'] = $total;
            }
            krsort($return[$key]);
        }
        $i = 0;
        foreach ($return as $key => $item) {
            $returnList['Y' . $i] = $item;
            $i++;
        }

        return $returnList;
    }

    /*
     * DucNA17
     *
     * @return array
     */
    public function getDetailClassBSv($orgNo, $year, $kai)
    {
        $returnConvert = $this->entityManager->getRepository('Application\Entity\EikenTestResult')->getDetailClassB($orgNo, $year, $kai, 1);
        $returnData = array();
        $sumTotalReal = 0;
        if (!empty($returnConvert[0]['year'])) {
            foreach ($returnConvert as $key => $item) {
                $sumTotalReal += $item['countElement'];
            }
        }
        $sumPer = 0;
        foreach ($returnConvert as $key => $item) {
            $returnConvert[$key]['ratio'] = round($item['countElement'] * 100 / $sumTotalReal);
            $returnConvert[$key]['ratio2'] = $item['countElement'] * 100 / $sumTotalReal;
            $sumPer += $returnConvert[$key]['ratio'];
            $returnConvert[$key]['residual'] = $returnConvert[$key]['ratio'] - $item['countElement'] * 100 / $sumTotalReal;
            $returnData['data'] = $returnConvert;
            $returnData['sumTotal'] = $returnData['sumTotalReal'] = $sumTotalReal;
        }

        return $returnData;
    }

    /*
     * DucNA17
     *
     * @return array
     */
    public function getTableByClassBSv($dataClass = null, $orgNo, $year, $time, $page, $schoolYearCode = null, $schoolClassification = null, $schoolYearCode = null, $notMap = null)
    {
        if (empty($dataClass[0]['year'])) {
        }
        else {
            foreach ($dataClass as $key => $item) {
                $schoolClassification = $dataClass[$key]["schoolClassification"];
                $schoolYearCode = $dataClass[$key]["schoolYearCode"];
                if (isset($dataClass[$key]["notMap"]))
                    $notMap = $dataClass[$key]["notMap"];
                $returnData['sum'] = $dataClass[$key]["countElement"];
                $returnData['keyActive'] = $key;
                break;
            }
        }
        $offset = ($page - 1) * $this->limit;
        $paginator = $this->entityManager->getRepository('Application\Entity\EikenTestResult')->getDataDetailTableC($orgNo, $year, $time, $schoolYearCode, $schoolClassification, 'B');
        $return = $paginator->getItems($offset, $this->limit);
        $returnData['data'] = $return;
        $returnData['paginator'] = $paginator;

        return $returnData;
    }

    /*
     * DucNA17
     * get total student of organization by year
     * @return array
     */
    public function getTotalStudentByYearSv($year, $orgId)
    {
        return $this->entityManager->getRepository('Application\Entity\ClassJ')->getTotalStudentByYear($year, $orgId);
    }

    /*
     * DucNA17
     */
    public function getDDLYear($year)
    {
        $list = array();
        if (intval($year) > 2010) {
            for ($i = $year + 2; $i >= 2010; $i--) {
                $list[] = $i;
            }
        }

        return $list;
    }

    /*
     * TAIVH
     */
    public function getInfoBox3($orgId)
    {
        $date1 = date('Y-m-d', strtotime('-1 days'));
        $date2 = date('Y-m-d', strtotime('-2 days'));
        $t1 = $this->entityManager->getRepository('Application\Entity\InquiryStudyGear')->getStudyGearHistory($orgId, $date1);
        $t2 = $this->entityManager->getRepository('Application\Entity\InquiryStudyGear')->getStudyGearHistory($orgId, $date2);
        $a = 0;
        $b = 0;
        if (count($t1) > 0)
            $a = (int)$t1[0]['total'];
        if (count($t2) > 0)
            $b = (int)$t2[0]['total'];
        $t = $a - $b;

        return array(
            $a,
            $t
        );
    }

    /*
     * DucNA17
     * get CityName by CityID
     */
    public function getCityNameByOrgNo($orgNo)
    {
        if (empty($orgNo)) {
            return;
        }
        $org = $this->entityManager->getRepository('Application\Entity\Organization')->findOneBy(array(
            'organizationNo' => $orgNo
        ));
        if (empty($org))
            return;
        if ($org->getCityId() != null) {
            $city = $this->entityManager->getRepository('Application\Entity\City')->find($org->getCityId());
        }
        if (empty($city))
            return;

        return $city->getCityName();
    }

    /*
     * DucNA17
     * get value maxRate, maxTotal for homepage detail b
     */
    public function getMaxRateAndTotal($data)
    {
        $maxTotal = $maxRate = 0;
        if (!empty($data)) {
            foreach ($data as $dataYear) {
                foreach ($dataYear as $item) {
                    if (!empty($item['totalPassed'])) {
                        if ($maxTotal < $item['totalPassed']) {
                            $maxTotal = $item['totalPassed'];
                        }
                    }
                    if (!empty($item['orgRate'])) {
                        if ($maxRate < $item['orgRate']) {
                            $maxRate = $item['orgRate'];
                        }
                    }
                    if (!empty($item['cityRate'])) {
                        if ($maxRate < $item['cityRate']) {
                            $maxRate = $item['cityRate'];
                        }
                    }
                    if (!empty($item['nationRate'])) {
                        if ($maxRate < $item['nationRate']) {
                            $maxRate = $item['nationRate'];
                        }
                    }
                }
            }
            $maxRate = ceil($maxRate / 10) * 10;
            $maxTotal = ceil($maxTotal / 10) * 10;
        }

        return array(
            'maxTotal' => $maxTotal,
            'maxRate'  => $maxRate
        );
    }

    public function getNews()
    {
        $return = null;
        $return = $this->entityManager->getRepository('Application\Entity\NewsEiken')->getListNews();

        return $return;
    }

    /**
     * DucNa17
     *
     * @param
     *            $dataGraph
     * @param
     *            current year $year
     * @return \HomePage\Service\array:
     */
    public function formatDataByYear($dataGraph, $year)
    {
        $dataConvert = array();
        for ($i = $year - 2; $i <= $year; $i++) {
            $dataConvert[$i] = empty($dataGraph[$i]) ? null : $dataGraph[$i];
        }

        return $dataConvert;
    }

    /**
     * DucNA17
     *
     * @param
     *            $dataGraph
     * @return array:
     */
    public function formatDataOfDetailC($dataGraph)
    {
        foreach ($dataGraph as $key => $val) {
            for ($i = 1; $i < 4; $i++) {
                $arr[$key][$i]['year'] = $key;
                $arr[$key][$i]['time'] = $i;
                $arr[$key][$i]['1'] = !empty($val[$i]['1']) ? $val[$i]['1'] : 0;
            }
            $arr[$key] = array_values($arr[$key]);
        }

        return $arr;
    }

    /**
     * DucNA17
     *
     * @param
     *            $dataGraph
     * @return array
     */
    public function formatDataByColGraph($dataGraph)
    {
        $dataFormat = array();
        foreach ($dataGraph as $key => $item) {
            $dataFormat[$key]['name'] = $item[0]['year'];
            $dataFormat[$key]['color'] = $this->colorGraphPageC[$key];
            $dataFormat[$key]['pointWidth'] = floatval($this->pointWidthGraphPageC);
            $dataFormat[$key]['pointPadding'] = floatval($this->pointPaddingGraphPageC);
            $dataFormat[$key]['pointPlacement'] = ($this->pointPlacementGraphPageC[$key] !== null) ? floatval($this->pointPlacementGraphPageC[$key]) : 0;
            foreach ($item as $data) {
                $dataFormat[$key]['data'][] = floatval($data[1]);
            }
        }

        return $dataFormat;
    }

    /**
     *
     * @author taivh
     */
    public function getDataTimeline()
    {
        $currYear = date('Y');
        $data = $this->getDataEikenScheduleByCurrentDate($currYear);
        $jsonString = array();
        if ($data) {
            $i = 0;
            foreach ($data as $datas) {
               $isDantaiA = $this->dantaiService->isDantaiA($datas['id']);
                foreach ($datas as $k => $item) {
                    if ($k == 'deadlineFrom' && $item) {
                        $jsonString[$i]['start'] = $item->format('Y-m-d');
                        $st0 = $item->format('n/j');
                        $startTT = $item->format('Y/n/j');
                    }
                    if ($k == 'deadlineTo') {
                        $jsonString[$i]['end'] = $item->format('Y-m-d');
                        $en0 = $item->format('n/j');
                        $endTT = $item->format('Y/n/j');
                    }
                    if ($k == 'examFullName') {
                        $jsonString[$i]['content'] = '<i id="ttip" style="left:0;" class="timeline-dot tooltip-home1" alt="アプリケーションの開始 : '.$startTT.'"></i><i id="ttip" class="timeline-dot tooltip-home1" alt="アプリケーションの終了 : '. $endTT.'"></i><span>' . $item . '(' . $st0 . '~' . $en0 . ')</span>';
                    }
                    if ($k == 'friDate') {
                        $jsonString[$i + 1]['start'] = $item ? $item->format('Y-m-d') : '';
                        $st2 = $item ? $item->format('n/j') : '';
                        $st2DateContent = $item ? $item->format('Y/n/j') : '';
                    }
                    if ($k == 'friDate') {
                        $jsonString[$i + 1]['content'] = '<i id="ttip" class="timeline-dot tooltip-home1" alt="一次試験 : '. $st2DateContent .'(金)"></i><span>(' . $st2 . ')';
                    }
                    if ($k == 'satDate') {
                        $jsonString[$i + 2]['start'] =  $item ? $item->format('Y-m-d') : "";
                        $st3 = $item ? $item->format('n/j') : '';
                        $st3DateContent = $item ? $item->format('Y/n/j') : '';
                    }
                    if ($k == 'satDateName') {
                        $jsonString[$i + 2]['content'] = '<i id="ttip" class="timeline-dot tooltip-home1" alt="一次試験 : '. $st3DateContent .'(土)"></i><span>(' . $st3 . ')';
                    }
                    if ($k == 'sunDate') {
                        $jsonString[$i + 3]['start'] = $item ? $item->format('Y-m-d') : "";
                        $st1 = $item ? $item->format('n/j') : "";
                        $st1DateContent = $item ? $item->format('Y/n/j') : '';
                    }
                    if ($k == 'sunDateName') {
                        $jsonString[$i + 3]['content'] = '<i id="ttip" class="timeline-dot tooltip-home1" alt="一次試験 : '. $st1DateContent .'(日)"></i><span>(' . $st1 . ')';
                    }
//                    if ($k == 'round2ExamDate') {
//                        $jsonString[$i + 4]['start'] = $item->format('Y-m-d');
//                        $st4 = $item->format('n/j');
//                    }
                    if ($k == 'round2Day1ExamDate') {
                        $jsonString[$i + 4]['start'] = $item->format('Y-m-d');
                        $round21 = $item->format('n/j');
                        $jsonString[$i + 4]['axisOnTop'] = false;
                    }
                    if ($k == 'round2Day1ExamDate') {
                        $jsonString[$i + 4]['content'] = '<i id="ttip" class="timeline-dot tooltip-home1" alt="二次試験実施日（A日程） : '. $item->format('Y/n/j') .' "></i><span>'  . '(' . $round21 . ')</span>';
                    }
                    if ($k == 'round2Day2ExamDate' && !$isDantaiA) {
                        $jsonString[$i + 5]['start'] = $item->format('Y-m-d');
                        $round22 = $item->format('n/j');
                    }
                    if ($k == 'round2Day2ExamDate' && !$isDantaiA) {
                        $jsonString[$i + 5]['content'] = '<i id="ttip" class="timeline-dot tooltip-home1" alt="二次試験実施日（B日程） : '. $item->format('Y/n/j') .' "></i><span>'  . '(' . $round22 . ')</span>';
                    }
                    if ($k == 'satDateName') {
                        $st4 = isset($st4) ? $st4 : '';
                        $jsonString[$i + 4]['content'] = '<i class="timeline-dot"></i><span>' . $item . '(' . $st4 . ')</span>';
                    }
                }
                $i += 6;
            }

            $arrEiken = array();
            $eikenData = $this->getDataFromApplyEikenByYear();
            if ($eikenData) {
                foreach ($eikenData as $key => $item) {
                    $insertAt = !empty($item['insertAt']) ? $item['insertAt']->format('Y/m/d') : '';
                    $insertBy = isset($item['executorName']) ? $item['executorName'] : (isset($item['firtNameKanji']) ? $item['firtNameKanji'] : '').(isset($item['lastNameKanji']) ? $item['lastNameKanji'] : '');
                    $status = !empty($item['status']) ? $item['status'] : '';
                    if ($status == 'DRAFT') {
                        $status = 'ドラフト';
                    }
                    elseif ($status == 'SUBMITTED') {
                        $status = '提出済み';
                    }
                    else {
                        $status = '';
                    }
                    if (!empty($item['insertAt'])) {
                        foreach ($item as $k => $value) {
                            if ($k == 'insertAt') {
                                $arrEiken[$i + 1]['start'] = $value->format('Y/m/d');
                                $arrEiken[$i + 1]['content'] = '<i id="ttip" class="timeline-dot star-orange tooltip-home1" alt="英検
                                登録日時:' . $insertAt . ' - 登録者:' . $insertBy . '
                                申込状況:' . $status . '"></i>';
                                $i++;
                            }
                        }
                    }
                }
            }

            foreach ($arrEiken as $value) {
                array_push($jsonString, $value);
            }
            $arrIBA = array();
            $ibaData = $this->getDataFromApplyIBAByYear();

            if ($ibaData) {
                foreach ($ibaData as $key => $item) {
                    $insertAt = !empty($item['insertAt']) ? $item['insertAt']->format('Y/m/d') : '';
                    $testDate = !empty($item['testDate']) ? $item['testDate']->format('Y/m/d') : '';
                    $insertBy = (isset($item['firtNameKanji']) ? $item['firtNameKanji'] : '').(isset($item['lastNameKanji']) ? $item['lastNameKanji'] : '');
                    $status = !empty($item['status']) ? $item['status'] : '';
                    if ($status == 'DRAFT') {
                        $status = 'ドラフト';
                    }
                    elseif ($status == 'PENDING') {
                        $status = '確定待ち';
                    }
                    elseif ($status == 'CONFIRMED') {
                        $status = '確定済み';
                    }
                    else {
                        $status = '';
                    }
                    if (!empty($item['insertAt'])) {
                        foreach ($item as $k => $value) {
                            if ($k == 'testDate') {
                                $arrIBA[$i + 1]['start'] = $value->format('Y/m/d');
                                $arrIBA[$i + 1]['content'] = '<i id="ttip" class="timeline-dot green tooltip-home2" alt="英検IBA:' . $testDate . '
                                        登録日時:' . $insertAt . ' - 登録者:' . $insertBy . '
                                        申込状況:' . $status . '"></i>';
                                $i++;
                            }
                        }
                    }
                }
            }
            foreach ($arrIBA as $value) {
                array_push($jsonString, $value);
            }
            $jsonString[$i + 1]['start'] = $currYear . "-01-01";
            $jsonString[$i + 1]['content'] = '';
            $jsonString[$i + 2]['start'] = ($currYear + 1) . "-01-01";
            $jsonString[$i + 2]['content'] = '';
        }
        else {
            $jsonString[0]['start'] = $currYear . "-01-01";
            $jsonString[0]['content'] = '';
            $jsonString[1]['start'] = ($currYear + 1) . "-01-01";
            $jsonString[1]['content'] = '';
        }
        $jsonString = json::encode($jsonString);

        return $jsonString;
    }
    
    public function getDataFromApplyEikenByYear()
    {
        $em = $this->entityManager;
        $data = $em->getRepository('Application\Entity\ApplyEikenOrg')->getDataFromApplyEikenByYear(date('Y'), $this->organizationId);

        return $data;
    }
    
    public function getDataFromApplyIBAByYear()
    {
        $em = $this->entityManager;
        $data = $em->getRepository('Application\Entity\ApplyIBAOrg')->getDataFromApplyIBAByYear(date('Y'), $this->organizationId);

        return $data;
    }

    /**
     * ChungDV
     *
     * @param int $orgId
     * @param int $year
     * @param string $orgNo
     */
    public function systemNotification($orgId, $year, $orgNo)
    {
        $em = $this->entityManager;
        $currentDate = date(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
        $currentKai = null;
        $currentYear = null;
        $current2ndExamDate = '';
        $currentDeadlineTo = '';
        $currentDay2ndTestResult = '';
        $currentStatus = '';
        $currentDay1stTestResult = '';
        $nextExamDeadlineFrom = '';
        $dayDeadlineTo = '';
        $monthDeadlineTo = '';
        $getCurrentKaiByYear = $em->getRepository('Application\Entity\EikenSchedule')->getCurrentKaiByYear($year);
        foreach ($getCurrentKaiByYear as $key => $value) {
            if (!empty($value['deadlineFrom']) && $value['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) <= $currentDate) {
                $currentEikenScheduleId = $value['id'];
                $currentYear = $value['year'];
                $currentKai = $value['kai'];
                $dayDeadlineTo = $value['deadlineTo'] ? $value['deadlineTo']->format('j') : '';
                $monthDeadlineTo = $value['deadlineTo'] ? $value['deadlineTo']->format('n') : '';
                $currentDeadlineTo = $value['deadlineTo'] ? $value['deadlineTo']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) : '';
                $current2ndExamDate = $value['round2ExamDate'] ? $value['round2ExamDate']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) : '';
                $currentDay1stTestResult = $value['day1stTestResult'] ? $value['day1stTestResult']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) : '';
                $currentDay2ndTestResult = $value['day2ndTestResult'] ? $value['day2ndTestResult']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) : '';
                break;
            }
            $nextExamDeadlineFrom = !empty($value['deadlineFrom']) ? $value['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) : '';
        }
        if ($currentEikenScheduleId) {
            $infoStudentCurrentYear = $em->getRepository('Application\Entity\ApplyEikenOrg')->getApplyEikenOrgByEikenScheduleId($orgId, $currentEikenScheduleId);
            if ($infoStudentCurrentYear) {
                foreach ($infoStudentCurrentYear as $key => $value) {
                    $currentStatus = $value['status'];
                }
            }
        }
        // Get apply eiken type (1: group, 0: personal)
        $paymentType = null;
        if ($currentKai != null) {
            $invSetting = $em->getRepository('Application\Entity\InvitationSetting')->getInvitationSetting($orgId, $currentEikenScheduleId);
            if ($invSetting) {
                $paymentType = $invSetting['paymentType'];
            }
        }
        $countgGraduationGoal = $em->getRepository('Application\Entity\OrgGraduationGoal')->getCountGraduationGoal($orgId, $currentYear, 1);
        $countGraduationGoalOfYears = $em->getRepository('Application\Entity\OrgGraduationGoal')->getCountGraduationGoal($orgId, $currentYear, 0);
        $daySystemStart = $em->getRepository('Application\Entity\Organization')->findOneBy(array(
            'id'       => $orgId,
            'isDelete' => 0
        ));
        $countPupil = $em->getRepository('Application\Entity\Pupil')->getCountPupil($orgId, $currentYear);
        $dateForm = $year . '-01-01';
        $dateTo = $year . '-12-31';
        $status = 'CONFIRMED';
        $infoApplyIBAOrg = $em->getRepository('Application\Entity\ApplyIBAOrg')->infoApplyIBAOrg($orgId, $dateForm, $dateTo, $status);
        if ($currentKai != null) {
            $eikenTestResult = $em->getRepository('Application\Entity\EikenTestResult')->findOneBy(array(
                'year'           => $currentYear,
                'organizationNo' => $orgNo,
                'kai'            => $currentKai
            ));
        }
        $beforeTenDaysDeadlineApply = $currentDeadlineTo ? date(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT, strtotime($currentDeadlineTo . '-10 days')) : '';
        $afterOneMonthSystemStart = !empty($daySystemStart) ? date(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT, strtotime($daySystemStart->getInsertAt()->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) . ' +1 month')) : '';
        $msg = array();
        // month 4 or in 1 month system operation start
        if (date('m') == 4 || ($currentDate <= $afterOneMonthSystemStart && $currentDate >= $daySystemStart->getInsertAt()->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT))) {
            // value graduation goal has not been set
            // or value graduation goal of years has not been set
            // and list students of current year not register
            if (($countgGraduationGoal == 0 || $countGraduationGoalOfYears == 0) && $countPupil == 0) {
                $msg01 = array(
                    'msg'      => $this->translate('HomePageNo1'),
                    'priority' => 40,
                    'btn'      => 1
                );
                array_push($msg, $msg01);
            }
            // value graduation goal has not been set
            // or value graduation goal of years has not been set
            // and list students of current year register
            if (($countgGraduationGoal == 0 || $countGraduationGoalOfYears == 0) && $countPupil > 0) {
                $msg02 = array(
                    'msg'      => $this->translate('HomePageNo2'),
                    'priority' => 40,
                    'btn'      => 2
                );
                array_push($msg, $msg02);
            }
            // value graduation goal has set
            // or value graduation goal of years has set
            // and list students of current year not register
            if ($countgGraduationGoal > 0 && $countGraduationGoalOfYears > 0 && $countPupil == 0) {
                $msg03 = array(
                    'msg'      => $this->translate('HomePageNo3'),
                    'priority' => 40,
                    'btn'      => 3
                );
                array_push($msg, $msg03);
            }
        }
        if ($infoStudentCurrentYear) {
            // after day result exam first time -> day exam second time
            if ($currentKai != null && $currentDay1stTestResult && $currentDate > $currentDay1stTestResult && $currentDate <= $current2ndExamDate) {
                // implement apply eiken group
                if ($currentStatus != '' && $currentStatus == 'SUBMITTED' && $paymentType == 1) {
                    // not import data result exam first time
                    if ($eikenTestResult && !$eikenTestResult->getTotalPrimaryScore()) {
                        $msg10 = array(
                            'msg'      => sprintf($this->translate('HomePageNo10'), $currentKai),
                            'priority' => 30,
                            'btn'      => 4
                        );
                        array_push($msg, $msg10);
                    }
                }
            }
        }
        if ($infoStudentCurrentYear) {
            // after day exam second time -> start day exam next time
            if ($currentKai != null && $currentDay2ndTestResult && $currentDate > $currentDay2ndTestResult && $currentDate <= $nextExamDeadlineFrom) {
                // implement apply eiken group
                if ($currentStatus != '' && $currentStatus == 'SUBMITTED' && $paymentType == 1) {
                    // not import data result exam second time
                    if ($eikenTestResult && !$eikenTestResult->getTotalSecondScore()) {
                        $msg13 = array(
                            'msg'      => sprintf($this->translate('HomePageNo13'), $currentKai),
                            'priority' => 30,
                            'btn'      => 4
                        );
                        array_push($msg, $msg13);
                    }
                }
            }
        }
        // before 10 day deadline apply registration exam -> day dealine apply registration exam
//        if ($currentDeadlineTo && $currentDate <= $currentDeadlineTo && $currentDate >= $beforeTenDaysDeadlineApply) {
//            $dStart = new \DateTime(date('Y-m-d'));
//            $dEnd = new \DateTime($currentDeadlineTo);
//            $dEnd = new \DateTime(date_format($dEnd, 'Y-m-d'));
//            $dDiff = $dStart->diff($dEnd);
//            $msg06 = array(
//                'msg'      => sprintf($this->translate('HomePageNo6'), $monthDeadlineTo, $dayDeadlineTo, $dDiff->days),
//                'priority' => 10,
//                'btn'      => 0
//            );
//            array_push($msg, $msg06);
//        }

        return $msg;
    }

    /**
     * AnNV6
     *
     * @param int $orgId
     * @param int $year
     * @param string $orgNo
     */
    public function applyNotification($orgId, $year, $orgNo, $fiscalYear)
    {
        $applyEikenStatus = '';
        $em = $this->getEntityManager();
        //$infoStudentCurrentYear = $em->getRepository('Application\Entity\EikenSchedule')->getInfoStudentCurrentYear($orgId, $year);
        $infoStudentCurrentYear = '';
        $currentDate = date(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
        $friExamDate = '';
        $satExamDate = '';
        $sunExamDate = '';
        $currentKai = null;
        $currentYear = null;
        $current2ndExamDate = '';
        $currentDeadlineFrom = '';
        $currentDeadlineTo = '';
        $current1stTestResult = '';
        $current2ndTestResult = '';
        $nextDeadlineFrom = '';
        $applyStatus = '';
        $importStatus = '';
        $applyEikenOrgUpdateAt = '';
        $currentRegistrationDate = '';
        $isDeleteApply = '';
        $applyEikenStatus = '';
        //         if ($infoStudentCurrentYear) {
        //             foreach ($infoStudentCurrentYear as $key => $value) {
        //                 if (! empty($value['deadlineFrom']) && $value['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) <= $currentDate) {
        //                     $applyEikenScheduleId = $value['id'];
        //                     $applyYear = ($value['year'])?$value['year']:'';
        //                     $applyKai = ($value['kai'])?$value['kai']:'';
        //                     $applyDeadlineFrom = ($value['deadlineFrom'])?$value['deadlineFrom']:'';
        //                     $applyDeadlineTo = ($value['deadlineTo'])?$value['deadlineTo']:'';
        //                     $apply2ndExamDate = ($value['round2ExamDate'])?$value['round2ExamDate']:'';
        //                     $apply1stTestResult = ($value['day1stTestResult'])?$value['day1stTestResult']:'';
        //                     $apply2ndTestResult = ($value['day2ndTestResult'])?$value['day2ndTestResult']:'';
        //                     $applyFriExamDate = ($value['friDate'])?$value['friDate']:'';
        //                     $applySatExamDate = ($value['satDate'])?$value['satDate']:'';
        //                     $applySunExamDate = ($value['sunDate'])?$value['sunDate']:'';
        //                     $applyRegistrationDate = ($value['registrationDate'])?$value['registrationDate']:'';
        //                     $applyStatus = ($value['status'])?$value['status']:'';
        //                     $importStatus = ($value['statusImporting'])?:'';
        //                     $applyEikenOrgUpdateAt = ($value['applyEikenOrgUpdateAt'])?$value['applyEikenOrgUpdateAt']:'';
        //                     $isDeleteApply = ($value['isDeleteApply'])?$value['isDeleteApply']:'';
        //                     break;
        //                 }
        //             }
        //         }
        $getCurrentKaiByYear = $em->getRepository('Application\Entity\EikenSchedule')->getCurrentKaiByYear($year);
        foreach ($getCurrentKaiByYear as $key => $value) {
            if (!empty($value['deadlineFrom']) && $value['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) <= $currentDate) {
                $currentEikenScheduleId = $value['id'];
                $currentYear = $value['year'];
                $currentKai = $value['kai'];
                $currentDeadlineFrom = $value['deadlineFrom'];
                $currentDeadlineTo = $value['deadlineTo'];
                $current2ndExamDate = $value['round2ExamDate'];
                $current1stTestResult = $value['day1stTestResult'];
                $current2ndTestResult = $value['day2ndTestResult'];
                $friExamDate = $value['friDate'];
                $satExamDate = $value['satDate'];
                $sunExamDate = $value['sunDate'];
                break;
            }
        }
        if ($currentEikenScheduleId) {
            $infoStudentCurrentYear = $em->getRepository('Application\Entity\ApplyEikenOrg')->getApplyEikenOrgByEikenScheduleId($orgId, $currentEikenScheduleId);
            if ($infoStudentCurrentYear) {
                foreach ($infoStudentCurrentYear as $key => $value) {
                    $currentRegistrationDate = ($value['registrationDate']) ? $value['registrationDate'] : '';
                    $applyStatus = ($value['status']) ? $value['status'] : '';
                    $importStatus = ($value['statusImporting']) ? $value['statusImporting'] : '';
                    $applyEikenOrgUpdateAt = ($value['applyEikenOrgUpdateAt']) ? $value['applyEikenOrgUpdateAt'] : '';
                    $isDeleteApply = ($value['isDeleteApply']) ? $value['isDeleteApply'] : '';
                }
            }
        }
        $applyEikenOrgUpdateAt = !empty($applyEikenOrgUpdateAt) ? $applyEikenOrgUpdateAt->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        $dayCurrentDeadlineFrom = !empty($currentDeadlineFrom) ? $currentDeadlineFrom->format('j') : '';
        $monthCurrentDeadlineFrom = !empty($currentDeadlineFrom) ? $currentDeadlineFrom->format('n') : '';
        $currentDeadlineFrom = !empty($currentDeadlineFrom) ? $currentDeadlineFrom->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        $currentDeadlineTo = !empty($currentDeadlineTo) ? $currentDeadlineTo->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        $examDays = array(
            $friExamDate,
            $satExamDate,
            $sunExamDate
        );
        arsort($examDays);
        $dayFirstExamDate = !empty($examDays[0]) ? $examDays[0]->format('j') : '';
        $monthFirstExamDate = !empty($examDays[0]) ? $examDays[0]->format('n') : '';
        $firstExamDate = !empty($examDays[0]) ? $examDays[0]->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        $dayCurrent2ndExamDate = !empty($current2ndExamDate) ? $current2ndExamDate->format('j') : '';
        $monthCurrent2ndExamDate = !empty($current2ndExamDate) ? $current2ndExamDate->format('n') : '';
        $beforeOneDayCurrent2ndExamDate = !empty($current2ndExamDate) ? date(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT, strtotime($current2ndExamDate->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) . '-1 days')) : '';
        $current2ndExamDate = !empty($current2ndExamDate) ? $current2ndExamDate->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        $dayCurrent1stTestResult = !empty($current1stTestResult) ? $current1stTestResult->format('j') : '';
        $monthCurrent1stTestResult = !empty($current1stTestResult) ? $current1stTestResult->format('n') : '';
        $current1stTestResult = !empty($current1stTestResult) ? $current1stTestResult->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        $hourCurrent2ndTestResult = !empty($current2ndTestResult) ? $current2ndTestResult->format('H') : '';
        $dayCurrent2ndTestResult = !empty($current2ndTestResult) ? $current2ndTestResult->format('j') : '';
        $monthCurrent2ndTestResult = !empty($current2ndTestResult) ? $current2ndTestResult->format('n') : '';
        $current2ndTestResult = !empty($current2ndTestResult) ? $current2ndTestResult->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        $currentRegistrationDate = !empty($currentRegistrationDate) ? $currentRegistrationDate->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        // Get datas of table ApplyIBAOrg
        $ibaTestDate = '';
        $ibaUpdateAt = '';
        $dateFrom = $year . '-01-01';
        $dateTo = $year . '-12-31';
        $status = 'CONFIRMED';
        $infoApplyIBAOrg = $em->getRepository('Application\Entity\ApplyIBAOrg')->infoApplyIBAOrg($orgId, $dateFrom, $dateTo, $status);
        if ($infoApplyIBAOrg) {
            $ibaTestDate = $infoApplyIBAOrg['testDate'];
            $registrationDate = $infoApplyIBAOrg['registrationDate'];
        }

        $monthIBATestDate = ! empty($ibaTestDate) ? $ibaTestDate->format('n') : '';
        $dayIBATestDate = ! empty($ibaTestDate) ? $ibaTestDate->format('j') : '';
        $ibaTestDate = ! empty($ibaTestDate) ? $ibaTestDate->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        $monthIBARegistrationDate = ! empty($registrationDate) ? $registrationDate->format('n') : '';
        $dayIBARegistrationDate = ! empty($registrationDate) ? $registrationDate->format('j') : '';
        //$registrationDate = ! empty($registrationDate) ? $registrationDate->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) : '';
        
        $eikenTestResult = $em->getRepository('Application\Entity\EikenTestResult')->findOneBy(array(
            'year'           => $currentYear,
            'organizationNo' => $orgNo
        ));
        // Get apply eiken type (1: group, 0: personal)
        $paymentType = null;
        if ($currentKai != null) {
            $invSetting = $em->getRepository('Application\Entity\InvitationSetting')->getInvitationSetting($orgId, $currentEikenScheduleId);
            if ($invSetting) {
                $paymentType = $invSetting['paymentType'];
            }
        }
        // Get datas of next kai
        if ($currentKai != null) {
            $nextKaiData = $em->getRepository('Application\Entity\EikenSchedule')->getNextKaiOfOrg($orgId, $currentKai, $currentYear);
            if ($nextKaiData) {
                $nextKaiYear = ($nextKaiData['year'] != 0) ? $nextKaiData['year'] : 0;
                $nextKai = ($nextKaiData['kai'] != 0) ? $nextKaiData['kai'] : 0;
                $dayNextDeadlineFrom = !empty($nextKaiData['deadlineFrom']) ? $nextKaiData['deadlineFrom']->format('j') : '';
                $monthNextDeadlineFrom = !empty($nextKaiData['deadlineFrom']) ? $nextKaiData['deadlineFrom']->format('n') : '';
                $nextDeadlineFrom = !empty($nextKaiData['deadlineFrom']) ? $nextKaiData['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
            }
            else {
                $nextKaiYear = null;
                $nextKai = null;
                $dayNextDeadlineFrom = '';
                $monthNextDeadlineFrom = '';
                $nextDeadlineFrom = '';
            }
        }
        // Get datas of table EikenTestResult for check 1st result importing
        if ($currentKai != null) {
            $eikenTestResult = $em->getRepository('Application\Entity\EikenTestResult')->findOneBy(array(
                'year'           => $currentYear,
                'organizationNo' => $orgNo,
                'kai'            => $currentKai
            ));
        }
        if ($currentKai != null) {
            $orgApplyEikenData = $em->getRepository('Application\Entity\EikenSchedule')->getOrgApplyEikenData($orgId, $currentKai, $currentYear);
        }
        $dataApplyEiken = $em->getRepository('Application\Entity\EikenSchedule')->getInfoStudentCurrentYear($orgId, $year);
        if ($dataApplyEiken) {
            foreach ($dataApplyEiken as $key => $value) {
                if (!empty($value['deadlineFrom']) && $value['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) <= $currentDate) {
                    $applyEikenScheduleId = $value['id'];
                    $applyYear = ($value['year']) ? $value['year'] : '';
                    $applyKai = ($value['kai']) ? $value['kai'] : '';
                    $applyDeadlineTo = ($value['deadlineTo']) ? $value['deadlineTo'] : '';
                    $applyEikenStatus = ($value['status']) ? $value['status'] : '';
                    $registrationDate = ($value['registrationDate']) ? $value['registrationDate'] : '';
                    $confirmationDate = ($value['confirmationDate']) ? $value['confirmationDate'] : $registrationDate;
                    $applyEikenOrgUpdateAt = ($value['applyEikenOrgUpdateAt']) ? $value['applyEikenOrgUpdateAt'] : '';
                    $applyEikenOrgUpdateBy = ($value['applyEikenOrgUpdateBy']) ? $value['applyEikenOrgUpdateBy'] : '';
                    $applyEikenOrgInsertAt = ($value['applyEikenOrgInsertAt']) ? $value['applyEikenOrgInsertAt'] : '';
                    $applyEikenOrgPicName = ($value['applyEikenOrgInsertBy']) ? $value['applyEikenOrgInsertBy'] : '';
                    $applyEikenOrgExecutorName = ($value['executorName']) ? $value['executorName'] : '';
                    break;
                }
            }
        }
        $dayApplyDeadlineTo = !empty($applyDeadlineTo) ? $applyDeadlineTo->format('j') : '';
        $monthApplyDeadlineTo = !empty($applyDeadlineTo) ? $applyDeadlineTo->format('n') : '';
        $applyDeadlineTo = !empty($applyDeadlineTo) ? $applyDeadlineTo->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        $dayConfirmationDate = !empty($confirmationDate) ? $confirmationDate->format('j') : '';
        $monthConfirmationDate = !empty($confirmationDate) ? $confirmationDate->format('n') : '';
        $registrationDate = !empty($registrationDate) ? $registrationDate->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        $dayApplyEikenOrgInsertAt = !empty($applyEikenOrgInsertAt) ? $applyEikenOrgInsertAt->format('j') : '';
        $monthApplyEikenOrgInsertAt = !empty($applyEikenOrgInsertAt) ? $applyEikenOrgInsertAt->format('n') : '';
        $applyEikenOrgInsertAt = !empty($applyEikenOrgInsertAt) ? $applyEikenOrgInsertAt->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : '';
        $msg = array();
        //MSG 16 Priority = 50
        if ($infoApplyIBAOrg) {
            if ($monthIBARegistrationDate == '' || !in_array($monthIBARegistrationDate, array(
                    '01',
                    '02',
                    '03',
                    '04'
                ))
            ) {
                if (in_array((int)date('m'), array(
                    1,
                    2,
                    3,
                    4
                ))) {
                    $msg16 = array(
                        'msg'      => $this->translate('HomePageNo16'),
                        'priority' => 50,
                        'redirect' => 16
                    );
                    array_push($msg, $msg16);
                }
            }
        }
        // MSG 17 Priority = 50
        if ($infoApplyIBAOrg) {
            if ($monthIBARegistrationDate == '' || !in_array($monthIBARegistrationDate, array(
                    '05',
                    '06',
                    '07',
                    '08'
                ))
            ) {
                if (in_array((int)date('m'), array(
                    5,
                    6,
                    7,
                    8
                ))) {
                    $msg17 = array(
                        'msg'      => $this->translate('HomePageNo17'),
                        'priority' => 50,
                        'redirect' => 17
                    );
                    array_push($msg, $msg17);
                }
            }
        }
        // MSG 18 Priority = 50
        if ($infoApplyIBAOrg) {
            if ($monthIBARegistrationDate == '' || !in_array($monthIBARegistrationDate, array(
                    '09',
                    '10',
                    '11',
                    '12'
                ))
            ) {
                if (in_array((int)date('m'), array(
                    9,
                    10,
                    11,
                    12
                ))) {
                    $msg18 = array(
                        'msg'      => $this->translate('HomePageNo18'),
                        'priority' => 50,
                        'redirect' => 18
                    );
                    array_push($msg, $msg18);
                }
            }
        }
        if ($dataApplyEiken) {
            // R3 US 308 first new announcement
            /* fix error check expired date apply that apply-eiken status is DRAFT*/
            if ($currentDate <= $applyDeadlineTo) {
                if ($applyEikenStatus == 'DRAFT') {
                    $newMSG1R3 = array(
                        'msg'      => sprintf($this->translate('newMSG1R3'), $monthApplyEikenOrgInsertAt, $dayApplyEikenOrgInsertAt, $applyKai, $applyEikenOrgExecutorName),
                        'priority' => 0,
                        'redirect' => 0
                    );
                    array_push($msg, $newMSG1R3);
                }
            }
            // R3 US 308 second new announcement
            if ($dataApplyEiken) {
                if ($currentDate <= $applyDeadlineTo) {
                    if ($applyEikenStatus == 'SUBMITTED' && !empty($applyEikenOrgExecutorName)) {
                        $newMSG2R4 = array(
                            'msg'      => sprintf($this->translate('newMSG2R4'), $monthConfirmationDate, $dayConfirmationDate, $applyKai,$applyEikenOrgExecutorName,$applyEikenOrgPicName, $monthApplyDeadlineTo, $dayApplyDeadlineTo),
                            'priority' => 0,
                            'redirect' => 0
                        );
                        array_push($msg, $newMSG2R4);
                    }else if($applyEikenStatus == 'SUBMITTED' && empty($applyEikenOrgExecutorName)) {
                        $newMSG2R3 = array(
                            'msg'      => sprintf($this->translate('newMSG2R3'), $monthConfirmationDate, $dayConfirmationDate, $applyKai,$applyEikenOrgPicName, $monthApplyDeadlineTo, $dayApplyDeadlineTo),
                            'priority' => 0,
                            'redirect' => 0
                        );
                        array_push($msg, $newMSG2R3);
                    }
                }
            }
        }
        // MSG 08 Priority = 40
        if ($infoStudentCurrentYear && $isDeleteApply == false) {
            if ($currentKai != '' && $currentDate > $currentRegistrationDate && $currentDate <= $firstExamDate && $applyStatus == 'SUBMITTED') {
                $msg08 = array(
                    'msg'      => sprintf($this->translate('HomePageNo8'), $monthFirstExamDate, $dayFirstExamDate, $currentKai),
                    'priority' => 40,
                    'redirect' => 0
                );
                array_push($msg, $msg08);
            }
        }
        // MSG 04 Priority = 30
        if ($currentKai != null && $currentDate >= $beforeOneDayCurrent2ndExamDate && $currentDate < $nextDeadlineFrom) {
            $msg04 = array(
                'msg'      => sprintf($this->translate('HomePageNo4'), $monthNextDeadlineFrom, $dayNextDeadlineFrom, $nextKai),
                'priority' => 30,
                'redirect' => 0
            );
            array_push($msg, $msg04);
        }
        // MSG 19 Priority = 30
        if ($infoApplyIBAOrg) {
            if ($currentKai != null && $currentDate >= $registrationDate && $currentDate <= $ibaTestDate) {
                if ($infoApplyIBAOrg) {
                    $msg19 = array(
                        'msg'      => sprintf($this->translate('HomePageNo19'), $monthIBATestDate, $dayIBATestDate),
                        'priority' => 20,
                        'redirect' => 0
                    );
                    array_push($msg, $msg19);
                }
            }
        }
        // MSG 09 Priority = 20
        if ($infoStudentCurrentYear && $isDeleteApply == false) {
            if ($currentKai != null && $currentDate > $firstExamDate && $currentDate <= $current2ndExamDate) {
                if ($applyStatus == 'SUBMITTED' && $paymentType == 1) {
                    if ($importStatus == 0 && $eikenTestResult && !$eikenTestResult->getTotalPrimaryScore()) {
                        $msg09 = array(
                            'msg'      => sprintf($this->translate('HomePageNo9'), $monthCurrent1stTestResult, $dayCurrent1stTestResult, $currentKai, $monthCurrent2ndExamDate, $dayCurrent2ndExamDate, $currentKai),
                            'priority' => 20,
                            'redirect' => 0
                        );
                        array_push($msg, $msg09);
                    }
                }
            }
        }
        // MSG 11 Priority = 20
        if ($infoStudentCurrentYear && $isDeleteApply == false) {
            if ($currentKai != null && $currentDate > $firstExamDate && $currentDate <= $current2ndExamDate) {
                if ($applyStatus == 'SUBMITTED' && $paymentType == 1) {
                    if ($importStatus == 1 && $eikenTestResult && $eikenTestResult->getTotalPrimaryScore()) {
                        $msg11 = array(
                            'msg'      => sprintf($this->translate('HomePageNo11'), $monthCurrent2ndExamDate, $dayCurrent2ndExamDate, $currentKai),
                            'priority' => 20,
                            'redirect' => 0
                        );
                        array_push($msg, $msg11);
                    }
                }
            }
        }
        // MSG 12 Priority = 20
        if ($infoStudentCurrentYear && $isDeleteApply == false && $current2ndTestResult != null) {
            if ($currentKai != null && $currentDate > $current2ndTestResult && $currentDate <= $nextDeadlineFrom) {
                $msg12 = array(
                    'msg'      => sprintf($this->translate('HomePageNo12'), $monthCurrent2ndTestResult, $dayCurrent2ndTestResult, $hourCurrent2ndTestResult, $currentKai),
                    'priority' => 20,
                    'redirect' => 0
                );
                array_push($msg, $msg12);
            }
        }
        // MSG 05 Priority = 10
        if (empty($currentRegistrationDate)) {
            if( $currentKai != '' && $currentDate > $currentDeadlineTo){
                $msg08 = array(
                    'msg' => sprintf($this->translate('HomePageNo8'), $monthFirstExamDate, $dayFirstExamDate, $currentKai),
                    'priority' => 40,
                    'redirect' => 0
                );
                array_push($msg, $msg08);
            }else if ($currentKai != '' && $currentDate >= $currentDeadlineFrom) {
                $msg05 = array(
                    'msg' => sprintf($this->translate('HomePageNo5'), $currentYear, $currentKai),
                    'priority' => 10,
                    'redirect' => 10
                );
                array_push($msg, $msg05);
            }
        } else {
            if( $currentKai != '' && $currentDate > $currentDeadlineTo){
                $msg08 = array(
                    'msg' => sprintf($this->translate('HomePageNo8'), $monthFirstExamDate, $dayFirstExamDate, $currentKai),
                    'priority' => 40,
                    'redirect' => 0
                );
                array_push($msg, $msg08);
            }else if ($currentKai != '' && $currentDate >= $currentDeadlineFrom && $currentDate <= $currentRegistrationDate) {
                $msg05 = array(
                    'msg' => sprintf($this->translate('HomePageNo5'), $currentYear, $currentKai),
                    'priority' => 10,
                    'redirect' => 10
                );
                array_push($msg, $msg05);
            }
        }
        return $msg;
    }

    public function translate($messageKey)
    {
        $translator = $this->serviceManager->get('MVCTranslator');

        return $translator->translate($messageKey);
    }

    /**
     * Process for achieve goal
     */
    public function getMaxKai($currentYear)
    {
        $repo = $this->entityManager->getRepository('Application\Entity\EikenTestResult');
        $kai = $repo->getMaxKai($currentYear);

        return ($kai != null) ? $kai['kai'] : 4;
    }

    public function getTarget($orgId = 0, $orgSchoolYearId = 0, $year = 2010, $isGraduationGoal = 0)
    {
        $data = $this->entityManager->getRepository('Application\Entity\OrgGraduationGoal');

        return $data->getTarget($orgId, $orgSchoolYearId, $year, $isGraduationGoal);
    }

    /**
     *
     * @author taivh
     * @param unknown $target
     * @param unknown $orgId
     * @param unknown $year
     * @param unknown $maxKai
     * @return multitype:number unknown
     */
    public function getPassRatingAndComparing($orgId = 0, $orgNo = 0, $year = 2010, $eikenLevelId = 7, $target = 0, $kai = 4, $orgSchoolYearId = 0, $numOfSchoolYear)
    {
        $repoClass = $this->entityManager->getRepository('Application\Entity\ClassJ');
        $sumStudent = $repoClass->getNumberOfStudent($orgId, $orgSchoolYearId, $year);
        $year1 = $year;
        if ($kai == 0 || $kai == 4) {
            $kai = 4;
            $year = $year - 1;
        }
        //orgSchoolYear of last year
        $orgSchoolYear1Id = 0;
        $listOrgSchoolYear = $this->listOrgSchoolYear($orgId);
        for ($i = 0; $i < count($listOrgSchoolYear); $i++) {
            if ($listOrgSchoolYear[$i]['id'] == $orgSchoolYearId) {
                if (isset($listOrgSchoolYear[$i + 1]['id']))
                    $orgSchoolYear1Id = $listOrgSchoolYear[$i + 1]['id'];
            }
        }
        // ------ calculating Rate for current year (k = 4)
        $kai1 = 4;
        $numPass = $this->getSumPassLevelAndSchoolYear($orgId, $orgNo, $year1, $kai1, $eikenLevelId, $numOfSchoolYear, $orgSchoolYearId);
        $passRate = $this->passRate($numPass, $sumStudent, $target);
        // --- Pass rate last Kai
        $totalLastKai = 0;
        $kai = $kai - 1;
        if ($kai > 0) {
            for ($i = 1; $i <= $kai; $i++) {
                if ($kai == 3)
                    $totalLastKai += $this->getSumStudent($orgNo, $eikenLevelId, $orgSchoolYear1Id, $year, $i);
                else
                    $totalLastKai += $this->getSumStudent($orgNo, $eikenLevelId, $orgSchoolYearId, $year, $i);
            }
        }
        else
            $totalLastKai = 0;

        $numPassY1 = $this->getSumPassLevelAndSchoolYear($orgId, $orgNo, $year1-1, $kai1, $eikenLevelId, $numOfSchoolYear - 1, $orgSchoolYear1Id); // Num pass last year
        if ($kai == 3)
            $numPass1 = $totalLastKai;
        else
            $numPass1 = $numPassY1 + $totalLastKai;
        $passRate1 = $this->passRate($numPass1, $sumStudent, $target);
        // --- Compare with last Kai
        $compRate = round($passRate, 0) - round($passRate1, 0);
        // --------- calculating Rate for last year
        $passRateY1 = $this->passRate($numPassY1, $sumStudent, $target);
        // $passRate : Pass rate this year
        // $compRate : Comparing Rate with Kai before
        // $numPass : Number of student pass this year
        // $numPassComp: Comparing number pass this year with Kai before
        // $passRate1 : Pass rate of Kai before
        // $numPass1 : Number pass of Kai before
        // $passRateY1 : Pass rate last year
        // $numPassY1 : Number of student pass last year
        return array(
            ($target > 0 && $sumStudent > 0) ? round($passRate, 0) . '<span>%</span>' : '-',
            ($target > 0 && $sumStudent > 0) ? $compRate : '-',
            ($target > 0 && $sumStudent > 0) ? $numPass . '<span>人</span>' : '<p>-</p>',
            ($target > 0 && $sumStudent > 0) ? $numPass - $numPass1 : '-',
            ($target > 0 && $sumStudent > 0) ? round($passRate1, 0) . '<span> %</span>' : '-',
            ($target > 0 && $sumStudent > 0) ? $numPass1 . '<span> 人</span>' : '-',
            ($target > 0 && $sumStudent > 0) ? round($passRateY1, 0) . '<span> %</span>' : '-',
            ($target > 0 && $sumStudent > 0) ? $numPassY1 . '<span> 人</span>' : '-'
        );
    }

    /**
     *
     * @author taivh
     *         Tổng số người đỗ Level mục tiêu theo từng năm học có mặt tại trường tại năm
     */
    public function getSumPassLevelAndSchoolYear($orgId = 0, $orgNo = 0, $year = 2010, $kai = 4, $eikenLevelId = 0, $numOfSchoolYear, $orgSchoolYearID)    {
        $numYear = 0;
        $stNum = '';
        $orgSchoolYearIndex = -1;
        $listOrgSchoolYear = $this->getOrgSchoolYears($orgId);
        $listOrgSchoolYearId = array_values($this->getOrgSchoolYearIdList($orgId));
        if (!empty($listOrgSchoolYear)) {
            foreach ($listOrgSchoolYear as $key => $schoolYear) {
                $listOrgSchoolYear[$key] = 0;
//                $listOrgSchoolYearId[$key] = $listOrgSchoolYearId[$key]['id'];
            }
        }
        if (!empty($listOrgSchoolYearId)) {
            foreach ($listOrgSchoolYearId as $key => $schoolYear) {
                $listOrgSchoolYearId[$key] = $listOrgSchoolYearId[$key]['id'];
                if ($listOrgSchoolYearId[$key] == $orgSchoolYearID)
                    $orgSchoolYearIndex = $key;
            }
        }
        $countSchoolYear = count($listOrgSchoolYear);
        // ---- Get total pass target with n year. (n: total orgSchoolYear)
        $res = $this->entityManager->getRepository('Application\Entity\EikenTestResult')->getTotalStudentWasInSchool($orgId, $orgNo, $eikenLevelId, $year, $countSchoolYear, $kai);
        $item = array();
        foreach ($res as $key => $val) {
            $item[$val['Year']][$val['id']] = $val['Total'];
        }
        $totalPassedForEachYears = array();
        if (!empty($item)) {
            foreach ($item as $yearNo => $detail) {
                $schoolYears = $listOrgSchoolYear;
                foreach ($detail as $schoolYearId => $total) {
                    $schoolYears[$schoolYearId] = $total;
                }
                $totalPassedForEachYears[$yearNo] = array_values($schoolYears);
            }
        }
        $listItem = array_values($totalPassedForEachYears);

        return $this->sumSubLine($totalPassedForEachYears, $orgSchoolYearIndex, $year, $numOfSchoolYear);
    }

    /**
     * Caculating total all element on secondary diagonal of matrix level N
     *
     * @author TaiVH
     */
    public function sumSubLine($arrResult, $orgSchoolYearIndex, $year, $n)
    {
        $count = 0;
        if ($orgSchoolYearIndex>=0)
            for ($i = 0; $i <= $n; $i++) {
                if (isset($arrResult[$year - $i][$orgSchoolYearIndex - $i])) {
                    $count += $arrResult[$year - $i][$orgSchoolYearIndex - $i];
                }
            }
        return $count;
    }

    /**
     * Hight Chart for Achieve goal
     *
     * @author TaiVH
     */
    public function getHightChartForAchieveGoal($orgId = 0, $orgNo = 0, $year = 2010, $eikenLevelId = 7, $target = 0, $kai = 4, $orgSchoolYearId = 0, $numOfSchoolYear)
    {
        $stResult = '';
        $stRating = '';
        $orgSchoolYear0Id = 0;
        $orgSchoolYear1Id = 0;
        $orgSchoolYear2Id = 0;
        $maxKai = $this->getMaxKai($year);
        $maxKai1 = $this->getMaxKai($year-1);
        $maxKai2 = $this->getMaxKai($year-2);
        $listOrgSchoolYear = $this->listOrgSchoolYear($orgId);
        for ($i = 0; $i < count($listOrgSchoolYear); $i++) {
            if ($listOrgSchoolYear[$i]['id'] == $orgSchoolYearId) {
                if (isset($listOrgSchoolYear[$i + 2]['id']))
                    $orgSchoolYear2Id = $listOrgSchoolYear[$i + 2]['id'];
                if (isset($listOrgSchoolYear[$i + 1]['id']))
                    $orgSchoolYear1Id = $listOrgSchoolYear[$i + 1]['id'];
                if (isset($listOrgSchoolYear[$i]['id']))
                    $orgSchoolYear0Id = $listOrgSchoolYear[$i]['id'];
            }
        }
        $repoClass = $this->entityManager->getRepository('Application\Entity\ClassJ');
        $totalStudent2 = (int)$repoClass->getNumberOfStudent($orgId, $orgSchoolYear2Id, $year - 2);
        $totalStudent1 = (int)$repoClass->getNumberOfStudent($orgId, $orgSchoolYear1Id, $year - 1);
        $totalStudent = (int)$repoClass->getNumberOfStudent($orgId, $orgSchoolYear0Id, $year);
        $totalColum1 = 0;
        $totalColum2 = 0;
        $totalColum3 = 0;
        for ($i = 1; $i <= 3; $i++) {
            // -------- Person
            $colum1 = 0;
            $colum2 = 0;
            $colum3 = 0;
            $colum1 = (int)$this->getSumStudent($orgNo, $eikenLevelId, $orgSchoolYear2Id, $year - 2, $i);
            $colum2 = (int)$this->getSumStudent($orgNo, $eikenLevelId, $orgSchoolYear1Id, $year - 1, $i);
            $colum3 = (int)$this->getSumStudent($orgNo, $eikenLevelId, $orgSchoolYear0Id, $year, $i);
            $totalColum1 += $colum1;
            $totalColum2 += $colum2;
            $totalColum3 += $colum3;

            // -------- JSON Peron
            if ($target > 0 && $totalStudent2 > 0 && $maxKai2 != 4)
                $rsString[$i]['data'][0] = $colum1;
            else
                $rsString[$i]['data'][0] = null;
            if ($target > 0 && $totalStudent1 > 0 && $maxKai1 != 4)
                $rsString[$i]['data'][1] = $colum2;
            else
                $rsString[$i]['data'][1] = null;
            if ($target > 0 && $totalStudent > 0 && $i <= $kai && $maxKai != 4)
                $rsString[$i]['data'][2] = $colum3;
            else
                $rsString[$i]['data'][2] = null;
            $stResult .= '<tr class="bgtitle"><td>第' . (string)$i . '回</td><td>' . (($totalStudent2 > 0 && $maxKai2 != 4) ? $colum1 . '人' : '-') . '</td><td>' . (($totalStudent1 > 0 && $maxKai1 != 4) ? $colum2 . '人' : '-') . '</td><td>' . (($i <= $kai && $totalStudent > 0 && $maxKai != 4) ? $colum3 . '人' : '-') . '</td></tr>';
            $passRate1 = round($this->passRate($colum1, $totalStudent2, $target));
            $passRate2 = round($this->passRate($colum2, $totalStudent1, $target));
            $passRate3 = round($this->passRate($colum3, $totalStudent, $target));
            if ($target > 0 && $totalStudent2 > 0 && $maxKai2!= 4)
                $rsRating[$i]['data'][0] = $passRate1;
            else
                $rsRating[$i]['data'][0] = null;
            if ($target > 0 && $totalStudent1 > 0 && $maxKai1 != 4)
                $rsRating[$i]['data'][1] = $passRate2;
            else
                $rsRating[$i]['data'][1] = null;
            if ($target > 0 && $totalStudent > 0 && $i <= $kai && $maxKai != 4)
                $rsRating[$i]['data'][2] = $passRate3;
            else
                $rsRating[$i]['data'][2] = null;
            $stRating .= '<tr class="bgtitle"><td>第' . (string)$i . '回</td><td>' . (($target > 0 && $totalStudent2 > 0 && $maxKai2 != 4) ? $passRate1 . '%' : '-') . '</td><td>' . (($target > 0 && $totalStudent1 > 0 && $maxKai1 != 4) ? $passRate2 . '%' : '-') . '</td><td>' . (($i <= $kai && $target > 0 && $totalStudent > 0 && $maxKai != 4) ? $passRate3 . '%' : '-') . '</td></tr>';
        }
        $stResult .= '<tr class="bgtitle"><td>合計</td><td>' . (($totalStudent2 > 0 && $maxKai2 != 4) ? $totalColum1 . '人' : '-') . '</td><td>' . (($totalStudent1 > 0 && $maxKai1 != 4) ? $totalColum2 . '人' : '-') . '</td><td>' . (($totalStudent > 0 && $maxKai != 4) ? $totalColum3 . '人' : '-') . '</td></tr>';
        $rsString = json::encode($rsString);
        $rsRating = json::encode($rsRating);

        return array(
            $stResult,
            $rsString,
            $stRating,
            $rsRating
        );
    }

    // --- BEGIN TAIVH
    public function getListPassBySchoolYearId($orgId, $orgNo, $year, $kai, $eikenLevelId, $key, $ord)
    {
        return $this->entityManager->getRepository('Application\Entity\EikenTestResult')->getListPassBySchoolYearId($orgId, $orgNo, $year, $kai, $eikenLevelId, $key, $ord);
    }

    public function listEikenLevel()
    {
        return $this->entityManager->getRepository('Application\Entity\EikenLevel')->ListEikenLevel();
    }

    public function listOrgSchoolYear($orgId, $sort = 'DESC')
    {
        return $this->entityManager->getRepository('Application\Entity\OrgSchoolYear')->listSchoolYearName($orgId, $sort);
    }

    public function getOrgSchoolYears($orgId)
    {
        return $this->entityManager->getRepository('Application\Entity\OrgSchoolYear')->getOrgSchoolYears($orgId);
    }

    public function getOrgSchoolYearIdList($orgId)
    {
        return $this->entityManager->getRepository('Application\Entity\OrgSchoolYear')->getOrgSchoolYearIdList($orgId);
    }

    public function getListPupilByYear($orgId, $orgNo, $year, $kai, $kyu)
    {
        return $this->entityManager->getRepository('Application\Entity\EikenTestResult')->getListPupilByYear($orgId, $orgNo, $year, $kai, $kyu);
    }

    public function getSumStudent($orgNo, $eikenLevelId, $orgSchoolYearId, $year, $kai)
    {
        return $this->entityManager->getRepository('Application\Entity\EikenTestResult')->getSumStudent($orgNo, $eikenLevelId, $orgSchoolYearId, $year, $kai);
    }
    // --- END TAIVH
    // ducna17
    public function exportListAttendPupil($schoolYearName, $orgNo, $kai, $year, $response, $mappingLevel, $typeDetail = null, $schoolClassification = null, $schoolYearCode = null, $notMap = null)
    {
        if ($typeDetail == 'B') {
            $paginator = $this->entityManager->getRepository('Application\Entity\EikenTestResult')->
            getDataDetailTableC($orgNo, $year, $kai, $schoolYearCode, $schoolClassification, $typeDetail );
            $filenames = "英検合格実績_" . $year;
            $templateExcel = 'homepage-detailb1';
        }
        else {
            $paginator = $this->entityManager->getRepository('Application\Entity\EikenTestResult')->
            getDataDetailTableC($orgNo, $year, $kai, $schoolYearCode, $schoolClassification , $typeDetail);
            $filenames = "英検受験実_" . $year;
            $templateExcel = 'homepage-detailc';
        }
        if ($kai !== 'all') {
            $filenames .= '_' . $kai;
        }
        $filenames .= '_' . date('Ynj');
        $listExam = array();
        $return = $paginator->getAllItems();
        if (!empty($return) && is_array($return)) {
            foreach ($return as $key => $record) {
                $temp = array();
                $temp['schoolYear'] = $record['schoolYearName'];
                $temp['kumi'] = $record['className'];
                $temp['pupilNo'] = $record['pupilNo'];
                $temp['name'] = $record['tempNameKanji'];
                $temp['examYear'] = $record['year'] . '  第' . $record['kai'] . '回';
                $temp['level'] = $mappingLevel[$record['eikenLevelId']]; // con map voi eikenName
                if ($typeDetail == null) {
                    if ($record['oneExemptionFlag'] == 1) {
                        $temp['passFail1'] = '一次免除';
                    }
                    else {
                        if ($record['primaryPassFailFlag'] == 1) {
                            $temp['passFail1'] = '合格';
                        }
                        elseif ($record['primaryPassFailFlag'] === 0) {
                            $temp['passFail1'] = '不合格';
                        }
                        else {
                            $temp['passFail1'] = '';
                        }
                    }
                    $temp['passFail2'] = '';
                    if ($record['secondPassFailFlag'] == 1) {
                        $temp['passFail2'] = '合格';
                    }
                    elseif ($record['secondPassFailFlag'] === 0) {
                        $temp['passFail2'] = '不合格';
                    }
                }
                $temp['confirmmappingStatus'] = $record['confirmmappingStatus'];
                array_push($listExam, $temp);
            }
        }
        \Dantai\Utility\PHPExcel::export($listExam, $filenames . '.xlsx', $templateExcel);
        

        return $response;
    }

    public function listYear()
    {
        if (date("m") < 4) {
            $currentYear = date("Y") - 1;
        }
        else {
            $currentYear = date("Y");
        }
        $listYear = array();
        for ($i = $currentYear + 2; $i >= 2010; $i--) {
            $listYear[$i] = $i;
        }

        return $listYear;
    }

    public function getDataGoalResult($orgId, $search, $lastOrgSchoolYearId)
    {
        $classIds = array();
        $goalResultSchoolYear = array();
        $goalResultClass = array();
        $graduationGoal = array();
        $em = $this->getEntityManager();
        $data = $em->getRepository('Application\Entity\GoalResults')->getListDataByOrgAndArraySearch($orgId, $search);
        if ($data) {
            foreach ($data as $value) {
                if (trim($value['objectType']) == 'OrgSchoolYear') {
                    $goalResultSchoolYear[$value['year']][$value['objectId']] = $value;
                }
                if (trim($value['objectType']) == 'Class') {
                    $goalResultClass[$value['year']][$value['referenceId']][$value['objectId']] = $value;
                    $classIds[] = $value['objectId'];
                }
            }
        }
        // Get all class of school year
        // $classJ = $em->getRepository('Application\Entity\ClassJ')->getDataClassByClassIds($classIds);
        $classJ = array();
        $dataGoal = $em->getRepository('Application\Entity\OrgGraduationGoal')->getListDataByOrgAndArraySearch($orgId, $search);
        if ($dataGoal) {
            foreach ($dataGoal as $value) {
                if (!empty($value['orgSchoolYearId'])) {
                    $graduationGoal[$value['year']][$value['orgSchoolYearId']] = $value;
                }
                else {
                    $graduationGoal[$value['year']][$lastOrgSchoolYearId] = $value;
                }
            }
        }

        return array(
            $goalResultSchoolYear,
            $goalResultClass,
            $graduationGoal,
            $classJ
        );
    }

    public function getHtmlOutPutOfTemplate($template, $params)
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true)
            ->setTemplate($template)
            ->setVariables($params);
        $htmlOutput = $this->serviceManager->get('viewrenderer')->render($viewModel);

        return $htmlOutput;
    }
    
    public function getApplyEikenOrg($orgId, $eikenScheduleId) {
        if (intval($orgId) <= 0 || intval($eikenScheduleId) <= 0) {
            return false;
        }

        return $this->entityManager->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
                    'organizationId' => $orgId,
                    'eikenScheduleId' => $eikenScheduleId,
                    'isDelete' => 0
        ));
    }
    
    public function listKyuNameExamDate($date){
        $kyuNames = $this->serviceManager->get('config')['MappingLevel'];
        $listKyu = $this->dantaiService->getDateRound2EachKyu();
        $listName = array();
        foreach($listKyu as $k => $v){
            if($v == $date){
                array_push($listName,$kyuNames[$k]);
            }
        }
        return $listName;
    }

}