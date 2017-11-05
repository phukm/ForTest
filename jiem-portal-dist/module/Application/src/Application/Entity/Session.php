<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Session
 *
 * @ORM\Table(name="Session")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\SessionRepository")
 */
class Session
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=32, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id = '';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $name = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="modified", type="integer", nullable=true)
     */
    private $modified;

    /**
     * @var integer
     *
     * @ORM\Column(name="lifetime", type="integer", nullable=true)
     */
    private $lifetime;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="blob", nullable=true)
     */
    private $data;

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getModified() {
        return $this->modified;
    }

    public function getLifetime() {
        return $this->lifetime;
    }

    public function getData() {
        return stream_get_contents($this->data);
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setModified($modified) {
        $this->modified = $modified;
    }

    public function setLifetime($lifetime) {
        $this->lifetime = $lifetime;
    }

    public function setData($data) {
        $this->data = $data;
    }

}