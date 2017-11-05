<?php

namespace PupilMnt;

use Dantai\PrivateSession;
use PupilMnt\PupilConst;

class ExportPupilServiceTest extends \Dantai\Test\AbstractHttpControllerTestCase
{
    public function testReturnCorrectArrayWhenPutParamToFunctionMapArrayPupilExport(){
        for ($i = 0; $i < 10; $i++) {
            $dataPupils[$i]['id'] = $i+1;
            $dataPupils[$i]['schoolYearId'] = 1;
            $dataPupils[$i]['year'] = date('Y');
            $dataPupils[$i]['displayName'] = '小学1年生';
            $dataPupils[$i]['className'] = '小学1年生';
            $dataPupils[$i]['number'] = $i+1;
            $dataPupils[$i]['firstNameKanji'] = '寺百';
            $dataPupils[$i]['lastNameKanji'] = '出寺';
            $dataPupils[$i]['firstNameKana'] = 'カナ';
            $dataPupils[$i]['lastNameKana'] = 'オオ';
            $dataPupils[$i]['birthday'] = new \DateTime('now');
            $dataPupils[$i]['gender'] = 1;
            $dataPupils[$i]['einaviId'] = '';
            $dataPupils[$i]['eikenId'] = '';
            $dataPupils[$i]['eikenPassword'] = '';
            $dataPupils[$i]['resultVocabularyName'] = '準2級';
            $dataPupils[$i]['resultGrammarName'] = '5級';
        }

        for ($i = 1; $i <= 5; $i++) {
            $eikenScores[$i]['eikenLevelId'] = rand(1, 7);
            $eikenScores[$i]['year'] = date('Y');
            $eikenScores[$i]['kai'] = rand(1, 3);
            $eikenScores[$i]['readingScore'] = 200;
            $eikenScores[$i]['listeningScore'] = 300;
            $eikenScores[$i]['cSEScoreWriting'] = 400;
            $eikenScores[$i]['cSEScoreSpeaking'] = 500;
        }
        for ($i = 1; $i <= 5; $i++) {
            $ibaScores[$i]['eikenLevelId'] = rand(1, 7);
            $ibaScores[$i]['examDate'] = new \DateTime('now');
            $ibaScores[$i]['readingScore'] = 222;
            $ibaScores[$i]['listeningScore'] = 333;
        }
        $schoolYears[1]['name'] = '小学1年生';
        
        /*@var $importPupilService \PupilMnt\Service\ImportPupilService*/
        $importPupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        /*@var $exportPupilService \PupilMnt\Service\ExportPupilService*/
        $exportPupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ExportPupilServiceInterface');
        
        
        $eikenLevels = $importPupilService->getArrayEikenLevel();
        
        $result = $exportPupilService->mapArrayPupilExport($dataPupils, $eikenScores, $ibaScores, $schoolYears);
        

        $this->assertEquals(10, count($result));
        for($i = 1; $i <= 10; $i++){
            $this->assertEquals(26, count($result[$i-1]));
            
            $eikenLevelId =  isset($eikenScores[$i]['eikenLevelId']) ? $eikenScores[$i]['eikenLevelId'] : 0;
            $eikenLevelName = isset($eikenLevels[$eikenLevelId]) ? $eikenLevels[$eikenLevelId] : '';
            $eikenKai = isset($eikenScores[$i]) ? $eikenScores[$i]['kai'] : '';
            $eikenReadingScore = isset($eikenScores[$i]) ? $eikenScores[$i]['readingScore'] : '';
            $eikenListeningScore = isset($eikenScores[$i]) ? $eikenScores[$i]['listeningScore'] : '';
            $eikenWritingScore = isset($eikenScores[$i]) ? $eikenScores[$i]['cSEScoreWriting'] : '';
            $eikenSpeakingScore = isset($eikenScores[$i]) ? $eikenScores[$i]['cSEScoreSpeaking'] : '';
            
            $this->assertEquals($eikenLevelName, $result[$i-1]['eikenLevel']);
            $this->assertEquals($eikenKai, $result[$i-1]['kai']);
            $this->assertEquals($eikenReadingScore, $result[$i-1]['eikenScoreReading']);
            $this->assertEquals($eikenListeningScore, $result[$i-1]['eikenScoreListening']);
            $this->assertEquals($eikenWritingScore, $result[$i-1]['eikenScoreWriting']);
            $this->assertEquals($eikenSpeakingScore, $result[$i-1]['eikenScoreSpeaking']);
            
            $ibaLevelId =  isset($ibaScores[$i]['eikenLevelId']) ? $ibaScores[$i]['eikenLevelId'] : 0;
            $ibaLevelName = isset($eikenLevels[$ibaLevelId]) ? $eikenLevels[$ibaLevelId] : '';
            $ibaDate = isset($ibaScores[$i]) ? $ibaScores[$i]['examDate']->format('Y/m/d') : '';
            $ibaReadingScore = isset($ibaScores[$i]) ? $ibaScores[$i]['readingScore'] : '';
            $ibaListeningScore = isset($ibaScores[$i]) ? $ibaScores[$i]['listeningScore'] : '';
            
            $this->assertEquals($ibaLevelName, $result[$i-1]['ibaLevel']);
            $this->assertEquals($ibaDate, $result[$i-1]['ibaDate']);
            $this->assertEquals($ibaReadingScore, $result[$i-1]['ibaScoreReading']);
            $this->assertEquals($ibaListeningScore, $result[$i-1]['ibaScoreListening']);
        }
    }
}