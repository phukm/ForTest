<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\EikenSchedule;
use Application\Entity\ApplyEikenOrg;
use Application\Entity\ApplyIBAOrg;
use Doctrine\ORM\Query\ResultSetMapping;
use Eiken\Helper\NativePaginator as DTPaginator;

class InquiryStudyGearRepository extends EntityRepository
{

    public function getListDataLearningHistory($learningDate, $type,$learningField)
    {

        $em = $this->getEntityManager();
        $strSelect = '';
        $strGroupBy = '';
        switch ($type) {
            case 1://Org and EikenLevel
                $strSelect = "LearningHistory.eikenLevelId,NULL AS orgSchoolYearId,NULL as classId,";
                $strGroupBy = ",LearningHistory.eikenLevelId";
                break;
            case 2://Org and EikenLevel and SchoolYear
                $strSelect = "LearningHistory.eikenLevelId,pupil.orgSchoolYearId,NULL as classId,";
                $strGroupBy = " ,LearningHistory.eikenLevelId,pupil.orgSchoolYearId";
                break;
            case 3://Org and EikenLevel and SchoolYear and Class
                $strSelect = "LearningHistory.eikenLevelId,pupil.orgSchoolYearId,pupil.classId,";
                $strGroupBy = ",LearningHistory.eikenLevelId,pupil.orgSchoolYearId,pupil.classId";
                break;
            case 4://Org
                $strSelect = "NULL AS eikenLevelId,NULL AS orgSchoolYearId,NULL as classId,";
                break;
            case 5: //Org and SchoolYear
                $strSelect = "NULL AS eikenLevelId,pupil.orgSchoolYearId,NULL as classId,";
                $strGroupBy = ",pupil.orgSchoolYearId";
                break;
            default://Org and SchoolYear and Class
                $strSelect = "NULL AS eikenLevelId,pupil.orgSchoolYearId,pupil.classId,";
                $strGroupBy = ",pupil.orgSchoolYearId,pupil.classId";
                break;
        }
        $sql = " SELECT  NULL AS id, LearningHistory.learningDate as inquiryDate,pupil.organizationId," . $strSelect . "
                    SUM(CASE WHEN LearningHistory.learningType='".$learningField['WORD']."' THEN LearningHistory.learningTime ELSE 0 END) AS vocabularyTime ,
                    SUM(CASE WHEN LearningHistory.learningType='".$learningField['GRAM']."' THEN LearningHistory.learningTime ELSE 0 END) AS grammarTime,
                    SUM(CASE WHEN LearningHistory.learningType='".$learningField['READ']."' THEN LearningHistory.learningTime ELSE 0 END) AS readingTime,
                    SUM(CASE WHEN LearningHistory.learningType='".$learningField['LIST']."' THEN LearningHistory.learningTime ELSE 0 END) AS listeningTime,
                    SUM(CASE WHEN LearningHistory.learningType='".$learningField['EK']."' THEN LearningHistory.learningTime ELSE 0 END) AS eikenTime,
                    SUM(LearningHistory.learningTime) AS totalTime,
                    COUNT(DISTINCT(CASE WHEN LearningHistory.learningType='".$learningField['WORD']."' THEN LearningHistory.personalId  END)) AS vocabularyPeople ,
                    COUNT(DISTINCT(CASE WHEN LearningHistory.learningType='".$learningField['GRAM']."' THEN LearningHistory.personalId  END)) AS grammarPeople,
                    COUNT(DISTINCT(CASE WHEN LearningHistory.learningType='".$learningField['READ']."' THEN LearningHistory.personalId  END)) AS readingPeople,
                    COUNT(DISTINCT(CASE WHEN LearningHistory.learningType='".$learningField['LIST']."' THEN LearningHistory.personalId  END)) AS listeningPeople,
                    COUNT(DISTINCT(CASE WHEN LearningHistory.learningType='".$learningField['EK']."' THEN LearningHistory.personalId  END)) AS eikenPeople,
                    COUNT(DISTINCT LearningHistory.personalId)AS totalPeople ";
        $sql .= " FROM LearningHistory AS LearningHistory INNER JOIN Pupil AS pupil ";
        $sql .= " ON pupil.id = LearningHistory.pupilId ";
        $sql .= " WHERE LearningHistory.isDelete = 0 AND LearningHistory.learningDate = :learningDate ";
        $sql .= " GROUP BY pupil.organizationId" . $strGroupBy;

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addEntityResult('\Application\Entity\InquiryStudyGear', 'inquiry');
        $rsm->addFieldResult('inquiry', 'id', 'id');
        $rsm->addFieldResult('inquiry', 'vocabularyTime', 'vocabularyTime');
        $rsm->addFieldResult('inquiry', 'grammarTime', 'grammarTime');
        $rsm->addFieldResult('inquiry', 'readingTime', 'readingTime');
        $rsm->addFieldResult('inquiry', 'listeningTime', 'listeningTime');
        $rsm->addFieldResult('inquiry', 'eikenTime', 'eikenTime');
        $rsm->addFieldResult('inquiry', 'totalTime', 'totalTime');
        $rsm->addFieldResult('inquiry', 'vocabularyPeople', 'vocabularyPeople');
        $rsm->addFieldResult('inquiry', 'grammarPeople', 'grammarPeople');
        $rsm->addFieldResult('inquiry', 'readingPeople', 'readingPeople');
        $rsm->addFieldResult('inquiry', 'listeningPeople', 'listeningPeople');
        $rsm->addFieldResult('inquiry', 'eikenPeople', 'eikenPeople');
        $rsm->addFieldResult('inquiry', 'totalPeople', 'totalPeople');
        $rsm->addFieldResult('inquiry', 'eikenLevelId', 'eikenLevelId');
        $rsm->addFieldResult('inquiry', 'organizationId', 'organizationId');
        $rsm->addFieldResult('inquiry', 'orgSchoolYearId', 'orgSchoolYearId');
        $rsm->addFieldResult('inquiry', 'classId', 'classId');
        $rsm->addFieldResult('inquiry', 'inquiryDate', 'inquiryDate');
        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter(':learningDate', $learningDate);
        $result = $query->getArrayResult();
        return $result;
    }

    /**
     *
     * Uthv
     *
     * @param unknown $day
     * @param unknown $type
     * @return array InquiryMeasure
     *         *
     */
    public function getListDataEinaviExam($day, $type)
    {
        $em = $this->getEntityManager();
        $strSelect = '';
        $strGroupBy = '';
        switch ($type) {
            case 1://Org and EikenLevel
                $strSelect = ",A.eikenLevelId,NULL AS orgSchoolYearId,NULL as classId";
                $strGroupBy = ",A.eikenLevelId";
                break;
            case 2://Org and EikenLevel and SchoolYear
                $strSelect = ",A.eikenLevelId,pupil.orgSchoolYearId,NULL as classId";
                $strGroupBy = ",A.eikenLevelId,pupil.orgSchoolYearId";
                break;
            case 3://Org and EikenLevel and SchoolYear and Class
                $strSelect = ",A.eikenLevelId,pupil.orgSchoolYearId,pupil.classId";
                $strGroupBy = ",A.eikenLevelId,pupil.orgSchoolYearId,pupil.classId";
                break;
            case 4://Org
                $strSelect = ",NULL AS eikenLevelId,NULL AS orgSchoolYearId,NULL as classId";
                break;
            case 5: //Org and SchoolYear
                $strSelect = ",NULL AS eikenLevelId,pupil.orgSchoolYearId,NULL as classId";
                $strGroupBy = ",pupil.orgSchoolYearId";
                break;
            default://Org and SchoolYear and Class
                $strSelect = ",NULL AS eikenLevelId,pupil.orgSchoolYearId,pupil.classId";
                $strGroupBy = ",pupil.orgSchoolYearId,pupil.classId";
                break;
        }
        $sql = " SELECT NULL AS id,SUM(CASE WHEN A.passFail=1 THEN 1  END) AS pass ,
                     SUM(CASE WHEN A.passFail=0 THEN 1  END) AS fail,
                     A.measureTime,A.examDate as inquiryDate,pupil.organizationId" . $strSelect;
        $sql .= " FROM EinaviExam AS A  INNER JOIN Pupil AS pupil ON pupil.id = A.pupilId ";
        $sql .= " WHERE A.isDelete = 0 AND A.examDate =:examDate";

        $sql .= " GROUP BY A.measureTime,pupil.organizationId" . $strGroupBy;

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('\Application\Entity\InquiryMeasure', 'inquiry');
        $rsm->addFieldResult('inquiry', 'id', 'id');
        $rsm->addFieldResult('inquiry', 'measureTime', 'measureTime');
        $rsm->addFieldResult('inquiry', 'pass', 'pass');
        $rsm->addFieldResult('inquiry', 'fail', 'fail');
        $rsm->addFieldResult('inquiry', 'eikenLevelId', 'eikenLevelId');
        $rsm->addFieldResult('inquiry', 'organizationId', 'organizationId');
        $rsm->addFieldResult('inquiry', 'orgSchoolYearId', 'orgSchoolYearId');
        $rsm->addFieldResult('inquiry', 'classId', 'classId');
        $rsm->addFieldResult('inquiry', 'inquiryDate', 'inquiryDate');
        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter(':examDate', $day);
        $result = $query->getArrayResult();
        //print_r($result);die;
        return $result;
    }

    function getInquiryStudyGear($orgId, $dateFrom, $dateTo, $schoolYear = null, $class = null, $eikenId = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('history.inquiryDate,history.vocabularyTime,history.grammarTime,history.readingTime,history.listeningTime,
                     history.totalTime,history.vocabularyPeople,history.grammarPeople,history.readingPeople,
                     history.listeningPeople,history.totalPeople,history.eikenTime,history.eikenPeople')
                         ->from('Application\Entity\InquiryStudyGear', 'history')
                         ->where('history.organizationId = :orgid')
                         ->andWhere('history.inquiryDate BETWEEN :datefrom AND :dateto ')
                         ->andWhere('history.isDelete = 0')
                         ->setParameter(':orgid', $orgId)
                         ->setParameter(':datefrom', $dateFrom)
                         ->setParameter(':dateto', $dateTo);
        if ($schoolYear)
        {
            $qb->andWhere('history.orgSchoolYearId = :schooyear')->setParameter(':schooyear', $schoolYear);
        }
        else $qb->andWhere('history.orgSchoolYearId is NULL');
        if ($class)
        {
            $qb->andWhere('history.classId= :class')->setParameter(':class', $class);
        }
        else $qb->andWhere('history.classId is NULL');
        if($eikenId)
        {
            $qb->andWhere('history.eikenLevelId= :eikenid')->setParameter(':eikenid', $eikenId);
        }
        else $qb->andWhere('history.eikenLevelId is NULL');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getStudyGearHistory($orgId, $date)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('dt.totalPeople as total')
        ->from('\Application\Entity\InquiryStudyGear', 'dt')
        ->where('dt.organizationId = :org_id')
        ->andWhere('dt.inquiryDate = :date')
        ->andWhere('dt.eikenLevelId is null')
        ->andWhere('dt.orgSchoolYearId is null')
        ->andWhere('dt.classId is null')
        ->setParameter('org_id', $orgId)
        ->setParameter('date', $date);

        $res = $qb->getQuery()->getArrayResult();
        return $res;
    }
}