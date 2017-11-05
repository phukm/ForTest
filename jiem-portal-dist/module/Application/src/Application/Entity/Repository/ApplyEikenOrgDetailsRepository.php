<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ApplyEikenOrgDetailsRepository extends EntityRepository {

    const FAIL = 0;
    const SUCCESS = 1;

    public function updateDiscountKyu($detailID=0, $kyu=array()) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('\Application\Entity\ApplyEikenOrgDetails', 'applydetails')
                ->set('applydetails.'.$kyu['ColumName'], ':disCount')
                ->set('applydetails.'.$kyu['Columlevel'], ':totaldisCount')
                ->where('applydetails.id = :detailID')
                ->setParameter(':disCount', $kyu['Value'])
                ->setParameter(':totaldisCount', $kyu['Vallevel'])
                ->setParameter(':detailID', $detailID);
        try {

            $query = $qb->getQuery();
            $query->execute();
            return self::SUCCESS;
        } catch (Exception $e) {
            return self::FAIL;
        }
    }
    
    public function getTotalPupilDiscount($orgId , $eikenScheduleId){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('
                     SUM(applyEikenOrgDetails.discountLev1) as level1,
                     SUM(applyEikenOrgDetails.discountPreLev1) as preLevel1,
                     SUM(applyEikenOrgDetails.discountLev2) as level2,
                     SUM(applyEikenOrgDetails.discountPreLev2) as preLevel2,
                     SUM(applyEikenOrgDetails.discountLev3) as level3,
                     SUM(applyEikenOrgDetails.discountLev4) as level4,
                     SUM(applyEikenOrgDetails.discountLev5) as level5
                    ')
            ->from('\Application\Entity\ApplyEikenOrg', 'applyEikenOrg')
            ->join('\Application\Entity\ApplyEikenOrgDetails', 'applyEikenOrgDetails', \Doctrine\ORM\Query\Expr\Join::WITH, 'applyEikenOrgDetails.applyEikenOrgId = applyEikenOrg.id')
            ->where('applyEikenOrgDetails.isDelete = 0')
            ->andWhere('applyEikenOrg.isDelete = 0')
            ->andWhere('applyEikenOrg.eikenScheduleId = :eikenScheduleId')
            ->andWhere('applyEikenOrg.organizationId = :organizationId')
            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->setParameter(':organizationId', $orgId)
            ->groupBy('applyEikenOrgDetails.applyEikenOrgId');
        return $qb->getQuery()->getArrayResult();
    }
    
    /**
     * @param $year
     * @param $kai
     * @return mixed
     */
    public function insertDummyEikenOrgDetail($year, $kai) {
        if (empty($year) || empty($kai)) {
            return false;
        }

        $em = $this->getEntityManager();

        $tableName = $em->getClassMetadata('Application\Entity\ApplyEikenOrgDetails')->getTableName();
        $tbNameAppEikenOrg = $em->getClassMetadata('Application\Entity\ApplyEikenOrg')->getTableName();
        $tableNameSchedule = $em->getClassMetadata('Application\Entity\EikenSchedule')->getTableName();
        
        $sql = "INSERT INTO ".$tableName." (applyEikenOrgId, hallType, updateBy, insertBy, `status`, isDelete) 
                SELECT applyEikenOrgId, hallType, updateBy, insertBy, `status`, isDelete FROM (
                SELECT 
                id as applyEikenOrgId,
                1 as hallType,
                'DUMMY_DATA' as updateBy,
                'DUMMY_DATA' as insertBy,
                'N/A' as `status`,
                0 as `isDelete`
                FROM ".$tbNameAppEikenOrg." WHERE insertBy = 'DUMMY_DATA' AND eikenScheduleId = (SELECT id FROM ".$tableNameSchedule." WHERE year = ".$year." AND kai = ".$kai.")
                AND id NOT IN (SELECT applyEikenOrgId FROM ".$tableName." WHERE isDelete = 0)
                UNION ALL
                SELECT 
                id as applyEikenOrgId,
                0 as hallType,
                'DUMMY_DATA' as updateBy,
                'DUMMY_DATA' as insertBy,
                'N/A' as `status`,
                0 as `IsDelete`
                FROM ".$tbNameAppEikenOrg." WHERE insertBy = 'DUMMY_DATA' AND eikenScheduleId = (SELECT id FROM ".$tableNameSchedule." WHERE year = ".$year." AND kai = ".$kai.")
                AND id NOT IN (SELECT applyEikenOrgId FROM ".$tableName." WHERE isDelete = 0)
                ) as tmp ORDER BY applyEikenOrgId ASC, hallType ASC";

        return $em->getConnection()->executeUpdate($sql);
    }

}
