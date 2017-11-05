<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\EinaviExam;
use Zend\Db\Sql\Where;
use Doctrine\ORM\Query\ResultSetMapping;
use Eiken\Helper\NativePaginator;
use DoctrineORMModuleTest\Assets\Entity\Date;
class InquiryMeasureRepository extends EntityRepository
{
    function getInquiryMeasure($orgId, $dateFrom, $dateTo, $schoolYear = null, $class = null, $eikenId = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('measure.inquiryDate,measure.measureTime,measure.pass,measure.fail')
           ->from('Application\Entity\InquiryMeasure', 'measure')
           ->where('measure.organizationId = :orgid')
           ->andWhere('measure.inquiryDate BETWEEN :datefrom AND :dateto ')
           ->andWhere('measure.isDelete = 0')
           ->addOrderBy('measure.measureTime','DESC')
           ->addOrderBy('measure.inquiryDate','DESC')
           ->setParameter(':orgid', $orgId)
           ->setParameter(':datefrom', $dateFrom)
           ->setParameter(':dateto', $dateTo);
        if ($schoolYear)
        {
            $qb->andWhere('measure.orgSchoolYearId = :schooyear')->setParameter(':schooyear', $schoolYear);
        }
        else
        {
            $qb->andWhere('measure.orgSchoolYearId is NULL');
        }
        if ($class)
        {
            $qb->andWhere('measure.classId= :class')->setParameter(':class', $class);
        }
        else
        {
            $qb->andWhere('measure.classId is NULL');
        }
        if($eikenId)
        {
            $qb->andWhere('measure.eikenLevelId= :eikenid')->setParameter(':eikenid', $eikenId);
        }
        else
        {
            $qb->andWhere('measure.eikenLevelId is NULL');
        }
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
}

