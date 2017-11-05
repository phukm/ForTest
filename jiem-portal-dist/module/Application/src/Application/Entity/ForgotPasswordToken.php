<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ForgotPasswordTokenRepository")
 * @ORM\Table(name="ForgotPasswordToken")
 */
class ForgotPasswordToken extends Common{
    
    /**
     * Foreign key reference to Pupil
     * @ORM\Column(type="integer", name="UserId")
     *
     * @var integer
     */
    protected $userId;
    
    /**
     * @ORM\Column(type="string", name="token", length=64)
     *
     * @var string
     */
    protected $token;

    public function getUserId() {
        return $this->userId;
    }

    public function getToken() {
        return $this->token;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function setToken($token) {
        $this->token = $token;
    }
}