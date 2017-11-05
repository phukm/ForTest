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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\EikenScoreRepository")
 * @ORM\Table(name="EikenScore")
 */
class EikenScore extends Common
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
    protected $eikenLevel;
    /**
     *@ORM\Column(name="ReadingScore", type="decimal", nullable=true)
     * @var decimal
     */
    protected $readingScore;

    /**
     *@ORM\Column(name="ListeningScore", type="decimal", nullable=true)
     * @var decimal
     */
    protected $listeningScore;

    /**
     *@ORM\Column(name="CSEScoreWriting", type="decimal", nullable=true)
     * @var decimal
     */
    protected $cSEScoreWriting;

    /**
     *@ORM\Column(name="CSEScoreSpeaking", type="decimal", nullable=true)
     * @var decimal
     */
    protected $cSEScoreSpeaking;

    /**
     *
     * @ORM\Column(type="decimal", name="EikenCSETotal", nullable=true)
     *
     * @var decimal
     */
    protected $eikenCSETotal;
    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;
    /**
     * @var string
     * @ORM\Column(name="SecondCertificationDate", type="datetime", nullable=true)
     */
    protected $secondCertificationDate;
    /**
     * @var \DateTime
     * @ORM\Column(name="CertificationDate", type="datetime", nullable=true)
     */
    protected $certificationDate;
    /**
     * @var integer
     * @ORM\Column(name="PrimaryPassFailFlag", type="integer", nullable=true)
     */
    protected $primaryPassFailFlag;
    /**
     * @var integer
     * @ORM\Column(name="OneExemptionFlag", type="string", length=32, nullable=true)
     */
    protected $oneExemptionFlag;
    /**
     * @var integer
     * @ORM\Column(name="SecondPassFailFlag", type="integer", nullable=true)
     */
    protected $secondPassFailFlag;
    /**
     * @var integer
     * @ORM\Column(name="PassFailFlag", type="integer", nullable=true)
     */
    protected $passFailFlag;
    
    /**
     * @var integer
     * @ORM\Column(name="EikenTestResultId", type="integer", nullable=true)
     */
    protected $eikenTestResultId;
    
    
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
    public function getEikenTestResultId()
    {
        return $this->eikenTestResultId;
    }
    
    /**
     * @param int $pupilId
     */
    public function setEikenTestResultId($eikenTestResultId)
    {
        $this->eikenTestResultId = $eikenTestResultId;
    }

    /**
     * @return int
     */
    public function getPupilId()
    {
        return $this->pupilId;
    }

    /**
     * @param int $pupilId
     */
    public function setPupilId($pupilId)
    {
        $this->pupilId = $pupilId;
    }

    /**
     * @return int
     */
    public function getEikenLevelId()
    {
        return $this->eikenLevelId;
    }

    /**
     * @param int $eikenLevelId
     */
    public function setEikenLevelId($eikenLevelId)
    {
        $this->eikenLevelId = $eikenLevelId;
    }

    /**
     * @return mixed
     */
    public function getEikenLevel()
    {
        return $this->eikenLevel;
    }

    /**
     * @param mixed $eikenLevel
     */
    public function setEikenLevel($eikenLevel)
    {
        $this->eikenLevel = $eikenLevel;
    }
    /**
     * @return decimal
     */
    public function getReadingScore()
    {
        return $this->readingScore;
    }

    /**
     * @param decimal $readingScore
     */
    public function setReadingScore($readingScore)
    {
        $this->readingScore = $readingScore;
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
     * @return decimal
     */
    public function getListeningScore()
    {
        return $this->listeningScore;
    }

    /**
     * @param decimal $listeningScore
     */
    public function setListeningScore($listeningScore)
    {
        $this->listeningScore = $listeningScore;
    }

    /**
     * @return decimal
     */
    public function getCSEScoreWriting()
    {
        return $this->cSEScoreWriting;
    }

    /**
     * @param decimal $cSEScoreWriting
     */
    public function setCSEScoreWriting($cSEScoreWriting)
    {
        $this->cSEScoreWriting = $cSEScoreWriting;
    }

    /**
     * @return decimal
     */
    public function getCSEScoreSpeaking()
    {
        return $this->cSEScoreSpeaking;
    }

    /**
     * @param decimal $cSEScoreSpeaking
     */
    public function setCSEScoreSpeaking($cSEScoreSpeaking)
    {
        $this->cSEScoreSpeaking = $cSEScoreSpeaking;
    }

    /**
     * @return decimal
     */
    public function getEikenCSETotal()
    {
        return $this->eikenCSETotal;
    }

    /**
     * @param decimal $eikenCSETotal
     */
    public function setEikenCSETotal($eikenCSETotal)
    {
        $this->eikenCSETotal = $eikenCSETotal;
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
     * @return string
     */
    public function getSecondCertificationDate()
    {
        return $this->secondCertificationDate;
    }

    /**
     * @param string $secondCertificationDate
     */
    public function setSecondCertificationDate($secondCertificationDate)
    {
        $this->secondCertificationDate = $secondCertificationDate;
    }

    /**
     * @return \DateTime
     */
    public function getCertificationDate()
    {
        return $this->certificationDate;
    }

    /**
     * @param \DateTime $certificationDate
     */
    public function setCertificationDate($certificationDate)
    {
        $this->certificationDate = $certificationDate;
    }

    /**
     * @return int
     */
    public function getPrimaryPassFailFlag()
    {
        return $this->primaryPassFailFlag;
    }

    /**
     * @param int $primaryPassFailFlag
     */
    public function setPrimaryPassFailFlag($primaryPassFailFlag)
    {
        $this->primaryPassFailFlag = $primaryPassFailFlag;
    }

    /**
     * @return int
     */
    public function getOneExemptionFlag()
    {
        return $this->oneExemptionFlag;
    }

    /**
     * @param int $oneExemptionFlag
     */
    public function setOneExemptionFlag($oneExemptionFlag)
    {
        $this->oneExemptionFlag = $oneExemptionFlag;
    }

    /**
     * @return int
     */
    public function getSecondPassFailFlag()
    {
        return $this->secondPassFailFlag;
    }

    /**
     * @param int $secondPassFailFlag
     */
    public function setSecondPassFailFlag($secondPassFailFlag)
    {
        $this->secondPassFailFlag = $secondPassFailFlag;
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
