<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\IBAScoreRepository")
 * @ORM\Table(name="IBAScore")
 */
class IBAScore extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to Pupil
     * @ORM\Column(type="integer", name="PupilId", nullable=true)
     *
     * @var integer
     */
    protected $pupilId;

    /**
     * Foreing key reference to EikenLevel
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;

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

    /* Property */

    /**
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="EikenLevelId", referencedColumnName="id")
     */
    protected $iBALevel;

    /**
     * @ORM\Column(type="datetime", name="ExamDate", nullable=true)
     */
    protected $examDate;

    /**
     * @ORM\Column(name="ReadingScore", type="decimal", nullable=true)
     *
     * @var decimal
     */
    protected $readingScore;

    /**
     * @ORM\Column(name="ListeningScore", type="decimal", nullable=true)
     *
     * @var decimal
     */
    protected $listeningScore;

    /**
     *
     * @ORM\Column(type="decimal", name="IBACSETotal", nullable=true)
     *
     * @var decimal
     */
    protected $iBACSETotal;

    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;
    /**
     * @var integer
     * @ORM\Column(name="PassFailFlag", type="integer", nullable=true)
     */
    protected $passFailFlag;
    
    /**
     * @var integer
     * @ORM\Column(name="IbaTestResultId", type="integer", nullable=true)
     */
    protected $ibaTestResultId;
    
    /**
     * @var integer
     * @ORM\Column(name="StatusSave", type="integer", nullable=true)
     */
    protected $statusSave;
    
    /**
     * @return int
     */
    public function getStatusSave()
    {
        return $this->statusSave;
    }
    
    /**
     * @param int $statusSave
     */
    public function setStatusSave($statusSave)
    {
        $this->statusSave = $statusSave;
    }
    
    /**
     * @return int
     */
    public function getIbaTestResultId()
    {
        return $this->ibaTestResultId;
    }
    
    /**
     * @param int $pupilId
     */
    public function setIbaTestResultId($ibaTestResultId)
    {
        $this->ibaTestResultId = $ibaTestResultId;
    }

    /**
     *
     * @return int
     */
    public function getPupilId()
    {
        return $this->pupilId;
    }

    /**
     *
     * @param int $pupilId
     */
    public function setPupilId($pupilId)
    {
        $this->pupilId = $pupilId;
    }

    /**
     *
     * @return int
     */
    public function getEikenLevelId()
    {
        return $this->eikenLevelId;
    }

    /**
     *
     * @param int $eikenLevelId
     */
    public function setEikenLevelId($eikenLevelId)
    {
        $this->eikenLevelId = $eikenLevelId;
    }



    /**
     *
     * @return mixed
     */
    public function getIBALevel()
    {
        return $this->iBALevel;
    }

    /**
     *
     * @param mixed $iBALevel
     */
    public function setIBALevel($iBALevel)
    {
        $this->iBALevel = $iBALevel;
    }

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
     * @return mixed
     */
    public function getExamDate()
    {
        return $this->examDate;
    }

    /**
     *
     * @param mixed $examDate
     */
    public function setExamDate($examDate)
    {
        $this->examDate = $examDate;
    }

    /**
     *
     * @return decimal
     */
    public function getReadingScore()
    {
        return $this->readingScore;
    }

    /**
     *
     * @param decimal $readingScore
     */
    public function setReadingScore($readingScore)
    {
        $this->readingScore = $readingScore;
    }

    /**
     *
     * @return decimal
     */
    public function getListeningScore()
    {
        return $this->listeningScore;
    }

    /**
     *
     * @param decimal $listeningScore
     */
    public function setListeningScore($listeningScore)
    {
        $this->listeningScore = $listeningScore;
    }

    /**
     *
     * @return decimal
     */
    public function getIBACSETotal()
    {
        return $this->iBACSETotal;
    }

    /**
     *
     * @param decimal $iBACSETotal
     */
    public function setIBACSETotal($iBACSETotal)
    {
        $this->iBACSETotal = $iBACSETotal;
    }

    function getPupil()
    {
        return $this->pupil;
    }

    function setPupil($pupil)
    {
        $this->pupil = $pupil;
    }

    /**
     * @return int
     */
    public function getPassFailFlag()
    {
        return $this->passFailFlag;
    }

    /**
     * @param int $passFailFlag
     */
    public function setPassFailFlag($passFailFlag)
    {
        $this->passFailFlag = $passFailFlag;
    }

}
