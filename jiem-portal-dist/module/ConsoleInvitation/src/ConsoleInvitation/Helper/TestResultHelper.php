<?php

namespace ConsoleInvitation\Helper;

class TestResultHelper {

    protected static $instance = null;
    protected $em;
    protected $serviceManager;

    protected function __construct(\Doctrine\ORM\EntityManager $em, \Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        $this->em = $em;
        $this->serviceManager = $serviceManager;
    }

    /**
     *
     * @return TestResultHelper
     */
    public static function getInstance(\Doctrine\ORM\EntityManager $em, \Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        if (NULL === self::$instance) {
            self::$instance = new self($em, $serviceManager);
        }
        return self::$instance;
    }

    /**
     * get result eiken or iba lastest
     * @param $pupilId
     * @return mixed
     */
    public function getLastestTestResult($pupilId) {
        $eikenRepository = $this->em->getRepository('Application\Entity\EikenTestResult');
        $ibaRepository = $this->em->getRepository('Application\Entity\IBATestResult');

        /* @var $eikenResult \Application\Entity\EikenTestResult */
        $eikenResult = $eikenRepository->getDataLastestByPupilId($pupilId);
        /* @var $ibaResult \Application\Entity\IBATestResult */
        $ibaResult = $ibaRepository->getDataLastestByPupilId($pupilId);

        if ($eikenResult === Null && $ibaResult === Null) {
            return;
        }

        if ($eikenResult === Null) {
            return $ibaResult;
        }

        /* @var $eikenSchedule \Application\Entity\EikenSchedule */
        $eikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
            'year' => $eikenResult->getYear(),
            'kai' => $eikenResult->getKai()
        ));

        if ($eikenSchedule === Null) {
            return $ibaResult;
        }
        if (in_array($eikenResult->getEikenLevelId(), array(1, 2, 3, 4, 5))) {
            /* @var $pupil \Application\Entity\Pupil */
            $pupil = $this->em->getRepository('Application\Entity\Pupil')->find($pupilId);
            $dantaiService = $this->serviceManager->get('Application\Service\DantaiServiceInterface');
            $dateRule = $dantaiService->getDateRound2EachKyu($eikenSchedule->getId(), ($pupil ? $pupil->getOrganization()->getOrganizationNo() : Null));
            // 1級 -> 3級
            $eikenDate = 0;
            if(isset($dateRule[$eikenResult->getEikenLevelId()])){
                if($dateRule[$eikenResult->getEikenLevelId()] == 1){
                    $eikenDate = $eikenSchedule->getRound2Day1ExamDate() != Null ? $eikenSchedule->getRound2Day1ExamDate()->format('Ymd') : 0;
                }else{
                    $eikenDate = $eikenSchedule->getRound2Day2ExamDate() != Null ? $eikenSchedule->getRound2Day2ExamDate()->format('Ymd') : 0;
                }
            }
        } else {
            //4級 -> 5級
            if ($eikenSchedule->getSunDate() == Null && $eikenSchedule->getFriDate() == Null && $eikenSchedule->getSatDate() == Null) {
                $eikenDate = 0;
            } else {
                $sunDate = $eikenSchedule->getSunDate() != Null ? $eikenSchedule->getSunDate()->format('Ymd') : 0;
                $friDate = $eikenSchedule->getFriDate() != Null ? $eikenSchedule->getFriDate()->format('Ymd') : 0;
                $satDate = $eikenSchedule->getSatDate() != Null ? $eikenSchedule->getSatDate()->format('Ymd') : 0;

                $arrWday = array();
                if($sunDate > 0) $arrWday[] = $sunDate;
                if($friDate > 0) $arrWday[] = $friDate;
                if($satDate > 0) $arrWday[] = $satDate;

                $eikenDate = max($arrWday);
            }
        }
        $ibaDate = ($ibaResult != Null && $ibaResult->getExamDate() != Null) ? $ibaResult->getExamDate()->format('Ymd') : 0;

        return ($eikenDate >= $ibaDate) ? $eikenResult : $ibaResult;
    }

}
