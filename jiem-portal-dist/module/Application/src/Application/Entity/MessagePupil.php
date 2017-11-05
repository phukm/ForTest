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
 * @ORM\Table(name="MessagePupil")
 */
class MessagePupil extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to TemplateInvitationMsg
     * @ORM\Column(type="integer", name="TemplateInvitationMsgId1", nullable=true)
     *
     * @var integer
     */
    protected $templateInvitationMsgId1;

    /**
     * Foreing key reference to TemplateInvitationMsg
     * @ORM\Column(type="integer", name="TemplateInvitationMsgId2", nullable=true)
     *
     * @var integer
     */
    protected $templateInvitationMsgId2;

    /**
     * Foreing key reference to InvitationLetter
     * @ORM\Column(type="integer", name="InvitationLetterId", nullable=true)
     *
     * @var integer
     */
    protected $invitationLetterId;

    /* Property */
    /**
     * @ORM\Column(type="string", name="TeacherTitle", length=250, nullable=true)
     *
     * @var string
     */
    protected $teacherTitle;

    /**
     * @ORM\Column(type="string", name="ParentTitle", length=250, nullable=true)
     *
     * @var string
     */
    protected $parentTitle;

    /**
     * @ORM\Column(type="string", name="TeacherMessage", length=500, nullable=true)
     *
     * @var string
     */
    protected $teacherMessage;

    /**
     * @ORM\Column(type="string", name="ParentMessage", length=500, nullable=true)
     *
     * @var string
     */
    protected $parentMessage;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="TemplateInvitationMsg")
     * @ORM\JoinColumn(name="TemplateInvitationMsgId1", referencedColumnName="id")
     */
    protected $template1;

    /**
     * @ORM\ManyToOne(targetEntity="TemplateInvitationMsg")
     * @ORM\JoinColumn(name="TemplateInvitationMsgId2", referencedColumnName="id")
     */
    protected $template2;

    /**
     * @ORM\ManyToOne(targetEntity="InvitationLetter")
     * @ORM\JoinColumn(name="InvitationLetterId", referencedColumnName="id")
     */
    protected $invitationLetter;

    /**
     * @return int
     */
    public function getTemplateInvitationMsgId1()
    {
        return $this->templateInvitationMsgId1;
    }

    /**
     * @param int $templateInvitationMsgId1
     */
    public function setTemplateInvitationMsgId1($templateInvitationMsgId1)
    {
        $this->templateInvitationMsgId1 = $templateInvitationMsgId1;
    }

    /**
     * @return int
     */
    public function getTemplateInvitationMsgId2()
    {
        return $this->templateInvitationMsgId2;
    }

    /**
     * @param int $templateInvitationMsgId2
     */
    public function setTemplateInvitationMsgId2($templateInvitationMsgId2)
    {
        $this->templateInvitationMsgId2 = $templateInvitationMsgId2;
    }

    /**
     * @return int
     */
    public function getInvitationLetterId()
    {
        return $this->invitationLetterId;
    }

    /**
     * @param int $invitationLetterId
     */
    public function setInvitationLetterId($invitationLetterId)
    {
        $this->invitationLetterId = $invitationLetterId;
    }

    /**
     * @return string
     */
    public function getTeacherTitle()
    {
        return $this->teacherTitle;
    }

    /**
     * @param string $teacherTitle
     */
    public function setTeacherTitle($teacherTitle)
    {
        $this->teacherTitle = $teacherTitle;
    }

    /**
     * @return string
     */
    public function getParentTitle()
    {
        return $this->parentTitle;
    }

    /**
     * @param string $parentTitle
     */
    public function setParentTitle($parentTitle)
    {
        $this->parentTitle = $parentTitle;
    }

    /**
     * @return string
     */
    public function getTeacherMessage()
    {
        return $this->teacherMessage;
    }

    /**
     * @param string $teacherMessage
     */
    public function setTeacherMessage($teacherMessage)
    {
        $this->teacherMessage = $teacherMessage;
    }

    /**
     * @return string
     */
    public function getParentMessage()
    {
        return $this->parentMessage;
    }

    /**
     * @param string $parentMessage
     */
    public function setParentMessage($parentMessage)
    {
        $this->parentMessage = $parentMessage;
    }

    /**
     * @return mixed
     */
    public function getTemplate1()
    {
        return $this->template1;
    }

    /**
     * @param mixed $template1
     */
    public function setTemplate1($template1)
    {
        $this->template1 = $template1;
    }

    /**
     * @return mixed
     */
    public function getTemplate2()
    {
        return $this->template2;
    }

    /**
     * @param mixed $template2
     */
    public function setTemplate2($template2)
    {
        $this->template2 = $template2;
    }

    /**
     * @return mixed
     */
    public function getInvitationLetter()
    {
        return $this->invitationLetter;
    }

    /**
     * @param mixed $invitationLetter
     */
    public function setInvitationLetter($invitationLetter)
    {
        $this->invitationLetter = $invitationLetter;
    }
    /* Getter and Setter */

}