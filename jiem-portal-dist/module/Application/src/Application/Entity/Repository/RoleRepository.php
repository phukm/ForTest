<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\Role;

class RoleRepository extends EntityRepository
{
	// get list role
	public function ListRole($id) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
		// Fix role 6 (new role viewer) is between role 3 and 4
		$minId = $id;
		$maxId = 6;
		if($minId == 6){
			$minId = 4;
		}elseif($minId == 4 || $minId == 5){
			$maxId = 5;
		}

        $qb->select('role')
           ->from('\Application\Entity\Role','role')
           ->where('role.id >= ?1')
           ->andWhere('role.id <= ?2')
           ->setParameter("1", $minId)
           ->setParameter("2", $maxId);

        $query = $qb->getQuery();

        $result = $query->getArrayResult();
        return $result;
    }
    // get one object
    public function getRole($id) {
    	$em = $this->getEntityManager();
    	$qb = $em->createQueryBuilder();
    
    	$qb->select('role')
    	->from('\Application\Entity\Role','role')
    	->where('role.id = ?1')
    	->andWhere('role.id < 6')
    	->setParameter("1", $id);
    
    	$query = $qb->getQuery();
    
    	$result = $query->getSingleResult();
    	return $result;
    }
}