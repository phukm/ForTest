<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class NewsEikenRepository extends EntityRepository
{
    public function getListNews()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('news.description, news.newsDate, news.url')
            ->from('\Application\Entity\NewsEiken', 'news')
            ->orderBy('news.newsDate', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(3);

        return $qb->getQuery()->getArrayResult();
    }
}