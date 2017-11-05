<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\XmlRpc\Value\Boolean;

/**
 * @ORM\Entity
 * @ORM\Table(name="InvitationLetter")
 */
class InvitationLetter extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to ResultComment
     * @ORM\Column(type="integer", name="InvitationSettingId", nullable=true)
     *
     * @var integer
     */
    protected $invitationSettingId;

    /**
     * Foreing key reference to ConditionMessages
     * @ORM\Column(type="integer", name="AcquisitionMeritId", nullable=true)
     *
     * @var integer
     */
    protected $acquisitionMeritId;

    /**
     * Foreing key reference to ConditionMessages
     * @ORM\Column(type="integer", name="RecommendLevelMsg", nullable=true)
     *
     * @var integer
     */
    protected $recommendLevelMsgId;

    /**
     * Foreing key reference to ResultComment
     * @ORM\Column(type="integer", name="ResultCommentId", nullable=true)
     *
     * @var integer
     */
    protected $resultCommentId;

    /**
     * Foreing key reference to ResultComment
     * @ORM\Column(type="integer", name="ResultCommentId2", nullable=true)
     *
     * @var integer
     */
    protected $resultCommentId2;

    /**
     * Foreing key reference to ConditionMessages
     * @ORM\Column(type="integer", name="QuestionGuideId", nullable=true)
     *
     * @var integer
     */
    protected $questionGuideId;

    /**
     * Foreing key reference to QuestionFormat
     * @ORM\Column(type="integer", name="QuestionFormatId", nullable=true)
     *
     * @var integer
     */
    protected $questionFormatId;

    /**
     * @ORM\Column(type="boolean", name="PrintMessage", nullable=true)
     *
     * @var boolean
     */
    protected $printMessage;

    /**
     * Foreing key reference to ConditionMessages
     * @ORM\Column(type="integer", name="CanDoListMessageListeningId", nullable=true)
     *
     * @var integer
     */
    protected $canDoListMessageListeningId;

    /**
     * Foreing key reference to ConditionMessages
     * @ORM\Column(type="integer", name="CanDoListMessageReadingId", nullable=true)
     *
     * @var integer
     */
    protected $canDoListMessageReadingId;

    /**
     * Foreing key reference to ConditionMessages
     * @ORM\Column(type="integer", name="CanDoListMessageSpeakingId", nullable=true)
     *
     * @var integer
     */
    protected $canDoListMessageSpeakingId;

    /**
     * Foreing key reference to ConditionMessages
     * @ORM\Column(type="integer", name="CanDoListMessageWritingId", nullable=true)
     *
     * @var integer
     */
    protected $canDoListMessageWritingId;

    /**
     * Foreing key reference to EikenSchedule
     * @ORM\Column(type="integer", name="EikenScheduleId", nullable=true)
     *
     * @var integer
     */
    protected $eikenScheduleId;

    /**
     * Foreing key reference to Pupil
     * @ORM\Column(type="integer", name="PupilId", nullable=true)
     *
     * @var integer
     */
    protected $pupilId;

    /**
     * Foreing key reference to City
     * @ORM\Column(type="integer", name="CityId", nullable=true)
     *
     * @var integer
     */
    protected $cityId;

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
     * Foreing key reference to RecommendLevel
     * @ORM\Column(type="integer", name="RecommendLevelId", nullable=true)
     *
     * @var integer
     */
    protected $recommendLevelId;
    
    /**
     * Foreing key reference to RecommendLevel
     * @ORM\Column(type="string", name="RecommendLevelName", length=250, nullable=true)
     *
     * @var integer
     */
    protected $recommendLevelName;

    /* Property */
    
    /**
     * @ORM\Column(type="smallint", name="HallType", nullable=true)
     *
     * @var integer
     */
    protected $hallType;

    /**
     * @ORM\Column(type="string", name="Sender", length=250, nullable=true)
     *
     * @var string
     */
    protected $sender;

    /**
     * @ORM\Column(type="string", name="Title", length=250, nullable=true)
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type="string", name="OrganizationName", length=250, nullable=true)
     *
     * @var string
     */
    protected $organizationName;

    /**
     * @ORM\Column(type="string", name="NumberPupil", length=50, nullable=true)
     *
     * @var string
     */
    protected $numberPupil;

    /**
     * @ORM\Column(type="string", name="Messages1", length=500, nullable=true)
     *
     * @var string
     */
    protected $messages1;

    /**
     * @ORM\ManyToOne(targetEntity="TemplateInvitationMsg")
     * @ORM\JoinColumn(name="TemplateInvitationMsgId2", referencedColumnName="id")
     */
    protected $template2;

    /**
     * @ORM\Column(type="string", name="Messages2", length=500, nullable=true)
     *
     * @var string
     */
    protected $messages2;

    /**
     * @ORM\Column(type="string", name="DoubleExamMessages", length=500, nullable=true)
     *
     * @var string
     */
    protected $doubleExamMsgs;

    /**
     * @ORM\Column(type="string", name="Combini", length=1000, nullable=true)
     *
     * @var string
     */
    protected $combini;

    /**
     * @ORM\Column(type="datetime", name="Deadline", nullable=true)
     */
    protected $deadline;

    /**
     * @ORM\Column(type="datetime", name="ExamDate1", nullable=true)
     */
    protected $examDate1;

    /**
     * @ORM\Column(type="datetime", name="ExamDate12", nullable=true)
     */
    protected $examDate12;

    /**
     * @ORM\Column(type="string", name="ExamPlace1", length=250, nullable=true)
     */
    protected $examPlace1;

    /**
     * @ORM\Column(type="datetime", name="ExamDate2", nullable=true)
     */
    protected $examDate2;

    /**
     * @ORM\Column(type="string", name="ExamPlace2", length=250, nullable=true)
     */
    protected $examPlace2;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="ConditionMessages")
     * @ORM\JoinColumn(name="CanDoListMessageListeningId", referencedColumnName="id")
     */
    protected $canDoListMessageListening;

    /**
     * @ORM\ManyToOne(targetEntity="ConditionMessages")
     * @ORM\JoinColumn(name="CanDoListMessageReadingId", referencedColumnName="id")
     */
    protected $canDoListMessageReading;

    /**
     * @ORM\ManyToOne(targetEntity="ConditionMessages")
     * @ORM\JoinColumn(name="CanDoListMessageSpeakingId", referencedColumnName="id")
     */
    protected $canDoListMessageSpeaking;

    /**
     * @ORM\ManyToOne(targetEntity="ConditionMessages")
     * @ORM\JoinColumn(name="CanDoListMessageWritingId", referencedColumnName="id")
     */
    protected $canDoListMessageWriting;

    /**
     * @ORM\ManyToOne(targetEntity="RecommendLevel")
     * @ORM\JoinColumn(name="RecommendLevelId", referencedColumnName="id")
     */
    protected $recommendLevel;

    /**
     * @ORM\ManyToOne(targetEntity="TemplateInvitationMsg")
     * @ORM\JoinColumn(name="TemplateInvitationMsgId1", referencedColumnName="id")
     */
    protected $template1;

    /**
     * @ORM\ManyToOne(targetEntity="EikenSchedule")
     * @ORM\JoinColumn(name="EikenScheduleId", referencedColumnName="id")
     */
    protected $eikenSchedule;

    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="CityId", referencedColumnName="id")
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity="ConditionMessages")
     * @ORM\JoinColumn(name="AcquisitionMeritId", referencedColumnName="id")
     */
    protected $acquisitionMerit;

    /**
     * @ORM\ManyToOne(targetEntity="ConditionMessages")
     * @ORM\JoinColumn(name="RecommendLevelMsgId", referencedColumnName="id")
     */
    protected $recommendLevelMsg;

    /**
     * @ORM\ManyToOne(targetEntity="ResultsComment")
     * @ORM\JoinColumn(name="ResultCommentId", referencedColumnName="id")
     */
    protected $resultComment;

    /**
     * @ORM\ManyToOne(targetEntity="ResultsComment")
     * @ORM\JoinColumn(name="ResultCommentId2", referencedColumnName="id")
     */
    protected $resultComment2;

    /**
     * @ORM\ManyToOne(targetEntity="ConditionMessages")
     * @ORM\JoinColumn(name="QuestionGuideId", referencedColumnName="id")
     */
    protected $questionGuide;

    /**
     * @ORM\ManyToOne(targetEntity="ConditionMessages")
     * @ORM\JoinColumn(name="QuestionFormatId", referencedColumnName="id")
     */
    protected $questionFormat;

    /**
     * @ORM\ManyToOne(targetEntity="InvitationSetting")
     * @ORM\JoinColumn(name="InvitationSettingId", referencedColumnName="id")
     */
    protected $invitationSetting;

    /**
     * @ORM\Column(type="boolean", name="UpdateByHand",options={"default":0})
     *
     * @var boolean
     */
    protected $updateByHand = 0;

    /**
     *
     * @return int
     */
    public function getInvitationSettingId()
    {
        return $this->invitationSettingId;
    }

    /**
     *
     * @param int $invitationSettingId            
     */
    public function setInvitationSettingId($invitationSettingId)
    {
        $this->invitationSettingId = $invitationSettingId;
    }

    /**
     *
     * @return int
     */
    public function getAcquisitionMeritId()
    {
        return $this->acquisitionMeritId;
    }

    /**
     *
     * @param int $acquisitionMeritId            
     */
    public function setAcquisitionMeritId($acquisitionMeritId)
    {
        $this->acquisitionMeritId = $acquisitionMeritId;
    }

    /**
     *
     * @return int
     */
    public function getRecommendLevelMsgId()
    {
        return $this->recommendLevelMsgId;
    }

    /**
     *
     * @param int $recommendLevelMsgId            
     */
    public function setRecommendLevelMsgId($recommendLevelMsgId)
    {
        $this->recommendLevelMsgId = $recommendLevelMsgId;
    }

    /**
     *
     * @return int
     */
    public function getResultCommentId()
    {
        return $this->resultCommentId;
    }

    /**
     *
     * @param int $resultCommentId            
     */
    public function setResultCommentId($resultCommentId)
    {
        $this->resultCommentId = $resultCommentId;
    }

    /**
     *
     * @return int
     */
    public function getQuestionGuideId()
    {
        return $this->questionGuideId;
    }

    /**
     *
     * @param int $questionGuideId            
     */
    public function setQuestionGuideId($questionGuideId)
    {
        $this->questionGuideId = $questionGuideId;
    }

    /**
     *
     * @return int
     */
    public function getQuestionFormatId()
    {
        return $this->questionFormatId;
    }

    /**
     *
     * @param int $questionFormatId            
     */
    public function setQuestionFormatId($questionFormatId)
    {
        $this->questionFormatId = $questionFormatId;
    }

    /**
     *
     * @return int
     */
    public function getEikenScheduleId()
    {
        return $this->eikenScheduleId;
    }

    /**
     *
     * @param int $eikenScheduleId            
     */
    public function setEikenScheduleId($eikenScheduleId)
    {
        $this->eikenScheduleId = $eikenScheduleId;
    }

    /**
     *
     * @return int
     */
    public function getPupilId()
    {
        return $this->pupilId;
    }

    /**
     *
     * @return int
     */
    public function getCanDoListMessageListeningId()
    {
        return $this->canDoListMessageListeningId;
    }

    /**
     *
     * @param int $canDoListMessageListeningId            
     */
    public function setCanDoListMessageListeningId($canDoListMessageListeningId)
    {
        $this->canDoListMessageListeningId = $canDoListMessageListeningId;
    }

    /**
     *
     * @return int
     */
    public function getCanDoListMessageReadingId()
    {
        return $this->canDoListMessageReadingId;
    }

    /**
     *
     * @param int $canDoListMessageReadingId            
     */
    public function setCanDoListMessageReadingId($canDoListMessageReadingId)
    {
        $this->canDoListMessageReadingId = $canDoListMessageReadingId;
    }

    /**
     *
     * @return int
     */
    public function getCanDoListMessageSpeakingId()
    {
        return $this->canDoListMessageSpeakingId;
    }

    /**
     *
     * @param int $canDoListMessageSpeakingId            
     */
    public function setCanDoListMessageSpeakingId($canDoListMessageSpeakingId)
    {
        $this->canDoListMessageSpeakingId = $canDoListMessageSpeakingId;
    }

    /**
     *
     * @return int
     */
    public function getCanDoListMessageWritingId()
    {
        return $this->canDoListMessageWritingId;
    }

    /**
     *
     * @param int $canDoListMessageWritingId            
     */
    public function setCanDoListMessageWritingId($canDoListMessageWritingId)
    {
        $this->canDoListMessageWritingId = $canDoListMessageWritingId;
    }

    /**
     *
     * @return mixed
     */
    public function getCanDoListMessageListening()
    {
        return $this->canDoListMessageListening;
    }

    /**
     *
     * @param mixed $canDoListMessageListening            
     */
    public function setCanDoListMessageListening($canDoListMessageListening)
    {
        $this->canDoListMessageListening = $canDoListMessageListening;
    }

    /**
     *
     * @return mixed
     */
    public function getCanDoListMessageReading()
    {
        return $this->canDoListMessageReading;
    }

    /**
     *
     * @param mixed $canDoListMessageReading            
     */
    public function setCanDoListMessageReading($canDoListMessageReading)
    {
        $this->canDoListMessageReading = $canDoListMessageReading;
    }

    /**
     *
     * @return mixed
     */
    public function getCanDoListMessageSpeaking()
    {
        return $this->canDoListMessageSpeaking;
    }

    /**
     *
     * @param mixed $canDoListMessageSpeaking            
     */
    public function setCanDoListMessageSpeaking($canDoListMessageSpeaking)
    {
        $this->canDoListMessageSpeaking = $canDoListMessageSpeaking;
    }

    /**
     *
     * @return mixed
     */
    public function getCanDoListMessageWriting()
    {
        return $this->canDoListMessageWriting;
    }

    /**
     *
     * @param mixed $canDoListMessageWriting            
     */
    public function setCanDoListMessageWriting($canDoListMessageWriting)
    {
        $this->canDoListMessageWriting = $canDoListMessageWriting;
    }

    /**
     *
     * @param int $pupilId            
     */
    public function setPupilId($pupilId)
    {
        $this->pupilId = $pupilId;
    }

    /**
     *
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     *
     * @param int $cityId            
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;
    }

    /**
     *
     * @return int
     */
    public function getTemplateInvitationMsgId1()
    {
        return $this->templateInvitationMsgId1;
    }

    /**
     *
     * @param int $templateInvitationMsgId1            
     */
    public function setTemplateInvitationMsgId1($templateInvitationMsgId1)
    {
        $this->templateInvitationMsgId1 = $templateInvitationMsgId1;
    }

    /**
     *
     * @return int
     */
    public function getTemplateInvitationMsgId2()
    {
        return $this->templateInvitationMsgId2;
    }

    /**
     *
     * @param int $templateInvitationMsgId2            
     */
    public function setTemplateInvitationMsgId2($templateInvitationMsgId2)
    {
        $this->templateInvitationMsgId2 = $templateInvitationMsgId2;
    }

    /**
     *
     * @return int
     */
    public function getRecommendLevelId()
    {
        return $this->recommendLevelId;
    }

    /**
     *
     * @param int $recommendLevelId            
     */
    public function setRecommendLevelId($recommendLevelId)
    {
        $this->recommendLevelId = $recommendLevelId;
    }

    /**
     *
     * @return int
     */
    public function getHallType()
    {
        return $this->hallType;
    }

    /**
     *
     * @param int $hallType            
     */
    public function setHallType($hallType)
    {
        $this->hallType = $hallType;
    }

    /**
     *
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     *
     * @param string $sender            
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @param string $title            
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     *
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     *
     * @param string $organizationName            
     */
    public function setOrganizationName($organizationName)
    {
        $this->organizationName = $organizationName;
    }

    /**
     *
     * @return string
     */
    public function getNumberPupil()
    {
        return $this->numberPupil;
    }

    /**
     *
     * @param string $numberPupil            
     */
    public function setNumberPupil($numberPupil)
    {
        $this->numberPupil = $numberPupil;
    }

    /**
     *
     * @return string
     */
    public function getMessages1()
    {
        return $this->messages1;
    }

    /**
     *
     * @param string $messages1            
     */
    public function setMessages1($messages1)
    {
        $this->messages1 = $messages1;
    }

    /**
     *
     * @return mixed
     */
    public function getTemplate2()
    {
        return $this->template2;
    }

    /**
     *
     * @param mixed $template2            
     */
    public function setTemplate2($template2)
    {
        $this->template2 = $template2;
    }

    /**
     *
     * @return string
     */
    public function getMessages2()
    {
        return $this->messages2;
    }

    /**
     *
     * @param string $messages2            
     */
    public function setMessages2($messages2)
    {
        $this->messages2 = $messages2;
    }

    /**
     *
     * @return string
     */
    public function getDoubleExamMsgs()
    {
        return $this->doubleExamMsgs;
    }

    /**
     *
     * @param string $doubleExamMsgs            
     */
    public function setDoubleExamMsgs($doubleExamMsgs)
    {
        $this->doubleExamMsgs = $doubleExamMsgs;
    }

    /**
     *
     * @return string
     */
    public function getCombini()
    {
        return $this->combini;
    }

    /**
     *
     * @param string $combini            
     */
    public function setCombini($combini)
    {
        $this->combini = $combini;
    }

    /**
     *
     * @return mixed
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     *
     * @param mixed $deadline            
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
    }

    /**
     *
     * @return \DateTime
     */
    public function getExamDate1()
    {
        return $this->examDate1;
    }

    /**
     *
     * @param mixed $examDate1            
     */
    public function setExamDate1($examDate1)
    {
        $this->examDate1 = $examDate1;
    }

    /**
     *
     * @return mixed
     */
    public function getExamPlace1()
    {
        return $this->examPlace1;
    }

    /**
     *
     * @param mixed $examPlace1            
     */
    public function setExamPlace1($examPlace1)
    {
        $this->examPlace1 = $examPlace1;
    }

    /**
     *
     * @return mixed
     */
    public function getExamDate2()
    {
        return $this->examDate2;
    }

    /**
     *
     * @param mixed $examDate2            
     */
    public function setExamDate2($examDate2)
    {
        $this->examDate2 = $examDate2;
    }

    /**
     *
     * @return mixed
     */
    public function getExamPlace2()
    {
        return $this->examPlace2;
    }

    /**
     *
     * @param mixed $examPlace2            
     */
    public function setExamPlace2($examPlace2)
    {
        $this->examPlace2 = $examPlace2;
    }

    /**
     *
     * @return \Application\Entity\RecommendLevel
     */
    public function getRecommendLevel()
    {
        return $this->recommendLevel;
    }

    /**
     *
     * @param mixed $recommendLevel            
     */
    public function setRecommendLevel($recommendLevel)
    {
        $this->recommendLevel = $recommendLevel;
    }

    /**
     *
     * @return mixed
     */
    public function getTemplate1()
    {
        return $this->template1;
    }

    /**
     *
     * @param mixed $template1            
     */
    public function setTemplate1($template1)
    {
        $this->template1 = $template1;
    }

    /**
     *
     * @return mixed
     */
    public function getEikenSchedule()
    {
        return $this->eikenSchedule;
    }

    /**
     *
     * @param mixed $eikenSchedule            
     */
    public function setEikenSchedule($eikenSchedule)
    {
        $this->eikenSchedule = $eikenSchedule;
    }

    /**
     *
     * @return \Application\Entity\Pupil
     */
    public function getPupil()
    {
        return $this->pupil;
    }

    /**
     *
     * @param mixed $pupil            
     */
    public function setPupil($pupil)
    {
        $this->pupil = $pupil;
    }

    /**
     *
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     *
     * @return boolean
     */
    public function getPrintMessage()
    {
        return $this->printMessage;
    }

    /**
     *
     * @param boolean $printMessage            
     */
    public function setPrintMessage($printMessage)
    {
        $this->printMessage = $printMessage;
    }

    /**
     *
     * @return \Application\Entity\InvitationSetting
     */
    public function getInvitationSetting()
    {
        return $this->invitationSetting;
    }

    /**
     *
     * @param mixed $invitationSetting            
     */
    public function setInvitationSetting($invitationSetting)
    {
        $this->invitationSetting = $invitationSetting;
    }

    /**
     *
     * @param mixed $city            
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     *
     * @return mixed
     */
    public function getAcquisitionMerit()
    {
        return $this->acquisitionMerit;
    }

    /**
     *
     * @param mixed $acquisitionMerit            
     */
    public function setAcquisitionMerit($acquisitionMerit)
    {
        $this->acquisitionMerit = $acquisitionMerit;
    }

    /**
     *
     * @return mixed
     */
    public function getRecommendLevelMsg()
    {
        return $this->recommendLevelMsg;
    }

    /**
     *
     * @param mixed $recommendLevelMsg            
     */
    public function setRecommendLevelMsg($recommendLevelMsg)
    {
        $this->recommendLevelMsg = $recommendLevelMsg;
    }

    /**
     *
     * @return mixed
     */
    public function getResultComment()
    {
        return $this->resultComment;
    }

    /**
     *
     * @param mixed $resultComment            
     */
    public function setResultComment($resultComment)
    {
        $this->resultComment = $resultComment;
    }

    /**
     *
     * @return mixed
     */
    public function getQuestionGuide()
    {
        return $this->questionGuide;
    }

    /**
     *
     * @param mixed $questionGuide            
     */
    public function setQuestionGuide($questionGuide)
    {
        $this->questionGuide = $questionGuide;
    }

    /**
     *
     * @return mixed
     */
    public function getQuestionFormat()
    {
        return $this->questionFormat;
    }

    /**
     *
     * @param mixed $questionFormat            
     */
    public function setQuestionFormat($questionFormat)
    {
        $this->questionFormat = $questionFormat;
    }

    /**
     *
     * @return int
     */
    public function getResultCommentId2()
    {
        return $this->resultCommentId2;
    }

    /**
     *
     * @param int $resultCommentId2            
     */
    public function setResultCommentId2($resultCommentId2)
    {
        $this->resultCommentId2 = $resultCommentId2;
    }

    /**
     *
     * @return mixed
     */
    public function getResultComment2()
    {
        return $this->resultComment2;
    }

    /**
     *
     * @param mixed $resultComment2            
     */
    public function setResultComment2($resultComment2)
    {
        $this->resultComment2 = $resultComment2;
    }

    /**
     *
     * @return mixed
     */
    public function getExamDate12()
    {
        return $this->examDate12;
    }

    /**
     *
     * @param mixed $examDate12            
     */
    public function setExamDate12($examDate12)
    {
        $this->examDate12 = $examDate12;
    }

    /**
     * @return boolean
     */
    public function isUpdateByHand()
    {
        return $this->updateByHand;
    }

    /**
     * @param boolean $updateByHand
     */
    public function setUpdateByHand($updateByHand)
    {
        $this->updateByHand = $updateByHand;
    }
    /* Getter and Setter */
    
    function getUpdateByHand() {
        return $this->updateByHand;
    }
    
    public function getRecommendLevelName() {
        return $this->recommendLevelName;
    }

    public function setRecommendLevelName($recommendLevelName) {
        $this->recommendLevelName = $recommendLevelName;
    }

}