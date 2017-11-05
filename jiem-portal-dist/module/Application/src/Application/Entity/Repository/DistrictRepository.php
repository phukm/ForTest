<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\City;
use Doctrine\ORM\EntityRepository;

class DistrictRepository extends EntityRepository
{
    public function getListDistrict()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('district')
            ->from('Application\Entity\District', 'district', 'district.id')
            ->where('district.isDelete = 0')
            ->andWhere("district.name <> '島部'") // not show this district
         ->orderBy('district.id', 'ASC');

        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        return $result;
    }
    
     public function getListDistrictInCity($cityId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('district')
            ->from('Application\Entity\District', 'district')
            ->where('district.isDelete = 0')
            ->andWhere('district.cityId = :id')
            ->andWhere("district.name <> '島部'") // not show this district
            ->setParameter(':id', $cityId)
         ->orderBy('district.id', 'ASC');

        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        return $result;
    }
    
    public function getListDistrictByCode()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('district')
            ->from('Application\Entity\District', 'district', 'district.code')
            ->where('district.isDelete = 0')
            ->andWhere("district.name <> '島部'") // not show this district
         ->orderBy('district.id', 'ASC');

        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        return $result;
    }
}