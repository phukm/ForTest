<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\Pupil;
use Zend\Filter\StripTags;
use Eiken\Helper\NativePaginator as DTPaginator;
use Doctrine\ORM\Query\ResultSetMapping;

class PupilRepository extends EntityRepository {

    public function getPagedPupilList($organizationId = false, $year = NULL, $orgSchoolYear = false, $class = false, $name = '') {
        $em = $this->getEntityManager();
        
        $minYear = date('Y') - 6;
        // subquery: find min eikenLevelId of EikenScore
        $qbSubEiken = $em->createQueryBuilder();
        $qbSubEiken->select('ekr1.id')
                ->from('\Application\Entity\EikenScore', 'ekr1', 'ekr1.pupilId')
                ->where('pupil.id = ekr1.pupilId AND ekr1.eikenLevelId IS NOT NULL AND ekr1.passFailFlag = 1 AND ekr1.year >= :minYear AND ekr1.isDelete = 0')
                ->orderBy('ekr1.eikenLevelId', 'ASC')
                ->addOrderBy('ekr1.year', 'DESC');
        // Main SQL
        $qb = $em->createQueryBuilder();
        $query = $qb->select('pupil')
                    ->from('\Application\Entity\Pupil', 'pupil')
                    ->leftJoin('\Application\Entity\OrgSchoolYear', 'schoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.orgSchoolYear = schoolYear.id')
                    ->leftJoin('\Application\Entity\ClassJ', 'class', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.class = class.id')
                    ->leftJoin('\Application\Entity\EikenScore', 'ekr', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.id=ekr.pupilId AND '. $qb->expr()->in('ekr.id', $qbSubEiken->getDQL()))
                    ->where('pupil.organizationId = :organizationId')
                    ->setParameter(':organizationId', $organizationId)
                    ->andWhere('pupil.isDelete = 0')
                    ->setParameter(':minYear', $minYear)
                    ->addOrderBy('pupil.year', 'DESC')
                    ->addOrderBy('schoolYear.schoolYearId', 'ASC')
                    ->addOrderBy('class.className', 'ASC')
                    ->addOrderBy('pupil.number', 'ASC')
                    ->addOrderBy('pupil.firstNameKanji', 'ASC')
                    ->addOrderBy('pupil.lastNameKanji', 'ASC')
                    ->addOrderBy('ekr.eikenLevelId', 'ASC')
                    ->addOrderBy('ekr.year', 'DESC')
                    ->groupBy('pupil.id');            
        if (!empty($year)) {
            $query->andWhere('pupil.year = :year')->setParameter(':year', intval($year));
        }
        
        if (!empty($orgSchoolYear)) {
            $query->andWhere('pupil.orgSchoolYearId = :orgSchoolYearId')->setParameter(':orgSchoolYearId', intval($orgSchoolYear));
        }        
        if (!empty($class)) {
            $query->andWhere('pupil.classId = :classId')->setParameter(':classId', intval($class));
        }
        if ($name != '') {
            $query->andWhere($qb->expr()->orX(
                        $qb->expr()->like('CONCAT(COALESCE(pupil.firstNameKanji,\'\'),COALESCE(pupil.lastNameKanji,\'\'))', ':name'),
                        $qb->expr()->like('CONCAT(COALESCE(pupil.firstNameKana,\'\'),COALESCE(pupil.lastNameKana,\'\'))', ':name')
                    ))->setParameter(':name', '%' . trim($name) . '%');
        }
        $paginator = new DTPaginator($query, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    public function getPupilDetail($id, $organizationId = false) {
        $em = $this->getEntityManager();
        $minDateEiken = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y") - 6));
        $minDateIBA = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y") - 3));
            
        $qbSubEiken = $em->createQueryBuilder();
        $qbSubEiken->select('ekr1.id')
                ->from('\Application\Entity\EikenScore', 'ekr1', 'ekr1.pupilId')
                ->where('p.id = ekr1.pupilId AND ekr1.eikenLevelId IS NOT NULL AND ekr1.passFailFlag = 1 AND ekr1.certificationDate >= :minDateEiken AND ekr1.isDelete = 0')
                ->orderBy('ekr1.eikenLevelId', 'ASC')
                ->addOrderBy('ekr1.year', 'DESC');
        // subquery: find min eikenLevelId of IBAScore
        $qbSubIba = $em->createQueryBuilder();
        $qbSubIba->select('ibar1.id')
                ->from('\Application\Entity\IBAScore', 'ibar1')
                ->where('p.id = ibar1.pupilId AND ibar1.eikenLevelId IS NOT NULL AND ibar1.examDate >= :minDateIBA AND ibar1.isDelete = 0')
                ->orderBy('ibar1.eikenLevelId', 'ASC')
                ->addOrderBy('ibar1.examDate', 'DESC')
                ->addOrderBy('ibar1.iBACSETotal', 'DESC');
        // Main SQL
        $qb = $em->createQueryBuilder();
        $qb->select('p.id,p.year, p.number, p.firstNameKanji, p.lastNameKanji, p.firstNameKana, p.lastNameKana,
            p.birthday, p.gender, p.eikenId, p.eikenPassword, p.orgSchoolYearId as orgSchoolYearId, p.einaviId, p.number,
            ekr.id as idEkr, ekr.readingScore as cSEScoreReading, ekr.listeningScore as cSEScoreListening, 
            ekr.cSEScoreWriting as cSEScoreWriting, ekr.cSEScoreSpeaking,ekr.eikenLevelId as eikenLevelIdEkr,
            ekr.eikenCSETotal, ekr.certificationDate as examDateEkien, ibar.id as idIbar, ibar.readingScore as readIbar,
            ibar.listeningScore as listenIbar, ibar.iBACSETotal as totalIbar, ibar.eikenLevelId as eikenLevelIdIbar,
            ibar.examDate as examDateIbar, cl.id as idCl, cl.className,
            schoolyear.id as idSchoolYear, schoolyear.displayName as displayName,
            smresult.id as idSMResult, smresult.resultGrammarId as grammarSMResult, smresult.resultVocabularyId as vocabularySMResult,
            smresult.resultGrammarName as grammarSMResultName, smresult.resultVocabularyName as vocabularySMResultName,
            ekr.year as yearEkr, ekr.kai as kaiEkr')
            ->from('\Application\Entity\Pupil', 'p', 'p.id')
            ->leftJoin('\Application\Entity\EikenScore', 'ekr', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id=ekr.pupilId AND '. $qb->expr()->in('ekr.id', $qbSubEiken->getDQL()))
            ->leftJoin('\Application\Entity\IBAScore', 'ibar', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id=ibar.pupilId AND '. $qb->expr()->in('ibar.id', $qbSubIba->getDQL()))
            ->leftJoin('\Application\Entity\SimpleMeasurementResult', 'smresult', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id=smresult.pupilId AND smresult.status = :status')
            ->leftJoin('\Application\Entity\ClassJ', 'cl', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.classId=cl.id')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'schoolyear', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.orgSchoolYearId=schoolyear.id')
            ->where('p.id = :id')
            ->andWhere('p.organizationId = :organizationId')
            ->setParameter(':id', intval($id))
            ->setParameter(':organizationId', intval($organizationId))
            ->andWhere('p.isDelete = 0')
            ->setParameter(':status', 'Active')
            ->setParameter(':minDateEiken', $minDateEiken)
            ->setParameter(':minDateIBA', $minDateIBA)
            ->orderBy('p.id', 'DESC')
            ->addOrderBy('ekr.eikenLevelId', 'ASC')
            ->addOrderBy('ekr.year', 'DESC')
            ->addOrderBy('ekr.certificationDate', 'DESC')
            ->addOrderBy('ibar.eikenLevelId', 'ASC')
            ->addOrderBy('ibar.examDate', 'DESC')
            ->addOrderBy('ibar.iBACSETotal', 'DESC')
            ->addOrderBy('ekr.status', 'ASC')
            ->addOrderBy('ibar.status', 'ASC')
            ->setMaxResults(1);
        
        $query = $qb->getQuery();
        $details = $query->getOneOrNullResult();
        return $details;
    }

    public function getTotalOrgPupil($organizationId = false) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('MAX(pupil.serialId) AS maxID')
        ->from('\Application\Entity\Pupil', 'pupil')
        ->where('pupil.organizationId = :organizationId')
        ->setParameter(':organizationId', intval($organizationId));

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();
        $results = (int) $result['maxID'];
        return $results;
    }

    public function getPupilDetails($id, $organizationId) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('pupil')
        ->from('\Application\Entity\Pupil', 'pupil')
        ->innerJoin('\Application\Entity\EikenTestResult', 'eikentestresult', \Doctrine\ORM\Query\Expr\Join::WITH, 'eikentestresult.pupilId = pupil.id')
        ->where('pupil.id = :id')
        ->andWhere('pupil.organizationId = :organizationId')
        ->setParameter(':id', intval($id))
        ->setParameter(':organizationId', intval($organizationId));
        $query = $qb->getQuery();
        $pupil = $query->getResult();

        return $pupil;
    }
    
    public function getAllPupilExportByOrgAndSearch($orgId, array $search){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('pupil.id, pupil.year, pupil.orgSchoolYearId, pupil.classId, pupil.einaviId, 
                pupil.firstNameKanji, pupil.lastNameKanji,pupil.firstNameKana, pupil.lastNameKana,
                pupil.number,pupil.birthday, pupil.gender, pupil.eikenId, pupil.eikenPassword,
                class.className, orgSchoolYear.displayName, orgSchoolYear.schoolYearId, 
                smresult.resultVocabularyName, smresult.resultGrammarName')
                ->from('Application\Entity\Pupil', 'pupil')
                ->innerJoin('\Application\Entity\ClassJ', 'class', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.classId = class.id')
                ->innerJoin('\Application\Entity\OrgSchoolYear', 'orgSchoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.orgSchoolYearId = orgSchoolYear.id')
                ->leftJoin('\Application\Entity\SimpleMeasurementResult', 'smresult', \Doctrine\ORM\Query\Expr\Join::WITH, "pupil.id = smresult.pupilId AND smresult.status = :status")
                ->where('pupil.organizationId = :organizationId')
                ->setParameter(':organizationId', intval($orgId))
                ->andWhere('pupil.isDelete = 0')
                ->setParameter(':status', 'Active')
                ->orderBy('pupil.year', 'DESC')
                ->addOrderBy('orgSchoolYear.schoolYearId', 'ASC')
                ->addOrderBy('class.className', 'ASC')
                ->addOrderBy('pupil.number', 'ASC')
                ->addOrderBy('pupil.firstNameKanji', 'ASC')
                ->addOrderBy('pupil.lastNameKanji', 'ASC');
        if (!empty($search['year'])) {
            $query->andWhere('pupil.year = :year')->setParameter(':year', intval($search['year']));
        } 
        if (!empty($search['orgSchoolYearId'])) {
            $query->andWhere('pupil.orgSchoolYearId = :orgSchoolYearId')->setParameter(':orgSchoolYearId', intval($search['orgSchoolYearId']));
        }
        if (!empty($search['className'])) {
            $query->andWhere('class.className = :className')->setParameter(':className', $search['className']);
        }
        if (!empty($search['name'])) {
            $query->andWhere($qb->expr()->orX(
                        $qb->expr()->like('CONCAT(pupil.firstNameKanji, pupil.lastNameKanji)', ':name'),
                        $qb->expr()->like('CONCAT(pupil.firstNameKana, pupil.lastNameKana)', ':name'),
                        $qb->expr()->like('pupil.firstNameKanji', ':name'),
                        $qb->expr()->like('pupil.lastNameKanji', ':name'),
                        $qb->expr()->like('pupil.firstNameKana', ':name'),
                        $qb->expr()->like('pupil.lastNameKana', ':name')
                    ))->setParameter(':name', '%' . trim($search['name']) . '%');
        }
        if(!empty($search['exportIds']) && is_array($search['exportIds'])){
            $query->andWhere($qb->expr()->in('pupil.id', ':exportIds'))->setParameter(':exportIds', $search['exportIds']);
        }
        return $query->getQuery()->getArrayResult();
    }

    public function getPupilExport($organizationId = 0, $year = 0, $orgSchoolYear = 0, $class = 0, $name = '', $export = array()) {
        $em = $this->getEntityManager();
        
        $minYear = date('Y') - 6;
        $qbSubEiken = $em->createQueryBuilder();
        $qbSubEiken->select('ekr1.id')
                ->from('\Application\Entity\EikenScore', 'ekr1', 'ekr1.pupilId')
                ->where('p.id = ekr1.pupilId AND ekr1.eikenLevelId IS NOT NULL AND ekr1.passFailFlag = 1 AND ekr1.year >= :minYear AND ekr1.isDelete = 0')
                ->orderBy('ekr1.eikenLevelId', 'ASC')
                ->addOrderBy('ekr1.year', 'DESC');
        // subquery: find min eikenLevelId of IBAScore
        $qbSubIba = $em->createQueryBuilder();
        $qbSubIba->select('ibar1.id')
                ->from('\Application\Entity\IBAScore', 'ibar1')
                ->where('p.id = ibar1.pupilId AND ibar1.eikenLevelId IS NOT NULL AND ibar1.isDelete = 0')
                ->orderBy('ibar1.eikenLevelId', 'ASC')
                ->addOrderBy('ibar1.examDate', 'DESC');
        // Main SQL
        $qb = $em->createQueryBuilder();
        $qb->select('p.id, p.year,schoolyear.displayName,cl.className,p.number,p.firstNameKanji,p.lastNameKanji,p.firstNameKana,p.lastNameKana,
            p.birthday,p.gender,p.einaviId,p.eikenId,p.eikenPassword,ekr.eikenLevelId as eikenlevelName, ekr.year as yearEkr,ekr.kai as kaiEkr,
            ekr.readingScore as cSEScoreReading,ekr.listeningScore as cSEScoreListening,ekr.cSEScoreWriting as cSEScoreWriting, ekr.cSEScoreSpeaking,
            ibar.eikenLevelId as ibalevelName, ibar.examDate,
            ibar.readingScore as readIbar,ibar.listeningScore as listenIbar,smresult.resultVocabularyName as vocabularySMResult,smresult.resultGrammarName as grammarSMResult')
            ->from('\Application\Entity\Pupil', 'p', 'p.id')
            ->leftJoin('\Application\Entity\EikenScore', 'ekr', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id=ekr.pupilId AND '. $qb->expr()->in('ekr.id', $qbSubEiken->getDQL()) . '')
            ->leftJoin('\Application\Entity\IBAScore', 'ibar', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id=ibar.pupilId AND '. $qb->expr()->in('ibar.id', $qbSubIba->getDQL()))
            ->leftJoin('\Application\Entity\SimpleMeasurementResult', 'smresult', \Doctrine\ORM\Query\Expr\Join::WITH, "p.id=smresult.pupilId AND smresult.status = :status")
            ->leftJoin('\Application\Entity\ClassJ', 'cl', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.classId=cl.id')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'schoolyear', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.orgSchoolYearId=schoolyear.id')
            ->where('p.organizationId = :organizationId')
            ->setParameter(':organizationId', intval($organizationId))
            ->andWhere('p.isDelete = 0')
            ->setParameter(':status', 'Active')
            ->setParameter(':minYear', $minYear)
            ->addOrderBy('p.year', 'DESC')
            ->addOrderBy('schoolyear.schoolYearId', 'ASC')
            ->addOrderBy('cl.className', 'ASC')
            ->addOrderBy('p.number', 'ASC')
            ->addOrderBy('p.firstNameKanji', 'ASC')
            ->addOrderBy('p.lastNameKanji', 'ASC')
            ->addOrderBy('ekr.eikenLevelId', 'ASC')
            ->addOrderBy('ekr.year', 'DESC')
            ->addOrderBy('ibar.eikenLevelId', 'ASC')
            ->addOrderBy('ibar.examDate', 'DESC')
            ->groupBy('p.id');
        if (!empty($year)) {
            $qb->andWhere('p.year = :year')->setParameter(':year', intval($year));
        }else{
            $qb->andWhere('p.year = '.date('Y'));
        }
        if (!empty($orgSchoolYear)) {
            $qb->andWhere('p.orgSchoolYearId = :orgSchoolYearId')->setParameter(':orgSchoolYearId', intval($orgSchoolYear));
        }
        
        if (!empty($class)) {
            $qb->andWhere('p.classId = :classId')->setParameter(':classId', intval($class));
        }
        if (!empty($name)) {
            $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like('CONCAT(p.firstNameKanji, p.lastNameKanji)', ':name'),
                        $qb->expr()->like('CONCAT(p.firstNameKana, p.lastNameKana)', ':name'),
                        $qb->expr()->like('p.firstNameKanji', ':name'),
                        $qb->expr()->like('p.lastNameKanji', ':name'),
                        $qb->expr()->like('p.firstNameKana', ':name'),
                        $qb->expr()->like('p.lastNameKana', ':name')
                    ))->setParameter(':name', '%' . trim($name) . '%');
        }
        if(!empty($export)){
            $qb->andWhere($qb->expr()->in('p.id', ':exportIds'))->setParameter(':exportIds', $export);
        }

        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    
    public function getDataByOrgAndYearAndArraySearch($orgId, $year, $search = array()){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $query = $qb->select('pupil.id,pupil.firstNameKanji , pupil.lastNameKanji
             , pupil.firstNameKana , pupil.lastNameKana, pupil.birthday
             , pupil.classId, pupil.number, pupil.orgSchoolYearId, orgSchoolYear.displayName as orgSchoolYearName, classJ.className')
            ->from('Application\Entity\Pupil', 'pupil')
            ->join('Application\Entity\OrgSchoolYear', 'orgSchoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'orgSchoolYear.id = pupil.orgSchoolYearId')
            ->join('Application\Entity\ClassJ', 'classJ', \Doctrine\ORM\Query\Expr\Join::WITH, 'classJ.id = pupil.classId')
            ->where('pupil.organizationId =:orgId')
            ->andWhere('pupil.year =:year')
            ->setParameter(':orgId', $orgId)
            ->setParameter(':year', $year)
            ->andWhere('pupil.isDelete = 0')
            ->addOrderBy('pupil.firstNameKana', 'ASC')
            ->addOrderBy('pupil.lastNameKana', 'ASC');
        
        return $query->getQuery()->getArrayResult();
    }
    
    //DucNA17
    public function getPupilData($orgId, $year, $examType = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('pupil.id,pupil.firstNameKanji , pupil.lastNameKanji
             , pupil.firstNameKana , pupil.lastNameKana  , pupil.birthday
             , pupil.classId, pupil.pupilID, pupil.number, pupil.orgSchoolYearId, orgSchoolYear.displayName as orgSchoolYearName, classJ.className
            ')->from('Application\Entity\Pupil', 'pupil')
            ->leftjoin('\Application\Entity\OrgSchoolYear', 'orgSchoolYear', 'WITH', 'orgSchoolYear.id = pupil.orgSchoolYearId')
            ->leftjoin('\Application\Entity\ClassJ', 'classJ', 'WITH', 'classJ.id = pupil.classId')
            ->where('pupil.isDelete = 0')
            ->andWhere('pupil.organizationId =:orgId')
            ->andWhere('pupil.year =:year')
            ->setParameter(':orgId', $orgId)
            ->setParameter(':year', $year);
        if($examType == null){
            $qb->addOrderBy('pupil.firstNameKanji', 'ASC');
            $qb->addOrderBy('pupil.lastNameKanji', 'ASC');
        }elseif($examType == 'IBA') {
            $qb->addOrderBy('pupil.firstNameKana', 'ASC');
            $qb->addOrderBy('pupil.lastNameKana', 'ASC');
        }
            $query = $qb->getQuery();
            $result = $query->getArrayResult();
            return $result;
    }

    //DucNA17
    public function getListPupilDataById($ids)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('pupil.id,pupil.firstNameKanji , pupil.lastNameKanji
             , pupil.firstNameKana , pupil.lastNameKana  , pupil.birthday
             , pupil.classId, pupil.pupilID, pupil.number, pupil.orgSchoolYearId, orgSchoolYear.displayName as orgSchoolYearName, classJ.className
            ')->from('Application\Entity\Pupil', 'pupil','pupil.id')
            ->leftjoin('\Application\Entity\OrgSchoolYear', 'orgSchoolYear', 'WITH', 'orgSchoolYear.id = pupil.orgSchoolYearId')
            ->leftjoin('\Application\Entity\ClassJ', 'classJ', 'WITH', 'classJ.id = pupil.classId')
            ->where('pupil.id IN (:ids)')
            ->setParameter(':ids', $ids)
            ->andWhere('pupil.isDelete = 0');

        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getIBATestResult($pupilId) {
        $em = $this->getEntityManager();
        $pupil = $em->getReference('Application\Entity\Pupil', $pupilId);
        return $em->getRepository('Application\Entity\IBATestResult')->findBy(array(
            'pupil' => $pupil
        ));
    }

    public function getEikenTestResult($pupilId) {
        $em = $this->getEntityManager();
        $pupil = $em->getReference('Application\Entity\Pupil', $pupilId);
        return $em->getRepository('Application\Entity\EikenTestResult')->findBy(array(
            'pupil' => $pupil
        ));
    }

    public function getListInquiryProgressPupil($orgId = 0, $year = 0, $orgSchoolYearId = 0, $classId = 0, $fullName = '')
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('pupil.id,pupil.year, CONCAT(COALESCE(pupil.firstNameKanji,\'\'), COALESCE(pupil.lastNameKanji,\'\')) as FullName
                                ,cl.className,orgschoolyear.displayName,pupil.number, COALESCE(pupil.personalId,0) as personalId')
                                ->from('\Application\Entity\Pupil', 'pupil')
                                ->leftJoin('\Application\Entity\ClassJ', 'cl', \Doctrine\ORM\Query\Expr\Join::WITH, "cl.id = pupil.classId")
                                ->leftJoin('\Application\Entity\OrgSchoolYear', 'orgschoolyear', \Doctrine\ORM\Query\Expr\Join::WITH, "orgschoolyear.id = pupil.orgSchoolYearId")
                                ->leftJoin('\Application\Entity\SchoolYear', 'schoolyear', \Doctrine\ORM\Query\Expr\Join::WITH, "schoolyear.id=orgschoolyear.schoolYearId")            
                                ->where('pupil.year = :year')
                                ->setParameter(':year', $year)
                                ->andWhere('pupil.organizationId = :orgId')
                                ->setParameter(':orgId', $orgId)
                                ->andWhere('pupil.isDelete =0');

        if( $classId > 0)
        {
            $query->andWhere('pupil.classId= :classid')->setParameter(':classid', $classId);
        }

        if( $orgSchoolYearId > 0)
        {
            $query->andWhere('pupil.orgSchoolYearId= :orgschoolyearid')->setParameter(':orgschoolyearid', $orgSchoolYearId);
        }

        if( $fullName!='' )
        {
            $query->andWhere('CONCAT(COALESCE(pupil.firstNameKanji,\'\'), COALESCE(pupil.lastNameKanji,\'\'),COALESCE(pupil.firstNameKana,\'\'), COALESCE(pupil.lastNameKana,\'\')) like :fullname')->setParameter(':fullname', '%'. $fullName. '%');
        }

        $query->addOrderBy('pupil.year', 'DESC')
        ->addOrderBy('schoolyear.id', 'ASC')
        ->addOrderBy('cl.className', 'ASC')
        ->addOrderBy('pupil.number', 'ASC');

        $paginator = new DTPaginator($query, 'DoctrineORMQueryBuilder');

        return $paginator;
    }
    /*
     * tuanvn21
     * */
    function totalPupilInSchoolYear($orgId,$keySearch){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('pupil.id')
        ->from('Application\Entity\Pupil', 'pupil')
        ->where('pupil.orgSchoolYearId = :SchoolYearId')
        ->andWhere('pupil.year = :year')
        ->andWhere('pupil.organizationId = :orgId')
        ->setParameter(':SchoolYearId', $keySearch['orgSchoolYearId'])
        ->setParameter(':year', $keySearch['year'])
        ->setParameter(':orgId', $orgId)
        ->andWhere('pupil.isDelete = 0');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getListPupilOfClassByOrg($organizationId) {

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('pupil.id, pupil.number, pupil.year, classj.className, 
                orgschoolyear.displayName as schoolyearName,
                pupil.firstNameKanji, pupil.lastNameKanji, pupil.firstNameKana, pupil.lastNameKana, 
                pupil.birthday, pupil.gender')
                    ->from('\Application\Entity\Pupil', 'pupil')
                    ->join('\Application\Entity\OrgSchoolYear', 'orgschoolyear', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.orgSchoolYearId = orgschoolyear.id')
                    ->join('\Application\Entity\ClassJ', 'classj', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.classId = classj.id')
                    ->where('pupil.organizationId = :organizationId')
                    ->setParameter(':organizationId', $organizationId)
                    ->andWhere('pupil.isDelete = 0');
        return $query->getQuery()->getArrayResult();
    }

    public function getPupilNumberExistInClassByOrg($organizationId, $year, $classId, $orgSchoolYearId, $pupilNumber, $pupilId=0){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('pupil.id, pupil.number')
        ->from('\Application\Entity\Pupil', 'pupil')
        ->where('pupil.organizationId = :organizationId')
        ->andWhere('pupil.year = :year')
        ->andWhere('pupil.classId = :classId')
        ->andWhere('pupil.orgSchoolYearId = :orgSchoolYearId')
        ->andWhere('pupil.number = :number')
        ->setParameter(':organizationId', $organizationId)
        ->setParameter(':year', $year)
        ->setParameter(':classId', $classId)
        ->setParameter(':orgSchoolYearId', $orgSchoolYearId)
        ->setParameter(':number', $pupilNumber)
        ->andWhere('pupil.isDelete = 0')
        ->setMaxResults(1);
        if($pupilId >0){
            $qb->andWhere('pupil.id <> '.intval($pupilId));
        }

        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }
    
    /**
     * ChungDV
     */
    public function getCountPupil($orgId, $year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('count(pupil.id)')
           ->from('\Application\Entity\Pupil', 'pupil')
           ->where(
                    $qb->expr()->andX(
                            $qb->expr()->eq('pupil.organizationId', ':organizationId'),
                            $qb->expr()->eq('pupil.year', ':year'),
                            $qb->expr()->eq('pupil.isDelete', ':isDelete')
                        )
               );
         
         $qb->setParameters(array(
                'organizationId' => $orgId,
                'year'           => $year,
                'isDelete'       => 0
            ));
        
         $count = $qb->getQuery()->getSingleScalarResult();
        
         return $count;
    }
    
    public function getAllPupilByOrgId($organizationId){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();      
        $qb->select('pupil.id, pupil.firstNameKanji, pupil.lastNameKanji, pupil.firstNameKana, pupil.lastNameKana, pupil.birthday')
           ->from('Application\Entity\Pupil', 'pupil')
           ->where('pupil.organizationId = :organizationId')
           ->setParameter(':organizationId', $organizationId)
           ->andWhere('pupil.isDelete = 0');
        return $qb->getQuery()->getArrayResult();
    }

    public function findPupilList($organizationId, $schoolYearId, $classId, $year, $birthday, $nameKana, $nameKanji)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('pupil.id,pupil.number,pupil.year,pupil.birthday,CONCAT(COALESCE(pupil.firstNameKana,\'\'),COALESCE(pupil.lastNameKana,\'\')) as nameKana,CONCAT(COALESCE(pupil.firstNameKanji,\'\'), COALESCE(pupil.lastNameKanji,\'\')) as nameKanji, schoolYear.displayName,class.className')
            ->from('\Application\Entity\Pupil', 'pupil','pupil.id')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'schoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.orgSchoolYear = schoolYear.id')
            ->leftJoin('\Application\Entity\ClassJ', 'class', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.class = class.id')
            ->where('pupil.organizationId = :organizationId')
            ->setParameter(':organizationId', $organizationId)
            ->andWhere('pupil.isDelete = 0')
            ->orderBy('pupil.firstNameKana', 'ASC')
            ->addOrderBy('pupil.lastNameKana', 'ASC')
            ->addOrderBy('pupil.birthday', 'ASC')
            ->addOrderBy('pupil.year', 'DESC')
            ->addOrderBy('schoolYear.schoolYearId', 'ASC')
            ->addOrderBy('class.className', 'ASC');

        if (!empty($year)) {
            $qb->andWhere('pupil.year = :year')->setParameter(':year', intval($year));
        }
        if (!empty($schoolYearId)) {
            $qb->andWhere('pupil.orgSchoolYearId = :orgSchoolYearId')->setParameter(':orgSchoolYearId', intval($schoolYearId));
        }
        if (!empty($classId)) {
            $qb->andWhere('pupil.classId = :classId')->setParameter(':classId', intval($classId));
        }
        if (!empty($birthday)) {
            $qb->andWhere('pupil.birthday = :birthday')->setParameter(':birthday', $birthday);
        }
        if (!empty($nameKana)) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('CONCAT(COALESCE(pupil.firstNameKana,\'\'),COALESCE(pupil.lastNameKana,\'\'))', ':name')
            ))->setParameter(':name', '%' . trim($nameKana) . '%');
        }
        if (!empty($nameKanji)) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('CONCAT(COALESCE(pupil.firstNameKanji,\'\'),COALESCE(pupil.lastNameKanji,\'\'))', ':nameKanji')
            ))->setParameter(':nameKanji', '%' . trim($nameKanji) . '%');
        }
        $qb->setMaxResults(200);

        return $qb->getQuery()->getArrayResult();
    }
    public function getSearchPupilList($organizationId = false, $year = NULL, $orgSchoolYear = false, $class = false, $name = '') {
        $em = $this->getEntityManager();
        
        $minYear = date('Y') - 6;
        // subquery: find min eikenLevelId of EikenScore
        $qbSubEiken = $em->createQueryBuilder();
        $qbSubEiken->select('ekr1.id')
                ->from('\Application\Entity\EikenScore', 'ekr1', 'ekr1.pupilId')
                ->where('pupil.id = ekr1.pupilId AND ekr1.eikenLevelId IS NOT NULL AND ekr1.passFailFlag = 1 AND ekr1.year >= :minYear AND ekr1.isDelete = 0')
                ->orderBy('ekr1.eikenLevelId', 'ASC')
                ->addOrderBy('ekr1.year', 'DESC');
        // Main SQL
        $qb = $em->createQueryBuilder();
        $query = $qb->select('pupil')
                    ->from('\Application\Entity\Pupil', 'pupil')
                    ->leftJoin('\Application\Entity\OrgSchoolYear', 'schoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.orgSchoolYear = schoolYear.id')
                    ->leftJoin('\Application\Entity\ClassJ', 'class', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.class = class.id')
                    ->leftJoin('\Application\Entity\EikenScore', 'ekr', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.id=ekr.pupilId AND '. $qb->expr()->in('ekr.id', $qbSubEiken->getDQL()))
                    ->where('pupil.organizationId = :organizationId')
                    ->setParameter(':organizationId', $organizationId)
                    ->andWhere('pupil.isDelete = 0')
                    ->setParameter(':minYear', $minYear)
                    ->addOrderBy('pupil.year', 'DESC')
                    ->addOrderBy('schoolYear.schoolYearId', 'ASC')
                    ->addOrderBy('class.className', 'ASC')
                    ->addOrderBy('pupil.number', 'ASC')
                    ->addOrderBy('pupil.firstNameKanji', 'ASC')
                    ->addOrderBy('pupil.lastNameKanji', 'ASC')
                    ->addOrderBy('ekr.eikenLevelId', 'ASC')
                    ->addOrderBy('ekr.year', 'DESC')
                    ->groupBy('pupil.id');            
        if (!empty($year)) {
            $query->andWhere('pupil.year = :year')->setParameter(':year', intval($year));
        }
        
        if (!empty($orgSchoolYear)) {
            $query->andWhere('pupil.orgSchoolYearId = :orgSchoolYearId')->setParameter(':orgSchoolYearId', intval($orgSchoolYear));
        }        
        if (!empty($class)) {
            $query->andWhere('class.className = :className')->setParameter(':className', $class);
        }
        if ($name != '') {
            $query->andWhere($qb->expr()->orX(
                        $qb->expr()->like('CONCAT(COALESCE(pupil.firstNameKanji,\'\'),COALESCE(pupil.lastNameKanji,\'\'))', ':name'),
                        $qb->expr()->like('CONCAT(COALESCE(pupil.firstNameKana,\'\'),COALESCE(pupil.lastNameKana,\'\'))', ':name')
                    ))->setParameter(':name', '%' . trim($name) . '%');
        }
        $paginator = new DTPaginator($query, 'DoctrineORMQueryBuilder');
        return $paginator;
    }
    

    public function getListEmptyNameKana($organizationId = false, $year = false) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('pupil')
                    ->from('\Application\Entity\Pupil', 'pupil')
                    ->leftJoin('\Application\Entity\OrgSchoolYear', 'schoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.orgSchoolYear = schoolYear.id')
                    ->leftJoin('\Application\Entity\ClassJ', 'class', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.class = class.id')
                    ->where('pupil.organizationId = :organizationId')
                    ->andWhere('pupil.isDelete = 0')
                    ->andWhere('pupil.year = :year')
                    ->andWhere('pupil.firstNameKana IS NULL')
                    ->andWhere('pupil.lastNameKana IS NULL')
                    ->setParameter(':organizationId', $organizationId)
                    ->setParameter(':year', $year)
                    ->addOrderBy('schoolYear.schoolYearId', 'ASC')
                    ->addOrderBy('class.className', 'ASC')
                    ->addOrderBy('pupil.number', 'ASC');
        $paginator = new DTPaginator($query, 'DoctrineORMQueryBuilder');
        return $paginator;
    }
    
    public function getListPupilWithoutNameKana($orgId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('pupil')
            ->from('\Application\Entity\Pupil', 'pupil')
            ->where('pupil.isDelete = 0')
            ->andWhere('pupil.organizationId = :organizationId')
            ->andWhere('pupil.firstNameKana IS NULL')
            ->andWhere('pupil.lastNameKana IS NULL')
            ->setParameter('organizationId', $orgId);
        return $qb->getQuery()->getArrayResult();
    }

    public function checkDuplicatePupil($organizationId = false,$firstNameKanji = false,$lastNameKanji = false,$firstNameKana = false,$lastNameKana = false,$birthDay = false,$year = false, $pupilId = false){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();      
        $qb ->select('CONCAT(COALESCE(pupil.firstNameKanji,\'\'),COALESCE(pupil.lastNameKanji,\'\')) AS nameKanji,
                    CONCAT(COALESCE(pupil.firstNameKana,\'\'),COALESCE(pupil.lastNameKana,\'\')) AS nameKana,
                    pupil.birthday, pupil.year,pupil.number, schoolYear.displayName AS schoolYearName,
                    class.className, pupil.gender')
            ->from('Application\Entity\Pupil', 'pupil')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'schoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.orgSchoolYear = schoolYear.id')
            ->leftJoin('\Application\Entity\ClassJ', 'class', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.class = class.id')
            ->where('pupil.organizationId = :organizationId')
            ->andWhere('CONCAT(COALESCE(pupil.firstNameKanji,\'\'),COALESCE(pupil.lastNameKanji,\'\')) = :nameKanji')
            ->andWhere('pupil.year = :year')
            ->setParameter(':organizationId', $organizationId)
            ->setParameter(':nameKanji', $firstNameKanji.$lastNameKanji)
            ->setParameter(':year', $year)
            ->andWhere('pupil.isDelete = 0')
            ->orderBy('pupil.number', 'ASC');
        if($firstNameKana || $lastNameKana)
        {
            $qb->andWhere('CONCAT(COALESCE(pupil.firstNameKana,\'\'),COALESCE(pupil.lastNameKana,\'\')) = :nameKana')->setParameter(':nameKana', $firstNameKana.$lastNameKana);
        }
        if($birthDay)
        {
            $qb->andWhere('pupil.birthday = :birthday')->setParameter(':birthday', $birthDay);
        }
        if($pupilId)
        {
            $qb->andWhere('pupil.id != :pupilId')->setParameter(':pupilId', $pupilId);
        }
        return $qb->getQuery()->getArrayResult();
    }

    public function getClassNameByPupilId($pupilId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('c.className as class,s.displayName as orgSchoolYear')
            ->from('\Application\Entity\Pupil', 'p')
            ->leftJoin('\Application\Entity\ClassJ', 'c', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.id = p.classId')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 's', \Doctrine\ORM\Query\Expr\Join::WITH, 's.id = c.orgSchoolYearId')
            ->where('p.id = :pupilId')
            ->setParameter(':pupilId', $pupilId)
            ->andWhere('p.isDelete = 0');
        $query = $qb->getQuery();
        $result = $query->getSingleResult();
        return $result;
    }
}
