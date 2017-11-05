<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\EikenResultMasterDataRepository")
 * @ORM\Table(name="EikenResultMasterData")
 */
class EikenResultMasterData extends Common
{

    /**
     * @ORM\Column(type="integer", name="Year", nullable=true)
     *
     * @var integer
     */
    protected $year;

    /**
     * @ORM\Column(type="integer", name="Kai", nullable=true)
     *
     * @var integer
     */
    protected $kai;
        
    /**
     * @ORM\Column(type="integer", name="IsInland", nullable=true)
     *
     * @var integer
     */
    protected $isInland;

    /**
     * @ORM\Column(type="integer", name="DateOfWeek", nullable=true)
     *
     * @var integer
     */
    protected $dateOfWeek;
    
    /**
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;
    
    /**
     * @ORM\Column(type="integer", name="Reading", nullable=true)
     *
     * @var integer
     */
    protected $reading;
    
    /**
     * @ORM\Column(type="integer", name="Listening", nullable=true)
     *
     * @var integer
     */
    protected $listening;
    
    /**
     * @ORM\Column(type="integer", name="Writing", nullable=true)
     *
     * @var integer
     */
    protected $writing;
    
    /**
     * @ORM\Column(type="integer", name="Speaking", nullable=true)
     *
     * @var integer
     */
    protected $speaking;
    
    /**
     * @ORM\Column(type="integer", name="MaxScoreRound1", nullable=true)
     *
     * @var integer
     */
    protected $maxScoreRound1;
    
    /**
     * @ORM\Column(type="integer", name="MaxScoreRound2", nullable=true)
     *
     * @var integer
     */
    protected $maxScoreRound2;
    
    /**
     * @ORM\Column(type="integer", name="CSEBand1", nullable=true)
     *
     * @var integer
     */
    protected $cseBand1;
    
    /**
     * @ORM\Column(type="integer", name="CSEBand2", nullable=true)
     *
     * @var integer
     */
    protected $cseBand2;
    
    function getYear() {
        return $this->year;
    }

    function getKai() {
        return $this->kai;
    }

    function getIsInland() {
        return $this->isInland;
    }

    function getDateOfWeek() {
        return $this->dateOfWeek;
    }

    function getEikenLevelId() {
        return $this->eikenLevelId;
    }

    function getReading() {
        return $this->reading;
    }

    function getListening() {
        return $this->listening;
    }

    function getWriting() {
        return $this->writing;
    }

    function getSpeaking() {
        return $this->speaking;
    }

    function getMaxScoreRound1() {
        return $this->maxScoreRound1;
    }

    function getMaxScoreRound2() {
        return $this->maxScoreRound2;
    }

    function getCseBand1() {
        return $this->cseBand1;
    }

    function getCseBand2() {
        return $this->cseBand2;
    }

    function setYear($year) {
        $this->year = $year;
    }

    function setKai($kai) {
        $this->kai = $kai;
    }

    function setIsInland($isInland) {
        $this->isInland = $isInland;
    }

    function setDateOfWeek($dateOfWeek) {
        $this->dateOfWeek = $dateOfWeek;
    }

    function setEikenLevelId($eikenLevelId) {
        $this->eikenLevelId = $eikenLevelId;
    }

    function setReading($reading) {
        $this->reading = $reading;
    }

    function setListening($listening) {
        $this->listening = $listening;
    }

    function setWriting($writing) {
        $this->writing = $writing;
    }

    function setSpeaking($speaking) {
        $this->speaking = $speaking;
    }

    function setMaxScoreRound1($maxScoreRound1) {
        $this->maxScoreRound1 = $maxScoreRound1;
    }

    function setMaxScoreRound2($maxScoreRound2) {
        $this->maxScoreRound2 = $maxScoreRound2;
    }

    function setCseBand1($cseBand1) {
        $this->cseBand1 = $cseBand1;
    }

    function setCseBand2($cseBand2) {
        $this->cseBand2 = $cseBand2;
    }


}