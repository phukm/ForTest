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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\DantaiLockRepository")
 * @ORM\Table(name="DantaiLock",uniqueConstraints={@ORM\UniqueConstraint(name="ModuleUnique", columns={"Module"})})
 */
class DantaiLock extends Common
{
    /* Properties */
    /**
     * @ORM\Column(type="integer", name="Locker", nullable=false, options={"default":0})
     *
     * @var integer
     */
    protected $locker = 0;
    
    /**
     * @return integer
     */
    public function getLocker(){
        return  $this->locker;
    }
    
    /**
     * Setter for Locker
     * @param string $locker
     */
    public function setLocker($locker){
        $this->locker = $locker;
    }
    
    /**
     * @ORM\Column(type="string", name="Module", length=254, nullable=false, options={"default":""})
     *
     * @var string
     */
    protected $module = '';
    
    /**
     * @return string
     */
    public function getModule(){
        return  $this->module;
    }
    
    /**
     * Setter for Module
     * @param string $module
     */
    public function setModule($module){
        $this->module = $module;
    }
}