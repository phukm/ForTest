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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\TemplateInvitationMsgRepository")
 * @ORM\Table(name="TemplateInvitationMsg")
 */
class TemplateInvitationMsg extends Common
{

    /* Foreing key */
    
    /* Property */
    /**
     * @ORM\Column(type="string", name="Name", length=250, nullable=true)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="Messages", length=500, nullable=true)
     *
     * @var string
     */
    protected $messages;

    /**
     * @ORM\Column(type="boolean", name="Type",nullable=true)
     *
     * @var boolean
     */
    protected $type;

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

    /**
     * @return string
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return boolean
     */
    public function isType()
    {
        return $this->type;
    }

    /**
     * @param boolean $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    /* Relationship */
    
    /* Getter and Setter */

}