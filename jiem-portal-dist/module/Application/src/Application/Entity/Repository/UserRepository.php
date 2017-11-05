<?php
namespace Application\Entity\Repository;

use Dantai\PublicSession;
use Doctrine\ORM\EntityRepository;
use Application\Entity\User;
use Eiken\Helper\NativePaginator as DTPaginator;

class UserRepository extends EntityRepository
{

    
    // search list user
    public function searchUserOrg($userId = '', $fullName = '', $roleId = '', $orgId = '', $roleLevelId = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        // Fix role 6 (new role viewer) is between role 3 and 4
        $minId = $roleLevelId;
        $maxId = 6;
        if($minId == 6){
            $minId = 4;
        }elseif($minId == 4 || $minId == 5){
            $maxId = 5;
        }

        $query = $qb->select('user')
                    ->from('\Application\Entity\User', 'user')
                    ->where('user.organizationId = :orgId')
                    ->setParameter(':orgId', $orgId)
                    ->andWhere('user.roleId >= :minId')
                    ->andWhere('user.roleId <= :maxId')
                    ->setParameter(':minId', $minId)
                    ->setParameter(':maxId', $maxId)
                    ->andWhere('user.status in (:status)')
                    ->setParameter(':status', array('Enable', 'Disable'))
                    ->orderBy('user.id', 'DESC');
        
        if ( $userId != '' ) 
        {
            $query->andWhere('user.userId LIKE :userId')->setParameter(':userId', '%'. $userId.'%');
        }
        if ( $fullName != '' ) 
        {
            $query->andWhere('CONCAT( user.firstNameKanji,user.lastNameKanji ) LIKE :fullName')->setParameter(':fullName', '%'.$fullName.'%');
        }
        if ( $roleId )
        {
            $query->andWhere('user.role = :roleId')->setParameter(':roleId', $roleId);
        }
        
        $paginator = new DTPaginator($query, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    public function incCountLoginFailureById($id = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->update('\Application\Entity\User', 'user')
            ->set('user.countLoginFailure', 'user.countLoginFailure + 1')
            ->where('user.id = ?1')
            ->setParameter('1', $id)
            ->getQuery();
        $result = $query->execute();
        
        return $result;
    }

    public function objectexistsupdate($id = 0, $org_no = '', $id_user = '')
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $query = $qb->getQuery();
        
        $qb->select('user')
            ->from('\Application\Entity\User', 'user')
            ->where('user.id != ?1')
            ->andwhere('user.organizationNo = ?2')
            ->andwhere('user.userId = ?3')
            ->andWhere('user.isDelete = ?4')
            ->setParameter('1', $id)
            ->setParameter('2', $org_no)
            ->setParameter('3', $id_user)
            ->setParameter('4', 0);
        
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        if (! empty($result)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function objectExistEmail($id = 0, $email = '')
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $query = $qb->getQuery();
        
        $qb->select('user')
            ->from('\Application\Entity\User', 'user')
            ->where('user.id != ?1')
            ->andwhere('user.emailAddress = ?3')
            ->andWhere('user.isDelete = ?4')
            ->setParameter('1', $id)
            ->setParameter('3', $email)
            ->setParameter('4', 0);
        
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        if (! empty($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function objectexistdelete($id_list = '', $orgId = '', $role_id = '')
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $query = $qb->getQuery();
        
        $qb->select('user')
            ->from('\Application\Entity\User', 'user')
            ->where('user.id IN (:id_list)')->setParameter('id_list', $id_list)
            ->andWhere('user.organizationId != :org_id OR user.roleId < :role_id')
            ->setParameter('org_id', $orgId)
            ->setParameter('role_id', $role_id)
            ->setMaxResults(1);
        
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        if (empty($result)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getListMailServiceManagerIBA(){
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq ->select('user.emailAddress')
            ->from('\Application\Entity\User', 'user')
            ->where('user.isDelete = 0 AND user.roleId = 2')
            ->andWhere($dq->expr()->in('user.serviceType', array('IBA','All')));
        return $dq->getQuery()->getArrayResult();
    }
    
    public function deleteAllUserByOrgNo($orgNo){
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq ->delete('\Application\Entity\User', 'user')
            ->where('user.organizationNo = :orgNo')
            ->setParameter('orgNo', $orgNo);
        return $dq->getQuery()->execute();
    }
    /*
     * Cron job process disable user have roleId=4 and roleId=5 when begin start new kai.
     * Author: Uthv
     * Create: 24/11/2015
     */
    public function disableAllUserByNewKai() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $query = $qb->update('\Application\Entity\User', 'user')
                ->set('user.status', ':statusDisable')
                ->where('user.roleId = :role4 OR user.roleId = :role5')             
                ->andWhere("user.status =:statusEnable")
                ->setParameter(':statusDisable', 'Disable')
                ->setParameter(':statusEnable', 'Enable')
                ->setParameter(':role4', 4)
                ->setParameter(':role5', 5)
                ->getQuery();
        $query->execute();
    }
}