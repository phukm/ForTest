<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\EikenIBAKojinStepGakusyuAdvRepository")
 * @ORM\Table(name="EikenIBAKojinStepGakusyuAdv")
 */
class EikenIBAKojinStepGakusyuAdv extends Common
{
    /**
     * @ORM\Column(type="string", name="Testsyubetsu", length=10, nullable=false, nullable=true)
     * @var string
     */
    protected $testsyubetsu;
    /**
     * @ORM\Column(type="integer", name="SinkyuKbn", nullable=true)
     * @var integer
     */
    protected $sinkyuKbn;
    /**
     * @ORM\Column(type="integer", name="Gino", nullable=true)
     * @var integer
     */
    protected $gino;
    /**
     * @ORM\Column(type="integer", name="ScoreRangeFrom", nullable=true)
     * @var integer
     */
    protected $scoreRangeFrom;
    /**
     * @ORM\Column(type="integer", name="ScoreRangeTo", nullable=true)
     * @var integer
     */
    protected $scoreRangeTo;
    /**
     * @ORM\Column(type="integer", name="KojinStep", nullable=true)
     * @var integer
     */
    protected $kojinStep;
    /**
     * @ORM\Column(type="string", name="KojinEikenkyulv", nullable=false, nullable=true)
     * @var string
     */
    protected $kojinEikenkyulv;
    /**
     * @ORM\Column(type="integer", name="HanteiKijunKyu", nullable=true)
     * @var integer
     */
    protected $hanteiKijunKyu;
    /**
     * @ORM\Column(type="integer", name="Hantei", nullable=true)
     * @var integer
     */
    protected $hantei;
    /**
     * @ORM\Column(type="string", name="ReadKeisaiKyu", nullable=true)
     * @var string
     */
    protected $readKeisaiKyu;
    /**
     * @ORM\Column(type="string", name="ReadCapaStatment", nullable=true)
     * @var string
     */
    protected $readCapaStatment;
    /**
     * @ORM\Column(type="string", name="ListenKeisaiKyu", nullable=true)
     * @var string
     */
    protected $listenKeisaiKyu;
    /**
     * @ORM\Column(type="string", name="ListenCapaStatment", nullable=true)
     * @var string
     */
    protected $listenCapaStatment;
    /**
     * @ORM\Column(type="string", name="WriteCapaStatment", nullable=true)
     * @var string
     */
    protected $writeCapaStatment;
    /**
     * @ORM\Column(type="string", name="WriteKeisaiKyu", nullable=true)
     * @var string
     */
    protected $writeKeisaiKyu;
    /**
     * @ORM\Column(type="integer", name="Bunya", nullable=true)
     * @var integer
     */
    protected $bunya;
    /**
     * @ORM\Column(type="integer", name="GakusyuAdvStep", nullable=true)
     * @var integer
     */
    protected $gakusyuAdvStep;
    /**
     * @ORM\Column(type="string", name="AdvBun", nullable=true)
     * @var string
     */
    protected $advBun;
    /**
     * @ORM\Column(type="string", name="KojinStr1", nullable=true)
     * @var string
     */
    protected $kojinStr1;
    /**
     * @ORM\Column(type="string", name="KojinStr2", nullable=true)
     * @var string
     */
    protected $kojinStr2;
    /**
     * @ORM\Column(type="string", name="KojinStr3", nullable=true)
     * @var string
     */
    protected $kojinStr3;
    /**
     * @ORM\Column(type="string", name="GrpListStr", nullable=true)
     * @var string
     */
    protected $grpListStr;
    /**
     * @ORM\Column(type="string", name="GrpStr1", nullable=true)
     * @var string
     */
    protected $grpStr1;

    /**
     * @return string
     */
    public function getTestsyubetsu()
    {
        return $this->testsyubetsu;
    }

    /**
     * @param string $testsyubetsu
     */
    public function setTestsyubetsu($testsyubetsu)
    {
        $this->testsyubetsu = $testsyubetsu;
    }

    /**
     * @return int
     */
    public function getSinkyuKbn()
    {
        return $this->sinkyuKbn;
    }

    /**
     * @param int $sinkyuKbn
     */
    public function setSinkyuKbn($sinkyuKbn)
    {
        $this->sinkyuKbn = $sinkyuKbn;
    }

    /**
     * @return int
     */
    public function getGino()
    {
        return $this->gino;
    }

    /**
     * @param int $gino
     */
    public function setGino($gino)
    {
        $this->gino = $gino;
    }

    /**
     * @return int
     */
    public function getScoreRangeFrom()
    {
        return $this->scoreRangeFrom;
    }

    /**
     * @param int $scoreRangeFrom
     */
    public function setScoreRangeFrom($scoreRangeFrom)
    {
        $this->scoreRangeFrom = $scoreRangeFrom;
    }

    /**
     * @return int
     */
    public function getScoreRangeTo()
    {
        return $this->scoreRangeTo;
    }

    /**
     * @param int $scoreRangeTo
     */
    public function setScoreRangeTo($scoreRangeTo)
    {
        $this->scoreRangeTo = $scoreRangeTo;
    }

    /**
     * @return int
     */
    public function getKojinStep()
    {
        return $this->kojinStep;
    }

    /**
     * @param int $kojinStep
     */
    public function setKojinStep($kojinStep)
    {
        $this->kojinStep = $kojinStep;
    }

    /**
     * @return string
     */
    public function getKojinEikenkyulv()
    {
        return $this->kojinEikenkyulv;
    }

    /**
     * @param string $kojinEikenkyulv
     */
    public function setKojinEikenkyulv($kojinEikenkyulv)
    {
        $this->kojinEikenkyulv = $kojinEikenkyulv;
    }

    /**
     * @return int
     */
    public function getHanteiKijunKyu()
    {
        return $this->hanteiKijunKyu;
    }

    /**
     * @param int $hanteiKijunKyu
     */
    public function setHanteiKijunKyu($hanteiKijunKyu)
    {
        $this->hanteiKijunKyu = $hanteiKijunKyu;
    }

    /**
     * @return int
     */
    public function getHantei()
    {
        return $this->hantei;
    }

    /**
     * @param int $hantei
     */
    public function setHantei($hantei)
    {
        $this->hantei = $hantei;
    }

    /**
     * @return string
     */
    public function getReadKeisaiKyu()
    {
        return $this->readKeisaiKyu;
    }

    /**
     * @param string $readKeisaiKyu
     */
    public function setReadKeisaiKyu($readKeisaiKyu)
    {
        $this->readKeisaiKyu = $readKeisaiKyu;
    }

    /**
     * @return string
     */
    public function getReadCapaStatment()
    {
        return $this->readCapaStatment;
    }

    /**
     * @param string $readCapaStatment
     */
    public function setReadCapaStatment($readCapaStatment)
    {
        $this->readCapaStatment = $readCapaStatment;
    }

    /**
     * @return string
     */
    public function getListenKeisaiKyu()
    {
        return $this->listenKeisaiKyu;
    }

    /**
     * @param string $listenKeisaiKyu
     */
    public function setListenKeisaiKyu($listenKeisaiKyu)
    {
        $this->listenKeisaiKyu = $listenKeisaiKyu;
    }

    /**
     * @return string
     */
    public function getListenCapaStatment()
    {
        return $this->listenCapaStatment;
    }

    /**
     * @param string $listenCapaStatment
     */
    public function setListenCapaStatment($listenCapaStatment)
    {
        $this->listenCapaStatment = $listenCapaStatment;
    }

    /**
     * @return string
     */
    public function getWriteCapaStatment()
    {
        return $this->writeCapaStatment;
    }

    /**
     * @param string $writeCapaStatment
     */
    public function setWriteCapaStatment($writeCapaStatment)
    {
        $this->writeCapaStatment = $writeCapaStatment;
    }

    /**
     * @return string
     */
    public function getWriteKeisaiKyu()
    {
        return $this->writeKeisaiKyu;
    }

    /**
     * @param string $writeKeisaiKyu
     */
    public function setWriteKeisaiKyu($writeKeisaiKyu)
    {
        $this->writeKeisaiKyu = $writeKeisaiKyu;
    }

    /**
     * @return int
     */
    public function getBunya()
    {
        return $this->bunya;
    }

    /**
     * @param int $bunya
     */
    public function setBunya($bunya)
    {
        $this->bunya = $bunya;
    }

    /**
     * @return int
     */
    public function getGakusyuAdvStep()
    {
        return $this->gakusyuAdvStep;
    }

    /**
     * @param int $gakusyuAdvStep
     */
    public function setGakusyuAdvStep($gakusyuAdvStep)
    {
        $this->gakusyuAdvStep = $gakusyuAdvStep;
    }

    /**
     * @return string
     */
    public function getAdvBun()
    {
        return $this->advBun;
    }

    /**
     * @param string $advBun
     */
    public function setAdvBun($advBun)
    {
        $this->advBun = $advBun;
    }

    /**
     * @return string
     */
    public function getKojinStr1()
    {
        return $this->kojinStr1;
    }

    /**
     * @param string $kojinStr1
     */
    public function setKojinStr1($kojinStr1)
    {
        $this->kojinStr1 = $kojinStr1;
    }

    /**
     * @return string
     */
    public function getKojinStr2()
    {
        return $this->kojinStr2;
    }

    /**
     * @param string $kojinStr2
     */
    public function setKojinStr2($kojinStr2)
    {
        $this->kojinStr2 = $kojinStr2;
    }

    /**
     * @return string
     */
    public function getKojinStr3()
    {
        return $this->kojinStr3;
    }

    /**
     * @param string $kojinStr3
     */
    public function setKojinStr3($kojinStr3)
    {
        $this->kojinStr3 = $kojinStr3;
    }

    /**
     * @return string
     */
    public function getGrpListStr()
    {
        return $this->grpListStr;
    }

    /**
     * @param string $grpListStr
     */
    public function setGrpListStr($grpListStr)
    {
        $this->grpListStr = $grpListStr;
    }

    /**
     * @return string
     */
    public function getGrpStr1()
    {
        return $this->grpStr1;
    }

    /**
     * @param string $grpStr1
     */
    public function setGrpStr1($grpStr1)
    {
        $this->grpStr1 = $grpStr1;
    }



}