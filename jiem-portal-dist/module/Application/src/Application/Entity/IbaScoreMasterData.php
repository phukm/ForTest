<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\IbaScoreMasterDataRepository")
 * @ORM\Table(name="IbaScoreMasterData")
 */
class IbaScoreMasterData extends Common
{

    
    /**
     * @ORM\Column(type="string", name="Type", length=50, nullable=true, options={"comment":"Type(TOTAL,READING,LISTENING)"})
     * 
     * @var string
     */
    protected $type;
    
    /**
     * @ORM\Column(type="string", name="TestSet", length=50, nullable=true, options={"comment":"TestSet(A, B, C…..)"})
     * 
     * @var string
     */
    protected $testSet;
    
    /**
     * @ORM\Column(type="integer", name="ScoreRangeFrom", length=50, nullable=true)
     * 
     * @var integer
     */
    protected $scoreRangeFrom;
    
    /**
     * @ORM\Column(type="integer", name="ScoreRangeTo", length=50, nullable=true)
     * 
     * @var integer
     */
    protected $scoreRangeTo;
    
    /**
     * @ORM\Column(type="string", name="IbaLevelDecision", length=200, nullable=true)
     * 
     * @var string
     */
    protected $ibaLevelDecision;
    
    /**
     * @ORM\Column(type="integer", name="StarNumber", length=50, nullable=true)
     * 
     * @var integer
     */
    protected $starNumber;
    
    /**
     * @ORM\Column(type="string", name="StarNumberDescription", length=500, nullable=true)
     * 
     * @var string
     */
    protected $starNumberDescription;
    
     /**
     * @ORM\Column(type="string", name="IbaLevelName", length=50, nullable=true)
     * 
     * @var string
     */
    protected $ibaLevelName;
    
    /**
     * @ORM\Column(type="string", name="CanDoName", length=50, nullable=false)
     * 
     * @var string
     */
    protected $canDoName;
    
    /**
     * @ORM\Column(type="string", name="AdviceName", length=50, nullable=false)
     * 
     * @var string
     */
    protected $adviceName;
    
    function getType() {
        return $this->type;
    }

    function getTestSet() {
        return $this->testSet;
    }

    function getScoreRangeFrom() {
        return $this->scoreRangeFrom;
    }

    function getScoreRangeTo() {
        return $this->scoreRangeTo;
    }

    function getIbaLevelDecision() {
        return $this->ibaLevelDecision;
    }

    function getStarNumber() {
        return $this->starNumber;
    }

    function getStarNumberDescription() {
        return $this->starNumberDescription;
    }

    function getIbaLevelName() {
        return $this->ibaLevelName;
    }

    function getCanDoName() {
        return $this->canDoName;
    }

    function getAdviceName() {
        return $this->adviceName;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setTestSet($testSet) {
        $this->testSet = $testSet;
    }

    function setScoreRangeFrom($scoreRangeFrom) {
        $this->scoreRangeFrom = $scoreRangeFrom;
    }

    function setScoreRangeTo($scoreRangeTo) {
        $this->scoreRangeTo = $scoreRangeTo;
    }

    function setIbaLevelDecision($ibaLevelDecision) {
        $this->ibaLevelDecision = $ibaLevelDecision;
    }

    function setStarNumber($starNumber) {
        $this->starNumber = $starNumber;
    }

    function setStarNumberDescription($starNumberDescription) {
        $this->starNumberDescription = $starNumberDescription;
    }

    function setIbaLevelName($ibaLevelName) {
        $this->ibaLevelName = $ibaLevelName;
    }

    function setCanDoName($canDoName) {
        $this->canDoName = $canDoName;
    }

    function setAdviceName($adviceName) {
        $this->adviceName = $adviceName;
    }


}