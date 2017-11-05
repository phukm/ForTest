<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Application\Entity\Common;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Action")
 */
class Action extends Common
{

    /* Foreing key */
    
    /* Property */
    /**
     * @ORM\Column(type="string", name="Title", length=500, nullable=true)
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type="string", name="Link", length=500, nullable=true)
     *
     * @var string
     */
    protected $link;

    /**
     * @ORM\Column(type="string", name="Description", length=500, nullable=true)
     *
     * @var string
     */
    protected $description;

    /* Relationship */
    
    /* Getter and Setter */
    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @param string $title            
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     *
     * @param string $link            
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     *
     * @param string $description            
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}
