<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SimpleMeasurementResultRepository extends EntityRepository
{

    public function getResultByPupilIds($pupilIds){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('smr.pupilId, smr.resultVocabularyName, smr.resultGrammarName')
                    ->from('\Application\Entity\SimpleMeasurementResult', 'smr')
                    ->andWhere($qb->expr()->in('smr.pupilId', ':pupilIds'))
                    ->setParameter(':pupilIds', $pupilIds)
                    ->andWhere('smr.status = :status')
                    ->setParameter(':status', 'Active')
                    ->andWhere('smr.isDelete = 0');

        return $query->getQuery()->getArrayResult();
    }
}