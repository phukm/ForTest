<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\OrganizationClassification;
use Doctrine\ORM\Tools\Pagination\Paginator;

class OrganizationClassificationRepository extends EntityRepository
{

    public function getListClassification()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('orgclassification')->from('\Application\Entity\OrganizationClassification', 'orgclassification');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
}