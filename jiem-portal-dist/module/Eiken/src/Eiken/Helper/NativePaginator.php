<?php

namespace Eiken\Helper;
use Zend\Paginator\Adapter\AdapterInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
#use Doctrine\ORM\Tools\Pagination\Paginator;
#use Doctrine\ORM\Tools\Pagination\CountWalker;

 
class NativePaginator implements \Countable, AdapterInterface
{
    /**
     * @var Query
     */
    private $query = '';
    
    /**
     * @var DQL Query
     */
    private $dql = '';
    
    /**
     * @var array
     */
    private $params = array();
    
    /**
     * @var Object
     */
    private $queryObj = null;

    /**
     * @var String
     */
    private $queryType = 'DoctrineORMNativeQuery';
    
    /**
     * @var int
     */
    private $count;

    public function __construct($query, $type = 'DoctrineORMNativeQuery')
    {
        $this->queryType = $type;
        
        if( $this->queryType == "DoctrineORMNativeQuery")
        {
            $this->query = $query->getSQL();
            $this->params = $query->getParameters();
        } 
        else
        {
            $this->dql = $query->getQuery()->getDQL();
            $this->query = $query->getQuery()->getSQL();
            $this->params = $query->getQuery()->getParameters();
        }

        $this->queryObj = $query;
    }

    /**
     * @return int
     */
    public function count()
    {
        if ($this->count === null) 
        {
            if( $this->queryType != "DoctrineORMNativeQuery" && count($this->params) > 0)
            {
                $dql = $this->queryObj->getDQL();
                
                $regex = '/(\:|\?)\w+/';
                if(preg_match_all($regex, $dql, $matches) !== false)
                {
                    $params = $matches[0];
                }
                
                foreach($params as $param)
                {
                    $param = str_replace("?", "::", $param);
                    $this->query = preg_replace('/\?/', $param, $this->query, 1);
                }   
                $this->query = str_replace("::", "?", $this->query);
            }
            
            $rsm = new ResultSetMappingBuilder($this->queryObj->getEntityManager());
            $rsm->addScalarResult('count', 'count');
            $sqlCount = 'select count(*) as count from (' . $this->query . ') as item';
            $qCount = $this->queryObj->getEntityManager()->createNativeQuery($sqlCount, $rsm);
            
            foreach($this->params as $param)
            {
                $keyName = str_replace("?", "", $param->getName());
                $keyName = ( is_numeric( $keyName) ) ? '?'.$keyName : ':'. $keyName;
                $qCount->setParameter($keyName, $param->getValue());
            }
            
            $this->count = (int)$qCount->getSingleScalarResult();

        }
        
        return $this->count;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return ArrayObject 
     */
    public function getItems($offset = 0, $limit = 10, $isReturnArray = true)
    {   
        if ( $this->queryType == "DoctrineORMNativeQuery" )
        {
            $this->queryObj->setSQL($this->query . ' limit ' . (int)$offset . ', ' . (int)$limit);
            $query = $this->queryObj;
        } 
        else 
        {
            $query = $this->queryObj->setFirstResult((int)$offset)->setMaxResults((int)$limit)->getQuery();
        }
        
        if((bool)$isReturnArray)
        {
            return $query->getArrayResult();
        }
        else
        {
            return $query->getResult();
        } 
    }
    
    /**
     * This function will be return turn all record without limit
     * 
     * @return ArrayObject
     */
    public function getAllItems($isReturnArray = true)
    {
        if ( $this->queryType == "DoctrineORMNativeQuery" )
        {
            $query = $this->queryObj;
        }
        else
        {
            $query = $this->queryObj->getQuery();
        }
        
        if((bool)$isReturnArray)
        {
            return $query->getArrayResult();
        }
        else
        {
            return $query->getResult();
        }
    }
    
    /**
     * this function will be return the query
     * 
     */
    public function getSQL()
    {
        return $this->query;
    }
    
    /**
     * this function will be return the DQL
     *
     */
    public function getDQL()
    {
        return $this->dql;
    }
    /**
     * this function will be return the params
     *
     */
    public function getParameters()
    {
        return $this->params;
    }
}