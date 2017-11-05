<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\FileDownload;

class FileDownloadRepository extends EntityRepository
{
    public function getDataBySearch($organizationNo){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('fileDownload')
            ->from('\Application\Entity\FileDownload', 'fileDownload')
            ->where('fileDownload.status = :status')
            ->andWhere('fileDownload.isDelete = :isDelete')
            ->andWhere('fileDownload.organizationNo = :organizationNo')
            ->andWhere('fileDownload.startDate <= :currentDate')
            ->andWhere('fileDownload.endDate >= :currentDate')
            ->setParameter(':status', 'Enable')
            ->setParameter(':isDelete', 0)
            ->setParameter(':organizationNo', $organizationNo)
            ->setParameter(':currentDate', date('Y-m-d H:i:s'));
        return $query->getQuery()->getArrayResult();
    }

    public function getOneDataBySearch($organizationNo, $type, $year, $kai){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('fileDownload')
            ->from('\Application\Entity\FileDownload', 'fileDownload')
            ->where('fileDownload.status = :status')
            ->andWhere('fileDownload.isDelete = :isDelete')
            ->andWhere('fileDownload.organizationNo = :organizationNo')
            ->andWhere('fileDownload.type = :type')
            ->andWhere('fileDownload.year = :year')
            ->andWhere('fileDownload.kai = :kai')
            ->andWhere('fileDownload.startDate <= :currentDate')
            ->andWhere('fileDownload.endDate >= :currentDate')
            ->setParameter(':status', 'Enable')
            ->setParameter(':isDelete', 0)
            ->setParameter(':organizationNo', $organizationNo)
            ->setParameter(':type', $type)
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai)
            ->setParameter(':currentDate', date('Y-m-d H:i:s'))
            ->setMaxResults(1);
        return $query->getQuery()->getOneOrNullResult();
    }

    public function insertMultipleRows($dataInserts)
    {
        if (count($dataInserts) < 1) {
            return false;
        }
        $arrayValues = array();
        foreach ($dataInserts as $key => $data) {
            $arrayFields = array_keys($data);
            $arrayValues[$key] = '';

            foreach ($data as $value) {
                $arrayValues[$key] .= $value !== NULL ? "'" . mysql_escape_string($value) . "'," : 'NULL,';
            }

            $arrayValues[$key] = rtrim($arrayValues[$key], ',');
            $arrayValues[$key] = "(" . $arrayValues[$key] . ")";
        }
        $values = implode(",", $arrayValues);
        if (empty($values)) {
            return false;
        }

        $sql = 'INSERT INTO FileDownload(' . implode(',', $arrayFields) . ') VALUES ' . $values . '';
        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeUpdate($sql);
        return $result;
    }

    public function deleteDataByYearAndKai($year, $kai, $type)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->delete('\Application\Entity\FileDownload', 'fileDownload')
            ->where('fileDownload.year = :year')
            ->andWhere('fileDownload.kai = :kai')
            ->andWhere('fileDownload.type = :type')
            ->setParameter('year', intval($year))
            ->setParameter('kai', intval($kai))
            ->setParameter('type', $type);
        return $qb->getQuery()->execute();
    }
}