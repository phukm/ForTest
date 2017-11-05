<?php
namespace PupilMnt;
class FunctionSaveDataPupilListServiceTest extends \Dantai\Test\AbstractHttpControllerTestCase
{

    public function testConvertKanaFullWidthToHalfWidth()
    {
        $dataTest = array(
            1 => '１組',
            2 => 'アアア',
            3 => '中学１アアア',
            4 => 'アア１ア中学１ｱｱｱアア学３'
        );
        $expect = array(
            1 => '1組',
            2 => 'ｱｱｱ',
            3 => '中学1ｱｱｱ',
            4 => 'ｱｱ1ｱ中学1ｱｱｱｱｱ学3'
        );
        $importPupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        foreach ($dataTest as $key => $str) {
            $actual = $importPupilService->convertKanaFullWidthToHalfWidth($str);
            $this->assertEquals($actual, $expect[$key]);
        }
    }

    public function testFunctionSaveDatabasePupilList()
    {
        $datalist = $this->getDataList();
        $result = array();
        $importPupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        //$importPupilService->saveDataPupil($datalist);
        foreach ($datalist as $key => $items) {
            $pupil = $importPupilService->saveProfilePupil($items);
            $eikenScore = $importPupilService->saveResultEikenScore($items, $pupil);
            $ibaScore = $importPupilService->saveResultIbaScore($items, $pupil);
            $simpleMeasurementResult = $importPupilService->saveSimpleMeasurementResult($items, $pupil);
            $this->getEntityManager()->persist($pupil);
            $this->getEntityManager()->persist($eikenScore);
            $this->getEntityManager()->persist($ibaScore);
            $this->getEntityManager()->persist($simpleMeasurementResult);
            $this->getEntityManager()->flush();
            $result['pupil'][] = $pupil->getId();
            $result['eikenScore'][] = $eikenScore->getId();
            $result['ibaScore'][] = $ibaScore->getId();
            $result['simpleMeasurementResult'][] = $simpleMeasurementResult->getId();
            $this->getEntityManager()->clear();
        }
        $this->assertNotEmpty($result['pupil']);
        $this->assertNotEmpty($result['eikenScore']);
        $this->assertNotEmpty($result['ibaScore']);
        $this->assertNotEmpty($result['simpleMeasurementResult']);
        $this->checkPupil($result['pupil']);
        $this->checkEikenScore($result['eikenScore']);
        $this->checkIbaScore($result['ibaScore']);
        $this->checkSimpleMeasurementResult($result['simpleMeasurementResult']);
        //Delete after check success.
        $this->delete($result['simpleMeasurementResult'], 'SimpleMeasurementResult');
        $this->delete($result['eikenScore'], 'EikenScore');
        $this->delete($result['ibaScore'], 'IbaScore');
        $this->delete($result['pupil'], 'Pupil');
    }

    public function delete($listId, $entityName)
    {
        foreach ($listId as $id) {
            $result = $this->getEntityManager()->getRepository('Application\\Entity\\' . $entityName)->find($id);
            $this->getEntityManager()->remove($result);
        }
        $this->getEntityManager()->flush();
    }

    public function checkPupil($data)
    {
        $datalist = $this->getDataList();
        foreach ($data as $key => $id) {
            $result = $this->getEntityManager()->getRepository('Application\Entity\Pupil')->find($id);
            $this->assertEquals($result->getYear(), $datalist[$key]['year']);
            $this->assertEquals($result->getNumber(), $datalist[$key]['pupilNumber']);
            $this->assertEquals($result->getFirstNameKanji(), $datalist[$key]['firstnameKanji']);
            $this->assertEquals($result->getLastNameKanji(), $datalist[$key]['lastnameKanji']);
            $this->assertEquals($result->getFirstNameKana(), $datalist[$key]['firstnameKana']);
            $this->assertEquals($result->getLastNameKana(), $datalist[$key]['lastnameKana']);
            $this->assertEquals($result->getGender(), ($datalist[$key]['gender'] == '男' ? 1 : 0));
        }
    }

    public function checkEikenScore($data)
    {
        $datalist = $this->getDataList();
        foreach ($data as $key => $id) {
            $result = $this->getEntityManager()->getRepository('Application\Entity\EikenScore')->find($id);
            $this->assertNotNull($result->getCertificationDate());
            $this->assertEquals($result->getKai(), $datalist[$key]['kai']);
            $this->assertEquals($result->getYear(), $datalist[$key]['eikenYear']);
            $this->assertEquals($result->getReadingScore(), $datalist[$key]['eikenScoreReading']);
            $this->assertEquals($result->getListeningScore(), $datalist[$key]['eikenScoreListening']);
            $this->assertEquals($result->getCSEScoreWriting(), $datalist[$key]['eikenScoreWriting']);
            $this->assertEquals($result->getCSEScoreSpeaking(), $datalist[$key]['eikenScoreSpeaking']);
            $this->assertEquals($result->getEikenCSETotal(), $datalist[$key]['eikenScoreSpeaking'] + $datalist[$key]['eikenScoreWriting'] + $datalist[$key]['eikenScoreListening'] + $datalist[$key]['eikenScoreReading']);
        }
    }

    public function checkIbaScore($data)
    {
        $eikenLevelName = array(
            7 => '5級',
            6 => '4級',
            5 => '3級',
            4 => '準2級',
            3 => '2級',
            2 => '準1級',
            1 => '1級'
        );
        $eikenLevelName = array_flip($eikenLevelName);
        $datalist = $this->getDataList();
        foreach ($data as $key => $id) {
            $result = $this->getEntityManager()->getRepository('Application\Entity\IBAScore')->find($id);
            $this->assertNotNull($result->getExamDate());
            $this->assertEquals($result->getReadingScore(), $datalist[$key]['ibaScoreReading']);
            $this->assertEquals($result->getListeningScore(), $datalist[$key]['ibaScoreListening']);
            $this->assertEquals($result->getIBACSETotal(), $datalist[$key]['ibaScoreReading'] + $datalist[$key]['ibaScoreListening']);
            $this->assertEquals($result->getIBALevel()->getId(), $eikenLevelName[$datalist[$key]['ibaLevel']]);
        }
    }

    public function checkSimpleMeasurementResult($data)
    {
        $eikenLevelName = array(
            7 => '5級',
            6 => '4級',
            5 => '3級',
            4 => '準2級',
            3 => '2級',
            2 => '準1級',
            1 => '1級'
        );
        $eikenLevelName = array_flip($eikenLevelName);
        $datalist = $this->getDataList();
        foreach ($data as $key => $id) {
            $result = $this->getEntityManager()->getRepository('Application\Entity\SimpleMeasurementResult')->find($id);
            $this->assertEquals($result->getResultGrammarId(), $eikenLevelName[$datalist[$key]['grammarLevel']]);
            $this->assertEquals($result->getResultVocabularyId(), $eikenLevelName[$datalist[$key]['wordLevel']]);
            $this->assertEquals($result->getResultGrammarName(), $datalist[$key]['grammarLevel']);
            $this->assertEquals($result->getResultVocabularyName(), $datalist[$key]['wordLevel']);
        }
    }

    public function testFunctionGetResultProfilePupilList()
    {
        $data = $this->getData();
        $importPupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $pupil = $importPupilService->saveProfilePupil($data);
        $this->assertNotNull($pupil);
    }

    public function testFunctionGetResultIbaScore()
    {
        $data = $this->getData();
        $importPupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $ibaScore = $importPupilService->saveResultIbaScore($data);
        $this->assertNotNull($ibaScore);
    }

    public function testFunctionGetResultEikenScore()
    {
        $data = $this->getData();
        $importPupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $eikenScore = $importPupilService->saveResultEikenScore($data);
        $this->assertNotNull($eikenScore);
    }

    public function testFunctionGetSimpleMeasurementResult()
    {
        $data = $this->getData();
        $importPupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $simpleMeasuremenResult = $importPupilService->saveSimpleMeasurementResult($data);
        $this->assertNotNull($simpleMeasuremenResult);
    }

    public function testFunctionGetExamDateByYearAndKaiAndEikenLevelOfPupilService()
    {
        $data = $this->getData();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\PupilServiceInterface');
        $examDate = $pupilService->getExamDateByYearAndKaiAndEikenLevel($data['eikenYear'], $data['kai'], $data['eikenLevel']);
        $this->assertNotNull($examDate);
    }

    public function getData()
    {
        return Array(
            'year'                => 2015,
            'schoolYear'          => '',
            'orgSchoolYear'       => '中学1年生',
            'class'               => '１組',
            'pupilNumber'         => 103,
            'firstnameKanji'      => '田中',
            'lastnameKanji'       => '次郎',
            'firstnameKana'       => 'タナカ',
            'lastnameKana'        => 'ジロウ',
            'birthday'            => '2000/01/02',
            'gender'              => '男',
            'einaviId'            => 2,
            'eikenId'             => 1,
            'eikenPassword'       => '',
            'eikenLevel'          => '2級',
            'eikenYear'           => 2015,
            'kai'                 => 2,
            'eikenScoreReading'   => 3,
            'eikenScoreListening' => 2,
            'eikenScoreWriting'   => 3,
            'eikenScoreSpeaking'  => 4,
            'ibaLevel'            => '2級',
            'ibaDate'             => '2015-12-02',
            'ibaScoreReading'     => 200,
            'ibaScoreListening'   => 200,
            'wordLevel'           => '2級',
            'grammarLevel'        => '2級'
        );
    }

    public function getDataList()
    {
        $array = Array(
            'year'                => 2015,
            'schoolYear'          => '',
            'orgSchoolYear'       => '中学1年生',
            'class'               => '１組',
            'pupilNumber'         => 103,
            'firstnameKanji'      => '田中',
            'lastnameKanji'       => '次郎',
            'firstnameKana'       => 'タナカ',
            'lastnameKana'        => 'ジロウ',
            'birthday'            => '2000/01/02',
            'gender'              => '男',
            'einaviId'            => 2,
            'eikenId'             => 1,
            'eikenPassword'       => '',
            'eikenLevel'          => '2級',
            'eikenYear'           => 2015,
            'kai'                 => 2,
            'eikenScoreReading'   => 3,
            'eikenScoreListening' => 2,
            'eikenScoreWriting'   => 3,
            'eikenScoreSpeaking'  => 4,
            'ibaLevel'            => '2級',
            'ibaDate'             => '2015-12-02',
            'ibaScoreReading'     => 200,
            'ibaScoreListening'   => 200,
            'wordLevel'           => '2級',
            'grammarLevel'        => '2級'
        );
        $result = array();
       // $result[] = $this->getData();
        for ($i = 0; $i < 10; $i++) {
            $result[] = $array;
        }


        return $result;
    }
}