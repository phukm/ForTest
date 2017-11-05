<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\IBATestResult;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Eiken\Helper\NativePaginator as DTPaginator;
use Eiken\Helper\NativePaginator;
use Doctrine\ORM\Query\ResultSetMapping;
use Dantai\Utility\DateHelper;

class IBATestResultRepository extends EntityRepository {

    const SCORE_TYPE_NAME_ORG = 'org';
    const SCORE_TYPE_NAME_ORGSCHOOLYEAR = 'orgSchoolYear';
    const SCORE_TYPE_NAME_CLASS = 'class';
    const SCORE_EDGE_LEAST = 'least';
    const SCORE_EDGE_GREATEST = 'greatest';
    const NOT_DELETE_VALUE = 0;

    public function checkResultIbaTestResultPupil($pupilListId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('i.id,i.pupilId,i.nameKanji as pupilName')
            ->from('Application\Entity\IBATestResult', 'i', 'i.pupilId')
            ->where('i.pupilId in (:pupilListId)')
            ->andWhere('i.mappingStatus = 1')
            ->setParameter(':pupilListId', $pupilListId);

        return $qb->getQuery()->getArrayResult();
    }

    public function getDataResultLastestByPupilId($pupilId) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('ibaTestResult.examDate,ibaTestResult.eikenLevelId')
                ->from('\Application\Entity\IBATestResult', 'ibaTestResult', 'ibaTestResult.id')
                ->where('ibaTestResult.pupilId = :pupilId')
                ->setParameter('pupilId', $pupilId)
                ->andWhere('ibaTestResult.correctAnswerNumberTotal IS NOT NULL')
                ->andWhere('ibaTestResult.correctAnswerPercentGrammar IS NOT NULL')
                ->andWhere('ibaTestResult.correctAnswerPercentStructure IS NOT NULL')
                ->andWhere('ibaTestResult.correctAnswerPercentReading IS NOT NULL')
                ->andWhere('ibaTestResult.correctAnswerPercentListening IS NOT NULL')
                ->orderBy('ibaTestResult.examDate', 'DESC')
                ->setMaxResults(1);
        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    public function getDataInquiryIBA($orgNo, $searchCriteria = false) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();

        $examDate1 = new \DateTime($searchCriteria['examDate']);

        $query->select('ibatest.id, ibatest.examDate, ibatest.schoolYear, ibatest.classCode, ibatest.idNumber,
            ibatest.nameKanji,
            ibatest.nameKana,
            CASE
                WHEN ibatest.pupilId is NULL THEN ibatest.nameKana
                ELSE ibatest.tempNameKana
            END as nameKana,
            ibatest.testType, ibatest.total, ibatest.read, ibatest.listen,
            ibatest.eikenLevelTotal, ibatest.ekenLevelRead, ibatest.eikenLevelListening,
            ibatest.mappingStatus, ibatest.pupilId,
            ibatest.className, ibatest.schoolYearName,
            CASE
                WHEN ibatest.pupilId is NULL THEN ibatest.pupilNo
                ELSE ibatest.tempPupilNo
            END as pupilNo');
        $query->from('\Application\Entity\IBATestResult', 'ibatest');
        $query->andWhere("ibatest.isDelete = 0");
        if ($orgNo) {
            $query->andWhere('ibatest.organizationNo = :orgNo');
            $query->setParameter(':orgNo', trim($orgNo));
        }
        if ($searchCriteria['orgSchoolYear']) {
            $query->andWhere('ibatest.schoolYearName LIKE :schoolyear');
            $query->setParameter(':schoolyear', $searchCriteria['orgSchoolYear']);
        }
        if ($searchCriteria['classj']) {
                $query->andWhere('ibatest.className LIKE :class');
                       $query->setParameter(':class', $searchCriteria['classj']);
        }
        if ($searchCriteria['name'] != null) {
            $query->andWhere('(ibatest.pupilId is not NULL AND ibatest.tempNameKana LIKE :nameKana)
                                OR ibatest.nameKana LIKE :nameKana
                                OR ibatest.nameKanji LIKE :nameKanji');
            $query->setParameter(':nameKana', "%" . trim($searchCriteria['name']) . "%");
            $query->setParameter(':nameKanji', "%" . trim($searchCriteria['name']) . "%");
        }
        if ($searchCriteria['jisshiId'] && $searchCriteria['examType']) {
            $query->andWhere('ibatest.jisshiId = :jisshiId');
            $query->andWhere('ibatest.examType LIKE :examType');
            $query->setParameter(':jisshiId', $searchCriteria['jisshiId']);
            $query->setParameter(':examType', $searchCriteria['examType']);
        }

        $query->addOrderBy('ibatest.schoolYearName', 'ASC');
        $query->addOrderBy('ibatest.className', 'ASC');
        $query->addOrderBy('nameKana', 'ASC');
        $paginator = new DTPaginator($query, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    public function getSchoolYearCode($orgNo, $examDate = false, $jisshiId = false, $examType = false) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $examDate1 = new \DateTime($examDate);
        $examDate2 = date_format($examDate1, 'Y-m-d H:i:s');

        $qb->select('ibatest');
        $qb->from('\Application\Entity\IBATestResult', 'ibatest');
        $qb->andWhere('ibatest.isDelete = 0');
        if ($orgNo) {
            $qb->andWhere('ibatest.organizationNo = :orgNo');
            $qb->setParameter(':orgNo', trim($orgNo));
        }
        if ($jisshiId && $examType) {
            $qb->andWhere('ibatest.jisshiId = :jisshiId');
            $qb->andWhere('ibatest.examType LIKE :examType');
            $qb->setParameter(':jisshiId', trim($jisshiId));
            $qb->setParameter(':examType', trim($examType));
        }
        $qb->groupBy('ibatest.schoolYearName');
        $qb->orderBy('ibatest.schoolYearName', 'ASC');

        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getClassCode($orgNo, $examDate = false, $jisshiId = false, $examType = false) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $examDate1 = new \DateTime($examDate);
        $examDate2 = date_format($examDate1, 'Y-m-d H:i:s');

        $qb->select('ibatest');
        $qb->from('\Application\Entity\IBATestResult', 'ibatest');
        $qb->andWhere('ibatest.isDelete = 0');
        $qb->andWhere('ibatest.className !='.$qb->expr()->literal(''));
        if ($orgNo) {
            $qb->andWhere('ibatest.organizationNo = :orgNo');
            $qb->setParameter(':orgNo', trim($orgNo));
        }
        if ($jisshiId && $examType) {
            $qb->andWhere('ibatest.jisshiId = :jisshiId');
            $qb->andWhere('ibatest.examType LIKE :examType');
            $qb->setParameter(':jisshiId', trim($jisshiId));
            $qb->setParameter(':examType', trim($examType));
        }
        $qb->groupBy('ibatest.className');
        $qb->orderBy('ibatest.className', 'ASC');

        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getHistoryPupilIBA($orgNo, $searchCriteria = false, $getRecord = false) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('ibatest');
        $qb->from('\Application\Entity\IBATestResult', 'ibatest');
        $qb->andWhere('ibatest.isDelete = 0');

        if ($orgNo) {
            $qb->andWhere('ibatest.organizationNo = :orgNo')->setParameter(':orgNo', $orgNo);
        }

        if ($searchCriteria['pupilId'] != null) {
            $qb->andWhere('ibatest.pupilId = :pupilid')->setParameter(':pupilid', $searchCriteria['pupilId']);
        } else {
            $qb->andWhere('ibatest.id = :id')->setParameter(':id', $searchCriteria['id']);
        }

        $qb->orderBy('ibatest.examDate', 'DESC');

        $paginator = new DTPaginator($qb, 'DoctrineORMQueryBuilder');
        if($getRecord){
            return $paginator->getAllItems();
        }
        return $paginator;
    }

    public function getDataLastestByPupilId($pupilId) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('ibaTestResult')
                ->from('\Application\Entity\IBATestResult', 'ibaTestResult', 'ibaTestResult.id')
                ->where('ibaTestResult.pupilId = :pupilId')
                ->setParameter('pupilId', $pupilId)
                ->andWhere('ibaTestResult.correctAnswerNumberTotal IS NOT NULL')
                ->andWhere('ibaTestResult.correctAnswerPercentGrammar IS NOT NULL')
                ->andWhere('ibaTestResult.correctAnswerPercentStructure IS NOT NULL')
                ->andWhere('ibaTestResult.correctAnswerPercentReading IS NOT NULL')
                ->andWhere('ibaTestResult.correctAnswerPercentListening IS NOT NULL')
                ->orderBy('ibaTestResult.examDate', 'DESC')
                ->setMaxResults(1);
        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    public function deleteIBATestResult($orgNo, $jisshiId, $examType) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->delete('\Application\Entity\IBATestResult', 'ibatestresult')
                ->where('ibatestresult.jisshiId = :jisshiId')
                ->andWhere('ibatestresult.examType = :examType')
                ->andWhere('ibatestresult.organizationNo = :orgNo')
                ->setParameter(':jisshiId', $jisshiId)
                ->setParameter(':examType', $examType)
                ->setParameter(':orgNo', $orgNo);
        $query = $qb->getQuery();
        $query->execute();
    }

    /**
     * @param int $year
     * @param $jisshiId
     * @param $examType
     * @param null $isMapped
     * @return array
     * @internal param int $organizationNo
     * @Return ArrayObject
     */
    public function getListIbaTestResult($jisshiId, $examType, $isMapped = null) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('IbaTestResult.id,IbaTestResult.nameKanji, IbaTestResult.nameKana, IbaTestResult.tempNameKana,
                        IbaTestResult.birthday, IbaTestResult.pupilId, IbaTestResult.organizationNo,
                        IbaTestResult.schoolYear, IbaTestResult.classCode,
                        IbaTestResult.year,
                        IbaTestResult.testType,
                        IbaTestResult.testSetNo,
                        IbaTestResult.total,
                        IbaTestResult.read,
                        IbaTestResult.listen,
                        IbaTestResult.eikenLevelTotal,
                        IbaTestResult.eikenLevelTotalNo,
                        IbaTestResult.ekenLevelRead,
                        IbaTestResult.eikenLevelListening,
                        IbaTestResult.examDate,
                        IbaTestResult.isPass
                    ')
                ->from('\Application\Entity\IBATestResult', 'IbaTestResult')
                ->andWhere('IbaTestResult.jisshiId = :jisshiId')
                ->andWhere('IbaTestResult.examType = :examType')
                ->setParameter(':jisshiId', $jisshiId)
                ->setParameter(':examType', $examType)
                ->andWhere('IbaTestResult.isDelete = 0')
                ->addOrderBy('IbaTestResult.schoolYear', 'ASC')
                ->addOrderBy('IbaTestResult.classCode', 'ASC')
                ->addOrderBy('IbaTestResult.nameKanji', 'ASC');
        if ($isMapped === false) {
            $qb->andWhere('IbaTestResult.isMapped = 0');
        } elseif ($isMapped === true) {
            $qb->andWhere('IbaTestResult.pupilId IS NOT NULL');
        }
        return $qb->getQuery()->getArrayResult();
    }

    //
    public function getIbaTestResultByOrgNo($orgNo = 0, $year = 2010, $mappingStatus = 1, $jisshiId = 0, $examType = '') {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('IbaTestResult.id,IbaTestResult.nameKanji, IbaTestResult.nameKana, IbaTestResult.tempNameKana, IbaTestResult.preTempNameKana,IbaTestResult.mappingStatus,
                        IbaTestResult.birthday, IbaTestResult.pupilId, IbaTestResult.tempPupilId, IbaTestResult.organizationNo,
                        IbaTestResult.schoolYear, IbaTestResult.classCode, IbaTestResult.pupilNo, IbaTestResult.attendanceNo,
                        IbaTestResult.year,
                        IbaTestResult.testType,
                        IbaTestResult.testSetNo,
                        IbaTestResult.total,
                        IbaTestResult.read,
                        IbaTestResult.listen,
                        IbaTestResult.eikenLevelTotal,
                        IbaTestResult.ekenLevelRead,
                        IbaTestResult.eikenLevelListening
                    ')
                ->from('\Application\Entity\IBATestResult', 'IbaTestResult')
                ->where('IbaTestResult.isDelete = 0')
                ->andWhere('IbaTestResult.organizationNo = :orgNo')
                ->andWhere('IbaTestResult.year = :year')
                ->andWhere('IbaTestResult.jisshiId = :jisshiId')
                ->andWhere('IbaTestResult.examType = :examType')
                ->setParameter(':orgNo', $orgNo)
                ->setParameter(':year', $year)
                ->setParameter(':jisshiId', $jisshiId)
                ->setParameter(':examType', $examType)
                ->addOrderBy('IbaTestResult.mappingStatus', 'ASC')
                ->addOrderBy('IbaTestResult.schoolYear', 'ASC')
                ->addOrderBy('IbaTestResult.classCode', 'ASC')
                ->addOrderBy('IbaTestResult.nameKana', 'ASC');
        if ($mappingStatus == 1) {
            $qb->andWhere('IbaTestResult.mappingStatus = 1');
        } else {
            $qb->andWhere('IbaTestResult.mappingStatus IN (0,2)');
        }

        $paginator = new DTPaginator($qb, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    // ducna17
    public function updateMappingStatus($ids, $status) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->update('\Application\Entity\IBATestResult', 'ibaTestResult')
                ->set('ibaTestResult.mappingStatus', ':status')
                ->where('ibaTestResult.id IN (:ids)')
                ->setParameter(':ids', $ids)
                ->setParameter(':status', $status);

        $qb->getQuery()->execute();
    }

    /**
     * @author DucNA17
     * @param int $id
     * @param int $pupilId
     * @return 
     */
    public function updatePupilId($id, $pupilId) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->update('\Application\Entity\IBATestResult', 'ibaTestResult')
                ->set('ibaTestResult.pupilId', ':pupilId')
                ->set('ibaTestResult.tempPupilId', ':pupilId')
                ->where('ibaTestResult.id = :id')
                ->setParameter(':id', $id)
                ->setParameter(':pupilId', $pupilId);

        $qb->getQuery()->execute();
    }

    /**
     * @author DucNA17
     * @param int $id
     * @param int $pupilId
     * @return
     */
    public function updateTempPupilId($id, $pupilId) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->update('\Application\Entity\IBATestResult', 'ibaTestResult')
                ->set('ibaTestResult.tempPupilId', ':pupilId')
                ->where('ibaTestResult.id = :id')
                ->setParameter(':id', $id)
                ->setParameter(':pupilId', $pupilId);

        $qb->getQuery()->execute();
    }

    public function getListIdIbaTestResult($jisshiId, $examType) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('IbaTestResult.id')
                ->from('\Application\Entity\IBATestResult', 'IbaTestResult')
                ->andWhere('IbaTestResult.jisshiId = :jisshiId')
                ->andWhere('IbaTestResult.examType = :examType')
                ->setParameter(':jisshiId', $jisshiId)
                ->setParameter(':examType', $examType);

        return $result = $qb->getQuery()->getResult();
    }

    /**
     * 
     * @param array $ids
     * @return 
     */
    public function updateTempData($ids) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->update('\Application\Entity\IBATestResult', 'IbaTestResult')
                ->set('IbaTestResult.mappingStatus', '1')
                ->set('IbaTestResult.tempNameKana', 'IbaTestResult.preTempNameKana')
                ->set('IbaTestResult.pupilId', 'IbaTestResult.tempPupilId')
                ->set('IbaTestResult.schoolYearName', 'IbaTestResult.tempSchoolYearName')
                ->set('IbaTestResult.className', 'IbaTestResult.tempClassName')
                ->set('IbaTestResult.pupilNo', 'IbaTestResult.tempPupilNo')
                ->set('IbaTestResult.classId', 'IbaTestResult.tempClassId')
                ->set('IbaTestResult.orgSchoolYearId', 'IbaTestResult.tempOrgSchoolYearId')
                ->where('IbaTestResult.id IN (:ids)')
                ->setParameter(':ids', $ids);

        $qb->getQuery()->execute();
    }

    /**
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * 
     * get edge(least and greatest) of all 2 skill: Reading, Listenning in IBATestResult
     * 
     * @param type $year
     * @param type $type
     * @param type $objectId
     * @param type $edge
     * @param type $examDate
     * @return int
     */
    function getIBAEdgeScore($year = 0, $type = '', $objectId = 0, $edge = '', $examDate = '', $examType, $jisshiId) {
        $em = $this->getEntityManager();
        $objectId = (int) $objectId;
        $select = '';
        $score = 0;
        switch ($edge) {
            case self::SCORE_EDGE_LEAST: $select = 'MIN(IBATestResult.total)';
                break;
            case self::SCORE_EDGE_GREATEST: $select = 'MAX(IBATestResult.total)';
                break;
        }
        //
        if (!$select)
            return $score;
        //
        $qb = $em->createQueryBuilder();
        $qb->select($select)->from('\Application\Entity\IBATestResult', 'IBATestResult');
        switch ($type) {
            case self::SCORE_TYPE_NAME_ORG:
                $qb->innerJoin('\Application\Entity\Organization', 'Organization', \Doctrine\ORM\Query\Expr\Join::WITH, 'IBATestResult.organizationNo = Organization.organizationNo');
                $qb->where('Organization.id=' . $objectId);
                break;
            case self::SCORE_TYPE_NAME_ORGSCHOOLYEAR:
                $qb->where('IBATestResult.orgSchoolYearId=' . $objectId);
                break;
            case self::SCORE_TYPE_NAME_CLASS:
                $qb->where('IBATestResult.classId=' . $objectId);
                break;
        }
        $qb->andWhere('IBATestResult.isDelete=:isDelete')
                ->andWhere('IBATestResult.year=:year')
                ->andWhere('IBATestResult.examDate=:examDate')
                ->andWhere('IBATestResult.examType=:examType')
                ->andWhere('IBATestResult.jisshiId=:jisshiId')
                ->andWhere('IBATestResult.total > 0');
        $qb->setParameter(':isDelete', self::NOT_DELETE_VALUE);
        $qb->setParameter(':year', $year);
        $qb->setParameter(':examDate', $examDate);
        $qb->setParameter(':examType', $examType);
        $qb->setParameter(':jisshiId', $jisshiId);
        $query = $qb->getQuery();
        $score = $query->getSingleScalarResult();
        return (int) $score;
    }

    /**
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * 
     * UC24 get TestDate
     * 
     * @param type $organizationId
     * @param type $kai
     */
    function getTestDate($organizationId = 0, $year = 0) {
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('MIN(IBATestResult.examDate)')
                ->from('\Application\Entity\IBATestResult', 'IBATestResult')
                ->innerJoin('\Application\Entity\Organization', 'Organization', \Doctrine\ORM\Query\Expr\Join::WITH, 'IBATestResult.organizationNo = Organization.organizationNo')
                ->where('Organization.id=:organizationId')
                ->andWhere('IBATestResult.year=:year')
                ->andWhere('IBATestResult.examDate IS NOT NULL');
        $qb->setParameter(':year', $year);
        $qb->setParameter(':organizationId', $organizationId);
        $query = $qb->getQuery();
        $date = $query->getSingleScalarResult();
        if (!$date)
            $date = '';
        return $date;
    }

    //

    public function getIBALevelByPupilId($listPupilId, $orgNo, $year) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('ibaTestResult.pupilId as pupilId, MIN(ibaTestResult.eikenLevelTotalNo) as ibaLevel')
                ->from('\Application\Entity\IBATestResult', 'ibaTestResult', 'ibaTestResult.pupilId')
                ->where('ibaTestResult.pupilId IN (:listPupil)')
                ->andWhere('ibaTestResult.organizationNo = :orgNo')
                ->andWhere('ibaTestResult.year = :year')
                ->andWhere('ibaTestResult.isDelete = 0')
                ->groupBy('ibaTestResult.pupilId')
                ->setParameter('listPupil', $listPupilId)
                ->setParameter('orgNo', $orgNo)
                ->setParameter('year', $year);
        $query = $qb->getQuery();
        return $query->getArrayResult();
    }

    /**
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * @param type $options
     * @return number 
     */
    function countRecords($options = array()) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('count(IBATestResult)')
                ->from('\Application\Entity\IBATestResult', 'IBATestResult')
                ->where('IBATestResult.isDelete=:isDelete');
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
            $qb->andWhere('IBATestResult.year=:year');
            $qb->setParameter(':year', $options['year']);
        }
        if (isset($options['eikenLevelId']) && $options['eikenLevelId']) {
            $qb->andWhere('IBATestResult.eikenLevelId=:eikenLevelId');
            $qb->setParameter(':eikenLevelId', $options['eikenLevelId']);
        }
        if (isset($options['classId']) && $options['classId']) {
            $qb->andWhere('IBATestResult.classId=:classId');
            $qb->setParameter(':classId', $options['classId']);
        }
        if (isset($options['isPass'])) {
            $qb->andWhere('IBATestResult.isPass=:isPass');
            $qb->setParameter(':isPass', $options['isPass']);
        }
        if (isset($options['orgSchoolYearId']) && $options['orgSchoolYearId']) {
            $qb->andWhere('IBATestResult.orgSchoolYearId=:orgSchoolYearId');
            $qb->setParameter(':orgSchoolYearId', $options['orgSchoolYearId']);
        }
        if (isset($options['examDate']) && $options['examDate']) {
            $qb->andWhere('IBATestResult.examDate=:examDate');
            $qb->setParameter(':examDate', $options['examDate']);
        }
        if (isset($options['examType']) && $options['examType']) {
            $qb->andWhere('IBATestResult.examType=:examType');
            $qb->setParameter(':examType', $options['examType']);
        }
        if (isset($options['jisshiId']) && $options['jisshiId']) {
            $qb->andWhere('IBATestResult.jisshiId=:jisshiId');
            $qb->setParameter(':jisshiId', $options['jisshiId']);
        }
        $query = $qb->getQuery();
        $count = $query->getSingleScalarResult();
        return (int) $count;
    }

    /**
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * Sum CSE score as: speaking, listening, reading, writing
     * @param type $options
     * @return int
     */
    function sumIBAScore($options = array()) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $field = isset($options['field']) ? $options['field'] : '';
        $sum = 0;
        if (!$field)
            return $sum;
        $qb->select('SUM(IBATestResult.' . $field . ')')
                ->from('\Application\Entity\IBATestResult', 'IBATestResult')
                ->where('IBATestResult.isDelete=:isDelete');
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
            $qb->andWhere('IBATestResult.year=:year');
            $qb->setParameter(':year', $options['year']);
        }
        if (isset($options['classId']) && $options['classId']) {
            $qb->andWhere('IBATestResult.classId=:classId');
            $qb->setParameter(':classId', $options['classId']);
        }
        if (isset($options['orgSchoolYearId']) && $options['orgSchoolYearId']) {
            $qb->andWhere('IBATestResult.orgSchoolYearId=:orgSchoolYearId');
            $qb->setParameter(':orgSchoolYearId', $options['orgSchoolYearId']);
        }
        if (isset($options['examDate']) && $options['examDate']) {
            $qb->andWhere('IBATestResult.examDate=:examDate');
            $qb->setParameter(':examDate', $options['examDate']);
        }
        if (isset($options['examType']) && $options['examType']) {
            $qb->andWhere('IBATestResult.examType=:examType');
            $qb->setParameter(':examType', $options['examType']);
        }
        if (isset($options['jisshiId']) && $options['jisshiId']) {
            $qb->andWhere('IBATestResult.jisshiId=:jisshiId');
            $qb->setParameter(':jisshiId', $options['jisshiId']);
        }
        $query = $qb->getQuery();
        $sum = $query->getSingleScalarResult();
        return (int) $sum;
    }

    /**
     * @author minhbn1 <minhbn1@fsoft.com.vn>
     * @param type $organizationId
     * @param type $year
     */
    function getDistinctExamDateOfOrg($organizationId = 0, $year = 0) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('distinct IBATestResult.examDate')
                ->from('\Application\Entity\IBATestResult', 'IBATestResult')
                ->innerJoin('\Application\Entity\Organization', 'Organization', \Doctrine\ORM\Query\Expr\Join::WITH, 'IBATestResult.organizationNo = Organization.organizationNo')
                ->where('Organization.id = :organizationId')
                ->andWhere('IBATestResult.year = :year')
                ->andWhere('IBATestResult.isDelete=:isDelete')
                ->andWhere('IBATestResult.examDate IS NOT NULL')
                ->setParameter(':organizationId', intval($organizationId))
                ->setParameter(':year', intval($year))
                ->setParameter(':isDelete', self::NOT_DELETE_VALUE)
                ->orderBy('IBATestResult.examDate', 'ASC');
        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }
    
    function getDistinctExamIBAOfOrg($organizationId = 0, $year = 0) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('distinct CONCAT(IBATestResult.examType, \'\', IBATestResult.jisshiId) as uniqueExam, IBATestResult.examType, IBATestResult.jisshiId, IBATestResult.examDate')
                ->from('\Application\Entity\IBATestResult', 'IBATestResult')
                ->innerJoin('\Application\Entity\Organization', 'Organization', \Doctrine\ORM\Query\Expr\Join::WITH, 'IBATestResult.organizationNo = Organization.organizationNo')
                ->where('Organization.id = :organizationId')
                ->andWhere('IBATestResult.year = :year')
                ->andWhere('IBATestResult.isDelete=:isDelete')
                ->andWhere('IBATestResult.examDate IS NOT NULL')
                ->setParameter(':organizationId', intval($organizationId))
                ->setParameter(':year', intval($year))
                ->setParameter(':isDelete', self::NOT_DELETE_VALUE)
                ->orderBy('IBATestResult.examDate', 'ASC');
        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }
    
    public function getDataToExport($orgNo) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();

        $query->select('IBATestResult');
        $query->from('\Application\Entity\IBATestResult', 'IBATestResult');
        $query->where('IBATestResult.isDelete = 0');
        if ($orgNo) {
            $query->andWhere('IBATestResult.organizationNo = :orgNo');
            $query->setParameter(':orgNo', trim($orgNo));
        }

        $query->addOrderBy('IBATestResult.schoolYearName', 'ASC');
        $query->addOrderBy('IBATestResult.className', 'ASC');
        $query->addOrderBy('IBATestResult.nameKana', 'ASC');
        
        return $query->getQuery()->getArrayResult();
    }
    
    public function getDataToExportByJisshiIdExamType($jisshiId = false, $examType = false) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();

        $query->select('IBATestResult');
        $query->from('\Application\Entity\IBATestResult', 'IBATestResult');
        $query->where('IBATestResult.isDelete = 0');
        if ($jisshiId && $examType) {
            $query->andWhere('IBATestResult.jisshiId = :jisshiId');
            $query->andWhere('IBATestResult.examType = :examType');
            $query->setParameter(':jisshiId', trim($jisshiId));
            $query->setParameter(':examType', trim($examType));
        }

        $query->addOrderBy('IBATestResult.schoolYearName', 'ASC');
        $query->addOrderBy('IBATestResult.className', 'ASC');
        $query->addOrderBy('IBATestResult.nameKana', 'ASC');
        
        return $query->getQuery()->getArrayResult();
    }
    public function updateMultipleRowsWithEachId($ibaTestResults){
        $setOrgSchoolYearIdSql = '';
        $setOrgSchoolYearNameSql = '';
        $setTempSchoolYearNameSql = '';
        $setClassId = '';
        $setClassName = '';
        $setTempClassName = '';
        $setTempNameKanji = '';
        $setNameKana = '';
        $setTempNameKana = '';
        $setPupilId = '';
        $setPupilNumber = '';
        $setBirthday = '';
        
        if(!$ibaTestResults){
            return false;
        }
        
        foreach($ibaTestResults as $key=>$value){
            $listId[] = $key;
            $setOrgSchoolYearIdSql .= $value['orgSchoolYearId'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "" . intval($value['orgSchoolYearId']) . "") : "WHEN " . $key .  " THEN NULL ";
            $setOrgSchoolYearNameSql .= $value['orgSchoolYearName'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "'" . mysql_escape_string($value['orgSchoolYearName']) . "'") : "WHEN " . $key .  " THEN NULL ";
            $setTempSchoolYearNameSql .= !empty($value['isDeleteMapping']) && $value['isDeleteMapping'] == 1 ? "WHEN " . $key .  " THEN NULL " : $setOrgSchoolYearNameSql;
            $setClassId .= $value['classId'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "" . intval($value['classId']) . "") : "WHEN " . $key .  " THEN NULL ";
            $setClassName .= $value['className'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "'" . mysql_escape_string($value['className']) . "'") : "WHEN " . $key . " THEN NULL ";
            $setTempClassName .= !empty($value['isDeleteMapping']) && $value['isDeleteMapping'] == 1 ? "WHEN " . $key .  " THEN NULL " : $setClassName;
            $setTempNameKanji .= $value['tempNameKanji'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "'" . mysql_escape_string($value['tempNameKanji']) . "'") : "WHEN " . $key . " THEN NULL ";
            $setNameKana .= $value['nameKana'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "'" . mysql_escape_string($value['nameKana']) . "'") : "WHEN " . $key . " THEN NULL ";
            $setTempNameKana .= !empty($value['isDeleteMapping']) && $value['isDeleteMapping'] == 1 ? "WHEN " . $key .  " THEN NULL " : $setNameKana;
            $setPupilId .= $value['pupilId'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "" . intval($value['pupilId']) . "") : "WHEN " . $key .  " THEN NULL";
            $setPupilNumber .= $value['pupilNumber'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "" . intval($value['pupilNumber']) . "") : "WHEN " . $key . " THEN NULL ";
            $setBirthday .= $value['birthday'] !== Null ? sprintf("WHEN %d THEN %s ", $key, "'" . mysql_escape_string($value['birthday']) . "'") : "WHEN " . $key . " THEN NULL ";
        }
        
        $sql = '
            UPDATE IBATestResult SET
                TempOrgSchoolYearId = CASE id ' . $setOrgSchoolYearIdSql . ' END,
                OrgSchoolYearId = CASE id ' . $setOrgSchoolYearIdSql . ' END,
                TempSchoolYearName = CASE id ' . $setTempSchoolYearNameSql . ' END,
                SchoolYearName = CASE id ' . $setOrgSchoolYearNameSql . ' END,
                TempClassId = CASE id ' . $setClassId . ' END,
                ClassId = CASE id ' . $setClassId . ' END,
                TempClassName = CASE id ' . $setTempClassName . ' END,
                ClassName = CASE id ' . $setClassName . ' END,
                TempNameKanji = CASE id ' . $setTempNameKanji . ' END,
                PreTempNameKana = CASE id ' . $setTempNameKana . ' END,
                tempNameKana = CASE id ' . $setNameKana . ' END,
                PupilId = CASE id ' . $setPupilId . ' END,
                TempPupilId = CASE id ' . $setPupilId . ' END,
                TempPupilNo = CASE id ' . $setPupilNumber . ' END,
                TempBirthday = CASE id ' . $setBirthday . ' END
            WHERE id IN (' . implode(',', $listId) . ')
        ';
        
        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeUpdate($sql);
        return $result;
    }
    
    public function getTotalMappingStatus($jisshiId, $examType, $orgNo)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('iba.mappingStatus, COUNT(iba.mappingStatus) AS total')
            ->from('\Application\Entity\IBATestResult', 'iba','iba.mappingStatus')
            ->where('iba.isDelete = 0')
            ->andWhere('iba.organizationNo = :orgNo')
            ->andWhere('iba.jisshiId = :jisshiId')
            ->andWhere('iba.examType = :examType')
            ->setParameter('orgNo', $orgNo)
            ->setParameter('jisshiId', $jisshiId)
            ->setParameter('examType', $examType)
            ->groupBy('iba.mappingStatus');
        return $qb->getQuery()->getArrayResult();
    }
    
    public function getConfirmStatus($organizationNo)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('CONCAT(i.jisshiId,i.examType,i.mappingStatus) as totalMap, i.id, i.jisshiId, i.examType, i.mappingStatus, count(i.mappingStatus) as total')
            ->from('\Application\Entity\IBATestResult', 'i')
            ->where('i.isDelete = 0')
            ->andWhere('i.organizationNo =:orgNo')
            ->setParameter(':orgNo', $organizationNo)
            ->groupBy('i.mappingStatus')
            ->addGroupBy('i.jisshiId', 'i.examType');

        return $qb->getQuery()->getArrayResult();
    }
    
    public function getIBAResultList($orgNo, $year, $jisshiId, $examType, $schoolYearId = '', $classId = '', $nameKana = '', $status = '')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('iba.id, iba.pupilId, iba.nameKana, iba.tempNameKana, iba.preTempNameKana,
                    iba.birthday, iba.tempBirthday,
                    iba.total, iba.eikenLevelTotal,
                    iba.orgSchoolYearId, iba.tempSchoolYearName,
                    iba.classId, iba.tempClassName,
                    pupil.year,
                    iba.mappingStatus,
                    iba.testType')
            ->from('\Application\Entity\IBATestResult', 'iba')
            ->leftJoin('\Application\Entity\Pupil', 'pupil', \Doctrine\ORM\Query\Expr\Join::WITH, 'iba.pupilId = pupil.id')
            ->where('iba.isDelete = 0')
            ->andWhere('iba.organizationNo = :orgNo')
            ->andWhere('iba.jisshiId = :jisshiId')
            ->andWhere('iba.examType = :examType')
            ->setParameter(':orgNo', $orgNo)
            ->setParameter(':examType', $examType)
            ->setParameter(':jisshiId', $jisshiId)
            ->orderBy('iba.nameKana','ASC')
            ->addOrderBy('iba.birthday','ASC');
        if($schoolYearId){
            $qb->andWhere('iba.orgSchoolYearId = :schoolyear')
                ->setParameter(':schoolyear', $schoolYearId);
        }
        if($classId){
            $qb->andWhere('iba.classId = :class')
                ->setParameter(':class', $classId);
        }
        if(!empty(trim($nameKana))){
            $qb->andWhere($qb->expr()->orX(
                    'iba.nameKana LIKE :namePupil',
                    'iba.tempNameKana LIKE :namePupil'
                    )
            )->setParameter(':namePupil', "%" . trim($nameKana) . "%");
        }
        if($status != ''){
            $qb->andWhere('iba.mappingStatus = :mappingstatus')
                ->setParameter(':mappingstatus', $status);
        }
        $paginator = new DTPaginator($qb, 'DoctrineORMQueryBuilder');

        return $paginator;
    }
    
    public function updateIsMappedWidthIds($ids) {
        
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('\Application\Entity\IBATestResult', 'ibaTestResult')
                ->set('ibaTestResult.isMapped', 1)
                ->where('ibaTestResult.id IN ('.implode(',', $ids).')');
        $qb->getQuery()->execute();
    }
    
    public function insertOnDuplicateUpdateMultiple($listIBATestResult)
    {
        if (empty($listIBATestResult)) {
            return false;
        }
        $em = $this->getEntityManager();

        // create sql data for insert.
        $time = date(DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
        $sqlData = '';
        $sqlUpdate = '';
        foreach ($listIBATestResult as $item) {
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
        $tableName = $em->getClassMetadata('Application\Entity\IBATestResult')->getTableName();

        // create header and sql data for on duplicate, not update tempNameKanji
        $headers = array_keys($listIBATestResult[0]);
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
            $exceptedColumn = array('tempNameKana', 'insertAt', 'className', 'schoolYearName','tempNameKanji','mappingStatus');
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
    
    
    public function updateTempValueAfterImport($orgNo, $jisshiId, $examType){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('\Application\Entity\IBATestResult', 'ibaTestResult')
            ->set('ibaTestResult.className', 'ibaTestResult.classCode')
            ->set('ibaTestResult.tempNameKana', 'ibaTestResult.nameKana')
            ->set('ibaTestResult.tempNameKanji', 'ibaTestResult.nameKanji')
            ->set('ibaTestResult.schoolYearName', 'ibaTestResult.schoolYear')
            ->where('ibaTestResult.pupilId IS NULL')
            ->andWhere('ibaTestResult.organizationNo = :orgNo')
            ->andWhere('ibaTestResult.jisshiId = :jisshiId')
            ->andWhere('ibaTestResult.examType = :examType')
            ->andWhere('ibaTestResult.isDelete = 0')
            ->setParameter(':orgNo', $orgNo)
            ->setParameter(':jisshiId', $jisshiId)
            ->setParameter(':examType', $examType);
        $qb->getQuery()->execute();
    }

}
