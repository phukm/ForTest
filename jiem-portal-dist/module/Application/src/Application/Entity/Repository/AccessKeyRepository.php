<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class AccessKeyRepository extends EntityRepository
{
    public function getAccessKeyMasterData($year , $kai) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('accessKey.organizationNo')
            ->from('\Application\Entity\AccessKey', 'accessKey')
            ->where('accessKey.year = :year')
            ->andWhere('accessKey.kai = :kai')
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai);

        $query = $qb->getQuery();

        $result = $query->getArrayResult();
        return $result;
    }

    /**
     * @param $listAccessKey
     * @return mixed
     */
    public function insertAccessKeyMasterData($listAccessKey)
    {
        if (empty($listAccessKey)) {
            return false;
        }
        $em = $this->getEntityManager();
        $headers = array(
            'organizationNo',
            'accessKey',
            'year',
            'kai',
            'status',
            'isDelete',
        );

        // create sql data for insert.
        $sqlData = "";
        foreach ($listAccessKey as $item) {
            $sqlData .= ", ("
                . "'" . mysql_escape_string($item['organizationNo']) . "'"
                . ", '" . mysql_escape_string($item['accessKey']) . "'"
                . ", " . intval($item['year'])
                . ", " . intval($item['kai'])
                . ", 'Enable'"
                . ', 0'
                . ')';
        }
        $sqlData = trim($sqlData, ",");

        // create sql columns.
        $sqlColumn = implode(",", $headers);

        $tableName = $em->getClassMetadata('Application\Entity\AccessKey')->getTableName();

        // create insert sql from data and columns.
        $sql = 'INSERT INTO ' . $tableName . ' (' . $sqlColumn . ') VALUES ' . $sqlData;

        return $em->getConnection()->executeUpdate($sql);
    }

    public function updateAccessKeyMasterData($listAccessKey)
    {
        if (empty($listAccessKey)) {
            return false;
        }
        $em = $this->getEntityManager();

        // create sql data for insert.
        $sqlSetAccessKey = "";
        $year = "";
        $kai = "";
        $listOrgNo = '';
        foreach ($listAccessKey as $item) {
            $sqlSetAccessKey .= " WHEN " . mysql_escape_string($item['organizationNo']) . " THEN '" . mysql_escape_string($item['accessKey']) . "'";
            $year = $item['year'];
            $kai = $item['kai'];
            if(empty($listOrgNo)){
                $listOrgNo = "'".$item['organizationNo']."'";
            }else{
                $listOrgNo .= ','."'".$item['organizationNo']."'";
            }
        }
        
        // create params.
        $tableName = $em->getClassMetadata('Application\Entity\AccessKey')->getTableName();
        $time = date('Y-m-d H:i:s');

        // create insert sql from data and columns.
        $sql = "UPDATE " . $tableName . " a
                SET a.accessKey = CASE a.organizationNo " . $sqlSetAccessKey . " END,
                    a.status = 'Enable',
                    a.isDelete = 0,
                    a.updateAt = '".$time."'
                WHERE a.organizationNo IN (" . $listOrgNo . ")"
                . " AND a.year = " . intval($year)
                . " AND a.kai = " . intval($kai);

        return $em->getConnection()->executeUpdate($sql);
    }
    
    public function deleteAccessKeyForNewKai($year,$kai) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $query = $qb->update('\Application\Entity\AccessKey', 'accessKey')
                ->set('accessKey.isDelete', '1')
                ->where('accessKey.year = :year')             
                ->andWhere("accessKey.kai =:kai")
                ->setParameter(':year', $year)
                ->setParameter(':kai', $kai)
                ->getQuery();
        $query->execute();
    }
}