<?php
namespace Application\Entity\Repository;

use Dantai\Utility\DateHelper;
use Doctrine\ORM\EntityRepository;
use Application\Entity\EikenTestResult;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Eiken\Helper\NativePaginator as DTPaginator;
use Doctrine\ORM\Query\ResultSetMapping;

class EikenTestResultRepository extends EntityRepository
{
    const FAIL = 0;
    const SUCCESS = 1;

    protected $sortFields = array(
        'col1' => 'schYear.id',
        'col2' => 'className',
        'col3' => 'pupilNo',
        'col4' => 'pupilName',
        'col5' => 'levelName',
        'col6' => 'year',
        'col7' => 'kai'
    );

    const SCORE_TYPE_NAME_ORG = 'org';

    const SCORE_TYPE_NAME_ORGSCHOOLYEAR = 'orgSchoolYear';

    const SCORE_TYPE_NAME_CLASS = 'class';

    const SCORE_EDGE_LEAST = 'least';

    const SCORE_EDGE_GREATEST = 'greatest';

    const NOT_DELETE_VALUE = 0;

    public function checkEikenTestResultPupil($pupilListId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e.id,e.pupilId,e.pupilName')
            ->from('Application\Entity\EikenTestResult', 'e', 'e.pupilId')
            ->where("e.pupilId IN (:pupilListId)")
            ->andWhere('e.mappingStatus = 1')
            ->setParameter(':pupilListId', $pupilListId);

        return $qb->getQuery()->getArrayResult();
    }

    public function getDataResultLastestByPupilId($pupilId, $fieldOrderBy = '')
    {
        if (empty($fieldOrderBy)) {
            $fieldOrderBy = 'eikenTestResult.id';
        }

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenTestResult.certificationDate, eikenTestResult.secondCertificationDate, eikenTestResult.eikenLevelId, eikenTestResult.oneExemptionFlag, eikenTestResult.secondPassFailFlag')
            ->from('Application\Entity\EikenTestResult', 'eikenTestResult')
            ->where('eikenTestResult.pupilId = :pupilId')
            ->setParameter(':pupilId', $pupilId)
            ->orderBy($fieldOrderBy, 'DESC')
            ->setMaxResults(1);
        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    public function getDataResultLastestByPupilIdAndType($pupilId, $type = 0)
    {
        if ($type == 0) {
            // Exam Date of Eiken = [二次認定日] (EikenDate2) nếu [英検申込] (AppliedEiken) = Level 1, Pre1, 2, Pre2, 3
            $eikenLevels = array(
                1,
                2,
                3,
                4,
                5
            );
            $eikenFieldOrderBy = 'eikenTestResult.secondCertificationDate';
        } else {
            // Exam Date of Eiken = [認定日] (ExamDate1) nếu [英検申込] (AppliedEiken) = level 4, 5
            $eikenLevels = array(
                6,
                7
            );
            $eikenFieldOrderBy = 'eikenTestResult.certificationDate';
        }

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenTestResult.certificationDate, eikenTestResult.secondCertificationDate, eikenTestResult.eikenLevelId, eikenTestResult.oneExemptionFlag, eikenTestResult.secondPassFailFlag')
            ->from('Application\Entity\EikenTestResult', 'eikenTestResult')
            ->where('eikenTestResult.pupilId = :pupilId')
            ->andWhere($qb->expr()
            ->in('eikenTestResult.eikenLevelId', ':eikenLevelIds'))
            ->setParameter(':pupilId', $pupilId)
            ->setParameter(':eikenLevelIds', $eikenLevels)
            ->andWhere('eikenTestResult.primaryPassFailFlag IS NOT NULL OR eikenTestResult.secondPassFailFlag IS NOT NULL')
            ->orderBy($eikenFieldOrderBy, 'DESC')
            ->setMaxResults(1);
        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    public function getDataInquiryEiken($orgNo, $searchCriteria = false)
    {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();

        $query->select('eikentest.id, eikentest.year, eikentest.kai, eikentest.examineeNumber, eikentest.eikenLevelId,
            eikentest.schoolYearCode, eikentest.classCode, eikentest.nameKanji, eikentest.nameKana, eikentest.className, eikentest.schoolYearName,
            CASE
                    WHEN eikentest.pupilId is NULL THEN eikentest.pupilNo
                    ELSE eikentest.tempPupilNo
            END as pupilNo,
            eikentest.pupilId, eikentest.pupilName, eikentest.tempNameKanji, eikentest.organizationNo, eikentest.schoolClassification,
            eikentest.oneExemptionFlag, eikentest.primaryPassFailFlag,
            eikentest.primaryFailureLevel, eikentest.totalPrimaryScore, eikentest.firstExamResultsPerfectScore,
            eikentest.secondPassFailFlag, eikentest.secondUnacceptableLevel, eikentest.totalSecondScore, eikentest.secondExamResultsPerfectScore,
            eikentest.mappingStatus, eikentest.firstExamResultsFlagForDisplay,
            eikenlevel.levelName as eikenlevelLevelName,
            CASE 
                    WHEN eikentest.eikenLevelId < 4 
                    THEN eikentest.firstSoreThreeSkillRLW 
                    ELSE eikentest.firsrtScoreTwoSkillRL 
                    END AS scoreRound1 ,
            eikentest.cSEScoreSpeaking as scoreRound2,
            eikentest.cSEScoreReading,
            eikentest.cSEScoreListening,
            eikentest.cSEScoreWriting,
            eikentest.eikenBand1 , eikentest.eikenBand2
            ');
        $query->from('\Application\Entity\EikenTestResult', 'eikentest');
        $query->leftJoin('\Application\Entity\EikenLevel', 'eikenlevel', \Doctrine\ORM\Query\Expr\Join::WITH, 'eikenlevel.id = eikentest.eikenLevelId');
        $query->groupBy('eikentest.id');
        $query->andWhere("eikentest.isDelete = 0");

        if ($orgNo) {
            $query->andWhere('eikentest.organizationNo = :orgNo');
            $query->setParameter(':orgNo', $orgNo);
        }
        if ($searchCriteria['year']) {
            $query->andWhere('eikentest.year = :year');
            $query->setParameter(':year', (int) $searchCriteria['year']);
        }
        if ($searchCriteria['kai']) {
            $query->andWhere('eikentest.kai = :kai');
            $query->setParameter(':kai', (int) $searchCriteria['kai']);
        }
        if ($searchCriteria['orgSchoolYear']) {
            $query->andWhere('eikentest.schoolYearName LIKE :schoolyear');
            $query->setParameter(':schoolyear', $searchCriteria['orgSchoolYear']);
        }
        if ($searchCriteria['classj']) {
                $query->andWhere('eikentest.className LIKE :class');
                $query->setParameter(':class', $searchCriteria['classj']);
        }
        if ($searchCriteria['name'] != NULL) {
            $query->andWhere('eikentest.tempNameKanji LIKE :name');
            $query->setParameter(':name', "%" . trim($searchCriteria['name']) . "%");
        }

        $query->addOrderBy('eikentest.schoolClassification', 'ASC');
        $query->addOrderBy('eikentest.schoolYearName', 'ASC');
        $query->addOrderBy('eikentest.className', 'ASC');
        $query->addOrderBy('eikentest.tempNameKanji', 'ASC');

        $paginator = new DTPaginator($query, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    /**
     * AnNV6 UC11
     * Get list of SchoolYearCode from table EikenTestResult
     */
    public function getSchoolYearCode($orgNo = false, $sessionYear = false, $sessionKai = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('eikentest');
        $qb->from('\Application\Entity\EikenTestResult', 'eikentest');
        $qb->andWhere('eikentest.isDelete = 0');
        if ($orgNo) {
            $qb->andWhere('eikentest.organizationNo = :orgNo');
            $qb->setParameter(':orgNo', trim($orgNo));
        }
        if ($sessionYear) {
            $qb->andWhere('eikentest.year = :year');
            $qb->setParameter(':year', (int) $sessionYear);
        }
        if ($sessionKai) {
            $qb->andWhere('eikentest.kai = :kai');
            $qb->setParameter(':kai', (int) $sessionKai);
        }
        $qb->groupBy('eikentest.schoolYearName');
        $qb->orderBy('eikentest.schoolYearName', 'ASC');

        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    /**
     * AnNV6 UC11
     * Get list of ClassCode from table EikenTestResult
     */
    public function getClassCode($orgNo = false, $sessionYear = false, $sessionKai = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikentest');
        $qb->from('\Application\Entity\EikenTestResult', 'eikentest');
        $qb->andWhere('eikentest.isDelete = 0');
        $qb->andWhere('eikentest.className != '.$qb->expr()->literal(''));
        if ($orgNo) {
            $qb->andWhere('eikentest.organizationNo = :orgNo');
            $qb->setParameter(':orgNo', trim($orgNo));
        }
        if ($sessionYear) {
            $qb->andWhere('eikentest.year = :year');
            $qb->setParameter(':year', (int) $sessionYear);
        }
        if ($sessionKai) {
            $qb->andWhere('eikentest.kai = :kai');
            $qb->setParameter(':kai', (int) $sessionKai);
        }
        $qb->groupBy('eikentest.className');
        $qb->orderBy('eikentest.className', 'ASC');

        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getHistoryPupilEiken($searchCriteria, $orgNo = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('eikentest.id, eikentest.year, eikentest.kai, eikentest.examineeNumber, eikentest.eikenLevelId,
            eikentest.schoolYearCode, eikentest.classCode, eikentest.nameKanji, eikentest.nameKana, eikentest.className, eikentest.schoolYearName, eikentest.pupilNo,
            eikentest.pupilId, eikentest.pupilName, eikentest.organizationNo, eikentest.schoolClassification,
            eikentest.oneExemptionFlag, eikentest.primaryPassFailFlag,
            eikentest.primaryFailureLevel, eikentest.totalPrimaryScore, eikentest.firstExamResultsPerfectScore,
            eikentest.secondPassFailFlag, eikentest.secondUnacceptableLevel, eikentest.totalSecondScore, eikentest.secondExamResultsPerfectScore,
            eikentest.mappingStatus, eikentest.firstExamResultsFlagForDisplay,
            eikentest.vocabularyFieldScore, eikentest.vocabularyScore, eikentest.readingFieldScore, eikentest.readingScore, eikentest.listeningFieldScore, eikentest.listeningScore,
            eikentest.compositionFieldScore, eikentest.compositionScore,
            eikentest.scoreAccordingField1, eikentest.scoringAccordingField1, eikentest.scoreAccordingField2, eikentest.scoringAccordingField2,
            eikentest.scoreAccordingField3, eikentest.scoringAccordingField3, eikentest.scoreAccordingField4, eikentest.scoringAccordingField4,
            eikentest.cSEScoreReading, eikentest.cSEScoreListening, eikentest.cSEScoreWriting, eikentest.cSEScoreSpeaking,
            eikentest.eikenBand1, eikentest.eikenBand2,
            eikenlevel.levelName as eikenlevelLevelName');
        $qb->from('\Application\Entity\EikenTestResult', 'eikentest');
        $qb->leftJoin('\Application\Entity\EikenLevel', 'eikenlevel', \Doctrine\ORM\Query\Expr\Join::WITH, 'eikenlevel.id = eikentest.eikenLevelId');
        $qb->groupBy('eikentest.id');
        $qb->andWhere('eikentest.isDelete = 0');

        if ($orgNo) {
            $qb->andWhere('eikentest.organizationNo = :orgNo')->setParameter(':orgNo', $orgNo);
        }

        if ($searchCriteria['pupilId'] != null) {
            $qb->andWhere('eikentest.pupilId = :pupilid')->setParameter(':pupilid', $searchCriteria['pupilId']);
        } else {
            $qb->andWhere('eikentest.id = :id')->setParameter(':id', $searchCriteria['id']);
        }

        $qb->addOrderBy('eikentest.year', 'DESC');
        $qb->addOrderBy('eikentest.kai', 'ASC');

        $paginator = new DTPaginator($qb, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    // ducna17
    public function getEikenTestResult($year, $kai, $organizationNo, $isMapped = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('EikenTestResult.id, EikenTestResult.tempNameKanji, EikenTestResult.nameKanji, EikenTestResult.nameKana,
                EikenTestResult.birthday, EikenTestResult.pupilId, EikenTestResult.organizationName as schoolNumber,
                EikenTestResult.schoolYearCode,EikenTestResult.classCode,
                EikenTestResult.year,EikenTestResult.kai,
                EikenTestResult.schoolClassification,
                EikenTestResult.totalPrimaryScore,
                EikenTestResult.firstExamResultsPerfectScore,
                EikenTestResult.oneExemptionFlag,
                EikenTestResult.eikenLevelId,
                EikenTestResult.vocabularyScore,
                EikenTestResult.secondExamResultsPerfectScore,
                EikenTestResult.readingScore,
                EikenTestResult.listeningScore,
                EikenTestResult.compositionScore,
                EikenTestResult.primaryPassFailFlag,
                EikenTestResult.primaryFailureLevel,
                EikenTestResult.totalSecondScore,
                EikenTestResult.secondPassFailFlag,
                EikenTestResult.secondUnacceptableLevel,
                EikenTestResult.scoringAccordingField1,
                EikenTestResult.scoringAccordingField2,
                EikenTestResult.scoringAccordingField3,
                EikenTestResult.scoringAccordingField4,
                EikenTestResult.eikenLevelId,
                EikenTestResult.year,
                EikenTestResult.kai,
                EikenTestResult.cSEScoreReading,
                EikenTestResult.cSEScoreListening,
                EikenTestResult.cSEScoreWriting,
                EikenTestResult.cSEScoreSpeaking,
                EikenTestResult.primaryPassFailFlag,
                EikenTestResult.secondPassFailFlag,
                EikenTestResult.isPass,
                EikenTestResult.certificationDate
                ')
            ->from('\Application\Entity\EikenTestResult', 'EikenTestResult')
            ->where('EikenTestResult.isDelete = 0')
            ->andWhere('EikenTestResult.organizationNo =:orgNo')
            ->andWhere('EikenTestResult.year =:year')
            ->andWhere('EikenTestResult.kai =:kai')
            ->setParameter(':orgNo', $organizationNo)
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai)
            ->addOrderBy('EikenTestResult.schoolClassification', 'ASC')
            ->addOrderBy('EikenTestResult.schoolYearCode', 'ASC')
            ->addOrderBy('EikenTestResult.classCode', 'ASC')
            ->addOrderBy('EikenTestResult.nameKanji', 'ASC');
        if ($isMapped === true) {
            $qb->andWhere('EikenTestResult.pupilId IS NOT NULL');
        } elseif ($isMapped === false) {
            $qb->andWhere('EikenTestResult.isMapped = 0');
        }
        $query = $qb->getQuery();
        return $query->getArrayResult();
    }

    public function getDataLastestByPupilId($pupilId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenTestResult')
            ->from('Application\Entity\EikenTestResult', 'eikenTestResult')
            ->where('eikenTestResult.pupilId = :pupilId')
            ->setParameter(':pupilId', $pupilId)
            ->andWhere('eikenTestResult.primaryPassFailFlag IS NOT NULL OR eikenTestResult.secondPassFailFlag IS NOT NULL')
            ->orderBy('eikenTestResult.year', 'DESC')
            ->addOrderBy('eikenTestResult.kai', 'DESC')
            ->setMaxResults(1);
        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();
        return $result;
    }

    // ducna17
    public function updateMappingStatus($ids, $status)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->set('eikenTestResult.mappingStatus', ':status')
            ->where('eikenTestResult.id IN (:ids)')
            ->setParameter(':ids', $ids)
            ->setParameter(':status', $status);
        $qb->getQuery()->execute();
    }

    // ducna17
    public function updatePupilId($id, $pupilId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->set('eikenTestResult.tempPupilId', ':pupilId')
            ->set('eikenTestResult.pupilId', ':pupilId')
            ->where('eikenTestResult.id = :id')
            ->setParameter(':pupilId', $pupilId)
            ->setParameter(':id', $id);
        $qb->getQuery()->execute();
    }

    // ducna17
    public function updateTempPupilId($id, $pupilId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->set('eikenTestResult.tempPupilId', ':pupilId')
            ->where('eikenTestResult.id = :id')
            ->setParameter(':pupilId', $pupilId)
            ->setParameter(':id', $id);
        $qb->getQuery()->execute();
    }

    // ducna17
    public function updateTempData($ids)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->set('eikenTestResult.mappingStatus', '1')
            ->set('eikenTestResult.pupilId', 'eikenTestResult.tempPupilId')
            ->set('eikenTestResult.dantaiSchoolYearCode', 'eikenTestResult.tempDantaiSchoolYearCode')
            ->set('eikenTestResult.tempNameKanji', 'eikenTestResult.preTempNameKanji')
            ->set('eikenTestResult.schoolYearName', 'eikenTestResult.tempSchoolYearName')
            ->set('eikenTestResult.className', 'eikenTestResult.tempClassName')
            ->set('eikenTestResult.pupilNo', 'eikenTestResult.tempPupilNo')
            ->set('eikenTestResult.classId', 'eikenTestResult.tempClassId')
            ->set('eikenTestResult.orgSchoolYearId', 'eikenTestResult.tempOrgSchoolYearId')
            ->where('eikenTestResult.id IN (:ids)')
            ->setParameter(':ids', $ids);
        $qb->getQuery()->execute();
    }
public function getListDataById($ids)
{
    $em = $this->getEntityManager();
    $qb = $em->createQueryBuilder();
    $qb->select('eikenTestResult')  
        ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
    ->where('eikenTestResult.id IN (:ids)')
    ->setParameter(':ids', $ids);
    return $qb->getQuery()->getResult();
}
public function getListDataByEikenScore($ids)
{
    $em = $this->getEntityManager();
    $qb = $em->createQueryBuilder();
    $qb->select('eikenScore')
    ->from('\Application\Entity\EikenScore', 'eikenScore')
    ->where('eikenScore.eikenTestResultId IN (:ids)')
    ->setParameter(':ids', $ids);
    return $qb->getQuery()->getResult();
}
    /**
     * TaiVH UC10
     *
     * @param number $orgNo
     * @param number $year
     * @param number $kai
     * @param number $mappingStatus
     */
    public function getEikenTestResultByOrgNo($orgNo = 0, $year = 2010, $kai = 1, $mappingStatus = 1)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('EikenTestResult.nameKanji,
                EikenTestResult.preTempNameKanji,
                EikenTestResult.tempNameKanji,
                EikenTestResult.tempPupilId,
                EikenTestResult.id,
                EikenTestResult.mappingStatus,
                EikenTestResult.nameKana,
                EikenTestResult.birthday,
                EikenTestResult.pupilId,
                EikenTestResult.schoolClassification as schoolNumber,
                EikenTestResult.schoolYearCode,
                EikenTestResult.classCode,
                EikenTestResult.oneExemptionFlag,
                EikenTestResult.year,
                EikenTestResult.kai,
                EikenTestResult.totalPrimaryScore,
                EikenTestResult.firstExamResultsPerfectScore,
                EikenTestResult.eikenLevelId,
                EikenTestResult.vocabularyScore,
                EikenTestResult.secondExamResultsPerfectScore,
                EikenTestResult.readingScore,
                EikenTestResult.listeningScore,
                EikenTestResult.compositionScore,
                EikenTestResult.primaryPassFailFlag,
                EikenTestResult.primaryFailureLevel,
                EikenTestResult.totalSecondScore,
                EikenTestResult.secondPassFailFlag,
                EikenTestResult.secondUnacceptableLevel,
                EikenTestResult.scoringAccordingField1,
                EikenTestResult.scoringAccordingField2,
                EikenTestResult.scoringAccordingField3,
                EikenTestResult.scoringAccordingField4,
                EikenTestResult.firstExamResultsFlagForDisplay')
            ->from('\Application\Entity\EikenTestResult', 'EikenTestResult')
            ->where('EikenTestResult.isDelete = 0')
            ->andWhere('EikenTestResult.year =:year')
            ->andWhere('EikenTestResult.kai =:kai')
            ->andWhere('EikenTestResult.organizationNo =:orgNo')
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai)
            ->setParameter(':orgNo', $orgNo);
        if ($mappingStatus == 1) {
            $qb->andWhere('EikenTestResult.mappingStatus = 1');
        } else {
            $qb->andWhere('EikenTestResult.mappingStatus IN (0,2)');
        }

        $qb->addOrderBy('EikenTestResult.mappingStatus', 'ASC')
            ->addOrderBy('EikenTestResult.schoolClassification', 'ASC')
            ->addOrderBy('EikenTestResult.schoolYearCode', 'ASC')
            ->addOrderBy('EikenTestResult.classCode', 'ASC')
            ->addOrderBy('EikenTestResult.nameKana', 'ASC');
        $paginator = new DTPaginator($qb, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    public function deleteEikenTestResult($organizationNo, $year, $kai)
    {
        $em = $this->getEntityManager();
        
        $qb = $em->createQueryBuilder();
        $qb->delete('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->where('eikenTestResult.organizationNo = :orgNo')
            ->andWhere('eikenTestResult.year = :year')
            ->andWhere('eikenTestResult.kai = :kai')
            ->setParameter(':orgNo', $organizationNo)
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai);
        try {
            $query = $qb->getQuery();
            $query->execute();
            return self::SUCCESS;
        }catch (Exception $e){
            return self::FAIL;
        }
    }

    public function getListEikenExamHistory($organizationNo, $keyword = '', $year = '')
    {
        $em = $this->getEntityManager();
        
        if($keyword){
            $searchYear = $keyword['year'];
        }
        $qb = $em->createQueryBuilder();
        $qe = $qb->expr();
        $conditionEikenNoPupil = $qe->andX();
        $conditionIBANoPupil = $qe->andX();
        $conditionEikenPupil = $qe->andX();
        $conditionIBAPupil = $qe->andX();
        $conditionNamePupil=$qe->andX();
        if (isset($keyword['year']) && $keyword['year'] != '') {
            $conditionEikenNoPupil->add($qe->eq('etr.year', ":historyYear"));
            $conditionIBANoPupil->add($qe->eq('itr.year', ":historyYear"));
            $conditionEikenPupil->add($qe->eq('eiken.year', ":historyYear"));
            $conditionIBAPupil->add($qe->eq('iba.year', ":historyYear"));
        }
        if (isset($keyword['orgSchoolYear']) && $keyword['orgSchoolYear'] != '') {
            $conditionEikenNoPupil->add($qe->eq('etr.schoolYearName', ":schoolYearName"));
            $conditionIBANoPupil->add($qe->eq('itr.schoolYearName', ":schoolYearName"));
            $conditionEikenPupil->add($qe->eq('eiken.schoolYearName', ":schoolYearName"));
            $conditionIBAPupil->add($qe->eq('iba.schoolYearName', ":schoolYearName"));
        }
        if (isset($keyword['classj']) && $keyword['classj'] != '') {
            $conditionEikenNoPupil->add($qe->eq('etr.className', ":className"));
            $conditionIBANoPupil->add($qe->eq('itr.className', ":className"));
            $conditionEikenPupil->add($qe->eq('eiken.className', ":className"));
            $conditionIBAPupil->add($qe->eq('iba.className', ":className"));
        }
        if (isset($keyword['name']) && $keyword['name'] != '') {
            //        update function for : GNCCNCJDM-235
            $conditionNamePupil->add($qe->like('history.pupilName', ":pupilName"));
        }
        if ($keyword == array() || (isset($keyword['name']) && $keyword['name'] == '' && isset($keyword['classj']) && $keyword['classj'] == '' && isset($keyword['orgSchoolYear']) && $keyword['orgSchoolYear'] == '' && ! isset($keyword['year']))) {
            $conditionEikenNoPupil->add($qe->eq('etr.year', (int) $searchYear));
            $conditionIBANoPupil->add($qe->eq('itr.year', (int) $searchYear));
            $conditionEikenPupil->add($qe->eq('eiken.year', (int) $searchYear));
            $conditionIBAPupil->add($qe->eq('iba.year', (int) $searchYear));
        }
        
        $conditionENoPupil = ($conditionEikenNoPupil->count()) ? ' AND ' . $conditionEikenNoPupil : '';
        $conditionINoPupil = ($conditionIBANoPupil->count()) ? ' AND ' . $conditionIBANoPupil : '';
        $conditionEPupil = ($conditionEikenPupil->count()) ? ' AND ' . $conditionEikenPupil : '';
        $conditionIBAPupil = ($conditionIBAPupil->count()) ? ' AND ' . $conditionIBAPupil : '';
        //        update function for : GNCCNCJDM-235
        $conditionName= ($conditionNamePupil->count()) ? ' WHERE ' . $conditionNamePupil : '';
//        update function for : GNCCNCJDM-235
//        update function for : GNCCNCJDM-236
        
        $sql = "
            SELECT history.id, history.eikenId, history.ibaId, history.pupilId, history.eikenLevelId, history.ibaLevelId, history.historyYear, history.schoolYearName,
                history.schoolYearCode, history.className, history.classCode, history.pupilNo, history.pupilName,
                history.type ,  history.isPass , history.testType, history.total
            FROM
                (SELECT DISTINCT etr.id as id,etr.id as eikenId,'' as ibaId , etr.pupilId as pupilId, CASE WHEN etr.IsPass =1 THEN etr.eikenLevelId ELSE '' END as eikenLevelId, '' as ibaLevelId, etr.year as historyYear,
                etr.schoolYearName as schoolYearName, etr.schoolYearCode as schoolYearCode, etr.className as className,
                etr.classCode as classCode, etr.pupilNo as pupilNo, etr.tempNameKanji as pupilName, etr.isPass as isPass ,'eiken' as type,
                '' as testType, '' as total
                    FROM EikenTestResult etr
                    WHERE etr.pupilId IS NULL AND etr.isDelete = 0 AND etr.organizationNo = :orgNo
                    " . $conditionENoPupil . "
                UNION
                SELECT DISTINCT itr.id as id,'' as eikenId, itr.id as ibaId, itr.pupilId as pupilId, '' as eikenLevelId,  itr.eikenLevelTotalNo as ibaLevelId, itr.year as historyYear,
                    itr.schoolYearName as schoolYearName, itr.schoolYear as schoolYearCode, itr.className as className,
                    itr.classCode as classCode, itr.pupilNo as pupilNo, itr.tempNameKana as pupilName, itr.isPass as isPass ,'iba' as type,
                    itr.testType as testType, itr.IBACSETotal as total
                        FROM IBATestResult itr
                        WHERE itr.pupilId IS NULL AND itr.isDelete = 0 AND itr.organizationNo = :orgNo
                        " . $conditionINoPupil . "
                UNION
                SELECT * FROM (
                    SELECT tbl.id as id, tbl.eikenId, CASE tbl.ibaId WHEN  '' THEN (SELECT ib1.id FROM IBATestResult as ib1 WHERE ib1.OrganizationNo = :orgNo  and ib1.PupilId=tbl.pupilId AND ib1.`Year`=tbl.historyYear  AND ib1.IsDelete=0 ORDER BY ib1.eikenLevelTotalNo  ASC LIMIT 1) ELSE tbl.ibaId END as ibaId, 
                        tbl.pupilId as pupilId, tbl.eikenLevelId as eikenLevelId, 
                        CASE tbl.ibaLevelId WHEN  '' THEN (SELECT ib1.EikenLevelTotalNo FROM IBATestResult as ib1 WHERE ib1.OrganizationNo = :orgNo  and ib1.PupilId=tbl.pupilId AND ib1.`Year`=tbl.historyYear  AND ib1.IsDelete=0 ORDER BY ib1.eikenLevelTotalNo  ASC LIMIT 1) ELSE tbl.ibaLevelId END as ibaLevelId, 
                        tbl.historyYear as historyYear,
                        tbl.schoolYearName as schoolYearName, tbl.schoolYearCode as schoolYearCode, tbl.className as className,
                        tbl.classCode as classCode, tbl.pupilNo as pupilNo, tbl.pupilName as pupilName, tbl.isPass as isPass  ,tbl.type as type ,
                        tbl.testType as testType, tbl.total as total
                            FROM (
                                SELECT eiken.id as id,eiken.id as eikenId,'' as ibaId, eiken.pupilId as pupilId, CASE WHEN eiken.IsPass =1 THEN eiken.eikenLevelId ELSE '' END as eikenLevelId, '' as ibaLevelId, eiken.year as historyYear,
                                    eiken.schoolYearName as schoolYearName, eiken.schoolYearCode as schoolYearCode, eiken.className as className,
                                    eiken.classCode as classCode, eiken.tempPupilNo as pupilNo, eiken.tempNameKanji as pupilName, eiken.isPass as isPass ,'eiken' as type,
                                    '' as testType, '' as total
                                FROM EikenTestResult eiken
                                WHERE eiken.pupilId IS NOT NULL AND eiken.isDelete = 0 AND eiken.organizationNo = :orgNo
                                " . $conditionEPupil . "
                                UNION
                                SELECT iba.id as id, '' as eikenId, iba.id as ibaId,  iba.pupilId as pupilId, '' as eikenLevelId, iba.eikenLevelTotalNo as ibaLevelId, iba.year as historyYear,
                                    iba.schoolYearName as schoolYearName, iba.schoolYear as schoolYearCode, iba.className as className,
                                    iba.classCode as classCode, iba.tempPupilNo as pupilNo, iba.tempNameKana as pupilName, iba.isPass as isPass , 'iba' as type ,
                                    iba.testType as testType, iba.IBACSETotal as total
                                FROM IBATestResult iba
                                WHERE iba.pupilId IS NOT NULL AND iba.isDelete = 0 AND iba.organizationNo = :orgNo
                                " . $conditionIBAPupil . "
                                ) as tbl ORDER BY isPass DESC , eikenLevelId ASC ,tbl.ibaLevelId ASC
                                )
                                as tba1 GROUP BY tba1.pupilId, tba1.historyYear
            ) as history ".$conditionName."
            ";

        $sql .= " ORDER BY history.historyYear DESC, history.schoolYearName ASC, history.className ASC, history.pupilNo ASC, history.pupilName ASC";
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('\Application\Entity\EikenExamHistory', 'history');
        $rsm->addFieldResult('history', 'pupilId', 'pupilId');
        $rsm->addFieldResult('history', 'eikenLevelId', 'eikenLevelId');
        $rsm->addFieldResult('history', 'ibaLevelId', 'ibaLevelId');
        $rsm->addFieldResult('history', 'historyYear', 'historyYear');
        $rsm->addFieldResult('history', 'schoolYearName', 'schoolYearName');
        $rsm->addFieldResult('history', 'schoolYearCode', 'schoolYearCode');
        $rsm->addFieldResult('history', 'className', 'className');
        $rsm->addFieldResult('history', 'classCode', 'classCode');
        $rsm->addFieldResult('history', 'pupilNo', 'pupilNo');
        $rsm->addFieldResult('history', 'pupilName', 'pupilName');
        $rsm->addFieldResult('history', 'type', 'type');
        $rsm->addFieldResult('history', 'id', 'id');
        $rsm->addFieldResult('history', 'ibaId', 'ibaId');
        $rsm->addFieldResult('history', 'testType', 'testType');
        $rsm->addFieldResult('history', 'total', 'total');

        $query = $em->createNativeQuery($sql, $rsm);

        $query->setParameter(":orgNo", $organizationNo);

        if (isset($keyword['year']) && $keyword['year'] != '') {
            $query->setParameter(":historyYear", $keyword['year']);
        }
        if (isset($keyword['orgSchoolYear']) && $keyword['orgSchoolYear'] != '') {
            $query->setParameter(":schoolYearName", $keyword['orgSchoolYear']);
        }
        if (isset($keyword['classj']) && $keyword['classj'] != '') {
            $query->setParameter(":className", $keyword['classj']);
        }
        if (isset($keyword['name']) && $keyword['name'] != '') {
            $query->setParameter(":pupilName", '%' . $keyword['name'] . '%');
        }

        $paginator = new DTPaginator($query);
        return $paginator;
    }

    public function getEikenLevelByPupilId($listPupilId, $orgNo, $year)
    {
        
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenTestResult.pupilId as pupilId, MIN(eikenTestResult.eikenLevelId) as eikenLevel')
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult', 'eikenTestResult.pupilId')
            ->where('eikenTestResult.pupilId IN (:listPupil)')
            ->andWhere('eikenTestResult.organizationNo = :orgNo')
            ->andWhere('eikenTestResult.year = :year')
            ->andWhere('eikenTestResult.isDelete = 0')
            ->groupBy('eikenTestResult.pupilId')
            ->setParameter('listPupil', $listPupilId)
            ->setParameter('orgNo', $orgNo)
            ->setParameter('year', $year);
        $query = $qb->getQuery();
        return $query->getArrayResult();
    }

    public function getListIdEikenTestResult($kai, $year, $organizationNo)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('eikenTestResult.id')
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->where('eikenTestResult.organizationNo =:orgNo')
            ->andWhere('eikenTestResult.kai =:kai')
            ->andWhere('eikenTestResult.year =:year')
            ->setParameter(':orgNo', $organizationNo)
            ->setParameter(':kai', $kai)
            ->setParameter(':year', $year);

        return $qb->getQuery()->getResult();
    }

    /*
     * tuanNV21
     */
    public function getTotalIsPass($data)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(eikenTestResult.id) as totalIsPass,eikenTestResult.year as Year,eikenTestResult.orgSchoolYearId as orgSchoolYearId')
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->where('eikenTestResult.eikenLevelId <= :eikenId')
            ->andWhere('eikenTestResult.year >= :yearMin')
            ->andWhere('eikenTestResult.year <= :yearMax')
            ->andWhere('eikenTestResult.isPass =1')
            ->andWhere('eikenTestResult.organizationNo = :orgNo')
            ->andWhere('eikenTestResult.isDelete =0')
            ->groupBy('eikenTestResult.year,eikenTestResult.orgSchoolYearId')
            ->setParameter('yearMax', (int) $data['yearMax'])
            ->setParameter('yearMin', (int) $data['yearMin'])
            ->setParameter('orgNo', (int) $data['orgNo'])
            ->setParameter(':eikenId', (int) $data['eikenLevelId']);
        $query = $qb->getQuery();
        return $query->getArrayResult();
    }

    /**
     *
     * @author taivh
     * @param number $orgNo
     * @param string $year
     * @return number
     */
	public function getMaxKai($year = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select("MAX(etresult.kai) as kai")
            ->from('\Application\Entity\EikenTestResult', 'etresult')
            ->where('etresult.year = :year')
            ->setParameter(":year", $year)            
            ->andWhere('etresult.isPass = 1')
            ->andWhere('etresult.isDelete = 0')
            ->groupBy('etresult.year');
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     *
     * @author taivh
     *         Lấy tổng số người đạt kyu mục ti có mặt tại trường tất cả các năm.
     */
    public function getTotalStudentWasInSchool($orgId, $orgNo, $eikenLevelId, $year, $numYear, $kai)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select("schYear.id, eikenTestResult.year as Year, count(eikenTestResult.id) as Total, schYear.name as Name")
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->join('eikenTestResult.orgSchoolYear', 'orgSchYear')
            ->join('orgSchYear.schoolYear', 'schYear')
            ->where('eikenTestResult.organizationNo = :orgNo')
            ->andWhere('orgSchYear.organizationId = :orgId')
            ->andWhere('eikenTestResult.year > :year1')
            ->andWhere('eikenTestResult.year <= :year2')
            ->setParameter(':orgNo', $orgNo)
            ->setParameter(':orgId', $orgId)
            ->setParameter(':year1', ($year - $numYear))
            ->setParameter(':year2', $year)
            ->andWhere('orgSchYear.isDelete = 0')
            ->andWhere('schYear.isDelete = 0')
            ->andWhere('eikenTestResult.isPass = 1')
            ->andWhere('eikenTestResult.isDelete = 0')
            ->addGroupBy('Year', 'schYear.name')
            ->orderBy('Year', 'DESC')
            ->addOrderBy('schYear.id', 'ASC');

        if ($eikenLevelId) {
            $qb->andWhere('eikenTestResult.eikenLevelId <= :eikenLevelId')->setParameter(':eikenLevelId', $eikenLevelId);
        }
        if ($kai != 4) {
            $qb->andWhere('eikenTestResult.kai = :kai')->setParameter(':kai', $kai);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     *
     * @author minhbn1<minhbn1@fsoft.com.vn>
     *
     *         get edge(least and greatest) of all 4 skill: Reading, Listenning, Speaking, Writing in EikenTestResult
     *
     * @param type $kai
     * @param type $year
     * @param type $type
     * @param type $objectId
     * @param type $edge
     * @return int
     */
    function getEikenEdgeScore($kai = 0, $year = 0, $type = '', $objectId = 0, $edge = '')
    {
        $em = $this->getEntityManager();
        $objectId = (int) $objectId;
        $select = '';
        $score = 0;
        switch ($edge) {
            case self::SCORE_EDGE_LEAST:
                $select = 'MIN(EikenTestResult.eikenCSETotal) ';
                break;
            case self::SCORE_EDGE_GREATEST:
                $select = 'MAX(EikenTestResult.eikenCSETotal) ';
                break;
        }
        //
        if (! $select)
            return $score;
            //
        $qb = $em->createQueryBuilder();
        $qb->select($select)->from('\Application\Entity\EikenTestResult', 'EikenTestResult');
        switch ($type) {
            case self::SCORE_TYPE_NAME_ORG:
                $qb->where('Organization.id=' . $objectId)->innerJoin('\Application\Entity\Organization', 'Organization', \Doctrine\ORM\Query\Expr\Join::WITH, 'EikenTestResult.organizationNo = Organization.organizationNo');
                break;
            case self::SCORE_TYPE_NAME_ORGSCHOOLYEAR:
                $qb->where('EikenTestResult.orgSchoolYearId=' . $objectId);
                break;
            case self::SCORE_TYPE_NAME_CLASS:
                $qb->where('EikenTestResult.classId=' . $objectId);
                break;
        }
        $qb->andWhere('EikenTestResult.isDelete=:isDelete')
            ->andWhere('EikenTestResult.kai=:kai')
            ->andWhere('EikenTestResult.year=:year')
            ->andWhere('EikenTestResult.eikenCSETotal > 0');
        $qb->setParameter(':isDelete', self::NOT_DELETE_VALUE);
        $qb->setParameter(':kai', $kai);
        $qb->setParameter(':year', $year);
        $query = $qb->getQuery();
        $score = $query->getSingleScalarResult();
        return (int) $score;
    }

    /**
     *
     * @author minhbn1<minhbn1@fsoft.com.vn>
     *
     *         UC24 get TestDate
     *
     * @param type $organizationId
     * @param type $kai
     */
    function getTestDate($kai = 0, $organizationId = 0, $year = 0)
    {
        $organizationId = (int) $organizationId;
        $kai = (int) $kai;
        $year = (int) $year;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('MIN(EikenTestResult.certificationDate)')
            ->from('\Application\Entity\EikenTestResult', 'EikenTestResult')
            ->innerJoin('\Application\Entity\Organization', 'Organization', \Doctrine\ORM\Query\Expr\Join::WITH, 'EikenTestResult.organizationNo = Organization.organizationNo')
            ->where('Organization.id=:organizationId')
            ->andWhere('EikenTestResult.kai=:kai')
            ->andWhere('EikenTestResult.year=:year')
            ->andWhere('EikenTestResult.certificationDate IS NOT NULL');
        $qb->setParameter(':year', $year);
        $qb->setParameter(':kai', $kai);
        $qb->setParameter(':organizationId', $organizationId);
        $query = $qb->getQuery();
        $date = $query->getSingleScalarResult();
        if (! $date)
            $date = '';
        return $date;
    }

    /**
     *
     * @author taivh
     *         Lấy danh sách người đạt kyu mục tieu có mặt tại trường tất cả các năm.
     */
    public function getListPassBySchoolYearId($orgId, $orgNo, $year, $kai, $eikenLevelId, $key, $ord)
    {
         $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select("eikenTestResult.schoolYearName as orgSchoolYear, eikenTestResult.className as className,
                    eikenTestResult.pupilNo as pupilNo, eikenTestResult.tempNameKanji as pupilName,
                    eikenLevel.levelName as levelName, eikenTestResult.year as year, eikenTestResult.kai as kai,CASE  
                        WHEN eikenTestResult.mappingStatus = 0 THEN '未確定'
                        WHEN eikenTestResult.mappingStatus = 1 THEN '確定済'
                        ELSE ''
                    END as mappingStatus")
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->innerJoin('\Application\Entity\OrgSchoolYear', 'orgSchYear', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'eikenTestResult.orgSchoolYearId = orgSchYear.id')
            ->innerJoin('\Application\Entity\SchoolYear', 'schYear', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'orgSchYear.schoolYearId = schYear.id')
            ->innerJoin('\Application\Entity\EikenLevel', 'eikenLevel', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'eikenTestResult.eikenLevelId = eikenLevel.id')
            ->where('eikenTestResult.organizationNo = :orgNo')
            ->andWhere('orgSchYear.organizationId = :orgId')
            ->andWhere('eikenTestResult.year = :year')
            ->setParameter(':orgNo', $orgNo)
            ->setParameter(':orgId', $orgId)
            ->setParameter(':year', $year)
            ->andWhere('orgSchYear.isDelete = 0')
            ->andWhere('schYear.isDelete = 0')
            ->andWhere('eikenTestResult.isPass = 1')
            ->andWhere('eikenTestResult.isDelete = 0');
        if ($kai != 4) {
            $qb->andWhere('eikenTestResult.kai = :kai')->setParameter(':kai', $kai);
        }
        if ($eikenLevelId != 0) {
            $qb->andWhere('eikenTestResult.eikenLevelId = :eikenLevelId')->setParameter(':eikenLevelId', $eikenLevelId);
        }

        if ($key == 'col0') {
            $qb->orderBy('schYear.id', 'asc')
                ->addOrderBy($this->sortFields['col2'], 'asc')
                ->addOrderBy($this->sortFields['col3'], 'asc');
        } else {
            $qb->orderBy($this->sortFields[$key], $ord == 'a' ? 'asc' : 'desc');
        }
        $paginator = new DTPaginator($qb, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    /**
     * function get number people by Year And Time(kai)
     *
     * @author DucNA
     * @param $orgNo string
     * @param $year int
     *
     * @return data of view
     *         Author Modified Start date End date
     *         DucNA Creates 2015-07-26 2015-07-26
     */
    public function getCountPeopleByYearAndTime($orgNo, $year = null)
    {
        if ($year == null)
            $year = date("Y");
        $year2 = (int) $year - 2;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenTestResult.year, eikenTestResult.kai, COUNT(eikenTestResult.id)')
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->where('eikenTestResult.year between :year2 and :year')
            ->andWhere('eikenTestResult.organizationNo = :org_no ')
            ->setParameter(':year2', $year2)
            ->setParameter(':year', $year)
            ->setParameter(':org_no', $orgNo)
            ->andWhere('eikenTestResult.isDelete = 0 ')
            //->andWhere('eikenTestResult.attendFlag = 1 ')         
            ->groupBy('eikenTestResult.year, eikenTestResult.kai')
            ->orderBy('eikenTestResult.year', 'desc');
        $query = $qb->getQuery();
        return $query->getArrayResult();
    }

    /**
     * function get number people by Year And Time(kai) detail code and orgSchoolYearId
     *
     * @author DucNA
     * @param $orgNo string
     * @param $year int
     * @param $kai int
     *
     * @return data of view
     *         Author Modified Start date End date
     *         DucNA Creates 2015-07-27 2015-07-28
     */
    public function getCountDetailCodeByYearAndTime($orgNo, $year, $kai)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
//        update function for : #GNCCNCJDM-304
        $qb->select("eikenTestResult.year, eikenTestResult.kai, COUNT(eikenTestResult.id) as countElement
                , eikenTestResult.schoolYearCode as schoolYearCode
                , eikenTestResult.className, eikenTestResult.mappingStatus
                , eikenTestResult.schoolClassification as schoolClassification, eikenTestResult.schoolYearName as schoolYearName
            ")
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->where('eikenTestResult.isDelete = 0')
            ->andWhere('eikenTestResult.organizationNo = :org_No')
            ->andWhere('eikenTestResult.year = :year')
            ->setParameter(':org_No', $orgNo)
            ->setParameter(':year', $year);

        if ($kai == 'all') {
            $qb->groupBy('schoolYearCode,schoolClassification');
        } else {
            $qb->andWhere('eikenTestResult.kai = :kai ')
                ->setParameter(':kai', $kai)
                ->groupBy('schoolYearCode, eikenTestResult.kai','schoolClassification');
        }
        $qb->orderBy('schoolClassification', 'ASC');
        $qb->addOrderBy('schoolYearCode', 'ASC');

        $query = $qb->getQuery();

        return $query->getArrayResult();
    }

    /**
     * function get list detail exam result
     *
     * @author DucNA
     * @param $orgNo string
     * @param $year int
     * @param $kai int
     *
     * @param null $schoolYearName
     * @param null $typeDetail
     * @param $schoolClassification string
     * @param $schoolYearCode string
     * @return list data of table ActualExamResult
     *         Author Modified Start date End date
     * DucNA Creates 2015-07-29 2015-07-29
     */
    public function getDataDetailTableC($orgNo, $year, $kai, $schoolYearCode = null, $schoolClassification = null, $typeDetail = null)
    {
        
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select("eikenTestResult.year, eikenTestResult.kai, eikenTestResult.className,
            CASE
                WHEN eikenTestResult.pupilId is NULL THEN eikenTestResult.pupilNo
                ELSE eikenTestResult.tempPupilNo
            END as pupilNo,
            eikenTestResult.schoolClassification,
            eikenTestResult.schoolYearName,
            eikenTestResult.primaryPassFailFlag,
            eikenTestResult.primaryFailureLevel,
            eikenTestResult.secondPassFailFlag,
            eikenTestResult.secondUnacceptableLevel,
            eikenTestResult.oneExemptionFlag,
            eikenTestResult.tempNameKanji, eikenTestResult.eikenLevelId,
            CASE  
                WHEN eikenTestResult.mappingStatus = 0 THEN '未確定'
                WHEN eikenTestResult.mappingStatus = 1 THEN '確定済'
                ELSE ''
            END as confirmmappingStatus")
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->where('eikenTestResult.isDelete = 0')
            ->andWhere('eikenTestResult.organizationNo = :org_no')
            ->andWhere('eikenTestResult.year = :year')
            ->setParameter(':org_no', $orgNo)
            ->setParameter(':year', $year)
           // ->andWhere('eikenTestResult.attendFlag = 1')
            ->addOrderBy('eikenTestResult.schoolYearName', 'ASC')
            ->addOrderBy('eikenTestResult.className', 'ASC')
            ->addOrderBy('eikenTestResult.pupilNo', 'ASC');
        if ($kai !== 'all') {
            $qb->andWhere('eikenTestResult.kai = :kai ')->setParameter(':kai', $kai);
        }

        if (!empty($schoolYearCode) && $schoolYearCode === 'other') {
        $qb->andWhere('eikenTestResult.schoolYearCode IS NULL');
        }elseif(!empty($schoolYearCode) && $schoolYearCode !== 'all'){
            $qb->andWhere('eikenTestResult.schoolYearCode = :schoolYearCode')
                ->setParameter(':schoolYearCode', $schoolYearCode);
        }
        
        if($schoolClassification != '' && $schoolClassification != null){
            $qb->andWhere('eikenTestResult.schoolClassification = :schoolClassification')
                ->setParameter(':schoolClassification', $schoolClassification);
        }

        if ($typeDetail == 'B') {
            $qb->andWhere('eikenTestResult.isPass = 1');
        }       
        
        $paginator = new DTPaginator($qb, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    /**
     * DucNA17
     * get data Graph
     *
     * @return array
     */
    public function getDataGraphB($orgNo, $year, $type)
    {
        $year2 = (int) $year - 2;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenTestResult.year, COUNT(eikenTestResult.id) as totalPassed,  eikenLv.id, eikenLv.levelName')
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->leftjoin('\Application\Entity\EikenLevel', 'eikenLv', 'WITH', 'eikenTestResult.eikenLevelId = eikenLv.id')
            ->where('eikenTestResult.year between :year2 and :year')
            ->andWhere('eikenTestResult.organizationNo = :org_no')
            ->setParameter(':year', $year)
            ->setParameter(':year2', $year2)
            ->setParameter(':org_no', $orgNo)
            ->andWhere('eikenTestResult.isDelete = 0 ')
            ->groupBy('eikenTestResult.year, eikenLv.id')
            ->orderBy('eikenTestResult.year', 'DESC')
            ->addOrderBy('eikenLv.id', 'DESC');
        if ($type == 'pass') {
            $qb->andWhere('eikenTestResult.isPass = 1');
        }
        $query = $qb->getQuery();
        return $query->getArrayResult();
    }

    /*
     * DucNA17
     * get data of Class by OrgSchoolYear and OrgClassification
     */
    public function getDetailClassB($orgNo, $year, $kai,  $isPass = 1)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenTestResult.year, eikenTestResult.kai, COUNT(eikenTestResult.id) as countElement,
            eikenTestResult.schoolClassification as schoolClassification,
            eikenTestResult.mappingStatus,
            eikenTestResult.schoolYearCode as schoolYearCode,
            eikenTestResult.className,eikenTestResult.schoolYearName as schoolYearName')
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->where('eikenTestResult.isDelete = 0')
            ->andWhere('eikenTestResult.year = :year')
            ->andWhere('eikenTestResult.organizationNo = :org_no')
            ->setParameter(':year', $year)
            ->setParameter(':org_no', $orgNo);

        if ($isPass == 1)
            $qb->andWhere('eikenTestResult.isPass = 1');

        if ($kai == 'all') {
            $qb->groupBy('schoolYearCode ,schoolClassification');
        } else {
            $qb->andWhere('eikenTestResult.kai = :kai ')->setParameter(':kai', $kai);
            $qb->groupBy('schoolYearCode ,schoolClassification');
        }
        $qb->orderBy('schoolClassification', 'ASC');
        $qb->addOrderBy('schoolYearCode', 'ASC');

        $query = $qb->getQuery();

        return $query->getArrayResult();
    }

    /**
     *
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * @param type $options
     * @return number
     */
    function countRecords($options = array())
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('count(EikenTestResult)')
            ->from('\Application\Entity\EikenTestResult', 'EikenTestResult')
            ->where('EikenTestResult.isDelete=:isDelete');
        $qb->setParameter(':isDelete', self::NOT_DELETE_VALUE);
        //
        if (isset($options['join']) && count($options['join'])) {
            foreach ($options['join'] as $join) {
                $qb->innerJoin($join['entity'], $join['alias'], $join['expr'], $join['condition']);
            }
        }
        if (isset($options['condition']) && $options['condition']) {
            $qb->andWhere($options['condition']);
        }
        if (isset($options['year']) && $options['year']) {
            $qb->andWhere('EikenTestResult.year=:year');
            $qb->setParameter(':year', $options['year']);
        }
        if (isset($options['eikenLevelId']) && $options['eikenLevelId']) {
            $qb->andWhere('EikenTestResult.eikenLevelId=:eikenLevelId');
            $qb->setParameter(':eikenLevelId', $options['eikenLevelId']);
        }
        if (isset($options['classId']) && $options['classId']) {
            $qb->andWhere('EikenTestResult.classId=:classId');
            $qb->setParameter(':classId', $options['classId']);
        }
        if (isset($options['isPass'])) {
            $qb->andWhere('EikenTestResult.isPass=:isPass');
            $qb->setParameter(':isPass', $options['isPass']);
        }
        if (isset($options['orgSchoolYearId']) && $options['orgSchoolYearId']) {
            $qb->andWhere('EikenTestResult.orgSchoolYearId=:orgSchoolYearId');
            $qb->setParameter(':orgSchoolYearId', $options['orgSchoolYearId']);
        }
        if (isset($options['kai']) && $options['kai']) {
            $qb->andWhere('EikenTestResult.kai=:kai');
            $qb->setParameter(':kai', $options['kai']);
        }

        $query = $qb->getQuery();
        $count = $query->getSingleScalarResult();
        return (int) $count;
    }

    /**
     *
     * @author minhbn1<minhbn1@fsoft.com.vn>
     *         Sum CSE score as: speaking, listening, reading, writing
     * @param type $options
     * @return int
     */
    function sumCSEScore($options = array())
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $field = isset($options['field']) ? $options['field'] : '';
        $sum = 0;
        if (! $field)
            return $sum;
        $qb->select('SUM(EikenTestResult.' . $field . ')')
            ->from('\Application\Entity\EikenTestResult', 'EikenTestResult')
            ->where('EikenTestResult.isDelete=:isDelete');
        $qb->setParameter(':isDelete', self::NOT_DELETE_VALUE);
        //
        if (isset($options['join']) && count($options['join'])) {
            foreach ($options['join'] as $join) {
                $qb->innerJoin($join['entity'], $join['alias'], $join['expr'], $join['condition']);
            }
        }
        if (isset($options['condition']) && $options['condition']) {
            $qb->andWhere($options['condition']);
        }
        if (isset($options['year']) && $options['year']) {
            $qb->andWhere('EikenTestResult.year=:year');
            $qb->setParameter(':year', $options['year']);
        }
        if (isset($options['classId']) && $options['classId']) {
            $qb->andWhere('EikenTestResult.classId=:classId');
            $qb->setParameter(':classId', $options['classId']);
        }
        if (isset($options['orgSchoolYearId']) && $options['orgSchoolYearId']) {
            $qb->andWhere('EikenTestResult.orgSchoolYearId=:orgSchoolYearId');
            $qb->setParameter(':orgSchoolYearId', $options['orgSchoolYearId']);
        }
        if (isset($options['kai']) && $options['kai']) {
            $qb->andWhere('EikenTestResult.kai=:kai');
            $qb->setParameter(':kai', $options['kai']);
        }
        $query = $qb->getQuery();
        $sum = $query->getSingleScalarResult();
        return (int) $sum;
    }

    /**
     *
     * @author minhbn1 <minhbn1@fsoft.com.vn>
     * @param type $organizationId
     * @param type $year
     */
    function getDistinctKaiOfOrg($organizationId = 0, $year = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('distinct EikenTestResult.kai')
            ->from('\Application\Entity\EikenTestResult', 'EikenTestResult')
            ->innerJoin('\Application\Entity\Organization', 'Organization', \Doctrine\ORM\Query\Expr\Join::WITH, 'EikenTestResult.organizationNo = Organization.organizationNo')
            ->where('Organization.id = :organizationId')
            ->andWhere('EikenTestResult.year = :year')
            ->andWhere('EikenTestResult.isDelete=:isDelete')
            ->andWhere('EikenTestResult.kai > 0')
            ->setParameter(':organizationId', intval($organizationId))
            ->setParameter(':year', intval($year))
            ->setParameter(':isDelete', self::NOT_DELETE_VALUE)
            ->orderBy('EikenTestResult.kai', 'ASC');
        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     *
     * @author taivh
     *         Lấy danh sách người đạt kyu mục tieu có mặt tại trường theo năm
     */
    public function getListPupilByYear($orgId, $orgNo, $year, $kai, $kyu)
    {
       $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("eikenTestResult.schoolYearName as orgSchoolYear, eikenTestResult.className as className,
                    eikenTestResult.pupilNo as pupilNo, eikenTestResult.tempNameKanji as pupilName,
                    eikenLevel.levelName as levelName, eikenTestResult.year as year, eikenTestResult.kai as kai")
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->innerJoin('\Application\Entity\OrgSchoolYear', 'orgSchYear', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'eikenTestResult.orgSchoolYearId = orgSchYear.id')
            ->innerJoin('\Application\Entity\SchoolYear', 'schYear', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'orgSchYear.schoolYearId = schYear.id')
            ->innerJoin('\Application\Entity\EikenLevel', 'eikenLevel', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'eikenTestResult.eikenLevelId = eikenLevel.id')
            ->where('eikenTestResult.organizationNo = :orgNo')
            ->andWhere('orgSchYear.organizationId = :orgId')
            ->andWhere('eikenTestResult.year = :year')
            ->setParameter(':orgNo', $orgNo)
            ->setParameter(':orgId', $orgId)
            ->setParameter(':year', $year)
            ->andWhere('orgSchYear.isDelete = 0')
            ->andWhere('schYear.isDelete = 0')
            ->andWhere('eikenTestResult.isPass = 1')
            ->andWhere('eikenTestResult.isDelete = 0')
            ->orderBy('schYear.id', 'asc')
            ->addOrderBy('className', 'asc')
            ->addOrderBy('pupilNo', 'asc');

        if ($kai != 4) {
            $qb->andWhere('eikenTestResult.kai = :kai')->setParameter(':kai', $kai);
        }
        if ($kyu != 0) {
            $qb->andWhere('eikenTestResult.eikenLevelId = :eikenLevelId')->setParameter(':eikenLevelId', $kyu);
        }
        return $qb->getQuery()->getArrayResult();
    }

    /**
     *
     * @author taivh
     *         Lấy tổng số người đỗ theo từng năm và từng Lần thi
     */
    public function getSumStudent($orgNo, $eikenLevelId, $orgSchoolYearId, $year, $kai)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select("count(eikenTestResult.id) as Total")
            ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->where('eikenTestResult.organizationNo = :orgNo')
            ->andWhere('eikenTestResult.eikenLevelId <= :eikenLevelId')
            ->andWhere('eikenTestResult.orgSchoolYearId = :orgSchoolYearId')
            ->andWhere('eikenTestResult.year = :year')
            ->andWhere('eikenTestResult.kai = :kai')
            ->setParameter(':orgNo', $orgNo)
            ->setParameter(':eikenLevelId', $eikenLevelId)
            ->setParameter(':orgSchoolYearId', $orgSchoolYearId)
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai)
            ->andWhere('eikenTestResult.isPass = 1')
            ->andWhere('eikenTestResult.isDelete = 0');
        $query = $qb->getQuery();
        try{
            return $query->getSingleScalarResult();
        } catch (\Exception $e){
            return 0;
        }
    }

    public function updateMultipleRowsWithEachId($eikenTestResults){
        $setOrgSchoolYearIdSql = '';
        $setOrgSchoolYearNameSql = '';
        $setTempSchoolYearNameSql = '';
        $setOrgSchoolYearCodeSql = '';
        $setClassId = '';
        $setClassName = '';
        $setTempClassName = '';
        $setNameKanji = '';
        $setTempNameKanji = '';
        $setNameKana = '';
        $setPupilId = '';
        $setPupilNumber = '';
        $setBirthday = '';
        $setStatus = '';
        
        if(!$eikenTestResults){
            return false;
        }

        foreach($eikenTestResults as $key=>$value){
            $listId[] = $key;
            $setOrgSchoolYearIdSql .= $value['orgSchoolYearId'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "" . intval($value['orgSchoolYearId']) . "") : "WHEN " . $key .  " THEN NULL ";
            $setOrgSchoolYearNameSql .= $value['orgSchoolYearName'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "'" . mysql_escape_string($value['orgSchoolYearName']) . "'") : "WHEN " . $key .  " THEN NULL ";
            $setTempSchoolYearNameSql .= !empty($value['isDeleteMapping']) && $value['isDeleteMapping'] == 1 ? "WHEN " . $key .  " THEN NULL " : $setOrgSchoolYearNameSql;
            $setOrgSchoolYearCodeSql .= $value['orgSchoolYearCode'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "" . intval($value['orgSchoolYearCode']) . "") : "WHEN " . $key .  " THEN NULL ";
            $setClassId .= $value['classId'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "" . intval($value['classId']) . "") : "WHEN " . $key .  " THEN NULL ";
            $setClassName .= $value['className'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "'" . mysql_escape_string($value['className']) . "'") : "WHEN " . $key .  " THEN NULL ";
            $setTempClassName .= !empty($value['isDeleteMapping']) && $value['isDeleteMapping'] == 1 ? "WHEN " . $key .  " THEN NULL " : $setClassName;
            $setNameKanji .= $value['nameKanji'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "'" . mysql_escape_string($value['nameKanji']) . "'") : "WHEN " . $key .  " THEN NULL ";
            $setTempNameKanji .= !empty($value['isDeleteMapping']) && $value['isDeleteMapping'] == 1 ? "WHEN " . $key .  " THEN NULL " : $setNameKanji;
            $setNameKana .= $value['nameKana'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "'" . mysql_escape_string($value['nameKana']) . "'") : "WHEN " . $key .  " THEN NULL ";
            $setPupilId .= $value['pupilId'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "" . intval($value['pupilId']) . "") : "WHEN " . $key .  " THEN NULL ";
            $setPupilNumber .= $value['pupilNumber'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "" . intval($value['pupilNumber']) . ""): "WHEN " . $key .  " THEN NULL ";
            $setBirthday .= $value['birthday'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "'" . mysql_escape_string($value['birthday']) . "'") : "WHEN " . $key . " THEN NULL ";
            $setStatus .= isset($value['isExitSchoolYearCode']) && $value['isExitSchoolYearCode'] === false ? "WHEN " . $key . " THEN 'other' " : "WHEN " . $key . " THEN 'DRAFT' ";
        }
        
        $sql = '
            UPDATE EikenTestResult SET
                TempOrgSchoolYearId = CASE id ' . $setOrgSchoolYearIdSql . ' END,
                OrgSchoolYearId = CASE id ' . $setOrgSchoolYearIdSql . ' END,
                TempSchoolYearName = CASE id ' . $setTempSchoolYearNameSql . ' END,
                SchoolYearName = CASE id ' . $setOrgSchoolYearNameSql . ' END,
                TempDantaiSchoolYearCode = CASE id ' . $setOrgSchoolYearCodeSql . ' END,
                DantaiSchoolYearCode = CASE id ' . $setOrgSchoolYearCodeSql . ' END,
                TempClassId = CASE id ' . $setClassId . ' END,
                ClassId = CASE id ' . $setClassId . ' END,
                TempClassName = CASE id ' . $setTempClassName . ' END,
                ClassName = CASE id ' . $setClassName . ' END,
                PreTempNameKanji = CASE id ' . $setTempNameKanji . ' END,
                TempNameKanji = CASE id ' . $setNameKanji . ' END,
                TempNameKana = CASE id ' . $setNameKana . ' END,
                TempPupilId = CASE id ' . $setPupilId . ' END,
                PupilId = CASE id ' . $setPupilId . ' END,
                TempPupilNo = CASE id ' . $setPupilNumber . ' END,
                TempBirthday = CASE id ' . $setBirthday . ' END,
                Status = CASE id ' . $setStatus . ' END
            WHERE id IN (' . implode(',', $listId) . ')
        ';
        
        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeUpdate($sql);
        return $result;
    }

    public function getEikenResultsDetails($year, $kai, $organizationNo, $orgSchoolYearId = '', $classId = '', $namePupil = '', $mappingStatus = '')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e.id,
            e.pupilName,
            e.nameKanji,
            e.tempNameKanji,
            e.preTempNameKanji,
            e.nameKana,
            e.tempNameKana,
            e.schoolYearCode,
            e.eikenLevelId,
            pupil.year,
            e.kai,
            e.tempPupilNo,
            e.pupilId,
            e.tempPupilId,
            e.schoolYearName,
            e.tempSchoolYearName,
            e.className,
            e.tempClassName,
            e.birthday,
            e.tempBirthday,
            e.primaryPassFailFlag,
            e.oneExemptionFlag,
            e.secondPassFailFlag,
            e.primaryFailureLevel,
            e.status,
            e.mappingStatus')
            ->from('\Application\Entity\EikenTestResult', 'e')
            ->leftJoin('\Application\Entity\Pupil', 'pupil', \Doctrine\ORM\Query\Expr\Join::WITH, 'e.pupilId = pupil.id')
            ->where('e.isDelete = 0')
            ->andWhere('e.organizationNo =:orgNo')
            ->andWhere('e.year =:year')
            ->andWhere('e.kai =:kai')
            ->setParameter(':orgNo', $organizationNo)
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai)
            ->orderBy('e.nameKana','ASC')
            ->addOrderBy('e.birthday','ASC');
        if ($orgSchoolYearId) {
            $qb->andWhere('e.orgSchoolYearId =:orgSchoolYearId')
                ->setParameter(':orgSchoolYearId', $orgSchoolYearId);
        }
        if ($mappingStatus != '') {
            $qb->andWhere('e.mappingStatus =:mappingStatus')
                ->setParameter(':mappingStatus', $mappingStatus);
        }
        if ($classId) {
            $qb->andWhere('e.classId =:class')
                ->setParameter(':class', $classId);
        }
        if (!empty(trim($namePupil))) {
            $qb->andWhere($qb->expr()->orX(
                    'e.tempNameKanji LIKE :namePupil',
                    'e.pupilName LIKE :namePupil'
                    )
            )->setParameter(':namePupil', "%" . trim($namePupil) . "%");
        }
        $paginator = new DTPaginator($qb, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    public function getTotalMappingStatus($year, $kai, $organizationNo)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e.mappingStatus, count(e.mappingStatus) AS total')
            ->from('\Application\Entity\EikenTestResult', 'e','e.mappingStatus')
            ->where('e.isDelete = 0')
            ->andWhere('e.organizationNo =:orgNo')
            ->andWhere('e.year =:year')
            ->andWhere('e.kai =:kai')
            ->setParameter(':orgNo', $organizationNo)
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai)
            ->groupBy('e.mappingStatus');

        return $qb->getQuery()->getArrayResult();
    }

    public function getConfirmStatus($organizationNo)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('CONCAT(e.year,e.kai,e.mappingStatus) as totalMap, e.id, e.year, e.kai, e.mappingStatus, count(e.mappingStatus) as total')
            ->from('\Application\Entity\EikenTestResult', 'e')
            ->where('e.isDelete = 0')
            ->andWhere('e.organizationNo =:orgNo')
            ->setParameter(':orgNo', $organizationNo)
            ->groupBy('e.mappingStatus')
            ->addGroupBy('e.year')
            ->addGroupBy('e.kai');

        return $qb->getQuery()->getArrayResult();
    }
    // DuocDD: get List Exam Result for export excel
    public function getListExamResult($orgNo, $year, $kai) 
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $query = $qb->select('eikenTestResult')
                ->from('\Application\Entity\EikenTestResult', 'eikenTestResult')
                ->where('eikenTestResult.organizationNo = :orgNo')
                ->andWhere('eikenTestResult.year = :year')
                ->andWhere('eikenTestResult.kai = :kai')
                ->andWhere('eikenTestResult.isDelete = 0')
                ->setParameter(':orgNo', $orgNo)
                ->setParameter(':year', (int) $year)
                ->setParameter(':kai', (int) $kai);
 
        $query->addOrderBy('eikenTestResult.schoolClassification', 'ASC');
        $query->addOrderBy('eikenTestResult.schoolYearName', 'ASC');
        $query->addOrderBy('eikenTestResult.className', 'ASC');
        $query->addOrderBy('eikenTestResult.nameKana', 'ASC');
        
        return $query->getQuery()->getArrayResult();
    }

    // DuocDD: get List Grade or class in exam-history-list page
    public function getListGradeClass($organizationNo, $type)
    {
        if (empty($type)) {
            $type = 'schoolYearName';
        }
        $where = 'className IS NOT NULL ';
        if ($type == 'schoolYearName') {
            $where = 'SchoolYearName IS NOT NULL';
        }
        $em = $this->getEntityManager();
        $sql = "SELECT * FROM(
                             SELECT etr.schoolYearName as schoolYearName, etr.className as className
                                     FROM EikenTestResult etr
                                     WHERE 
                                     etr.isDelete = 0 
                                     AND etr." . $where . "
                                     AND etr.organizationNo = :orgNo
                             UNION
                             SELECT itr.schoolYearName as schoolYearName, itr.className as className
 					FROM IBATestResult itr
 					WHERE 
                                         itr.isDelete = 0 
                                         AND itr." . $where . "
                                         AND itr.organizationNo = :orgNo
                             ) as lgc GROUP BY " . $type;

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('\Application\Entity\EikenExamHistory', 'lgc');
        $rsm->addFieldResult('lgc', $type, $type);

        $query = $em->createNativeQuery($sql, $rsm);

        $query->setParameter(":orgNo", $organizationNo);

        return $query->getArrayResult();
    }

    /**
     * Function import EikenTestResult into DB
     * @param $listEikenTestResult
     * @return bool|int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertOnDuplicateUpdateMultiple($listEikenTestResult)
    {
        if (empty($listEikenTestResult)) {
            return false;
        }

        $em = $this->getEntityManager();

        // create sql data for insert.
        $time = date(DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
        $sqlData = '';
        $sqlUpdate = '';
        foreach ($listEikenTestResult as $item) {
            $sqlData .= ', (';
            $sql = '';
            foreach ($item as $value) {
                $sql .= !is_a($value, 'DateTime') ? ($value === null) ? ', NULL' : ", '" . mysql_escape_string($value) . "'" : ", '" . mysql_escape_string($value->format(DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT)) . "'" ;
            }
            $sql = trim($sql, ", ");
            $sqlData .= $sql;

            // add common column: isDelete, InsertAt, updateAt
            $sqlData .= ", 0, '$time', '$time')";
        }
        $tableName = $em->getClassMetadata('Application\Entity\EikenTestResult')->getTableName();

        // create header and sql data for on duplicate, not update tempNameKanji
        $headers = array_keys($listEikenTestResult[0]);
        $headers = array_merge(
            $headers,
            array(
                'isDelete',
                'insertAt',
                'updateAt'
            )
        );
        foreach ($headers as $key => $value) {
            // lowercase first char
            $value = lcfirst($value);
            $headers[$key] = $value;
            // on duplicate, not update tempNameKanji
            $exceptedColumn = array('tempNameKanji', 'insertAt','className', 'schoolYearName','mappingStatus');
            $sqlUpdate .= (!in_array($value, $exceptedColumn)) ? "$tableName.$value = VALUES($value), " : '';
        }
        $sqlData = trim($sqlData, ',');
        $sqlUpdate = trim($sqlUpdate, ', ');

        // create sql columns.
        $sqlColumn = implode(",", $headers);


        // create insert sql from data and columns.
        $sql = 'INSERT INTO ' . $tableName . ' (' . $sqlColumn . ') VALUES ' . $sqlData
            . ' ON DUPLICATE KEY UPDATE '
            . $sqlUpdate
        ;

        return $em->getConnection()->executeUpdate($sql);
    }

    public function updateTempValueAfterImport($organizationNo, $year, $kai){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('\Application\Entity\EikenTestResult', 'eikenTestResult')
            ->set('eikenTestResult.tempNameKanji', 'eikenTestResult.pupilName')
            ->set('eikenTestResult.className', 'eikenTestResult.classCode')
            ->set('eikenTestResult.schoolYearName', 'eikenTestResult.preSchoolYearName')
            ->where('eikenTestResult.pupilId IS NULL')
            ->andWhere('eikenTestResult.organizationNo = :orgNo')
            ->andWhere('eikenTestResult.year = :year')
            ->andWhere('eikenTestResult.kai = :kai')
            ->andWhere('eikenTestResult.isDelete = 0')
            ->setParameter(':orgNo', $organizationNo)
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai);
        $qb->getQuery()->execute();
    }
    
    public function updateIsMappedWidthIds($ids) {
        
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('\Application\Entity\EikenTestResult', 'eikenTestResult')
                ->set('eikenTestResult.isMapped', 1)
                ->where('eikenTestResult.id IN ('.implode(',', $ids).')');
        $qb->getQuery()->execute();
    }

}
