<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\City;
use Doctrine\ORM\EntityRepository;

class CityRepository extends EntityRepository
{

    public function getCityName()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('city.cityName')->form('Application\Entity\City', 'city');
        $query = $qb->getQuery();
        $result = $query->getSingleResult();
        return $result;
    }

    /*
     * DucNA17
     * Get city name by CityId
     */
    public function getCityNameByCityId($cityId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('city.cityName')
            ->from('Application\Entity\City', 'city')
            ->where('city.id = :id')
            ->setParameter(':id', $cityId);
        $query = $qb->getQuery();
        $result = $query->getSingleResult();
        return $result;
    }

    /**
     *
     * @return get all cities except '00'
     */
    public function getApplyEikCitiesList ($isStandardHall = false, $field = false, $eikenIdForm = false,$showBroad = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('city')
            ->from('Application\Entity\City', 'city')
            ->innerJoin('Application\Entity\District', 'district', 'WITH', 'city.id = district.cityId')
            ->distinct()
            ->where('city.isDelete = 0')
            ->andWhere('city.cityCode != \'00\'')
            ->andWhere('city.status = \'Enable\'');

        if ($eikenIdForm)
            $qb->andWhere('city.cityCode != \'99\'');

        if ($isStandardHall){
            if($showBroad){
                $qb->andWhere('(district.forHallType = 0 OR city.cityCode = \'99\')');
            }else{
                $qb->andWhere('district.forHallType = 0');
            }
        }
        if ($field)
            $qb->andWhere('district.' . $field . '= 1');
        $qb->orderBy('city.cityCode', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function getListCity()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('city')
            ->from('Application\Entity\City', 'city', 'city.id')
            ->where('city.isDelete = 0')
         ->orderBy('city.cityCode', 'ASC');

        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        return $result;
    }
    
    public function getListCityWithDistrict()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('city')
            ->from('Application\Entity\City', 'city')
            ->innerJoin('Application\Entity\District', 'district', 'WITH', 'city.id = district.cityId')
            ->distinct()
            ->where('city.isDelete = 0')
            ->andWhere("city.cityName <> '海外'")// not show this city
         ->orderBy('city.cityCode', 'ASC');

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }
    
    public function getCityMasterData() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('city.id,city.cityCode')
            ->from('\Application\Entity\City', 'city');

        $query = $qb->getQuery();

        $result = $query->getArrayResult();
        return $result;
    }
}