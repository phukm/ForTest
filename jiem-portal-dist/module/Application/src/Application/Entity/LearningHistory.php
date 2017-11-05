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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\LearningHistoryRepository")
 * @ORM\Table(name="LearningHistory")
 */
class LearningHistory extends Common
{

    /* Property */
    /**
     * @ORM\Column(type="string", name="PersonalId", length=100, nullable=false)
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
     * @ORM\Column(type="date", name="LearningDate", length=100, nullable=false)
     *
     * @var \DateTime
     */
    protected $learningDate = '';
    
    /**
     * @return \DateTime
     */
    public function getLearningDate(){
        return  $this->learningDate;
    }
    
    /**
     * Setter for LearningDate
     * @param string $learningDate
     */
    public function setLearningDate($learningDate){
        $this->learningDate = $learningDate;
    }
    
    /**
     * @ORM\Column(type="integer", name="EikenLevelId", length=100, nullable=false)
     *
     * @var integer
     */
    protected $eikenLevelId = '';
    
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
     * @ORM\Column(type="string", name="LearningType", length=100, nullable=false)
     *
     * @var string
     */
    protected $learningType = '';
    
    /**
     * @return string
     */
    public function getLearningType(){
        return  $this->learningType;
    }
    
    /**
     * Setter for LearningType
     * @param string $learningType
     */
    public function setLearningType($learningType){
        $this->learningType = $learningType;
    }
    
    /**
     * @ORM\Column(type="integer", name="PlanLearningTime", nullable=true)
     */
    protected $planLearningTime;
    
    /**
     * @return \DateTime
     */
    public function getPlanLearningTime(){
        return  $this->planLearningTime;
    }
    
    /**
     * Setter for PlanLearningTime
     * @param string $planLearningTime
     */
    public function setPlanLearningTime($planLearningTime){
        $this->planLearningTime = $planLearningTime;
    }
    
    /**
     * @ORM\Column(type="integer", name="QuestionCount", nullable=false)
     *
     * @var integer
     */
    protected $questionCount = '';
    
    /**
     * @return integer
     */
    public function getQuestionCount(){
        return  $this->questionCount;
    }
    
    /**
     * Setter for QuestionCount
     * @param string $questionCount
     */
    public function setQuestionCount($questionCount){
        $this->questionCount = $questionCount;
    }
    
    /**
     * @ORM\Column(type="integer", name="CorrectAnswerCount", length=100, nullable=false)
     *
     * @var integer
     */
    protected $correctAnswerCount = '';
    
    /**
     * @return integer
     */
    public function getCorrectAnswerCount(){
        return  $this->correctAnswerCount;
    }
    
    /**
     * Setter for CorrectAnswerCount
     * @param string $correctAnswerCount
     */
    public function setCorrectAnswerCount($correctAnswerCount){
        $this->correctAnswerCount = $correctAnswerCount;
    }
    
    /**
     * @ORM\Column(type="datetime", name="LastUsedDate", nullable=false)
     *
     * @var \DateTime
     */
    protected $lastUsedDate = '';
    
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
     * @ORM\Column(type="integer", name="LearningTime", nullable=false)
     *
     */
    protected $learningTime = '';
    
    /**
     * @return \DateTime
     */
    public function getLearningTime(){
        return  $this->learningTime;
    }
    
    /**
     * Setter for LearningTime
     * @param string $learningTime
     */
    public function setLearningTime($learningTime){
        $this->learningTime = $learningTime;
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
    protected $Pupil;
    
    /**
     * @return Pupil
     */
    public function getPupil(){
        return  $this->Pupil;
    }
    
    /**
     * Setter for Pupil
     * @param string $pupil
     */
    public function setPupil($pupil){
        $this->Pupil = $pupil;
    }
}