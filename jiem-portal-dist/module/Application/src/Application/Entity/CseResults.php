<?php

/**
 * @author minhbn1<minhbn1@fsoft.com.vn>
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cseresults
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\CseResultsRepository")
 * @ORM\Table(name="CseResults")
 */
class CseResults extends Common {

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=32, nullable=false)
     */
    protected $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="OrganizationId", type="integer", nullable=true)
     */
    protected $organizationId;

    /**
     * @var integer
     *
     * @ORM\Column(name="ObjectId", type="integer", nullable=false)
     */
    protected $objectId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="ObjectType", type="string", length=32, nullable=true)
     */
    protected $objectType;

    /**
     * @var integer
     *
     * @ORM\Column(name="Year", type="integer", nullable=true)
     */
    protected $year;

    /**
     * @var integer
     *
     * @ORM\Column(name="Kai", type="integer", nullable=true)
     */
    protected $kai;
    
    /**
     * @var string
     *
     * @ORM\Column(name="ExamType", type="string", nullable=true)
     */
    protected $examType;

    /**
     * @var string
     *
     * @ORM\Column(name="JisshiId", type="string", nullable=true)
     */
    protected $jisshiId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="TestDate", type="datetime", nullable=true)
     */
    protected $testDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="AttendRate", type="integer", nullable=false)
     */
    protected $attendRate = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="AverageScore", type="integer", nullable=false)
     */
    protected $averageScore = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="LowestScore", type="integer", nullable=false)
     */
    protected $lowestScore = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="HighestScore", type="integer", nullable=false)
     */
    protected $highestScore = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="AverageReadingScore", type="integer", nullable=false)
     */
    protected $averageReadingScore = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="AverageListeningScore", type="integer", nullable=false)
     */
    protected $averageListeningScore = '0';
    /**
     * @var integer
     *
     * @ORM\Column(name="AverageSpeakingScore", type="integer", nullable=false)
     */
    protected $averageSpeakingScore = '0';
    /**
     * @var integer
     *
     * @ORM\Column(name="AverageWritingScore", type="integer", nullable=false)
     */
    protected $averageWritingScore = '0';
    

    function getType() {
        return $this->type;
    }

    function getOrganizationId() {
        return $this->organizationId;
    }

    function getObjectId() {
        return $this->objectId;
    }

    function getObjectType() {
        return $this->objectType;
    }

    function getYear() {
        return $this->year;
    }

    function getKai() {
        return $this->kai;
    }
    
    function getExamType() {
        return $this->examType;
    }

    function getJisshiId() {
        return $this->jisshiId;
    }

    function getTestDate() {
        return $this->testDate;
    }

    function getAttendRate() {
        return $this->attendRate;
    }

    function getAverageScore() {
        return $this->averageScore;
    }

    function getLowestScore() {
        return $this->lowestScore;
    }

    function getHighestScore() {
        return $this->highestScore;
    }

    function getAverageReadingScore() {
        return $this->averageReadingScore;
    }

    function getAverageListeningScore() {
        return $this->averageListeningScore;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setOrganizationId($organizationId) {
        $this->organizationId = $organizationId;
    }

    function setObjectId($objectId) {
        $this->objectId = $objectId;
    }

    function setObjectType($objectType) {
        $this->objectType = $objectType;
    }

    function setYear($year) {
        $this->year = $year;
    }

    function setKai($kai) {
        $this->kai = $kai;
    }
    
    function setExamType($examType) {
        $this->examType = $examType;
    }

    function setJisshiId($jisshiId) {
        $this->jisshiId = $jisshiId;
    }

    function setTestDate($testDate) {
        $this->testDate = $testDate;
    }

    function setAttendRate($attendRate) {
        $this->attendRate = $attendRate;
    }

    function setAverageScore($averageScore) {
        $this->averageScore = $averageScore;
    }

    function setLowestScore($lowestScore) {
        $this->lowestScore = $lowestScore;
    }

    function setHighestScore($highestScore) {
        $this->highestScore = $highestScore;
    }

    function setAverageReadingScore($averageReadingScore) {
        $this->averageReadingScore = $averageReadingScore;
    }

    function setAverageListeningScore($averageListeningScore) {
        $this->averageListeningScore = $averageListeningScore;
    }

    public function getAverageSpeakingScore() {
        return $this->averageSpeakingScore;
    }

    public function getAverageWritingScore() {
        return $this->averageWritingScore;
    }

    public function setAverageSpeakingScore($averageSpeakingScore) {
        $this->averageSpeakingScore = $averageSpeakingScore;
    }

    public function setAverageWritingScore($averageWritingScore) {
        $this->averageWritingScore = $averageWritingScore;
    }


}
