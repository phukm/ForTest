<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\GroupBy;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Zend\Http\Header\IfMatch;
use Composer\Autoload\ClassLoader;
use Application\Entity\StudyGearHistory;
use Zend\Validator\File\Count;

/**
 *
 * @author TaiVH 2015
 *
 */
class StudyGearHistoryRepository extends EntityRepository
{

    /**
     * function get list
     *
     * @author TaiVH
     * @param $orgId int
     * @param $year int
     *
     * @return data of view
     *         Author Modified Start date End date
     *         DucNA Creates 2015-07-26 2015-07-26
     */
    public function getListStudyGear($orgId, $currentDate)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('dt.organizationId, dt.total, dt.updateDate')
            ->from('\Application\Entity\StudyGearHistory', 'dt')
            ->where('dt.organizationId = :org_id')
            ->setParameter('org_id', $orgId)
            ->andWhere('dt.updateDate = :current_date')
            ->setParameter('current_date', $currentDate);
        
        $res = $qb->getQuery()->getArrayResult();
        return $res;
    }
}