<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ConsoleInvitation for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace ConsoleInvitation\Controller;

use ConsoleInvitation\Service\InvitationGenerator;
use Dantai\Aws\AwsS3Client;
use Zend\Form\Element\DateTime;
use Zend\Mvc\Controller\AbstractActionController;

class ConsoleInvitationController extends AbstractActionController
{
    const REMOVE_DEADLOCK_TIME = '-3 hours';
    const REMOVE_PROCESS_LOG_TIME = '-24 hours';

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function getConfig()
    {
        return $this->getServiceLocator()->get('Config')['ConsoleInvitation'];
    }

    public function getGlobalConfig()
    {
        return $this->getServiceLocator()->get('Config');
    }

    public function receivePaymentInfoFromEcontextAction()
    {
        $combini = new \ConsoleInvitation\Service\Combini($this->getServiceLocator());
        $combini->receivePaymentInfoFromEcontext();
    }

    public function sendMailAction()
    {
        $globalConfig = $this->getServiceLocator()->get('Config');
        $emailSender = isset($globalConfig['emailSender']) ? $globalConfig['emailSender'] : 'dantai@mail.eiken.or.jp';
        $to = (array)$this->params('to');
        $type = $this->params('a');

        if (!filter_var($this->params('to'), FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid input email!');
        }
        if (3 == $type) {
            $name = $this->params('b');
            \Dantai\Aws\AwsSesClient::getInstance()->deliver($emailSender, $to, $type, array(

                'name' => $name,
                'url' => 'http://dantaiportal.dev/public'
            ));
        } else
            if (4 == $type) {
                $error = (array)$this->params('content');
                \Dantai\Aws\AwsSesClient::getInstance()->deliver($emailSender, $to, $type, $error);
            }
    }

    public function isExpiredLog($dateSend)
    {
        if (null === $dateSend) {
            return false;
        }
        $expireDate = new \DateTime(self::REMOVE_PROCESS_LOG_TIME);
        return ($dateSend < $expireDate);
    }

    public function generateInvitationLetterAction()
    {
        $return = new \Zend\View\Model\ConsoleModel();

        $sqsMessage = new \ConsoleInvitation\Helper\SqsMessage(\Dantai\Aws\AwsSqsClient::QUEUE_GEN_INVITATION);

        if ($sqsMessage->isEmpty()) {
            $return->setErrorLevel(1);
            return $return;
        }

        $classId = $sqsMessage->getClassId();
        $orgId = $sqsMessage->getOrgId();
        $scheduleId = $sqsMessage->getScheduleId();
        $priceLevels = $sqsMessage->getPriceLevels();

        $processLogManager = \ConsoleInvitation\Service\ProcessLogManager::getInstance($orgId, $scheduleId, $this->getServiceLocator());

        $em = $this->getEntityManager();

        /* @var $processLog \Application\Entity\ProcessLog */
        $processLog = $processLogManager->getProcessLog();

        if (empty($processLog)) {
            $sqsMessage->delete();
            return;
        }

        if ($processLog->getIsError()) {
            $processLogManager->countDownTotal();

            $processLogManager->removeProcessLogComplete();
            $sqsMessage->delete();

            return;
        }
        // check ready runable
        if (!$processLog->isRunable()) {
            $sqsMessage->delete();

            if ($this->isExpiredLog($processLog->getSendCombiniAt())) {
                $processLogManager->countDownTotal();
                $processLogManager->getProcessLog()->setIsError(1);
                $processLogManager->saveProcessLog();
                $processLogManager->removeProcessLogComplete();
                $this->sendMail($processLog, 3);
                return;
            }
            $sqsMessage->recycleMessage();

            $return->setErrorLevel(1);
            return $return;
        }

        // generate html of invitation letter
        /** @var InvitationGenerator $invitationGenerator */
        $invitationGenerator = $this->getServiceLocator()->get('ConsoleInvitation\Service\InvitationGenerator');

        /* @var $invitationSetting \Application\Entity\InvitationSetting */
        $invitationSetting = $invitationGenerator->getInvitationSetting($orgId, $scheduleId);
        $invitationGenerator->renderInvitationLetterOfClass($classId, $orgId, $scheduleId, $priceLevels);

        $patchToConsole = realpath(__DIR__ . '/../../../../../console');
        $patchToHtml = $this->getConfig()['htmlExportDir'] . '/invitationLetter/' . $invitationSetting->getEikenScheduleId() . '/' . $classId . '/';

        shell_exec($patchToConsole . '/converttopdf.sh "' . $patchToHtml . '"');

        $sqsMessage->delete();

        $processLogManager->countDownTotal();

        if (false === $processLogManager->isGenerateComplete(true)) {
            return;
        }

        $invitationSetting = $invitationGenerator->getInvitationSetting($orgId, $scheduleId, true);

        $invitationSetting->setTempHallType($invitationSetting->getHallType());
        $invitationSetting->setTempPaymentType($invitationSetting->getPaymentType());
        $invitationSetting->setTempOrganizationPayment($invitationSetting->getOrganizationPayment());
        $invitationSetting->setTempPersonalPayment($invitationSetting->getPersonalPayment());
        $invitationSetting->setTempBeneficiary($invitationSetting->getBeneficiary());

        $invitationSetting->setStatus('1');


        $em->persist($invitationSetting);
        $em->flush();

        $this->updateSemiMainVenueTemp($orgId, $scheduleId);

        // send mail if all generated    
        $this->sendMail($processLog);
    }

    protected function sendMail(\Application\Entity\ProcessLog $processLog, $mailType = 4)
    {
        $em = $this->getEntityManager();
        $globalConfig = $this->getServiceLocator()->get('config');
        $emailSender = isset($globalConfig['emailSender']) ? $globalConfig['emailSender'] : 'dantai@mail.eiken.or.jp';
        // TODO: Need to check performance with this loop

        $adminInfo = unserialize($processLog->getAdminInfo());
        /* @var $organization \Application\Entity\Organization */
        $organization = $em->getRepository('\Application\Entity\Organization')->find($processLog->getOrgId());
        $orgName = $organization !== Null ? $organization->getOrgNameKanji() : '';
        foreach ($adminInfo as $adminEmail => $adminName) {
            \Dantai\Aws\AwsSesClient::getInstance()->deliver($emailSender, array(
                $adminEmail
            ), $mailType, array(
                'name' => $adminName,
                'orgName' => $orgName,
                'url' => $this->getServiceLocator()->get('Config')['ConsoleInvitation']['login_link']
            ));
        }

        return;
    }

    private function removeDeadLockCombini()
    {
        $deadLockTime = new \DateTime('now');
        $deadLockTime->modify(self::REMOVE_DEADLOCK_TIME);
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->delete('\Application\Entity\RunningCombini', 'RunningCombini')
            ->where('RunningCombini.insertAt < :deadLockTime')
            ->setParameter('deadLockTime', $deadLockTime->format('Y-m-d H:i:s'));
        return $dq->getQuery()->execute();
    }

    private function removeLockOnCompleteSendCombini($orgId)
    {
        $deadLockTime = new \DateTime('now');
        $deadLockTime->modify(self::REMOVE_DEADLOCK_TIME);
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->delete('\Application\Entity\RunningCombini', 'RunningCombini')
            ->where('RunningCombini.orgId = :orgId')
            ->setParameter('orgId', $orgId);
        return $dq->getQuery()->execute();
    }

    private function lockRunningCombini($orgId)
    {
        $runningCombini = new \Application\Entity\RunningCombini();
        $runningCombini->setOrgId($orgId);
        $this->getEntityManager()->persist($runningCombini);
        $this->getEntityManager()->flush();
    }

    public function sendPaymentToEcontextAction()
    {
        $awsSqsClient = \Dantai\Aws\AwsSqsClient::getInstance();
        $message = $awsSqsClient->receiveMessage(\Dantai\Aws\AwsSqsClient::QUEUE_GEN_COMBIBI);
        if (empty($message)) {
            $return = new \Zend\View\Model\ConsoleModel();
            $return->setErrorLevel(1);
            return $return;
        }
        $dataMessage = \Zend\Json\Json::decode($message['Body'], \Zend\Json\Json::TYPE_ARRAY);
        $classId = $dataMessage['classId'];
        $orgId = $dataMessage['orgId'];
        $scheduleId = array_key_exists('scheduleId', $dataMessage) ? $dataMessage['scheduleId'] : NULL;

        $priceLevels = !empty($dataMessage['priceLevels']) ? $dataMessage['priceLevels'] : array();


        $pupilId = !empty($dataMessage['pupilId']) ? $dataMessage['pupilId'] : NULL;
        $listKyu = !empty($dataMessage['listKyu']) ? $dataMessage['listKyu'] : NULL;

        echo date('[Y-m-d H:i:s e]'), ' Start send combini payment for class ', $classId, ' of Org ', $orgId, ' with chedule ', $scheduleId, PHP_EOL;

        $em = $this->getEntityManager();

        /* @var $processLog \Application\Entity\ProcessLog */
        $processLog = null;
        if (!empty($pupilId)) {
            $processLog = $em->getRepository('\Application\Entity\ProcessLog')->findOneBy(array(
                'pupilId' => $pupilId,
                'scheduleId' => $scheduleId
            ));
        } else {
            $processLog = $em->getRepository('\Application\Entity\ProcessLog')->findOneBy(array(
                'orgId' => $orgId,
                'scheduleId' => $scheduleId
            ));
        }
        $awsSqsClient->deleteMessage(\Dantai\Aws\AwsSqsClient::QUEUE_GEN_COMBIBI, $message['ReceiptHandle']);
        if (empty($processLog)) {
            return;
        }

        if ($processLog->getIsError()) {
            return;
        }
        $combini = new \ConsoleInvitation\Service\Combini($this->getServiceLocator());

        //$this->removeDeadLockCombini();
//        if($combini->isExpiredExecuteCombini($orgId, self::REMOVE_DEADLOCK_TIME)){
//            $combini->sendMailExpiredTimeExecuteCombini($processLog);
//            echo 'Expired Time Execute Send Combini of Org ' . $orgId,PHP_EOL;
//            return ;
//        }
        try {
            $this->lockRunningCombini($orgId);
        } catch (\Exception $ex) {
            echo 'Generate combini has locked of Org ' . $orgId, PHP_EOL;
            return;
        }


        try {
            $combini->sendOrgCombiniToEcontext($orgId, $scheduleId, $priceLevels, $pupilId, $listKyu);
        } catch (\Exception $ex) {
            $this->removeLockOnCompleteSendCombini($orgId);
            throw $ex;
        }

        $this->removeLockOnCompleteSendCombini($orgId);
        echo date('[Y-m-d H:i:s e]'), ' End send combini payment for class ', $classId, ' of Org ', $orgId, ' with chedule ', $scheduleId, PHP_EOL;
    }

    public function receivePaymentFromEcontextAction()
    {
        $combini = new \ConsoleInvitation\Service\Combini($this->getServiceLocator());
        $combini->receivePaymentInfoFromEcontext();
    }


    public function insertScoreDataAction()
    {
        echo 'Start', PHP_EOL;
        $countPupil = 1;
        $offset = 0;
        while ($countPupil > 0) {
            $listPupil = $this->getEntityManager()->getRepository('\Application\Entity\Pupil')
                ->findBy(array('isDelete' => 0), null, 10000, $offset);
            $offset += 10000;
            $countPupil = count($listPupil);
            echo 'Number of pupil : ', $countPupil, PHP_EOL;
            $countBatch = 0;
            foreach ($listPupil as $pupil) {
                /* @var $pupil \Application\Entity\Pupil */
                $eikenScore = new \Application\Entity\EikenScore();
                $eikenScore->setStatus('Active');
                $eikenScore->setPupil($pupil);
                $this->getEntityManager()->persist($eikenScore);

                $ibaScore = new \Application\Entity\IBAScore();
                $ibaScore->setStatus('Active');
                $ibaScore->setPupil($pupil);
                $this->getEntityManager()->persist($ibaScore);

                $simpleTest = new \Application\Entity\SimpleMeasurementResult();
                $simpleTest->setStatus('Active');
                $simpleTest->setPupil($pupil);
                $this->getEntityManager()->persist($simpleTest);

                if ($countBatch == 100) {
                    $countBatch = 0;
                    $this->getEntityManager()->flush();
                    $this->getEntityManager()->clear('\Application\Entity\EikenScore');
                    $this->getEntityManager()->clear('\Application\Entity\IBAScore');
                    $this->getEntityManager()->clear('\Application\Entity\SimpleMeasurementResult');
                }
                $countBatch++;
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear('\Application\Entity\EikenScore');
            $this->getEntityManager()->clear('\Application\Entity\IBAScore');
            $this->getEntityManager()->clear('\Application\Entity\SimpleMeasurementResult');
            $this->getEntityManager()->clear('\Application\Entity\Pupil');
        }
        echo 'DONE';
    }

    public function updateSemiMainVenueTemp($orgId, $eikenScheduleId)
    {
        $em = $this->getEntityManager();

        /* @var $semiVenue \Application\Entity\SemiVenue */
        $semiVenue = $em->getRepository('Application\Entity\SemiVenue')
            ->findOneBy(array(
                'organizationId' => $orgId,
                'eikenScheduleId' => $eikenScheduleId,
                'isDelete' => 0,
            ));
        if (empty($semiVenue)) {
            return null;
        } else {
            $semiVenue->setSemiMainVenueTemp($semiVenue->getSemiMainVenue());
            $em->persist($semiVenue);
            $em->flush();
        }
    }


    public function saveDownloadedFileFromS3Action()
    {
        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime("+365 days"));
        $bucket = 'dantai' . getenv('APP_ENV');
        $listTypeStaticDownloadedFile = $this->getGlobalConfig()['listTypeStaticDownloadedFile'];
        $year = $this->params()->fromRoute('year');
        $kai = $this->params()->fromRoute('kai');
        $type = $this->params()->fromRoute('type');

        try {
            /* @var $eikenSchedule \Application\Entity\EikenSchedule */
            $eikenSchedule = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
                'year' => intval($year),
                'kai' => intval($kai),
            ));

            if (!$eikenSchedule) {
                echo 'Do Not Exist EikenSchedule For Year = ' . $year . ' AND Kai = ' . $kai . ' ' . PHP_EOL;
            }

            $homeDir = isset($listTypeStaticDownloadedFile[$type]['homeDir']) ? $listTypeStaticDownloadedFile[$type]['homeDir'] : false;
            if (!$homeDir) {
                echo 'Do Not Exist This Download Type ' . PHP_EOL;
            }
            $prefixObject = $homeDir . $year . '/' . $kai . '/';
            $response = AwsS3Client::getInstance()->listObject($bucket, $prefixObject);
            if ($response['status'] == 0) {
                echo $response['error'] . PHP_EOL;
            }

            foreach ($response['data'] as $key => $value) {
                $dataFileName = $this->extractDownloadedFileName($value['filename']);
                if ($dataFileName) {
                    $dataInserts[] = array(
                        'OrganizationNo' => $dataFileName['organizationNo'],
                        'EikenScheduleId' => $eikenSchedule->getId(),
                        'Year' => $dataFileName['year'],
                        'Kai' => $dataFileName['kai'],
                        'Type' => $type,
                        'Filename' => $value['filename'],
                        'StartDate' => $startDate,
                        'EndDate' => $endDate,
                        'InsertAt' => $startDate,
                        'UpdateAt' => $startDate,
                        'IsDelete' => 0,
                        'Status' => 'Enable',
                    );
                }
            }
            if (isset($dataInserts)) {

                $response = $this->insertMultiLinkS3File($dataInserts, $year, $kai, $type);
                echo $response['message'] . PHP_EOL;
            } else {
                echo 'Empty Record';
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage() . PHP_EOL;
        }
    }

    public function extractDownloadedFileName($filename)
    {
        $patterns = '/4s5s_credentials_(\d+)_(\d+)_(\d+).pdf/';
        preg_match($patterns, $filename, $matches);
        if (count($matches) != 4) {
            return false;
        }
        $data['organizationNo'] = $matches[1];
        $data['year'] = $matches[2];
        $data['kai'] = $matches[3];
        return $data;
    }

    public function insertMultiLinkS3File($dataInserts, $year, $kai, $type)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $batch = 300;
            $fileDownloadRepos = $this->getEntityManager()->getRepository('Application\Entity\FileDownload');
            $fileDownloadRepos->deleteDataByYearAndKai($year, $kai, $type);
            if (count($dataInserts) <= $batch) {
                $fileDownloadRepos->insertMultipleRows($dataInserts);
            } else {
                for ($i = 0; $i < count($dataInserts); $i = $i + $batch) {
                    $dataInsertsSlice = array_slice($dataInserts, $i, $batch);
                    if ($dataInsertsSlice) {
                        $fileDownloadRepos->insertMultipleRows($dataInsertsSlice);
                    }
                }
            }
            $em->getConnection()->commit();
            $response = array(
                'status' => 1,
                'message' => 'Insert ' . count($dataInserts) . ' Record At Table FileDownload Success'
            );

        } catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
        }

        return $response;
    }

}
