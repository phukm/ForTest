<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\I18n\Validator\DateTime;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\EikenScheduleRepository")
 * @ORM\Table(name="EikenSchedule")
 *
 */
class EikenSchedule extends Common
{

    /* Foreing key */
    /* Property */
    /**
     * @ORM\Column(type="string", name="ExamName", nullable=true)
     *
     * @var string
     */
    protected $examName;
    /**
     * @ORM\Column(type="integer", name="Year",length=4, nullable=true)
     *
     * @var integer
     */
    protected $year;

    /**
     * @ORM\Column(type="string", name="Kai", length=100, nullable=true)
     *
     * @var string
     */
    protected $kai;

    /**
     * @ORM\Column(type="datetime", name="DeadlineForm", nullable=true)
     */
    protected $deadlineFrom;

    /**
     * @ORM\Column(type="datetime", name="DeadlineTo", nullable=true)
     */
    protected $deadlineTo;

    /**
     * @ORM\Column(type="datetime", name="SunDate", nullable=true)
     */
    protected $sunDate;

    /**
     * @ORM\Column(type="datetime", name="FriDate", nullable=true)
     */
    protected $friDate;

    /**
     * @ORM\Column(type="datetime", name="SatDate", nullable=true)
     */
    protected $satDate;
    /**
     * @ORM\Column(type="datetime", name="Round2ExamDate", nullable=true)
     */
    protected $round2ExamDate;
    /**
     * @ORM\Column(type="string", name="SunDateName", length=100, nullable=true)
     *
     * @var string
     */
    protected $sunDateName;
    /**
     * @ORM\Column(type="string", name="SatDateName", length=100, nullable=true)
     *
     * @var string
     */
    protected $satDateName;
    /**
     * @ORM\Column(type="string", name="FriDateName", length=100, nullable=true)
     *
     * @var string
     */
    protected $friDateName;
    /**
     * @ORM\Column(type="string", name="ExamFullName", length=100, nullable=true)
     *
     * @var string
     */
    protected $examFullName;
    /**
     * @ORM\Column(type="string", name="Round2ExamDateName", length=100, nullable=true)
     *
     * @var string
     */
    protected $round2ExamDateName;
    /**
     * @ORM\Column(type="datetime", name="Day1stTestResult", nullable=true)
     *
     * @var \DateTime
     */
    protected $day1stTestResult = '';
    /**
     * @ORM\Column(type="datetime", name="Day2ndTestResult", nullable=true)
     *
     * @var \DateTime
     */
    protected $day2ndTestResult = '';
    /**
     * @ORM\Column(type="datetime", name="CombiniDeadline", nullable=true)
     *
     * @var DateTime
     */
    protected $combiniDeadline;
    /**
     * @ORM\Column(type="datetime", name="CreditCardDeadline", nullable=true)
     *
     * @var DateTime
     */
    protected $creditCardDeadline;
    /**
     * @ORM\Column(type="datetime", name="SatelliteSiteDeadline", nullable=true)
     *
     * @var DateTime
     */
    protected $satelliteSiteDeadline;
    
    /**
     * @ORM\Column(type="datetime", name="Round2Day1ExamDate", nullable=true)
     */
    protected $round2Day1ExamDate;
    
    /**
     * @ORM\Column(type="datetime", name="Round2Day2ExamDate", nullable=true)
     */
    protected $round2Day2ExamDate;

    /**
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     *
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     *
     * @return string
     */
    public function getKai()
    {
        return $this->kai;
    }

    /**
     *
     * @param string $kai
     */
    public function setKai($kai)
    {
        $this->kai = $kai;
    }

    /**
     *
     * @return \DateTime
     */
    public function getDeadlineFrom()
    {
        return $this->deadlineFrom;
    }

    /**
     *
     * @param mixed $deadlineFrom
     */
    public function setDeadlineFrom($deadlineFrom)
    {
        $this->deadlineFrom = $deadlineFrom;
    }

    /**
     *
     * @return \DateTime
     */
    public function getDeadlineTo()
    {
        return $this->deadlineTo;
    }

    /**
     *
     * @param mixed $deadlineTo
     */
    public function setDeadlineTo($deadlineTo)
    {
        $this->deadlineTo = $deadlineTo;
    }

    /**
     *
     * @return \DateTime
     */
    public function getSunDate()
    {
        return $this->sunDate;
    }

    /**
     *
     * @param mixed $sunDate
     */
    public function setSunDate($sunDate)
    {
        $this->sunDate = $sunDate;
    }

    /**
     *
     * @return \DateTime
     */
    public function getFriDate()
    {
        return $this->friDate;
    }

    /**
     *
     * @param mixed $friDate
     */
    public function setFriDate($friDate)
    {
        $this->friDate = $friDate;
    }

    /**
     *
     * @return \DateTime
     */
    public function getSatDate()
    {
        return $this->satDate;
    }

    /**
     *
     * @param mixed $satDate
     */
    public function setSatDate($satDate)
    {
        $this->satDate = $satDate;
    }

    /**
     *
     * @return string
     */
    public function getExamName()
    {
        return $this->examName;
    }

    /**
     *
     * @param string $examName
     */
    public function setExamName($examName)
    {
        $this->examName = $examName;
    }

    /**
     * @return mixed
     */
    public function getRound2ExamDate()
    {
        return $this->round2ExamDate;
    }

    /**
     * @param mixed $round2ExamDate
     */
    public function setRound2ExamDate($round2ExamDate)
    {
        $this->round2ExamDate = $round2ExamDate;
    }

    /**
     * @return string
     */
    public function getSunDateName()
    {
        return $this->sunDateName;
    }

    /**
     * @param string $sunDateName
     */
    public function setSunDateName($sunDateName)
    {
        $this->sunDateName = $sunDateName;
    }

    /**
     * @return string
     */
    public function getSatDateName()
    {
        return $this->satDateName;
    }

    /**
     * @param string $satDateName
     */
    public function setSatDateName($satDateName)
    {
        $this->satDateName = $satDateName;
    }

    /**
     * @return string
     */
    public function getFriDateName()
    {
        return $this->friDateName;
    }

    /**
     * @param string $friDateName
     */
    public function setFriDateName($friDateName)
    {
        $this->friDateName = $friDateName;
    }

    /**
     * @return string
     */
    public function getExamFullName()
    {
        return $this->examFullName;
    }

    /**
     * @param string $examFullName
     */
    public function setExamFullName($examFullName)
    {
        $this->examFullName = $examFullName;
    }

    /**
     * @return string
     */
    public function getRound2ExamDateName()
    {
        return $this->round2ExamDateName;
    }

    /**
     * @param string $round2ExamDateName
     */
    public function setRound2ExamDateName($round2ExamDateName)
    {
        $this->round2ExamDateName = $round2ExamDateName;
    }
    
    /**
     * @return \DateTime
     */
    public function getDay1stTestResult(){
        return  $this->day1stTestResult;
    }
    
    /**
     * Setter for Day1stTestResult
     * @param string $day1stTestResult
     */
    public function setDay1stTestResult($day1stTestResult){
        $this->day1stTestResult = $day1stTestResult;
    }
    
    /**
     * @return \DateTime
     */
    public function getDay2ndTestResult(){
        return  $this->day2ndTestResult;
    }
    
    /**
     * Setter for Day2ndTestResult
     * @param string $day2ndTestResult
     */
    public function setDay2ndTestResult($day2ndTestResult){
        $this->day2ndTestResult = $day2ndTestResult;
    }

    /**
     * @return \DateTime
     */
    public function getCombiniDeadline()
    {
        return $this->combiniDeadline;
    }

    /**
     * @param mixed $combiniDeadline
     */
    public function setCombiniDeadline($combiniDeadline)
    {
        $this->combiniDeadline = $combiniDeadline;
    }

    /**
     * @return \DateTime
     */
    public function getCreditCardDeadline()
    {
        return $this->creditCardDeadline;
    }

    /**
     * @param mixed $creditCardDeadline
     */
    public function setCreditCardDeadline($creditCardDeadline)
    {
        $this->creditCardDeadline = $creditCardDeadline;
    }

    /**
     * @return \DateTime
     */
    public function getSatelliteSiteDeadline()
    {
        return $this->satelliteSiteDeadline;
    }

    /**
     * @param mixed $satelliteSiteDeadline
     */
    public function setSatelliteSiteDeadline($satelliteSiteDeadline)
    {
        $this->satelliteSiteDeadline = $satelliteSiteDeadline;
    }
    
    function getRound2Day1ExamDate() {
        return $this->round2Day1ExamDate;
    }

    function getRound2Day2ExamDate() {
        return $this->round2Day2ExamDate;
    }

    function setRound2Day1ExamDate($round2Day1ExamDate) {
        $this->round2Day1ExamDate = $round2Day1ExamDate;
    }

    function setRound2Day2ExamDate($round2Day2ExamDate) {
        $this->round2Day2ExamDate = $round2Day2ExamDate;
    }


}