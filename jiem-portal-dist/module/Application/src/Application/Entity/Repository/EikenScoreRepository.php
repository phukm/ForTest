<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\EikenScore;

class EikenScoreRepository extends EntityRepository
{
    const FAIL = 0;
    const SUCCESS = 1;
    /**
     * Ducna17
     * update list record EikenScore eikenTestResultId: StatusSave = 1
     * @param array $ids
     */
    public function updateStatusSave($eikenTestResultId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('\Application\Entity\EikenScore', 'eikenScore')
        ->set('eikenScore.statusSave', 1)
        ->where('eikenScore.eikenTestResultId IN (:eikenTestResultIds)')
        ->setParameter('eikenTestResultIds', $eikenTestResultId);
        $qb->getQuery()->execute();
    }
    
    public function deleteEikenScore($listEikenResultIds)
    {
        $em = $this->getEntityManager();
        
        $qb = $em->createQueryBuilder();
        $qb->delete('\Application\Entity\EikenScore', 'eikenScore')
        ->where('eikenScore.eikenTestResultId IN (:listEikenResultIds)')
        ->setParameter(':listEikenResultIds', $listEikenResultIds);
        try {
            $qb->getQuery()->execute();
            return self::SUCCESS;
        } catch (Exception $e){
            return self::FAIL;
        } 
    }
    
    public function getEikenScoreByPupilIdsAndDate($pupilIds, $datetime){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ekr.id, ekr.pupilId, ekr.eikenLevelId, ekr.year, ekr.kai,
            ekr.readingScore, ekr.listeningScore, ekr.cSEScoreWriting, ekr.cSEScoreSpeaking')
            ->from('\Application\Entity\EikenScore', 'ekr', 'ekr.id')
            ->where('ekr.certificationDate >= :datetime')
            ->setParameter(':datetime', $datetime)
            ->andWhere($qb->expr()->in('ekr.pupilId', ':pupilIds'))
            ->setParameter(':pupilIds', $pupilIds)
            ->andWhere('ekr.eikenLevelId IS NOT NULL')
            ->andWhere('ekr.passFailFlag = 1')
            ->andWhere('ekr.isDelete = 0')
            ->orderBy('ekr.eikenLevelId', 'ASC')
            ->addOrderBy('ekr.pupilId', 'ASC')
            ->addOrderBy('ekr.certificationDate', 'DESC')
            ->addOrderBy('ekr.status', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

    public function insertMultipleRows($eikenScores) {
        if (count($eikenScores) < 1) {
            return false;
        }
        $arrayValues = array();
        foreach ($eikenScores as $key => $eikenScore) {
            $arrayFields = array_keys($eikenScore);
            $arrayValues[$key] = '';

            foreach ($eikenScore as $value) {
                $arrayValues[$key] .= $value !== NULL ? "'" . mysql_escape_string($value) . "'," : 'NULL,';
            }
            
            $arrayValues[$key] = rtrim($arrayValues[$key], ',');
            $arrayValues[$key] = "(" . $arrayValues[$key] . ")";
        }
        $values = implode(",", $arrayValues);
        if (empty($values)) {
            return false;
        }

        $sql = 'INSERT INTO EikenScore(' . implode(',', $arrayFields) . ') VALUES ' . $values . '';
        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeUpdate($sql);
        return $result;
    }

}