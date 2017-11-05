<?php
namespace Application\Entity\Repository;

use Application\Entity\GoalResults;
use Doctrine\ORM\EntityRepository;

class GoalResultsRepository extends EntityRepository
{

    /*
     * @author: MinhTN6
     * get all data goal result of organization and array parameter
     * @param int $orgId
     * @param int $schoolYearId
     * $param $search = array{
     * 'type' => int,
     * 'objectType' => enum(OrgSchoolYear, Class),
     * 'orgSchoolYearId' => int,
     * 'year' => int,
     * 'yearFrom' => int,
     * 'yearTo' => int,
     * }
     * @return array
     */
    public function getListDataByOrgAndArraySearch($orgId, $search = array())
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('goalResults')
            ->from('\Application\Entity\GoalResults', 'goalResults')
            ->where('goalResults.organizationId = :organizationId')
            ->setParameter(':organizationId', $orgId)
            ->andWhere('goalResults.isDelete = 0')
            ->orderBy('goalResults.objectType', 'DESC')
            ->addOrderBy('goalResults.objectId', 'ASC');

        if (! empty($search['type'])) {
            $qb->andWhere('goalResults.type = :type')->setParameter(':type', trim($search['type']));
        }

        if (! empty($search['orgSchoolYearId']) && intval($search['orgSchoolYearId']) > 0) {
            $qb->andWhere($qb->expr()
                ->orX('goalResults.referenceId = :orgSchoolYearId AND goalResults.objectType = \'Class\'', 'goalResults.objectId = :orgSchoolYearId AND goalResults.objectType = \'OrgSchoolYear\''))
                ->setParameter(':orgSchoolYearId', intval($search['orgSchoolYearId']));
        }

        if (! empty($search['objectType'])) {
            $qb->andWhere('goalResults.objectType = :objectType')->setParameter(':objectType', trim($search['objectType']));
        }

        if (! empty($search['year'])) {
            $qb->andWhere('goalResults.year = :year')->setParameter(':year', intval($search['year']));
        }

        if (! empty($search['yearFrom'])) {
            $qb->andWhere('goalResults.year >= :yearFrom')->setParameter(':yearFrom', intval($search['yearFrom']));
        }

        if (! empty($search['yearTo'])) {
            $qb->andWhere('goalResults.year <= :yearTo')->setParameter(':yearTo', intval($search['yearTo']));
        }

        if (! empty($search['yearFrom']) || ! empty($search['yearTo'])) {
            $qb->addOrderBy('goalResults.year', 'ASC');
        }
        return $qb->getQuery()->getArrayResult();
    }

    public function getGoalResult($orgId, $year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('goalResults')
            ->from('\Application\Entity\GoalResults', 'goalResults')
            ->where('goalResults.organizationId = :organizationId')
            ->andWhere('goalResults.year = :year')
            ->andWhere("goalResults.objectType = 'OrgSchoolYear'")
            ->andWhere("goalResults.type = 'Deem'")
            ->andWhere('goalResults.isDelete = 0')
            ->setParameter(':organizationId', $orgId)
            ->setParameter(':year', $year);
        return $qb->getQuery()->getArrayResult();
    }
}
