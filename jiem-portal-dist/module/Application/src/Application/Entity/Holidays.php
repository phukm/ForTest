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
 * @ORM\Entity
 * @ORM\Table(name="Holidays")
 */
class Holidays extends Common
{

    /**
     * @ORM\Column(type="datetime", name="DayOff", nullable=false)
     *
     * @var \DateTime
     */
    protected $dayOff = '';
    
    /**
     * @return \DateTime
     */
    public function getDayOff(){
        return  $this->dayOff;
    }
    
    /**
     * Setter for DayOff
     * @param string $dayOff
     */
    public function setDayOff($dayOff){
        $this->dayOff = $dayOff;
    }
    
    /**
     * @ORM\Column(type="string", name="Name", length=100, nullable=true)
     *
     * @var string
     */
    protected $name = '';
    
    /**
     * @return string
     */
    public function getName(){
        return  $this->name;
    }
    
    /**
     * Setter for Name
     * @param string $Name
     */
    public function setName($Name){
        $this->name = $Name;
    }
    
    /**
     * @ORM\Column(type="string", name="Description", length=500, nullable=true)
     *
     * @var string
     */
    protected $description = '';
    
    /**
     * @return string
     */
    public function getDescription(){
        return  $this->description;
    }
    
    /**
     * Setter for Description
     * @param string $description
     */
    public function setDescription($description){
        $this->description = $description;
    }
}