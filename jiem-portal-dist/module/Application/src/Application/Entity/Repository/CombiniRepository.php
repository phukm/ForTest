<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\Year;

class CombiniRepository extends EntityRepository
{
    public function getCombinis()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('combini')
            ->from('\Application\Entity\Combini', 'combini')
            ->where('combini.isDelete = 0')
            ->orderBy('combini.ordinal', 'ASC');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        return $result;
    }
    
    public function getCombinisByIds($combiniIds){
        $result = array();
        if($combiniIds){
            $em = $this->getEntityManager();
            $qb = $em->createQueryBuilder();
            
            $qb->select('combini')
            ->from('\Application\Entity\Combini', 'combini')
            ->where($qb->expr()->in('combini.id', ':combiniIds'))
            ->setParameter(':combiniIds', $combiniIds)
            ->andWhere('combini.isDelete = 0')
            ->orderBy('combini.ordinal', 'ASC');
            $query = $qb->getQuery();
            $result = $query->getArrayResult();
        }
        
        return $result;
    }
}