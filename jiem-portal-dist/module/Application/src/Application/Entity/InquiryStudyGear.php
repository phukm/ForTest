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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\InquiryStudyGearRepository")
 * @ORM\Table(name="InquiryStudyGear")
 */
class InquiryStudyGear extends Common
{
    /**
     * Foreing key reference to EikenLevel
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;
    /**
     * Foreing key reference to OrgSchoolYear
     * @ORM\Column(type="integer", name="OrgSchoolYearId", nullable=true)
     *
     * @var integer
     */
    protected $orgSchoolYearId;
    /**
     * Foreing key reference to Organization
     * @ORM\Column(type="integer", name="OrganizationId", nullable=true)
     *
     * @var integer
     */
    protected $organizationId;
    /**
     * Foreing key reference to ClassJ
     * @ORM\Column(type="integer", name="ClassId", nullable=true)
     *
     * @var integer
     */
    protected $classId;
    /**
     * @ORM\Column(type="date", name="InquiryDate", nullable=true)
     */
    protected $inquiryDate;

     /**
     * @ORM\Column(type="integer", name="VocabularyTime", nullable=true)
     *
     * @var integer
     */
    protected $vocabularyTime;
     /**
     * @ORM\Column(type="integer", name="GrammarTime", nullable=true)
     *
     * @var integer
     */
    protected $grammarTime;
     /**
     * @ORM\Column(type="integer", name="ReadingTime", nullable=true)
     *
     * @var integer
     */
    protected $readingTime;
     /**
     * @ORM\Column(type="integer", name="ListeningTime", nullable=true)
     *
     * @var integer
     */
    protected $listeningTime;
    /**
     * @ORM\Column(type="integer", name="EikenTime", nullable=true)
     *
     * @var integer
     */
    protected $eikenTime;
     /**
     * @ORM\Column(type="integer", name="TotalTime", nullable=true)
     *
     * @var integer
     */
    protected $totalTime;
     /**
     * @ORM\Column(type="integer", name="VocabularyPeople", nullable=true)
     *
     * @var integer
     */
    protected $vocabularyPeople;
     /**
     * @ORM\Column(type="integer", name="GrammarPeople", nullable=true)
     *
     * @var integer
     */
    protected $grammarPeople;
     /**
     * @ORM\Column(type="integer", name="ReadingPeople", nullable=true)
     *
     * @var integer
     */
    protected $readingPeople;
     /**
     * @ORM\Column(type="integer", name="ListeningPeople", nullable=true)
     *
     * @var integer
     */
    protected $listeningPeople;
    /**
     * @ORM\Column(type="integer", name="EikenPeople", nullable=true)
     *
     * @var integer
     */
    protected $eikenPeople;
     /**
     * @ORM\Column(type="integer", name="TotalPeople", nullable=true)
     *
     * @var integer
     */
    protected $totalPeople;

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
     * @return int
     */
    public function getOrgSchoolYearId()
    {
        return $this->orgSchoolYearId;
    }

    /**
     * @param int $orgSchoolYearId
     */
    public function setOrgSchoolYearId($orgSchoolYearId)
    {
        $this->orgSchoolYearId = $orgSchoolYearId;
    }

    /**
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param int $organizationId
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     * @return int
     */
    public function getClassId()
    {
        return $this->classId;
    }

    /**
     * @param int $classId
     */
    public function setClassId($classId)
    {
        $this->classId = $classId;
    }

    /**
     * @return mixed
     */
    public function getInquiryDate()
    {
        return $this->inquiryDate;
    }

    /**
     * @param mixed $inquiryDate
     */
    public function setInquiryDate($inquiryDate)
    {
        $this->inquiryDate = $inquiryDate;
    }

    /**
     * @return int
     */
    public function getVocabularyTime()
    {
        return $this->vocabularyTime;
    }

    /**
     * @param int $vocabularyTime
     */
    public function setVocabularyTime($vocabularyTime)
    {
        $this->vocabularyTime = $vocabularyTime;
    }

    /**
     * @return int
     */
    public function getGrammarTime()
    {
        return $this->grammarTime;
    }

    /**
     * @param int $grammarTime
     */
    public function setGrammarTime($grammarTime)
    {
        $this->grammarTime = $grammarTime;
    }

    /**
     * @return int
     */
    public function getReadingTime()
    {
        return $this->readingTime;
    }

    /**
     * @param int $readingTime
     */
    public function setReadingTime($readingTime)
    {
        $this->readingTime = $readingTime;
    }

    /**
     * @return int
     */
    public function getListeningTime()
    {
        return $this->listeningTime;
    }

    /**
     * @param int $listeningTime
     */
    public function setListeningTime($listeningTime)
    {
        $this->listeningTime = $listeningTime;
    }
    /**
     * @return int
     */
    public function getEikenTime()
    {
        return $this->eikenTime;
    }

    /**
     * @param int $eikenTime
     */
    public function setEikenTime($eikenTime)
    {
        $this->eikenTime = $eikenTime;
    }
    /**
     * @return int
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }

    /**
     * @param int $totalTime
     */
    public function setTotalTime($totalTime)
    {
        $this->totalTime = $totalTime;
    }

    /**
     * @return int
     */
    public function getVocabularyPeople()
    {
        return $this->vocabularyPeople;
    }

    /**
     * @param int $vocabularyPeople
     */
    public function setVocabularyPeople($vocabularyPeople)
    {
        $this->vocabularyPeople = $vocabularyPeople;
    }

    /**
     * @return int
     */
    public function getGrammarPeople()
    {
        return $this->grammarPeople;
    }

    /**
     * @param int $grammarPeople
     */
    public function setGrammarPeople($grammarPeople)
    {
        $this->grammarPeople = $grammarPeople;
    }

    /**
     * @return int
     */
    public function getReadingPeople()
    {
        return $this->readingPeople;
    }

    /**
     * @param int $readingPeople
     */
    public function setReadingPeople($readingPeople)
    {
        $this->readingPeople = $readingPeople;
    }

    /**
     * @return int
     */
    public function getListeningPeople()
    {
        return $this->listeningPeople;
    }

    /**
     * @param int $listeningPeople
     */
    public function setListeningPeople($listeningPeople)
    {
        $this->listeningPeople = $listeningPeople;
    }
    /**
     * @return int
     */
    public function getEikenPeople()
    {
        return $this->eikenPeople;
    }

    /**
     * @param int $eikenPeople
     */
    public function setEikenPeople($eikenPeople)
    {
        $this->eikenPeople = $eikenPeople;
    }
    /**
     * @return int
     */
    public function getTotalPeople()
    {
        return $this->totalPeople;
    }

    /**
     * @param int $totalPeople
     */
    public function setTotalPeople($totalPeople)
    {
        $this->totalPeople = $totalPeople;
    }


}