<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\ActivityLog;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Eiken\Helper\NativePaginator as DTPaginator;
use Doctrine\ORM\Query\ResultSetMapping;

class ActivityLogRepository extends EntityRepository
{
    public function getListActivityLog($roleId, $orgNo, $search)
    {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        
        $startDate = !empty($search['datetime1']) ? $search['datetime1'] . ' 00:00:00' : '';
        $endDate = !empty($search['datetime2']) ? $search['datetime2'] . ' 23:59:59' : '';
        
        $query->select('activitylog')
        ->from('Application\Entity\ActivityLog', 'activitylog')
        ->where('activitylog.isDelete = 0');
        
        if ($search['orgno'] != '') {
            $query->andWhere('activitylog.organizationNo LIKE :organizationNo1');
            $query->setParameter('organizationNo1', "%" . $search['orgno'] . "%");
        }
        
        if($roleId == 3 || $roleId == 4 || $roleId == 5){
            $query->andWhere('activitylog.organizationNo = :organizationNo2');
            $query->setParameter('organizationNo2', $orgNo);
        }
        
        if ($search['orgname'] != '') {
            $query->andWhere('activitylog.organizationName LIKE :organizationName');
            $query->setParameter('organizationName', "%" . $search['orgname'] . "%");
        }
        
        if ($search['userid'] != '') {
            $query->andWhere('activitylog.userID LIKE :userID');
            $query->setParameter('userID', "%" . $search['userid'] ."%");
        }
        
        if ($search['actiontype']) {
            $query->andWhere('activitylog.actionName = :actionName');
            $query->setParameter('actionName', $search['actiontype']);
        }
        
        if ($search['datetime1']) {
            $query->andWhere('activitylog.insertAt >= :dateTime1');
            $query->setParameter('dateTime1', $startDate);
        }
        
        if ($search['datetime2']) {
            $query->andWhere('activitylog.insertAt <= :dateTime2');
            $query->setParameter('dateTime2', $endDate);
        }
        
        $query->addOrderBy('activitylog.insertAt', 'DESC');
        $query->addOrderBy('activitylog.organizationNo', 'ASC');
        $query->addOrderBy('activitylog.userID', 'ASC');

        $paginator = new DTPaginator($query, 'DoctrineORMQueryBuilder');
        return $paginator;
    }
    public function exportActivityLog($roleId, $orgNo, $search)
    {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        
        $startDate = !empty($search['datetime1']) ? $search['datetime1'] . ' 00:00:00' : '';
        $endDate = !empty($search['datetime2']) ? $search['datetime2'] . ' 23:59:59' : '';
        
        $query->select(
                'activitylog.organizationNo,
                 activitylog.organizationName,
                 activitylog.insertAt,
                 activitylog.userID,
                 activitylog.userName,
                 activitylog.screenName,
                 activitylog.actionName
                '
                )
        ->from('Application\Entity\ActivityLog', 'activitylog')
        ->where('activitylog.isDelete = 0');
        
        if ($search['orgno'] != '') {
            $query->andWhere('activitylog.organizationNo LIKE :organizationNo1');
            $query->setParameter('organizationNo1', "%" . $search['orgno'] . "%");
        }
        
        if($roleId == 3 || $roleId == 4 || $roleId == 5){
            $query->andWhere('activitylog.organizationNo = :organizationNo2');
            $query->setParameter('organizationNo2', $orgNo);
        }
        
        if ($search['orgname'] != '') {
            $query->andWhere('activitylog.organizationName LIKE :organizationName');
            $query->setParameter('organizationName', "%" . $search['orgname'] . "%");
        }
        
        if ($search['userid'] != '') {
            $query->andWhere('activitylog.userID LIKE :userID');
            $query->setParameter('userID', "%" . $search['userid'] ."%");
        }
        
        if ($search['actiontype']) {
            $query->andWhere('activitylog.actionName = :actionName');
            $query->setParameter('actionName', $search['actiontype']);
        }
        
        if ($search['datetime1']) {
            $query->andWhere('activitylog.insertAt >= :dateTime1');
            $query->setParameter('dateTime1', $startDate);
        }
        
        if ($search['datetime2']) {
            $query->andWhere('activitylog.insertAt <= :dateTime2');
            $query->setParameter('dateTime2', $endDate);
        }
        
        $query->addOrderBy('activitylog.insertAt', 'DESC');
        $query->addOrderBy('activitylog.organizationNo', 'ASC');
        $query->addOrderBy('activitylog.userID', 'ASC');
        $query->setMaxResults(50000);
        return $query->getQuery()->getArrayResult();
    }
    
    public function getDetailActivityLog($id)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMapping();
        
        $query = $em->createQuery(
            'SELECT activitylog FROM Application\Entity\ActivityLog activitylog WHERE activitylog.id = :id');
        $query->setParameter('id', $id);
        $query->setMaxResults(1);
        return $result = $query->getOneOrNullResult();
    }
}