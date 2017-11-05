<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\EikenLevelRepository")
 * @ORM\Table(name="EikenLevel")
 */
class EikenLevel extends Common
{
    /**
     * @ORM\Column(type="string", name="LevelName", length=50, nullable=false, unique=true)
     *
     * @var string
     */
    protected $levelName;

    /**
     * @ORM\Column(type="decimal", name="StandardHallTuitionFee",nullable=true)
     *
     * @var decimal
     */
    protected $standardHallTuitionFee;

    /**
     * @ORM\Column(type="decimal", name="MainHallTuitionFee",nullable=true)
     *
     * @var decimal
     */
    protected $mainHallTuitionFee;
    /**
     * @ORM\Column(type="decimal", name="SpeciceFeeMainHallOne",nullable=true)
     * @var decimal
     */
    protected $speciceFeeMainHallOne;
    /**
     * @ORM\Column(type="decimal", name="SpeciceFeeMainHallTwo",nullable=true)
     * @var decimal
     */
    protected $speciceFeeMainHallTwo;
    /**
     * @ORM\Column(type="decimal", name="SpeciceFeeMainHallThree",nullable=true)
     * @var decimal
     */
    protected $speciceFeeMainHallThree;
    /**
     * @ORM\Column(type="decimal", name="SpeciceFeeStandardHallOne",nullable=true)
     * @var decimal
     */
    protected $speciceFeeStandardHallOne;
    /**
     * @ORM\Column(type="decimal", name="SpeciceFeeStandardHallTwo",nullable=true)
     * @var decimal
     */
    protected $speciceFeeStandardHallTwo;
    /**
     * @ORM\Column(type="decimal", name="SpeciceFeeStandardHallThree",nullable=true)
     * @var decimal
     */
    protected $speciceFeeStandardHallThree;

    /**
     * @return decimal
     */
    public function getSpeciceFeeMainHallOne()
    {
        return $this->speciceFeeMainHallOne;
    }

    /**
     * @param decimal $speciceFeeMainHallOne
     */
    public function setSpeciceFeeMainHallOne($speciceFeeMainHallOne)
    {
        $this->speciceFeeMainHallOne = $speciceFeeMainHallOne;
    }

    /**
     * @return decimal
     */
    public function getSpeciceFeeMainHallTwo()
    {
        return $this->speciceFeeMainHallTwo;
    }

    /**
     * @param decimal $speciceFeeMainHallTwo
     */
    public function setSpeciceFeeMainHallTwo($speciceFeeMainHallTwo)
    {
        $this->speciceFeeMainHallTwo = $speciceFeeMainHallTwo;
    }

    /**
     * @return decimal
     */
    public function getSpeciceFeeMainHallThree()
    {
        return $this->speciceFeeMainHallThree;
    }

    /**
     * @param decimal $speciceFeeMainHallThree
     */
    public function setSpeciceFeeMainHallThree($speciceFeeMainHallThree)
    {
        $this->speciceFeeMainHallThree = $speciceFeeMainHallThree;
    }

    /**
     * @return decimal
     */
    public function getSpeciceFeeStandardHallOne()
    {
        return $this->speciceFeeStandardHallOne;
    }

    /**
     * @param decimal $speciceFeeStandardHallOne
     */
    public function setSpeciceFeeStandardHallOne($speciceFeeStandardHallOne)
    {
        $this->speciceFeeStandardHallOne = $speciceFeeStandardHallOne;
    }

    /**
     * @return decimal
     */
    public function getSpeciceFeeStandardHallTwo()
    {
        return $this->speciceFeeStandardHallTwo;
    }

    /**
     * @param decimal $speciceFeeStandardHallTwo
     */
    public function setSpeciceFeeStandardHallTwo($speciceFeeStandardHallTwo)
    {
        $this->speciceFeeStandardHallTwo = $speciceFeeStandardHallTwo;
    }

    /**
     * @return decimal
     */
    public function getSpeciceFeeStandardHallThree()
    {
        return $this->speciceFeeStandardHallThree;
    }

    /**
     * @param decimal $speciceFeeStandardHallThree
     */
    public function setSpeciceFeeStandardHallThree($speciceFeeStandardHallThree)
    {
        $this->speciceFeeStandardHallThree = $speciceFeeStandardHallThree;
    }

    /**
     * @return string
     */
    public function getLevelName()
    {
        return $this->levelName;
    }

    /**
     * @param string $levelName
     */
    public function setLevelName($levelName)
    {
        $this->levelName = $levelName;
    }

    /**
     * @return decimal
     */
    public function getStandardHallTuitionFee()
    {
        return $this->standardHallTuitionFee;
    }

    /**
     * @param decimal $standardHallTuitionFee
     */
    public function setStandardHallTuitionFee($standardHallTuitionFee)
    {
        $this->standardHallTuitionFee = $standardHallTuitionFee;
    }

    /**
     * @return decimal
     */
    public function getMainHallTuitionFee()
    {
        return $this->mainHallTuitionFee;
    }

    /**
     * @param decimal $mainHallTuitionFee
     */
    public function setMainHallTuitionFee($mainHallTuitionFee)
    {
        $this->mainHallTuitionFee = $mainHallTuitionFee;
    }
}