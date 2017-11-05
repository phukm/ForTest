<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\ResultSetMapping;
use Eiken\Helper\NativePaginator as DTPaginator;
use History\HistoryConst;

class EikenScheduleRepository extends EntityRepository
{

    protected $sortFields = array('col1' => 'examName', 'col3' => 'examKai');
    // filter ID EikenSchedule
    public function getAllEikenSchedule()
    {
        // get sql
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('i.year,i.id,i.kai,i.deadlineFrom,i.deadlineTo')
            ->from('\Application\Entity\EikenSchedule', 'i')
            ->where('i.kai IS NOT NULL AND i.kai != \'\'')
            ->orderBy('i.year, i.kai', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }

    public function listEikenExam($orgId, $examname ='' , $year = 0, $kai = 0, $startDate = '', $endDate = '')
    {
        $currentDate = date('Y-m-d');
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
                
        $examLeastDate = "CASE
            WHEN eikenorg.HasMainHall=1 && eikenorg.TypeExamDate=4 THEN
            (CASE WHEN eikenorg.ActualExamDate=1 THEN  least(eikenschedule.FriDate,  eikenschedule.SunDate)
                 WHEN eikenorg.ActualExamDate=2 THEN  least(eikenschedule.SatDate,eikenschedule.SunDate)
                 WHEN eikenorg.ActualExamDate=4 THEN  least(eikenschedule.SatDate, eikenschedule.FriDate,eikenschedule.SunDate)
                 ELSE eikenschedule.SunDate END
            )
            WHEN eikenorg.HasMainHall=1 && eikenorg.TypeExamDate=1 THEN least(eikenschedule.SunDate, eikenschedule.FriDate)
            WHEN eikenorg.HasMainHall=1 && eikenorg.TypeExamDate=2 THEN least(eikenschedule.SatDate, eikenschedule.SunDate)
            WHEN eikenorg.HasMainHall=0 && eikenorg.TypeExamDate=4 THEN
                (CASE WHEN eikenorg.ActualExamDate=1 THEN eikenschedule.FriDate
                     WHEN eikenorg.ActualExamDate=2 THEN eikenschedule.SatDate
                     WHEN eikenorg.ActualExamDate=4 THEN  least(eikenschedule.SatDate, eikenschedule.FriDate)
                     ELSE least(eikenschedule.SatDate, eikenschedule.FriDate) END
                            )
            WHEN eikenorg.HasMainHall=0 && eikenorg.TypeExamDate=1 THEN eikenschedule.FriDate
            WHEN eikenorg.HasMainHall=0 && eikenorg.TypeExamDate=2 THEN eikenschedule.SatDate
            ELSE eikenschedule.SunDate
            END";
        
        // conditions for subquery
        $qe = $qb->expr();
        $conditionSubQuery = $qe->andX();
        $conditionIBA = clone $conditionSubQuery;
        $conditionSubQuery->add($qe->eq('eikenorg.isDelete', 0));
        $conditionSubQuery->add($qe->lte('eikenschedule.DeadlineForm', ':currentdate'));
        $conditionSubQuery->add($qe->eq('eikenorg.OrganizationId', ':orgid'));
        $conditionSubQuery->add($qe->in('eikenorg.Status', array('DRAFT', 'SUBMITTED')));
        
        $hasIBAtable = ($examname !== '英検');
        if ($examname)
        {
            $conditionSubQuery->add($qe->eq('eikenschedule.examName', ':examname'));
        }
        
        if ($year != 0)
        {
            $conditionSubQuery->add($qe->eq('eikenschedule.year', ':year'));
        }
        
        if ($kai != 0)
        {
            $conditionSubQuery->add($qe->eq('eikenschedule.kai', ':kai'));
        }
        
        // conditions for main query
        $qe1 = $qb->expr();
        $condition = $qe1->andX();
        
        if ($startDate)
        {
            $condition->add($qe->gte('DATE(ExamDate)', ':startdate'));
        }
        
        if ($endDate)
        {
            $condition->add($qe->lte('DATE(ExamDate)', ':enddate'));
        }
        
        $sqlTable = 'SELECT     
                                eikenschedule.id AS scheId,
                                eikenschedule.ExamName as examName,
                                eikenschedule.Year as examYear,
                                eikenschedule.Kai as examKai,
                                CASE
                                    WHEN eikenschedule.DeadlineForm <= :currentdate
                                    AND eikenschedule.DeadlineTo >= :currentdate THEN 1
            	                       ELSE 0
                                    END AS examExpire,
                                eikenorg.id AS orgId,
                                ' . $examLeastDate . ' AS examDate,
            	                IFNULL(eikenorg.Total, 0) as examTotal,
                                "" as moshikomiId
                            FROM ApplyEikenOrg  eikenorg
                            INNER JOIN EikenSchedule eikenschedule 
                                ON eikenschedule.id=eikenorg.EikenScheduleId AND eikenorg.IsDelete = 0
                            WHERE ' . $conditionSubQuery;
        
        if($hasIBAtable && $kai == ''){
            if ($year != 0)
            {
                $condition->add('examYear = :year');
            }
            $conditionIBA->add($qe->eq('OrganizationId', ':orgid'));
            $conditionIBA->add($qe->eq('IsDelete', 0));
            $conditionIBA->add($qe->orX($qe->neq('FromUketuke', 1), $qe->isNull('FromUketuke')));

            $sqlTable .= '
                UNION SELECT 
                            id AS scheId,
                            \'IBA\' AS examName,
                            CASE
                                    WHEN TestDate IS NULL THEN NULL
                                    WHEN TestDate <= DATE(CONCAT(DATE_FORMAT(TestDate,\'%Y\'),\'-03-31\'))
                                    THEN (CONVERT(DATE_FORMAT(TestDate,\'%Y\') ,UNSIGNED INTEGER)-1)
                                    ELSE DATE_FORMAT(TestDate,\'%Y\')
                                    END
                            AS examYear,
                            NULL AS examKai,
                            Status AS examExpire,
                            OrganizationId AS orgId,
                            TestDate AS examDate,
                            TotalPeople AS examTotal,
                            moshikomiId AS moshikomiId
                FROM ApplyIBAOrg
                WHERE '.$conditionIBA;
        }
        
        $sql = 'SELECT * FROM ( '.$sqlTable. ' ) as exam';
        
        if ($condition->count()) 
        {
            $sql .= ' WHERE '.$condition;
        }
        $sql .= ' ORDER BY exam.examYear DESC, exam.examDate DESC';
        
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('\Application\Entity\EikenExamOrg', 'exam');
        $rsm->addFieldResult('exam', 'moshikomiId', 'moshikomiId');
        $rsm->addFieldResult('exam', 'orgId', 'orgId');
        $rsm->addFieldResult('exam', 'scheId', 'scheId');
        $rsm->addFieldResult('exam', 'examName', 'examName');
        $rsm->addFieldResult('exam', 'examTotal', 'examTotal');
        $rsm->addFieldResult('exam', 'examMapping', 'examMapping');
        $rsm->addFieldResult('exam', 'examImporting', 'examImporting');
        $rsm->addFieldResult('exam', 'examDate', 'examDate');
        $rsm->addFieldResult('exam', 'examExpire', 'examExpire');
        $rsm->addFieldResult('exam', 'examYear', 'examYear');
        $rsm->addFieldResult('exam', 'examKai', 'examKai');
        $rsm->addFieldResult('exam', 'examMoshikomiId', 'examMoshikomiId');
        $rsm->addFieldResult('exam', 'id', 'id');
        
        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter(':currentdate', $currentDate);
        $query->setParameter(':orgid', $orgId);

        if ($examname)
        {
            $query->setParameter(':examname', $examname);
        }
        
        if ($year > 0)
        {
            $query->setParameter(':year', $year);
        }
        
        if ($kai > 0)
        {
            $query->setParameter(':kai', $kai);
        }
        
        if ($startDate)
        {
            $query->setParameter(':startdate', $startDate);
        }
        
        if ($endDate)
        {
            $query->setParameter(':enddate', $endDate);
        }
        
        $paginator = new DTPaginator($query);
        
        return $paginator;
    }

    /**
     * Get Available Eiken Schedule by current date
     *
     * @author DuongTD
     */
    public function getAvailableEikenScheduleByDate($year = '', $currentDate = '')
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenSchedule.id, eikenSchedule.kai, eikenSchedule.year')
            ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
            ->where('eikenSchedule.deadlineFrom <= :currentDate')
            ->andWhere('eikenSchedule.deadlineTo >= :currentDate')
            ->andWhere('eikenSchedule.year = :year')
            ->andWhere('eikenSchedule.examName = :examName')
            ->andWhere('eikenSchedule.isDelete = :isDelete')
            ->setParameter(':currentDate', $currentDate)
            ->setParameter(':examName', '英検')
            ->setParameter(':year', $year)
            ->setParameter('isDelete', 0)
            ->setMaxResults(1);
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return array();
        }
    }

    /**
     * Get Eiken Schedule by id
     *
     * @author DuongTD
     */
    public function getEikenScheduleById($id = 0, $year = '', $currentDate = '')
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenSchedule.id, eikenSchedule.kai, eikenSchedule.year,eikenSchedule.deadlineFrom,eikenSchedule.deadlineTo')
            ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
            ->where('eikenSchedule.id = :id')
            ->andWhere('eikenSchedule.isDelete = 0')
            ->setParameter(':id', $id)
            ->setMaxResults(1);
        if (! empty($year) && ! empty($currentDate)) {
            $qb->andWhere('eikenSchedule.deadlineFrom <= :currentDate')
                ->andWhere('eikenSchedule.deadlineTo >= :currentDate')
                ->andWhere('eikenSchedule.year = :year')
                ->setParameter(':currentDate', $currentDate)
                ->setParameter(':year', $year);
        }
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return array();
        }
    }

    /**
     * Get Kai by selected Year
     *
     * @author Anhnt
     */
    public function getKaiByYear($year)
    {
        if (empty($year))
            return false;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("eikenSchedule.id, eikenSchedule.kai")
            ->from("\Application\Entity\EikenSchedule", "eikenSchedule")
            ->where("eikenSchedule.year = :year AND eikenSchedule.isDelete = 0 AND eikenSchedule.examName = '英検'")
            ->orderBy("eikenSchedule.kai", "ASC")
            ->setParameter('year', $year)
            ->groupBy("eikenSchedule.kai");
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    /**
     * get list passed kais
     */
    public function getPassedKais($firstName, $lastName)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenSchedule.id,eikenSchedule.kai, eikenSchedule.year')
            ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
            ->where('eikenSchedule.year >= :from_year')
            ->andwhere('eikenSchedule.year <= :to_year')
            ->andWhere('eikenSchedule.isDelete = 0 OR eikenSchedule.isDelete is null')
            ->setParameter('from_year', (date('Y') - 1))
            ->setParameter('to_year', date('Y'));
        $query = $qb->getQuery();
        $kais = $query->getArrayResult();
        $kaiOptions = array(
            '' => ''
        );
        if (! empty($kais)) {
            foreach ($kais as $key => $value) {
                $kaiOptions[$value['id']] = $value['year'] . $firstName . $value['kai'] . $lastName;
            }
        }
        return $kaiOptions;
    }

    public function getInvClassList($limit = 10, $offset = 0, $year = false, $schoolyear = false, $class = false, $org_id = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classj')
            ->from('\Application\Entity\ClassJ', 'classj')
            ->innerJoin('\Application\Entity\OrgSchoolYear', 'orgSchoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'classj.orgSchoolYear = orgSchoolYear.id')
            ->orderBy('orgSchoolYear.schoolYearId ', 'ASC')
            ->addOrderBy('classj.className', 'ASC')
            ->where('classj.isDelete = 0')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        if (empty($schoolyear)) {
            $qb->andWhere('classj.year = :year')
                ->andWhere('classj.organizationId = :organizationId')
                ->setParameter(':year', intval($year))
                ->setParameter(':organizationId', intval($org_id)); 
        }else if(empty($class)){
            $qb->andWhere('classj.year = :year')
                ->andWhere('classj.orgSchoolYearId = :orgSchoolYearId')
                ->andWhere('classj.organizationId = :organizationId')
                ->setParameter(':year', intval($year))
                ->setParameter(':orgSchoolYearId', intval($schoolyear))
                ->setParameter(':organizationId', intval($org_id));
        }else{
            $qb->andWhere('classj.year = :year')
                ->andWhere('classj.id = :id')
                ->setParameter(':year', intval($year))
                ->setParameter(':id', intval($class));
        }
           
        $query = $qb->getQuery();
        $paginator = new Paginator($query, $fetchJoinCollection = false);
        return $paginator;
    }

    /*
     * Get Current EikenSchedule
     * AnhNT56
     */
    public function getCurrentEikenSchedule()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e.id,e.kai,e.year,e.deadlineFrom,e.deadlineTo,
                    e.day1stTestResult,
                    e.day2ndTestResult,
                    e.round2Day1ExamDate,
                    e.round2Day2ExamDate,
                    e.combiniDeadline,
                    e.friDate,
                    e.satDate,
                    e.sunDate
                    ')
            ->from('\Application\Entity\EikenSchedule', 'e')
            ->andwhere('e.deadlineFrom <= :currentDate')
            ->orderBy('e.year', 'DESC')
            ->addOrderBy('e.kai', 'DESC')
            ->setParameter(':currentDate', date('y-m-d'))
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     *
     * @author TaiVH
     */
    public function getEikenScheduleLastTime()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qe = $qb->expr();
        $qb->select("Eiken.id, Eiken.kai, Eiken.deadlineTo")
            ->from('\Application\Entity\EikenSchedule', 'Eiken')
            ->where('Eiken.deadlineTo < :currentDate')
            ->setParameter(':currentDate', date('Y-m-d'))
            ->andWhere('Eiken.isDelete = 0')
            ->orderBy('Eiken.deadlineTo', 'DESC')
            ->setMaxResults(1);
        return $qb->getQuery()->getArrayResult();
    }

    /**
     *
     * @author TaiVH
     */
    public function getEikenScheduleByCurrentTime()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qe = $qb->expr();
        $qb->select("Eiken.id, Eiken.kai,Eiken.year, Eiken.deadlineFrom,Eiken.deadlineTo")
            ->from('\Application\Entity\EikenSchedule', 'Eiken')           
            ->where('Eiken.deadlineTo >= :currentDate')
            ->andWhere('Eiken.deadlineFrom <= :currentDate')
            ->setParameter(':currentDate', date('Y-m-d H:i:s'))
            ->andWhere('Eiken.isDelete = 0')
            ->orderBy('Eiken.deadlineTo', 'DESC')
            ->setMaxResults(1);
        return $qb->getQuery()->getArrayResult();
    }

    /**
     *
     * @return Ambigous <number, \Doctrine\ORM\mixed>
     */
    public function getCurrentKai()
    {
        $date = date('Y-m-d');
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenSchedule.id,eikenSchedule.kai')
            ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
            ->where(":currentDate >= eikenSchedule.deadlineFrom AND :currentDate <= eikenSchedule.deadlineTo")
            ->setParameter(':currentDate', $date)
            ->setMaxResults(1);
        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();
        return $result != Null ? $result["kai"] : 0;
    }

    /**
     *
     * @param string $keyword
     * @return \Eiken\Helper\DTPaginator
     * @author: namth7
     */
    public function getListEikenExamResult($organizationId, $keyword = array()) {       
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $whereEiken = '';
        $whereIBA = '';
        $whereExamYear = '';
        
        if ($organizationId != '') {
            $whereEiken = 'AND Eiken.organizationId = :orgid';
            $whereIBA = 'AND IBA.organizationId = :orgid';
        }
        if($keyword != null){
            $selectExamYear = $keyword['year'];
        }
        $examLeastDate = "CASE
            WHEN Eiken.HasMainHall=1 && Eiken.TypeExamDate=4 THEN
            (CASE WHEN Eiken.ActualExamDate=1 THEN  least(eikenschedule.FriDate,  eikenschedule.SunDate)
                 WHEN Eiken.ActualExamDate=2 THEN  least(eikenschedule.SatDate,eikenschedule.SunDate)
                 WHEN Eiken.ActualExamDate=4 THEN  least(eikenschedule.SatDate, eikenschedule.FriDate,eikenschedule.SunDate)
                 ELSE eikenschedule.SunDate END
            )
            WHEN Eiken.HasMainHall=1 && Eiken.TypeExamDate=1 THEN least(eikenschedule.SunDate, eikenschedule.FriDate)
            WHEN Eiken.HasMainHall=1 && Eiken.TypeExamDate=2 THEN least(eikenschedule.SatDate,eikenschedule.SunDate)
            WHEN Eiken.HasMainHall=0 && Eiken.TypeExamDate=4 THEN
                (CASE WHEN Eiken.ActualExamDate=1 THEN eikenschedule.FriDate
                     WHEN Eiken.ActualExamDate=2 THEN eikenschedule.SatDate
                     WHEN Eiken.ActualExamDate=4 THEN  least(eikenschedule.SatDate, eikenschedule.FriDate)
                     ELSE least(eikenschedule.SatDate, eikenschedule.FriDate) END
                            )
            WHEN Eiken.HasMainHall=0 && Eiken.TypeExamDate=1 THEN eikenschedule.FriDate
            WHEN Eiken.HasMainHall=0 && Eiken.TypeExamDate=2 THEN eikenschedule.SatDate
            ELSE eikenschedule.SunDate
            END";
        $examNameIBA = "'".HistoryConst::EXAM_NAME_IBA."'";

        $sql = "
                SELECT exam.id as id, exam.orgId as orgId, exam.scheId as scheId, exam.examDate as examDate, exam.examName,
                exam.examYear as examYear, exam.examKai as examKai, exam.total as examTotal,
                exam.statusMapping as examMapping, exam.statusImport as examImporting,
                exam.jisshiId as jisshiId,
                exam.examType as examType,
                exam.setName, exam.hasNewData
                FROM
                (SELECT eikenschedule.ExamName as examName, eikenschedule.Year AS examYear, eikenschedule.Kai as examKai, Eiken.id as id, Eiken.organizationId as orgId, Eiken.eikenScheduleId as scheId, ".$examLeastDate." as examDate,
                        Eiken.TotalImport as total, Eiken.StatusMapping as statusMapping, Eiken.StatusImporting as statusImport,
                        'jisshiId' as jisshiId, 'examType' as examType, null as setName, null as hasNewData
                        FROM ApplyEikenOrg Eiken
                        JOIN EikenSchedule AS eikenschedule ON eikenschedule.id = Eiken.eikenScheduleId
                        WHERE Eiken.isDelete = 0 ".$whereEiken."
                            AND (Eiken.Status = 'SUBMITTED' OR Eiken.Status = 'DRAFT' OR  Eiken.Status = 'N/A')
                UNION
                SELECT ".$examNameIBA." as examName,
                        YEAR(IBA.testDate) as examYear, 'ibaKai' as examKai, IBA.id as id, IBA.organizationId as orgId, IBA.eikenScheduleId as scheId, IBA.testDate as examDate, IBA.TotalImport as total,
                        IBA.StatusMapping as statusMapping, IBA.StatusImporting as statusImport,
                        IBA.jisshiId as jisshiId, IBA.examType as examType, IBA.setName as setName, IBA.hasNewData as hasNewData
                        FROM ApplyIBAOrg IBA
                        WHERE IBA.isDelete = 0 ".$whereIBA."
                            AND IBA.fromUketuke = 1
                ) as exam ";
        $qe = $qb->expr();
        $condition = $qe->andX();
        if (isset($keyword['examType']) && $keyword['examType'] != '') {
           $condition->add($qe->eq('exam.ExamName', ":examName"));
        }

        if (isset($keyword['year']) && $keyword['year'] != '') {
            $condition->add($qe->eq('exam.examYear', ":year"));
        }

        if (isset($keyword['kai']) && $keyword['kai'] != '') {
                $condition->add($qe->eq('exam.examKai', ":kai"));
        }

        if (isset($keyword['startDate']) && $keyword['startDate'] != '') {
            $condition->add($qe->gte('exam.examDate', ":startDate"));
        }
        if (isset($keyword['endDate']) && $keyword['endDate'] != '') {
            $condition->add($qe->lte('exam.examDate', ":endDate"));
        }
        if ($keyword == array() 
                || ((isset($keyword['examType']) && $keyword['examType'] == '') && (isset($keyword['kai']) && $keyword['kai'] == '') 
                        && (isset($keyword['startDate']) && $keyword['startDate'] == '') && (isset($keyword['endDate']) && $keyword['endDate'] == '') && !isset($keyword['year']))
                || ((isset($keyword['examType']) && $keyword['examType'] == '') && (isset($keyword['startDate']) && $keyword['startDate'] == '') 
                        && (isset($keyword['endDate']) && $keyword['endDate'] == '') && !isset($keyword['year']) && !isset($keyword['kai']))) {
            $condition->add($qe->eq('exam.examYear', (int)Date('Y')));
        }
        if ($condition->count()) {
            $sql .= 'WHERE '.$condition;
        }
        
        if (isset($keyword['sortKey']) && $keyword['sortKey'] != '' && isset($this->sortFields[$keyword['sortKey']])) {
            if(!empty($keyword['sortOrder']) && $keyword['sortOrder'] == 'asc'){
                $order = 'asc';
            } else {
                $order = 'desc';
            }
            $sql .= " ORDER BY exam." . $this->sortFields[$keyword['sortKey']] . " " . $order . ", exam.examDate DESC, exam.examYear DESC, exam.examKai DESC";
        } else {
            $sql .= " ORDER BY exam.examDate DESC, exam.examYear DESC, exam.examKai DESC";
        }
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('\Application\Entity\EikenExamOrg', 'exam');
        $rsm->addFieldResult('exam', 'orgId', 'orgId');
        $rsm->addFieldResult('exam', 'scheId', 'scheId');
        $rsm->addFieldResult('exam', 'examName', 'examName');
        $rsm->addFieldResult('exam', 'examTotal', 'examTotal');
        $rsm->addFieldResult('exam', 'examMapping', 'examMapping');
        $rsm->addFieldResult('exam', 'examImporting', 'examImporting');
        $rsm->addFieldResult('exam', 'examDate', 'examDate');
        $rsm->addFieldResult('exam', 'examYear', 'examYear');
        $rsm->addFieldResult('exam', 'examKai', 'examKai');
        $rsm->addFieldResult('exam', 'jisshiId', 'jisshiId');
        $rsm->addFieldResult('exam', 'examType', 'examType');
        $rsm->addFieldResult('exam', 'id', 'id');
        $rsm->addFieldResult('exam', 'setName', 'setName');
        $rsm->addFieldResult('exam', 'hasNewData', 'hasNewData');

        $query = $em->createNativeQuery($sql, $rsm);
        
        if ($organizationId != '') 
        {
            $query->setParameter(":orgid", $organizationId);
        }
        
        if (isset($keyword['examType']) && $keyword['examType'] != '') {
            $query->setParameter(":examName", $keyword['examType']);
        }
        if (isset($keyword['year']) && $keyword['year'] != '') {
            $query->setParameter(":year", (int)$keyword['year']);
        }
        if (isset($keyword['kai']) && $keyword['kai'] != '') {
            $query->setParameter(":kai", (int)$keyword['kai']);
        }
        if (isset($keyword['startDate']) && $keyword['startDate'] != '') {
            $query->setParameter(":startDate", date("Y-m-d H:i:s", strtotime($keyword['startDate'])));
        }
        if (isset($keyword['endDate']) && $keyword['endDate'] != '') {
            $query->setParameter(":endDate", date("Y-m-d 23:59:59", strtotime($keyword['endDate'])));
        }
        $paginator = new DTPaginator($query);
        
        return $paginator;
    }

    /**
     *
     * @param string $year
     * @param string $kai
     * @return Ambigous <number, unknown>
     */
    public function getIdByYearKai($year = '', $kai = '') {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenSchedule.id')
        ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
        ->where('eikenSchedule.year = :year')
        ->andWhere('eikenSchedule.kai = :kai')
        ->setParameter(':year', $year)
        ->setParameter(':kai', $kai)
        ->andWhere('eikenSchedule.examName = \'英検\'')
        ->andWhere('eikenSchedule.isDelete = 0')
        ->setMaxResults(1);
        $query = $qb->getQuery();
        $result = $query->getOneOrNullResult();
        return $result != Null ? $result["id"] : 0;
    }
    
    public function getYearByJisshiIdExamType($jisshiId, $examType)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenschedule.year')
        ->from('\Application\Entity\EikenSchedule', 'eikenschedule')
        ->leftJoin('\Application\Entity\ApplyIBAOrg', 'applyIBAorg', \Doctrine\ORM\Query\Expr\Join::WITH, 'applyIBAorg.eikenScheduleId = eikenschedule.id')
        ->where('applyIBAorg.jisshiId = :jisshiId')
        ->andWhere("applyIBAorg.examType = :examType")
        ->andWhere("eikenschedule.examName = 'iba'")
        ->setParameter(':jisshiId', $jisshiId)
        ->setParameter(':examType', $examType)
        ->setMaxResults(1);
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result != Null ? $result['year'] : 0;
    }

    /**
     * Get Data Eiken Schedule by current year
     * @author TaiVH
     */
    public function getDataEikenScheduleByCurrentDate($year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('eikenSchedule')
        ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
        ->andWhere('eikenSchedule.examName = :examName')
        ->andWhere('eikenSchedule.year = :year')
        ->orderBy('eikenSchedule.kai', 'ASC')
        ->setParameter('examName', '英検')
        ->setParameter('year', $year);
        try {
            return $qb->getQuery()->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return array();
        }
    }
    
    /*
     * ChungDV
     * get info student current year
     */
    public function getInfoStudentCurrentYear($orgId, $year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
    
        $fields = array(
            'eikenSchedule.id',
            'eikenSchedule.kai',
            'eikenSchedule.year',
            'eikenSchedule.deadlineFrom',
            'eikenSchedule.deadlineTo',
            'eikenSchedule.examName',
            'eikenSchedule.day1stTestResult',
            'eikenSchedule.day2ndTestResult',
            'eikenSchedule.friDate',
            'eikenSchedule.satDate',
            'eikenSchedule.sunDate',
            'eikenSchedule.round2ExamDate',
            'applyEikenOrg.registrationDate',
            'applyEikenOrg.confirmationDate',
            'applyEikenOrg.updateAt as applyEikenOrgUpdateAt',
            'applyEikenOrg.updateBy as applyEikenOrgUpdateBy',
            'applyEikenOrg.insertAt as applyEikenOrgInsertAt',
            'applyEikenOrg.executorName',
            'CONCAT(applyEikenOrg.firtNameKanji,applyEikenOrg.lastNameKanji) as applyEikenOrgInsertBy',
            'applyEikenOrg.status',
            'applyEikenOrg.statusImporting',
            'applyEikenOrg.isDelete as isDeleteApply'
        );
    
        $qb->select($fields)
        ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
        ->leftJoin('\Application\Entity\ApplyEikenOrg', 'applyEikenOrg',
            \Doctrine\ORM\Query\Expr\Join::WITH, 'applyEikenOrg.eikenScheduleId = eikenSchedule.id')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->orX(
                        $qb->expr()->eq('eikenSchedule.year', ':year'),
                        $qb->expr()->andX(
                            $qb->expr()->eq('eikenSchedule.year', ':nextyear'),
                            $qb->expr()->eq('eikenSchedule.kai', ':kai')
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->eq('eikenSchedule.year', ':pastyear'),
                            $qb->expr()->eq('eikenSchedule.kai', ':pastkai')
                        )
                    ),
                    $qb->expr()->eq('applyEikenOrg.organizationId', ':organizationId'),
                    $qb->expr()->eq('eikenSchedule.isDelete', ':isDelete')
                )
            );
    
        $qb->setParameters(array(
            'kai'            => 1,
            'pastkai'        => 3,
            'year'           => $year,
            'nextyear'       => $year + 1,
            'pastyear'       => $year - 1,
            'organizationId' => $orgId,
            'isDelete'       => 0
        ));
        
        $qb->addOrderBy('eikenSchedule.deadlineFrom', 'DESC');
    
        try {
            return $qb->getQuery()->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return array();
        }
    }

    /**
     * Get Kai by selected Year
     *
     * @author annv6
     */
    public function getKaiByYearDESC($year)
    {
        if (empty($year))
            return false;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("eikenSchedule.id, eikenSchedule.kai")
        ->from("\Application\Entity\EikenSchedule", "eikenSchedule")
        ->where("eikenSchedule.year = :year AND eikenSchedule.isDelete = 0 AND eikenSchedule.examName = '英検'")
        ->groupBy("eikenSchedule.kai")
        ->orderBy("eikenSchedule.kai", "ASC")
        ->setParameter('year', $year);
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    
    public function getPastKaiOfOrg($orgId, $currentKai)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $kai = 0;
        if($currentKai == 1){
            $kai = 3;
            $year = date('Y') - 1;
        }elseif($currentKai == 2 || $currentKai == 3){
            $kai = $currentKai - 1; 
            $year = date('Y');
        }
        $qb->select('eikenSchedule.round2ExamDate')
        ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
        ->innerJoin('\Application\Entity\ApplyEikenOrg', 'applyEikenOrg',
            \Doctrine\ORM\Query\Expr\Join::WITH, 'applyEikenOrg.eikenScheduleId = eikenSchedule.id')
        ->where('applyEikenOrg.organizationId = :organizationId')
        ->andWhere('eikenSchedule.kai = :kai')
        ->andWhere('eikenSchedule.year = :year')
        ->setParameter('organizationId', $orgId)
        ->setParameter('kai', $kai)
        ->setParameter('year', $year)
        //TODO need to check again for this case
        ->setMaxResults(1);
        $query = $qb->getQuery()->getOneOrNullResult();
        return $query;
    }
    
    public function getNextKaiOfOrg($orgId, $currentKai, $currentYear)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $kai = 0;
        if($currentKai == 3){
            $kai = 1;
            $year = $currentYear + 1;
        }elseif($currentKai == 1 || $currentKai == 2){
            $kai = $currentKai + 1;
            $year = $currentYear;
        }
        $qb->select('eikenSchedule.year, eikenSchedule.kai,eikenSchedule.deadlineFrom, eikenSchedule.round2ExamDate')
        ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
        ->innerJoin('\Application\Entity\ApplyEikenOrg', 'applyEikenOrg',
            \Doctrine\ORM\Query\Expr\Join::WITH, 'applyEikenOrg.eikenScheduleId = eikenSchedule.id')
            ->where('applyEikenOrg.organizationId = :organizationId')
            ->andWhere('eikenSchedule.kai = :kai')
            ->andWhere('eikenSchedule.year = :year')
            ->setParameter('organizationId', $orgId)
            ->setParameter('kai', $kai)
            ->setParameter('year', $year)
            //TODO need to check again for this case
        ->setMaxResults(1);
        $query = $qb->getQuery()->getOneOrNullResult();
        return $query;
    }

    /**
     * AnNV6
     */
    public function getOrgApplyEikenData($orgId, $currentKai, $year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('applyEikenOrg.status')
        ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
        ->innerJoin('\Application\Entity\ApplyEikenOrg', 'applyEikenOrg',
            \Doctrine\ORM\Query\Expr\Join::WITH, 'applyEikenOrg.eikenScheduleId = eikenSchedule.id')
            ->where('applyEikenOrg.organizationId = :organizationId')
            ->andWhere('eikenSchedule.kai = :kai')
            ->andWhere('eikenSchedule.year = :year')
            ->setParameter('organizationId', $orgId)
            ->setParameter('kai', $currentKai)
            ->setParameter('year', $year)
            //TODO need to check again for this case
            ->setMaxResults(1);
        $query = $qb->getQuery()->getOneOrNullResult();
        return $query;
    }
    
    public function getFirstExamDate($orgId, $year){
        $sql = 
            "SELECT count(eikenSchedule.id) as countEikenSchedule,
                    eikenSchedule.friDate, eikenSchedule.satDate, eikenSchedule.sunDate,
                    least(eikenSchedule.friDate, eikenSchedule.satDate, eikenSchedule.sunDate) as firstDate
            FROM EikenSchedule eikenSchedule
            LEFT JOIN ApplyEikenOrg applyEikenOrg 
                ON eikenSchedule.id=applyEikenOrg.eikenScheduleId
            WHERE applyEikenOrg.organizationId = :orgId AND eikenSchedule.year = :year";
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('orgId', $orgId);
        $stmt->bindValue('year', $year);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     *
     * @author ThanhNX6
     */
    public function getEikenSchedulesByYear($yearFrom, $yearTo, $examName) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenSchedule')
        ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
        ->andWhere('eikenSchedule.examName = :examName')
        ->andWhere('eikenSchedule.year BETWEEN :yearFrom AND :yearTo')
        ->andWhere('eikenSchedule.kai > 0')
        ->orderBy('eikenSchedule.kai', 'ASC')
        ->setParameter('examName', $examName)
        ->setParameter('yearFrom', $yearFrom)
        ->setParameter('yearTo', $yearTo);
    
        try {
            return $qb->getQuery()->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return array();
        }
    }
    
    /**
     *
     * @author ThanhNX6
     */
    public function getHolidaysByDate($dateFrom, $dateTo) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('holidays.dayOff, holidays.name')
        ->from('\Application\Entity\Holidays', 'holidays')
        ->where('holidays.dayOff BETWEEN :dateFrom AND :dateTo')
        ->setParameter(':dateFrom', $dateFrom)
        ->setParameter(':dateTo', $dateTo);
        $holidays = $qb->getQuery()->getArrayResult();
        return $holidays;
    }
    
    /**
     * @author AnNV6
     */
    public function getAllKai(){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenSchedule.kai')
        ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
        ->orderBy('eikenSchedule.kai', 'ASC')
        ->groupBy('eikenSchedule.kai')
        ->where('eikenSchedule.year <= :year')
        ->setParameter('year', date('Y'));
        return $qb->getQuery()->getArrayResult();
        
    }
    
    /**
     * @author AnNV6
     */
    public function getAllYear(){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenSchedule.year')
        ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
        ->orderBy('eikenSchedule.year', 'DESC')
        ->groupBy('eikenSchedule.year')
        ->where('eikenSchedule.year <= :year')
        ->setParameter('year', date('Y'));
        return $qb->getQuery()->getArrayResult();
    
    }
    
    /**
     * @author AnhNT56
     */
    public function getCurrentKaiByYear($year){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
    
        $fields = array(
            'eikenSchedule.id',
            'eikenSchedule.kai',
            'eikenSchedule.year',
            'eikenSchedule.deadlineFrom',
            'eikenSchedule.deadlineTo',
            'eikenSchedule.examName',
            'eikenSchedule.day1stTestResult',
            'eikenSchedule.day2ndTestResult',
            'eikenSchedule.friDate',
            'eikenSchedule.satDate',
            'eikenSchedule.sunDate',
            'eikenSchedule.round2ExamDate',
        );
    
        $qb->select($fields)
        ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
        ->where(
            $qb->expr()->andX(
                $qb->expr()->orX(
                    $qb->expr()->eq('eikenSchedule.year', ':year'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('eikenSchedule.year', ':nextyear'),
                        $qb->expr()->eq('eikenSchedule.kai', ':kai')
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->eq('eikenSchedule.year', ':pastyear'),
                        $qb->expr()->eq('eikenSchedule.kai', ':pastkai')
                    )
                ),
                $qb->expr()->eq('eikenSchedule.isDelete', ':isDelete')
            )
        );
    
        $qb->setParameters(array(
            'kai'            => 1,
            'pastkai'        => 3,
            'year'           => $year,
            'nextyear'       => $year + 1,
            'pastyear'       => $year - 1,
            'isDelete'       => 0
        ));
    
        $qb->addOrderBy('eikenSchedule.deadlineFrom', 'DESC');
    
        try {
            return $qb->getQuery()->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return array();
        }
    }
    public function getCurrentKaiByTestDateResult()
    {
        $year=date('Y');
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenSchedule')
            ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
            ->where("eikenSchedule.isDelete=0")
            ->andWhere('(eikenSchedule.year=:year OR (eikenSchedule.year=:lastyear AND eikenSchedule.kai=3)OR (eikenSchedule.year=:nextyear AND eikenSchedule.kai=1))')        
           ->setParameter(':year', $year)
            ->setParameter(':lastyear', $year-1)
            ->setParameter(':nextyear', $year+1)
            ->orderBy('eikenSchedule.year ,eikenSchedule.kai', 'Asc')
            ->setMaxResults(5);
        $query = $qb->getQuery();
        $result=$query->getResult();
        return $result;  
    }
    public function getListEikenScheduleByTestDateResult(){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $currentYear = (int)date('Y');
        $query = $qb->select('eikenSchedule')
                ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
                ->where($qb->expr()->in('eikenSchedule.year', array($currentYear - 1, $currentYear, $currentYear + 1)))
                ->andWhere('eikenSchedule.isDelete = 0')
                ->orderBy('eikenSchedule.year', 'ASC')
                ->addOrderBy('eikenSchedule.kai', 'ASC');
        $result = $query->getQuery()->getResult();
        return $result;
    }

    public function checkKaiExitByDatelineFrom() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $todayStartDateTime = \DateTime::createFromFormat( "Y-m-d H:i:s", date("Y-m-d 00:00:00") );
        $todayEndDateTime = \DateTime::createFromFormat( "Y-m-d H:i:s", date("Y-m-d 23:59:59") );
        $qb->select('eikenSchedule.id')
                ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')              
               ->where('eikenSchedule.deadlineFrom >= :todayStartDateTime')
                ->andWhere('eikenSchedule.deadlineFrom <= :todayEndDateTime')
                ->andWhere('eikenSchedule.isDelete = :isDelete')
                ->setParameter('todayStartDateTime', $todayStartDateTime->modify('+1 day'))
                ->setParameter('todayEndDateTime', $todayEndDateTime->modify('+1 day'))       
                ->setParameter('isDelete', 0)
                ->setMaxResults(1);
       return $qb->getQuery()->getResult();
    }   
    
    public function getDeadlineFromOfNextKai($kai, $year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenSchedule.deadlineFrom')
        ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
            ->where('eikenSchedule.kai = :kai')
            ->andWhere('eikenSchedule.year = :year')
            ->setParameter('kai', $kai)
            ->setParameter('year', $year)
            //TODO need to check again for this case
        ->setMaxResults(1);
        $query = $qb->getQuery()->getOneOrNullResult();
        return $query;
    }

    public function getListEikenSchedule($year = null, $kai = null){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenSchedule.year, eikenSchedule.kai, eikenSchedule.deadlineFrom, eikenSchedule.deadlineTo,
        eikenSchedule.combiniDeadline, eikenSchedule.creditCardDeadline, eikenSchedule.satelliteSiteDeadline,
        eikenSchedule.friDate, eikenSchedule.satDate, eikenSchedule.sunDate, eikenSchedule.round2Day1ExamDate, eikenSchedule.round2Day2ExamDate,
        eikenSchedule.day1stTestResult, eikenSchedule.day2ndTestResult')
            ->from('\Application\Entity\EikenSchedule', 'eikenSchedule')
            ->where('eikenSchedule.isDelete = 0');
        if(!empty($year)){
            $qb->andWhere('eikenSchedule.year = :year');
            $qb->setParameter('year', $year);
        }
        if(!empty($kai)){
            $qb->andWhere('eikenSchedule.kai = :kai');
            $qb->setParameter('kai', $kai);
        }

        return $qb->getQuery()->getArrayResult();
    }
}
