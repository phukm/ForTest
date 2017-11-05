<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="RunningCombini")
 * @ORM\Entity
 */
class RunningCombini
{
    /**
     * @var integer
     *
     * @ORM\Column(name="OrgId", type="integer", nullable=false)
     * @ORM\Id
     */
    private $orgId;
    
    /**
     * @ORM\Column(type="datetime", name="InsertAt", nullable=false, options={"default":"CURRENT_TIMESTAMP"})
     */
    private $insertAt;
    
    public function getOrgId() {
        return $this->orgId;
    }

    public function setOrgId($orgId) {
        $this->orgId = $orgId;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->insertAt = new \DateTime("now");
    }
    
    public function getInsertAt() {
        return $this->insertAt;
    }

    public function setInsertAt($insertAt) {
        $this->insertAt = $insertAt;
    }

}
