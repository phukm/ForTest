<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\LearningHistory;
use Zend\Db\Sql\Where;
use Doctrine\ORM\Query\ResultSetMapping;
use Eiken\Helper\NativePaginator;
use DoctrineORMModuleTest\Assets\Entity\Date;
use Eiken\Helper\NativePaginator as DTPaginator;
class LearningHistoryRepository extends EntityRepository
{
    
    /**
     * TODO
     * @param unknown $orgId
     * @param unknown $date
     * @param string $schoolYear
     * @param string $class
     * @param string $eikenId
     * @return multitype:
     */
    function getHistory($orgId, $date, $schoolYear ='', $class='', $eikenId='')
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('pupil.orgSchoolYearId, pupil.classId, COUNT(history.id) AS people, SUM(history.learningTime) AS learningTime,
                     history.learningType,history.eikenLevelId')
            ->from('Application\Entity\LearningHistory', 'history')
            ->leftJoin('Application\Entity\Pupil', 'pupil', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.id = history.pupilId')
            ->where('pupil.organizationId = :orgid')
            ->andWhere('history.learningDate = :date')
            ->andWhere('pupil.isDelete = 0')
            ->addGroupBy('pupil.orgSchoolYearId')
            ->addGroupBy('pupil.classId')
            ->addGroupBy('history.learningType')
            ->addGroupBy('history.eikenLevelId')
            ->setParameter(':orgid', $orgId)
            ->setParameter(':date', $date);
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    
    /**
     * TODO
     * @param unknown $orgId
     * @param unknown $date
     * @return multitype:
     */
    function getTotalHistory($orgId, $date)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('pupil.orgSchoolYearId, pupil.classId,history.eikenLevelId, COUNT(history.pupilId ) AS total, SUM(history.learningTime) AS learningTime')
        ->from('Application\Entity\LearningHistory', 'history')
        ->leftJoin('Application\Entity\Pupil', 'pupil', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.id = history.pupilId')
        ->where('pupil.organizationId = :orgid')
        ->andWhere('history.learningDate = :date')
        ->andWhere('pupil.isDelete = 0')
        ->addGroupBy('pupil.orgSchoolYearId')
        ->addGroupBy('pupil.classId')
        ->addGroupBy('history.eikenLevelId')
        ->setParameter(':orgid', $orgId)
        ->setParameter(':date', $date);
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    
    function getDetailHistory($orgId, $date, $learningType, $schoolYear ='', $class='', $eikenId='')
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('history.personalId, orgSchYear.displayName AS schoolYear, SUM(history.learningTime) AS learningTime, 
                                classJ.className,CONCAT(pupil.firstNameKanji,pupil.lastNameKanji) AS nameKanji,pupil.number')
            ->from('Application\Entity\LearningHistory', 'history')
            ->leftJoin('Application\Entity\Pupil', 'pupil', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.id = history.pupilId')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'orgSchYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.orgSchoolYearId = orgSchYear.id')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'schYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'orgSchYear.schoolYearId = schYear.id')
            ->leftJoin('\Application\Entity\ClassJ', 'classJ', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.classId = classJ.id')
            ->where('pupil.organizationId = :orgid')
            ->andWhere('history.learningDate = :date')
            ->andWhere('pupil.isDelete = 0')
            ->orderBy('schYear.id','ASC')
            ->groupBy('history.personalId')
            ->addOrderBy('classJ.className', 'ASC')
            ->addOrderBy('pupil.number', 'ASC')
            ->addOrderBy('pupil.firstNameKanji', 'ASC')
            ->addOrderBy('pupil.lastNameKanji', 'ASC')
            ->setParameter(':orgid', $orgId)
            ->setParameter(':date', $date);
        
        if ($learningType)
        {
            $qb->andWhere('history.learningType = :type')->setParameter(':type', $learningType);
        }
        if ($schoolYear)
        {
            $qb->andWhere('pupil.orgSchoolYearId = :schooyear')->setParameter(':schooyear', $schoolYear);
        }
        if ($class)
        {
            $qb->andWhere('pupil.classId= :class')->setParameter(':class', $class);
        }
        if($eikenId)
        {
            $qb->andWhere('history.eikenLevelId= :eikenid')->setParameter(':eikenid', $eikenId);
        }
        
        $paginator = new DTPaginator($qb, 'DoctrineORMQueryBuilder');
        return $paginator;
    }
}

