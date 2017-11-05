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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\SchoolYearRepository")
 * @ORM\Table(name="SchoolYear")
 */
class SchoolYear extends Common
{

    /* Foreing key */
    /* Property */
    /**
     * @ORM\Column(type="integer", name="Ordinal")
     *
     * @var integer
     */
    protected $ordinal;
    /* Property */
    /**
     * @ORM\Column(type="string", name="Name", length=250, nullable=false, unique=true)
     *
     * @var string
     */
    protected $name;

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
    /* Relationship */
    
    /* Getter and Setter */

}