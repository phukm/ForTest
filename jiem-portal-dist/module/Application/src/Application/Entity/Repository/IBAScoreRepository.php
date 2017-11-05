<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\IBAScore;

class IBAScoreRepository extends EntityRepository
{
    /**
     * Ducna17
     * update list record IbaScore ibaTestResultId: StatusSave = 1
     * @param array $ids
     */
    public function updateStatusSave($ibaTestResultIds)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('\Application\Entity\IbaScore', 'ibaScore')
        ->set('ibaScore.statusSave', 1)
        ->where('ibaScore.ibaTestResultId IN (:ibaTestResultIds)')
        ->setParameter('ibaTestResultIds', $ibaTestResultIds);
        $qb->getQuery()->execute();
    }

    public function deleteIBAScore($list)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->delete('\Application\Entity\IbaScore', 'ibaScore')
        ->where('ibaScore.ibaTestResultId IN (:list)')
        ->setParameter(':list', $list);
        $qb->getQuery()->execute();
    }
    
    
    public function getIBAScoreByPupilIdsAndDate($pupilIds, $datetime)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ibar.pupilId, ibar.eikenLevelId, ibar.examDate,
            ibar.readingScore, ibar.listeningScore')
            ->from('\Application\Entity\IBAScore', 'ibar', 'ibar.id')
            ->where('ibar.examDate >= :datetime')
            ->setParameter(':datetime', $datetime)
           ->andWhere($qb->expr()->in('ibar.pupilId', ':pupilIds'))
            ->setParameter(':pupilIds', $pupilIds)
            ->andWhere('ibar.eikenLevelId IS NOT NULL')
            ->andWhere('ibar.isDelete = 0')
            ->orderBy('ibar.eikenLevelId', 'ASC')
            ->addOrderBy('ibar.pupilId', 'ASC')   
            ->addOrderBy('ibar.examDate', 'DESC')
            ->addOrderBy('ibar.status', 'ASC');
            
        return $qb->getQuery()->getArrayResult();
        
    }
    
    public function insertMultipleRows($ibaScores) {
        if (count($ibaScores) < 1) {
            return false;
        }
        $arrayValues = array();
        foreach ($ibaScores as $key => $ibaScore) {
            $arrayFields = array_keys($ibaScore);
            $arrayValues[$key] = '';
            foreach ($ibaScore as $value) {
                $arrayValues[$key] .= $value !== NULL ? "'" . mysql_escape_string($value) . "'," : 'NULL,';
            }
            
            $arrayValues[$key] = rtrim($arrayValues[$key], ',');
            $arrayValues[$key] = "(" . $arrayValues[$key] . ")";
        }
        $values = implode(",", $arrayValues);
        if (empty($values)) {
            return false;
        }

        $sql = 'INSERT INTO IBAScore(' . implode(',', $arrayFields) . ') VALUES ' . $values . '';

        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeUpdate($sql);
        return $result;
    }
}