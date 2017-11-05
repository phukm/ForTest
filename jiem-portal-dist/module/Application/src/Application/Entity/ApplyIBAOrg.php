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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ApplyIBAOrgRepository")
 * @ORM\Table(name="ApplyIBAOrg")
 */
class ApplyIBAOrg extends Common {

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
     * @ORM\Column(type="string", name="FirtNameKanji", length=50, nullable=true)
     *
     * @var string
     */
    protected $firtNameKanji;

    /**
     * @ORM\Column(type="string", name="LastNameKanji", length=50, nullable=true)
     *
     * @var string
     */
    protected $lastNameKanji;

    /**
     * @ORM\Column(type="string", name="MailAddress", length=100, nullable=true)
     *
     * @var string
     */
    protected $mailAddress;

    /**
     * @ORM\Column(type="string", name="ConfirmEmail", length=100, nullable=true)
     *
     * @var string
     */
    protected $confirmEmail;

    /**
     * @ORM\Column(type="string", name="PhoneNumber", length=100, nullable=true)
     *
     * @var string
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(type="string", name="OrganizationNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $organizationNo;

    /**
     * @ORM\Column(type="string", name="OrgNameKanji", length=250, nullable=true)
     *
     * @var string
     */
    protected $orgNameKanji;

    /**
     * @ORM\Column(type="string", name="TelNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $telNo;

    /**
     * @ORM\Column(type="string", name="Fax", length=50, nullable=true)
     *
     * @var string
     */
    protected $fax;

    /**
     * @ORM\Column(type="string", name="MailName1", length=100, nullable=true)
     *
     * @var string
     */
    protected $mailName1;

    /**
     * @ORM\Column(type="string", name="MailAddress1", length=100, nullable=true)
     *
     * @var string
     */
    protected $mailAddress1;

    /**
     * @ORM\Column(type="string", name="MailName2", length=100, nullable=true)
     *
     * @var string
     */
    protected $mailName2;

    /**
     * @ORM\Column(type="string", name="MailAddress2", length=100, nullable=true)
     *
     * @var string
     */
    protected $mailAddress2;

    /**
     * @ORM\Column(type="string", name="MailName3", length=100, nullable=true)
     *
     * @var string
     */
    protected $mailName3;

    /**
     * @ORM\Column(type="string", name="MailAddress3", length=100, nullable=true)
     *
     * @var string
     */
    protected $mailAddress3;

    /**
     * @ORM\Column(type="datetime", name="TestDate", nullable=true)
     */
    protected $testDate;

    /**
     * @ORM\Column(type="string", name="Purpose", length=255, nullable=true)
     */
    protected $purpose;

    /**
     * @ORM\Column(type="text", name="PurposeOther", nullable=true)
     */
    protected $purposeOther;

    /**
     * @ORM\Column(type="string", name="PrefectureCode", length=50, nullable=true)
     *
     * @var string
     */
    protected $prefectureCode;

    /**
     * @ORM\Column(type="string", name="Address1", length=500, nullable=true)
     *
     * @var string
     */
    protected $address1;

    /**
     * @ORM\Column(type="string", name="Address2", length=500, nullable=true)
     *
     * @var string
     */
    protected $address2;

    /**
     * @ORM\Column(type="string", name="PICName", length=100, nullable=true)
     *
     * @var string
     */
    protected $pICName;

    /**
     * @ORM\Column(type="string", name="ZipCode1", length=8, nullable=true)
     */
    protected $zipCode1;

    /**
     * @ORM\Column(type="string", name="ZipCode2", length=8, nullable=true)
     */
    protected $zipCode2;

    /**
     * @ORM\Column(type="smallint", name="NumberPeopleA", nullable=true)
     */
    protected $numberPeopleA;

    /**
     * @ORM\Column(type="smallint", name="NumberPeopleB", nullable=true)
     */
    protected $numberPeopleB;

    /**
     * @ORM\Column(type="smallint", name="NumberPeopleC", nullable=true)
     */
    protected $numberPeopleC;

    /**
     * @ORM\Column(type="smallint", name="NumberPeopleD", nullable=true)
     */
    protected $numberPeopleD;

    /**
     * @ORM\Column(type="smallint", name="NumberPeopleE", nullable=true)
     */
    protected $numberPeopleE;

    /**
     * @ORM\Column(type="smallint", name="TotalPeople", nullable=true, options={"unsigned"=true})
     */
    protected $totalPeople;

    /**
     * @ORM\Column(type="smallint", name="NumberCDA", nullable=true)
     */
    protected $numberCDA;

    /**
     * @ORM\Column(type="smallint", name="NumberCDB", nullable=true)
     */
    protected $numberCDB;

    /**
     * @ORM\Column(type="smallint", name="NumberCDC", nullable=true)
     */
    protected $numberCDC;

    /**
     * @ORM\Column(type="smallint", name="NumberCDD", nullable=true)
     */
    protected $numberCDD;

    /**
     * @ORM\Column(type="smallint", name="NumberCDE", nullable=true)
     */
    protected $numberCDE;

    /**
     * @ORM\Column(type="smallint", name="TotalCD", nullable=true, options={"unsigned"=true})
     */
    protected $totalCD;

    /**
     * @ORM\Column(name="Question1", type="text", nullable=true)
     *
     * @var string
     */
    protected $question1;

    /**
     * @ORM\Column(type="smallint", name="OptionApply", nullable=true)
     */
    protected $optionApply;

    /**
     * @ORM\Column(type="string", name="OptionMenu", nullable=true)
     */
    protected $optionMenu;

    /**
     * @ORM\Column(type="smallint", name="QuestionNo", nullable=true)
     */
    protected $questionNo;

    /**
     * @ORM\Column(type="smallint", name="RankNo", nullable=true)
     */
    protected $rankNo;

    /**
     * @ORM\Column(name="Question2",  type="text", nullable=true)
     *
     * @var string
     */
    protected $question2;

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
     * @ORM\Column(type="smallint", name="StatusMapping", nullable=true)
     *
     * @var boolean
     */
    protected $statusMapping;

    /**
     * @ORM\Column(type="smallint", name="StatusImporting", nullable=true)
     *
     * @var boolean
     */
    protected $statusImporting;

    /**
     * @ORM\Column(type="integer", name="TotalImport", nullable=true)
     *
     * @var integer
     */
    protected $totalImport;
    
    /**
     * @ORM\Column(type="boolean", name="IsValid", options={"default":0})
     *
     */
    protected $isValid = 0;
    
    
    /**
     * @ORM\Column(type="string", name="MoshikomiId", length=100, nullable=true)
     *
     * @var string
     */
    protected $moshikomiId = '';
    
    /**
     * @ORM\Column(type="datetime", name="RegistrationDate", nullable=true)
     */
    protected $registrationDate;
    
    /**
     * @ORM\Column(type="integer", name="StatusAutoImport",  nullable=true)
     *  @var boolean
     */
    protected $statusAutoImport = 0;
    
    /**
     * @ORM\Column(type="string", name="Session",length=32, nullable=true)
     *
     * @var string
     */
    protected $session;

    /**
     * @ORM\Column(type="string", name="ExamType",nullable=true)
     *
     * @var string
     */
    protected $examType;

    /**
     * @ORM\Column(type="string", name="JisshiId",nullable=true)
     *
     * @var string
     */
    protected $jisshiId;

    /**
     * @ORM\Column(type="integer", name="HasNewData",nullable=true)
     *
     * @var integer
     */
    protected $hasNewData;

    /**
     * @ORM\Column(type="integer", name="FromUketuke",nullable=true)
     *
     * @var integer
     */
    protected $fromUketuke;

    /**
     * @ORM\Column(type="string", name="JisshiKanriNo",nullable=true)
     *
     * @var string
     */
    protected $jisshiKanriNo ;

    /**
     * @ORM\Column(type="string", name="SetName",nullable=true)
     *
     * @var string
     */
    protected $setName ;

    /**
     * @ORM\Column(type="string", name="GroupNo",nullable=true)
     *
     * @var string
     */
    protected $groupNo ;

    /**
     * @ORM\Column(type="integer", name="Year",nullable=true)
     *
     * @var integer
     */
    protected $year;
    
    /**
     * @return string
     */
    public function getMoshikomiId(){
        return  $this->moshikomiId;
    }
    
    /**
     * Setter for MoshikomiId
     * @param string $MoshikomiId
     */
    public function setMoshikomiId($moshikomiId){
        $this->moshikomiId = $moshikomiId;
    }

    /**
     *
     * @return int
     */
    public function getOrganizationId() {
        return $this->organizationId;
    }

    /**
     *
     * @param int $organizationId
     */
    public function setOrganizationId($organizationId) {
        $this->organizationId = $organizationId;
    }

    /**
     *
     * @return int
     */
    public function getEikenScheduleId() {
        return $this->eikenScheduleId;
    }

    /**
     *
     * @param int $eikenScheduleId
     */
    public function setEikenScheduleId($eikenScheduleId) {
        $this->eikenScheduleId = $eikenScheduleId;
    }

    /**
     *
     * @return string
     */
    public function getFirtNameKanji() {
        return $this->firtNameKanji;
    }

    /**
     *
     * @param string $firtNameKanji
     */
    public function setFirtNameKanji($firtNameKanji) {
        $this->firtNameKanji = $firtNameKanji;
    }

    /**
     *
     * @return string
     */
    public function getLastNameKanji() {
        return $this->lastNameKanji;
    }

    /**
     *
     * @param string $lastNameKanji
     */
    public function setLastNameKanji($lastNameKanji) {
        $this->lastNameKanji = $lastNameKanji;
    }

    /**
     *
     * @return string
     */
    public function getMailAddress() {
        return $this->mailAddress;
    }

    /**
     *
     * @param string $mailAddress
     */
    public function setMailAddress($mailAddress) {
        $this->mailAddress = $mailAddress;
    }

    /**
     *
     * @return string
     */
    public function getConfirmEmail() {
        return $this->confirmEmail;
    }

    /**
     *
     * @param string $confirmEmail
     */
    public function setConfirmEmail($confirmEmail) {
        $this->confirmEmail = $confirmEmail;
    }

    /**
     *
     * @return string
     */
    public function getOrganizationNo() {
        return $this->organizationNo;
    }

    /**
     *
     * @param string $organizationNo
     */
    public function setOrganizationNo($organizationNo) {
        $this->organizationNo = $organizationNo;
    }

    /**
     *
     * @return string
     */
    public function getOrgNameKanji() {
        return $this->orgNameKanji;
    }

    /**
     *
     * @param string $orgNameKanji
     */
    public function setOrgNameKanji($orgNameKanji) {
        $this->orgNameKanji = $orgNameKanji;
    }

    /**
     *
     * @return string
     */
    public function getTelNo() {
        return $this->telNo;
    }

    /**
     *
     * @param string $telNo
     */
    public function setTelNo($telNo) {
        $this->telNo = $telNo;
    }

    /**
     *
     * @return string
     */
    public function getFax() {
        return $this->fax;
    }

    /**
     *
     * @param string $fax
     */
    public function setFax($fax) {
        $this->fax = $fax;
    }

    /**
     *
     * @return string
     */
    public function getMailName1() {
        return $this->mailName1;
    }

    /**
     *
     * @param string $mailName1
     */
    public function setMailName1($mailName1) {
        $this->mailName1 = $mailName1;
    }

    /**
     *
     * @return string
     */
    public function getMailAddress1() {
        return $this->mailAddress1;
    }

    /**
     *
     * @param string $mailAddress1
     */
    public function setMailAddress1($mailAddress1) {
        $this->mailAddress1 = $mailAddress1;
    }

    /**
     *
     * @return string
     */
    public function getMailName2() {
        return $this->mailName2;
    }

    /**
     *
     * @param string $mailName2
     */
    public function setMailName2($mailName2) {
        $this->mailName2 = $mailName2;
    }

    /**
     *
     * @return string
     */
    public function getMailAddress2() {
        return $this->mailAddress2;
    }

    /**
     *
     * @param string $mailAddress2
     */
    public function setMailAddress2($mailAddress2) {
        $this->mailAddress2 = $mailAddress2;
    }

    /**
     *
     * @return string
     */
    public function getMailName3() {
        return $this->mailName3;
    }

    /**
     *
     * @param string $mailName3
     */
    public function setMailName3($mailName3) {
        $this->mailName3 = $mailName3;
    }

    /**
     *
     * @return string
     */
    public function getMailAddress3() {
        return $this->mailAddress3;
    }

    /**
     *
     * @param string $mailAddress3
     */
    public function setMailAddress3($mailAddress3) {
        $this->mailAddress3 = $mailAddress3;
    }

    /**
     *
     * @return mixed
     */
    public function getTestDate() {
        return $this->testDate;
    }

    /**
     *
     * @param mixed $testDate
     */
    public function setTestDate($testDate) {
        $this->testDate = $testDate;
    }

    /**
     *
     * @return mixed
     */
    public function getPurpose() {
        return $this->purpose;
    }

    /**
     *
     * @param mixed $purpose
     */
    public function setPurpose($purpose) {
        $this->purpose = $purpose;
    }

    /**
     *
     * @return mixed
     */
    public function getPurposeOther() {
        return $this->purposeOther;
    }

    /**
     *
     * @param mixed $purposeOther
     */
    public function setPurposeOther($purposeOther) {
        $this->purposeOther = $purposeOther;
    }

    /**
     *
     * @return string
     */
    public function getPrefectureCode() {
        return $this->prefectureCode;
    }

    /**
     *
     * @param string $prefectureCode
     */
    public function setPrefectureCode($prefectureCode) {
        $this->prefectureCode = $prefectureCode;
    }

    /**
     *
     * @return string
     */
    public function getAddress1() {
        return $this->address1;
    }

    /**
     *
     * @param string $address1
     */
    public function setAddress1($address1) {
        $this->address1 = $address1;
    }

    /**
     *
     * @return string
     */
    public function getAddress2() {
        return $this->address2;
    }

    /**
     *
     * @param string $address2
     */
    public function setAddress2($address2) {
        $this->address2 = $address2;
    }

    /**
     *
     * @return string
     */
    public function getPICName() {
        return $this->pICName;
    }

    /**
     *
     * @param string $pICName
     */
    public function setPICName($pICName) {
        $this->pICName = $pICName;
    }

    /**
     *
     * @return mixed
     */
    public function getZipCode1() {
        return $this->zipCode1;
    }

    /**
     *
     * @param mixed $ZipCode1
     */
    public function setZipCode1($zipCode1) {
        $this->zipCode1 = $zipCode1;
    }

    /**
     *
     * @return mixed
     */
    public function getZipCode2() {
        return $this->zipCode2;
    }

    /**
     *
     * @param mixed $ZipCode2
     */
    public function setZipCode2($zipCode2) {
        $this->zipCode2 = $zipCode2;
    }

    /**
     *
     * @return mixed
     */
    public function getNumberPeopleA() {
        return $this->numberPeopleA;
    }

    /**
     *
     * @param mixed $numberPeopleA
     */
    public function setNumberPeopleA($numberPeopleA) {
        $this->numberPeopleA = $numberPeopleA;
    }

    /**
     *
     * @return mixed
     */
    public function getNumberPeopleB() {
        return $this->numberPeopleB;
    }

    /**
     *
     * @param mixed $numberPeopleB
     */
    public function setNumberPeopleB($numberPeopleB) {
        $this->numberPeopleB = $numberPeopleB;
    }

    /**
     *
     * @return mixed
     */
    public function getNumberPeopleC() {
        return $this->numberPeopleC;
    }

    /**
     *
     * @param mixed $numberPeopleC
     */
    public function setNumberPeopleC($numberPeopleC) {
        $this->numberPeopleC = $numberPeopleC;
    }

    /**
     *
     * @return mixed
     */
    public function getNumberPeopleD() {
        return $this->numberPeopleD;
    }

    /**
     *
     * @param mixed $numberPeopleD
     */
    public function setNumberPeopleD($numberPeopleD) {
        $this->numberPeopleD = $numberPeopleD;
    }

    /**
     *
     * @return mixed
     */
    public function getNumberPeopleE() {
        return $this->numberPeopleE;
    }

    /**
     *
     * @param mixed $numberPeopleE
     */
    public function setNumberPeopleE($numberPeopleE) {
        $this->numberPeopleE = $numberPeopleE;
    }

    /**
     *
     * @return mixed
     */
    public function getTotalPeople() {
        return $this->totalPeople;
    }

    /**
     *
     * @param mixed $totalPeople
     */
    public function setTotalPeople($totalPeople) {
        $this->totalPeople = $totalPeople;
    }

    /**
     *
     * @return mixed
     */
    public function getNumberCDA() {
        return $this->numberCDA;
    }

    /**
     *
     * @param mixed $numberCDA
     */
    public function setNumberCDA($numberCDA) {
        $this->numberCDA = $numberCDA;
    }

    /**
     *
     * @return mixed
     */
    public function getNumberCDB() {
        return $this->numberCDB;
    }

    /**
     *
     * @param mixed $numberCDB
     */
    public function setNumberCDB($numberCDB) {
        $this->numberCDB = $numberCDB;
    }

    /**
     *
     * @return mixed
     */
    public function getNumberCDC() {
        return $this->numberCDC;
    }

    /**
     *
     * @param mixed $numberCDC
     */
    public function setNumberCDC($numberCDC) {
        $this->numberCDC = $numberCDC;
    }

    /**
     *
     * @return mixed
     */
    public function getNumberCDD() {
        return $this->numberCDD;
    }

    /**
     *
     * @param mixed $numberCDD
     */
    public function setNumberCDD($numberCDD) {
        $this->numberCDD = $numberCDD;
    }

    /**
     *
     * @return mixed
     */
    public function getNumberCDE() {
        return $this->numberCDE;
    }

    /**
     *
     * @param mixed $numberCDE
     */
    public function setNumberCDE($numberCDE) {
        $this->numberCDE = $numberCDE;
    }

    /**
     *
     * @return mixed
     */
    public function getTotalCD() {
        return $this->totalCD;
    }

    /**
     *
     * @param mixed $totalCD
     */
    public function setTotalCD($totalCD) {
        $this->totalCD = $totalCD;
    }

    /**
     *
     * @return string
     */
    public function getQuestion1() {
        return $this->question1;
    }

    /**
     *
     * @param string $question1
     */
    public function setQuestion1($question1) {
        $this->question1 = $question1;
    }

    /**
     *
     * @return mixed
     */
    public function getOptionApply() {
        return $this->optionApply;
    }

    /**
     *
     * @param mixed $optionApply
     */
    public function setOptionApply($optionApply) {
        $this->optionApply = $optionApply;
    }

    /**
     *
     * @return mixed
     */
    public function getOptionMenu() {
        return $this->optionMenu;
    }

    /**
     *
     * @param mixed $optionMenu
     */
    public function setOptionMenu($optionMenu) {
        $this->optionMenu = $optionMenu;
    }

    /**
     *
     * @return mixed
     */
    public function getQuestionNo() {
        return $this->questionNo;
    }

    /**
     *
     * @param mixed $questionNo
     */
    public function setQuestionNo($questionNo) {
        $this->questionNo = $questionNo;
    }

    /**
     *
     * @return mixed
     */
    public function getRankNo() {
        return $this->rankNo;
    }

    /**
     *
     * @param mixed $rankNo
     */
    public function setRankNo($rankNo) {
        $this->rankNo = $rankNo;
    }

    /**
     *
     * @return string
     */
    public function getQuestion2() {
        return $this->question2;
    }

    /**
     *
     * @param string $question2
     */
    public function setQuestion2($question2) {
        $this->question2 = $question2;
    }

    /**
     *
     * @return mixed
     */
    public function getOrganization() {
        return $this->organization;
    }

    /**
     *
     * @param mixed $organization
     */
    public function setOrganization($organization) {
        $this->organization = $organization;
    }

    /**
     *
     * @return mixed
     */
    public function getEikenSchedule() {
        return $this->eikenSchedule;
    }

    /**
     *
     * @param mixed $eikenSchedule
     */
    public function setEikenSchedule($eikenSchedule) {
        $this->eikenSchedule = $eikenSchedule;
    }

    /**
     *
     * @return boolean
     */
    public function getStatusMapping() {
        return $this->statusMapping;
    }

    /**
     *
     * @param boolean $statusMapping
     */
    public function setStatusMapping($statusMapping) {
        $this->statusMapping = $statusMapping;
    }

    /**
     *
     * @return boolean
     */
    public function getStatusImporting() {
        return $this->statusImporting;
    }

    /**
     *
     * @param boolean $statusImporting
     */
    public function setStatusImporting($statusImporting) {
        $this->statusImporting = $statusImporting;
    }

    /**
     *
     * @return int
     */
    public function getTotalImport() {
        return $this->totalImport;
    }

    /**
     *
     * @param int $totalImport
     */
    public function setTotalImport($totalImport) {
        $this->totalImport = $totalImport;
    }
    
    /**
     *
     * @return datetime
     */
    public function getRegistrationDate() {
        return $this->registrationDate;
    }

    /**
     *
     * @param datetime $registrationDate
     */
    public function setRegistrationDate($registrationDate) {
        $this->registrationDate = $registrationDate;
    }
    
    function getIsValid() {
        return $this->isValid;
    }

    function setIsValid($isValid) {
        $this->isValid = $isValid;
    }

    function toArray($format = 'Y/m/d'){
        $properties = get_object_vars($this);
        $return =  array();
        foreach ($properties as $key => $pr){
            if($pr instanceof \DateTime){
                $return[$key] = $pr->format($format);
            }
            if(is_object($pr)){
                continue;
            }
            $return[$key] = $pr;
        }
        return $return;
    }
    
    /**
     * @return int
     */
    public function getStatusAutoImport()
    {
        return $this->statusAutoImport;
    }

    /**
     * @param int $statusAutoImport
     */
    public function setStatusAutoImport($statusAutoImport)
    {
        $this->statusAutoImport = $statusAutoImport;
    }
    
    /**
     *
     * @return string
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     *
     * @param string $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getExamType()
    {
        return $this->examType;
    }

    /**
     * @param string $examType
     */
    public function setExamType($examType)
    {
        $this->examType = $examType;
    }

    /**
     * @return string
     */
    public function getJisshiId()
    {
        return $this->jisshiId;
    }

    /**
     * @param string $jisshiId
     */
    public function setJisshiId($jisshiId)
    {
        $this->jisshiId = $jisshiId;
    }

    /**
     * @return int
     */
    public function getHasNewData()
    {
        return $this->hasNewData;
    }

    /**
     * @param int $hasNewData
     */
    public function setHasNewData($hasNewData)
    {
        $this->hasNewData = $hasNewData;
    }

    /**
     * @return int
     */
    public function getFromUketuke()
    {
        return $this->fromUketuke;
    }

    /**
     * @param int $fromUketuke
     */
    public function setFromUketuke($fromUketuke)
    {
        $this->fromUketuke = $fromUketuke;
    }

    /**
     * @return int
     */
    public function getJisshiKanriNo()
    {
        return $this->jisshiKanriNo;
    }

    /**
     * @param string $jisshiKanriNo
     */
    public function setJisshiKanriNo($jisshiKanriNo)
    {
        $this->jisshiKanriNo = $jisshiKanriNo;
    }

    /**
     * @return string
     */
    public function getSetName()
    {
        return $this->setName;
    }

    /**
     * @param string $setName
     */
    public function setSetName($setName)
    {
        $this->setName = $setName;
    }

    /**
     * @return string
     */
    public function getGroupNo()
    {
        return $this->groupNo;
    }

    /**
     * @param string $groupNo
     */
    public function setGroupNo($groupNo)
    {
        $this->groupNo = $groupNo;
    }


    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }
}
