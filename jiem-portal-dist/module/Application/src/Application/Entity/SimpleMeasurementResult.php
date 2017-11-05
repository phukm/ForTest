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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\SimpleMeasurementResultRepository")
 * @ORM\Table(name="SimpleMeasurementResult")
 */
class SimpleMeasurementResult extends Common
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
     * @ORM\Column(type="integer", name="ResultVocabularyId", nullable=true)
     *
     * @var integer
     */
    protected $resultVocabularyId;

    /**
     * @ORM\Column(type="integer", name="ResultGrammarId", nullable=true)
     *
     * @var integer
     */
    protected $resultGrammarId;

    /* Property */
    
    /**
     * @ORM\Column(type="string", name="ResultVocabularyName", nullable=true)
     *
     * @var string
     */
    protected $resultVocabularyName;

    /**
     * @ORM\Column(type="string", name="ResultGrammarName", nullable=true)
     *
     * @var string
     */
    protected $resultGrammarName;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;

    /**
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="ResultGrammarId", referencedColumnName="id")
     */
    protected $resultGrammar;

    /**
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="ResultVocabularyId", referencedColumnName="id")
     */
    protected $resultVocabulary;
    /**
     * @ORM\Column(type="datetime", name="ResultDate", nullable=true)
     */
    protected $resultDate;

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
    public function getResultVocabularyId()
    {
        return $this->resultVocabularyId;
    }

    /**
     * @param int $resultVocabularyId
     */
    public function setResultVocabularyId($resultVocabularyId)
    {
        $this->resultVocabularyId = $resultVocabularyId;
    }

    /**
     * @return int
     */
    public function getResultGrammarId()
    {
        return $this->resultGrammarId;
    }

    /**
     * @param int $resultGrammarId
     */
    public function setResultGrammarId($resultGrammarId)
    {
        $this->resultGrammarId = $resultGrammarId;
    }

    /**
     * @return string
     */
    public function getResultVocabularyName()
    {
        return $this->resultVocabularyName;
    }

    /**
     * @param string $resultVocabularyName
     */
    public function setResultVocabularyName($resultVocabularyName)
    {
        $this->resultVocabularyName = $resultVocabularyName;
    }

    /**
     * @return string
     */
    public function getResultGrammarName()
    {
        return $this->resultGrammarName;
    }

    /**
     * @param string $resultGrammarName
     */
    public function setResultGrammarName($resultGrammarName)
    {
        $this->resultGrammarName = $resultGrammarName;
    }

    /**
     * @return mixed
     */
    public function getPupil()
    {
        return $this->pupil;
    }

    /**
     * @param mixed $pupil
     */
    public function setPupil($pupil)
    {
        $this->pupil = $pupil;
    }

    /**
     * @return mixed
     */
    public function getResultGrammar()
    {
        return $this->resultGrammar;
    }

    /**
     * @param mixed $resultGrammar
     */
    public function setResultGrammar($resultGrammar)
    {
        $this->resultGrammar = $resultGrammar;
    }

    /**
     * @return mixed
     */
    public function getResultVocabulary()
    {
        return $this->resultVocabulary;
    }

    /**
     * @param mixed $resultVocabulary
     */
    public function setResultVocabulary($resultVocabulary)
    {
        $this->resultVocabulary = $resultVocabulary;
    }

    /**
     * @return mixed
     */
    public function getResultDate()
    {
        return $this->resultDate;
    }

    /**
     * @param mixed $resultDate
     */
    public function setResultDate($resultDate)
    {
        $this->resultDate = $resultDate;
    }


}