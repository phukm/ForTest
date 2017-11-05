<?php
namespace InvitationMnt\Service;

use InvitationMnt\Service\ServiceInterface\GenerateServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Doctrine\ORM\EntityManager;

class GenerateService implements GenerateServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const INVITATION_TYPE_EIKEN = 1;

    const INVITATION_TYPE_SCHOOL = 2;

    const INVITATION_TYPE_EINAVI = 3;

    const INVITATION_TYPE_PAYMENT = 4;
    
    const CROSS_EDITING = 'cross-edit-invtn';
    const CROSS_EDITING_MESG = 'cross-edit-invtn-message';
    const CROSS_EDITING_DATA = 'cross-edit-invtn-data';
    

    /**
     *
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    /**
     *
     * @param int $orgId            
     * @param int $schoolYear            
     * @param int $kai            
     * @return array (
     *         sqsData => array(...),
     *         sqsType => combini|invitation,
     *         total => number
     *         )
     */
    public function splitOrgCombini($orgId, $year, $kai)
    {
        /**
         *
         * @var array of calssId => startIndex
         */
        $return = array(
            'sqsData' => array(),
            'sqsType' => 'invitation',
            'total' => 0
        );
        $em = $this->getEntityManager();
        
        $orgRepo = $em->getRepository('Application\Entity\Organization');
        $isExist = $orgRepo->findOneBy(array(
            'id' => $orgId,
            'isDelete' => 0
        ));
        
        if ($isExist) {
            // Common variables for building queries
            // $classExpr is used to generate expression on comparing values, calculating values, etc...
            // $classDql is used to build a custom query follow by Doctrine Query Language
            $classDql = $em->createQueryBuilder();
            $classExpr = $classDql->expr();
            
            $andExpr = $classExpr->andX($classExpr->eq('classj.year', $year), $classExpr->eq('classj.organization', $orgId), $classExpr->eq('classj.isDelete', 0), $classExpr->eq('pupil.isDelete', 0));
            // Trick for the current support of DQL
            $classDql->select('classj.id', $classExpr->count('pupil.id') . ' as totalPupil')
                ->from('\Application\Entity\Pupil', 'pupil')
                ->join('\Application\Entity\ClassJ', 'classj', \Doctrine\ORM\Query\Expr\Join::WITH, $classExpr->eq('classj.id', 'pupil.classId'))
                ->where($andExpr)
                ->groupBy('classj.id');
            
            // $classList is used to check pupil count for each class
            $query = $classDql->getQuery();
            $classList = $query->getArrayResult();
            
            $classDql = $em->createQueryBuilder();
            $andExpr = $classExpr->andX($classExpr->eq('i.eikenScheduleId', $kai), $classExpr->eq('i.organizationId', $orgId), $classExpr->eq('i.isDelete', 0));
            $classDql->select('i.listEikenLevel', 'i.invitationType', 'i.personalPayment', 'i.combini')
                ->from('\Application\Entity\InvitationSetting', 'i')
                ->where($andExpr);
            
            // $eikenLevels is used to calculate the next startIndex value for each class
            $query = $classDql->getQuery();
            $invitationSetting = $query->getArrayResult();
            
            // Only split combini when invitation type is payment or einavi version
            if (count($invitationSetting)) {
                $invitationCombini = \Zend\Json\Decoder::decode($invitationSetting[0]['combini'], \Zend\Json\Json::TYPE_ARRAY);
                $invitationPersonalPayment = \Zend\Json\Decoder::decode($invitationSetting[0]['personalPayment'], \Zend\Json\Json::TYPE_ARRAY);
                
                if ($invitationSetting[0]['invitationType'] != self::INVITATION_TYPE_EINAVI && is_array($invitationPersonalPayment) && in_array(1, $invitationPersonalPayment) && count($invitationCombini)) {
                    $return['sqsType'] = 'combini';
                } else {
                    $return['sqsType'] = 'invitation';
                }
                $eikenLevels = $invitationSetting[0]['listEikenLevel'];
                
                try {
                    // 1|2|3444|22
                    // explode('|')
                    $eikenLevels = json_decode($eikenLevels, false);
                } catch (Exception $e) {
                    // TODO Log this event for tracing purpose
                    $eikenLevels = array(
                        1
                    );
                }
                
                $eikenLevelCount = count($eikenLevels);
                $classCount = count($classList);
                if ($classCount) {
                    $startIndex = 1;
                    
                    foreach ($classList as $classItem) {
                        // If empty class, ignore this class
                        if($classItem['totalPupil'] <= 0){
                            $classCount --;
                        }
                        else {
                            $return['sqsData'][$classItem['id']] = $startIndex;
                            $startIndex += $classItem['totalPupil'] * $eikenLevelCount + 1;
                        }
                    }
                    
                    $return['total'] = $classCount;
                }
            }
        } else {
            // TODO: Report object not found problem
        }
        
        return $return;
    }

    /**
     *
     * @param int $orgId            
     * @param int $total            
     * @param array $requester
     *            Array(
     *            'email' => 'someone@somehwere.com',
     *            'name' => 'requester name',
     *            'active' => 'If sqs type is combinni then 0 else total'
     *            )
     * @return int
     *          1 When record exists
     *          -1 When record does not exists
     */
    public function addProcessLogRecord($orgId, $scheduleId, $total, $requester = array('email' => '', 'name' => '', 'active' => 0))
    {
        $em = $this->getEntityManager();
        
        $plRepo = $em->getRepository('Application\Entity\ProcessLog');
        $processLog = $plRepo->findOneBy(array(
            'orgId' => $orgId,
            'scheduleId' => $scheduleId
        ));
        if ($processLog) {
            $adminInfo = unserialize($processLog->getAdminInfo());
            
            if (! array_key_exists($requester['email'], $adminInfo)) {
                $adminInfo[$requester['email']] = $requester['name'];
            }
            
            $processLog->setAdminInfo(serialize($adminInfo));
            
            $em->persist($processLog);
            $em->flush();
            $em->clear();
            
            return 1;
        } else {
            $processLog = new \Application\Entity\ProcessLog();
            
            $adminInfo = array(
                $requester['email'] => $requester['name']
            );
            
            $processLog->setOrgId($orgId);
            $processLog->setScheduleId($scheduleId);
            $processLog->setTotal($total);
            $processLog->setActive($requester['active']);
            $processLog->setAdminInfo(serialize($adminInfo));
            
            $em->persist($processLog);
            $em->flush();
            $em->clear();
            
            return -1;
        }
        
        return -1;
    }

    public function isMainVenue($orgId, $year = 2016, $kai = 2, $eikenScheduleId = '')
    {
        if ($eikenScheduleId) {
            $eikenScheduleId = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->findOneBy(array('year' => $year, 'id' => $eikenScheduleId));
        }
        else {
            $eikenScheduleId = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->findOneBy(array('year' => $year, 'kai' => $kai));
        }
        $int = $this->getEntityManager()->getRepository('Application\Entity\InvitationSetting')->findOneBy(array(
            'eikenScheduleId' => $eikenScheduleId->getId(),
            'organizationId'  => $orgId,
            'isDelete'        => $isDelete = 0
        ));

        return ($int && $int->getHallType() == 1);
    }
}