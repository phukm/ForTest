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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\IBATestResultRepository")
 * @ORM\Table(name="IBATestResult", uniqueConstraints={@ORM\UniqueConstraint(name="uk_acquisionNo_jisshiId_examType_eikenLevelId", columns={"AcquisitionNo", "JisshiId", "ExamType", "EikenLevelId", "IsDelete"})})
 */
class IBATestResult extends Common {
    /* Foreing key */

    /**
     * Foreing key reference to Pupil
     * @ORM\Column(type="integer", name="PupilId", nullable=true)
     *
     * @var integer
     */
    protected $pupilId;

    /**
     * Foreing key reference to EikenLevel
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;

    /**
     * Foreing key reference to EikenSchedule
     * @ORM\Column(type="integer", name="EikenScheduleId", nullable=true)
     *
     * @var integer
     */
    protected $eikenScheduleId;

    /**
     * @ORM\Column(type="integer", name="IBACSERead", nullable=true)
     */
    protected $read;

    /**
     * @ORM\Column(type="integer", name="IBACSEListen", nullable=true)
     */
    protected $listen;

    /**
     * @ORM\Column(type="integer", name="IBACSETotal", nullable=true)
     */
    protected $total;

    /**
     * @ORM\Column(type="datetime", name="ExamDate", nullable=true)
     */
    protected $examDate;

    /* Relationship */

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
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="EikenLevelId", referencedColumnName="id")
     */
    protected $eikenLevel;

    /**
     * @var integer
     *
     * @ORM\Column(name="ExecuteId", type="integer", nullable=true)
     */
    protected $executeId;

    /**
     * @var integer
     *
     * @ORM\Column(name="FixationSEQ", type="integer", nullable=true)
     */
    protected $fixationSEQ;

    /**
     * @var integer
     *
     * @ORM\Column(name="Year", type="integer", nullable=true)
     */
    protected $year;

    /**
     * @var integer
     *
     * @ORM\Column(name="ExecuteManagerNo", type="string", length=20, nullable=true)
     */
    protected $executeManagerNo;

    /**
     * @var string
     *
     * @ORM\Column(name="OrganizationNo", type="string", nullable=true)
     */
    protected $organizationNo;

    /**
     * @var integer
     *
     * @ORM\Column(name="AcquisitionNo", type="string", nullable=true)
     */
    protected $acquisitionNo;

    /**
     * @var string
     *
     * @ORM\Column(name="UketsukeNo", type="string", length=20, nullable=true)
     */
    protected $uketsukeNo;

    /**
     * @var string
     *
     * @ORM\Column(name="TestType", type="string", length=20, nullable=true)
     */
    protected $testType;

    /**
     * @var string
     *
     * @ORM\Column(name="TestSetNo", type="string", nullable=true)
     */
    protected $testSetNo;

    /**
     * @var string
     *
     * @ORM\Column(name="ExistenceListening", type="string", length=50, nullable=true)
     */
    protected $existenceListening;

    /**
     * @var string
     *
     * @ORM\Column(name="IdAlphabet", type="string", length=50, nullable=true)
     */
    protected $idAlphabet;

    /**
     * @var string
     *
     * @ORM\Column(name="IdNumber", type="string", nullable=true)
     */
    protected $idNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="Gender", type="string", length=20, nullable=true)
     */
    protected $gender;

    /**
     * @var string
     *
     * @ORM\Column(name="NameRomanji", type="string", length=255, nullable=true)
     */
    protected $nameRomanji;

    /**
     * @var string
     *
     * @ORM\Column(name="NameKana", type="string", length=255, nullable=true)
     */
    protected $nameKana;

    /**
     * @var string
     *
     * @ORM\Column(name="IndividualAttibute", type="string", length=255, nullable=true)
     */
    protected $individualAttibute;

    /**
     * @var string
     *
     * @ORM\Column(name="NameKanji", type="string", length=255, nullable=true)
     */
    protected $nameKanji;

    /**
     * @var integer
     *
     * @ORM\Column(name="SchoolYear", type="integer", nullable=true)
     */
    protected $schoolYear;

    /**
     * @var string
     *
     * @ORM\Column(name="ClassCode", type="string", length=20, nullable=true)
     */
    protected $classCode;

    /**
     * @var string
     *
     * @ORM\Column(name="AttendanceNo", type="string", nullable=true)
     */
    protected $attendanceNo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="Birthday", type="datetime", nullable=true)
     */
    protected $birthday;
    /**
     *
     * @ORM\Column(name="TempBirthday", type="datetime", nullable=true)
     */
    protected $tempBirthday;
    /**
     * @var integer
     *
     * @ORM\Column(name="OldScoreTotal", type="integer", nullable=true)
     */
    protected $oldScoreTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="OldScoreReading", type="integer", nullable=true)
     */
    protected $oldScoreReading;

    /**
     * @var integer
     *
     * @ORM\Column(name="OldScoreListening", type="integer", nullable=true)
     */
    protected $oldScoreListening;

    /**
     * @var integer
     *
     * @ORM\Column(name="RankTotal", type="integer", nullable=true)
     */
    protected $rankTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="RankReading", type="integer", nullable=true)
     */
    protected $rankReading;

    /**
     * @var integer
     *
     * @ORM\Column(name="RankListening", type="integer", nullable=true)
     */
    protected $rankListening;

    /**
     * @var string
     *
     * @ORM\Column(name="ExamNumber", type="string", nullable=true)
     */
    protected $examNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="QuestionNumberGrammar", type="integer", nullable=true)
     */
    protected $questionNumberGrammar;

    /**
     * @var integer
     *
     * @ORM\Column(name="QuestionNumberStructure", type="integer", nullable=true)
     */
    protected $questionNumberStructure;

    /**
     * @var integer
     *
     * @ORM\Column(name="QuestionNumberReading", type="integer", nullable=true)
     */
    protected $questionNumberReading;

    /**
     * @var integer
     *
     * @ORM\Column(name="QuestionNumberListening", type="integer", nullable=true)
     */
    protected $questionNumberListening;

    /**
     * @var integer
     *
     * @ORM\Column(name="QuestionNumberTotal", type="integer", nullable=true)
     */
    protected $questionNumberTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="CorrectAnswerNumberGrammar", type="integer", nullable=true)
     */
    protected $correctAnswerNumberGrammar;

    /**
     * @var integer
     *
     * @ORM\Column(name="CorrectAnswerNumberStructure", type="integer", nullable=true)
     */
    protected $correctAnswerNumberStructure;

    /**
     * @var integer
     *
     * @ORM\Column(name="CorrectAnswerNumberReading", type="integer", nullable=true)
     */
    protected $correctAnswerNumberReading;

    /**
     * @var integer
     *
     * @ORM\Column(name="CorrectAnswerNumberListening", type="integer", nullable=true)
     */
    protected $correctAnswerNumberListening;

    /**
     * @var integer
     *
     * @ORM\Column(name="CorrectAnswerNumberTotal", type="integer", nullable=true)
     */
    protected $correctAnswerNumberTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="CorrectAnswerPercentGrammar", type="integer", nullable=true)
     */
    protected $correctAnswerPercentGrammar;

    /**
     * @var integer
     *
     * @ORM\Column(name="CorrectAnswerPercentStructure", type="integer", nullable=true)
     */
    protected $correctAnswerPercentStructure;

    /**
     * @var integer
     *
     * @ORM\Column(name="CorrectAnswerPercentReading", type="integer", nullable=true)
     */
    protected $correctAnswerPercentReading;

    /**
     * @var integer
     *
     * @ORM\Column(name="CorrectAnswerPercentListening", type="integer", nullable=true)
     */
    protected $correctAnswerPercentListening;

    /**
     * @var integer
     *
     * @ORM\Column(name="CorrectAnswerPercentTotal", type="integer", nullable=true)
     */
    protected $correctAnswerPercentTotal;

    /**
     * @var string
     *
     * @ORM\Column(name="EikenKyu", type="string", length=20, nullable=true)
     */
    protected $eikenKyu;

    /**
     * @var string
     *
     * @ORM\Column(name="Toeic", type="string", length=50, nullable=true)
     */
    protected $toeic;

    /**
     * @var string
     *
     * @ORM\Column(name="Toefl", type="string", length=50, nullable=true)
     */
    protected $toefl;

    /**
     * @var string
     *
     * @ORM\Column(name="ToeicBridge", type="string", length=50, nullable=true)
     */
    protected $toeicBridge;

    /**
     * @var integer
     *
     * @ORM\Column(name="AverageScoreTotal", type="integer", nullable=true)
     */
    protected $averageScoreTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="AverageScoreReading", type="integer", nullable=true)
     */
    protected $averageScoreReading;

    /**
     * @var integer
     *
     * @ORM\Column(name="AverageScoreListening", type="integer", nullable=true)
     */
    protected $averageScoreListening;

    /**
     * @var integer
     *
     * @ORM\Column(name="OldAverageScoreTotal", type="integer", nullable=true)
     */
    protected $oldAverageScoreTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="OldAverageScoreReading", type="integer", nullable=true)
     */
    protected $oldAverageScoreReading;

    /**
     * @var integer
     *
     * @ORM\Column(name="OldAverageScoreListening", type="integer", nullable=true)
     */
    protected $oldAverageScoreListening;

    /**
     * @var integer
     *
     * @ORM\Column(name="AvgCorrectPercentGrammar", type="integer", nullable=true)
     */
    protected $avgCorrectPercentGrammar;

    /**
     * @var integer
     *
     * @ORM\Column(name="AvgCorrectPercentStructure", type="integer", nullable=true)
     */
    protected $avgCorrectPercentStructure;

    /**
     * @var integer
     *
     * @ORM\Column(name="AvgCorrectPercentReading", type="integer", nullable=true)
     */
    protected $avgCorrectPercentReading;

    /**
     * @var integer
     *
     * @ORM\Column(name="AvgCorrectPercentListening", type="integer", nullable=true)
     */
    protected $avgCorrectPercentListening;

    /**
     * @var integer
     *
     * @ORM\Column(name="AvgCorrectPercentTotal", type="integer", nullable=true)
     */
    protected $avgCorrectPercentTotal;

    /**
     * @var string
     *
     * @ORM\Column(name="AnswerSerialize", type="text", nullable=true)
     */
    protected $answerSerialize;

    /**
     * @var string
     *
     * @ORM\Column(name="AccuraryJugdeSerialize", type="text", nullable=true)
     */
    protected $accuraryJugdeSerialize;

    /**
     * @var string
     *
     * @ORM\Column(name="EikenId", type="string", length=50, nullable=true)
     */
    protected $eikenId;

    /**
     * @var string
     *
     * @ORM\Column(name="Password", type="string", length=50, nullable=true)
     */
    protected $password;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ProcessDate", type="datetime", nullable=true)
     */
    protected $processDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="NewOldClassification", type="integer", nullable=true)
     */
    protected $newOldClassification;

    /**
     * @var integer
     *
     * @ORM\Column(name="TotalFlag", type="integer", nullable=true)
     */
    protected $totalFlag;

    /**
     * @var integer
     *
     * @ORM\Column(name="EikenLevelTotal", type="string", length=50, nullable=true)
     */
    protected $eikenLevelTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="EkenLevelRead", type="string", length=50, nullable=true)
     */
    protected $ekenLevelRead;

    /**
     * @var integer
     *
     * @ORM\Column(name="EikenLevelListening", type="string", length=50, nullable=true)
     */
    protected $eikenLevelListening;

    /**
     * @var string
     *
     * @ORM\Column(name="ResultDocOutput", type="string", length=255, nullable=true)
     */
    protected $resultDocOutput;

    /**
     * @var string
     *
     * @ORM\Column(name="EikenLevel", type="string", length=20, nullable=true)
     */
    protected $eikenLevelKyu;

    /**
     * @var string
     *
     * @ORM\Column(name="RankDisplay", type="string", length=50, nullable=true)
     */
    protected $rankDisplay;

    /**
     * @var string
     *
     * @ORM\Column(name="RankDisplayLimit", type="string", length=50, nullable=true)
     */
    protected $rankDisplayLimit;

    /**
     * @var string
     *
     * @ORM\Column(name="TitleUpdate", type="string", length=255, nullable=true)
     */
    protected $titleUpdate;

    /**
     * @var string
     *
     * @ORM\Column(name="Title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="EikenIdDisplay", type="string", length=50, nullable=true)
     */
    protected $eikenIdDisplay;

    /**
     *
     * @ORM\Column(name="IsPass", type="boolean", options={"default":0}, nullable=true)
     * @var boolean
     */
    protected $isPass;

    /**
     *
     * @ORM\Column(name="MappingStatus", type="smallint", options={"default":0}, nullable=true)
     * @var boolean
     */
    protected $mappingStatus;

    /**
     *
     * @ORM\Column(name="PupilNo", type="string", nullable=true)
     */
    protected $pupilNo;

    /**
     *
     * @ORM\Column(name="ClassName", type="string", nullable=true)
     */
    protected $className;

    /**
     *
     * @ORM\Column(name="SchoolYearName", type="string", nullable=true)
     */
    protected $schoolYearName;

    /**
     * @var integer
     * @ORM\Column(name="TempPupilId", type="integer", nullable=true)
     */
    protected $tempPupilId;

    /**
     * @var string
     * @ORM\Column(name="TempSchoolYearName", type="string", nullable=true)
     */
    protected $tempSchoolYearName;

    /**
     * @var string
     * @ORM\Column(name="TempClassName", type="string", nullable=true)
     */
    protected $tempClassName;

    /**
     * @var string
     * @ORM\Column(name="TempPupilNo", type="string", nullable=true)
     */
    protected $tempPupilNo;

    /**
     * @ORM\Column(type="integer", name="ClassId", nullable=true)
     *
     * @var integer
     */
    protected $classId;

    /**
     * @ORM\Column(type="datetime", name="CreateDate", length=100, nullable=true)
     *
     * @var datetime
     */
    protected $createDate = '';

    /**
     * @ORM\Column(type="string", name="MoshikomiId", length=100, nullable=true)
     *
     * @var string
     */
    protected $moshikomiId = '';
    
    /**
     * @ORM\Column(type="integer", name="EikenLevelTotalNo", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelTotalNo = 0;

    /**
     * @ORM\Column(type="string", name="JisshiId",nullable=true)
     *
     * @var string
     */
    protected $jisshiId;

    /**
     * @ORM\Column(type="string", name="ExamType",nullable=true)
     *
     * @var string
     */
    protected $examType;

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
     * @var boolean
     * @ORM\Column(name="IsMapped", type="boolean", options={"default":0}, nullable=true)
     */
    protected $isMapped;
    
    /**
     * @var string
     * @ORM\Column(name="TempNameKanji", type="string", nullable=true)
     */
    protected $tempNameKanji;

    /**
     * @return integer
     */
    public function getEikenLevelTotalNo(){
        return  $this->eikenLevelTotalNo;
    }
    
    /**
     * Setter for EikenLevelTotalNo
     * @param string $EikenLevelTotalNo
     */
    public function setEikenLevelTotalNo($eikenLevelTotalNo){
        $this->eikenLevelTotalNo = $eikenLevelTotalNo;
    }

    /**
     * @return string
     */
    public function getMoshikomiId() {
        return $this->moshikomiId;
    }

    /**
     * Setter for MoshikomiId
     * @param string $MoshikomiId
     */
    public function setMoshikomiId($moshikomiId) {
        $this->moshikomiId = $moshikomiId;
    }

    /**
     * @return datetime
     */
    public function getCreateDate() {
        return $this->createDate;
    }

    /**
     * Setter for CreateDate
     * @param string $CreateDate
     */
    public function setCreateDate($createDate) {
        $this->createDate = $createDate;
    }

    /**
     * @ORM\Column(type="datetime", name="UpdateDate", length=100, nullable=true)
     *
     * @var datetime
     */
    protected $updateDate = '';

    /**
     * @return datetime
     */
    public function getUpdateDate() {
        return $this->updateDate;
    }

    /**
     * Setter for UpdateDate
     * @param string $UpdateDate
     */
    public function setUpdateDate($updateDate) {
        $this->updateDate = $updateDate;
    }

    /**
     * @return integer
     */
    public function getClassId() {
        return $this->classId;
    }

    /**
     * Setter for ClassId
     * @param string $ClassId
     */
    public function setClassId($ClassId) {
        $this->classId = $ClassId;
    }

    /**
     * @ORM\Column(type="integer", name="OrgSchoolYearId", nullable=true)
     *
     * @var integer
     */
    protected $orgSchoolYearId;

    /**
     * @return integer
     */
    public function getOrgSchoolYearId() {
        return $this->orgSchoolYearId;
    }

    /**
     * Setter for OrgSchoolYearId
     * @param string $orgSchoolYearId
     */
    public function setOrgSchoolYearId($orgSchoolYearId) {
        $this->orgSchoolYearId = $orgSchoolYearId;
    }

    /**
     * @ORM\Column(type="integer", name="TempClassId", nullable=true)
     *
     * @var integer
     */
    protected $tempClassId;

    /**
     * @return integer
     */
    public function getTempClassId(){
        return  $this->tempClassId;
    }

    /**
     * Setter for TempClassId
     * @param string $tempClassId
     */
    public function setTempClassId($tempClassId) {
        $this->tempClassId = $tempClassId;
    }

    /**
     * @ORM\Column(type="integer", name="TempOrgSchoolYearId", nullable=true)
     *
     * @var integer
     */
    protected $tempOrgSchoolYearId;

    /**
     * @return integer
     */
    public function getTempOrgSchoolYearId() {
        return $this->tempOrgSchoolYearId;
    }

    /**
     * Setter for TempOrgSchoolYearId
     * @param string $tempOrgSchoolYearId
     */
    public function setTempOrgSchoolYearId($tempOrgSchoolYearId) {
        $this->tempOrgSchoolYearId = $tempOrgSchoolYearId;
    }

    /**
     * @var string
     * @ORM\Column(name="TempNameKana", type="string", nullable=true)
     */
    protected $tempNameKana;

    /**
     * @return string
     */
    public function getTempNameKana() {
        return $this->tempNameKana;
    }

    /**
     * Setter for TempNameKana
     * @param string $tempNameKana
     */
    public function setTempNameKana($tempNameKana) {
        $this->tempNameKana = $tempNameKana;
    }
    /**
     * @var string
     * @ORM\Column(name="PreTempNameKana", type="string", nullable=true)
     */
    protected $preTempNameKana;
    
    /**
     * @return string
     */
    public function getPreTempNameKana() {
        return $this->preTempNameKana;
    }
    
    /**
     * Setter for PreTempNameKana
     * @param string $preTempNameKana
     */
    public function setPreTempNameKana($preTempNameKana) {
        $this->preTempNameKana = $preTempNameKana;
    }
    
    
    

    /**
     * @return int
     */
    public function getTempPupilId() {
        return $this->tempPupilId;
    }

    /**
     * @param int $tempPupilId
     */
    public function setTempPupilId($tempPupilId) {
        $this->tempPupilId = $tempPupilId;
    }

    /**
     * @return string
     */
    public function getTempSchoolYearName() {
        return $this->tempSchoolYearName;
    }

    /**
     * @param string $tempSchoolYearName
     */
    public function setTempSchoolYearName($tempSchoolYearName) {
        $this->tempSchoolYearName = $tempSchoolYearName;
    }

    /**
     * @return string
     */
    public function getTempClassName() {
        return $this->tempClassName;
    }

    /**
     * @param string $tempClassName
     */
    public function setTempClassName($tempClassName) {
        $this->tempClassName = $tempClassName;
    }

    /**
     * @return string
     */
    public function getTempPupilNo() {
        return $this->tempPupilNo;
    }

    /**
     * @param string $tempPupilNo
     */
    public function setTempPupilNo($tempPupilNo) {
        $this->tempPupilNo = $tempPupilNo;
    }

    function getSchoolYearName() {
        return $this->schoolYearName;
    }

    function setSchoolYearName($schoolYearName) {
        $this->schoolYearName = $schoolYearName;
    }

    function getPupilNo() {
        return $this->pupilNo;
    }

    function setPupilNo($pupilNo) {
        $this->pupilNo = $pupilNo;
    }

    function getClassName() {
        return $this->className;
    }

    function setClassName($className) {
        $this->className = $className;
    }

    function getMappingStatus() {
        return $this->mappingStatus;
    }

    function setMappingStatus($mappingStatus) {
        $this->mappingStatus = $mappingStatus;
    }

    function getIsPass() {
        return $this->isPass;
    }

    function setIsPass($isPass) {
        $this->isPass = $isPass;
    }

    function getEikenLevelKyu() {
        return $this->eikenLevelKyu;
    }

    function setEikenLevelKyu($eikenLevelKyu) {
        $this->eikenLevelKyu = $eikenLevelKyu;
    }

    function getExecuteId() {
        return $this->executeId;
    }

    function getFixationSEQ() {
        return $this->fixationSEQ;
    }

    function getYear() {
        return $this->year;
    }

    function getExecuteManagerNo() {
        return $this->executeManagerNo;
    }

    function getOrganizationNo() {
        return $this->organizationNo;
    }

    function getAcquisitionNo() {
        return $this->acquisitionNo;
    }

    function getUketsukeNo() {
        return $this->uketsukeNo;
    }

    function getTestType() {
        return $this->testType;
    }

    function getTestSetNo() {
        return $this->testSetNo;
    }

    function getExistenceListening() {
        return $this->existenceListening;
    }

    function getIdAlphabet() {
        return $this->idAlphabet;
    }

    function getIdNumber() {
        return $this->idNumber;
    }

    function getGender() {
        return $this->gender;
    }

    function getNameRomanji() {
        return $this->nameRomanji;
    }

    function getNameKana() {
        return $this->nameKana;
    }

    function getIndividualAttibute() {
        return $this->individualAttibute;
    }

    function getNameKanji() {
        return $this->nameKanji;
    }

    function getSchoolYear() {
        return $this->schoolYear;
    }

    function getClassCode() {
        return $this->classCode;
    }

    function getAttendanceNo() {
        return $this->attendanceNo;
    }

    function getBirthday() {
        return $this->birthday;
    }

    function getOldScoreTotal() {
        return $this->oldScoreTotal;
    }

    function getOldScoreReading() {
        return $this->oldScoreReading;
    }

    function getOldScoreListening() {
        return $this->oldScoreListening;
    }

    function getRankTotal() {
        return $this->rankTotal;
    }

    function getRankReading() {
        return $this->rankReading;
    }

    function getRankListening() {
        return $this->rankListening;
    }

    function getExamNumber() {
        return $this->examNumber;
    }

    function getQuestionNumberGrammar() {
        return $this->questionNumberGrammar;
    }

    function getQuestionNumberStructure() {
        return $this->questionNumberStructure;
    }

    function getQuestionNumberReading() {
        return $this->questionNumberReading;
    }

    function getQuestionNumberListening() {
        return $this->questionNumberListening;
    }

    function getQuestionNumberTotal() {
        return $this->questionNumberTotal;
    }

    function getCorrectAnswerNumberGrammar() {
        return $this->correctAnswerNumberGrammar;
    }

    function getCorrectAnswerNumberStructure() {
        return $this->correctAnswerNumberStructure;
    }

    function getCorrectAnswerNumberReading() {
        return $this->correctAnswerNumberReading;
    }

    function getCorrectAnswerNumberListening() {
        return $this->correctAnswerNumberListening;
    }

    function getCorrectAnswerNumberTotal() {
        return $this->correctAnswerNumberTotal;
    }

    function getCorrectAnswerPercentGrammar() {
        return $this->correctAnswerPercentGrammar;
    }

    function getCorrectAnswerPercentStructure() {
        return $this->correctAnswerPercentStructure;
    }

    function getCorrectAnswerPercentReading() {
        return $this->correctAnswerPercentReading;
    }

    function getCorrectAnswerPercentListening() {
        return $this->correctAnswerPercentListening;
    }

    function getCorrectAnswerPercentTotal() {
        return $this->correctAnswerPercentTotal;
    }

    function getEikenKyu() {
        return $this->eikenKyu;
    }

    function getToeic() {
        return $this->toeic;
    }

    function getToefl() {
        return $this->toefl;
    }

    function getToeicBridge() {
        return $this->toeicBridge;
    }

    function getAverageScoreTotal() {
        return $this->averageScoreTotal;
    }

    function getAverageScoreReading() {
        return $this->averageScoreReading;
    }

    function getAverageScoreListening() {
        return $this->averageScoreListening;
    }

    function getOldAverageScoreTotal() {
        return $this->oldAverageScoreTotal;
    }

    function getOldAverageScoreReading() {
        return $this->oldAverageScoreReading;
    }

    function getOldAverageScoreListening() {
        return $this->oldAverageScoreListening;
    }

    function getAvgCorrectPercentGrammar() {
        return $this->avgCorrectPercentGrammar;
    }

    function getAvgCorrectPercentStructure() {
        return $this->avgCorrectPercentStructure;
    }

    function getAvgCorrectPercentReading() {
        return $this->avgCorrectPercentReading;
    }

    function getAvgCorrectPercentListening() {
        return $this->avgCorrectPercentListening;
    }

    function getAvgCorrectPercentTotal() {
        return $this->avgCorrectPercentTotal;
    }

    function getAnswerSerialize() {
        return $this->answerSerialize;
    }

    function getAccuraryJugdeSerialize() {
        return $this->accuraryJugdeSerialize;
    }

    function getEikenId() {
        return $this->eikenId;
    }

    function getPassword() {
        return $this->password;
    }

    function getProcessDate() {
        return $this->processDate;
    }

    function getNewOldClassification() {
        return $this->newOldClassification;
    }

    function getTotalFlag() {
        return $this->totalFlag;
    }

    function getEikenLevelTotal() {
        return $this->eikenLevelTotal;
    }

    function getEkenLevelRead() {
        return $this->ekenLevelRead;
    }

    function getEikenLevelListening() {
        return $this->eikenLevelListening;
    }

    function getResultDocOutput() {
        return $this->resultDocOutput;
    }

    function getRankDisplay() {
        return $this->rankDisplay;
    }

    function getRankDisplayLimit() {
        return $this->rankDisplayLimit;
    }

    function getTitleUpdate() {
        return $this->titleUpdate;
    }

    function getTitle() {
        return $this->title;
    }

    function getEikenIdDisplay() {
        return $this->eikenIdDisplay;
    }

    function setExecuteId($executeId) {
        $this->executeId = $executeId;
    }

    function setFixationSEQ($fixationSEQ) {
        $this->fixationSEQ = $fixationSEQ;
    }

    function setYear($year) {
        $this->year = $year;
    }

    function setExecuteManagerNo($executeManagerNo) {
        $this->executeManagerNo = $executeManagerNo;
    }

    function setOrganizationNo($organizationNo) {
        $this->organizationNo = $organizationNo;
    }

    function setAcquisitionNo($acquisitionNo) {
        $this->acquisitionNo = $acquisitionNo;
    }

    function setUketsukeNo($uketsukeNo) {
        $this->uketsukeNo = $uketsukeNo;
    }

    function setTestType($testType) {
        $this->testType = $testType;
    }

    function setTestSetNo($testSetNo) {
        $this->testSetNo = $testSetNo;
    }

    function setExistenceListening($existenceListening) {
        $this->existenceListening = $existenceListening;
    }

    function setIdAlphabet($idAlphabet) {
        $this->idAlphabet = $idAlphabet;
    }

    function setIdNumber($idNumber) {
        $this->idNumber = $idNumber;
    }

    function setGender($gender) {
        $this->gender = $gender;
    }

    function setNameRomanji($nameRomanji) {
        $this->nameRomanji = $nameRomanji;
    }

    function setNameKana($nameKana) {
        $this->nameKana = $nameKana;
    }

    function setIndividualAttibute($individualAttibute) {
        $this->individualAttibute = $individualAttibute;
    }

    function setNameKanji($nameKanji) {
        $this->nameKanji = $nameKanji;
    }

    function setSchoolYear($schoolYear) {
        $this->schoolYear = $schoolYear;
    }

    function setClassCode($classCode) {
        $this->classCode = $classCode;
    }

    function setAttendanceNo($attendanceNo) {
        $this->attendanceNo = $attendanceNo;
    }

    function setBirthday($birthday) {
        $this->birthday = $birthday;
    }

    function setOldScoreTotal($oldScoreTotal) {
        $this->oldScoreTotal = $oldScoreTotal;
    }

    function setOldScoreReading($oldScoreReading) {
        $this->oldScoreReading = $oldScoreReading;
    }

    function setOldScoreListening($oldScoreListening) {
        $this->oldScoreListening = $oldScoreListening;
    }

    function setRankTotal($rankTotal) {
        $this->rankTotal = $rankTotal;
    }

    function setRankReading($rankReading) {
        $this->rankReading = $rankReading;
    }

    function setRankListening($rankListening) {
        $this->rankListening = $rankListening;
    }

    function setExamNumber($examNumber) {
        $this->examNumber = $examNumber;
    }

    function setQuestionNumberGrammar($questionNumberGrammar) {
        $this->questionNumberGrammar = $questionNumberGrammar;
    }

    function setQuestionNumberStructure($questionNumberStructure) {
        $this->questionNumberStructure = $questionNumberStructure;
    }

    function setQuestionNumberReading($questionNumberReading) {
        $this->questionNumberReading = $questionNumberReading;
    }

    function setQuestionNumberListening($questionNumberListening) {
        $this->questionNumberListening = $questionNumberListening;
    }

    function setQuestionNumberTotal($questionNumberTotal) {
        $this->questionNumberTotal = $questionNumberTotal;
    }

    function setCorrectAnswerNumberGrammar($correctAnswerNumberGrammar) {
        $this->correctAnswerNumberGrammar = $correctAnswerNumberGrammar;
    }

    function setCorrectAnswerNumberStructure($correctAnswerNumberStructure) {
        $this->correctAnswerNumberStructure = $correctAnswerNumberStructure;
    }

    function setCorrectAnswerNumberReading($correctAnswerNumberReading) {
        $this->correctAnswerNumberReading = $correctAnswerNumberReading;
    }

    function setCorrectAnswerNumberListening($correctAnswerNumberListening) {
        $this->correctAnswerNumberListening = $correctAnswerNumberListening;
    }

    function setCorrectAnswerNumberTotal($correctAnswerNumberTotal) {
        $this->correctAnswerNumberTotal = $correctAnswerNumberTotal;
    }

    function setCorrectAnswerPercentGrammar($correctAnswerPercentGrammar) {
        $this->correctAnswerPercentGrammar = $correctAnswerPercentGrammar;
    }

    function setCorrectAnswerPercentStructure($correctAnswerPercentStructure) {
        $this->correctAnswerPercentStructure = $correctAnswerPercentStructure;
    }

    function setCorrectAnswerPercentReading($correctAnswerPercentReading) {
        $this->correctAnswerPercentReading = $correctAnswerPercentReading;
    }

    function setCorrectAnswerPercentListening($correctAnswerPercentListening) {
        $this->correctAnswerPercentListening = $correctAnswerPercentListening;
    }

    function setCorrectAnswerPercentTotal($correctAnswerPercentTotal) {
        $this->correctAnswerPercentTotal = $correctAnswerPercentTotal;
    }

    function setEikenKyu($eikenKyu) {
        $this->eikenKyu = $eikenKyu;
    }

    function setToeic($toeic) {
        $this->toeic = $toeic;
    }

    function setToefl($toefl) {
        $this->toefl = $toefl;
    }

    function setToeicBridge($toeicBridge) {
        $this->toeicBridge = $toeicBridge;
    }

    function setAverageScoreTotal($averageScoreTotal) {
        $this->averageScoreTotal = $averageScoreTotal;
    }

    function setAverageScoreReading($averageScoreReading) {
        $this->averageScoreReading = $averageScoreReading;
    }

    function setAverageScoreListening($averageScoreListening) {
        $this->averageScoreListening = $averageScoreListening;
    }

    function setOldAverageScoreTotal($oldAverageScoreTotal) {
        $this->oldAverageScoreTotal = $oldAverageScoreTotal;
    }

    function setOldAverageScoreReading($oldAverageScoreReading) {
        $this->oldAverageScoreReading = $oldAverageScoreReading;
    }

    function setOldAverageScoreListening($oldAverageScoreListening) {
        $this->oldAverageScoreListening = $oldAverageScoreListening;
    }

    function setAvgCorrectPercentGrammar($avgCorrectPercentGrammar) {
        $this->avgCorrectPercentGrammar = $avgCorrectPercentGrammar;
    }

    function setAvgCorrectPercentStructure($avgCorrectPercentStructure) {
        $this->avgCorrectPercentStructure = $avgCorrectPercentStructure;
    }

    function setAvgCorrectPercentReading($avgCorrectPercentReading) {
        $this->avgCorrectPercentReading = $avgCorrectPercentReading;
    }

    function setAvgCorrectPercentListening($avgCorrectPercentListening) {
        $this->avgCorrectPercentListening = $avgCorrectPercentListening;
    }

    function setAvgCorrectPercentTotal($avgCorrectPercentTotal) {
        $this->avgCorrectPercentTotal = $avgCorrectPercentTotal;
    }

    function setAnswerSerialize($answerSerialize) {
        $this->answerSerialize = $answerSerialize;
    }

    function setAccuraryJugdeSerialize($accuraryJugdeSerialize) {
        $this->accuraryJugdeSerialize = $accuraryJugdeSerialize;
    }

    function setEikenId($eikenId) {
        $this->eikenId = $eikenId;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setProcessDate($processDate) {
        $this->processDate = $processDate;
    }

    function setNewOldClassification($newOldClassification) {
        $this->newOldClassification = $newOldClassification;
    }

    function setTotalFlag($totalFlag) {
        $this->totalFlag = $totalFlag;
    }

    function setEikenLevelTotal($eikenLevelTotal) {
        $this->eikenLevelTotal = $eikenLevelTotal;
    }

    function setEkenLevelRead($ekenLevelRead) {
        $this->ekenLevelRead = $ekenLevelRead;
    }

    function setEikenLevelListening($eikenLevelListening) {
        $this->eikenLevelListening = $eikenLevelListening;
    }

    function setResultDocOutput($resultDocOutput) {
        $this->resultDocOutput = $resultDocOutput;
    }

    function setRankDisplay($rankDisplay) {
        $this->rankDisplay = $rankDisplay;
    }

    function setRankDisplayLimit($rankDisplayLimit) {
        $this->rankDisplayLimit = $rankDisplayLimit;
    }

    function setTitleUpdate($titleUpdate) {
        $this->titleUpdate = $titleUpdate;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function setEikenIdDisplay($eikenIdDisplay) {
        $this->eikenIdDisplay = $eikenIdDisplay;
    }

    /**
     * @return int
     */
    public function getPupilId() {
        return $this->pupilId;
    }

    /**
     * @param int $pupilId
     */
    public function setPupilId($pupilId) {
        $this->pupilId = $pupilId;
    }

    /**
     * @return int
     */
    public function getEikenLevelId() {
        return $this->eikenLevelId;
    }

    /**
     * @param int $eikenLevelId
     */
    public function setEikenLevelId($eikenLevelId) {
        $this->eikenLevelId = $eikenLevelId;
    }

    /**
     * @return int
     */
    public function getEikenScheduleId() {
        return $this->eikenScheduleId;
    }

    /**
     * @param int $eikenScheduleId
     */
    public function setEikenScheduleId($eikenScheduleId) {
        $this->eikenScheduleId = $eikenScheduleId;
    }

    /**
     * @return mixed
     */
    public function getRead() {
        return $this->read;
    }

    /**
     * @param mixed $read
     */
    public function setRead($read) {
        $this->read = $read;
    }

    /**
     * @return mixed
     */
    public function getListen() {
        return $this->listen;
    }

    /**
     * @param mixed $listen
     */
    public function setListen($listen) {
        $this->listen = $listen;
    }

    /**
     * @return mixed
     */
    public function getTotal() {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total) {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getExamDate() {
        return $this->examDate;
    }

    /**
     * @param mixed $examDate
     */
    public function setExamDate($examDate) {
        $this->examDate = $examDate;
    }

    /**
     * @return mixed
     */
    public function getContestDay() {
        return $this->contestDay;
    }

    /**
     * @param mixed $contestDay
     */
    public function setContestDay($contestDay) {
        $this->contestDay = $contestDay;
    }

    /**
     * @return mixed
     */
    public function getEikenSchedule() {
        return $this->eikenSchedule;
    }

    /**
     * @param mixed $eikenSchedule
     */
    public function setEikenSchedule($eikenSchedule) {
        $this->eikenSchedule = $eikenSchedule;
    }

    /**
     * @return mixed
     */
    public function getPupil() {
        return $this->pupil;
    }

    /**
     * @param mixed $pupil
     */
    public function setPupil($pupil) {
        $this->pupil = $pupil;
    }

    /**
     * @return \Application\Entity\EikenLevel
     */
    public function getEikenLevel() {
        return $this->eikenLevel;
    }

    /**
     * @param mixed $eikenLevel
     */
    public function setEikenLevel($eikenLevel) {
        $this->eikenLevel = $eikenLevel;
    }

    /**
     * @return mixed
     */
    public function getTempBirthday()
    {
        return $this->tempBirthday;
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
     * @param mixed $tempBirthday
     */
    public function setTempBirthday($tempBirthday)
    {
        $this->tempBirthday = $tempBirthday;
    }

    /**
     * @param mixed $groupNo
     */
    public function setGroupNo($groupNo)
    {
        $this->groupNo = $groupNo;
    }

    public function getGroupNo(){
        return $this->groupNo;
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
    
    function getIsMapped() {
        return $this->isMapped;
    }

    function setIsMapped($isMapped) {
        $this->isMapped = $isMapped;
    }
    
    function getTempNameKanji() {
        return $this->tempNameKanji;
    }

    function setTempNameKanji($tempNameKanji) {
        $this->tempNameKanji = $tempNameKanji;
    }


}
