<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
//Application\Entity\EinaviExam
/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\EinaviExamRepository")
 * @ORM\Table(name="EinaviExam")
 */
class EinaviExam extends Common
{

    /* Properties */

    /**
     * @ORM\Column(type="string", name="PersonalId", nullable=false, options={"default":""})
     *
     * @var string
     */
    protected $personalId = '';
    
    /**
     * @return string
     */
    public function getPersonalId(){
        return  $this->personalId;
    }
    
    /**
     * Setter for PersonalId
     * @param string $personalId
     */
    public function setPersonalId($personalId){
        $this->personalId = $personalId;
    }
    
    /**
     * @ORM\Column(type="date", name="ExamDate", nullable=false)
     *
     * @var \DateTime
     */
    protected $examDate;
    
    /**
     * @return \DateTime
     */
    public function getExamDate(){
        return  $this->examDate;
    }
    
    /**
     * Setter for ExamDate
     * @param string $examDate
     */
    public function setExamDate($examDate){
        $this->examDate = $examDate;
    }
    
    /**
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=false)
     *
     * @var integer
     */
    protected $eikenLevelId;
    
    /**
     * @return integer
     */
    public function getEikenLevelId(){
        return  $this->eikenLevelId;
    }
    
    /**
     * Setter for EikenLevelId
     * @param string $eikenLevelId
     */
    public function setEikenLevelId($eikenLevelId){
        $this->eikenLevelId = $eikenLevelId;
    }
    
    /**
     * @ORM\Column(type="string", name="MeasureKind", nullable=false)
     *
     * @var string
     */
    protected $measureKind = '';
    
    /**
     * @return string
     */
    public function getMeasureKind(){
        return  $this->measureKind;
    }
    
    /**
     * Setter for MeasureKind
     * @param string $measureKind
     */
    public function setMeasureKind($measureKind){
        $this->measureKind = $measureKind;
    }
    
    /**
     * @ORM\Column(type="string", name="MeasureTime", length=20, nullable=true)
     */
    protected $measureTime;
    
    /**
     * @return string
     */
    public function getMeasureTime(){
        return  $this->measureTime;
    }
    
    /**
     * Setter for MeasureTime
     * @param string $measureTime
     */
    public function setMeasureTime($measureTime){
        $this->measureTime = $measureTime;
    }
    
    /**
     * @ORM\Column(type="float", name="ScoreMax", nullable=false)
     *
     * @var float
     */
    protected $scoreMax;
    
    /**
     * @return float
     */
    public function getScoreMax(){
        return  $this->scoreMax;
    }
    
    /**
     * Setter for ScoreMax
     * @param string $scoreMax
     */
    public function setScoreMax($scoreMax){
        $this->scoreMax = $scoreMax;
    }
    
    /**
     * @ORM\Column(type="float", name="ScoreCurrent", nullable=false)
     *
     * @var float
     */
    protected $scoreCurrent;
    
    /**
     * @return float
     */
    public function getScoreCurrent(){
        return  $this->scoreCurrent;
    }
    
    /**
     * Setter for ScoreCurrent
     * @param string $scoreCurrent
     */
    public function setScoreCurrent($scoreCurrent){
        $this->scoreCurrent = $scoreCurrent;
    }
    
    /**
     * @ORM\Column(type="boolean", name="PassFail", nullable=false)
     *
     * @var boolean
     */
    protected $passFail = false;
    
    /**
     * @return boolean
     */
    public function getPassFail(){
        return  $this->passFail;
    }
    
    /**
     * Setter for PassFail
     * @param string $passFail
     */
    public function setPassFail($passFail){
        $this->passFail = $passFail;
    }
    
    /**
     * @ORM\Column(type="datetime", name="LastUsedDate", nullable=false)
     *
     * @var \DateTime
     */
    protected $lastUsedDate;
    
    /**
     * @return \DateTime
     */
    public function getLastUsedDate(){
        return  $this->lastUsedDate;
    }
    
    /**
     * Setter for LastUsedDate
     * @param string $lastUsedDate
     */
    public function setLastUsedDate($lastUsedDate){
        $this->lastUsedDate = $lastUsedDate;
    }
    
    /**
     * @ORM\Column(type="integer", name="PupilId", nullable=true)
     *
     * @var integer
     */
    protected $pupilId = '';
    
    /**
     * @return integer
     */
    public function getPupilId(){
        return  $this->pupilId;
    }
    
    /**
     * Setter for PupilId
     * @param string $pupilId
     */
    public function setPupilId($pupilId){
        $this->pupilId = $pupilId;
    }
    
    /* Relationship */
    
    /**
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="EikenLevelId", referencedColumnName="id")
     *
     * @var EikenLevel
     */
    protected $eikenLevel;
    
    /**
     * @return EikenLevel
     */
    public function getEikenLevel(){
        return  $this->eikenLevel;
    }
    
    /**
     * Setter for EikenLevel
     * @param string $eikenLevel
     */
    public function setEikenLevel($eikenLevel){
        $this->eikenLevel = $eikenLevel;
    }
    
    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     *
     * @var Pupil
     */
    protected $pupil;
    
    /**
     * @return Pupil
     */
    public function getPupil(){
        return  $this->pupil;
    }
    
    /**
     * Setter for Pupil
     * @param string $pupil
     */
    public function setPupil($pupil){
        $this->pupil = $pupil;
    }
}