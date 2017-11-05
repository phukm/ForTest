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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\EikenTestResultRepository")
 * @ORM\Table(name="EikenTestResult", uniqueConstraints={@ORM\UniqueConstraint(name="uk_eiken_test_result", columns={"EikenId", "EikenLevelId", "Kai", "Year", "IsDelete"})})
 */
class EikenTestResult extends Common
{

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
     *
     * @ORM\Column(type="smallint", name="EikenCSETotal", nullable=true)
     *
     * @var decimal
     */
    protected $eikenCSETotal;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="EikenSchedule")
     * @ORM\JoinColumn(name="EikenScheduleId", referencedColumnName="id")
     */
    protected $eikenSchedule;

    /**
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="EikenLevelId", referencedColumnName="id")
     */
    protected $eikenLevel;

    /**
     * @ORM\ManyToOne(targetEntity="Pupil")
     * @ORM\JoinColumn(name="PupilId", referencedColumnName="id")
     */
    protected $pupil;

    /**
     *
     * @ORM\Column(name="ResultFlag", type="string", length=2, nullable=true)
     */
    protected $resultFlag;

    /**
     *
     * @ORM\Column(name="Year", type="smallint", nullable=true)
     */
    protected $year;

    /**
     *
     * @ORM\Column(name="Kai", type="smallint", nullable=true)
     */
    protected $kai;

    /**
     *
     * @ORM\Column(name="EikenId", type="string", length=15, nullable=true)
     */
    protected $eikenId;

    /**
     *
     * @ORM\Column(name="UketsukeNo", type="string", length=7, nullable=true)
     */
    protected $uketsukeNo;

    /**
     *
     * @ORM\Column(name="HallClassification", type="smallint", nullable=true)
     */
    protected $hallClassification;

    /**
     *
     * @ORM\Column(name="ExecutionDayOfTheWeek", type="string", length=2, nullable=true)
     */
    protected $executionDayOfTheWeek;

    /**
     *
     * @ORM\Column(name="ExamineeNumber", type="string", length=7, nullable=true)
     */
    protected $examineeNumber;

    /**
     *
     * @ORM\Column(name="PupilName", type="string", length=255, nullable=true)
     */
    protected $pupilName;

    /**
     *
     * @ORM\Column(name="SchoolNumber", type="string", length=2, nullable=true)
     */
    protected $schoolNumber;

    /**
     *
     * @ORM\Column(name="SchoolYearCode", type="string", length=2, nullable=true)
     */
    protected $schoolYearCode;

    /**
     *
     * @ORM\Column(name="ClassCode", type="string", length=2, nullable=true)
     */
    protected $classCode;

    /**
     *
     * @ORM\Column(name="OneExemptionFlag", type="string", length=1, nullable=true)
     */
    protected $oneExemptionFlag;

    /**
     *
     * @ORM\Column(name="OrganizationNo", type="string", length=8, nullable=true)
     */
    protected $organizationNo;

    /**
     *
     * @ORM\Column(name="FisrtScore1", type="smallint", nullable=true)
     */
    protected $fisrtScore1;

    /**
     *
     * @ORM\Column(name="FisrtScore2", type="smallint", nullable=true)
     */
    protected $fisrtScore2;

    /**
     *
     * @ORM\Column(name="FisrtScore3", type="smallint", nullable=true)
     */
    protected $fisrtScore3;

    /**
     *
     * @ORM\Column(name="FisrtScore4", type="smallint", nullable=true)
     */
    protected $fisrtScore4;

    /**
     *
     * @ORM\Column(name="FisrtScore5", type="smallint", nullable=true)
     */
    protected $fisrtScore5;

    /**
     *
     * @ORM\Column(name="FisrtScore6", type="smallint", nullable=true)
     */
    protected $fisrtScore6;

    /**
     *
     * @ORM\Column(name="FisrtScore7", type="smallint", nullable=true)
     */
    protected $fisrtScore7;

    /**
     *
     * @ORM\Column(name="FisrtScore8", type="smallint", nullable=true)
     */
    protected $fisrtScore8;

    /**
     *
     * @ORM\Column(name="TotalPrimaryScore", type="smallint", nullable=true)
     */
    protected $totalPrimaryScore;

    /**
     *
     * @ORM\Column(name="PrimaryPassFailFlag", type="smallint", nullable=true)
     */
    protected $primaryPassFailFlag;

    /**
     *
     * @ORM\Column(name="PrimaryFailureLevel", type="string", length=2, nullable=true)
     */
    protected $primaryFailureLevel;

    /**
     *
     * @ORM\Column(name="SecondScore1", type="smallint", nullable=true)
     */
    protected $secondScore1;

    /**
     *
     * @ORM\Column(name="SecondScore2", type="smallint", nullable=true)
     */
    protected $secondScore2;

    /**
     *
     * @ORM\Column(name="SecondScore3", type="smallint", nullable=true)
     */
    protected $secondScore3;

    /**
     *
     * @ORM\Column(name="SecondScore4", type="smallint", nullable=true)
     */
    protected $secondScore4;

    /**
     *
     * @ORM\Column(name="SecondScore5", type="smallint", nullable=true)
     */
    protected $secondScore5;

    /**
     *
     * @ORM\Column(name="SecondScore6", type="smallint", nullable=true)
     */
    protected $secondScore6;

    /**
     *
     * @ORM\Column(name="SecondScore7", type="smallint", nullable=true)
     */
    protected $secondScore7;

    /**
     *
     * @ORM\Column(name="SecondScore8", type="smallint", nullable=true)
     */
    protected $secondScore8;

    /**
     *
     * @ORM\Column(name="TotalSecondScore", type="smallint", nullable=true)
     */
    protected $totalSecondScore;

    /**
     *
     * @ORM\Column(name="SecondPassFailFlag", type="smallint", nullable=true)
     */
    protected $secondPassFailFlag;

    /**
     *
     * @ORM\Column(name="SecondUnacceptableLevel", type="string", length=2, nullable=true)
     */
    protected $secondUnacceptableLevel;

    /**
     *
     * @ORM\Column(name="SecondExamHall", type="string", length=7, nullable=true)
     */
    protected $secondExamHall;

    /**
     *
     * @ORM\Column(name="SecondSetTimeHour", type="string", length=3, nullable=true)
     */
    protected $secondSetTimeHour;

    /**
     *
     * @ORM\Column(name="SecondSetTimeMinute", type="string", length=3, nullable=true)
     */
    protected $secondSetTimeMinute;

    /**
     *
     * @ORM\Column(name="FirstMailSendFlag", type="string", length=1, nullable=true)
     */
    protected $firstMailSendFlag;

    /**
     *
     * @ORM\Column(name="SecondMailSendFlag", type="string", length=1, nullable=true)
     */
    protected $secondMailSendFlag;

    /**
     *
     * @ORM\Column(name="InsertDate", type="datetime", nullable=true)
     */
    protected $insertDate;

    /**
     *
     * @ORM\Column(name="UpdateDate", type="datetime", nullable=true)
     */
    protected $updateDate;

    /**
     *
     *  @ORM\Column(name="IssueDate", type="string", length=63, nullable=true)
     */
    protected $issueDate;

    /**
     *
     * @ORM\Column(name="DeliveryClassification", type="string", length=1, nullable=true)
     */
    protected $deliveryClassification;

    /**
     *
     * @ORM\Column(name="SemiClassification", type="string", length=1, nullable=true)
     */
    protected $semiClassification;

    /**
     *
     * @ORM\Column(name="DomesticInternationalClassification", type="string", length=1, nullable=true)
     */
    protected $domesticInternationalClassification;

    /**
     *
     * @ORM\Column(name="ShippingClassification", type="string", length=1, nullable=true)
     */
    protected $shippingClassification;

    /**
     *
     * @ORM\Column(name="DeedClassification", type="string", length=1, nullable=true)
     */
    protected $deedClassification;

    /**
     *
     * @ORM\Column(name="DisplayClass", type="string", length=20, nullable=true)
     */
    protected $displayClass;

    /**
     *
     * @ORM\Column(name="ExamLocation", type="string", length=4, nullable=true)
     */
    protected $examLocation;

    /**
     *
     * @ORM\Column(name="NameKanji", type="string", length=255, nullable=true)
     */
    protected $nameKanji;

    /**
     *
     * @ORM\Column(name="NameRomanji", type="string", length=128, nullable=true)
     */
    protected $nameRomanji;

    /**
     *
     * @ORM\Column(name="NameRomanjiWithPrefix", type="string", length=255, nullable=true)
     */
    protected $nameRomanjiWithPrefix;

    /**
     *
     * @ORM\Column(name="NameKana", type="string", length=128, nullable=true)
     */
    protected $nameKana;
    /**
     *
     * @ORM\Column(name="TempNameKana", type="string", length=255, nullable=true)
     */
    protected $tempNameKana;
    /**
     *
     * @ORM\Column(name="ZipCode", type="string", length=8, nullable=true)
     */
    protected $zipCode;

    /**
     *
     * @ORM\Column(name="Address1", type="text", nullable=true)
     */
    protected $address1;

    /**
     *
     * @ORM\Column(name="Address2", type="text", nullable=true)
     */
    protected $address2;

    /**
     *
     * @ORM\Column(name="Address3", type="text", nullable=true)
     */
    protected $address3;

    /**
     *
     * @ORM\Column(name="Address4", type="text", nullable=true)
     */
    protected $address4;

    /**
     *
     * @ORM\Column(name="Address5", type="text", nullable=true)
     */
    protected $address5;

    /**
     *
     * @ORM\Column(name="UrgentNotification", type="text", nullable=true)
     */
    protected $urgentNotification;

    /**
     *
     * @ORM\Column(name="BatchNumber", type="string", length=8, nullable=true)
     */
    protected $batchNumber;

    /**
     *
     * @ORM\Column(name="SeriNumber", type="string", length=8, nullable=true)
     */
    protected $seriNumber;

    /**
     *
     * @ORM\Column(name="SchoolClassification", type="string", length=1, nullable=true)
     */
    protected $schoolClassification;

    /**
     *
     * @ORM\Column(name="ClassForDisplay", type="string", length=64, nullable=true)
     */
    protected $classForDisplay;

    /**
     *
     * @ORM\Column(name="Sex", type="smallint", nullable=true)
     */
    protected $sex;

    /**
     *
     * @ORM\Column(name="BarCodeStatus", type="string", length=1, nullable=true)
     */
    protected $barCodeStatus;

    /**
     *
     * @ORM\Column(name="Barcode", type="string", length=24, nullable=true)
     */
    protected $barcode;

    /**
     *
     * @ORM\Column(name="OrganizationName", type="string", length=255, nullable=true)
     */
    protected $organizationName;

    /**
     *
     * @ORM\Column(name="Password", type="string", length=8, nullable=true)
     */
    protected $password;

    /**
     *
     * @ORM\Column(name="Note1", type="text", nullable=true)
     */
    protected $note1;

    /**
     *
     * @ORM\Column(name="Note2", type="text", nullable=true)
     */
    protected $note2;

    /**
     *
     * @ORM\Column(name="Note3", type="text", nullable=true)
     */
    protected $note3;

    /**
     *
     * @ORM\Column(name="ExamResults", type="string", length=2, nullable=true)
     */
    protected $examResults;

    /**
     *
     * @ORM\Column(name="FirstExamResultsFlag", type="smallint", nullable=true)
     */
    protected $firstExamResultsFlag;

    /**
     *
     * @ORM\Column(name="FirstExamResultsFlagForDisplay", type="string", length=26, nullable=true)
     */
    protected $firstExamResultsFlagForDisplay;

    /**
     *
     * @ORM\Column(name="FirstExamResultsPerfectScore", type="smallint", nullable=true)
     */
    protected $firstExamResultsPerfectScore;

    /**
     *
     * @ORM\Column(name="FirstExamResultsPassPoint", type="smallint", nullable=true)
     */
    protected $firstExamResultsPassPoint;

    /**
     *
     * @ORM\Column(name="FirstExamResultsFailPoint", type="smallint", nullable=true)
     */
    protected $firstExamResultsFailPoint;

    /**
     *
     * @ORM\Column(name="FirstExamResultsAveragePass", type="smallint", nullable=true)
     */
    protected $firstExamResultsAveragePass;

    /**
     *
     * @ORM\Column(name="FirstExamResultsExamAverage", type="smallint", nullable=true)
     */
    protected $firstExamResultsExamAverage;

    /**
     *
     * @ORM\Column(name="FirstAdviceSentence1", type="text", nullable=true)
     */
    protected $firstAdviceSentence1;

    /**
     *
     * @ORM\Column(name="FirstAdviceSentence2", type="string", length=128, nullable=true)
     */
    protected $firstAdviceSentence2;

    /**
     *
     * @ORM\Column(name="FirstAdviceSentence3", type="string", length=128, nullable=true)
     */
    protected $firstAdviceSentence3;

    /**
     *
     * @ORM\Column(name="FirstAdviceSentence4", type="string", length=128, nullable=true)
     */
    protected $firstAdviceSentence4;

    /**
     *
     * @ORM\Column(name="FirstAdviceSentence5", type="string", length=128, nullable=true)
     */
    protected $firstAdviceSentence5;

    /**
     *
     * @ORM\Column(name="FirstAdviceSentence6", type="string", length=128, nullable=true)
     */
    protected $firstAdviceSentence6;

    /**
     *
     * @ORM\Column(name="CorrectAnswer", type="text", nullable=true)
     */
    protected $correctAnswer;

    /**
     *
     * @ORM\Column(name="Correction", type="string", length=128, nullable=true)
     */
    protected $correction;

    /**
     *
     * @ORM\Column(name="Explanation1", type="text", nullable=true)
     */
    protected $explanation1;

    /**
     *
     * @ORM\Column(name="Explanation2", type="text", nullable=true)
     */
    protected $explanation2;

    /**
     *
     * @ORM\Column(name="CrowdedFlag", type="smallint", nullable=true)
     */
    protected $crowdedFlag;

    /**
     *
     * @ORM\Column(name="CrowdedSentence", type="string", length=255, nullable=true)
     */
    protected $crowdedSentence;

    /**
     *
     * @ORM\Column(name="SecondHallClassification", type="string", length=1, nullable=true)
     */
    protected $secondHallClassification;

    /**
     *
     * @ORM\Column(name="HallNumber", type="string", length=8, nullable=true)
     */
    protected $hallNumber;

    /**
     *
     * @ORM\Column(name="HallName", type="string", length=128, nullable=true)
     */
    protected $hallName;

    /**
     *
     * @ORM\Column(name="SecondZipCode", type="string", length=8, nullable=true)
     */
    protected $secondZipCode;

    /**
     *
     * @ORM\Column(name="SecondAddress", type="text", nullable=true)
     */
    protected $secondAddress;

    /**
     *
     * @ORM\Column(name="TrafficRoute1", type="text", nullable=true)
     */
    protected $trafficRoute1;

    /**
     *
     * @ORM\Column(name="TrafficRoute2", type="text", nullable=true)
     */
    protected $trafficRoute2;

    /**
     *
     * @ORM\Column(name="TrafficRoute3", type="text", nullable=true)
     */
    protected $trafficRoute3;

    /**
     *
     * @ORM\Column(name="MapCode", type="string", length=22, nullable=true)
     */
    protected $mapCode;

    /**
     *
     * @ORM\Column(name="MeetingTime", type="string", length=6, nullable=true)
     */
    protected $meetingTime;

    /**
     *
     * @ORM\Column(name="MeetingTimeDisplay", type="string", length=128, nullable=true)
     */
    protected $meetingTimeDisplay;

    /**
     *
     * @ORM\Column(name="MeetingTimeColorFlag", type="string", length=1, nullable=true)
     */
    protected $meetingTimeColorFlag;

    /**
     *
     * @ORM\Column(name="PhotoAttachEsitence", type="string", length=1, nullable=true)
     */
    protected $photoAttachEsitence;

    /**
     *
     * @ORM\Column(name="SemiHallApplicationDisplay", type="string", length=22, nullable=true)
     */
    protected $semiHallApplicationDisplay;

    /**
     *
     * @ORM\Column(name="BaggageOutputClassification", type="string", length=1, nullable=true)
     */
    protected $baggageOutputClassification;

    /**
     *
     * @ORM\Column(name="Comment", type="text", nullable=true)
     */
    protected $comment;

    /**
     *
     * @ORM\Column(name="CommunicationField", type="text", nullable=true)
     */
    protected $communicationField;

    /**
     *
     * @ORM\Column(name="FirstFailureFourFiveClass", type="string", length=22, nullable=true)
     */
    protected $firstFailureFourFiveClass;

    /**
     *
     * @ORM\Column(name="VocabularyFieldScore", type="smallint", nullable=true)
     */
    protected $vocabularyFieldScore;

    /**
     *
     * @ORM\Column(name="VocabularyScore", type="smallint", nullable=true)
     */
    protected $vocabularyScore;

    /**
     *
     * @ORM\Column(name="VocabularyPercentCorrectAnswers", type="smallint", nullable=true)
     */
    protected $vocabularyPercentCorrectAnswers;

    /**
     *
     * @ORM\Column(name="VocabularyOverallAverage", type="smallint", nullable=true)
     */
    protected $vocabularyOverallAverage;

    /**
     *
     * @ORM\Column(name="VocabularyPassAverage", type="smallint", nullable=true)
     */
    protected $vocabularyPassAverage;

    /**
     *
     * @ORM\Column(name="ReadingFieldScore", type="smallint", nullable=true)
     */
    protected $readingFieldScore;

    /**
     *
     * @ORM\Column(name="ReadingScore", type="smallint", nullable=true)
     */
    protected $readingScore;

    /**
     *
     * @ORM\Column(name="ReadingPercentCorrectAnswers", type="smallint", nullable=true)
     */
    protected $readingPercentCorrectAnswers;

    /**
     *
     * @ORM\Column(name="ReadingOverallAverage", type="smallint", nullable=true)
     */
    protected $readingOverallAverage;

    /**
     *
     * @ORM\Column(name="ReadingPassAverage", type="smallint", nullable=true)
     */
    protected $readingPassAverage;

    /**
     *
     * @ORM\Column(name="ListeningFieldScore", type="smallint", nullable=true)
     */
    protected $listeningFieldScore;

    /**
     *
     * @ORM\Column(name="ListeningScore", type="smallint", nullable=true)
     */
    protected $listeningScore;

    /**
     *
     * @ORM\Column(name="ListeningPercentCorrectAnswers", type="smallint", nullable=true)
     */
    protected $listeningPercentCorrectAnswers;

    /**
     *
     * @ORM\Column(name="ListeningOverallAverage", type="smallint", nullable=true)
     */
    protected $listeningOverallAverage;

    /**
     *
     * @ORM\Column(name="ListeningPassAverage", type="smallint", nullable=true)
     */
    protected $listeningPassAverage;

    /**
     *
     * @ORM\Column(name="CompositionFieldScore", type="smallint", nullable=true)
     */
    protected $compositionFieldScore;

    /**
     *
     * @ORM\Column(name="CompositionScore", type="smallint", nullable=true)
     */
    protected $compositionScore;

    /**
     *
     * @ORM\Column(name="CompositionPercentCorrectAnswers", type="smallint", nullable=true)
     */
    protected $compositionPercentCorrectAnswers;

    /**
     *
     * @ORM\Column(name="CompositionOverallAverage", type="smallint", nullable=true)
     */
    protected $compositionOverallAverage;

    /**
     *
     * @ORM\Column(name="CompositionPassAverage", type="smallint", nullable=true)
     */
    protected $compositionPassAverage;

    /**
     *
     * @ORM\Column(name="ResultScoreAccordingField1", type="string", length=146, nullable=true)
     */
    protected $resultScoreAccordingField1;

    /**
     *
     * @ORM\Column(name="ResultScoreAccordingField2", type="string", length=146, nullable=true)
     */
    protected $resultScoreAccordingField2;

    /**
     *
     * @ORM\Column(name="ResultScoreAccordingField3", type="string", length=146, nullable=true)
     */
    protected $resultScoreAccordingField3;

    /**
     *
     * @ORM\Column(name="ResultScoreAccordingField4", type="string", length=146, nullable=true)
     */
    protected $resultScoreAccordingField4;

    /**
     *
     * @ORM\Column(name="ResultPerfectScoreAccordingField1", type="string", length=146, nullable=true)
     */
    protected $resultPerfectScoreAccordingField1;

    /**
     *
     * @ORM\Column(name="ResultPerfectScoreAccordingField2", type="string", length=146, nullable=true)
     */
    protected $resultPerfectScoreAccordingField2;

    /**
     *
     * @ORM\Column(name="ResultPerfectScoreAccordingField3", type="string", length=146, nullable=true)
     */
    protected $resultPerfectScoreAccordingField3;

    /**
     *
     * @ORM\Column(name="ResultPerfectScoreAccordingField4", type="string", length=146, nullable=true)
     */
    protected $resultPerfectScoreAccordingField4;

    /**
     *
     * @ORM\Column(name="LargeQuestionCorrectAnswer1", type="string", length=146, nullable=true)
     */
    protected $largeQuestionCorrectAnswer1;

    /**
     *
     * @ORM\Column(name="LargeQuestionCorrectAnswer2", type="string", length=146, nullable=true)
     */
    protected $largeQuestionCorrectAnswer2;

    /**
     *
     * @ORM\Column(name="LargeQuestionCorrectAnswer3", type="string", length=146, nullable=true)
     */
    protected $largeQuestionCorrectAnswer3;

    /**
     *
     * @ORM\Column(name="LargeQuestionCorrectAnswer4", type="string", length=146, nullable=true)
     */
    protected $largeQuestionCorrectAnswer4;

    /**
     *
     * @ORM\Column(name="LargeQuestionProblemResult1", type="string", length=146, nullable=true)
     */
    protected $largeQuestionProblemResult1;

    /**
     *
     * @ORM\Column(name="LargeQuestionProblemResult2", type="string", length=146, nullable=true)
     */
    protected $largeQuestionProblemResult2;

    /**
     *
     * @ORM\Column(name="LargeQuestionProblemResult3", type="string", length=146, nullable=true)
     */
    protected $largeQuestionProblemResult3;

    /**
     *
     * @ORM\Column(name="LargeQuestionProblemResult4", type="string", length=146, nullable=true)
     */
    protected $largeQuestionProblemResult4;

    /**
     *
     * @ORM\Column(name="StydyAdvice1", type="text", nullable=true)
     */
    protected $stydyAdvice1;

    /**
     *
     * @ORM\Column(name="StydyAdvice2", type="text", nullable=true)
     */
    protected $stydyAdvice2;

    /**
     *
     * @ORM\Column(name="StydyAdvice3", type="text", nullable=true)
     */
    protected $stydyAdvice3;

    /**
     *
     * @ORM\Column(name="StydyAdvice4", type="text", nullable=true)
     */
    protected $stydyAdvice4;

    /**
     *
     * @ORM\Column(name="NoticeCode1", type="string", length=22, nullable=true)
     */
    protected $noticeCode1;

    /**
     *
     * @ORM\Column(name="NoticeCode2", type="string", length=22, nullable=true)
     */
    protected $noticeCode2;

    /**
     *
     * @ORM\Column(name="StudyRealityGraph1", type="string", length=22, nullable=true)
     */
    protected $studyRealityGraph1;

    /**
     *
     * @ORM\Column(name="StudyRealityGraph2", type="string", length=22, nullable=true)
     */
    protected $studyRealityGraph2;

    /**
     *
     * @ORM\Column(name="FirstPassMerit1", type="text", nullable=true)
     */
    protected $firstPassMerit1;

    /**
     *
     * @ORM\Column(name="FirstPassMerit2", type="text", nullable=true)
     */
    protected $firstPassMerit2;

    /**
     *
     * @ORM\Column(name="FirstPassMerit3", type="text", nullable=true)
     */
    protected $firstPassMerit3;

    /**
     *
     * @ORM\Column(name="FirstPassMerit4", type="text", nullable=true)
     */
    protected $firstPassMerit4;

    /**
     *
     * @ORM\Column(name="FirstPassMerit5", type="text", nullable=true)
     */
    protected $firstPassMerit5;

    /**
     *
     * @ORM\Column(name="FirstPassMerit6", type="text", nullable=true)
     */
    protected $firstPassMerit6;

    /**
     *
     * @ORM\Column(name="FirstPassMerit7", type="text", nullable=true)
     */
    protected $firstPassMerit7;

    /**
     *
     * @ORM\Column(name="FirstPassMerit8", type="text", nullable=true)
     */
    protected $firstPassMerit8;

    /**
     *
     * @ORM\Column(name="FirstPassMerit9", type="text", nullable=true)
     */
    protected $firstPassMerit9;

    /**
     *
     * @ORM\Column(name="FirstPassMerit10", type="text", nullable=true)
     */
    protected $firstPassMerit10;

    /**
     *
     * @ORM\Column(name="FirstPassMerit11", type="text", nullable=true)
     */
    protected $firstPassMerit11;

    /**
     *
     * @ORM\Column(name="FirstPassMerit12", type="text", nullable=true)
     */
    protected $firstPassMerit12;

    /**
     *
     * @ORM\Column(name="FirstPassMerit13", type="text", nullable=true)
     */
    protected $firstPassMerit13;

    /**
     *
     * @ORM\Column(name="FirstPassMerit14", type="text", nullable=true)
     */
    protected $firstPassMerit14;

    /**
     *
     * @ORM\Column(name="FirstPassMerit15", type="text", nullable=true)
     */
    protected $firstPassMerit15;

    /**
     *
     * @ORM\Column(name="CanDoList1", type="string", length=22, nullable=true)
     */
    protected $canDoList1;

    /**
     *
     * @ORM\Column(name="CertificateNumber", type="string", length=8, nullable=true)
     */
    protected $certificateNumber;

    /**
     *
     *  @ORM\Column(name="CertificationDate", type="datetime", nullable=true)
     */
    protected $certificationDate;

    /**
     *
     * @ORM\Column(name="SortArea", type="string", length=52, nullable=true)
     */
    protected $sortArea;

    /**
     *
     * @ORM\Column(name="SelfOrganizationsDeliveryFlag", type="smallint", nullable=true)
     */
    protected $selfOrganizationsDeliveryFlag;

    /**
     *
     * @ORM\Column(name="SecondIssueYear", type="string", length=50, nullable=true)
     */
    protected $secondIssueYear;

    /**
     *
     * @ORM\Column(name="SecondDeliveryClassification", type="smallint", nullable=true)
     */
    protected $secondDeliveryClassification;

    /**
     *
     * @ORM\Column(name="SecondSemiClassification", type="smallint", nullable=true)
     */
    protected $secondSemiClassification;

    /**
     *
     * @ORM\Column(name="SecondExecutionDayOfTheWeek", type="smallint", nullable=true)
     */
    protected $secondExecutionDayOfTheWeek;

    /**
     *
     * @ORM\Column(name="SecondDomesticInternationalClassification", type="smallint", nullable=true)
     */
    protected $secondDomesticInternationalClassification;

    /**
     *
     * @ORM\Column(name="SecondShippingClassification", type="smallint", nullable=true)
     */
    protected $secondShippingClassification;

    /**
     *
     * @ORM\Column(name="SecondDeedExistenceClassification", type="smallint", nullable=true)
     */
    protected $secondDeedExistenceClassification;

    /**
     *
     * @ORM\Column(name="SecondExaminationAreas", type="string", length=6, nullable=true)
     */
    protected $secondExaminationAreas;

    /**
     *
     * @ORM\Column(name="SecondEmergencyNotice", type="text", nullable=true)
     */
    protected $secondEmergencyNotice;

    /**
     *
     * @ORM\Column(name="SecondBatchNumber", type="string", length=8, nullable=true)
     */
    protected $secondBatchNumber;

    /**
     *
     * @ORM\Column(name="SecondSeriNumber", type="string", length=7, nullable=true)
     */
    protected $secondSeriNumber;

    /**
     *
     * @ORM\Column(name="SecondBarCodeStatus", type="smallint", nullable=true)
     */
    protected $secondBarCodeStatus;

    /**
     *
     * @ORM\Column(name="SecondBarCode", type="string", length=25, nullable=true)
     */
    protected $secondBarCode;

    /**
     *
     * @ORM\Column(name="SecondNote1", type="text", nullable=true)
     */
    protected $secondNote1;

    /**
     *
     * @ORM\Column(name="SecondNote2", type="text", nullable=true)
     */
    protected $secondNote2;

    /**
     *
     * @ORM\Column(name="SecondNote3", type="text", nullable=true)
     */
    protected $secondNote3;

    /**
     *
     * @ORM\Column(name="SecondExamClassification", type="string", length=1, nullable=true)
     */
    protected $secondExamClassification;

    /**
     *
     * @ORM\Column(name="SecondExamResultsFlag", type="smallint", nullable=true)
     */
    protected $secondExamResultsFlag;

    /**
     *
     * @ORM\Column(name="SecondExamResultsFlagForDisplay", type="string", length=26, nullable=true)
     */
    protected $secondExamResultsFlagForDisplay;

    /**
     *
     * @ORM\Column(name="SecondExamResultsPerfectScore", type="smallint", nullable=true)
     */
    protected $secondExamResultsPerfectScore;

    /**
     *
     * @ORM\Column(name="SecondExamResultsPassPoint", type="smallint", nullable=true)
     */
    protected $secondExamResultsPassPoint;

    /**
     *
     * @ORM\Column(name="SecondtExamResultsFailPoint", type="smallint", nullable=true)
     */
    protected $secondtExamResultsFailPoint;

    /**
     *
     * @ORM\Column(name="SecondAdviceSentence1", type="text", nullable=true)
     */
    protected $secondAdviceSentence1;

    /**
     *
     * @ORM\Column(name="SecondAdviceSentence2", type="text", nullable=true)
     */
    protected $secondAdviceSentence2;

    /**
     *
     * @ORM\Column(name="SecondAdviceSentence3", type="text", nullable=true)
     */
    protected $secondAdviceSentence3;

    /**
     *
     * @ORM\Column(name="SecondAdviceSentence4", type="text", nullable=true)
     */
    protected $secondAdviceSentence4;

    /**
     *
     * @ORM\Column(name="SecondAdviceSentence5", type="text", nullable=true)
     */
    protected $secondAdviceSentence5;

    /**
     *
     * @ORM\Column(name="SecondAdviceSentence6", type="text", nullable=true)
     */
    protected $secondAdviceSentence6;

    /**
     *
     * @ORM\Column(name="ScoreAccordingField1", type="string", length=26, nullable=true)
     */
    protected $scoreAccordingField1;

    /**
     *
     * @ORM\Column(name="ScoreAccordingField2", type="string", length=26, nullable=true)
     */
    protected $scoreAccordingField2;

    /**
     *
     * @ORM\Column(name="ScoreAccordingField3", type="string", length=26, nullable=true)
     */
    protected $scoreAccordingField3;

    /**
     *
     * @ORM\Column(name="ScoreAccordingField4", type="string", length=26, nullable=true)
     */
    protected $scoreAccordingField4;

    /**
     *
     * @ORM\Column(name="ScoreAccordingField5", type="string", length=26, nullable=true)
     */
    protected $scoreAccordingField5;

    /**
     *
     * @ORM\Column(name="ScoringAccordingField1", type="string", length=26, nullable=true)
     */
    protected $scoringAccordingField1;

    /**
     *
     * @ORM\Column(name="ScoringAccordingField2", type="string", length=26, nullable=true)
     */
    protected $scoringAccordingField2;

    /**
     *
     * @ORM\Column(name="ScoringAccordingField3", type="string", length=26, nullable=true)
     */
    protected $scoringAccordingField3;

    /**
     *
     * @ORM\Column(name="ScoringAccordingField4", type="string", length=26, nullable=true)
     */
    protected $scoringAccordingField4;

    /**
     *
     * @ORM\Column(name="ScoringAccordingField5", type="string", length=26, nullable=true)
     */
    protected $scoringAccordingField5;

    /**
     *
     * @ORM\Column(name="SecondPassMerit1", type="text", nullable=true)
     */
    protected $secondPassMerit1;

    /**
     *
     * @ORM\Column(name="SecondPassMerit2", type="text", nullable=true)
     */
    protected $secondPassMerit2;

    /**
     *
     * @ORM\Column(name="SecondPassMerit3", type="text", nullable=true)
     */
    protected $secondPassMerit3;

    /**
     *
     * @ORM\Column(name="SecondPassMerit4", type="text", nullable=true)
     */
    protected $secondPassMerit4;

    /**
     *
     * @ORM\Column(name="SecondPassMerit5", type="text", nullable=true)
     */
    protected $secondPassMerit5;

    /**
     *
     * @ORM\Column(name="SecondPassMerit6", type="text", nullable=true)
     */
    protected $secondPassMerit6;

    /**
     *
     * @ORM\Column(name="SecondPassMerit7", type="text", nullable=true)
     */
    protected $secondPassMerit7;

    /**
     *
     * @ORM\Column(name="SecondPassMerit8", type="text", nullable=true)
     */
    protected $secondPassMerit8;

    /**
     *
     * @ORM\Column(name="SecondPassMerit9", type="text", nullable=true)
     */
    protected $secondPassMerit9;

    /**
     *
     * @ORM\Column(name="SecondPassMerit10", type="text", nullable=true)
     */
    protected $secondPassMerit10;

    /**
     *
     * @ORM\Column(name="SecondPassMerit11", type="text", nullable=true)
     */
    protected $secondPassMerit11;

    /**
     *
     * @ORM\Column(name="SecondPassMerit12", type="text", nullable=true)
     */
    protected $secondPassMerit12;

    /**
     *
     * @ORM\Column(name="SecondPassMerit13", type="text", nullable=true)
     */
    protected $secondPassMerit13;

    /**
     *
     * @ORM\Column(name="SecondPassMerit14", type="text", nullable=true)
     */
    protected $secondPassMerit14;

    /**
     *
     * @ORM\Column(name="SecondPassMerit15", type="text", nullable=true)
     */
    protected $secondPassMerit15;

    /**
     *
     * @ORM\Column(name="CanDoList2", type="string", length=22, nullable=true)
     */
    protected $canDoList2;

    /**
     *
     * @ORM\Column(name="Notice", type="string", length=22, nullable=true)
     */
    protected $notice;

    /**
     *
     * @ORM\Column(name="SecondCertificateNumber", type="string", length=7, nullable=true)
     */
    protected $secondCertificateNumber;

    /**
     *
     * @ORM\Column(name="SecondCertificationDate", type="datetime", nullable=true)
     */
    protected $secondCertificationDate;

    /**
     *
     * @ORM\Column(name="SecondSortArea", type="string", length=52, nullable=true)
     */
    protected $secondSortArea;

    /**
     *
     * @ORM\Column(name="SecondselfOrganizationDeliveryFlag", type="smallint", nullable=true)
     */
    protected $secondselfOrganizationDeliveryFlag;

    /**
     *
     * @ORM\Column(name="PasswordNumber", type="string", length=8, nullable=true)
     */
    protected $passwordNumber;

    /**
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
     *
     * @ORM\Column(name="FirsrtScoreTwoSkillRL", type="string", length=128, nullable=true)
     */
    protected $firsrtScoreTwoSkillRL;

    /**
     *
     * @ORM\Column(name="FirstSoreThreeSkillRLW", type="string", length=128, nullable=true)
     */
    protected $firstSoreThreeSkillRLW;

    /**
     *
     * @ORM\Column(name="SecondScoreThreeSkillRLS", type="string", length=128, nullable=true)
     */
    protected $secondScoreThreeSkillRLS;

    /**
     *
     * @ORM\Column(name="SecondScoreFourSkillRLWS", type="string", length=128, nullable=true)
     */
    protected $secondScoreFourSkillRLWS;

    /**
     *
     * @ORM\Column(name="CSEScoreReading", type="smallint", nullable=true)
     */
    protected $cSEScoreReading;

    /**
     *
     * @ORM\Column(name="CSEScoreListening", type="smallint", nullable=true)
     */
    protected $cSEScoreListening;

    /**
     *
     * @ORM\Column(name="CSEScoreWriting", type="smallint", nullable=true)
     */
    protected $cSEScoreWriting;

    /**
     *
     * @ORM\Column(name="CSEScoreSpeaking", type="smallint", nullable=true)
     */
    protected $cSEScoreSpeaking;

    /**
     *
     * @ORM\Column(name="EikenBand1", type="string", length=255, nullable=true)
     */
    protected $eikenBand1;

    /**
     *
     * @ORM\Column(name="EikenBand2", type="string", length=255, nullable=true)
     */
    protected $eikenBand2;

    /**
     *
     * @ORM\Column(name="CSEScoreMessage1", type="text", nullable=true)
     */
    protected $cSEScoreMessage1;

    /**
     *
     * @ORM\Column(name="CSEScoreMessage2", type="text", nullable=true)
     */
    protected $cSEScoreMessage2;

    /**
     *
     * @ORM\Column(name="IsPass", type="boolean", options={"default":0}, nullable=true)
     */
    protected $isPass;
    
    /**
     *
     * @ORM\Column(name="PupilNo", type="decimal", nullable=true)
     */
    protected $pupilNo;
    /**
     *
     * @ORM\Column(name="ClassName", type="string", length=128, nullable=true)
     */
    protected $className;
    /**
     *
     * @ORM\Column(name="SchoolYearName", type="string", nullable=true)
     */
    protected $schoolYearName;
    
    /**
     *
     * @ORM\Column(name="MappingStatus", type="smallint", options={"default":0}, nullable=true)
     */
    protected $mappingStatus;
    
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
     * @var decimal
     * @ORM\Column(name="TempPupilNo", type="decimal", nullable=true)
     */
    protected $tempPupilNo;
    
    /**
     * @ORM\Column(type="integer", name="ClassId", nullable=true)
     *
     * @var integer
     */
    protected $classId;
    
    /**
     * @ORM\Column(type="integer", name="DantaiSchoolYearCode",nullable=true)
     *
     * @var integer
     */
    protected $dantaiSchoolYearCode;

    /**
     * @var boolean
     * @ORM\Column(name="IsMapped", type="boolean", options={"default":0}, nullable=true)
     */
    protected $isMapped;
    
    /**
     *
     * @ORM\Column(name="PreSchoolYearName", type="string", nullable=true)
     */
    protected $preSchoolYearName;

    /**
     * @return string
     */
    public function getDantaiSchoolYearCode(){
        return  $this->dantaiSchoolYearCode;
    }
    
    /**
     * Setter for DantaiSchoolYearCode
     * @param string $DantaiSchoolYearCode
     */
    public function setDantaiSchoolYearCode($dantaiSchoolYearCode){
        $this->dantaiSchoolYearCode = $dantaiSchoolYearCode;
    }
    
    /**
     * @ORM\Column(type="integer", name="TempDantaiSchoolYearCode", nullable=true)
     *
     * @var integer
     */
    protected $tempDantaiSchoolYearCode;
    
    /**
     * @return string
     */
    public function getTempDantaiSchoolYearCode(){
        return  $this->tempDantaiSchoolYearCode;
    }
    
    /**
     * Setter for TeampDantaiSchoolYearCode
     * @param string $TeampDantaiSchoolYearCode
     */
    public function setTempDantaiSchoolYearCode($tempDantaiSchoolYearCode){
        $this->tempDantaiSchoolYearCode = $tempDantaiSchoolYearCode;
    }
    /**
     * @return integer
     */
    public function getClassId(){
        return  $this->classId;
    }
    
    /**
     * Setter for ClassId
     * @param string $classId
     */
    public function setClassId($classId){
        $this->classId = $classId;
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
    public function getOrgSchoolYearId(){
        return  $this->orgSchoolYearId;
    }
    
    /**
     * Setter for OrgSchoolYearId
     * @param string $orgSchoolYearId
     */
    public function setOrgSchoolYearId($orgSchoolYearId){
        $this->orgSchoolYearId = $orgSchoolYearId;
    }
    
    
    /**
     * @ORM\Column(type="smallint", name="AttendFlag", nullable=true)
     *
     * @var smallint
     */
    protected $attendFlag;
    

    public function getAttendFlag(){
        return  $this->attendFlag;
    }
    

    public function setAttendFlag($attendFlag){
        $this->attendFlag = $attendFlag;
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
    public function setTempClassId($tempClassId){
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
    public function getTempOrgSchoolYearId(){
        return  $this->tempOrgSchoolYearId;
    }
    
    /**
     * Setter for TempOrgSchoolYearId
     * @param string $tempOrgSchoolYearId
     */
    public function setTempOrgSchoolYearId($tempOrgSchoolYearId){
        $this->tempOrgSchoolYearId = $tempOrgSchoolYearId;
    }
    
    /**
     * @var string
     * @ORM\Column(name="TempNameKanji", type="string", nullable=true)
     */
    protected $tempNameKanji;
    /**
     * @return string
     */
    public function getTempNameKanji(){
        return  $this->tempNameKanji;
    }
    
    /**
     * Setter for TempNameKanji
     * @param string $tempNameKanji
     */
    public function setTempNameKanji($tempNameKanji){
        $this->tempNameKanji = $tempNameKanji;
    }
    
    /**
     * @var string
     * @ORM\Column(name="PreTempNameKanji", type="string", nullable=true)
     */
    protected $preTempNameKanji;
    /**
     * @return string
     */
    public function getPreTempNameKanji(){
        return  $this->preTempNameKanji;
    }
    
    /**
     * Setter for PreTempNameKanji
     * @param string $preTempNameKanji
     */
    public function setPreTempNameKanji($preTempNameKanji){
        $this->preTempNameKanji = $preTempNameKanji;
    }
    
    
    
    
    /**
     * @ORM\ManyToOne(targetEntity="ClassJ")
     * @ORM\JoinColumn(name="ClassId", referencedColumnName="id")
     *
     * @var ClassJ
     */
    protected $classJ;
    
    /**
     * @return ClassJ
     */
    public function getClassJ(){
        return  $this->classJ;
    }
    
    /**
     * Setter for ClassJ
     * @param string $ClassJ
     */
    public function setClassJ($ClassJ){
        $this->classJ = $ClassJ;
    }
    
    /**
     * @ORM\ManyToOne(targetEntity="OrgSchoolYear")
     * @ORM\JoinColumn(name="OrgSchoolYearId", referencedColumnName="id")
     *
     * @var OrgSchoolYear
     */
    protected $orgSchoolYear;
    
    /**
     * @return OrgSchoolYear
     */
    public function getOrgSchoolYear(){
        return  $this->orgSchoolYear;
    }
    
    /**
     * Setter for OrgSchoolYear
     * @param string $orgSchoolYear
     */
    public function setOrgSchoolYear($orgSchoolYear){
        $this->orgSchoolYear = $orgSchoolYear;
    }
    
    /**
     * @return int
     */
    public function getTempPupilId()
    {
        return $this->tempPupilId;
    }
    /**
     * @param int $tempPupilId
     */
    public function setTempPupilId($tempPupilId)
    {
        $this->tempPupilId = $tempPupilId;
    }
   
    /**
     * @return string
     */
    public function getTempSchoolYearName()
    {
        return $this->tempSchoolYearName;
    }
    /**
     * @param string $tempSchoolYearName
     */
    public function setTempSchoolYearName($tempSchoolYearName)
    {
        $this->tempSchoolYearName = $tempSchoolYearName;
    }
    
   
    /**
     * @return string
     */
    public function getTempClassName()
    {
        return $this->tempClassName;
    }
    /**
     * @param string $tempClassName
     */
    public function setTempClassName($tempClassName)
    {
        $this->tempClassName = $tempClassName;
    }
    
    /**
     * @return string
     */
    public function getTempPupilNo()
    {
        return $this->tempPupilNo;
    }
    /**
     * @param string $tempPupilNo
     */
    public function setTempPupilNo($tempPupilNo)
    {
        $this->tempPupilNo = $tempPupilNo;
    }

    function getMappingStatus()
    {
        return $this->mappingStatus;
    }
    function setMappingStatus($mappingStatus)
    {
        $this->mappingStatus = $mappingStatus;
    }
    
    function getSchoolYearName()
    {
        return $this->schoolYearName;
    }
    function setSchoolYearName($schoolYearName)
    {
        $this->schoolYearName = $schoolYearName;
    }
    
    function getPupilNo()
    {
        return $this->pupilNo;
    }
    function setPupilNo($pupilNo)
    {
        $this->pupilNo = $pupilNo;
    }
    
    function getClassName()
    {
        return $this->className;
    }
    function setClassName($className)
    {
        $this->className = $className;
    }
    
    function getIsPass()
    {
        return $this->isPass;
    }

    function setIsPass($isPass)
    {
        $this->isPass = $isPass;
    }

    function getPupilId()
    {
        return $this->pupilId;
    }

    function getEikenLevelId()
    {
        return $this->eikenLevelId;
    }

    function getEikenScheduleId()
    {
        return $this->eikenScheduleId;
    }

    function getEikenSchedule()
    {
        return $this->eikenSchedule;
    }

    function getEikenLevel()
    {
        return $this->eikenLevel;
    }

    function getPupil()
    {
        return $this->pupil;
    }

    function getResultFlag()
    {
        return $this->resultFlag;
    }

    function getYear()
    {
        return $this->year;
    }

    function getKai()
    {
        return $this->kai;
    }

    function getEikenId()
    {
        return $this->eikenId;
    }

    function getUketsukeNo()
    {
        return $this->uketsukeNo;
    }

    function getHallClassification()
    {
        return $this->hallClassification;
    }

    function getExecutionDayOfTheWeek()
    {
        return $this->executionDayOfTheWeek;
    }

    function getExamineeNumber()
    {
        return $this->examineeNumber;
    }

    function getPupilName()
    {
        return $this->pupilName;
    }

    function getSchoolNumber()
    {
        return $this->schoolNumber;
    }

    function getSchoolYearCode()
    {
        return $this->schoolYearCode;
    }

    function getClassCode()
    {
        return $this->classCode;
    }

    function getOneExemptionFlag()
    {
        return $this->oneExemptionFlag;
    }

    function getOrganizationNo()
    {
        return $this->organizationNo;
    }

    function getFisrtScore1()
    {
        return $this->fisrtScore1;
    }

    function getFisrtScore2()
    {
        return $this->fisrtScore2;
    }

    function getFisrtScore3()
    {
        return $this->fisrtScore3;
    }

    function getFisrtScore4()
    {
        return $this->fisrtScore4;
    }

    function getFisrtScore5()
    {
        return $this->fisrtScore5;
    }

    function getFisrtScore6()
    {
        return $this->fisrtScore6;
    }

    function getFisrtScore7()
    {
        return $this->fisrtScore7;
    }

    function getFisrtScore8()
    {
        return $this->fisrtScore8;
    }

    function getTotalPrimaryScore()
    {
        return $this->totalPrimaryScore;
    }

    function getPrimaryPassFailFlag()
    {
        return $this->primaryPassFailFlag;
    }

    function getPrimaryFailureLevel()
    {
        return $this->primaryFailureLevel;
    }

    function getSecondScore1()
    {
        return $this->secondScore1;
    }

    function getSecondScore2()
    {
        return $this->secondScore2;
    }

    function getSecondScore3()
    {
        return $this->secondScore3;
    }

    function getSecondScore4()
    {
        return $this->secondScore4;
    }

    function getSecondScore5()
    {
        return $this->secondScore5;
    }

    function getSecondScore6()
    {
        return $this->secondScore6;
    }

    function getSecondScore7()
    {
        return $this->secondScore7;
    }

    function getSecondScore8()
    {
        return $this->secondScore8;
    }

    function getTotalSecondScore()
    {
        return $this->totalSecondScore;
    }

    function getSecondPassFailFlag()
    {
        return $this->secondPassFailFlag;
    }

    function getSecondUnacceptableLevel()
    {
        return $this->secondUnacceptableLevel;
    }

    function getSecondExamHall()
    {
        return $this->secondExamHall;
    }

    function getSecondSetTimeHour()
    {
        return $this->secondSetTimeHour;
    }

    function getSecondSetTimeMinute()
    {
        return $this->secondSetTimeMinute;
    }

    function getFirstMailSendFlag()
    {
        return $this->firstMailSendFlag;
    }

    function getSecondMailSendFlag()
    {
        return $this->secondMailSendFlag;
    }

    function getInsertDate()
    {
        return $this->insertDate;
    }

    function getUpdateDate()
    {
        return $this->updateDate;
    }

    function getIssueDate()
    {
        return $this->issueDate;
    }

    function getDeliveryClassification()
    {
        return $this->deliveryClassification;
    }

    function getSemiClassification()
    {
        return $this->semiClassification;
    }

    function getDomesticInternationalClassification()
    {
        return $this->domesticInternationalClassification;
    }

    function getShippingClassification()
    {
        return $this->shippingClassification;
    }

    function getDeedClassification()
    {
        return $this->deedClassification;
    }

    function getDisplayClass()
    {
        return $this->displayClass;
    }

    function getExamLocation()
    {
        return $this->examLocation;
    }

    function getNameKanji()
    {
        return $this->nameKanji;
    }

    function getNameRomanji()
    {
        return $this->nameRomanji;
    }

    function getNameRomanjiWithPrefix()
    {
        return $this->nameRomanjiWithPrefix;
    }

    function getNameKana()
    {
        return $this->nameKana;
    }

    function getZipCode()
    {
        return $this->zipCode;
    }

    function getAddress1()
    {
        return $this->address1;
    }

    function getAddress2()
    {
        return $this->address2;
    }

    function getAddress3()
    {
        return $this->address3;
    }

    function getAddress4()
    {
        return $this->address4;
    }

    function getAddress5()
    {
        return $this->address5;
    }

    function getUrgentNotification()
    {
        return $this->urgentNotification;
    }

    function getBatchNumber()
    {
        return $this->batchNumber;
    }

    function getSeriNumber()
    {
        return $this->seriNumber;
    }

    function getSchoolClassification()
    {
        return $this->schoolClassification;
    }

    function getClassForDisplay()
    {
        return $this->classForDisplay;
    }

    function getSex()
    {
        return $this->sex;
    }

    function getBarCodeStatus()
    {
        return $this->barCodeStatus;
    }

    function getBarcode()
    {
        return $this->barcode;
    }

    function getOrganizationName()
    {
        return $this->organizationName;
    }

    function getPassword()
    {
        return $this->password;
    }

    function getNote1()
    {
        return $this->note1;
    }

    function getNote2()
    {
        return $this->note2;
    }

    function getNote3()
    {
        return $this->note3;
    }

    function getExamResults()
    {
        return $this->examResults;
    }

    function getFirstExamResultsFlag()
    {
        return $this->firstExamResultsFlag;
    }

    function getFirstExamResultsFlagForDisplay()
    {
        return $this->firstExamResultsFlagForDisplay;
    }

    function getFirstExamResultsPerfectScore()
    {
        return $this->firstExamResultsPerfectScore;
    }

    function getFirstExamResultsPassPoint()
    {
        return $this->firstExamResultsPassPoint;
    }

    function getFirstExamResultsFailPoint()
    {
        return $this->firstExamResultsFailPoint;
    }

    function getFirstExamResultsAveragePass()
    {
        return $this->firstExamResultsAveragePass;
    }

    function getFirstExamResultsExamAverage()
    {
        return $this->firstExamResultsExamAverage;
    }

    function getFirstAdviceSentence1()
    {
        return $this->firstAdviceSentence1;
    }

    function getFirstAdviceSentence2()
    {
        return $this->firstAdviceSentence2;
    }

    function getFirstAdviceSentence3()
    {
        return $this->firstAdviceSentence3;
    }

    function getFirstAdviceSentence4()
    {
        return $this->firstAdviceSentence4;
    }

    function getFirstAdviceSentence5()
    {
        return $this->firstAdviceSentence5;
    }

    function getFirstAdviceSentence6()
    {
        return $this->firstAdviceSentence6;
    }

    function getCorrectAnswer()
    {
        return $this->correctAnswer;
    }

    function getCorrection()
    {
        return $this->correction;
    }

    function getExplanation1()
    {
        return $this->explanation1;
    }

    function getExplanation2()
    {
        return $this->explanation2;
    }

    function getCrowdedFlag()
    {
        return $this->crowdedFlag;
    }

    function getCrowdedSentence()
    {
        return $this->crowdedSentence;
    }

    function getSecondHallClassification()
    {
        return $this->secondHallClassification;
    }

    function getHallNumber()
    {
        return $this->hallNumber;
    }

    function getHallName()
    {
        return $this->hallName;
    }

    function getSecondZipCode()
    {
        return $this->secondZipCode;
    }

    function getSecondAddress()
    {
        return $this->secondAddress;
    }

    function getTrafficRoute1()
    {
        return $this->trafficRoute1;
    }

    function getTrafficRoute2()
    {
        return $this->trafficRoute2;
    }

    function getTrafficRoute3()
    {
        return $this->trafficRoute3;
    }

    function getMapCode()
    {
        return $this->mapCode;
    }

    function getMeetingTime()
    {
        return $this->meetingTime;
    }

    function getMeetingTimeDisplay()
    {
        return $this->meetingTimeDisplay;
    }

    function getMeetingTimeColorFlag()
    {
        return $this->meetingTimeColorFlag;
    }

    function getPhotoAttachEsitence()
    {
        return $this->photoAttachEsitence;
    }

    function getSemiHallApplicationDisplay()
    {
        return $this->semiHallApplicationDisplay;
    }

    function getBaggageOutputClassification()
    {
        return $this->baggageOutputClassification;
    }

    function getComment()
    {
        return $this->comment;
    }

    function getCommunicationField()
    {
        return $this->communicationField;
    }

    function getFirstFailureFourFiveClass()
    {
        return $this->firstFailureFourFiveClass;
    }

    function getVocabularyFieldScore()
    {
        return $this->vocabularyFieldScore;
    }

    function getVocabularyScore()
    {
        return $this->vocabularyScore;
    }

    function getVocabularyPercentCorrectAnswers()
    {
        return $this->vocabularyPercentCorrectAnswers;
    }

    function getVocabularyOverallAverage()
    {
        return $this->vocabularyOverallAverage;
    }

    function getVocabularyPassAverage()
    {
        return $this->vocabularyPassAverage;
    }

    function getReadingFieldScore()
    {
        return $this->readingFieldScore;
    }

    function getReadingScore()
    {
        return $this->readingScore;
    }

    function getReadingPercentCorrectAnswers()
    {
        return $this->readingPercentCorrectAnswers;
    }

    function getReadingOverallAverage()
    {
        return $this->readingOverallAverage;
    }

    function getReadingPassAverage()
    {
        return $this->readingPassAverage;
    }

    function getListeningFieldScore()
    {
        return $this->listeningFieldScore;
    }

    function getListeningScore()
    {
        return $this->listeningScore;
    }

    function getListeningPercentCorrectAnswers()
    {
        return $this->listeningPercentCorrectAnswers;
    }

    function getListeningOverallAverage()
    {
        return $this->listeningOverallAverage;
    }

    function getListeningPassAverage()
    {
        return $this->listeningPassAverage;
    }

    function getCompositionFieldScore()
    {
        return $this->compositionFieldScore;
    }

    function getCompositionScore()
    {
        return $this->compositionScore;
    }

    function getCompositionPercentCorrectAnswers()
    {
        return $this->compositionPercentCorrectAnswers;
    }

    function getCompositionOverallAverage()
    {
        return $this->compositionOverallAverage;
    }

    function getCompositionPassAverage()
    {
        return $this->compositionPassAverage;
    }

    function getResultScoreAccordingField1()
    {
        return $this->resultScoreAccordingField1;
    }

    function getResultScoreAccordingField2()
    {
        return $this->resultScoreAccordingField2;
    }

    function getResultScoreAccordingField3()
    {
        return $this->resultScoreAccordingField3;
    }

    function getResultScoreAccordingField4()
    {
        return $this->resultScoreAccordingField4;
    }

    function getResultPerfectScoreAccordingField1()
    {
        return $this->resultPerfectScoreAccordingField1;
    }

    function getResultPerfectScoreAccordingField2()
    {
        return $this->resultPerfectScoreAccordingField2;
    }

    function getResultPerfectScoreAccordingField3()
    {
        return $this->resultPerfectScoreAccordingField3;
    }

    function getResultPerfectScoreAccordingField4()
    {
        return $this->resultPerfectScoreAccordingField4;
    }

    function getLargeQuestionCorrectAnswer1()
    {
        return $this->largeQuestionCorrectAnswer1;
    }

    function getLargeQuestionCorrectAnswer2()
    {
        return $this->largeQuestionCorrectAnswer2;
    }

    function getLargeQuestionCorrectAnswer3()
    {
        return $this->largeQuestionCorrectAnswer3;
    }

    function getLargeQuestionCorrectAnswer4()
    {
        return $this->largeQuestionCorrectAnswer4;
    }

    function getLargeQuestionProblemResult1()
    {
        return $this->largeQuestionProblemResult1;
    }

    function getLargeQuestionProblemResult2()
    {
        return $this->largeQuestionProblemResult2;
    }

    function getLargeQuestionProblemResult3()
    {
        return $this->largeQuestionProblemResult3;
    }

    function getLargeQuestionProblemResult4()
    {
        return $this->largeQuestionProblemResult4;
    }

    function getStydyAdvice1()
    {
        return $this->stydyAdvice1;
    }

    function getStydyAdvice2()
    {
        return $this->stydyAdvice2;
    }

    function getStydyAdvice3()
    {
        return $this->stydyAdvice3;
    }

    function getStydyAdvice4()
    {
        return $this->stydyAdvice4;
    }

    function getNoticeCode1()
    {
        return $this->noticeCode1;
    }

    function getNoticeCode2()
    {
        return $this->noticeCode2;
    }

    function getStudyRealityGraph1()
    {
        return $this->studyRealityGraph1;
    }

    function getStudyRealityGraph2()
    {
        return $this->studyRealityGraph2;
    }

    function getFirstPassMerit1()
    {
        return $this->firstPassMerit1;
    }

    function getFirstPassMerit2()
    {
        return $this->firstPassMerit2;
    }

    function getFirstPassMerit3()
    {
        return $this->firstPassMerit3;
    }

    function getFirstPassMerit4()
    {
        return $this->firstPassMerit4;
    }

    function getFirstPassMerit5()
    {
        return $this->firstPassMerit5;
    }

    function getFirstPassMerit6()
    {
        return $this->firstPassMerit6;
    }

    function getFirstPassMerit7()
    {
        return $this->firstPassMerit7;
    }

    function getFirstPassMerit8()
    {
        return $this->firstPassMerit8;
    }

    function getFirstPassMerit9()
    {
        return $this->firstPassMerit9;
    }

    function getFirstPassMerit10()
    {
        return $this->firstPassMerit10;
    }

    function getFirstPassMerit11()
    {
        return $this->firstPassMerit11;
    }

    function getFirstPassMerit12()
    {
        return $this->firstPassMerit12;
    }

    function getFirstPassMerit13()
    {
        return $this->firstPassMerit13;
    }

    function getFirstPassMerit14()
    {
        return $this->firstPassMerit14;
    }

    function getFirstPassMerit15()
    {
        return $this->firstPassMerit15;
    }

    function getCanDoList1()
    {
        return $this->canDoList1;
    }

    function getCertificateNumber()
    {
        return $this->certificateNumber;
    }

    function getCertificationDate()
    {
        return $this->certificationDate;
    }

    function getSortArea()
    {
        return $this->sortArea;
    }

    function getSelfOrganizationsDeliveryFlag()
    {
        return $this->selfOrganizationsDeliveryFlag;
    }

    function getSecondIssueYear()
    {
        return $this->secondIssueYear;
    }

    function getSecondDeliveryClassification()
    {
        return $this->secondDeliveryClassification;
    }

    function getSecondSemiClassification()
    {
        return $this->secondSemiClassification;
    }

    function getSecondExecutionDayOfTheWeek()
    {
        return $this->secondExecutionDayOfTheWeek;
    }

    function getSecondDomesticInternationalClassification()
    {
        return $this->secondDomesticInternationalClassification;
    }

    function getSecondShippingClassification()
    {
        return $this->secondShippingClassification;
    }

    function getSecondDeedExistenceClassification()
    {
        return $this->secondDeedExistenceClassification;
    }

    function getSecondExaminationAreas()
    {
        return $this->secondExaminationAreas;
    }

    function getSecondEmergencyNotice()
    {
        return $this->secondEmergencyNotice;
    }

    function getSecondBatchNumber()
    {
        return $this->secondBatchNumber;
    }

    function getSecondSeriNumber()
    {
        return $this->secondSeriNumber;
    }

    function getSecondBarCodeStatus()
    {
        return $this->secondBarCodeStatus;
    }

    function getSecondBarCode()
    {
        return $this->secondBarCode;
    }

    function getSecondNote1()
    {
        return $this->secondNote1;
    }

    function getSecondNote2()
    {
        return $this->secondNote2;
    }

    function getSecondNote3()
    {
        return $this->secondNote3;
    }

    function getSecondExamClassification()
    {
        return $this->secondExamClassification;
    }

    function getSecondExamResultsFlag()
    {
        return $this->secondExamResultsFlag;
    }

    function getSecondExamResultsFlagForDisplay()
    {
        return $this->secondExamResultsFlagForDisplay;
    }

    function getSecondExamResultsPerfectScore()
    {
        return $this->secondExamResultsPerfectScore;
    }

    function getSecondExamResultsPassPoint()
    {
        return $this->secondExamResultsPassPoint;
    }

    function getSecondtExamResultsFailPoint()
    {
        return $this->secondtExamResultsFailPoint;
    }

    function getSecondAdviceSentence1()
    {
        return $this->secondAdviceSentence1;
    }

    function getSecondAdviceSentence2()
    {
        return $this->secondAdviceSentence2;
    }

    function getSecondAdviceSentence3()
    {
        return $this->secondAdviceSentence3;
    }

    function getSecondAdviceSentence4()
    {
        return $this->secondAdviceSentence4;
    }

    function getSecondAdviceSentence5()
    {
        return $this->secondAdviceSentence5;
    }

    function getSecondAdviceSentence6()
    {
        return $this->secondAdviceSentence6;
    }

    function getScoreAccordingField1()
    {
        return $this->scoreAccordingField1;
    }

    function getScoreAccordingField2()
    {
        return $this->scoreAccordingField2;
    }

    function getScoreAccordingField3()
    {
        return $this->scoreAccordingField3;
    }

    function getScoreAccordingField4()
    {
        return $this->scoreAccordingField4;
    }

    function getScoreAccordingField5()
    {
        return $this->scoreAccordingField5;
    }

    function getScoringAccordingField1()
    {
        return $this->scoringAccordingField1;
    }

    function getScoringAccordingField2()
    {
        return $this->scoringAccordingField2;
    }

    function getScoringAccordingField3()
    {
        return $this->scoringAccordingField3;
    }

    function getScoringAccordingField4()
    {
        return $this->scoringAccordingField4;
    }

    function getScoringAccordingField5()
    {
        return $this->scoringAccordingField5;
    }

    function getSecondPassMerit1()
    {
        return $this->secondPassMerit1;
    }

    function getSecondPassMerit2()
    {
        return $this->secondPassMerit2;
    }

    function getSecondPassMerit3()
    {
        return $this->secondPassMerit3;
    }

    function getSecondPassMerit4()
    {
        return $this->secondPassMerit4;
    }

    function getSecondPassMerit5()
    {
        return $this->secondPassMerit5;
    }

    function getSecondPassMerit6()
    {
        return $this->secondPassMerit6;
    }

    function getSecondPassMerit7()
    {
        return $this->secondPassMerit7;
    }

    function getSecondPassMerit8()
    {
        return $this->secondPassMerit8;
    }

    function getSecondPassMerit9()
    {
        return $this->secondPassMerit9;
    }

    function getSecondPassMerit10()
    {
        return $this->secondPassMerit10;
    }

    function getSecondPassMerit11()
    {
        return $this->secondPassMerit11;
    }

    function getSecondPassMerit12()
    {
        return $this->secondPassMerit12;
    }

    function getSecondPassMerit13()
    {
        return $this->secondPassMerit13;
    }

    function getSecondPassMerit14()
    {
        return $this->secondPassMerit14;
    }

    function getSecondPassMerit15()
    {
        return $this->secondPassMerit15;
    }

    function getCanDoList2()
    {
        return $this->canDoList2;
    }

    function getNotice()
    {
        return $this->notice;
    }

    function getSecondCertificateNumber()
    {
        return $this->secondCertificateNumber;
    }

    function getSecondCertificationDate()
    {
        return $this->secondCertificationDate;
    }

    function getSecondSortArea()
    {
        return $this->secondSortArea;
    }

    function getSecondselfOrganizationDeliveryFlag()
    {
        return $this->secondselfOrganizationDeliveryFlag;
    }

    function getPasswordNumber()
    {
        return $this->passwordNumber;
    }

    function getBirthday()
    {
        return $this->birthday;
    }

    function getFirsrtScoreTwoSkillRL()
    {
        return $this->firsrtScoreTwoSkillRL;
    }

    function getFirstSoreThreeSkillRLW()
    {
        return $this->firstSoreThreeSkillRLW;
    }

    function getSecondScoreThreeSkillRLS()
    {
        return $this->secondScoreThreeSkillRLS;
    }

    function getSecondScoreFourSkillRLWS()
    {
        return $this->secondScoreFourSkillRLWS;
    }

    function getCSEScoreReading()
    {
        return $this->cSEScoreReading;
    }

    function getCSEScoreListening()
    {
        return $this->cSEScoreListening;
    }

    function getCSEScoreWriting()
    {
        return $this->cSEScoreWriting;
    }

    function getCSEScoreSpeaking()
    {
        return $this->cSEScoreSpeaking;
    }

    function getEikenBand1()
    {
        return $this->eikenBand1;
    }

    function getEikenBand2()
    {
        return $this->eikenBand2;
    }

    function getCSEScoreMessage1()
    {
        return $this->cSEScoreMessage1;
    }

    function getCSEScoreMessage2()
    {
        return $this->cSEScoreMessage2;
    }

    /**
     * @return bool
     */
    function getIsMapped()
    {
        return $this->isMapped;
    }

    function setPupilId($pupilId)
    {
        $this->pupilId = $pupilId;
    }

    function setEikenLevelId($eikenLevelId)
    {
        $this->eikenLevelId = $eikenLevelId;
    }

    function setEikenScheduleId($eikenScheduleId)
    {
        $this->eikenScheduleId = $eikenScheduleId;
    }

    function setEikenSchedule($eikenSchedule)
    {
        $this->eikenSchedule = $eikenSchedule;
    }

    function setEikenLevel($eikenLevel)
    {
        $this->eikenLevel = $eikenLevel;
    }

    function setPupil($pupil)
    {
        $this->pupil = $pupil;
    }

    function setResultFlag($resultFlag)
    {
        $this->resultFlag = $resultFlag;
    }

    function setYear($year)
    {
        $this->year = $year;
    }

    function setKai($kai)
    {
        $this->kai = $kai;
    }

    function setEikenId($eikenId)
    {
        $this->eikenId = $eikenId;
    }

    function setUketsukeNo($uketsukeNo)
    {
        $this->uketsukeNo = $uketsukeNo;
    }

    function setHallClassification($hallClassification)
    {
        $this->hallClassification = $hallClassification;
    }

    function setExecutionDayOfTheWeek($executionDayOfTheWeek)
    {
        $this->executionDayOfTheWeek = $executionDayOfTheWeek;
    }

    function setExamineeNumber($examineeNumber)
    {
        $this->examineeNumber = $examineeNumber;
    }

    function setPupilName($pupilName)
    {
        $this->pupilName = $pupilName;
    }

    function setSchoolNumber($schoolNumber)
    {
        $this->schoolNumber = $schoolNumber;
    }

    function setSchoolYearCode($schoolYearCode)
    {
        $this->schoolYearCode = $schoolYearCode;
    }

    function setClassCode($classCode)
    {
        $this->classCode = $classCode;
    }

    function setOneExemptionFlag($oneExemptionFlag)
    {
        $this->oneExemptionFlag = $oneExemptionFlag;
    }

    function setOrganizationNo($organizationNo)
    {
        $this->organizationNo = $organizationNo;
    }

    function setFisrtScore1($fisrtScore1)
    {
        $this->fisrtScore1 = $fisrtScore1;
    }

    function setFisrtScore2($fisrtScore2)
    {
        $this->fisrtScore2 = $fisrtScore2;
    }

    function setFisrtScore3($fisrtScore3)
    {
        $this->fisrtScore3 = $fisrtScore3;
    }

    function setFisrtScore4($fisrtScore4)
    {
        $this->fisrtScore4 = $fisrtScore4;
    }

    function setFisrtScore5($fisrtScore5)
    {
        $this->fisrtScore5 = $fisrtScore5;
    }

    function setFisrtScore6($fisrtScore6)
    {
        $this->fisrtScore6 = $fisrtScore6;
    }

    function setFisrtScore7($fisrtScore7)
    {
        $this->fisrtScore7 = $fisrtScore7;
    }

    function setFisrtScore8($fisrtScore8)
    {
        $this->fisrtScore8 = $fisrtScore8;
    }

    function setTotalPrimaryScore($totalPrimaryScore)
    {
        $this->totalPrimaryScore = $totalPrimaryScore;
    }

    function setPrimaryPassFailFlag($primaryPassFailFlag)
    {
        $this->primaryPassFailFlag = $primaryPassFailFlag;
    }

    function setPrimaryFailureLevel($primaryFailureLevel)
    {
        $this->primaryFailureLevel = $primaryFailureLevel;
    }

    function setSecondScore1($secondScore1)
    {
        $this->secondScore1 = $secondScore1;
    }

    function setSecondScore2($secondScore2)
    {
        $this->secondScore2 = $secondScore2;
    }

    function setSecondScore3($secondScore3)
    {
        $this->secondScore3 = $secondScore3;
    }

    function setSecondScore4($secondScore4)
    {
        $this->secondScore4 = $secondScore4;
    }

    function setSecondScore5($secondScore5)
    {
        $this->secondScore5 = $secondScore5;
    }

    function setSecondScore6($secondScore6)
    {
        $this->secondScore6 = $secondScore6;
    }

    function setSecondScore7($secondScore7)
    {
        $this->secondScore7 = $secondScore7;
    }

    function setSecondScore8($secondScore8)
    {
        $this->secondScore8 = $secondScore8;
    }

    function setTotalSecondScore($totalSecondScore)
    {
        $this->totalSecondScore = $totalSecondScore;
    }

    function setSecondAcceptanceFlag($secondAcceptanceFlag)
    {
        $this->secondAcceptanceFlag = $secondAcceptanceFlag;
    }

    function setSecondUnacceptableLevel($secondUnacceptableLevel)
    {
        $this->secondUnacceptableLevel = $secondUnacceptableLevel;
    }

    function setSecondExamHall($secondExamHall)
    {
        $this->secondExamHall = $secondExamHall;
    }

    function setSecondSetTimeHour($secondSetTimeHour)
    {
        $this->secondSetTimeHour = $secondSetTimeHour;
    }

    function setSecondSetTimeMinute($secondSetTimeMinute)
    {
        $this->secondSetTimeMinute = $secondSetTimeMinute;
    }

    function setFirstMailSendFlag($firstMailSendFlag)
    {
        $this->firstMailSendFlag = $firstMailSendFlag;
    }

    function setSecondMailSendFlag($secondMailSendFlag)
    {
        $this->secondMailSendFlag = $secondMailSendFlag;
    }

    function setInsertDate($insertDate)
    {
        $this->insertDate = $insertDate;
    }

    function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;
    }

    function setDeliveryClassification($deliveryClassification)
    {
        $this->deliveryClassification = $deliveryClassification;
    }

    function setSemiClassification($semiClassification)
    {
        $this->semiClassification = $semiClassification;
    }

    function setDomesticInternationalClassification($domesticInternationalClassification)
    {
        $this->domesticInternationalClassification = $domesticInternationalClassification;
    }

    function setShippingClassification($shippingClassification)
    {
        $this->shippingClassification = $shippingClassification;
    }

    function setDeedClassification($deedClassification)
    {
        $this->deedClassification = $deedClassification;
    }

    function setDisplayClass($displayClass)
    {
        $this->displayClass = $displayClass;
    }

    function setExamLocation($examLocation)
    {
        $this->examLocation = $examLocation;
    }

    function setNameKanji($nameKanji)
    {
        $this->nameKanji = $nameKanji;
    }

    function setNameRomanji($nameRomanji)
    {
        $this->nameRomanji = $nameRomanji;
    }

    function setNameRomanjiWithPrefix($nameRomanjiWithPrefix)
    {
        $this->nameRomanjiWithPrefix = $nameRomanjiWithPrefix;
    }

    function setNameKana($nameKana)
    {
        $this->nameKana = $nameKana;
    }

    function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    function setAddress3($address3)
    {
        $this->address3 = $address3;
    }

    function setAddress4($address4)
    {
        $this->address4 = $address4;
    }

    function setAddress5($address5)
    {
        $this->address5 = $address5;
    }

    function setUrgentNotification($urgentNotification)
    {
        $this->urgentNotification = $urgentNotification;
    }

    function setBatchNumber($batchNumber)
    {
        $this->batchNumber = $batchNumber;
    }

    function setSeriNumber($seriNumber)
    {
        $this->seriNumber = $seriNumber;
    }

    function setSchoolClassification($schoolClassification)
    {
        $this->schoolClassification = $schoolClassification;
    }

    function setClassForDisplay($classForDisplay)
    {
        $this->classForDisplay = $classForDisplay;
    }

    function setSex($sex)
    {
        $this->sex = $sex;
    }

    function setBarCodeStatus($barCodeStatus)
    {
        $this->barCodeStatus = $barCodeStatus;
    }

    function setBarcode($barcode)
    {
        $this->barcode = $barcode;
    }

    function setOrganizationName($organizationName)
    {
        $this->organizationName = $organizationName;
    }

    function setPassword($password)
    {
        $this->password = $password;
    }

    function setNote1($note1)
    {
        $this->note1 = $note1;
    }

    function setNote2($note2)
    {
        $this->note2 = $note2;
    }

    function setNote3($note3)
    {
        $this->note3 = $note3;
    }

    function setExamResults($examResults)
    {
        $this->examResults = $examResults;
    }

    function setFirstExamResultsFlag($firstExamResultsFlag)
    {
        $this->firstExamResultsFlag = $firstExamResultsFlag;
    }

    function setFirstExamResultsFlagForDisplay($firstExamResultsFlagForDisplay)
    {
        $this->firstExamResultsFlagForDisplay = $firstExamResultsFlagForDisplay;
    }

    function setFirstExamResultsPerfectScore($firstExamResultsPerfectScore)
    {
        $this->firstExamResultsPerfectScore = $firstExamResultsPerfectScore;
    }

    function setFirstExamResultsPassPoint($firstExamResultsPassPoint)
    {
        $this->firstExamResultsPassPoint = $firstExamResultsPassPoint;
    }

    function setFirstExamResultsFailPoint($firstExamResultsFailPoint)
    {
        $this->firstExamResultsFailPoint = $firstExamResultsFailPoint;
    }

    function setFirstExamResultsAveragePass($firstExamResultsAveragePass)
    {
        $this->firstExamResultsAveragePass = $firstExamResultsAveragePass;
    }

    function setFirstExamResultsExamAverage($firstExamResultsExamAverage)
    {
        $this->firstExamResultsExamAverage = $firstExamResultsExamAverage;
    }

    function setFirstAdviceSentence1($firstAdviceSentence1)
    {
        $this->firstAdviceSentence1 = $firstAdviceSentence1;
    }

    function setFirstAdviceSentence2($firstAdviceSentence2)
    {
        $this->firstAdviceSentence2 = $firstAdviceSentence2;
    }

    function setFirstAdviceSentence3($firstAdviceSentence3)
    {
        $this->firstAdviceSentence3 = $firstAdviceSentence3;
    }

    function setFirstAdviceSentence4($firstAdviceSentence4)
    {
        $this->firstAdviceSentence4 = $firstAdviceSentence4;
    }

    function setFirstAdviceSentence5($firstAdviceSentence5)
    {
        $this->firstAdviceSentence5 = $firstAdviceSentence5;
    }

    function setFirstAdviceSentence6($firstAdviceSentence6)
    {
        $this->firstAdviceSentence6 = $firstAdviceSentence6;
    }

    function setCorrectAnswer($correctAnswer)
    {
        $this->correctAnswer = $correctAnswer;
    }

    function setCorrection($correction)
    {
        $this->correction = $correction;
    }

    function setExplanation1($explanation1)
    {
        $this->explanation1 = $explanation1;
    }

    function setExplanation2($explanation2)
    {
        $this->explanation2 = $explanation2;
    }

    function setCrowdedFlag($crowdedFlag)
    {
        $this->crowdedFlag = $crowdedFlag;
    }

    function setCrowdedSentence($crowdedSentence)
    {
        $this->crowdedSentence = $crowdedSentence;
    }

    function setSecondHallClassification($secondHallClassification)
    {
        $this->secondHallClassification = $secondHallClassification;
    }

    function setHallNumber($hallNumber)
    {
        $this->hallNumber = $hallNumber;
    }

    function setHallName($hallName)
    {
        $this->hallName = $hallName;
    }

    function setSecondZipCode($secondZipCode)
    {
        $this->secondZipCode = $secondZipCode;
    }

    function setSecondAddress($secondAddress)
    {
        $this->secondAddress = $secondAddress;
    }

    function setTrafficRoute1($trafficRoute1)
    {
        $this->trafficRoute1 = $trafficRoute1;
    }

    function setTrafficRoute2($trafficRoute2)
    {
        $this->trafficRoute2 = $trafficRoute2;
    }

    function setTrafficRoute3($trafficRoute3)
    {
        $this->trafficRoute3 = $trafficRoute3;
    }

    function setMapCode($mapCode)
    {
        $this->mapCode = $mapCode;
    }

    function setMeetingTime($meetingTime)
    {
        $this->meetingTime = $meetingTime;
    }

    function setMeetingTimeDisplay($meetingTimeDisplay)
    {
        $this->meetingTimeDisplay = $meetingTimeDisplay;
    }

    function setMeetingTimeColorFlag($meetingTimeColorFlag)
    {
        $this->meetingTimeColorFlag = $meetingTimeColorFlag;
    }

    function setPhotoAttachEsitence($photoAttachEsitence)
    {
        $this->photoAttachEsitence = $photoAttachEsitence;
    }

    function setSemiHallApplicationDisplay($semiHallApplicationDisplay)
    {
        $this->semiHallApplicationDisplay = $semiHallApplicationDisplay;
    }

    function setBaggageOutputClassification($baggageOutputClassification)
    {
        $this->baggageOutputClassification = $baggageOutputClassification;
    }

    function setComment($comment)
    {
        $this->comment = $comment;
    }

    function setCommunicationField($communicationField)
    {
        $this->communicationField = $communicationField;
    }

    function setFirstFailureFourFiveClass($firstFailureFourFiveClass)
    {
        $this->firstFailureFourFiveClass = $firstFailureFourFiveClass;
    }

    function setVocabularyFieldScore($vocabularyFieldScore)
    {
        $this->vocabularyFieldScore = $vocabularyFieldScore;
    }

    function setVocabularyScore($vocabularyScore)
    {
        $this->vocabularyScore = $vocabularyScore;
    }

    function setVocabularyPercentCorrectAnswers($vocabularyPercentCorrectAnswers)
    {
        $this->vocabularyPercentCorrectAnswers = $vocabularyPercentCorrectAnswers;
    }

    function setVocabularyOverallAverage($vocabularyOverallAverage)
    {
        $this->vocabularyOverallAverage = $vocabularyOverallAverage;
    }

    function setVocabularyPassAverage($vocabularyPassAverage)
    {
        $this->vocabularyPassAverage = $vocabularyPassAverage;
    }

    function setReadingFieldScore($readingFieldScore)
    {
        $this->readingFieldScore = $readingFieldScore;
    }

    function setReadingScore($readingScore)
    {
        $this->readingScore = $readingScore;
    }

    function setReadingPercentCorrectAnswers($readingPercentCorrectAnswers)
    {
        $this->readingPercentCorrectAnswers = $readingPercentCorrectAnswers;
    }

    function setReadingOverallAverage($readingOverallAverage)
    {
        $this->readingOverallAverage = $readingOverallAverage;
    }

    function setReadingPassAverage($readingPassAverage)
    {
        $this->readingPassAverage = $readingPassAverage;
    }

    function setListeningFieldScore($listeningFieldScore)
    {
        $this->listeningFieldScore = $listeningFieldScore;
    }

    function setListeningScore($listeningScore)
    {
        $this->listeningScore = $listeningScore;
    }

    function setListeningPercentCorrectAnswers($listeningPercentCorrectAnswers)
    {
        $this->listeningPercentCorrectAnswers = $listeningPercentCorrectAnswers;
    }

    function setListeningOverallAverage($listeningOverallAverage)
    {
        $this->listeningOverallAverage = $listeningOverallAverage;
    }

    function setListeningPassAverage($listeningPassAverage)
    {
        $this->listeningPassAverage = $listeningPassAverage;
    }

    function setCompositionFieldScore($compositionFieldScore)
    {
        $this->compositionFieldScore = $compositionFieldScore;
    }

    function setCompositionScore($compositionScore)
    {
        $this->compositionScore = $compositionScore;
    }

    function setCompositionPercentCorrectAnswers($compositionPercentCorrectAnswers)
    {
        $this->compositionPercentCorrectAnswers = $compositionPercentCorrectAnswers;
    }

    function setCompositionOverallAverage($compositionOverallAverage)
    {
        $this->compositionOverallAverage = $compositionOverallAverage;
    }

    function setCompositionPassAverage($compositionPassAverage)
    {
        $this->compositionPassAverage = $compositionPassAverage;
    }

    function setResultScoreAccordingField1($resultScoreAccordingField1)
    {
        $this->resultScoreAccordingField1 = $resultScoreAccordingField1;
    }

    function setResultScoreAccordingField2($resultScoreAccordingField2)
    {
        $this->resultScoreAccordingField2 = $resultScoreAccordingField2;
    }

    function setResultScoreAccordingField3($resultScoreAccordingField3)
    {
        $this->resultScoreAccordingField3 = $resultScoreAccordingField3;
    }

    function setResultScoreAccordingField4($resultScoreAccordingField4)
    {
        $this->resultScoreAccordingField4 = $resultScoreAccordingField4;
    }

    function setResultPerfectScoreAccordingField1($resultPerfectScoreAccordingField1)
    {
        $this->resultPerfectScoreAccordingField1 = $resultPerfectScoreAccordingField1;
    }

    function setResultPerfectScoreAccordingField2($resultPerfectScoreAccordingField2)
    {
        $this->resultPerfectScoreAccordingField2 = $resultPerfectScoreAccordingField2;
    }

    function setResultPerfectScoreAccordingField3($resultPerfectScoreAccordingField3)
    {
        $this->resultPerfectScoreAccordingField3 = $resultPerfectScoreAccordingField3;
    }

    function setResultPerfectScoreAccordingField4($resultPerfectScoreAccordingField4)
    {
        $this->resultPerfectScoreAccordingField4 = $resultPerfectScoreAccordingField4;
    }

    function setLargeQuestionCorrectAnswer1($largeQuestionCorrectAnswer1)
    {
        $this->largeQuestionCorrectAnswer1 = $largeQuestionCorrectAnswer1;
    }

    function setLargeQuestionCorrectAnswer2($largeQuestionCorrectAnswer2)
    {
        $this->largeQuestionCorrectAnswer2 = $largeQuestionCorrectAnswer2;
    }

    function setLargeQuestionCorrectAnswer3($largeQuestionCorrectAnswer3)
    {
        $this->largeQuestionCorrectAnswer3 = $largeQuestionCorrectAnswer3;
    }

    function setLargeQuestionCorrectAnswer4($largeQuestionCorrectAnswer4)
    {
        $this->largeQuestionCorrectAnswer4 = $largeQuestionCorrectAnswer4;
    }

    function setLargeQuestionProblemResult1($largeQuestionProblemResult1)
    {
        $this->largeQuestionProblemResult1 = $largeQuestionProblemResult1;
    }

    function setLargeQuestionProblemResult2($largeQuestionProblemResult2)
    {
        $this->largeQuestionProblemResult2 = $largeQuestionProblemResult2;
    }

    function setLargeQuestionProblemResult3($largeQuestionProblemResult3)
    {
        $this->largeQuestionProblemResult3 = $largeQuestionProblemResult3;
    }

    function setLargeQuestionProblemResult4($largeQuestionProblemResult4)
    {
        $this->largeQuestionProblemResult4 = $largeQuestionProblemResult4;
    }

    function setStydyAdvice1($stydyAdvice1)
    {
        $this->stydyAdvice1 = $stydyAdvice1;
    }

    function setStydyAdvice2($stydyAdvice2)
    {
        $this->stydyAdvice2 = $stydyAdvice2;
    }

    function setStydyAdvice3($stydyAdvice3)
    {
        $this->stydyAdvice3 = $stydyAdvice3;
    }

    function setStydyAdvice4($stydyAdvice4)
    {
        $this->stydyAdvice4 = $stydyAdvice4;
    }

    function setNoticeCode1($noticeCode1)
    {
        $this->noticeCode1 = $noticeCode1;
    }

    function setNoticeCode2($noticeCode2)
    {
        $this->noticeCode2 = $noticeCode2;
    }

    function setStudyRealityGraph1($studyRealityGraph1)
    {
        $this->studyRealityGraph1 = $studyRealityGraph1;
    }

    function setStudyRealityGraph2($studyRealityGraph2)
    {
        $this->studyRealityGraph2 = $studyRealityGraph2;
    }

    function setFirstPassMerit1($firstPassMerit1)
    {
        $this->firstPassMerit1 = $firstPassMerit1;
    }

    function setFirstPassMerit2($firstPassMerit2)
    {
        $this->firstPassMerit2 = $firstPassMerit2;
    }

    function setFirstPassMerit3($firstPassMerit3)
    {
        $this->firstPassMerit3 = $firstPassMerit3;
    }

    function setFirstPassMerit4($firstPassMerit4)
    {
        $this->firstPassMerit4 = $firstPassMerit4;
    }

    function setFirstPassMerit5($firstPassMerit5)
    {
        $this->firstPassMerit5 = $firstPassMerit5;
    }

    function setFirstPassMerit6($firstPassMerit6)
    {
        $this->firstPassMerit6 = $firstPassMerit6;
    }

    function setFirstPassMerit7($firstPassMerit7)
    {
        $this->firstPassMerit7 = $firstPassMerit7;
    }

    function setFirstPassMerit8($firstPassMerit8)
    {
        $this->firstPassMerit8 = $firstPassMerit8;
    }

    function setFirstPassMerit9($firstPassMerit9)
    {
        $this->firstPassMerit9 = $firstPassMerit9;
    }

    function setFirstPassMerit10($firstPassMerit10)
    {
        $this->firstPassMerit10 = $firstPassMerit10;
    }

    function setFirstPassMerit11($firstPassMerit11)
    {
        $this->firstPassMerit11 = $firstPassMerit11;
    }

    function setFirstPassMerit12($firstPassMerit12)
    {
        $this->firstPassMerit12 = $firstPassMerit12;
    }

    function setFirstPassMerit13($firstPassMerit13)
    {
        $this->firstPassMerit13 = $firstPassMerit13;
    }

    function setFirstPassMerit14($firstPassMerit14)
    {
        $this->firstPassMerit14 = $firstPassMerit14;
    }

    function setFirstPassMerit15($firstPassMerit15)
    {
        $this->firstPassMerit15 = $firstPassMerit15;
    }

    function setCanDoList1($canDoList1)
    {
        $this->canDoList1 = $canDoList1;
    }

    function setCertificateNumber($certificateNumber)
    {
        $this->certificateNumber = $certificateNumber;
    }

    function setCertificationDate( $certificationDate)
    {
        $this->certificationDate = $certificationDate;
    }

    function setSortArea($sortArea)
    {
        $this->sortArea = $sortArea;
    }

    function setSelfOrganizationsDeliveryFlag($selfOrganizationsDeliveryFlag)
    {
        $this->selfOrganizationsDeliveryFlag = $selfOrganizationsDeliveryFlag;
    }

    function setSecondIssueYear($secondIssueYear)
    {
        $this->secondIssueYear = $secondIssueYear;
    }

    function setSecondDeliveryClassification($secondDeliveryClassification)
    {
        $this->secondDeliveryClassification = $secondDeliveryClassification;
    }

    function setSecondSemiClassification($secondSemiClassification)
    {
        $this->secondSemiClassification = $secondSemiClassification;
    }

    function setSecondExecutionDayOfTheWeek($secondExecutionDayOfTheWeek)
    {
        $this->secondExecutionDayOfTheWeek = $secondExecutionDayOfTheWeek;
    }

    function setSecondDomesticInternationalClassification($secondDomesticInternationalClassification)
    {
        $this->secondDomesticInternationalClassification = $secondDomesticInternationalClassification;
    }

    function setSecondShippingClassification($secondShippingClassification)
    {
        $this->secondShippingClassification = $secondShippingClassification;
    }

    function setSecondDeedExistenceClassification($secondDeedExistenceClassification)
    {
        $this->secondDeedExistenceClassification = $secondDeedExistenceClassification;
    }

    function setSecondExaminationAreas($secondExaminationAreas)
    {
        $this->secondExaminationAreas = $secondExaminationAreas;
    }

    function setSecondEmergencyNotice($secondEmergencyNotice)
    {
        $this->secondEmergencyNotice = $secondEmergencyNotice;
    }

    function setSecondBatchNumber($secondBatchNumber)
    {
        $this->secondBatchNumber = $secondBatchNumber;
    }

    function setSecondSeriNumber($secondSeriNumber)
    {
        $this->secondSeriNumber = $secondSeriNumber;
    }

    function setSecondBarCodeStatus($secondBarCodeStatus)
    {
        $this->secondBarCodeStatus = $secondBarCodeStatus;
    }

    function setSecondBarCode($secondBarCode)
    {
        $this->secondBarCode = $secondBarCode;
    }

    function setSecondNote1($secondNote1)
    {
        $this->secondNote1 = $secondNote1;
    }

    function setSecondNote2($secondNote2)
    {
        $this->secondNote2 = $secondNote2;
    }

    function setSecondNote3($secondNote3)
    {
        $this->secondNote3 = $secondNote3;
    }

    function setSecondExamClassification($secondExamClassification)
    {
        $this->secondExamClassification = $secondExamClassification;
    }

    function setSecondExamResultsFlag($secondExamResultsFlag)
    {
        $this->secondExamResultsFlag = $secondExamResultsFlag;
    }

    function setSecondExamResultsFlagForDisplay($secondExamResultsFlagForDisplay)
    {
        $this->secondExamResultsFlagForDisplay = $secondExamResultsFlagForDisplay;
    }

    function setSecondExamResultsPerfectScore($secondExamResultsPerfectScore)
    {
        $this->secondExamResultsPerfectScore = $secondExamResultsPerfectScore;
    }

    function setSecondExamResultsPassPoint($secondExamResultsPassPoint)
    {
        $this->secondExamResultsPassPoint = $secondExamResultsPassPoint;
    }

    function setSecondtExamResultsFailPoint($secondtExamResultsFailPoint)
    {
        $this->secondtExamResultsFailPoint = $secondtExamResultsFailPoint;
    }

    function setSecondAdviceSentence1($secondAdviceSentence1)
    {
        $this->secondAdviceSentence1 = $secondAdviceSentence1;
    }

    function setSecondAdviceSentence2($secondAdviceSentence2)
    {
        $this->secondAdviceSentence2 = $secondAdviceSentence2;
    }

    function setSecondAdviceSentence3($secondAdviceSentence3)
    {
        $this->secondAdviceSentence3 = $secondAdviceSentence3;
    }

    function setSecondAdviceSentence4($secondAdviceSentence4)
    {
        $this->secondAdviceSentence4 = $secondAdviceSentence4;
    }

    function setSecondAdviceSentence5($secondAdviceSentence5)
    {
        $this->secondAdviceSentence5 = $secondAdviceSentence5;
    }

    function setSecondAdviceSentence6($secondAdviceSentence6)
    {
        $this->secondAdviceSentence6 = $secondAdviceSentence6;
    }

    function setScoreAccordingField1($scoreAccordingField1)
    {
        $this->scoreAccordingField1 = $scoreAccordingField1;
    }

    function setScoreAccordingField2($scoreAccordingField2)
    {
        $this->scoreAccordingField2 = $scoreAccordingField2;
    }

    function setScoreAccordingField3($scoreAccordingField3)
    {
        $this->scoreAccordingField3 = $scoreAccordingField3;
    }

    function setScoreAccordingField4($scoreAccordingField4)
    {
        $this->scoreAccordingField4 = $scoreAccordingField4;
    }

    function setScoreAccordingField5($scoreAccordingField5)
    {
        $this->scoreAccordingField5 = $scoreAccordingField5;
    }

    function setScoringAccordingField1($scoringAccordingField1)
    {
        $this->scoringAccordingField1 = $scoringAccordingField1;
    }

    function setScoringAccordingField2($scoringAccordingField2)
    {
        $this->scoringAccordingField2 = $scoringAccordingField2;
    }

    function setScoringAccordingField3($scoringAccordingField3)
    {
        $this->scoringAccordingField3 = $scoringAccordingField3;
    }

    function setScoringAccordingField4($scoringAccordingField4)
    {
        $this->scoringAccordingField4 = $scoringAccordingField4;
    }

    function setScoringAccordingField5($scoringAccordingField5)
    {
        $this->scoringAccordingField5 = $scoringAccordingField5;
    }

    function setSecondPassMerit1($secondPassMerit1)
    {
        $this->secondPassMerit1 = $secondPassMerit1;
    }

    function setSecondPassMerit2($secondPassMerit2)
    {
        $this->secondPassMerit2 = $secondPassMerit2;
    }

    function setSecondPassMerit3($secondPassMerit3)
    {
        $this->secondPassMerit3 = $secondPassMerit3;
    }

    function setSecondPassMerit4($secondPassMerit4)
    {
        $this->secondPassMerit4 = $secondPassMerit4;
    }

    function setSecondPassMerit5($secondPassMerit5)
    {
        $this->secondPassMerit5 = $secondPassMerit5;
    }

    function setSecondPassMerit6($secondPassMerit6)
    {
        $this->secondPassMerit6 = $secondPassMerit6;
    }

    function setSecondPassMerit7($secondPassMerit7)
    {
        $this->secondPassMerit7 = $secondPassMerit7;
    }

    function setSecondPassMerit8($secondPassMerit8)
    {
        $this->secondPassMerit8 = $secondPassMerit8;
    }

    function setSecondPassMerit9($secondPassMerit9)
    {
        $this->secondPassMerit9 = $secondPassMerit9;
    }

    function setSecondPassMerit10($secondPassMerit10)
    {
        $this->secondPassMerit10 = $secondPassMerit10;
    }

    function setSecondPassMerit11($secondPassMerit11)
    {
        $this->secondPassMerit11 = $secondPassMerit11;
    }

    function setSecondPassMerit12($secondPassMerit12)
    {
        $this->secondPassMerit12 = $secondPassMerit12;
    }

    function setSecondPassMerit13($secondPassMerit13)
    {
        $this->secondPassMerit13 = $secondPassMerit13;
    }

    function setSecondPassMerit14($secondPassMerit14)
    {
        $this->secondPassMerit14 = $secondPassMerit14;
    }

    function setSecondPassMerit15($secondPassMerit15)
    {
        $this->secondPassMerit15 = $secondPassMerit15;
    }

    function setCanDoList2($canDoList2)
    {
        $this->canDoList2 = $canDoList2;
    }

    function setNotice($notice)
    {
        $this->notice = $notice;
    }

    function setSecondCertificateNumber($secondCertificateNumber)
    {
        $this->secondCertificateNumber = $secondCertificateNumber;
    }

    function setSecondCertificationDate($secondCertificationDate)
    {
        $this->secondCertificationDate = $secondCertificationDate;
    }

    /**
     *
     * @return decimal
     */
    public function getEikenCSETotal()
    {
        return $this->eikenCSETotal;
    }

    /**
     *
     * @param decimal $eikenCSETotal
     */
    public function setEikenCSETotal($eikenCSETotal)
    {
        $this->eikenCSETotal = $eikenCSETotal;
    }

    function setSecondSortArea($secondSortArea)
    {
        $this->secondSortArea = $secondSortArea;
    }

    function setSecondselfOrganizationDeliveryFlag($secondselfOrganizationDeliveryFlag)
    {
        $this->secondselfOrganizationDeliveryFlag = $secondselfOrganizationDeliveryFlag;
    }

    function setPasswordNumber($passwordNumber)
    {
        $this->passwordNumber = $passwordNumber;
    }

    function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    function setFirsrtScoreTwoSkillRL($firsrtScoreTwoSkillRL)
    {
        $this->firsrtScoreTwoSkillRL = $firsrtScoreTwoSkillRL;
    }

    function setFirstSoreThreeSkillRLW($firstSoreThreeSkillRLW)
    {
        $this->firstSoreThreeSkillRLW = $firstSoreThreeSkillRLW;
    }

    function setSecondScoreThreeSkillRLS($secondScoreThreeSkillRLS)
    {
        $this->secondScoreThreeSkillRLS = $secondScoreThreeSkillRLS;
    }

    function setSecondScoreFourSkillRLWS($secondScoreFourSkillRLWS)
    {
        $this->secondScoreFourSkillRLWS = $secondScoreFourSkillRLWS;
    }

    function setCSEScoreReading($cSEScoreReading)
    {
        $this->cSEScoreReading = $cSEScoreReading;
    }

    function setCSEScoreListening($cSEScoreListening)
    {
        $this->cSEScoreListening = $cSEScoreListening;
    }

    function setCSEScoreWriting($cSEScoreWriting)
    {
        $this->cSEScoreWriting = $cSEScoreWriting;
    }

    function setCSEScoreSpeaking($cSEScoreSpeaking)
    {
        $this->cSEScoreSpeaking = $cSEScoreSpeaking;
    }

    function setEikenBand1($eikenBand1)
    {
        $this->eikenBand1 = $eikenBand1;
    }

    function setEikenBand2($eikenBand2)
    {
        $this->eikenBand2 = $eikenBand2;
    }

    function setCSEScoreMessage1($cSEScoreMessage1)
    {
        $this->cSEScoreMessage1 = $cSEScoreMessage1;
    }

    function setCSEScoreMessage2($cSEScoreMessage2)
    {
        $this->cSEScoreMessage2 = $cSEScoreMessage2;
    }

    function setSecondPassFailFlag($secondPassFailFlag)
    {
        $this->secondPassFailFlag=$secondPassFailFlag;
    }

    /**
     * @param boolean $isMapped
     */
    function setIsMapped($isMapped){
        $this->isMapped = $isMapped;
    }

    /**
     * @return mixed
     */
    public function getTempNameKana()
    {
        return $this->tempNameKana;
    }

    /**
     * @param mixed $tempNameKana
     */
    public function setTempNameKana($tempNameKana)
    {
        $this->tempNameKana = $tempNameKana;
    }

    /**
     * @return mixed
     */
    public function getTempBirthday()
    {
        return $this->tempBirthday;
    }

    /**
     * @param mixed $tempBirthday
     */
    public function setTempBirthday($tempBirthday)
    {
        $this->tempBirthday = $tempBirthday;
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
    
    function getPreSchoolYearName() {
        return $this->preSchoolYearName;
    }

    function setPreSchoolYearName($preSchoolYearName) {
        $this->preSchoolYearName = $preSchoolYearName;
    }

}