<?php
namespace GoalSetting\Service;

use Dantai\PublicSession;
use GoalSetting\Service\ServiceInterface\GraduationGoalSettingServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\PrivateSession;
use Doctrine\ORM\EntityManager;
use GoalSetting\Form\GraduationGoal\GraduationGoalSearchForm;
use GoalSetting\Form\GraduationGoal\GraduationGoalNationalSearchForm;
use GoalSetting\Form\GraduationGoal\GraduationGoalAcquisitionRateSearchForm;
use GoalSetting\Form\GraduationGoal\GraduationGoalAcquisitionRateEditForm;
use Zend\Json\Json;

class GraduationGoalSettingService implements GraduationGoalSettingServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    private $id_org;

    private $code_org;

    private $no_org;

    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function __construct()
    {
        $user = PrivateSession::getData('userIdentity');
        $this->no_org = $user['organizationNo'];
        $this->id_org = $user['organizationId'];
        $this->code_org = $user['organizationCode'];
    }

    public function getGraduationGoalSearch($params, $response, $request)
    {
        if ($request->isPost()) {
            $data = $params->fromPost();
            if (empty($data) || empty($data['ddbYear'])) {
                $data['ddbYear'] = $this->formatYear();
                $search = array(
                    'edit' => 0,
                    'rdGoalSetting' => 0,
                    'ddbYear' => $this->formatYear()
                );
            } else {
                $search = array(
                    'edit' => $data['edit'],
                    'rdGoalSetting' => $data['rdGoalSetting'],
                    'ddbYear' => $data['ddbYear']
                );
            }
            PrivateSession::setData('SearchGoalSetting', $search);
        }
        $data = $this->getDataList($data);
        return $response->setContent(Json::encode($data));
    }

    /*
     * get list pass the Exam to display
     */
    public function listpassTheExam($params, $response, $request)
    {
        if ($request->isPost()) {
            $search = $params->fromPost();
            $data = $this->setdataOutBoxViewCity($search);
            if (! empty($data)) {
                $data = $data;
            } else {
                $data = false;
            }
            return $response->setContent(Json::encode($data));
        } else {
            return false;
        }
    }

    public function getDataList($search)
    {
        $em = $this->getEntityManager();
        $yearM = $year = $search['ddbYear'];
        $data = array();
        $listSchoolYear = $em->getRepository('Application\Entity\OrgSchoolYear')->listSchoolYearName($this->id_org);
        $data['idmax'] = 0;
        if (! empty($listSchoolYear)) {
            $goalRate = $em->getRepository('Application\Entity\GoalResults')->getGoalResult($this->id_org, (int) $year - 1);

            $count = count($listSchoolYear);
            if ($search['rdGoalSetting'] == 0) {
                $datasearch['yearMin'] = (int) $year - $count;
                $datasearch['yearMax'] = (int) $year + $count;
                $datasearch['isGraduationGoal'] = $search['rdGoalSetting'];
            } else {
                $datasearch['yearMin'] = (int) $year - 1;
                $datasearch['yearMax'] = (int) $year;
                $datasearch['isGraduationGoal'] = $search['rdGoalSetting'];
            }
            $listtarget = $em->getRepository('Application\Entity\OrgGraduationGoal')->getListTargetPassByYear($this->id_org, $datasearch);

            $arr_v = array();
            foreach ($listtarget as $k => $v) {
                $arr_v[$v['year']][$v['orgSchoolYearId']] = $k;
            }
            $arrTable1 = array();
            $arrTable2 = array();
            $searchIsPass = array();
            $data['idmax'] = $listSchoolYear[0]['id'];

            for ($i = 0; $i < $count; $i ++) {
                $arrTable2[$i]['SchoolYear'] = $arrTable1[$i]['SchoolYear'] = $listSchoolYear[$i]['displayName'];
                $arrTable2[$i]['schoolYearId'] = $arrTable1[$i]['schoolYearId'] = $listSchoolYear[$i]['id'];
                $arrTable2[$i]['pass'] = $arrTable1[$i]['pass'] = '';
                $arrTable2[$i]['totalIsPass'] = $arrTable1[$i]['totalIsPass'] = '';
                $arrTable2[$i]['levelName'] = $arrTable1[$i]['levelName'] = '';
                $arrTable2[$i]['targetPass'] = $arrTable1[$i]['targetPass'] = '';

                if ($search['rdGoalSetting'] == 0) {
                    $arrTable1[$i]['year'] = (int) $year + $i;
                    $key = array_search($arrTable1[$i]['year'], array_column($listtarget, 'year'));
                    if (! empty($listtarget) && false !== $key) {
                        $arrTable1[$i]['eikenLevelId'] = $listtarget[$key]['eikenLevelId'];
                        $arrTable1[$i]['levelName'] = $listtarget[$key]['levelName'];
                        $arrTable1[$i]['targetPass'] = $listtarget[$key]['targetPass'] . '%';
                    }
                } else {
                    $arrTable1[$i]['year'] = (int) $year;
                    $key = false;
                    if ($i == 0) {
                        $k = $this->multi_array_search($listtarget, array(
                            'isGraduationGoal' => 1,
                            'year' => $year
                        ));
                        if (! empty($k))
                            $key = $k[0];
                    }
                    if (false == $key && isset($arr_v[$year][$listSchoolYear[$i]['id']])) {
                        $key = $arr_v[$year][$listSchoolYear[$i]['id']];
                    }
                    if (! empty($listtarget) && false !== $key) {
                        $arrTable1[$i]['eikenLevelId'] = $listtarget[$key]['eikenLevelId'];
                        $arrTable1[$i]['levelName'] = $listtarget[$key]['levelName'];
                        $arrTable1[$i]['targetPass'] = $listtarget[$key]['targetPass'] . '%';
                    }
                }

                // Table 2
                $totalIsPass = '';
                if ($search['rdGoalSetting'] == 0) { // Get data graduation goal setting history
                    $arrTable2[$i]['eikenLevelId'] = 0;
                    $arrTable2[$i]['year'] = (int) $year - 1 - $i;
                    $key2 = array_search($arrTable2[$i]['year'], array_column($listtarget, 'year'));
                    if (false !== $key2 && ! empty($listtarget)) {
                        $arrTable2[$i]['eikenLevelId'] = $listtarget[$key2]['eikenLevelId'];
                        $arrTable2[$i]['levelName'] = $listtarget[$key2]['levelName'];
                        $arrTable2[$i]['targetPass'] = $listtarget[$key2]['targetPass'] . '%';
                    }
                    // Total pupil number
                    $totalPupilInClass = $em->getRepository('Application\Entity\ClassJ')->getTotalStudentByYearAndSchoolYear($this->id_org, $arrTable2[$i]['year'], $data['idmax']);
                    // Number eikenLevel Pass
                    $searchIsPass['eikenLevelId'] = isset($arrTable2[$i]['eikenLevelId']) ? $arrTable2[$i]['eikenLevelId'] : 0;
                    $searchIsPass['orgNo'] = $this->no_org;
                    $searchIsPass['yearMax'] = isset($arrTable2[$i]['year']) ? $arrTable2[$i]['year'] : $this->formatYear();
                    $searchIsPass['yearMin'] = $searchIsPass['yearMax'] - $count + 1;
                    $totalIsPass = $em->getRepository('Application\Entity\EikenTestResult')->getTotalIsPass($searchIsPass);
                    $arrTable2[$i]['totalIsPass'] = 0;
                    $rate = 0;
                    if (! empty($totalIsPass)) {
                        for ($j = 0; $j < $count; $j ++) {
                            $yearJ = $arrTable2[$i]['year'] - $j;
                            $schoolYearJ = $listSchoolYear[$j]['id'];
                            $keyJ = false;
                            $kJ = NULL;
                            $kJ = $this->multi_array_search($totalIsPass, array(
                                'Year' => $yearJ,
                                'orgSchoolYearId' => $schoolYearJ
                            ));
                            if (! empty($kJ))
                                $keyJ = $kJ[0];
                            if (false !== $keyJ && isset($totalIsPass[$keyJ]['totalIsPass'])) {
                                $rate = $rate + (int) $totalIsPass[$keyJ]['totalIsPass'];
                            }
                        }
                    }
                    if (! empty($totalPupilInClass)) {
                        $arrTable2[$i]['pass'] = (int) $rate / (int) $totalPupilInClass * 100;
                        $arrTable2[$i]['pass'] = round($arrTable2[$i]['pass']) . '%';
                    }
                } else { // Get data Year of goal history
                    $arrTable2[$i]['year'] = (int) $year - 1;
                    $year2 = (int) $year - 1;
                    $key2 = false;
                    if ($i == 0) {
                        $k = $this->multi_array_search($listtarget, array(
                            'isGraduationGoal' => 1,
                            'year' => $year2
                        ));
                        if (! empty($k))
                            $key2 = $k[0];
                    }
                    if (isset($arr_v[$year2][$listSchoolYear[$i]['id']])) {
                        $key2 = $arr_v[$year2][$listSchoolYear[$i]['id']];
                    }
                    if (! empty($listtarget) && false !== $key2) {

                        if ($listtarget[$key2]['year'] == (int) $year - 1) {
                            $arrTable2[$i]['eikenLevelId'] = $listtarget[$key2]['eikenLevelId'];
                            $arrTable2[$i]['levelName'] = $listtarget[$key2]['levelName'];
                            $arrTable2[$i]['targetPass'] = $listtarget[$key2]['targetPass'] . '%';
                        }
                        if (! empty($goalRate)) {
                            $keyRate = array_search($listSchoolYear[$i]['id'], array_column($goalRate, 'objectId'));
                            if (false !== $keyRate && ! empty($listtarget)) {
                                $objGoalRate = $goalRate[$keyRate];
                                switch ((int) $arrTable2[$i]['eikenLevelId']) {
                                    case 7:
                                        $arrTable2[$i]['pass'] = $objGoalRate['precentPassLevel5'] . '%';
                                        break;
                                    case 6:
                                        $arrTable2[$i]['pass'] = $objGoalRate['precentPassLevel4'] . '%';
                                        break;
                                    case 5:
                                        $arrTable2[$i]['pass'] = $objGoalRate['precentPassLevel3'] . '%';
                                        break;
                                    case 4:
                                        $arrTable2[$i]['pass'] = $objGoalRate['precentPassLevelPre2'] . '%';
                                        break;
                                    case 3:
                                        $arrTable2[$i]['pass'] = $objGoalRate['precentPassLevel2'] . '%';
                                        break;
                                    case 2:
                                        $arrTable2[$i]['pass'] = $objGoalRate['precentPassLevelPre1'] . '%';
                                        break;
                                    case 1:
                                        $arrTable2[$i]['pass'] = $objGoalRate['precentPassLevel1'] . '%';
                                        break;
                                    default:
                                        $arrTable2[$i]['pass'] = '';
                                        break;
                                }
                            }
                        }
                    }
                }
            }
            $data['graduationTimeTarget'] = array_reverse($arrTable1);
            if ($search['rdGoalSetting'] == 0) {
                $data['graduationYear'] = $arrTable2;
            } else {
                $data['graduationYear'] = array_reverse($arrTable2);
            }
        }
        $search['yearsearch'] = $search['ddbYear'];
        $listpassTheExam = $this->setdataOutBoxViewCity($search);
        if (! empty($listpassTheExam)) {
            $data['graduationStatistics'] = $listpassTheExam;
        } else {
            $data['graduationStatistics'] = false;
        }
        $data['listEkenLevel'] = $this->listEkenLevel();
        return $data;
    }

    /**
     * Multi-array search
     *
     * @param array $array
     * @param array $search
     * @return array
     */
    function multi_array_search($array, $search)
    {

        // Create the result array
        $result = array();

        // Iterate over each array element
        foreach ($array as $key => $value) {

            // Iterate over each search condition
            foreach ($search as $k => $v) {

                // If the array element does not meet the search condition then continue to the next element
                if (! isset($value[$k]) || $value[$k] != $v) {
                    continue 2;
                }
            }

            // Add the array element's key to the result array
            $result[] = $key;
        }

        // Return the result array
        return $result;
    }

    /*
     * Update
     */
    public function treatmentUpdate($params, $response, $request)
    {
        $em = $this->getEntityManager();
        if ($request->isPost()) {
            $data = $params->fromPost('item');
            $idmax = $params->fromPost('eikenLevelIdMax', 0);
            $yeartagetPass = $params->fromPost('yeartagetPass', 0);
            if($yeartagetPass)
            {
                PrivateSession::setData('yeartagetPass-flag',1);
            }
            else 
            {
                PrivateSession::setData('yeartagetPass-flag',0);
            }
            foreach ($data as $k => $value) {
                $schoolYearId = $value['schoolYearId'];
                $yearnow = $this->formatYear();
                $year = isset($value['Year']) ? $value['Year'] : $yearnow;
                if ($yeartagetPass) { // Thiet lap muc tieu tot nghiep
                    $arr = array(
                        'year' => (int) $year,
                        'organizationId' => $this->id_org,
                        'isGraduationGoal' => 1,
                        'isDelete' => 0
                    );
                } else { // Thiet lap muc tieu theo nam
                   
                    if (((int) $schoolYearId == (int) $idmax)) {
                        $arr = array(
                            'year' => (int) $year,
                            'organizationId' => $this->id_org,
                            'isGraduationGoal' => 1,
                            'isDelete' => 0
                        );
                    } else {
                        $arr = array(
                            'year' => (int) $year,
                            'organizationId' => $this->id_org,
                            'orgSchoolYearId' => (int) $schoolYearId,
                            'isDelete' => 0
                        );
                    }
                }
                $orgGraduationGoal = $em->getRepository('Application\Entity\OrgGraduationGoal')->findOneBy($arr);
                if (empty($orgGraduationGoal)) {
                    $orgGraduationGoal = new \Application\Entity\OrgGraduationGoal();
                }
                $tagetPass = isset($value['YearGoal']) ? $value['YearGoal'] : 0;
                $ekenLevel = isset($value['ekenLevel']) ? $value['ekenLevel'] : Null;

                if ((int) $yeartagetPass == 0 && ((int) $schoolYearId == (int) $idmax)) {
                    $orgGraduationGoal->setIsGraduationGoal(1);
                } else {
                    $orgGraduationGoal->setIsGraduationGoal($yeartagetPass);
                }
                $organization = $em->getReference('Application\Entity\Organization', $this->id_org);
                $ekenLevelObj = $em->getReference('Application\Entity\EikenLevel', $ekenLevel);
                $orgGraduationGoal->setYear($year);
                $orgGraduationGoal->setTargetPass($tagetPass);
                $orgGraduationGoal->setEikenLevel($ekenLevelObj);
                if ($yeartagetPass == 0) {
                    $objSchoolYear = $em->getReference('Application\Entity\OrgSchoolYear', $schoolYearId);
                    $orgGraduationGoal->setOrgSchoolYear($objSchoolYear);
                }
                $orgGraduationGoal->setOrganization($organization);
                $em->persist($orgGraduationGoal);
                $em->flush();
            }
            $em->flush();
            $em->clear();
        }
    }

    /*
     * set data in array get to out display box View pass of city
     */
    public function setdataOutBoxViewCity($search)
    {
        $em = $this->getEntityManager();
        $org = $em->getReference('Application\Entity\Organization', $this->id_org);
        $year = isset($search['yearsearch']) ? $search['yearsearch'] : $this->formatYear();
        $orgCode = isset($search['ddbOrganization']) ? $search['ddbOrganization'] : $this->code_org;
        $schoolYear = isset($search['ddbSchoolYear']) ? $search['ddbSchoolYear'] : 0;
        $cityId = isset($search['ddbPrefectures']) ? $search['ddbPrefectures'] : $org->getCityId();
        $data = array();
        $cityPassRate = $em->getRepository('Application\Entity\GoalPass')->getListCityPassRate((int) $year, (int) $schoolYear, $orgCode, (int) $cityId);
        $nationwidePassRate = $em->getRepository('Application\Entity\GoalPass')->getListCityPassRate((int) $year, (int) $schoolYear, $orgCode, 49);
        if (isset($search['ddbPrefectures'])) {
            $listCity = $this->listCity();
            $cityname = $listCity[$search['ddbPrefectures']];
        } else {
            $cityname = $org->getCity()->getCityName();
        }
        // Default 03 year display
        for ($j = 1; $j <= 3; $j ++) {
            $yearIndex = (int) $year - $j;

            $key = $this->multi_array_search($cityPassRate, array(
                'year' => $yearIndex
            ));
            $data[$yearIndex]['city']['cityName'] = $cityname;

            if (! empty($key) && ! empty($cityPassRate)) {
                foreach ($key as $index) {
                    $objCityRate = $cityPassRate[$index];
                    if (! empty($objCityRate['eikenLevelId'])) {
                        $data[$yearIndex]['city'][$objCityRate['eikenLevelId']] = round($objCityRate['ratePass']);
                    }
                }
            }
            $keyPass = $this->multi_array_search($nationwidePassRate, array(
                'year' => $yearIndex
            ));
            if (! empty($keyPass) && ! empty($nationwidePassRate)) {
                foreach ($keyPass as $index) {
                    $objPass = $nationwidePassRate[$index];

                    if (! empty($objPass['eikenLevelId'])) {
                        $data[$yearIndex]['nation'][$objPass['eikenLevelId']] = round($objPass['ratePass']);
                    }
                }
            }
        }

        return $data;
    }

    public function listEkenLevel()
    {
        $em = $this->getEntityManager();
        $listEkenLeveldata = array(
            ''
        );
        $listEkenLevel = $em->getRepository('Application\Entity\EikenLevel')->ListEikenLevel();
        foreach ($listEkenLevel as $key => $value) {
            $listEkenLeveldata[$value['id']] = $value['levelName'];
        }
        return $listEkenLeveldata;
    }

    public function formatYear($year = 0)
    {
        if (empty($year)) {
            $year = date('Y-m-d');
        }
        $month = date("m", strtotime($year));
        if ($month < 3) {
            $year = date("Y", strtotime($year)) - 1;
        } else {
            $year = date("Y", strtotime($year));
        }
        return $year;
    }

    public function getListOrg()
    {
        if (PublicSession::isHighSchool()) {
            return array(
                '01' => '中学校',
                '02' => '小中一貫校',
                '03' => '小中高一貫校',
                '04' => '中高一貫校',
                '05' => '高校'
            );
        }

        return array(
            '00' => '小学校',
            '01' => '中学校',
            '02' => '小中一貫校',
            '03' => '小中高一貫校',
            '04' => '中高一貫校',
            '05' => '高校',
            '10' => '短大',
            '15' => '大学',
            '20' => '高専'
        );
    }

    public function listCity()
    {
        $em = $this->getEntityManager();
        $listCity = $em->getRepository('Application\Entity\City')->getListCity();
        $listCitydata = array();
        foreach ($listCity as $key => $value) {
            if ($value['cityCode'] != '00') {
                $listCitydata[$value['cityCode']] = $value['cityName'];
            }
        }

        return $listCitydata;
    }

    public function getListSchoolYear($params, $response, $request)
    {
        $em = $this->getEntityManager();
        $listSchoolYear = array();
        if ($request->isPost()) {
            $orgCode = $params->fromPost('organizationCode');
            $listSchoolYearData = $em->getRepository('Application\Entity\SchoolYearMapping')->findBy(array(
                'isDelete' => 0,
                'orgCode' => $orgCode
            ));

            if (! empty($listSchoolYearData)) {
                foreach ($listSchoolYearData as $key => $item) {
                    $listSchoolYear[$item->getSchoolYearCode()] = $item->getSchoolYearName();
                }
            }
        }
        return $response->setContent(Json::encode($listSchoolYear));
    }
}
