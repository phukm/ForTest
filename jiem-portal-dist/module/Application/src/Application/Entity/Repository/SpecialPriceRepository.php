<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class SpecialPriceRepository extends EntityRepository {

    function getSpecialPrice($conditions = array()) {
        $qb = $this->getEntityManager()
                ->createQueryBuilder();

        return $qb->select('sp')
                        ->from('\Application\Entity\SpecialPrice', 'sp')
                        ->where($qb->expr()->in('sp.schoolYearCode', $this->getEntityManager()
                                        ->createQueryBuilder()
                                        ->select('sym.schoolYearCode')
                                        ->from('\Application\Entity\SchoolYearMapping', 'sym')
                                        ->where($qb->expr()->in('sym.orgCode', $this->getEntityManager()
                                                        ->createQueryBuilder()
                                                        ->select('o.organizationCode')
                                                        ->from('\Application\Entity\Organization', 'o')
                                                        ->where('o.organizationNo = :organizationNo')
                                                        ->andWhere('o.isDelete =:isDelete')
                                                        ->getDQL()
                                        ))
                                        ->andWhere($qb->expr()->in('sym.schoolYearId', $this->getEntityManager()
                                                        ->createQueryBuilder()
                                                        ->select('osy.schoolYearId')
                                                        ->from('\Application\Entity\OrgSchoolYear', 'osy')
                                                        ->where('osy.id = :orgSchoolYearId')
                                                        ->andWhere('osy.isDelete =:isDelete')
                                                        ->getDQL()
                                        ))
                                        ->getDQL()
                        ))
                        ->andWhere('sp.organizationNo = :organizationNo')
                        ->andWhere('sp.year = :year')
                        ->andWhere('sp.kai = :kai')
                        ->andWhere('sp.isDelete =:isDelete')
                        ->setParameter(':organizationNo', $conditions['orgNo'])
                        ->setParameter(':orgSchoolYearId', $conditions['orgSchoolYearId'])
                        ->setParameter(':year', $conditions['year'])
                        ->setParameter(':kai', $conditions['kai'])
                        ->setParameter(':isDelete', 0)
                        ->getQuery()
                        ->getarrayResult();
    }
    
    function getSpecialPriceAllGrade($orgId,$year,$kai) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $data = $qb->select('sp as special','orgSchoolYear.id as orgSchoolYearId','schoolYear.id as schoolYearId')
                        ->from('\Application\Entity\SpecialPrice', 'sp')
                        ->innerJoin('\Application\Entity\SchoolYearMapping', 'schoolYearMapping', 
                                \Doctrine\ORM\Query\Expr\Join::WITH, 'sp.schoolYearCode = schoolYearMapping.schoolYearCode AND sp.schoolClassification = schoolYearMapping.orgCode')
                        ->innerJoin('\Application\Entity\Organization', 'organization', 
                                \Doctrine\ORM\Query\Expr\Join::WITH, 'sp.organizationId = organization.id')
                        ->innerJoin('\Application\Entity\SchoolYear', 'schoolYear', 
                                \Doctrine\ORM\Query\Expr\Join::WITH, 'schoolYearMapping.schoolYearId = schoolYear.id')
                        ->innerJoin('\Application\Entity\OrgSchoolYear', 'orgSchoolYear', 
                                \Doctrine\ORM\Query\Expr\Join::WITH, 'orgSchoolYear.schoolYearId = schoolYearMapping.schoolYearId AND orgSchoolYear.organizationId = organization.id')
                        ->where($qb->expr()->in('sp.schoolYearCode', 
                                        $em->createQueryBuilder()
                                        ->select('sym.schoolYearCode')
                                        ->from('\Application\Entity\SchoolYearMapping', 'sym')
                                        ->where($qb->expr()->in('sym.orgCode', 
                                                        $em->createQueryBuilder()
                                                        ->select('o.organizationCode')
                                                        ->from('\Application\Entity\Organization', 'o')
                                                        ->where('o.id = :organizationId')
                                                        ->andWhere('o.isDelete =:isDelete')
                                                        ->getDQL()
                                        ))
                                        ->andWhere($qb->expr()->in('sym.orgCode',
                                                        $em->createQueryBuilder()
                                                        ->select('org.organizationCode')
                                                        ->from('\Application\Entity\Organization', 'org')
                                                        ->where('org.id = :organizationId')
                                                        ->andWhere('org.isDelete =:isDelete')
                                                        ->getDQL()
                                        ))
                                        ->getDQL()
                        ))
                        ->andWhere(
                                $qb->expr()->in('sp.schoolClassification',
                                            $em->createQueryBuilder()
                                            ->select('orga.organizationCode')
                                            ->from('\Application\Entity\Organization', 'orga')
                                            ->where('orga.id = :organizationId')
                                            ->andWhere('orga.isDelete =:isDelete')
                                            ->getDQL()
                                ))
                        ->andWhere('sp.organizationId = :organizationId')
                        ->andWhere('sp.year = :year')
                        ->andWhere('sp.kai = :kai')
                        ->andWhere('sp.isDelete =:isDelete')
                        ->setParameter(':organizationId', $orgId)
                        ->setParameter(':year', $year)
                        ->setParameter(':kai', $kai)
                        ->setParameter(':isDelete', 0)
                        ->orderBy('schoolYear.id','ASC')
                        ->getQuery()->getArrayResult();
        return $data;
    }

}
