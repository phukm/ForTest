<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\EinaviExam;
use Zend\Db\Sql\Where;
use Doctrine\ORM\Query\ResultSetMapping;
use Eiken\Helper\NativePaginator;
use DoctrineORMModuleTest\Assets\Entity\Date;

class EinaviExamRepository extends EntityRepository
{

    /**
     * Update pupilId in table LearningHistory.
     * Author: Uthv
     * *
     */
    public function updatePupilIdInLearningHistory()
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $sql = "UPDATE LearningHistory
                INNER JOIN EinaviInfo
                        ON LearningHistory.PersonalId = EinaviInfo.PersonalId
                SET LearningHistory.PupilId = EinaviInfo.PupilId
                WHERE LearningHistory.PupilId IS NULL;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    /**
     * Update pupilId in table EinaviExam.
     * Author: Uthv
     * *
     */
    public function updatePupilIdInEinaviExam()
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $sql = "UPDATE EinaviExam
                INNER JOIN EinaviInfo
                        ON EinaviExam.PersonalId = EinaviInfo.PersonalId
                SET EinaviExam.PupilId = EinaviInfo.PupilId
                WHERE EinaviExam.PupilId IS NULL;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
}

