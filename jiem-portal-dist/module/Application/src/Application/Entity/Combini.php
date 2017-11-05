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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\CombiniRepository")
 * @ORM\Table(name="Combini")
 */
class Combini extends Common
{

    /* Foreing key */

    /* Property */
    /**
     * @ORM\Column(type="string", name="Name", length=100, nullable=false)
     *
     * @var string
     */
    protected $name;
    /**
     * @ORM\Column(type="string", name="Name1", length=100, nullable=true)
     *
     * @var string
     */
    protected $name1;
    /**
     * @ORM\Column(type="string", name="SubName", length=100, nullable=true)
     *
     * @var string
     */
    protected $subName;
    /**
     * @ORM\Column(type="string", name="SubName1", length=100, nullable=true)
     *
     * @var string
     */
    protected $subName1;

    /**
     * @ORM\Column(type="string", name="Step1", length=100, nullable=true)
     *
     * @var string
     */
    protected $step1;

    /**
     * @ORM\Column(type="string", name="Step2", length=100, nullable=true)
     *
     * @var string
     */
    protected $step2;

    /**
     * @ORM\Column(type="string", name="Step3", length=100, nullable=true)
     *
     * @var string
     */
    protected $step3;

    /**
     * @ORM\Column(type="string", name="Step4", length=100, nullable=true)
     *
     * @var string
     */
    protected $step4;

    /**
     * @ORM\Column(type="string", name="Step5", length=100, nullable=true)
     *
     * @var string
     */
    protected $step5;
    /**
     * @ORM\Column(type="smallint", name="Ordinal")
     *
     * @var integer
     */
    protected $ordinal;
    /**
     * @return int
     */
    public function getOrdinal()
    {
        return $this->ordinal;
    }

    /**
     * @param int $ordinal
     */
    public function setOrdinal($ordinal)
    {
        $this->ordinal = $ordinal;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName1()
    {
        return $this->name1;
    }

    /**
     * @param string $name1
     */
    public function setName1($name1)
    {
        $this->name1 = $name1;
    }

    /**
     * @return string
     */
    public function getSubName()
    {
        return $this->subName;
    }

    /**
     * @param string $subName
     */
    public function setSubName($subName)
    {
        $this->subName = $subName;
    }

    /**
     * @return string
     */
    public function getSubName1()
    {
        return $this->subName1;
    }

    /**
     * @param string $subName1
     */
    public function setSubName1($subName1)
    {
        $this->subName1 = $subName1;
    }

    /**
     * @return string
     */
    public function getStep1()
    {
        return $this->step1;
    }

    /**
     * @param string $step1
     */
    public function setStep1($step1)
    {
        $this->step1 = $step1;
    }

    /**
     * @return string
     */
    public function getStep2()
    {
        return $this->step2;
    }

    /**
     * @param string $step2
     */
    public function setStep2($step2)
    {
        $this->step2 = $step2;
    }

    /**
     * @return string
     */
    public function getStep3()
    {
        return $this->step3;
    }

    /**
     * @param string $step3
     */
    public function setStep3($step3)
    {
        $this->step3 = $step3;
    }

    /**
     * @return string
     */
    public function getStep4()
    {
        return $this->step4;
    }

    /**
     * @param string $step4
     */
    public function setStep4($step4)
    {
        $this->step4 = $step4;
    }

    /**
     * @return string
     */
    public function getStep5()
    {
        return $this->step5;
    }

    /**
     * @param string $step5
     */
    public function setStep5($step5)
    {
        $this->step5 = $step5;
    }
    /* Relationship */

    /* Getter and Setter */

}