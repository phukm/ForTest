<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Description of ApplyEikenStudentRepository
 *
 * @author ThanhND35
 */
class ApplyEikenStudentRepository extends EntityRepository {

    //put your code her
    public function updateIsDeleteApplyEikenStudent($gradeId) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->update('\Application\Entity\ApplyEikenStudent', 'apply')
                ->set('apply.isDelete', 1)
                ->where('apply.orgSchoolYearId = :orgschoolyearId')
                ->setParameter(':orgschoolyearId', $gradeId);
        return $query->getQuery()->execute();
    }
    
    public function getApplyEikenStudentByGrade($gradeId,$hallType) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('aplyDetail.id,aplystudent.orgNo,aplystudent.applyEikenOrgId,aplystudent.eikenLevelId,aplystudent.totalStudent'
                .',aplyDetail.lev1,aplyDetail.preLev1,aplyDetail.lev2,aplyDetail.preLev2,aplyDetail.lev3,aplyDetail.lev4,aplyDetail.lev5'
                . ',aplyDetail.discountLev1,aplyDetail.discountPreLev1,aplyDetail.discountLev2,aplyDetail.discountPreLev2,aplyDetail.discountLev3,aplyDetail.discountLev4,aplyDetail.discountLev5')
            ->from('\Application\Entity\ApplyEikenStudent', 'aplystudent')
                ->innerJoin('\Application\Entity\ApplyEikenOrgDetails', 'aplyDetail', \Doctrine\ORM\Query\Expr\Join::WITH, 'aplystudent.applyEikenOrgId = aplyDetail.applyEikenOrgId')
                ->where('aplystudent.orgSchoolYearId = :orgschoolyearId')
                ->andWhere('aplyDetail.hallType = :hallType')
                ->setParameter(':hallType', $hallType)
                ->setParameter(':orgschoolyearId', $gradeId);
        
        $query = $qb->getQuery();
        return $query->getResult();
    }
    
    
    public function updateIsDeleteApplyEikenStudentByApplyEikenOrg($applyEikenOrg) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->update('\Application\Entity\ApplyEikenStudent', 'apply')
                ->set('apply.isDelete', 1)
                ->where('apply.applyEikenOrgId = :applyEikenOrgId')
                ->setParameter(':applyEikenOrgId', $applyEikenOrg);
        return $query->getQuery()->execute();
    }
}
