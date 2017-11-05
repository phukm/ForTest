<?php
namespace OrgMnt\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Album Repository
 */
class ClassformRepository extends EntityRepository
{

    /**
     * get Paged class
     *
     * @param int $offset            
     *
     * @param int $limit            
     *
     * @return Paginator
     */
    public function getPagedClass($offset = 0, $limit = 10)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('classj')
            ->from('\OrgMnt\Entity\Classform', 'classj')
            ->orderBy('classj.id')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        $query = $qb->getQuery();
        
        $paginator = new Paginator($query);
        
        return $paginator;
    }
    
    /**
     * 
     * @param string $artist
     */
   public function searchByArtist($offset = 0, $limit = 10,$artist) {
    	$em = $this->getEntityManager();
    	$qb = $em->createQueryBuilder();    
    	$qb->select('classj')
    	->from('\OrgMnt\Entity\Classform', 'classj')
    	->where('classj.ClassName = ?1 ')
    	->orderBy('classj.id')
    	->setMaxResults($limit)
    	->setFirstResult($offset);    
    	$query = $qb->getQuery();
    	$query->setParamter('1', $artist);
    	$paginator = new Paginator($query);    
    	return $paginator;
    	 
    }
}