<?php

namespace Application\Entity\Repository;

use Dantai\DantaiConstants;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\ApplyEikenOrg;
use History\HistoryConst;
use Eiken\Helper\NativePaginator as DTPaginator;

/**
 * @author DuongTD
 *
 */
class ApplyEikenOrgRepository extends EntityRepository {

    const FAIL = 0;
    const SUCCESS = 1;

    public function getEikenOrgByParams($orgId = 0, $eikenScheduleId = 0, $isCheckStatus = true) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenOrg.id, eikenOrg.firtNameKanji, eikenOrg.cd, eikenOrg.lastNameKanji, eikenOrg.mailAddress, eikenOrg.phoneNumber,
             eikenOrg.status, eikenOrg.applyStatus, eikenOrg.typeExamDate, eikenOrg.noApiCalls, eikenOrg.confirmEmail, eikenOrg.cityId,
             eikenOrg.isSentStandardHall, eikenOrg.isSentStandardHall, eikenOrg.districtId, eikenOrg.managerName, eikenOrg.locationType, eikenOrg.status,
             eikenOrg.locationType1, eikenOrg.eikenOrgNo1, eikenOrg.eikenOrgNo123, eikenOrg.eikenOrgNo2,eikenOrg.statusAutoImport, eikenOrg.session')
                ->from('\Application\Entity\ApplyEikenOrg', 'eikenOrg')
                ->where('eikenOrg.organizationId = :orgId')
                ->andWhere('eikenOrg.eikenScheduleId = :eikenScheduleId')
                ->andWhere('eikenOrg.isDelete = 0');
        if ($isCheckStatus) {
            $qb->andWhere('eikenOrg.status = :status1 OR eikenOrg.status = :status2 OR eikenOrg.status = :status3')
                    ->setParameter('status1', 'DRAFT')
                    ->setParameter('status2', 'SUBMITTED')
                    ->setParameter('status3', 'N/A');
        }
        $qb->setParameter('orgId', $orgId)
                ->setParameter('eikenScheduleId', $eikenScheduleId)
                ->setMaxResults(1);
        try {
            $applyOrg = $qb->getQuery()->getSingleResult();
            if ($applyOrg['status'] == 'DRAFT')
                $applyOrg['status'] = '';
            return $applyOrg;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return array();
        }
    }

    /**
     * Get Apply Eiken Org Detail by Hall Typ
     */
    public function getEikenOrgDetailByParams($orgId = 0, $eikenScheduleId = 0, $hallType = -1) {

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenOrgDetail.id, eikenOrg.total, eikenOrg.cd, eikenOrg.typeExamDate,
            eikenOrg.locationType, eikenOrg.locationType1, eikenOrg.eikenOrgNo1, eikenOrg.eikenOrgNo2,
            eikenOrg.eikenOrgNo123,
            eikenOrgDetail.hallType,
            eikenOrgDetail.lev1,  eikenOrgDetail.preLev1,eikenOrgDetail.lev2,eikenOrgDetail.preLev2,
            eikenOrgDetail.lev3, eikenOrgDetail.lev4, eikenOrgDetail.lev5,
            eikenOrgDetail.oldPreLev2, eikenOrgDetail.oldLev2, eikenOrgDetail.oldLev3, eikenOrgDetail.oldLev4, eikenOrgDetail.oldLev5,
            eikenOrgDetail.dateExamLev5, eikenOrgDetail.dateExamLev4, eikenOrgDetail.dateExamLev3,
            eikenOrgDetail.priceLev1, eikenOrgDetail.pricePreLev1, eikenOrgDetail.pricePreLev2,
            eikenOrgDetail.priceLev2, eikenOrgDetail.priceLev3, eikenOrgDetail.priceLev4, eikenOrgDetail.priceLev5,
            eikenOrgDetail.dateExamPreLev2, eikenOrgDetail.dateExamLev2, eikenOrgDetail.insertAt, eikenOrgDetail.updateAt,
            eikenOrg.statusRefund')
                ->from('\Application\Entity\ApplyEikenOrgDetails', 'eikenOrgDetail')
                ->join('eikenOrgDetail.applyEikenOrg', 'eikenOrg')
                ->where('eikenOrg.organizationId = :orgId')
                ->andWhere('eikenOrg.eikenScheduleId = :eikenSchedule_id')
                ->andWhere('eikenOrg.isDelete = 0')
//         ->andWhere('eikenOrgDetail.hallType = :hall_type')
//         ->setParameter('hall_type', $hallType)
                ->setParameter('orgId', $orgId)
                ->setParameter('eikenSchedule_id', $eikenScheduleId);
        //die($qb->getQuery()->getSQL());
        if ($hallType == -1) {
            return $qb->getQuery()->getArrayResult();
        }

        $qb->andWhere('eikenOrgDetail.hallType = :hall_type')
                ->setParameter('hall_type', $hallType)
                ->setMaxResults(1);
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return array();
        }
    }

    public function updateStatusAndTotalImporting($organizationId = '', $eikenScheduleId = '', $total = '', $importStatus = 0) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->update('\Application\Entity\ApplyEikenOrg', 'applyEikenOrg')
                ->set('applyEikenOrg.totalImport', ':total')
                ->set('applyEikenOrg.statusImporting', ':importStatus')
                ->set('applyEikenOrg.statusMapping', 0)
                ->where('applyEikenOrg.eikenScheduleId = :scheId')
                ->andWhere('applyEikenOrg.organizationId = :orgId')
                ->setParameter(':total', $total)
                ->setParameter(':importStatus', $importStatus)
                ->setParameter(':scheId', $eikenScheduleId)
                ->setParameter(':orgId', $organizationId);
        try {
            $query = $qb->getQuery();
            $query->execute();
            return self::SUCCESS;
        } catch (Exception $e) {
            return self::FAIL;
        }
    }

    public function updateStatusMapping($organizationId = '', $eikenScheduleId = '', $mappingStatus = 0) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->update('\Application\Entity\ApplyEikenOrg', 'applyEikenOrg')
                ->set('applyEikenOrg.statusMapping', ':mappingStatus')
                ->where('applyEikenOrg.eikenScheduleId = :scheId')
                ->andWhere('applyEikenOrg.organizationId = :orgId')
                ->setParameter(':mappingStatus', $mappingStatus)
                ->setParameter(':scheId', $eikenScheduleId)
                ->setParameter(':orgId', $organizationId);
        try {
            $query = $qb->getQuery();
            $query->execute();
            return self::SUCCESS;
        } catch (Exception $e) {
            return self::FAIL;
        }
    }

    /**
     * @author taivh
     * @param number $orgId
     * @param number $eikenScheduleId
     * @param string $isSUBITTED
     */
    public function getTotalApplyEikenOrg($orgId = 0, $eikenScheduleId = 0, $isSUBITTED = 'SUBMITTED') {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select("App.total, App.updateAt")
                ->from('\Application\Entity\ApplyEikenOrg', 'App')
                ->where('App.isDelete = 0')
                ->andWhere('App.organizationId = :orgId')
                ->andWhere('App.eikenScheduleId = :eikenScheduleId')
                ->andWhere('App.status = :status')
                ->setParameter(':orgId', $orgId)
                ->setParameter(':eikenScheduleId', $eikenScheduleId)
                ->setParameter(':status', $isSUBITTED)
                ->setMaxResults(1);
        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @author anhnt56
     * @param number $orgId
     * @param number $eikenScheduleId
     */
    public function getApplyEikenOrgByEikenScheduleId($orgId = 0, $eikenScheduleId = 0) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('applyEikenOrg.registrationDate', 'applyEikenOrg.updateAt as applyEikenOrgUpdateAt', 'applyEikenOrg.status', 'applyEikenOrg.statusImporting', 'applyEikenOrg.isDelete as isDeleteApply')
                ->from('\Application\Entity\ApplyEikenOrg', 'applyEikenOrg')
                ->where('applyEikenOrg.isDelete = 0')
                ->andWhere('applyEikenOrg.organizationId = :orgId')
                ->andWhere('applyEikenOrg.eikenScheduleId = :eikenScheduleId')
                ->setParameter(':orgId', $orgId)
                ->setParameter(':eikenScheduleId', $eikenScheduleId)
                ->setMaxResults(1);
        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @author anhnt56
     * @param number $orgId
     * @param number $eikenScheduleId
     */
    public function getApplyEikenOrgShowPopup($orgId = 0, $eikenScheduleId = 0) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('applyEikenOrg')
                ->from('\Application\Entity\ApplyEikenOrg', 'applyEikenOrg')
                ->where('applyEikenOrg.isDelete = 0')
                ->andWhere('applyEikenOrg.organizationId = :orgId')
                ->andWhere('applyEikenOrg.eikenScheduleId = :eikenScheduleId')
                ->andWhere('applyEikenOrg.statusAutoImport = ' . HistoryConst::STATUS_AUTO_IMPORT_EIKEN_ROUND1_COMPLETE .
                        ' OR applyEikenOrg.statusAutoImport = ' . HistoryConst::STATUS_AUTO_IMPORT_EIKEN_ROUND2_COMPLETE .
                        ' OR applyEikenOrg.statusAutoImport = ' . HistoryConst::STATUS_AUTO_IMPORT_EIKEN_FAILURE)
                ->setParameter(':orgId', $orgId)
                ->setParameter(':eikenScheduleId', $eikenScheduleId)
                ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getDataFromApplyEikenByYear($year, $orgId) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('applyeiken.id, applyeiken.registrationDate,
                applyeiken.firtNameKanji, applyeiken.lastNameKanji,
                applyeiken.executorName,
                applyeiken.status, eikenschedule.year,
                applyeiken.insertAt, applyeiken.insertBy')
                ->from('\Application\Entity\ApplyEikenOrg', 'applyeiken')
                ->leftJoin('\Application\Entity\EikenSchedule', 'eikenschedule', \Doctrine\ORM\Query\Expr\Join::WITH, 'applyeiken.eikenScheduleId = eikenschedule.id')
                ->where('applyeiken.organizationId = :orgId')
                ->andWhere('applyeiken.isDelete = 0')
                ->andWhere('eikenschedule.year = :year')
                ->orderBy('applyeiken.insertAt', 'ASC')
                ->setParameter(':orgId', $orgId)
                ->setParameter('year', $year);

        return $qb->getQuery()->getArrayResult();
    }

    public function searchApplyEikenWithRefund($orgNo = '', $orgName = '', $year = '', $kai = '', $refund = '') {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $expr = $qb->expr();

        $query = $qb->select('ApplyEikenOrg')
                ->from('\Application\Entity\ApplyEikenOrg', 'ApplyEikenOrg')
                ->leftJoin('\Application\Entity\Organization', 'Organization', \Doctrine\ORM\Query\Expr\Join::WITH, ' Organization.id = ApplyEikenOrg.organizationId')
                ->leftJoin('\Application\Entity\EikenSchedule', 'EikenSchedule', \Doctrine\ORM\Query\Expr\Join::WITH, ' ApplyEikenOrg.eikenScheduleId = EikenSchedule.id')
                ->andWhere('EikenSchedule.isDelete = 0')
                ->andWhere('ApplyEikenOrg.isDelete = 0')
                ->andWhere('Organization.isDelete = 0')
                ->andWhere('ApplyEikenOrg.status = \'SUBMITTED\'')
                ->groupBy('ApplyEikenOrg.id')
                ->orderBy('EikenSchedule.year', 'DESC')
                ->addOrderBy('EikenSchedule.kai', 'DESC');

        if ($orgNo) {
            $query->andWhere('Organization.organizationNo LIKE :orgNo')->setParameter(':orgNo', '%' . $orgNo . '%');
        }

        if ($orgName) {
            $query->andWhere($expr->orX('Organization.orgNameKanji LIKE :orgName', 'Organization.orgNameKana LIKE :orgName'))->setParameter(':orgName', '%' . $orgName . '%');
        }

        if ($refund != '') {
            $query->andWhere($expr->eq('ApplyEikenOrg.statusRefund', $refund));
        }

        if ($kai)
            $query->andWhere($expr->eq('EikenSchedule.kai', $kai));

        if ($year)
            $query->andWhere($expr->eq('EikenSchedule.year', $year));

        $paginator = new DTPaginator($query, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    public function getDataToExport($scheduleId) {
        return $this->getEntityManager()
                        ->createQueryBuilder()
                        ->select('a.organizationId, 
                        o.organizationNo,
                        o.orgNameKanji,
                        CASE a.status
                            WHEN :status1 THEN \'ドラフト\'
                            ELSE \'提出済み\'
                        END as status,
                        a.typeExamDate,
                        a.actualExamDate,
                        a.cd,
                        sd.friDate,
                        sd.satDate,
                        sd.sunDate,
                        a.locationType,
                        a.firtNameKanji,
                        a.lastNameKanji,
                        o.telNo')
                        ->from('\Application\Entity\ApplyEikenOrg', 'a')
                        ->join('\Application\Entity\Organization', 'o', \Doctrine\ORM\Query\Expr\Join::WITH, 'a.organizationId =o.id')
                        ->join('\Application\Entity\EikenSchedule', 'sd', \Doctrine\ORM\Query\Expr\Join::WITH, 'a.eikenSchedule =sd.id')
                        ->where('a.isDelete = 0')
                        ->andWhere('a.eikenScheduleId = :scheduleId')
                        ->andWhere('a.status = :status1 OR a.status = :status2')
                        ->setParameter(':status1', 'DRAFT')
                        ->setParameter(':status2', 'SUBMITTED')
                        ->setParameter(':scheduleId', $scheduleId)
                        ->groupby('a.organizationId')
                        ->getQuery()
                        ->getResult();
    }
    
    // Haven't applyken but it have pupil register.
    public function getDataToExport2($scheduleId, $listExceptOrg = null) {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('DISTINCT pi.organizationId')
            ->from('\Application\Entity\ApplyEikenPersonalInfo', 'pi')
            ->join('\Application\Entity\ApplyEikenLevel', 'lv', \Doctrine\ORM\Query\Expr\Join::WITH, 'pi.id = lv.applyEikenPersonalInfoId')
            ->where('lv.eikenScheduleId = :eikenScheduleId')
            ->andWhere('pi.isDelete = 0')
            ->andWhere('lv.isDelete = 0');
        if (!empty($listExceptOrg)) {
            $qb->andWhere($qb->expr()->notIn('pi.organizationId', $listExceptOrg));
        }
        $queryFromEikenLv = $qb->getDQL();

        $qb2 = $em->createQueryBuilder();
        $qb2->select('o.id as organizationId,
                      o.organizationNo,
                      o.orgNameKanji,
                      \'未作成\' as status,
                      \'\' as typeExamDate,
                      \'\' as actualExamDate,
                      \'\' as cd,
                      \'\' as friDate,
                      \'\' as satDate,
                      \'\' as sunDate,
                      \'\' as locationType,
                      \'\' as firtNameKanji,
                      \'\' as lastNameKanji,
                      o.telNo
                     ')
            ->from('\Application\Entity\Organization', 'o')
            ->where('o.isDelete = 0')
            ->andWhere($qb2->expr()->in('o.id', $queryFromEikenLv))
            ->setParameter('eikenScheduleId', $scheduleId);

        return $qb2->getQuery()->getArrayResult();
    }

    public function getListApplyEikenOrg($eikenScheduleId, $status = 'all') {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('a.organizationId')
                ->from('\Application\Entity\ApplyEikenOrg', 'a')
                ->where('a.isDelete = 0');

        if ($status != 'all') {
            $qb->andWhere('a.status LIKE :status')
                    ->setParameter(':status', $status);
        } else {
            $qb->andWhere('a.status LIKE :status1 OR a.status LIKE :status2')
                    ->setParameter(':status1', DantaiConstants::SUBMITTED)
                    ->setParameter(':status2', DantaiConstants::DRAFT);
        }

        $qb->andWhere('a.eikenScheduleId = :eikenScheduleId')
                ->setParameter(':eikenScheduleId', $eikenScheduleId);
        return $qb->getQuery()->getArrayResult();
    }

    public function getArrayRegisteredStandardHall($eikenScheduleId, $type = DantaiConstants::SUBMITTED) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $columns = $type == DantaiConstants::SUBMITTED ?
            array('oldLev2', 'oldPreLev2', 'oldLev3', 'oldLev4', 'oldLev5') :
            array('lev2', 'preLev2', 'lev3', 'lev4', 'lev5');
        $qb->select('a.organizationId,
                     CASE WHEN d.'.$columns[0].' IS NULL THEN 0 ELSE d.'.$columns[0].' as standardRegisteredLevel2,
                     CASE WHEN d.'.$columns[1].' IS NULL THEN 0 ELSE d.'.$columns[1].' as standardRegisteredLevelPre2,
                     CASE WHEN d.'.$columns[2].' IS NULL THEN 0 ELSE d.'.$columns[2].' as standardRegisteredLevel3,
                     CASE WHEN d.'.$columns[3].' IS NULL THEN 0 ELSE d.'.$columns[3].' as standardRegisteredLevel4,
                     CASE WHEN d.'.$columns[4].' IS NULL THEN 0 ELSE d.'.$columns[4].' as standardRegisteredLevel5
                     ')
                ->from('\Application\Entity\ApplyEikenOrg', 'a', 'a.organizationId')
                ->join('\Application\Entity\ApplyEikenOrgDetails', 'd', \Doctrine\ORM\Query\Expr\Join::WITH, 'd.applyEikenOrgId = a.id AND d.hallType = 0')
                ->where('a.isDelete = 0')
                ->andWhere('a.status LIKE :status')
                ->andWhere('a.eikenScheduleId = :eikenScheduleId')
                ->orderBy('a.organizationId')
                ->setParameter(':status', $type)
                ->setParameter(':eikenScheduleId', $eikenScheduleId);
        return $qb->getQuery()->getArrayResult();
    }
    
    /**
     * @param $year
     * @param $kai
     * @return mixed
     */
    public function insertDummyEikenOrg($year, $kai) {
        if (empty($year) || empty($kai)) {
            return false;
        }

        $em = $this->getEntityManager();

        $tableName = $em->getClassMetadata('Application\Entity\ApplyEikenOrg')->getTableName();
        $tableNameSchedule = $em->getClassMetadata('Application\Entity\EikenSchedule')->getTableName();
        $tableNameOrg = $em->getClassMetadata('Application\Entity\Organization')->getTableName();

        $sql = "INSERT INTO " . $tableName . " ( `organizationId`, `eikenScheduleId`, `status`, `updateBy`, `insertBy`, `statusRefund`, `isDelete`) 
                    SELECT 
                    id as organizationId, 
                    (SELECT id FROM ".$tableNameSchedule." WHERE year = " . $year . " AND kai = " . $kai . ") as eikenScheduleId,
                    'N/A' as `status`,
                    'DUMMY_DATA' as updateBy,
                    'DUMMY_DATA' as insertBy,
                    0 as statusRefund,
                    0 as isDelete
                    FROM ".$tableNameOrg." 
                    WHERE id NOT IN (SELECT organizationId FROM ".$tableName." WHERE eikenScheduleId = (SELECT id FROM ".$tableNameSchedule." WHERE year = " . $year . " AND kai = " . $kai . ")) 
                    AND isDelete = 0";

        return $em->getConnection()->executeUpdate($sql);
    }

}
