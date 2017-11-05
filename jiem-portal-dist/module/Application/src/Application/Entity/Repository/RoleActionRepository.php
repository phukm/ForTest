<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\RoleAction;

class RoleActionRepository extends EntityRepository
{
    
    // get action of role
    /*
     * @param $roleId
     * return array()
     */
    public function getActionsByRole($roleId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('action.title', 'action.link')
            ->from('Application\Entity\Action', 'action')
            ->innerJoin('Application\Entity\RoleAction', 'roleAction')
            ->where('action.id = roleAction.actionId AND roleAction.role = ?1')
            ->setParameter('1', $roleId);
        
        $query = $qb->getQuery();
        $results = $query->getResult();
        $roles = $query->getArrayResult();
        
        $dataRoles = array();
        foreach($roles as $role){
            $dataRoles[] = strtolower($role["link"]);
        }
 
        return $dataRoles;
    }
}