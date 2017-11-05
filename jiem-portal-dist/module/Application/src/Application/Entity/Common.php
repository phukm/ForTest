<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dantai\PrivateSession;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * s
 */
class Common
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id", nullable=false)
     *
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", name="UpdateAt", options={"default":"CURRENT_TIMESTAMP"})
     */
    protected $updateAt;

    /**
     * @ORM\Column(type="string", name="UpdateBy", nullable=true)
     *
     * @var string
     */
    protected $updateBy;

    /**
     * @ORM\Column(type="datetime", name="InsertAt", options={"default":"CURRENT_TIMESTAMP"})
     */
    protected $insertAt;

    /**
     * @ORM\Column(type="string", name="InsertBy", nullable=true)
     */
    protected $insertBy;

    /**
     * @ORM\Column(type="string", name="Status", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="boolean", name="IsDelete",options={"default":0})
     * @var boolean
     */
    protected $isDelete = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    /**
     * @param mixed $updateAt
     */
    public function setUpdateAt($updateAt)
    {
        $this->updateAt = $updateAt;
    }

    /**
     * @return string
     */
    public function getUpdateBy()
    {
        return $this->updateBy;
    }

    /**
     * @param string $updateBy
     */
    public function setUpdateBy($updateBy)
    {
        $this->updateBy = $updateBy;
    }

    /**
     * @return mixed
     */
    public function getInsertAt()
    {
        return $this->insertAt;
    }

    /**
     * @param mixed $insertAt
     */
    public function setInsertAt($insertAt)
    {
        $this->insertAt = $insertAt;
    }

    /**
     * @return mixed
     */
    public function getInsertBy()
    {
        return $this->insertBy;
    }

    /**
     * @param mixed $insertBy
     */
    public function setInsertBy($insertBy)
    {
        $this->insertBy = $insertBy;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getIsDelete()
    {
        return $this->isDelete;
    }

    /**
     * @param mixed $isDelete
     */
    public function setIsDelete($isDelete)
    {
        $this->isDelete = $isDelete;
    }

    // Pre persist
    /**
     * @ORM\PrePersist
     */
    public function init()
    {
        $session = PrivateSession::getData('userIdentity');

        $insertBy = "DRAFT";
        if (null != $session) {
            $insertBy = $session['organizationNo'] . "." . $session['userId'];
        }
        if(!isset($this->insertAt)) {
            $this->insertAt = new \DateTime("now");
        }
        $this->updateAt = new \DateTime("now");
        $this->insertBy = $insertBy;
        $this->updateBy = $insertBy;
        $this->isDelete = 0;
        if (! isset($this->status)) {
            $this->status = "DRAFT";
        }
        if (! isset($this->isDelete)) {
            $this->isDelete = 0;
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function update()
    {
        $session = PrivateSession::getData('userIdentity');
        $updateBy = "DRAFT";
        if (null != $session) {
            $updateBy = $session['organizationNo'] . "." . $session['userId'];
        }
        $this->updateAt = new \DateTime("now");
        $this->updateBy = $updateBy;
    }
}