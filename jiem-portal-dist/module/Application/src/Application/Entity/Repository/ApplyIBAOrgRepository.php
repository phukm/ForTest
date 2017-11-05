<?php
namespace Application\Entity\Repository;

use Dantai\Utility\DateHelper;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use History\HistoryConst;
/**
 *
 * @author KhoaNV4
 *
 */
class ApplyIBAOrgRepository extends EntityRepository
{

    public function getApplyIBAOrgIdbyOrgId($orgId, $year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('applyIBAorg.id')
            ->from('\Application\Entity\ApplyIBAOrg', 'applyIBAorg')
            ->leftJoin('\Application\Entity\EikenSchedule', 'eikenschedule', \Doctrine\ORM\Query\Expr\Join::WITH, 'applyIBAorg.eikenScheduleId = eikenschedule.id')
            ->where('applyIBAorg.organizationId = :orgId')
            ->andWhere('eikenschedule.year= :year')
            ->andWhere("eikenschedule.examName = 'iba'")
            ->setParameter(':year', $year)
            ->setParameter(':orgId', $orgId)
            ->setMaxResults('1');
        $result = $qb->getQuery()->getSingleResult();
        return $result['id'];
    }

    public function changeStatusUpdateTotalImport($id, $count, $importStatus = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $query = $qb->update('\Application\Entity\ApplyIBAOrg', 'applyIBAorg')
        ->set('applyIBAorg.totalImport', ':total')
        ->set('applyIBAorg.statusImporting', ':status')
        ->set('applyIBAorg.statusMapping', 0)
        ->where('applyIBAorg.id = :id')
        ->setParameter(':total', $count)
        ->setParameter(':status', $importStatus)
        ->setParameter(':id', $id)
        ->getQuery();
        $query->execute();
    }

    public function updateStatusMapping($id, $statusMapping = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $query = $qb->update('\Application\Entity\ApplyIBAOrg', 'applyIBAorg')
        ->set('applyIBAorg.statusMapping', ':status')
        ->where('applyIBAorg.id = :id')
        ->setParameter(':status', $statusMapping)
        ->setParameter(':id', $id)
        ->getQuery();
        $query->execute();
    }

    public function getNewMoshikomiIdForOrg(){
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->select('MAX(ApplyIBAOrg.moshikomiId)')
                ->from('\Application\Entity\ApplyIBAOrg', 'ApplyIBAOrg')
                ->addOrderBy('ApplyIBAOrg.moshikomiId', 'DESC')
                ->setMaxResults(1)
                ;
        $lastIndex = (int) $dq->getQuery()->getSingleScalarResult();
        return str_pad(($lastIndex + 1),7, '0', STR_PAD_LEFT);
    }
    public function isExistTestDateApplyIBAOrg($orgId, $status, $testDate,$idIBA) {
        $dq = $this->getEntityManager()->createQueryBuilder();
        $classExpr = $dq->expr();
        $andExpr = $classExpr->andX($classExpr->eq('applyIBAOrg.organizationId', ':orgId')
                , $classExpr->neq('applyIBAOrg.status', ':status')
                , $classExpr->eq('applyIBAOrg.testDate', ':testDate'));
        $dq ->select('count(applyIBAOrg.id)')
            ->from('\Application\Entity\ApplyIBAOrg', 'applyIBAOrg')
            ->where($andExpr)
            ->setParameter('orgId', $orgId)
            ->setParameter('status', $status)
            ->setParameter('testDate', $testDate);
        if((int)$idIBA > 0){
            $dq ->andWhere('applyIBAOrg.id <> :id')
                ->setParameter('id', (int)$idIBA);
        }
        $count = $dq->getQuery()->getSingleScalarResult();
        return ($count > 0);
    }

    public function infoApplyIBAOrg($orgId, $dateFrom, $dateTo, $status)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('applyIBAOrg.id, applyIBAOrg.updateAt, applyIBAOrg.testDate, applyIBAOrg.registrationDate')
           ->from('\Application\Entity\ApplyIBAOrg', 'applyIBAOrg')
           ->where(
                    $qb->expr()->andX(
                            $qb->expr()->between('applyIBAOrg.testDate', ':from', ':to'),
                            $qb->expr()->eq('applyIBAOrg.organizationId', ':organizationId'),
                            $qb->expr()->eq('applyIBAOrg.status', ':status'),
                            $qb->expr()->eq('applyIBAOrg.isDelete', ':isDelete')
                        )
               )
           ->addOrderBy('applyIBAOrg.registrationDate', 'ASC')
           ->setParameters(array(
               'organizationId'  => $orgId,
               'from'            => $dateFrom,
               'to'              => $dateTo,
               'status'          => $status,
               'isDelete'        => 0
           ))
           ->setMaxResults(1);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return array();
        }
    }
    public function getIBAOrgByTestDate($orgId)
    {
        $curDate = date('Y-m-d');
          $em = $this->getEntityManager();
          $qb = $em->createQueryBuilder();
           $qb->select('ibaorg')
        ->from('\Application\Entity\ApplyIBAOrg', 'ibaorg')
        ->where('ibaorg.isDelete =0')
        ->andWhere('ibaorg.organizationId = :orgId')
        ->andWhere('ibaorg.testDate < :curDate')
        ->orderBy('ibaorg.testDate','DESC')
        ->setMaxResults(1)
        ->setParameters(array('orgId' => $orgId, 'curDate' => $curDate));
    return $qb->getQuery()->getArrayResult();//$qb->getQuery()->getOneOrNullResult();

    }

    public function getApplyIBAOrgShowPopup($orgId,$year)
    {
        $curDate = date('Y-m-d');
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('ibaorg')
                ->from('\Application\Entity\ApplyIBAOrg', 'ibaorg')
                ->where('ibaorg.isDelete =0')
                ->andWhere('ibaorg.organizationId = :orgId')
//                ->andWhere('ibaorg.testDate < :curDate')
//        ->andWhere('ibaorg.statusMapping = '.HistoryConst::STATUS_MAPPED)
//        ->andWhere('ibaorg.statusImporting = '.HistoryConst::IMPORTED_STATUS)
                ->andWhere('ibaorg.statusAutoImport = ' . HistoryConst::STATUS_AUTO_IMPORT_IBA_COMPLETE
                        . ' OR ibaorg.statusAutoImport = ' . HistoryConst::STATUS_AUTO_IMPORT_IBA_FAILURE)
                ->orderBy('ibaorg.testDate', 'DESC')
                ->setParameters(array('orgId' => $orgId));
//                ->setMaxResults(1);
        return $qb->getQuery()->getResult();
    }

    public function getDataFromApplyIBAByYear($year, $orgId)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('\Application\Entity\ApplyIBAOrg', 'applyiba');
        $rsm->addFieldResult('applyiba', 'id', 'id');
        $rsm->addFieldResult('applyiba', 'status', 'status');
        $rsm->addFieldResult('applyiba', 'firtNameKanji', 'firtNameKanji');
        $rsm->addFieldResult('applyiba', 'lastNameKanji', 'lastNameKanji');
        $rsm->addFieldResult('applyiba', 'testDate', 'testDate');
        $rsm->addFieldResult('applyiba', 'registrationDate', 'registrationDate');
        $rsm->addFieldResult('applyiba', 'insertAt', 'insertAt');
        $rsm->addFieldResult('applyiba', 'insertBy', 'insertBy');

        $whereYear = "AND YEAR(applyiba.testDate) = :year";
        $whereOrg = " AND applyiba.organizationId = :orgId";

        $sql = "SELECT applyiba.id as id, applyiba.status as status, 
                applyiba.firtNameKanji as firtNameKanji, applyiba.lastNameKanji as lastNameKanji,
                applyiba.testDate as testDate, applyiba.registrationDate as registrationDate,
                applyiba.insertAt, applyiba.insertBy
                FROM ApplyIBAOrg applyiba WHERE applyiba.isDelete = 0 " . $whereYear . $whereOrg;

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter(':year', $year);
        $query->setParameter(':orgId', $orgId);

        return $result = $query->getArrayResult();
    }

    public function getIBAOrgByJisshiIdAndType($jisshiId, $examType)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('ibaorg')
            ->from('\Application\Entity\ApplyIBAOrg', 'ibaorg')
            ->where('ibaorg.isDelete =0')
            ->andWhere('ibaorg.jisshiId = :jisshiId')
            ->andWhere('ibaorg.examType = :examType')
            ->orderBy('ibaorg.testDate','DESC')
            ->setMaxResults(1)
            ->setParameters(array('jisshiId' => $jisshiId, 'examType' => $examType));
        return $qb->getQuery()->getArrayResult();//$qb->getQuery()->getOneOrNullResult();
    }

    public function getListExistIBAOrg($listIBAHeader){
        if(empty($listIBAHeader)){
            return false;
        }
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('iba.jisshiId as jisshiid, iba.examType as examkbn')
            ->from('\Application\Entity\ApplyIBAOrg', 'iba')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('iba.jisshiId', ':jisshiId0'),
                $qb->expr()->eq('iba.examType', ':examType0'),
                $qb->expr()->eq('iba.isDelete', '0')
            ))
            ->setParameter('jisshiId0', $listIBAHeader[0]['jisshiid'])
            ->setParameter('examType0', $listIBAHeader[0]['examkbn']);

        if(count($listIBAHeader) > 1){
            for ($i = 1; $i < count($listIBAHeader); $i++) {
                $qb->orWhere($qb->expr()->andX(
                    $qb->expr()->eq('iba.jisshiId', ':jisshiId' . $i),
                    $qb->expr()->eq('iba.examType', ':examType' . $i),
                    $qb->expr()->eq('iba.isDelete', '0')
                    ))
                    ->setParameter('jisshiId' . $i, $listIBAHeader[$i]['jisshiid'])
                    ->setParameter('examType' . $i, $listIBAHeader[$i]['examkbn']);
            }
        }
        $qb->orderBy('iba.jisshiId');

        return $qb->getQuery()->getArrayResult();
    }

    public function insertNewIBAHeader($orgId, $orgNo, $userId, $listIBAHeader){
        if(empty($listIBAHeader)){
            return false;
        }
        $em = $this->getEntityManager();
        $tableName = $em->getClassMetadata('Application\Entity\ApplyIBAOrg')->getTableName();
        $listColumn = array('examType', 'jisshiId', 'hasNewData', 'fromUketuke', 'jisshiKanriNo','groupNo','organizationNo', 'setName', 'year', 'testDate', 'organizationId', 'isDelete', 'insertAt', 'insertBy');

        // create sql param.
        $sqlData = '';
        $params = array();
        for ($i = 0 ; $i < count($listIBAHeader); $i++) {
            // create sql param name
            $sqlData .= ", (:examType" . $i
                            . ", :jisshiId" . $i
                            . ", :hasNewData" . $i
                            . ", :fromUketuke" . $i
                            . ", :jisshiKanriNo" . $i
                            . ", :groupNo" . $i
                            . ", :organizationNo" . $i
                            . ", :setName" . $i
                            . ", :year" . $i
                            . ", :testDate" . $i
                            . ", :orgId" . $i
                            . ", :isDelete" . $i
                            . ", :insertAt" . $i
                            . ", :insertBy" . $i
                        . ")";

            // add param value
            $params = array_merge($params, array('examType' . $i => $listIBAHeader[$i]['examkbn']));
            $params = array_merge($params, array('jisshiId' . $i => $listIBAHeader[$i]['jisshiid']));
            $params = array_merge($params, array('jisshiKanriNo' . $i => $listIBAHeader[$i]['jisshikanrino']));
            $params = array_merge($params, array('groupNo' . $i => $listIBAHeader[$i]['groupno']));
            $params = array_merge($params, array('organizationNo' . $i => $orgNo));
            $params = array_merge($params, array('setName' . $i => $listIBAHeader[$i]['groupnamekj']));
            $params = array_merge($params, array('year' . $i => $listIBAHeader[$i]['nendo']));
            $params = array_merge($params, array('testDate' . $i => date(DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT, strtotime($listIBAHeader[$i]['testdate']))));
            $params = array_merge($params, array('hasNewData' . $i => $listIBAHeader[$i]['midldataumu']));
            $params = array_merge($params, array('fromUketuke' . $i => 1));
            $params = array_merge($params, array('orgId' . $i => $orgId));
            $params = array_merge($params, array('isDelete' . $i => 0));
            $params = array_merge($params, array('insertAt' . $i => date(DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT)));
            $params = array_merge($params, array('insertBy' . $i => $orgNo . "." . $userId));
        }
        $sqlData = trim($sqlData, ",");

        $sql = "INSERT INTO " . $tableName . " (". implode(',', $listColumn) .") VALUES ". $sqlData;
        return $em->getConnection()->executeUpdate($sql, $params);
    }

    public function updateFlagNewDataIBAHeader($orgNo, $userId, $listIBAHeader){
        if(empty($listIBAHeader)){
            return false;
        }
        $em = $this->getEntityManager();
        $tableName = $em->getClassMetadata('Application\Entity\ApplyIBAOrg')->getTableName();

        // create case when sql
        $sqlData = '';
        $sqlCondition = '';
        $params = array();
        for($i = 0; $i < count($listIBAHeader); $i++){
            $condition = "a.jisshiId = :jisshiId".$i." AND a.examType = :examType".$i;
            $sqlData .= " WHEN (".$condition.") THEN :hasNewData".$i;
            $sqlCondition .= " OR (".$condition.")";
            $params = array_merge($params, array('examType' . $i => $listIBAHeader[$i]['examkbn']));
            $params = array_merge($params, array('jisshiId' . $i => $listIBAHeader[$i]['jisshiid']));
            $params = array_merge($params, array('hasNewData' . $i => $listIBAHeader[$i]['midldataumu']));
            $params = array_merge($params, array('isDelete' . $i => 0));
        }
        $sqlCondition = trim($sqlCondition, " OR");
        $params = array_merge($params, array('updateAt' => date(DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT)));
        $params = array_merge($params, array('updateBy' => $orgNo . "." . $userId));

        // create insert sql from data and columns.
        $sql = "UPDATE " . $tableName . " a
                SET a.hasNewData = CASE " . $sqlData . " END,
                a.updateAt = :updateAt,
                a.updateBy = :updateBy
                WHERE (" . $sqlCondition . ") AND a.isDelete = 0";
        //var_dump($sql);die;
        return $em->getConnection()->executeUpdate($sql, $params);
    }

    /**
     * @param $orgId
     * @param null $fromTime : ('Y-m-d H:i:s')
     * @return array
     */
    public function getListIBAHasNewData($orgId, $fromTime = null){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a.id, a.jisshiId, a.examType, a.year')
            ->from('\Application\Entity\ApplyIBAOrg', 'a', 'a.id')
            ->where('a.isDelete = 0')
            ->andWhere($qb->expr()->eq('a.organizationId', ':orgId'))
            ->andWhere($qb->expr()->eq('a.hasNewData', 1))
            ->setParameter('orgId', $orgId);

        if(isset($fromTime)){
            $qb->andWhere($qb->expr()->gte('a.testDate', ':testDate'));
            $qb->setParameter('testDate', $fromTime);
        }
        return $qb->getQuery()->getArrayResult();
    }
}