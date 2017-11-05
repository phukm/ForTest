<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Processlog
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\PaymentOrderIndexRepository")
 * @ORM\Table(name="PaymentOrderIndex")
 */
class PaymentOrderIndex
{
    /**
     * @var integer
     *
     * @ORM\Column(name="Id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="Prefix", type="string", length=50, unique=true, nullable=false)
     */
    private $prefix;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="OrderIndex", type="integer", nullable=false)
     */
    private $index;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="LastTelNoIndex", type="integer", nullable=false, options={"default":-1})
     */
    private $lastTelNoIndex = -1;
    
    public function getId() {
        return $this->id;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function getIndex() {
        return $this->index;
    }

    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }

    public function setIndex($index) {
        $this->index = $index;
    }
    
    public function addIndex() {
        $this->index++;
    }
    
    public function getLastTelNoIndex() {
        return $this->lastTelNoIndex;
    }

    public function setLastTelNoIndex($lastTelNoIndex) {
        $this->lastTelNoIndex = $lastTelNoIndex;
    }
    
}
