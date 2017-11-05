<?php
namespace Application\Entity\Repository;

use Dantai\DantaiConstants;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\ApplyEikenLevel;
use Application\Entity\ApplyEikenPersonalInfo;
use Doctrine\ORM\Query\Expr\GroupBy;
use Application\Entity\Pupil;
use Zend\Http\Header\IfMatch;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Composer\Autoload\ClassLoader;
use Application\Entity\EikenLevel;
use Eiken\Helper\NativePaginator as DTPaginator;

/**
 *
 * @author LangDD
 *
 */
class ApplyEikenLevelRepository extends EntityRepository
{

    /**
     *
     * @param unknown $orgId
     * @param number $limit
     * @param number $offset
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getPagedApplyEikenLevel($orgId, $eikenLevel, $isHallMaain = true, $eikenScheduleId, $limit = 10, $offset = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('applyeikenlevel')
            ->from('\Application\Entity\ApplyEikenLevel', 'applyeikenlevel')
            ->join('applyeikenlevel.applyEikenPersonalInfo', 'applyEikenPersonalInfo')
            ->join('applyEikenPersonalInfo.organization', 'organization')
            ->join('applyEikenPersonalInfo.eikenSchedule', 'eikenSchedule')    
            ->where('applyeikenlevel.eikenLevel = :eikenLevel_id')
            ->andWhere('eikenSchedule.id = :eiken_schedule_id')
            ->andWhere('organization.id = :organization_id')
            ->setParameter(':eikenLevel_id', $eikenLevel)
            ->setParameter(':organization_id', $orgId)
            ->setParameter(':eiken_schedule_id', $eikenScheduleId)
            ->andWhere('applyeikenlevel.isDelete = 0')
            ->orderBy('applyeikenlevel.isRegister', 'ASC')

            ->addOrderBy('applyeikenlevel.id', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        // Fix issue F1GJIEM-1641
        if (! $isHallMaain && $eikenLevel != 1 && $eikenLevel != 2) {
            $qb->andWhere('applyeikenlevel.isSateline = :isSateline')->setParameter(':isSateline', 0);
        }
        $query = $qb->getQuery();

        $paginator = new Paginator($query);

        return $paginator;
    }

    /**
     * function search data pupil payment, from all condition, return result
     *
     * @author
     * @param
     *
     * @return data of view
     *         Author Modified Start date End date
     *          Creates 2015-07-11 2015-07-11
     */
    public function getPagedPaymentStatus($orgId, $eikenLevelId = 0, $classId = 0, $schoolyearId = 0, $paymentStatus = null, $applyStatus = null, $fullName = null, $ddlYear = 0, $testSite = null, $eikenScheduleId = 0)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $expr = $qb->expr();
        $query = $qb->select('(CONCAT(COALESCE(pupil.firstNameKanji,\'\'), COALESCE(pupil.lastNameKanji,\'\')) AS fullName, pupil.id, pupil.number,
								classj.className, orgschoolyear.displayName,pupil.id as pupilId,
								applyeikenlevel.id as applyId, applyeikenlevel.eikenScheduleId, applyeikenlevel.eikenLevelId, applyeikenlevel.pupilId,
								applyeikenlevel.paymentBy, applyeikenlevel.paymentDate, COALESCE(applyeikenlevel.paymentStatus, 0) as paymentStatus,
								applyeikenlevel.regDateOnSatellite, COALESCE(applyeikenlevel.isRegister,0) as registerStatus,
                                eikenschedule.year, eikenschedule.kai, applyeikenlevel.isDelete , eikenLevel.levelName , applyeikenlevel.hallType, recommendLevel.eikenLevelId as recommendEikenLevelId')
            ->from('\Application\Entity\Pupil', 'pupil')
            ->leftJoin('\Application\Entity\ClassJ', 'classj', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.classId = classj.id')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'orgschoolyear', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.orgSchoolYearId = orgschoolyear.id')
            ->leftJoin('\Application\Entity\ApplyEikenLevel', 'applyeikenlevel', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.id = applyeikenlevel.pupilId AND applyeikenlevel.eikenScheduleId = :eikenScheduleId AND applyeikenlevel.isDelete = 0')
            ->leftJoin('\Application\Entity\EikenLevel', 'eikenLevel', \Doctrine\ORM\Query\Expr\Join::WITH, 'eikenLevel.id = applyeikenlevel.eikenLevelId')
            ->leftJoin('\Application\Entity\EikenSchedule', 'eikenschedule', \Doctrine\ORM\Query\Expr\Join::WITH, ' applyeikenlevel.eikenScheduleId = eikenschedule.id')
            ->leftJoin('\Application\Entity\RecommendLevel', 'recommendLevel', \Doctrine\ORM\Query\Expr\Join::WITH, ' recommendLevel.pupilId = pupil.id AND recommendLevel.eikenScheduleId = :eikenScheduleId')
            ->where('pupil.isDelete = 0')
            ->andWhere('pupil.organizationId = :orgId')
            ->andWhere('pupil.year = :pupilyear')
            ->orderBy('orgschoolyear.schoolYearId', 'ASC')
            ->addOrderBy('classj.className', 'ASC')
            ->addOrderBy('pupil.number', 'ASC')
            ->setParameter(':orgId', $orgId)
            ->setParameter(':pupilyear', $ddlYear)
            ->setParameter(':eikenScheduleId', intval($eikenScheduleId));
        if ($eikenLevelId != 0) {
            $query->andWhere('applyeikenlevel.eikenLevelId = :eikenlevelid')->setParameter(':eikenlevelid', $eikenLevelId);
        }
        if ($classId != 0) {
            $query->andWhere('classj.id = :classid')->setParameter(':classid', $classId);
        }
        if ($schoolyearId != 0) {
            $query->andWhere('orgschoolyear.id = :schoolyearid')->setParameter(':schoolyearid', $schoolyearId);
        }
        if ($paymentStatus == 1) {
            $query->andWhere('applyeikenlevel.paymentStatus = 1');
        }
        else if ($paymentStatus === '0') {
            $query->andWhere('applyeikenlevel.paymentStatus = 0 or applyeikenlevel.paymentStatus IS NULL');
        }
        if ($applyStatus == 1) {
            $query->andWhere('applyeikenlevel.isRegister = 1');
        }
        else if ($applyStatus === '0') {
            $query->andWhere('applyeikenlevel.isRegister IS NULL or applyeikenlevel.isRegister = 0');
        }
        if ($testSite == 1) {
            $query->andWhere('applyeikenlevel.hallType = 1');
        }
        if ($testSite === '0') {
            $query->andWhere('applyeikenlevel.hallType = 0');
        }
        if ($fullName != null) {
            $query->andWhere('CONCAT(COALESCE(pupil.firstNameKanji,\'\'), COALESCE(pupil.lastNameKanji,\'\')) like :fullname')->setParameter(':fullname', "%" . $fullName . "%");
        }
        
        $paginator = new DTPaginator($query, 'DoctrineORMQueryBuilder');
        return $paginator;
    }

    /**
     * function search data pupil payment, from all condition, return result
     *
     * @author
     * @param
     *
     * @return data of view
     *         Author Modified Start date End date
     *          Creates 2015-07-11 2015-07-11
     */
    public function getListPaymentStatusByEikenLevel($orgId = 0, $eikenScheduleId = 0, $eikenLevelId = 0, $limit = 20, $offset = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("applyeikenlevel")
            ->from('\Application\Entity\ApplyEikenLevel', 'applyeikenlevel')
            ->leftJoin('applyeikenlevel.applyEikenPersonalInfo', 'personal')
            ->leftJoin('personal.class', 'cl')
            ->leftJoin('personal.orgSchoolYear', 'schoolyear')
            ->where('personal.organizationId = :organizationId')
            ->andWhere('personal.eikenScheduleId= :eikenScheduleId')
            ->andWhere('applyeikenlevel.eikenLevelId = :eikenLevelId')
            ->setParameter(':organizationId', $orgId)
            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->setParameter(':eikenLevelId', $eikenLevelId)
            ->andWhere('applyeikenlevel.isDelete = 0')
            ->andWhere('personal.isSateline = 1')
            ->orderBy('personal.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator;
    }

    /**
     * function get details data pupil, return result
     *
     * @author
     * @param
     *
     * @return data of view
     *         Author Modified Start date End date
     *          Creates 2015-07-11 2015-07-11
     */
    public function getDetailsPaymentStatus($id = 0, $orgId = 0, $eikenScheduleId = 0,$applyId=0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $pageSize = 20;
        $qb->select('
            Pupil.id,
            Pupil.firstNameKanji,
            Pupil.lastNameKanji,
            Pupil.firstNameKana,
            Pupil.lastNameKana,
            Pupil.number,
            eikenlevel2.levelName as RecommendName,
            eikenlevel1.levelName as ApplyLevel,
            ApplyEikenLevel.paymentBy,
           ApplyEikenLevel.paymentStatus,
            ApplyEikenLevel.paymentDate,
            ApplyEikenLevel.isSubmit,
            ApplyEikenLevel.regDateOnSatellite,
            ApplyEikenLevel.isRegister,
            ClassJ.className,
            ApplyEikenLevel.id as applyEikenLevelId,
            ApplyEikenPersonalInfo.firstNameKana as applyFirstNameKana,
            ApplyEikenPersonalInfo.lastNameKana as applyLastNameKana,
            ApplyEikenPersonalInfo.firstNameKanji as applyFirstNameKanji,
            ApplyEikenPersonalInfo.lastNameKanji as applyLastNameKanji,
            OrgSchoolYear.displayName')
            ->from('\Application\Entity\Pupil', 'Pupil')
            ->leftJoin('\Application\Entity\ApplyEikenLevel', 'ApplyEikenLevel', \Doctrine\ORM\Query\Expr\Join::WITH, 'ApplyEikenLevel.pupilId=Pupil.id AND ApplyEikenLevel.eikenScheduleId = :eikenScheduleId AND ApplyEikenLevel.id = :applyId')
            ->leftJoin('\Application\Entity\ApplyEikenPersonalInfo', 'ApplyEikenPersonalInfo', \Doctrine\ORM\Query\Expr\Join::WITH, 'ApplyEikenPersonalInfo.pupilId=Pupil.id AND ApplyEikenPersonalInfo.eikenScheduleId = :eikenScheduleId')
            ->leftJoin('\Application\Entity\ClassJ', 'ClassJ', \Doctrine\ORM\Query\Expr\Join::WITH, 'ClassJ.id=Pupil.classId')

            ->leftJoin('\Application\Entity\OrgSchoolYear', 'OrgSchoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'OrgSchoolYear.id=Pupil.orgSchoolYearId')
            ->leftJoin('\Application\Entity\RecommendLevel', 'RecommendLevel', \Doctrine\ORM\Query\Expr\Join::WITH, 'Pupil.id=RecommendLevel.pupilId ')
            ->leftJoin('\Application\Entity\EikenLevel', 'eikenlevel1', \Doctrine\ORM\Query\Expr\Join::WITH, 'ApplyEikenLevel.eikenLevelId=eikenlevel1.id ')
            ->leftJoin('\Application\Entity\EikenLevel', 'eikenlevel2', \Doctrine\ORM\Query\Expr\Join::WITH, 'RecommendLevel.eikenLevelId=eikenlevel2.id ')

            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->setParameter(':applyId', $applyId)
            ->where('Pupil.id = :pupilId')
            ->andWhere('Pupil.organizationId = :orgId')    
            ->setParameter(':pupilId', $id)
            ->setParameter(':orgId', $orgId)
            ->andWhere('Pupil.isDelete = 0')
            ->setMaxResults(1);
        $query = $qb->getQuery();
        $details = $query->getOneOrNullResult();
        return $details;
    }

    /**
     * function count pupil by eiken level, from all condition, return result
     *
     * @author
     * @param
     *
     * @return data of view
     *         Author Modified Start date End date
     *          Creates 2015-07-11 2015-07-11
     */
    public function getCountPaymentStatusByEikenLevel($orgId = 0, $eikenScheduleId = 0)
    {
        $em = $this->getEntityManager();
        $quantity = $this->getCountPaymentStatus($orgId, $eikenScheduleId, 0, true);
        $quantityPayment = $this->getCountPaymentStatus($orgId, $eikenScheduleId, 1);
        $qbEikenLevel = $em->createQueryBuilder();
        $qbEikenLevel->select('eikenLevel.id, eikenLevel.levelName')
                ->from('\Application\Entity\EikenLevel', 'eikenLevel')
                ->where('eikenLevel.isDelete = 0')
                ->orderBy('eikenLevel.id', 'DESC');
        $queryEikenLevel = $qbEikenLevel->getQuery();
        $eikenLevelData = $queryEikenLevel->getArrayResult();
        foreach($eikenLevelData as $key => $level){
            $eikenLevelData[$key]['quantity'] = !empty($quantity[$level['id']]) ? $quantity[$level['id']] : 0;
            $eikenLevelDataPayment[$key]['quantity'] = !empty($quantityPayment[$level['id']]) ? $quantityPayment[$level['id']] : 0;
        }
        $eikenLevelData = array(
            'eikenLevelData' =>$eikenLevelData,
            'eikenLevelDataPayment' =>$eikenLevelDataPayment,
        );
        return $eikenLevelData;
    }

    public function getCountPaymentStatus($orgId = 0, $eikenScheduleId = 0,$hasPayment = 0, $isRegister = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('applylevel.eikenLevelId, count(applylevel.id) AS quantity , applylevel.paymentStatus')
                ->from('\Application\Entity\ApplyEikenLevel', 'applylevel')
                ->leftJoin('\Application\Entity\ApplyEikenPersonalInfo', 'personal', \Doctrine\ORM\Query\Expr\Join::WITH, 'personal.id = applylevel.applyEikenPersonalInfoId')
                ->leftJoin('\Application\Entity\EikenSchedule', 'eikenSchedule', \Doctrine\ORM\Query\Expr\Join::WITH, 'eikenSchedule.id = personal.eikenScheduleId')
                ->where('personal.organizationId = :orgId')
                ->andWhere('applylevel.isDelete = 0')
                ->andWhere('personal.isDelete = 0')
                ->andWhere('eikenSchedule.isDelete = 0')
                ->setParameter(':orgId', intval($orgId))
                ->groupBy('applylevel.eikenLevelId');
        if ($eikenScheduleId != 0){
            $qb->andWhere('eikenSchedule.id = '.intval($eikenScheduleId));
        }
        if($hasPayment != 0){
            $qb->andWhere('applylevel.paymentStatus = 1');
        }
        if($isRegister){
            $qb->andWhere('applylevel.isRegister = 1');
        }

        $query = $qb->getQuery();
        $applyEikenData = $query->getArrayResult();
        $quantity = array();
        if($applyEikenData){
            foreach($applyEikenData as $applyEiken){
                $quantity[$applyEiken['eikenLevelId']] = intval($applyEiken['quantity']);
            }
        }
        return $quantity;
    }

    /**
     * DuongTD
     * Get Data for Eiken Application Form
     */
    public function getApplyEikenPersonal($orgId = 0, $eikenScheduleId = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('eikenLevel.id, eikenLevel.levelName, applyeikenLevel.isSateline, count(applyeikenLevel.id) as total,
            sum(CASE applyeikenLevel.isDelete WHEN 1 THEN 1 ELSE 0 END) as totalDeleted,
            sum(CASE applyeikenLevel.isRegister WHEN 1 THEN CASE applyeikenLevel.isDelete WHEN 1 THEN 0 ELSE 1 END ELSE 0 END) as totalRegister')
            ->from('\Application\Entity\ApplyEikenLevel', 'applyeikenLevel')
            ->join('applyeikenLevel.eikenSchedule', 'eikenSchedule')
            ->join('applyeikenLevel.applyEikenPersonalInfo', 'eikenPerson')
            ->join('eikenPerson.organization', 'organization')
            ->join('applyeikenLevel.eikenLevel', 'eikenLevel')
            ->where('organization.id = :organization_id')
            ->andwhere('applyeikenLevel.isDelete = 0')
            ->andwhere('eikenSchedule.id = :eikenSchedule_id')
            ->setParameter(':organization_id', $orgId)
            ->setParameter(':eikenSchedule_id', $eikenScheduleId)
            ->groupBy('eikenLevel.id, applyeikenLevel.isSateline')
            ->orderBy('eikenLevel.id', 'ASC');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getTotalKyuPaymentInfo($orgId, $eikenScheduleId, $hallType = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $expr = $qb->expr();
        $qb->select('a.id,a.eikenScheduleId,a.eikenLevelId,count(a.eikenLevelId) as totalKyu')
            ->from('\Application\Entity\ApplyEikenLevel', 'a', 'a.eikenLevelId')
            ->join('\Application\Entity\ApplyEikenPersonalInfo', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = a.applyEikenPersonalInfoId')
            ->where('p.organizationId = :organizationId')
            ->andwhere('a.eikenScheduleId = :eikenScheduleId')
            ->andwhere('a.paymentStatus = 1')
            ->groupBy('a.eikenLevelId')
            ->setParameter(':organizationId', (int)$orgId)
            ->setParameter(':eikenScheduleId', (int)$eikenScheduleId)
            ->join('\Application\Entity\InvitationSetting', 'invitation', \Doctrine\ORM\Query\Expr\Join::WITH, 'invitation.organizationId = p.organizationId')
            ->andwhere('invitation.eikenScheduleId = :eikenScheduleId');

        if($hallType !== null){
            $qb->andWhere($expr->orX('a.hallType = :hallType',
                $expr->andX($expr->isNull('a.hallType'), 'invitation.hallType = :hallType')));
            $qb->setParameter(':hallType', $hallType);
        }
        
        return $qb->getQuery()->getArrayResult();
    }
    /**
     *
     * @param int $id
     * @param int $eikLevelId
     * @author LangDD
     * @uses Check if the the pupil can apply Eiken for a Eiken Level
     */
    public function checkValidEikenLevel($eikenId, $eikLevelId, $applyEikenLevelId = false, $eikenSchedule)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('applyeikenlevel')
            ->from('\Application\Entity\ApplyEikenLevel', 'applyeikenlevel')
            ->join('applyeikenlevel.applyEikenPersonalInfo', 'applyEikenPersonalInfo')
            ->join('applyEikenPersonalInfo.eikenSchedule', 'eikenschedule')         
            ->where('applyEikenPersonalInfo.eikenId = :eiken_id')
            ->andWhere('eikenschedule.id = :eikenschedule_id')
            ->setParameter('eikenschedule_id', $eikenSchedule)
            ->setParameter('eiken_id', $eikenId)
            ->andWhere('applyeikenlevel.isRegister = 1')
            ->andWhere('applyeikenlevel.isDelete = 0');

        if ($applyEikenLevelId) {
            $qb->andWhere('applyeikenlevel.id != :applyeikenlevel_id');
            $qb->setParameter('applyeikenlevel_id', $applyEikenLevelId);
        }

        $eikAppLevels = $qb->getQuery()->getResult();

        if (empty($eikAppLevels))
            return true;
        elseif (count($eikAppLevels) >= 2)
            return false;
        else {
            foreach ($eikAppLevels as $eikAppLevel) {
                if (abs($eikLevelId - $eikAppLevel->getEikenLevel()->getId()) != 1)
                    return false;
                else
                    return true;
            }
        }
    }

    public function getListApplyEikenLevel($orgId, $eikenScheduleId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('applyeikenlevel')
            ->from('\Application\Entity\ApplyEikenLevel', 'applyeikenlevel')
            ->join('applyeikenlevel.applyEikenPersonalInfo', 'applyEikenPersonalInfo')
            ->where(
                '(
                    (applyeikenlevel.isDelete = 0 OR applyeikenlevel.isDelete is null)
                    AND
                    (applyeikenlevel.isSubmit = 0 OR applyeikenlevel.isSubmit is null)
                )
                OR
                (
                    applyeikenlevel.isDelete = 1 AND applyeikenlevel.isSubmit = 1
                    AND
                    (applyeikenlevel.isCancel = 0 OR applyeikenlevel.isCancel is null)
                )
                OR
                (
                    (applyeikenlevel.oldEikenId is not null and applyeikenlevel.oldEikenId != \'\'
                        AND applyeikenlevel.isSubmit = 1
                        AND (applyeikenlevel.isDelete = 0))
                )')
            ->andWhere('applyeikenlevel.eikenScheduleId = :eikenScheduleId')
            ->andWhere('applyEikenPersonalInfo.organizationId = :organizationId')
            ->andWhere('applyeikenlevel.hallType = 1')
            ->andWhere('applyeikenlevel.isRegister = 1')
            ->setParameter(':organizationId', $orgId)
            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->groupBy('applyeikenlevel.id');

        return $qb->getQuery()->getResult();
    }

    public function getApplyEikLevelDetail ($id, $orgId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('applyeikenlevel')
            ->from('\Application\Entity\ApplyEikenLevel', 'applyeikenlevel')
            ->join('applyeikenlevel.applyEikenPersonalInfo', 'applyEikenPersonalInfo')
            ->join('applyEikenPersonalInfo.organization', 'organization')
            ->where('organization.id = :organization_id')
            ->andWhere('applyeikenlevel.id = :appeiklevel_id')
            ->setParameter(':organization_id', $orgId)
            ->setParameter(':appeiklevel_id', $id)
            ->andWhere('applyeikenlevel.isDelete = 0');

        return $query = $qb->getQuery()->getOneOrNullResult();
    }
    
    public function getApplyEikLevelByInfoId ($appEikInfoId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('applyeikenlevel')
            ->from('\Application\Entity\ApplyEikenLevel', 'applyeikenlevel')
            ->where('applyeikenlevel.applyEikenPersonalInfoId = :info_id')
            ->setParameter(':info_id', $appEikInfoId)
            ->andWhere('applyeikenlevel.isDelete = 0');

        return $qb->getQuery()->getResult();
    }
    public function checkValidEikenIdForOrg ($refEikenId, $scheduleId, $orgId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('applyEikenPersonalInfo.organizationId')
            ->from('\Application\Entity\ApplyEikenLevel', 'applyeikenlevel')
            ->join('applyeikenlevel.applyEikenPersonalInfo', 'applyEikenPersonalInfo')
            ->join('applyEikenPersonalInfo.eikenSchedule', 'eikenschedule')
            ->where('applyEikenPersonalInfo.eikenId = :eiken_id')
            ->andWhere('eikenschedule.id = :eikenschedule_id')
            ->setParameter(':eiken_id', $refEikenId)
            ->setParameter(':eikenschedule_id', $scheduleId)
            ->andWhere('applyeikenlevel.isDelete = 0')
            ->setMaxResults(1);

        $eikAppLevel = $qb->getQuery()->getOneOrNullResult();
        if (!empty($eikAppLevel) && $eikAppLevel['organizationId'] != $orgId)
        {
            return false;
        }
        return true;
    }
    /**
     * @author taivh
     * @param number $orgId
     * @param number $eikenScheduleId
     * @param unknown $curentDate
     */
    public function getTotalApplyEikenByOrgId($orgId = 0, $eikenScheduleId = 0, $curentDate)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("count(applyEikenLevel.id) as total")
        ->from('\Application\Entity\ApplyEikenLevel', 'applyEikenLevel')
        ->innerJoin('\Application\Entity\ApplyEikenPersonalInfo', 'applyEikenPerInfo', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'applyEikenLevel.applyEikenPersonalInfoId = applyEikenPerInfo.id')     
        ->where('applyEikenPerInfo.organizationId = :orgId')
        ->andWhere('applyEikenPerInfo.eikenScheduleId = :eikenScheduleId')
        ->andWhere('applyEikenLevel.insertAt < :currentDate')
        ->setParameter(':orgId', $orgId)
        ->setParameter(':eikenScheduleId', $eikenScheduleId)
        ->setParameter(':currentDate', $curentDate)//Chú ý current nếu hàm ko chạy
        ->andWhere('applyEikenLevel.isDelete = 0')
        ->andWhere('applyEikenLevel.isRegister = 1');
        return $qb->getQuery()->getArrayResult();
    }
    /**
     * @author hoangnk
     * @param number $pupilId
     * @param number $eikenScheduleId
     */
    public function getEikenLevelOfPupil($pupilId = 0, $eikenScheduleId = 0, $applyEikenLevelId = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("applyEikenLevel.eikenLevelId")
        ->from('\Application\Entity\ApplyEikenLevel', 'applyEikenLevel')
        ->innerJoin('\Application\Entity\ApplyEikenPersonalInfo', 'applyEikenPerInfo', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'applyEikenLevel.applyEikenPersonalInfoId = applyEikenPerInfo.id')     
        ->where('applyEikenPerInfo.pupilId = :pupilId')
        ->andWhere('applyEikenPerInfo.eikenScheduleId = :eikenScheduleId')
        ->andWhere('applyEikenLevel.eikenScheduleId = :eikenScheduleId')
        ->setParameter(':pupilId', $pupilId)
        ->setParameter(':eikenScheduleId', $eikenScheduleId)
        ->andWhere('applyEikenLevel.isDelete = 0');
        
        if(isset($applyEikenLevelId)){
            $qb->andWhere('applyEikenLevel.id = :applyEikenLevelId');
            $qb->setParameter(':applyEikenLevelId', $applyEikenLevelId);
        }
        return $qb->getQuery()->getArrayResult();
    }
    /**
     * @author hoangnk
     * @param number $pupilId
     * @param number $eikenScheduleId
     */
    public function getNumberStudentApplyEikenBySatellite($orgId = 0, $eikenScheduleId = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("applyEikenLevel.eikenLevelId, applyEikenLevel.hallType, COUNT(applyEikenPerInfo.id) as number")
        ->from('\Application\Entity\ApplyEikenLevel', 'applyEikenLevel')
        ->innerJoin('\Application\Entity\ApplyEikenPersonalInfo', 'applyEikenPerInfo', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'applyEikenLevel.applyEikenPersonalInfoId = applyEikenPerInfo.id')     
        ->where('applyEikenPerInfo.organizationId = :orgId')
        ->andWhere('applyEikenPerInfo.eikenScheduleId = :eikenScheduleId')
        ->andWhere('applyEikenLevel.eikenScheduleId = :eikenScheduleId')    
        ->setParameter(':orgId', $orgId)
        ->setParameter(':eikenScheduleId', $eikenScheduleId)
        ->andWhere('applyEikenLevel.isDelete = 0')
        ->andWhere('applyEikenPerInfo.isDelete = 0')
        ->andWhere('applyEikenLevel.isRegister = 1')
        ->groupBy('applyEikenLevel.eikenLevelId', 'applyEikenLevel.hallType');
        return $qb->getQuery()->getArrayResult();
    }
    
    public function getEikenLevel($pupilId = 0, $eikenScheduleId = 0, $applyEikenLevelId = null, $paymentStatus = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("applyEikenLevel.eikenLevelId,applyEikenLevel.tuitionFee, applyEikenLevel.hallType")
        ->from('\Application\Entity\ApplyEikenLevel', 'applyEikenLevel')
        ->innerJoin('\Application\Entity\ApplyEikenPersonalInfo', 'applyEikenPerInfo', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'applyEikenLevel.applyEikenPersonalInfoId = applyEikenPerInfo.id')     
        ->where('applyEikenPerInfo.pupilId = :pupilId')
        ->andWhere('applyEikenPerInfo.eikenScheduleId = :eikenScheduleId')
        ->andWhere('applyEikenLevel.eikenScheduleId = :eikenScheduleId')
        ->setParameter(':pupilId', $pupilId)
        ->setParameter(':eikenScheduleId', $eikenScheduleId)
        ->andWhere('applyEikenLevel.isDelete = 0')
        ->andWhere('applyEikenLevel.isRegister = 1');
        
        if(isset($applyEikenLevelId)){
            $qb->andWhere('applyEikenLevel.id = :applyEikenLevelId');
            $qb->setParameter(':applyEikenLevelId', $applyEikenLevelId);
        }
        if(isset($paymentStatus)){
            $qb->andWhere('applyEikenLevel.paymentStatus = :paymentStatus');
            $qb->setParameter(':paymentStatus', $paymentStatus);
        }
        
        return $qb->getQuery()->getArrayResult();
    }
    public function getPaymentInformationMuntilKyu($pupilId , $orgId , $eikenScheduleId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("ApplyEikenLevel.id , ApplyEikenLevel.tuitionFee, ApplyEikenLevel.eikenLevelId, ApplyEikenLevel.hallType,ApplyEikenLevel.paymentStatus , InvitationSetting.examDay , EikenSchedule.sunDate ,EikenSchedule.friDate , EikenSchedule.satDate , EikenSchedule.round2Day1ExamDate, EikenSchedule.round2Day2ExamDate")
        ->from('\Application\Entity\ApplyEikenLevel', 'ApplyEikenLevel')
        ->join('\Application\Entity\EikenSchedule', 'EikenSchedule', \Doctrine\ORM\Query\Expr\Join::WITH, 'EikenSchedule.id = ApplyEikenLevel.eikenScheduleId')     
        ->join('\Application\Entity\InvitationSetting', 'InvitationSetting', \Doctrine\ORM\Query\Expr\Join::WITH, 'InvitationSetting.eikenScheduleId = EikenSchedule.id')     
        ->where('ApplyEikenLevel.pupilId = :pupilId')
        ->andWhere('ApplyEikenLevel.isDelete = 0')
        ->andWhere('ApplyEikenLevel.isRegister = 1')
        ->andWhere('InvitationSetting.organizationId = :orgId')
        ->andWhere('ApplyEikenLevel.eikenScheduleId = :eikenScheduleId')
        ->setParameter(':pupilId', $pupilId)
        ->setParameter(':orgId', $orgId)
        ->setParameter(':eikenScheduleId', $eikenScheduleId)
        ->groupBy('ApplyEikenLevel.id')    
        ->orderBy('ApplyEikenLevel.eikenLevelId', 'ASC');
        return $qb->getQuery()->getArrayResult();
    }

    public function deleteApplyEiken($appEikenId){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->update('\Application\Entity\ApplyEikenLevel', 'applyEikenLevel')
            ->set('applyEikenLevel.isDelete', '1')
            ->where('applyEikenLevel.id = :id')
            ->setParameter(':id', $appEikenId);
        try {
            $query = $qb->getQuery();
            $query->execute();
            return TRUE;
        } catch (Exception $e){
            return FALSE;
        }
    }

    public function getListApplyEikenLevelByListPupidIds($listPupilId){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('applyEikenLevel.pupilId, concat(pupil.firstNameKanji, pupil.lastNameKanji) as name, count(applyEikenLevel.id) as numberApply')
            ->from('\Application\Entity\ApplyEikenLevel', 'applyEikenLevel')
            ->join('\Application\Entity\Pupil', 'pupil', \Doctrine\ORM\Query\Expr\Join::LEFT_JOIN, 'pupil.id = applyEikenLevel.pupilId')
            ->where('applyEikenLevel.pupilId IN (:listPupilId)')
            ->andWhere('applyEikenLevel.isDelete = 0')
            ->setParameter(':listPupilId', $listPupilId)
            ->groupBy('applyEikenLevel.pupilId')
            ->orderBy('applyEikenLevel.pupilId')
            ->having('numberApply > 0');

        return $qb->getQuery()->getArrayResult();
    }

    public function changeStatusAfterSubmit($listApplyEikenLevelId)
    {
        $em = $this->getEntityManager();
        $sql = 'UPDATE \Application\Entity\ApplyEikenLevel a
                SET a.isSubmit = 1, a.isRegister = 1, a.oldEikenId = :oldEikenId,
                    a.isCancel = CASE a.isDelete WHEN 1 THEN 1 ELSE ?1 END
                WHERE a.id IN (:listApplyEikenLevelId)';
        $query = $em->createQuery($sql);
        $query->setParameter('oldEikenId','');
        $query->setParameter(1, null);
        $query->setParameter('listApplyEikenLevelId',$listApplyEikenLevelId);
        return $query->execute();
    }

    public function getArrayPaidApplyEiken($eikenScheduleId){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('info.organizationId,
                     SUM(CASE WHEN apply.eikenLevelId = 1 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainPaidLevel1,
                     SUM(CASE WHEN apply.eikenLevelId = 2 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainPaidLevelPre1,
                     SUM(CASE WHEN apply.eikenLevelId = 3 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainPaidLevel2,
                     SUM(CASE WHEN apply.eikenLevelId = 4 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainPaidLevelPre2,
                     SUM(CASE WHEN apply.eikenLevelId = 5 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainPaidLevel3,
                     SUM(CASE WHEN apply.eikenLevelId = 6 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainPaidLevel4,
                     SUM(CASE WHEN apply.eikenLevelId = 7 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainPaidLevel5,
                     SUM(CASE WHEN apply.eikenLevelId = 3 AND apply.hallType = 0 THEN 1 ELSE 0 END) as standardPaidLevel2,
                     SUM(CASE WHEN apply.eikenLevelId = 4 AND apply.hallType = 0 THEN 1 ELSE 0 END) as standardPaidLevelPre2,
                     SUM(CASE WHEN apply.eikenLevelId = 5 AND apply.hallType = 0 THEN 1 ELSE 0 END) as standardPaidLevel3,
                     SUM(CASE WHEN apply.eikenLevelId = 6 AND apply.hallType = 0 THEN 1 ELSE 0 END) as standardPaidLevel4,
                     SUM(CASE WHEN apply.eikenLevelId = 7 AND apply.hallType = 0 THEN 1 ELSE 0 END) as standardPaidLevel5,
                     SUM(apply.tuitionFee) as totalPaidAmount
                    ')
            ->from('\Application\Entity\ApplyEikenPersonalInfo', 'info', 'info.organizationId')
            ->join('\Application\Entity\ApplyEikenLevel','apply', \Doctrine\ORM\Query\Expr\Join::WITH, 'info.id = apply.applyEikenPersonalInfoId')
            ->where('apply.isDelete = 0')
            ->andWhere('info.isDelete = 0')
            ->andWhere('apply.paymentStatus = 1')
            ->andWhere('apply.eikenScheduleId = :eikenScheduleId')
            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->groupBy('info.organizationId');

        return $qb->getQuery()->getArrayResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Function to get number apply eiken for orgs who not create apply eiken
     * @param $eikenScheduleId
     * @return array
     */
    public function getArrayRegisteredNotApplyEikenOrg($eikenScheduleId){
        $em = $this->getEntityManager();
        $listSubmittedOrg = $em->getRepository('\Application\Entity\ApplyEikenOrg')->getListApplyEikenOrg($eikenScheduleId);

        $qb = $em->createQueryBuilder();
        $qb->select('info.organizationId,
                     SUM(CASE WHEN apply.eikenLevelId = 1 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevel1,
                     SUM(CASE WHEN apply.eikenLevelId = 2 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevelPre1,
                     SUM(CASE WHEN apply.eikenLevelId = 3 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevel2,
                     SUM(CASE WHEN apply.eikenLevelId = 4 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevelPre2,
                     SUM(CASE WHEN apply.eikenLevelId = 5 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevel3,
                     SUM(CASE WHEN apply.eikenLevelId = 6 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevel4,
                     SUM(CASE WHEN apply.eikenLevelId = 7 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevel5,
                     SUM(CASE WHEN apply.eikenLevelId = 3 AND apply.hallType = 0 THEN 1 ELSE 0 END) as standardRegisteredLevel2,
                     SUM(CASE WHEN apply.eikenLevelId = 4 AND apply.hallType = 0 THEN 1 ELSE 0 END) as standardRegisteredLevelPre2,
                     SUM(CASE WHEN apply.eikenLevelId = 5 AND apply.hallType = 0 THEN 1 ELSE 0 END) as standardRegisteredLevel3,
                     SUM(CASE WHEN apply.eikenLevelId = 6 AND apply.hallType = 0 THEN 1 ELSE 0 END) as standardRegisteredLevel4,
                     SUM(CASE WHEN apply.eikenLevelId = 7 AND apply.hallType = 0 THEN 1 ELSE 0 END) as standardRegisteredLevel5
                    ')
            ->from('\Application\Entity\ApplyEikenPersonalInfo', 'info', 'info.organizationId')
            ->join('\Application\Entity\ApplyEikenLevel','apply', \Doctrine\ORM\Query\Expr\Join::WITH, 'info.id = apply.applyEikenPersonalInfoId')
            ->where('apply.isDelete = 0')
            ->andWhere('info.isDelete = 0')
            ->andWhere('apply.isRegister = 1')
            ->andWhere('apply.isSubmit = 0')
            ->andWhere('apply.eikenScheduleId = :eikenScheduleId')
            ->andWhere('info.organizationId NOT IN (:listOrg)')
            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->setParameter(':listOrg', $listSubmittedOrg)
            ->groupBy('info.organizationId');

        return $qb->getQuery()->getArrayResult();
    }

    public function getArrayRegisteredApplyEikenMainHall($eikenScheduleId, $type = DantaiConstants::SUBMITTED){
        $em = $this->getEntityManager();
        $listOrg = $em->getRepository('\Application\Entity\ApplyEikenOrg')->getListApplyEikenOrg($eikenScheduleId, $type);

        $qb = $em->createQueryBuilder();
        $qb->select('info.organizationId,
                     SUM(CASE WHEN apply.eikenLevelId = 1 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevel1,
                     SUM(CASE WHEN apply.eikenLevelId = 2 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevelPre1,
                     SUM(CASE WHEN apply.eikenLevelId = 3 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevel2,
                     SUM(CASE WHEN apply.eikenLevelId = 4 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevelPre2,
                     SUM(CASE WHEN apply.eikenLevelId = 5 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevel3,
                     SUM(CASE WHEN apply.eikenLevelId = 6 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevel4,
                     SUM(CASE WHEN apply.eikenLevelId = 7 AND apply.hallType = 1 THEN 1 ELSE 0 END) as mainRegisteredLevel5
                    ')
            ->from('\Application\Entity\ApplyEikenPersonalInfo', 'info', 'info.organizationId')
            ->join('\Application\Entity\ApplyEikenLevel', 'apply', \Doctrine\ORM\Query\Expr\Join::WITH, 'info.id = apply.applyEikenPersonalInfoId')
            ->where('apply.isRegister = 1');

        if ($type == DantaiConstants::SUBMITTED) {
            $qb->andWhere('apply.isSubmit = 1')
                ->andWhere('apply.isCancel = 0 OR apply.isCancel IS NULL');

        }else{
            $qb->andWhere('apply.isDelete = 0');
        }

        $qb->andWhere('apply.eikenScheduleId = :eikenScheduleId')
            ->andWhere('apply.hallType = 1')
            ->andWhere('info.organizationId IN (:listOrg)')
            ->orderBy('info.organizationId')
            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->setParameter(':listOrg', $listOrg)
            ->groupBy('info.organizationId');

        return $qb->getQuery()->getArrayResult();
    }
    
     public function getTotalPupilDiscountOfKyu($orgId , $eikenScheduleId){
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('
                     SUM(CASE WHEN apply.eikenLevelId = 1 AND apply.hallType = 1 THEN 1 ELSE 0 END) as level1,
                     SUM(CASE WHEN apply.eikenLevelId = 2 AND apply.hallType = 1 THEN 1 ELSE 0 END) as preLevel1,
                     SUM(CASE WHEN apply.eikenLevelId = 3 AND apply.hallType = 1 THEN 1 ELSE 0 END) as level2,
                     SUM(CASE WHEN apply.eikenLevelId = 4 AND apply.hallType = 1 THEN 1 ELSE 0 END) as preLevel2,
                     SUM(CASE WHEN apply.eikenLevelId = 5 AND apply.hallType = 1 THEN 1 ELSE 0 END) as level3,
                     SUM(CASE WHEN apply.eikenLevelId = 6 AND apply.hallType = 1 THEN 1 ELSE 0 END) as level4,
                     SUM(CASE WHEN apply.eikenLevelId = 7 AND apply.hallType = 1 THEN 1 ELSE 0 END) as level5
                    ')
            ->from('\Application\Entity\ApplyEikenPersonalInfo', 'info', 'info.organizationId')
            ->join('\Application\Entity\ApplyEikenLevel', 'apply', \Doctrine\ORM\Query\Expr\Join::WITH, 'info.id = apply.applyEikenPersonalInfoId')
            ->where('apply.isDelete = 0')
            ->andWhere('info.isDelete = 0')
            ->andWhere('info.eikenScheduleId = :eikenScheduleId')
            ->andWhere('info.organizationId = :organizationId')
            ->andWhere('apply.isDiscount = :isDiscount')
            ->andWhere('apply.hallType = 1')
            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->setParameter(':organizationId', $orgId)
            ->setParameter(':isDiscount', 1)
            ->groupBy('info.organizationId');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Function count number apply eiken of Main Hall which has empty required data.
     * @param $orgId
     * @param $eikenScheduleId
     */
    public function countNumberApplyMainHallEmptyExemption($orgId, $eikenScheduleId){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('apply.id as applyEikenLevelId , info.id as applyEikenPersonalInfoId , pupil.firstNameKanji  , pupil.lastNameKanji , info.firstNameKanji as infoFirstNameKanji  , info.lastNameKanji as infoLastNameKanji , classJ.className , orgSchoolYear.displayName ')
            ->from('\Application\Entity\ApplyEikenLevel', 'apply')
            ->join('\Application\Entity\ApplyEikenPersonalInfo', 'info', \Doctrine\ORM\Query\Expr\Join::WITH, 'info.id = apply.applyEikenPersonalInfoId')
            ->join('\Application\Entity\Pupil', 'pupil', \Doctrine\ORM\Query\Expr\Join::WITH, 'info.pupilId = pupil.id')
            ->join('\Application\Entity\ClassJ', 'classJ', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.classId = classJ.id')
            ->join('\Application\Entity\OrgSchoolYear', 'orgSchoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'pupil.orgSchoolYearId = orgSchoolYear.id')
            ->where('apply.eikenScheduleId = :eikenScheduleId')
            ->andWhere('info.organizationId = :organizationId')
            ->andWhere('apply.hallType = 1')
            ->andWhere('apply.isRegister = 1')
            ->andWhere('(
                        (apply.isDelete = 0 OR apply.isDelete is null)
                        AND
                        (apply.isSubmit = 0 OR apply.isSubmit is null)
                        )
                        OR
                        (
                            apply.isDelete = 1 AND apply.isSubmit = 1
                            AND
                            (apply.isCancel = 0 OR apply.isCancel is null)
                        )
                        OR
                        (
                            (apply.oldEikenId is not null and apply.oldEikenId != \'\'
                                AND apply.isSubmit = 1
                                AND (apply.isDelete = 0))
                        )')
            ->andWhere('apply.feeFirstTime IS NULL
                        OR
                        (apply.feeFirstTime = 0 AND (apply.cityId1 IS NULL OR apply.districtId1 IS NULL))
                        OR
                        (apply.feeFirstTime = 1 AND (apply.cityId2 IS NULL OR apply.districtId2 IS NULL))
                        OR info.eikenId is null
                        OR info.eikenId = \'\'
                        OR apply.eikenLevelId is null
                        OR apply.insertAt is null
                        ')
            ->setParameter(':organizationId', $orgId)
            ->setParameter(':eikenScheduleId', $eikenScheduleId);
        return $qb->getQuery()->getArrayResult();
    }

    public function getListPaid($pupilId, $eikenScheduleId, $eikenLevelIds){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('eikenLevel.paymentStatus')
            ->from('\Application\Entity\ApplyEikenLevel', 'eikenLevel')
            ->where('eikenLevel.pupilId = :pupilId')
            ->andWhere('eikenLevel.eikenScheduleId = :scheduleId')
            ->andWhere('eikenLevel.eikenLevelId IN (:eikenLevelIds)')
            ->andWhere('eikenLevel.paymentStatus = 1')
            ->setParameter('pupilId', $pupilId)
            ->setParameter('scheduleId', $eikenScheduleId)
            ->setParameter('eikenLevelIds', $eikenLevelIds);

        return $qb->getQuery()->getArrayResult();
    }
}