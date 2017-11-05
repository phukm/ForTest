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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\InvitationSettingRepository")
 * @ORM\Table(name="InvitationSetting")
 */
class InvitationSetting extends Common
{

    /* Foreing key */

    /* Property */
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
     * Foreing key reference to DoubleEikenMessages
     * @ORM\Column(type="integer", name="DoubleEikenMessagesId", nullable=true)
     *
     * @var integer
     */
    protected $doubleEikenMessagesId;

    /**
     * Foreing key reference to Organization
     * @ORM\Column(type="integer", name="OrganizationId", nullable=true)
     *
     * @var integer
     */
    protected $organizationId;

    /**
     * Foreing key reference to EikenSchedule
     * @ORM\Column(type="integer", name="EikenScheduleId", nullable=true)
     *
     * @var integer
     */
    protected $eikenScheduleId;

    /**
     * @ORM\Column(type="integer", name="HallType", nullable=true)
     *
     * @var integer
     */
    protected $hallType;

    /**
     * @ORM\Column(type="string", name="ListEikenLevel", length=250, nullable=true)
     *
     * @var string
     */
    protected $listEikenLevel;

    /**
     * @ORM\Column(type="integer", name="InvitationType", nullable=true)
     *
     * @var integer
     */
    protected $invitationType;

    /**
     * @ORM\Column(type="boolean", name="PrintMessage", nullable=true)
     *
     * @var boolean
     */
    protected $printMessage;

    /**
     * @ORM\Column(type="string", name="Message1", length=500, nullable=true)
     *
     * @var string
     */
    protected $message1;

    /**
     * @ORM\Column(type="string", name="Message2", length=500, nullable=true)
     *
     * @var string
     */
    protected $message2;

    /**
     * @ORM\Column(type="smallint", name="ExamDay", nullable=true)
     *
     * @var smallint
     */
    protected $examDay;

    /**
     * @ORM\Column(type="string", name="ExamPlace", length=250, nullable=true)
     *
     * @var string
     */
    protected $examPlace;

    /**
     * @ORM\Column(type="datetime", name="Deadline", nullable=true)
     */
    protected $deadLine;

    /**
     * @ORM\Column(type="smallint", name="PaymentType", nullable=true)
     */
    protected $paymentType;

    /**
     * @ORM\Column(type="smallint", name="OrganizationPayment", nullable=true)
     *
     * @var integer
     */
    protected $organizationPayment;

    /**
     * @ORM\Column(type="string", name="PersonalPayment", nullable=true)
     */
    protected $personalPayment;

    /**
     * @ORM\Column(type="string", name="Combini", length=500, nullable=true)
     *
     * @var string
     */
    protected $combini;

    /**
     * @ORM\Column(type="string", name="DoubleEikenMessage", length=500, nullable=true)
     *
     * @var string
     */
    protected $doubleEikenMessage;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

    /**
     * @ORM\ManyToOne(targetEntity="EikenSchedule")
     * @ORM\JoinColumn(name="EikenScheduleId", referencedColumnName="id")
     */
    protected $eikenSchedule;

    /**
     * @ORM\ManyToOne(targetEntity="DoubleEikenMessages")
     * @ORM\JoinColumn(name="DoubleEikenMessagesId", referencedColumnName="id")
     */
    protected $doubleEiken;

    /**
     * @ORM\ManyToOne(targetEntity="TemplateInvitationMsg")
     * @ORM\JoinColumn(name="TemplateInvitationMsgId2", referencedColumnName="id")
     */
    protected $templateMsg2;

    /**
     * @ORM\ManyToOne(targetEntity="TemplateInvitationMsg")
     * @ORM\JoinColumn(name="TemplateInvitationMsgId1", referencedColumnName="id")
     */
    protected $templateMsg1;
    
    /**
     * @ORM\Column(type="string", name="OrganizationName", length=250, nullable=true)
     *
     * @var string
     */
    protected $organizationName;
    
    /**
     * @ORM\Column(type="string", name="PrincipalName", length=250, nullable=true)
     *
     * @var string
     */
    protected $principalName;
    
    /**
     * @ORM\Column(type="datetime", name="IssueDate", nullable=true)
     */
    protected $issueDate;
    
    /**
     * @ORM\Column(type="string", name="PersonalTitle", length=250, nullable=true)
     *
     * @var string
     */
    protected $personalTitle;
    
    /**
     * @ORM\Column(type="integer", name="tempHallType", nullable=true)
     *
     * @var integer
     */
    protected $tempHallType;
    
    /**
     * @ORM\Column(type="integer", name="tempPaymentType", nullable=true)
     *
     * @var integer
     */
    protected $tempPaymentType;
    
    /**
     * @ORM\Column(type="integer", name="tempOrganizationPayment", nullable=true)
     *
     * @var integer
     */
    protected $tempOrganizationPayment;
    
    /**
     * @ORM\Column(type="string", name="TempPersonalPayment", nullable=true)
     */
    protected $tempPersonalPayment;
    
    /**
     * @ORM\Column(type="smallint", name="Beneficiary", nullable=true)
     */
    protected $beneficiary;
    
    /**
     * @ORM\Column(type="smallint", name="TempBeneficiary", nullable=true)
     */
    protected $tempBeneficiary;

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
    public function getDoubleEikenMessagesId()
    {
        return $this->doubleEikenMessagesId;
    }

    /**
     * @param int $doubleEikenMessagesId
     */
    public function setDoubleEikenMessagesId($doubleEikenMessagesId)
    {
        $this->doubleEikenMessagesId = $doubleEikenMessagesId;
    }

    /**
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param int $organizationId
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     * @return int
     */
    public function getEikenScheduleId()
    {
        return $this->eikenScheduleId;
    }

    /**
     * @param int $eikenScheduleId
     */
    public function setEikenScheduleId($eikenScheduleId)
    {
        $this->eikenScheduleId = $eikenScheduleId;
    }

    /**
     * @return int
     */
    public function getHallType()
    {
        return $this->hallType;
    }

    /**
     * @param int $hallType
     */
    public function setHallType($hallType)
    {
        $this->hallType = $hallType;
    }

    /**
     * @return string
     */
    public function getListEikenLevel()
    {
        return $this->listEikenLevel;
    }

    /**
     * @param string $listEikenLevel
     */
    public function setListEikenLevel($listEikenLevel)
    {
        $this->listEikenLevel = $listEikenLevel;
    }

    /**
     * @return int
     */
    public function getInvitationType()
    {
        return $this->invitationType;
    }

    /**
     * @param int $invitationType
     */
    public function setInvitationType($invitationType)
    {
        $this->invitationType = $invitationType;
    }
    
    /**
     * @return boolean
     */
    public function isPrintMessage()
    {
        return $this->printMessage;
    }

    /**
     * @return boolean
     */
    public function getPrintMessage()
    {
        return $this->printMessage;
    }

    /**
     * @param boolean $printMessage
     */
    public function setPrintMessage($printMessage)
    {
        $this->printMessage = $printMessage;
    }

    /**
     * @return string
     */
    public function getMessage1()
    {
        return $this->message1;
    }

    /**
     * @param string $message1
     */
    public function setMessage1($message1)
    {
        $this->message1 = $message1;
    }

    /**
     * @return string
     */
    public function getMessage2()
    {
        return $this->message2;
    }

    /**
     * @param string $message2
     */
    public function setMessage2($message2)
    {
        $this->message2 = $message2;
    }

    /**
     * @return smallint
     */
    public function getExamDay()
    {
        return $this->examDay;
    }

    /**
     * @param smallint $examDay
     */
    public function setExamDay($examDay)
    {
        $this->examDay = $examDay;
    }

    /**
     * @return string
     */
    public function getExamPlace()
    {
        return $this->examPlace;
    }

    /**
     * @param string $examPlace
     */
    public function setExamPlace($examPlace)
    {
        $this->examPlace = $examPlace;
    }

    /**
     * @return \DateTime
     */
    public function getDeadLine()
    {
        return $this->deadLine;
    }

    /**
     * @param mixed $deadLine
     */
    public function setDeadLine($deadLine)
    {
        $this->deadLine = $deadLine;
    }

    /**
     * @return mixed
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param mixed $paymentType
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    /**
     * @return int
     */
    public function getOrganizationPayment()
    {
        return $this->organizationPayment;
    }

    /**
     * @param int $organizationPayment
     */
    public function setOrganizationPayment($OrganizationPayment)
    {
        $this->organizationPayment = $OrganizationPayment;
    }

    /**
     * @return mixed
     */
    public function getPersonalPayment()
    {
        return $this->personalPayment;
    }

    /**
     * @param mixed $personalPayment
     */
    public function setPersonalPayment($personalPayment)
    {
        $this->personalPayment = $personalPayment;
    }

    /**
     * @return string
     */
    public function getCombini()
    {
        return $this->combini;
    }

    /**
     * @param string $combini
     */
    public function setCombini($combini)
    {
        $this->combini = $combini;
    }

    /**
     * @return string
     */
    public function getDoubleEikenMessage()
    {
        return $this->doubleEikenMessage;
    }

    /**
     * @param string $doubleEikenMessage
     */
    public function setDoubleEikenMessage($doubleEikenMessage)
    {
        $this->doubleEikenMessage = $doubleEikenMessage;
    }

    /**
     * @return \Application\Entity\Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return \Application\Entity\EikenSchedule
     */
    public function getEikenSchedule()
    {
        return $this->eikenSchedule;
    }

    /**
     * @param mixed $eikenSchedule
     */
    public function setEikenSchedule($eikenSchedule)
    {
        $this->eikenSchedule = $eikenSchedule;
    }

    /**
     * @return mixed
     */
    public function getDoubleEiken()
    {
        return $this->doubleEiken;
    }

    /**
     * @param mixed $doubleEiken
     */
    public function setDoubleEiken($doubleEiken)
    {
        $this->doubleEiken = $doubleEiken;
    }

    /**
     * @return mixed
     */
    public function getTemplateMsg2()
    {
        return $this->templateMsg2;
    }

    /**
     * @param mixed $templateMsg2
     */
    public function setTemplateMsg2($templateMsg2)
    {
        $this->templateMsg2 = $templateMsg2;
    }

    /**
     * @return mixed
     */
    public function getTemplateMsg1()
    {
        return $this->templateMsg1;
    }

    /**
     * @param mixed $templateMsg1
     */
    public function setTemplateMsg1($templateMsg1)
    {
        $this->templateMsg1 = $templateMsg1;
    }

    /**
     * @return string
     */
    function getOrganizationName() {
        return $this->organizationName;
    }

    /**
     * @return string
     */
    function getPrincipalName() {
        return $this->principalName;
    }
    
    /**
     * @return \DateTime
     */
    function getIssueDate() {
        return $this->issueDate;
    }

    /**
     * @return string
     */
    function getPersonalTitle() {
        return $this->personalTitle;
    }
       
    /**
     * @param string $organizationName
     */
    function setOrganizationName($organizationName) {
        $this->organizationName = $organizationName;
    }

    /**
     * @param string $principalName
     */
    function setPrincipalName($principalName) {
        $this->principalName = $principalName;
    }

    /**
     * @param mixed $issueDate
     */
    function setIssueDate($issueDate) {
        $this->issueDate = $issueDate;
    }

    /**
     * @param string $principalName
     */
    function setPersonalTitle($personalTitle) {
        $this->personalTitle = $personalTitle;
    }
    
   /**
     * @return mixed
     */
    public function getTempHallType()
    {
        return $this->tempHallType;
    }

    /**
     * @param mixed $tempHallType
     */
    public function setTempHallType($tempHallType)
    {
        $this->tempHallType = $tempHallType;
    }
    /**
     * @return mixed
     */
    public function getTempPaymentType()
    {
        return $this->tempPaymentType;
    }

    /**
     * @param mixed $tempPaymentType
     */
    public function setTempPaymentType($tempPaymentType)
    {
        $this->tempPaymentType = $tempPaymentType;
    }
    
    /**
     * @return mixed
     */
    public function getTempOrganizationPayment()
    {
        return $this->tempOrganizationPayment;
    }

    /**
     * @param mixed $tempOrganizationPayment
     */
    public function setTempOrganizationPayment($tempOrganizationPayment)
    {
        $this->tempOrganizationPayment = $tempOrganizationPayment;
    }
    
    /**
     * @return mixed
     */
    public function getTempPersonalPayment()
    {
        return $this->tempPersonalPayment;
    }

    /**
     * @param mixed $tempPersonalPayment
     */
    public function setTempPersonalPayment($tempPersonalPayment)
    {
        $this->tempPersonalPayment = $tempPersonalPayment;
    }
    
    /**
     * @return mixed
     */
    public function getBeneficiary()
    {
        return $this->beneficiary;
    }

    /**
     * @param mixed $benificiary
     */
    public function setBeneficiary($beneficiary)
    {
        $this->beneficiary = $beneficiary;
    }
    
    function getTempBeneficiary() {
        return $this->tempBeneficiary;
    }

    function setTempBeneficiary($tempBeneficiary) {
        $this->tempBeneficiary = $tempBeneficiary;
    }
    
}