<?php

namespace Application\Entity\Repository;

use Application\Entity\CseResults;
use Doctrine\ORM\EntityRepository;

class CseResultsRepository extends EntityRepository {
    
    public function getDataCseByOrgIdAndObjTypeAndArraySearch($orgId, $objectType, $search){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('cseResults')
                ->from('\Application\Entity\CSeResults', 'cseResults')
                ->where('cseResults.organizationId = :organizationId')
                ->andWhere('cseResults.objectType = :objectType')
                ->setParameter(':organizationId', $orgId)
                ->setParameter(':objectType', trim($objectType))
                ->andWhere('cseResults.isDelete = 0')
                ->orderBy('cseResults.testDate' , 'ASC')
                ->addOrderBy('cseResults.type' , 'ASC');
        
        if (!empty($search['type'])) {
            $qb->andWhere('cseResults.type = :type')->setParameter(':type', trim($search['type']));
        }
        
        if (!empty($search['yearFrom'])) {
            $qb->andWhere('cseResults.year >= :yearFrom')->setParameter(':yearFrom', intval($search['yearFrom']));
        }

        if (!empty($search['yearTo'])) {
            $qb->andWhere('cseResults.year <= :yearTo')->setParameter(':yearTo', intval($search['yearTo']));
        }
        
        if($objectType == 'OrgSchoolYear'){
            $orgSchoolYearId = !empty($search['orgSchoolYearId']) ? intval($search['orgSchoolYearId']) : 0;
            $qb->andWhere('cseResults.objectId = :objectId')->setParameter(':objectId', $orgSchoolYearId);
        }else if($objectType == 'Class'){
            $classId = !empty($search['classId']) ? intval($search['classId']) : 0;
            $qb->andWhere('cseResults.objectId = :objectId')->setParameter(':objectId', $classId);
        }
        
        return $qb->getQuery()->getArrayResult();
    }
}
