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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ActivityLogRepository")
 * @ORM\Table(name="ActivityLog")
 */
class ActivityLog  extends Common
{
    /**
     * @ORM\Column(type="string", name="OrganizationNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $organizationNo;
    /**
     * @ORM\Column(type="string", name="OrganizationName", length=250, nullable=true)
     *
     * @var string
     */
    protected $organizationName;
    
    /**
     * @ORM\Column(type="string", name="UserID", length=100, nullable=true)
     *
     * @var string
     */
    protected $userID;
    
    /**
     * @ORM\Column(type="string", name="UserName", length=100, nullable=true)
     *
     * @var string
     */
    protected $userName;
    /**
     * @ORM\Column(type="string", name="ScreenName", length=250, nullable=true)
     *
     * @var string
     */
    protected $screenName;

    /**
     * @ORM\Column(type="string", name="ActionName", length=250, nullable=true)
     *
     * @var string
     */
    protected $actionName;
    
    /**
     * @return string
     */
    public function getOrganizationNo(){
        return  $this->organizationNo;
    }
    
    /**
     * Setter for OrganizationNo
     * @param string $organizationNo
     */
    public function setOrganizationNo($organizationNo){
        $this->organizationNo = $organizationNo;
    }   
    
    /**
     * @return string
     */
    public function getOrganizationName(){
        return  $this->organizationName;
    }
    
    /**
     * Setter for OrganizationName
     * @param string $organizationName
     */
    public function setOrganizationName($organizationName){
        $this->organizationName = $organizationName;
    }
    
    /**
     * @return integer
     */
    public function getUserID(){
        return  $this->userID;
    }
    
    /**
     * Setter for UserID
     * @param integer $userID
     */
    public function setUserID($userID){
        $this->userID = $userID;
    }
    
    /**
     * @return string
     */
    public function getUserName(){
        return  $this->userName;
    }
    
    /**
     * Setter for UserName
     * @param string $userName
     */
    public function setUserName($userName){
        $this->userName = $userName;
    }
    
    /**
     * @return string
     */
    public function getScreenName(){
        return  $this->screenName;
    }
    
    /**
     * Setter for ScreenName
     * @param string $screenName
     */
    public function setScreenName($screenName){
        $this->screenName = $screenName;
    }
    
    /**
     * @return string
     */
    public function getActionName(){
        return  $this->actionName;
    }
    
    /**
     * Setter for ActionName
     * @param string $actionName
     */
    public function setActionName($actionName){
        $this->actionName = $actionName;
    }
}