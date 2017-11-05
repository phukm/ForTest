<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\EikenLevel;

class EikenLevelRepository extends EntityRepository
{
    public function ListEikenLevel()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenlevel')
            ->from('\Application\Entity\EikenLevel', 'eikenlevel','eikenlevel.id')
            ->where('eikenlevel.isDelete = 0')
            ->orderBy('eikenlevel.id', 'asc');

        return $qb->getQuery()->getArrayResult();
    }

    // get level in Recommendedlevel screen
    public function getLevelRemcommend()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ek')->from('\Application\Entity\EikenLevel', 'ek', 'ek.id');
        $result = $qb->getQuery()->getArrayResult();

        return $result;
    }

    // get one object
    public function getEikenLevel($id)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenlevel')
            ->from('\Application\Entity\Year', 'eikenlevel')
            ->where('eikenlevel.id = ?1')
            ->setParameter('1', $id);
        $query = $qb->getQuery();
        $result = $query->getSingleResult();

        return $result;
    }

    /**
     * get Price for all Level
     */
    public function getPriceForAllLevel()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenlevel.id, eikenlevel.standardHallTuitionFee, eikenlevel.mainHallTuitionFee')
            ->from('\Application\Entity\EikenLevel', 'eikenlevel', 'eikenlevel.id')
            ->where('eikenlevel.isDelete = 0')
            ->orderBy('eikenlevel.id', 'ASC');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        return $result;
    }

    /**
     * Get list class
     *
     * @author Anhnt
     */
    public function getPagedClassList($limit = 10, $offset = 0, $year = false, $schoolyear = false, $organizationId = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classj')
            ->from('\Application\Entity\Classj', 'classj')
            ->where($where)
            ->where('classj.organizationId = :organizationId')
            ->setParameter(':organizationId', $organizationId)
            ->andWhere('classj.isDelete = 0')
            ->orderBy('classj.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        if (empty($year) && empty($schoolyear)) {
            $qb->andWhere('classj.year = :year')->setParameter(':year', date('Y'));
        }else if($schoolyear && $year){
            $qb->andWhere('classj.year = :year')
                    ->andWhere('classj.orgSchoolYear = :orgSchoolYear')
                    ->setParameter(':year', intval($year))
                    ->setParameter(':orgSchoolYear', intval($schoolyear));
        }else{
            if ($year && empty($schoolyear)) {
                $qb->andWhere('classj.year = :year')->setParameter(':year', intval($year));
            }
            if ($schoolyear && empty($year)) {
                $qb->andWhere('classj.orgSchoolYear = :orgSchoolYear')->setParameter(':orgSchoolYear', intval($schoolyear));
            }
        }
        
        $query = $qb->getQuery();
        $paginator = new Paginator($query);

        return $paginator;
    }

    /*
     * Get Price By List Eiken Level
     * @param $arr_eiken_level
     * @return array price
     * */
    public function getPricesByListEikenLevel($eikenLevelIds){
        if(empty($eikenLevelIds)){
            return false;
        }
        $priceEikenLevel = array();
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('eikenlevel.id, eikenlevel.levelName, eikenlevel.standardHallTuitionFee, eikenlevel.mainHallTuitionFee')
        ->from('\Application\Entity\EikenLevel', 'eikenlevel', 'eikenlevel.id')
        ->where($qb->expr()->in('eikenlevel.id', ':eikenLevelIds'))
        ->setParameter(':eikenLevelIds', $eikenLevelIds)
        ->andWhere('eikenlevel.isDelete = 0')
        ->orderBy('eikenlevel.id', 'ASC');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        foreach($result as $value){
            $priceEikenLevel[0][$value["id"]]["price"] = intval($value["standardHallTuitionFee"]) == 0 ? $value["mainHallTuitionFee"] : $value["standardHallTuitionFee"];
            $priceEikenLevel[0][$value["id"]]["name"] = $value["levelName"];
            $priceEikenLevel[1][$value["id"]]["price"] = $value["mainHallTuitionFee"];
            $priceEikenLevel[1][$value["id"]]["name"] = $value["levelName"];
        }

        return $priceEikenLevel;
    }
    
    public function listEikenLevelName()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenlevel.id, eikenlevel.levelName')
            ->from('\Application\Entity\EikenLevel', 'eikenlevel','eikenlevel.id')
            ->where('eikenlevel.isDelete = 0')
            ->orderBy('eikenlevel.id', 'asc');

        return $qb->getQuery()->getArrayResult();
    }

    /*
     * get get list price by array eikenLevelIds 
     * @param array $eikenLevelIds
     * @author: minhtn6
     * 
     * @return array
     */
    public function getListDataByEikenLevelIds($eikenLevelIds) {
        if (!$eikenLevelIds) {
            return false;
        }

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('eikenlevel')
                    ->from('\Application\Entity\EikenLevel', 'eikenlevel', 'eikenlevel.id')
                    ->where($qb->expr()->in('eikenlevel.id', ':eikenLevelIds'))
                    ->setParameter(':eikenLevelIds', $eikenLevelIds)
                    ->andWhere('eikenlevel.isDelete = 0')
                    ->orderBy('eikenlevel.id', 'ASC');

        return $query->getQuery()->getArrayResult();
    }

}