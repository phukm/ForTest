<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\Organization;
use Application\Entity\ApplyEikenOrg;
use Eiken\Helper\NativePaginator as DTPaginator;
use Doctrine\ORM\Query\ResultSetMapping;
class OrganizationRepository extends EntityRepository
{
    const STATUS_SUBMITTED_IBA = 'SUBMITTED';
    /*
     * DucNA
     * get CityId by OrgId
     * return string/NULL
     */
    public function getCityIdByOrgId($orgId)
    {
        $return = NUll;
        if($orgId!==NULL){
            $em = $this->getEntityManager();
            $qb = $em->createQueryBuilder();

            $qb->select('organization.cityId')
            ->from('\Application\Entity\Organization', 'organization')
            ->where('organization.id =:orgId')
            ->setParameter(':orgId', $orgId);

            $return = $qb->getQuery()->getSingleScalarResult();
        }
        return $return;
    }
    // get one Organization
    public function getOrganizationByNo($no = "", $limit = 1, $offset = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('organization')
            ->from('\Application\Entity\Organization', 'organization')
            ->where('organization.organizationNo = ?1')
            ->setParameter("1", $no)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $qb->getQuery();

        $result = $query->getArrayResult();
        return $result;
    }
     
    public function searchOrganization($orgNo = "", $orgNameKanji = "", $orgNameKana = "", $orgExamType = "", $dateFrom='', $dateTo = '', $refundStatus = '', $publicFunding = '', $paymentBill = '', $currentEikenId = ''){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $expr = $qb->expr();
        //return true if searching with advanced search
        $advancedSearch = ($refundStatus != '') || ($publicFunding != '') || ($paymentBill != '');
        $query = $qb->select('organization.id,
                                organization.organizationNo,
                                organization.orgNameKanji,
                                organization.orgNameKana,
                                organization.cityId,
                                city.cityName as cityName,
                                organization.address1,
                                CASE
                                    WHEN semi.semiMainVenue IS NULL THEN 0
                                    ELSE semi.semiMainVenue
                                END as semiMainVenue
                                ')
            ->from('\Application\Entity\Organization', 'organization')
            ->leftJoin('\Application\Entity\SemiVenue', 'semi', \Doctrine\ORM\Query\Expr\Join::LEFT_JOIN, 'organization.id = semi.organizationId AND semi.eikenScheduleId = :semiEikenScheduleId')
            ->leftJoin('\Application\Entity\City', 'city', \Doctrine\ORM\Query\Expr\Join::LEFT_JOIN, 'organization.cityId = city.id')
            ->setParameter('semiEikenScheduleId', $currentEikenId)
            ->andWhere('organization.isDelete = 0')
            ->groupBy('organization.id')
            ->orderBy("organization.organizationNo", "ASC");
        if( $orgNo != '' )
        {
            $query->andWhere('organization.organizationNo LIKE :orgNo')->setParameter(':orgNo', '%'.$orgNo.'%');
        }
        if( $orgNameKanji != '' )
        {
            $query->andWhere('organization.orgNameKanji LIKE :orgNameKanji')->setParameter(':orgNameKanji', '%'.$orgNameKanji.'%');
        }
        if( $orgNameKana != '' )
        {
            $query->andWhere('organization.orgNameKana LIKE :orgNameKana')->setParameter(':orgNameKana', '%'.$orgNameKana.'%');
        }
        
        if ($advancedSearch){
             $query  ->leftJoin('\Application\Entity\ApplyEikenOrg', 'ApplyEikenOrg', \Doctrine\ORM\Query\Expr\Join::WITH, ' ApplyEikenOrg.organizationId = organization.id')                        
                     ->andWhere('ApplyEikenOrg.isDelete = 0')
                     ->andWhere('ApplyEikenOrg.status = \'SUBMITTED\'');
             
            if ($refundStatus != ''){               
                $query  ->andWhere($expr->eq('ApplyEikenOrg.statusRefund', $refundStatus));                        
            }
            if ($paymentBill != '' || $publicFunding != ''){
                $query  ->leftJoin('\Application\Entity\PaymentMethod', 'PaymentMethod', \Doctrine\ORM\Query\Expr\Join::WITH, ' PaymentMethod.organizationId = organization.id')                        
                        ->andWhere('PaymentMethod.isDelete = 0');
                
                if ($paymentBill != ''){
                     $query->andWhere($expr->eq('PaymentMethod.paymentBill', $paymentBill));
                }                   
                if ($publicFunding != ''){
                     $query->andWhere($expr->eq('PaymentMethod.publicFunding', $publicFunding));
                }                                   
            }
        }
        
        if( $orgExamType || $dateFrom || $dateTo )
        { 
            if($orgExamType === 'IBA')
            {
//                update function for : #GNCCNCJDR5-771
                $query  ->leftJoin('\Application\Entity\ApplyIBAOrg', 'ApplyIBAOrg', \Doctrine\ORM\Query\Expr\Join::WITH, ' ApplyIBAOrg.organizationId = organization.id')
                        ->andWhere('ApplyIBAOrg.isDelete = 0');
                if( $dateFrom)
                {
                    $query ->andWhere('ApplyIBAOrg.testDate >= :dateForm')->setParameter(':dateForm', $dateFrom);
                }
                if( $dateTo)
                {
                    $query ->andWhere('ApplyIBAOrg.testDate <= :dateTo')->setParameter(':dateTo', $dateTo);
                }
            }
            else 
            {
                $conditionExamType = $expr->orX();
                $conditionEken = $expr->andX();
                $conditionIBA = $expr->andX();
                if (!$advancedSearch)
                    $query  ->leftJoin('\Application\Entity\ApplyEikenOrg', 'ApplyEikenOrg', \Doctrine\ORM\Query\Expr\Join::WITH, ' ApplyEikenOrg.organizationId = organization.id');
                $query  ->leftJoin('\Application\Entity\EikenSchedule', 'EikenSchedule', \Doctrine\ORM\Query\Expr\Join::WITH, 'ApplyEikenOrg.eikenScheduleId = EikenSchedule.id');
                $conditionEken->add($expr->eq('ApplyEikenOrg.status', ':status'));
                $conditionEken->add($expr->eq('ApplyEikenOrg.isDelete', '0'));                
                if ($orgExamType === '英検')
                    $conditionEken->add($expr->eq('EikenSchedule.examName', '\'英検\''));
                
                if( ($dateFrom || $dateTo))
                {
                    $query->leftJoin('\Application\Entity\ApplyEikenOrgDetails', 'ApplyEikenOrgDetails', \Doctrine\ORM\Query\Expr\Join::WITH, 'ApplyEikenOrgDetails.applyEikenOrgId = ApplyEikenOrg.id');
                    $condition = $expr->orX();
                        
                    // condition for date
                    $conditionIBADate = $expr->andX();
                    $conditionRoundDate = $expr->andX();
                    $conditionDateFri = $expr->andX();
                    $conditionDateSat = $expr->andX();
                    $conditionDateSun = $expr->andX();
                    if( $dateFrom )
                    {
                        if(empty($orgExamType))
                        {
                            $conditionIBADate->add($expr->gte('ApplyIBAOrg.testDate', ':dateFrom'));
                        }
                        $conditionRoundDate->add($expr->gte('EikenSchedule.round2ExamDate', ':dateFrom'));
                        $conditionDateFri->add($expr->gte('EikenSchedule.friDate', ':dateFrom'));
                        $conditionDateSat->add($expr->gte('EikenSchedule.satDate', ':dateFrom'));
                        $conditionDateSun->add($expr->gte('EikenSchedule.sunDate', ':dateFrom'));
                    }
                    if( $dateTo )
                    {
                        if(empty($orgExamType))
                        {
                            $conditionIBADate->add($expr->lte('ApplyIBAOrg.testDate', ':dateTo'));
                        }
                        $conditionRoundDate->add( $expr->lte('EikenSchedule.round2ExamDate', ':dateTo') );
                        $conditionDateFri->add($expr->lte('EikenSchedule.friDate', ':dateTo'));
                        $conditionDateSat->add($expr->lte('EikenSchedule.satDate', ':dateTo'));
                        $conditionDateSun->add($expr->lte('EikenSchedule.sunDate', ':dateTo'));
                    }       
                    if(empty($orgExamType))
                    {
                        $conditionIBA->add($conditionIBADate);
                    }         
                    
                    $condition->add($conditionRoundDate);
                    
                    // condition examDateLevel for Friday
                    $conditionExamLevelFri =  $expr->orX();
                    $conditionExamLevelFri->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev2',     ':scheduleDateFri') );
                    $conditionExamLevelFri->add( $expr->eq('ApplyEikenOrgDetails.dateExamPreLev2',  ':scheduleDateFri') );
                    $conditionExamLevelFri->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev3',     ':scheduleDateFri') );
                    $conditionExamLevelFri->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev4',     ':scheduleDateFri') );
                    $conditionExamLevelFri->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev5',     ':scheduleDateFri') );
                    $condition->add($expr->andX($conditionDateFri, $conditionExamLevelFri));
                    
                    // condition examDateLevel for Satuday
                    $conditionExamLevelSat =  $expr->orX();
                    $conditionExamLevelSat->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev2',     ':scheduleDateSat') );
                    $conditionExamLevelSat->add( $expr->eq('ApplyEikenOrgDetails.dateExamPreLev2',  ':scheduleDateSat') );
                    $conditionExamLevelSat->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev3',     ':scheduleDateSat') );
                    $conditionExamLevelSat->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev4',     ':scheduleDateSat') );
                    $conditionExamLevelSat->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev5',     ':scheduleDateSat') );
                    $condition->add($expr->andX($conditionDateSat, $conditionExamLevelSat));
                    
                    // condition examDateLevel for Sunday
                    $conditionExamLevelSun =  $expr->orX();
                    $conditionExamLevelSun->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev2',     ':scheduleDateSun') );
                    $conditionExamLevelSun->add( $expr->eq('ApplyEikenOrgDetails.dateExamPreLev2',  ':scheduleDateSun') );
                    $conditionExamLevelSun->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev3',     ':scheduleDateSun') );
                    $conditionExamLevelSun->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev4',     ':scheduleDateSun') );
                    $conditionExamLevelSun->add( $expr->eq('ApplyEikenOrgDetails.dateExamLev5',     ':scheduleDateSun') );
                    $condition->add( $expr->andX( $conditionDateSun, 
                                        $expr->orX( $conditionExamLevelSun ,
                                                    $expr->eq('ApplyEikenOrg.hasMainHall',':hasMainHall') 
                                        )));
                    $conditionEken->add($condition);
                    if(empty($orgExamType))
                    {
                        $query  ->leftJoin('\Application\Entity\ApplyIBAOrg', 'ApplyIBAOrg', \Doctrine\ORM\Query\Expr\Join::WITH, ' ApplyIBAOrg.organizationId = organization.id');
                        $conditionIBA->add($expr->eq('ApplyIBAOrg.isDelete',0));
                        $conditionIBA->add($expr->eq('ApplyIBAOrg.status', '\'CONFIRMED\''));
                    }
                    
                    if( $dateFrom )
                    {
                        $query->setParameter(':dateFrom', $dateFrom);
                    }
                    if( $dateTo )
                    {
                        $query->setParameter(':dateTo', $dateTo);
                    }
                    $query->setParameter(':scheduleDateFri', 1) // scheduleDateFri have value is 1
                        ->setParameter(':scheduleDateSat', 2) // scheduleDateSat have value is 2
                        ->setParameter(':scheduleDateSun', 3) // scheduleDateSun have value is 3
                        ->setParameter(':hasMainHall', 1);// hasMainHall have value is 1
                }
                $conditionExamType->add($conditionEken);
                $conditionExamType->add($conditionIBA);
                $query->andWhere($conditionExamType);
                $query->setParameter(':status', self::STATUS_SUBMITTED_IBA);
            }
        }
        $paginator = new DTPaginator($query, 'DoctrineORMQueryBuilder');
        return $paginator;
    }
    
    public function listOrganization(){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('organization')
            ->from('\Application\Entity\Organization', 'organization')
            ->where('organization.isDelete = 0');
        
        $paginator = new DTPaginator($qb, 'DoctrineORMQueryBuilder');
        return $paginator;
    }
    //created by uthv
     public function getListOrgNotConfirmApplyEiken($year=0,$kai=0,$orgNo='',$orgName='',$status='')
     {
        $draft="DRAFT";
        $submitted="SUBMITTED";
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qe = $qb->expr();
        $conditionQuery = $qe->andX();
        $conditionSubQuery=  clone $conditionQuery;
                $conditionQuery->add($qe->eq('org.isDelete', 0));
         $conditionQuery->add($qe->eq('eikenmaster.isDelete', 0));
        $currentYear = date("Y");
        if (date("m") < 4) {
            $currentYear = date("Y") - 1;
        }
        if ($year != 0) {
            $currentYear = $year;
        }
        $conditionQuery->add($qe->eq('eikenschedule.year', ':year'));
        if ($kai != 0) {
            $conditionQuery->add($qe->eq('eikenschedule.kai', ':kai'));
        }
        if ($orgNo) {
            $conditionQuery->add($qe->like('COALESCE(org.OrganizationNo,\'\')', ':orgNo'));
        }
        if ($orgName) {
            $conditionQuery->add($qe->like('CONCAT(COALESCE(org.OrgNameKanji,\'\'), COALESCE(org.OrgNameKana,\'\'))', ':orgName'));
        }
        if ($status) {
            if($status=="DRAFT"){
                $conditionSubQuery->add($qe->eq('Status', ':draft'));
            }else{
                $conditionSubQuery->add(
                       $qe->andX( $qe->eq('Status', ':submitted'),
                        $qe->orX('Lv1!=Lev1', 'PreLv1!=PreLev1', 'Lv2!=Lev2', 'PreLv2!=PreLev2', 'Lv3!=Lev3', 'Lv4!=Lev4', 'Lv5!=Lev5'))
                );
            }
        }else{
             $conditionSubQuery->add(
                     $qe->orX(
                        $qe->eq('Status', ':draft'),
                        $qe->andX(
                            $qe->eq('Status', ':submitted'),
                                $qe->orX('Lv1!=Lev1', 'PreLv1!=PreLev1', 'Lv2!=Lev2', 'PreLv2!=PreLev2', 'Lv3!=Lev3', 'Lv4!=Lev4', 'Lv5!=Lev5')
                          )
                     ));
        }
        $select="Email,TelNo,OrgNameKanji,OrganizationNo,Status,PupilNo,MainHall,StandardHall FROM(
                SELECT org.Email,org.TelNo,org.OrgNameKanji,
                COALESCE(SUM(eikendetails.Lev1),0) as Lev1,
                COALESCE(SUM(eikendetails.PreLev1),0) as PreLev1,
                COALESCE(SUM(eikendetails.Lev2),0) as Lev2,
                COALESCE(SUM(eikendetails.PreLev2),0) as PreLev2,
                COALESCE(SUM(eikendetails.Lev3),0) as Lev3,
                COALESCE(SUM(eikendetails.Lev4),0) as Lev4,
                COALESCE(SUM(eikendetails.Lev5),0) as  Lev5,
                COALESCE(Lv1,0) as Lv1,
                COALESCE(PreLv1,0) as PreLv1,
                COALESCE(Lv2,0) as Lv2,
                COALESCE(PreLv2,0) as PreLv2,
                COALESCE(Lv3,0) as Lv3,
                COALESCE(Lv4,0) as Lv4,
                COALESCE(Lv5,0) as Lv5,
                org.OrganizationNo,eikenmaster.`Status`,               
                COALESCE(totalPupil,0) as PupilNo,               
                SUM(CASE WHEN eikendetails.HallType=1 THEN (COALESCE(eikendetails.Lev1,0)+COALESCE(eikendetails.PreLev1,0)+COALESCE(eikendetails.Lev2,0)+COALESCE(eikendetails.PreLev2,0)+COALESCE(eikendetails.Lev3,0)+COALESCE(eikendetails.Lev4,0)+COALESCE(eikendetails.Lev5,0)) ELSE 0 END) AS MainHall,
                SUM(CASE WHEN eikendetails.HallType=0 THEN (COALESCE(eikendetails.Lev1,0)+COALESCE(eikendetails.PreLev1,0)+COALESCE(eikendetails.Lev2,0)+COALESCE(eikendetails.PreLev2,0)+COALESCE(eikendetails.Lev3,0)+COALESCE(eikendetails.Lev4,0)+COALESCE(eikendetails.Lev5,0)) ELSE 0 END) AS StandardHall";        
        $subQuery="SELECT 
                    personal.OrganizationId as OrgId,applylevel.EikenScheduleId, 
                    SUM(CASE WHEN applylevel.EikenLevelId=1 THEN 1 ELSE 0 END) AS Lv1,
                    SUM(CASE WHEN applylevel.EikenLevelId=2 THEN 1 ELSE 0 END) AS PreLv1,
                    SUM(CASE WHEN applylevel.EikenLevelId=3 THEN 1 ELSE 0 END) AS Lv2,
                    SUM(CASE WHEN applylevel.EikenLevelId=4 THEN 1 ELSE 0 END) AS PreLv2,
                    SUM(CASE WHEN applylevel.EikenLevelId=5 THEN 1 ELSE 0 END) AS Lv3,
                    SUM(CASE WHEN applylevel.EikenLevelId=6 THEN 1 ELSE 0 END) AS Lv4,
                    SUM(CASE WHEN applylevel.EikenLevelId=7 THEN 1 ELSE 0 END) AS Lv5,
                    COUNT(applylevel.EikenLevelId) AS totalPupil
                    FROM  ApplyEikenLevel applylevel
                    LEFT JOIN ApplyEikenPersonalInfo personal ON personal.id = applylevel.ApplyEikenPersonalInfoId
                    WHERE applylevel.IsDelete=0 AND personal.IsDelete=0 AND applylevel.IsSateline=1
                    GROUP BY personal.OrganizationId,applylevel.EikenScheduleId";
       
       $query=" SELECT ".$select." 
                FROM  Organization AS org 
                INNER JOIN ApplyEikenOrg AS eikenmaster  ON eikenmaster.OrganizationId=org.id 
                LEFT JOIN ApplyEikenOrgDetails AS eikendetails ON eikenmaster.id=eikendetails.ApplyEikenOrgId 
                LEFT JOIN EikenSchedule AS eikenschedule ON eikenschedule.id=eikenmaster.EikenScheduleId 
                LEFT JOIN (".$subQuery.") as  satelite ON satelite.OrgId=eikenmaster.OrganizationId AND satelite.EikenScheduleId=eikenmaster.EikenScheduleId 
                WHERE ".$conditionQuery." GROUP BY eikenmaster.id) AS items WHERE " . $conditionSubQuery."  ORDER BY OrganizationNo,OrgNameKanji";  
        $rsm = new ResultSetMapping();
       $rsm->addEntityResult('\Application\Entity\Organization', 'org');
       $rsm->addScalarResult('Email', 'Email');
       $rsm->addScalarResult('TelNo', 'TelNo');
       $rsm->addScalarResult('OrganizationNo', 'OrganizationNo');
        $rsm->addScalarResult('OrgNameKanji', 'OrgNameKanji');
        $rsm->addScalarResult('Status', 'Status');
        $rsm->addScalarResult('PupilNo', 'PupilNo');
        $rsm->addScalarResult('MainHall', 'MainHall');
        $rsm->addScalarResult('StandardHall', 'StandardHall');

        $myqb = $em->createNativeQuery($query, $rsm);
         $myqb->setParameter(":year", $currentYear);       
        if ($kai != 0) {
            $myqb->setParameter(':kai', $kai);
        }
        if ($orgNo) {
            $myqb->setParameter(':orgNo','%' . trim($orgNo) . '%' );
        }
        if ($orgName) {
            $myqb->setParameter(':orgName','%' . trim($orgName) . '%' );
        }       
         if ($status) {
            if($status=="DRAFT"){                
                $myqb->setParameter(':draft', $draft);
            }else{
               $myqb->setParameter(':submitted', $submitted);
            }
        }else{
             $myqb->setParameter(':draft', $draft);
             $myqb->setParameter(':submitted', $submitted);
        }          

        $paginator = new DTPaginator($myqb);
        return $paginator;
    }

    public function getListOrganizations($listId){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('o.id as organizationId, o.organizationNo, o.orgNameKanji')
            ->from('\Application\Entity\Organization', 'o', 'o.id')
            ->where('o.isDelete = 0')
            ->andWhere('o.id IN (:listId)')
            ->orderBy('o.id')
            ->setParameter(':listId', $listId);

        return $query->getQuery()->getArrayResult();
    }
    
    public function getDantaiMasterData() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('organization.organizationNo')
            ->from('\Application\Entity\Organization', 'organization');

        $query = $qb->getQuery();

        $result = $query->getArrayResult();
        return $result;
    }

    /**
     * @param $listDantai
     * @return mixed
     */
    public function insertDantaiMasterData($listDantai)
    {
        if (empty($listDantai)) {
            return false;
        }

        $em = $this->getEntityManager();
        $headers = array(
            'organizationNo',
            'organizationCode',
            'flagRegister',
            'examLocation',
            'orgNameKanji',
            'orgNameKana',
            'department',
            'officerName',
            'email',
            'townCode',
            'address1',
            'address2',
            'telNo',
            'fax',
            'cityId',
            'cityCode',
            'stateCode',
            'schoolDivision',
            'passcode',
            'status',
            'isDelete',
        );

        // create sql data for insert.
        $sqlData = '';
        foreach ($listDantai as $item) {
            $cityId = $item['cityId'] != 0 ?  intval($item['cityId']) : 'NULL';
            $flagRegister = $item['flagRegister'] != 0 ?  intval($item['flagRegister']) : 'NULL';
            $schoolDivision = $item['schoolDivision'] != 0 ?  intval($item['schoolDivision']) : 'NULL';
            $sqlData .= ", ("
                . "'" . mysql_escape_string($item['organizationNo']) . "'"
                . ", '" . mysql_escape_string($item['organizationCode']) . "'"
                . ", " . $flagRegister
                . ", '" . mysql_escape_string($item['examLocation']) . "'"
                . ", '" . mysql_escape_string($item['orgNameKanji']) . "'"
                . ", '" . mysql_escape_string($item['orgNameKana']) . "'"
                . ", '" . mysql_escape_string($item['department']) . "'"
                . ", '" . mysql_escape_string($item['officerName']) . "'"
                . ", '" . mysql_escape_string($item['email']) . "'"
                . ", '" . mysql_escape_string($item['townCode']) . "'"
                . ", '" . mysql_escape_string($item['address1']) . "'"
                . ", '" . mysql_escape_string($item['address2']) . "'"
                . ", '" . mysql_escape_string($item['telNo']) . "'"
                . ", '" . mysql_escape_string($item['fax']) . "'"
                . ", " . $cityId 
                . ", '" . mysql_escape_string($item['cityCode']) . "'"
                . ", '" . mysql_escape_string($item['stateCode']) . "'"
                . ", " . $schoolDivision
                . ", '" . mysql_escape_string($item['passcode']) . "'"
                . ", 'Enable'"
                . ', 0'
                . ')';
            
        }
        $sqlData = trim($sqlData, ",");
        
        // create sql columns.
        $sqlColumn = implode(",", $headers);

        $tableName = $em->getClassMetadata('Application\Entity\Organization')->getTableName();

        // create insert sql from data and columns.
        $sql = 'INSERT INTO ' . $tableName . ' (' . $sqlColumn . ') VALUES ' . $sqlData;
        
        return $em->getConnection()->executeUpdate($sql);
    }

    public function updateDantaiMasterData($listDantai)
    {
        if (empty($listDantai)) {
            return false;
        }
        $em = $this->getEntityManager();

        // create sql data for insert.
        $sqlSetOrgCode = $sqlSetFlagRegister = $sqlSetExamLocation = $sqlSetOrgNameKanji = $sqlSetOrgNameKanji = "";
        $sqlSetOrgNameKana = $sqlSetDepartment = $sqlSetOfficer = $sqlSetEmail = $sqlSetTownCode = "";
        $sqlSetAddress1 = $sqlSetAddress2 = $sqlSetTelNo = $sqlSetFax = $sqlSetCityId = $sqlSetCityCode = "";
        $sqlSetStateCode = $sqlSetSchoolDivision = $sqlSetPassCode = "";
        $listOrgNo = '';
        foreach ($listDantai as $item) {
            $cityId = $item['cityId'] ? intval($item['cityId']) : 'NULL';
            $flagRegister = $item['cityId'] ? intval($item['flagRegister']) : 'NULL';
            $schoolDivision = $item['cityId'] ? intval($item['schoolDivision']) : 'NULL';
            
            $sqlSetOrgCode .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['organizationCode']) . "'";
            $sqlSetFlagRegister .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN " . $flagRegister . " ";
            $sqlSetExamLocation .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['examLocation']) . "'";
            $sqlSetOrgNameKanji .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['orgNameKanji']) . "'";
            $sqlSetOrgNameKana .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['orgNameKana']) . "'";
            $sqlSetDepartment .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['department']) . "'";
            $sqlSetOfficer .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['officerName']) . "'";
            $sqlSetEmail .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['email']) . "'";
            $sqlSetTownCode .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['townCode']) . "'";
            $sqlSetAddress1 .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['address1']) . "'";
            $sqlSetAddress2 .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['address2']) . "'";
            $sqlSetTelNo .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['telNo']) . "'";
            $sqlSetFax .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['fax']) . "'";
            $sqlSetCityId .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN " . $cityId  . " ";
            $sqlSetCityCode .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['cityCode']) . "'";
            $sqlSetStateCode .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['stateCode']) . "'";
            $sqlSetSchoolDivision .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN " . $schoolDivision . " ";
            $sqlSetPassCode .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['passcode']) . "'";
            if(empty($listOrgNo)){
                $listOrgNo = "'".$item['organizationNo']."'";
            }else{
                $listOrgNo .= ','."'".$item['organizationNo']."'";
            }
        }

        // create params.
        $tableName = $em->getClassMetadata('Application\Entity\Organization')->getTableName();
        $time = date('Y-m-d H:i:s');

        // create insert sql from data and columns.
        $sql = "UPDATE " . $tableName . " org
                SET org.organizationCode = CASE org.organizationNo " . $sqlSetOrgCode . " END,
                    org.flagRegister = CASE org.organizationNo " . $sqlSetFlagRegister . " END,
                    org.examLocation = CASE org.organizationNo " . $sqlSetExamLocation . " END,
                    org.orgNameKanji = CASE org.organizationNo " . $sqlSetOrgNameKanji . " END,
                    org.orgNameKana = CASE org.organizationNo " . $sqlSetOrgNameKana . " END,
                    org.department = CASE org.organizationNo " . $sqlSetDepartment . " END,
                    org.officerName = CASE org.organizationNo " . $sqlSetOfficer . " END,
                    org.email = CASE org.organizationNo " . $sqlSetEmail . " END,
                    org.townCode = CASE org.organizationNo " . $sqlSetTownCode . " END,
                    org.address1 = CASE org.organizationNo " . $sqlSetAddress1 . " END,
                    org.address2 = CASE org.organizationNo " . $sqlSetAddress2 . " END,
                    org.telNo = CASE org.organizationNo " . $sqlSetTelNo . " END,
                    org.fax = CASE org.organizationNo " . $sqlSetFax . " END,
                    org.cityId = CASE org.organizationNo " . $sqlSetCityId . " END,
                    org.cityCode = CASE org.organizationNo " . $sqlSetCityCode . " END,
                    org.stateCode = CASE org.organizationNo " . $sqlSetStateCode . " END,
                    org.schoolDivision = CASE org.organizationNo " . $sqlSetSchoolDivision . " END,
                    org.passcode = CASE org.organizationNo " . $sqlSetPassCode . " END,
                    org.status = 'Enable',
                    org.isDelete = 0,
                    org.updateAt = '".$time."'
                WHERE org.organizationNo IN (" . $listOrgNo . ")";
        
        return $em->getConnection()->executeUpdate($sql);
    }
    
}