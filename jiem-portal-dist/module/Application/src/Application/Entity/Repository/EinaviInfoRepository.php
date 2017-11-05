<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
class EinaviInfoRepository extends EntityRepository {
    /*
     * Function get list personalId transfer to Einavi system
     * Author: Uthv
     * Create: 16/09/2016
     * */
    public function getListPersonalIdForPupil()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("DISTINCT enavi.personalId,org.organizationNo")
        ->from('\Application\Entity\EinaviInfo ','enavi')
        ->join('\Application\Entity\Pupil', 'pupil', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.id = enavi.pupilId')
        ->join('\Application\Entity\Organization', 'org', \Doctrine\ORM\Query\Expr\Join::WITH, 'org.id = pupil.organizationId')
        ->where("pupil.isDelete =0 AND enavi.isDelete =0 AND enavi.personalId!=''");
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
}