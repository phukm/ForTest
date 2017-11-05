<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\RecommendLevel;
use Zend\Form\Element\Date;
use Eiken\Helper\NativePaginator as DTPaginator;

class RecommendedRepository extends EntityRepository
{
    public function getKai_Year($year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('kai')
            ->from('\Application\Entity\EikenSchedule', 'eks')
            ->where('eks.year = ' . (int)$year)
            ->groupBy('kai');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        return $result;
    }

    public function getResultRecommend($orgId , $year, $kai, $orgSchoolYearId = '', $classId = '', $eikenLevelId = '', $fullName = '')
    {
		$qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('Pupil.id AS PupilId, RecommendLevel.eikenLevelId AS resultLevel,SimpleMeasurementResult.resultVocabularyId,
                    SimpleMeasurementResult.resultGrammarId, StandardLevelSetting.eikenLevelId AS standardlevelId,
                    CONCAT(Pupil.firstNameKana,Pupil.lastNameKana) AS nameKana, CONCAT(Pupil.firstNameKanji,Pupil.lastNameKanji) AS nameKanji,
                    Pupil.number AS SerialID, EikenLevel.id AS EikenLevelId, EikenLevel.levelName , OrgSchoolYear.id AS OrgSchoolYearId,
                    OrgSchoolYear.displayName, EikenSchedule.year, EikenSchedule.kai, OrgSchoolYear.schoolYearId,ClassJ.id as classId, ClassJ.className, RecommendLevel.isManuallySet')
            ->from('\Application\Entity\Pupil', 'Pupil', 'Pupil.id')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'OrgSchoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'Pupil.orgSchoolYear = OrgSchoolYear.id')
            ->leftJoin('\Application\Entity\ClassJ', 'ClassJ', \Doctrine\ORM\Query\Expr\Join::WITH, 'Pupil.classId = ClassJ.id AND ClassJ.isDelete = 0')
            ->leftJoin('\Application\Entity\SimpleMeasurementResult', 'SimpleMeasurementResult', \Doctrine\ORM\Query\Expr\Join::WITH, 'Pupil.id = SimpleMeasurementResult.pupil AND SimpleMeasurementResult.isDelete =0')
            ->leftJoin('\Application\Entity\StandardLevelSetting', 'StandardLevelSetting', \Doctrine\ORM\Query\Expr\Join::WITH, 'Pupil.orgSchoolYear = StandardLevelSetting.orgSchoolYear AND StandardLevelSetting.year=:year AND StandardLevelSetting.isDelete = 0')
            ->leftJoin('\Application\Entity\EikenLevel', 'EikenLevel', \Doctrine\ORM\Query\Expr\Join::WITH, 'StandardLevelSetting.eikenLevel = EikenLevel.id')
            ->leftJoin('\Application\Entity\EikenSchedule', 'EikenSchedule', \Doctrine\ORM\Query\Expr\Join::WITH, 'EikenSchedule.isDelete = 0  AND EikenSchedule.year = :year AND EikenSchedule.kai= :kai')
            ->leftJoin('\Application\Entity\RecommendLevel', 'RecommendLevel', \Doctrine\ORM\Query\Expr\Join::WITH, 'Pupil.id = RecommendLevel.pupil AND RecommendLevel.eikenScheduleId = EikenSchedule.id')
            ->where('Pupil.isDelete = 0')
            ->andWhere('OrgSchoolYear.isDelete = 0')
            ->andWhere('ClassJ.isDelete = 0')
            ->andWhere('Pupil.organization = :orgid')
            ->andWhere('ClassJ.year = :year')
            ->andWhere('Pupil.year = :year')
            ->orderBy('OrgSchoolYear.schoolYearId', 'ASC')
            ->addOrderBy('ClassJ.className', 'ASC')
            ->addOrderBy('Pupil.number', 'ASC')
            ->addOrderBy('Pupil.id', 'ASC')
            ->groupBy('Pupil.id');
        
        if( $orgSchoolYearId > 0 )
        {
            if( $classId )
            {
                $query->andWhere('ClassJ.id = :classId')->setParameter(':classId', $classId);
            }
            else 
            {
                $query->andWhere('ClassJ.orgSchoolYear = :orgSchoolYearId')->setParameter(':orgSchoolYearId', $orgSchoolYearId);
                $query->andWhere('ClassJ.organization = :orgid');
            }
        }
        else 
        {
            $query->andWhere('ClassJ.organization = :orgid');
        }
        
        if ( $eikenLevelId > 0 ) 
        {
            $query->andWhere('RecommendLevel.eikenLevelId = :eikenLevelId')->setParameter(':eikenLevelId', $eikenLevelId );
        }
        
        $fullName = trim($fullName);
        if ( $fullName ) 
        {
            $query->andWhere('( CONCAT(Pupil.firstNameKanji,Pupil.lastNameKanji) LIKE :fullName OR  CONCAT(Pupil.firstNameKana,Pupil.lastNameKana) LIKE :fullName )')->setParameter(':fullName', '%'.$fullName.'%' );
        }
        $query->setParameter(':orgid', $orgId);
        $query->setParameter(':year', $year);
        $query->setParameter(':kai', $kai);
        
        $paginator = new DTPaginator($query,  "DoctrineORMQueryBuilder");
        
        return $paginator;
    }

    public function getEikenScore($pupilList, $datetime)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e.pupilId AS PupilId, e.id AS EikenTestResultId, e.eikenLevelId AS EikenTestResultLevel,
                    e.certificationDate AS examDateE1, e.passFailFlag AS EikenPassFailFlag')
            ->from('\Application\Entity\EikenScore', 'e', 'e.id')
            ->where('e.certificationDate >= :datetime')
            ->setParameter(':datetime', $datetime)
            ->andWhere('e.pupilId IN (:pupilList)')
            ->setParameter(':pupilList', $pupilList)
            ->andWhere('e.eikenLevelId IS NOT NULL')
            ->orderBy('e.eikenLevelId', 'ASC')
            ->addOrderBy('e.passFailFlag', 'DESC')
            ->addOrderBy('e.pupilId', 'ASC')
            ->addOrderBy('e.certificationDate', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }

    public function getIBAScore($pupilList, $datetime)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('i.pupilId AS PupilId, i.id AS IBATestResultId, i.eikenLevelId AS IBA,
                    i.examDate AS examDateIBA, i.passFailFlag AS IBAPassFailFlag')
            ->from('\Application\Entity\IBAScore', 'i', 'i.id')
            ->where('i.examDate >= :datetime')
            ->setParameter(':datetime', $datetime)
            ->andWhere('i.pupilId IN (:pupilList)')
            ->setParameter(':pupilList', $pupilList)
            ->andWhere('i.eikenLevelId IS NOT NULL')
            ->orderBy('i.eikenLevelId', 'ASC')
            ->addOrderBy('i.pupilId', 'ASC')   
            ->addOrderBy('i.examDate', 'DESC');
            
        return $qb->getQuery()->getArrayResult();
        
    }

    public function duplicateRecommendLevel($eikenScheduleId, $listPupil)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r.pupilId,r.id')
            ->from('\Application\Entity\RecommendLevel', 'r', 'r.id')
            ->where('r.eikenScheduleId = :eikenScheduleId')
            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->andWhere('r.pupilId IN (:listPupil)')
            ->setParameter(':listPupil', $listPupil);

        return $qb->getQuery()->getArrayResult();
    }

    public function getListPersonalId($organization, $year)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p.id,p.personalId')
            ->from('\Application\Entity\Pupil', 'p', 'p.personalId')
            ->where('p.organization = :orgId')
            ->setParameter(':orgId', $organization)
            ->andWhere('p.year = :year')
            ->setParameter(':year', $year)
            ->andWhere('p.personalId > 0');

        return $qb->getQuery()->getArrayResult();
    }

    public function getSimpleMeasurementResultId($listPupilId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('s.id,s.pupilId')
            ->from('\Application\Entity\SimpleMeasurementResult', 's', 's.pupilId')
            ->where('s.pupilId IN (:listPupilIds)')
            ->setParameter(':listPupilIds', $listPupilId);

        return $qb->getQuery()->getArrayResult();
    }

    public function getListRecommendPupilId($listPupilId, $eikenScheduleId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r.id,r.pupilId')
            ->from('\Application\Entity\RecommendLevel', 'r', 'r.pupilId')
            ->where('r.eikenScheduleId = :eikenScheduleId')
            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->andWhere('r.pupilId IN (:listPupilId)')
            ->setParameter(':listPupilId', $listPupilId);

        return $qb->getQuery()->getArrayResult();
    }

    public function getResultPupil($OrganizationId, $listPupilId, $EikenScheduleId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('Pupil.id,Pupil.year,IBATestResult.id AS IBATestResultId,EikenTestResult.id AS EikenTestResultId,StandardLevelSetting.id AS StandardLevelSettingId')
            ->from('\Application\Entity\Pupil', 'Pupil', 'Pupil.id')
            ->leftJoin('\Application\Entity\IBATestResult', 'IBATestResult', \Doctrine\ORM\Query\Expr\Join::WITH, 'Pupil.id = IBATestResult.pupilId AND IBATestResult.eikenScheduleId = :eikenScheduleId')
            ->leftJoin('\Application\Entity\EikenTestResult', 'EikenTestResult', \Doctrine\ORM\Query\Expr\Join::WITH, 'Pupil.id = EikenTestResult.pupilId AND Pupil.year = EikenTestResult.year AND EikenTestResult.eikenScheduleId = :eikenScheduleId')
            ->leftJoin('\Application\Entity\StandardLevelSetting', 'StandardLevelSetting', \Doctrine\ORM\Query\Expr\Join::WITH, 'Pupil.orgSchoolYearId = StandardLevelSetting.orgSchoolYearId AND Pupil.year = StandardLevelSetting.year')
            ->where('Pupil.organizationId = :organizationId')
            ->andWhere('Pupil.id IN (:listPupilId)')
            ->setParameter(':eikenScheduleId', $EikenScheduleId)
            ->setParameter(':listPupilId', $listPupilId)
            ->setParameter(':organizationId', $OrganizationId);

        return $qb->getQuery()->getArrayResult();
    }
}