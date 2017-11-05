<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\GoalPass;

class GoalPassRepository extends EntityRepository
{
    // Goal Pass
    public function getCityPassRate($year, $schoolYear, $orgCode, $cityId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('goalPass.eikenLevelId,goalPass.ratePass')
            ->from('\Application\Entity\GoalPass', 'goalPass')
            ->where('goalPass.year = :year')
            ->andWhere('goalPass.cityId = :cityId')
            ->andWhere('goalPass.schoolYear = :schoolYear')
            ->andWhere('goalPass.organizationCode = :organizationCode')
            ->setParameter(':year', $year)
            ->setParameter(':cityId', $cityId)
            ->setParameter(':schoolYear', $schoolYear)
            ->setParameter(':organizationCode', $orgCode)
            ->andWhere('goalPass.isDelete = 0')
            ->orderBy("goalPass.eikenLevel", "DESC");
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    // Goal Pass
    public function getListCityPassRate($year, $schoolYear, $orgCode, $cityId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('goalPass.eikenLevelId,goalPass.ratePass,goalPass.year')
        ->from('\Application\Entity\GoalPass', 'goalPass')
        ->where('goalPass.year >= :yearMin')
        ->andWhere('goalPass.year < :yearMax')
        ->andWhere('goalPass.organizationCode = :orgCode')
        ->andWhere('goalPass.cityId = :cityId')
        ->andWhere('goalPass.schoolYearCode = :schoolYears')
        ->setParameter(':yearMin', (int)$year-3)
        ->setParameter(':yearMax', (int)$year)
        ->setParameter(':orgCode', (int)$orgCode)
        ->setParameter(':cityId', (int)$cityId)
        ->setParameter(':schoolYears', (int)$schoolYear)
        ->andWhere('goalPass.isDelete = 0')
        ->orderBy('goalPass.year', 'DESC');
//         ->orderBy("goalPass.eikenLevel", "DESC");
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    /*
     * AnhNT56
     */
    public function getCountPupilPass($year, $orgId, $orgSchoolYear)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('actualExam.eikenLevelId,COUNT(actualExam.id) AS numberPass')
            ->from('\Application\Entity\ActualExamResult', 'actualExam')
            ->where('actualExam.year = :year')
            ->andWhere('actualExam.organizationId = :organizationId')
            ->setParameter(':year', $year)
            ->setParameter(':organizationId', $orgId)
            ->andWhere('actualExam.passFlag = 1')
            ->andWhere('actualExam.isDelete = 0')
            ->groupBy('actualExam.eikenLevelId')
            ->orderBy("actualExam.eikenLevel", "DESC");
        if ($orgSchoolYear != null) {
            $qb->andWhere('actualExam.orgSchoolYearId = :orgSchoolYearId')
                    ->setParameter(':orgSchoolYearId', $orgSchoolYear);
        }
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getCountPupilOfClass($year , $orgId, $orgSchoolYear = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('SUM(class.numberOfStudent)')
            ->from('\Application\Entity\ClassJ', 'class')
            ->where('class.year = :year')
            ->andWhere('class.organizationId = :organizationId')
            ->setParameter(':year', $year)
            ->setParameter(':organizationId', $orgId)
            ->andWhere('class.isDelete = 0');

        if ($orgSchoolYear != null) {
            $qb->andWhere('class.orgSchoolYearId = :orgSchoolYearId')
                    ->setParameter(':orgSchoolYearId', $orgSchoolYear);
        }

        $query = $qb->getQuery();
        $result = $query->getSingleResult();
        return $result;
    }

    /*
     * DucNA
     * get rate city by Year and CityId group by eikenLv
     * TODO
     */
    public function getRateByCityId($cityId,$year,$orgCode){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('goalPass.eikenLevelId,goalPass.ratePass')
            ->from('\Application\Entity\GoalPass', 'goalPass', 'goalPass.eikenLevelId')
            ->where('goalPass.year = :year')
            ->andWhere('goalPass.cityId = :cityid')
            ->andWhere('goalPass.organizationCode = :orgcode')
            ->andWhere('goalPass.schoolYearCode = 0')
            ->andWhere('goalPass.isDelete = 0')
            ->setParameter(':year', $year)
            ->setParameter(':cityid', $cityId)
            ->setParameter(':orgcode', $orgCode)
            ->groupBy("goalPass.eikenLevelId");
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    /*
     * TODO
     * DucNA
     * get rate nation by year group by eikenLv
     */
    public function getRateNation($year,$orgCode){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('goalPass.eikenLevelId, goalPass.ratePass')
        ->from('\Application\Entity\GoalPass', 'goalPass', 'goalPass.eikenLevelId')
        ->leftJoin('\Application\Entity\City', 'city', \Doctrine\ORM\Query\Expr\Join::WITH, "goalPass.cityId=city.id")
        ->where('goalPass.year = :year')
        ->andWhere('goalPass.schoolYearCode = 0')
        ->andWhere('goalPass.organizationCode = :orgcode')
        ->andWhere('city.cityCode = 00')
        ->andWhere('goalPass.isDelete = 0')
        ->setParameter(':year', $year)
        ->setParameter(':orgcode', $orgCode)
        ->groupBy("goalPass.eikenLevelId");
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    /*
     * TuanNv21
     * get rate by orgcode and CityId and Year and schoolyear
     */
    public function getListPassByYear($year,$org = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("goalPass.ratePass,goalPass.year, orgschoolYear.id as orgSchoolYearId")
        ->from('\Application\Entity\GoalPass', 'goalPass')
        ->leftJoin('\Application\Entity\OrgSchoolYear', 'orgschoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, "goalPass.schoolYearId=orgschoolYear.schoolYearId")
        ->where('goalPass.year = :yearQ')
        ->andWhere('goalPass.organizationCode = :org')
        ->setParameter(':yearQ', (int)$year)
        ->setParameter(':org', (int)$org)
        ->andWhere('goalPass.isDelete = 0')
        ->orderBy('goalPass.year', 'DESC');
        return $qb->getQuery()->getArrayResult();
    }
}
