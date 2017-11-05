<?php
namespace History;
use Dantai\Test\AbstractHttpControllerTestCase;
use Dantai\Utility\MappingUtility;
class HistoryUtilityTest extends AbstractHttpControllerTestCase {
    
    private $ibaScoreMasterData = array(
            1 => array(
                'type' => 'TOTAL',
                'testSet' => 'A',
                'scoreRangeFrom' => 0,
                'scoreRangeTo' => 418,
                'ibaLevelName' => '５級受験',
            ),
            2 => array(
                'type' => 'TOTAL',
                'testSet' => 'B',
                'scoreRangeFrom' => 419,
                'scoreRangeTo' => 621,
                'ibaLevelName' => '５級',
            ),
            3=>array(
                'type' => 'TOTAL',
                'testSet' => 'C',
                'scoreRangeFrom' => 1014,
                'scoreRangeTo' => 1100,
                'ibaLevelName' =>'２級',
            ),
            4=>array(
                'type' => 'TOTAL',
                'testSet' => 'D',
                'scoreRangeFrom' => 878,
                'scoreRangeTo' => 1000,
                'ibaLevelName' => '準２級',
            ),
            5=>array(
                'type' => 'TOTAL',
                'testSet' => 'E',
                'scoreRangeFrom' => 622,
                'scoreRangeTo' => 745,
                'ibaLevelName' => '４級',
            ),
            6=>array(
                'type' => 'TOTAL',
                'testSet' => 'F',
                'scoreRangeFrom' => 501,
                'scoreRangeTo' => 600,
                'ibaLevelName' => '５級',
            ),
            7 => array(
                'type' => 'READING',
                'testSet' => 'A',
                'scoreRangeFrom' => 0,
                'scoreRangeTo' => 117,
                'ibaLevelName' => '５級受験',
            ),
            8 => array(
                'type' => 'READING',
                'testSet' => 'B',
                'scoreRangeFrom' => 236,
                'scoreRangeTo' => 282,
                'ibaLevelName' => '5級',
            ),
            9=>array(
                'type' => 'READING',
                'testSet' => 'C',
                'scoreRangeFrom' => 330,
                'scoreRangeTo' => 358,
                'ibaLevelName' =>'４級',
            ),
            10=>array(
                'type' => 'READING',
                'testSet' => 'D',
                'scoreRangeFrom' => 330,
                'scoreRangeTo' => 358,
                'ibaLevelName' => '４級',
            ),
            11=>array(
                'type' => 'READING',
                'testSet' => 'E',
                'scoreRangeFrom' => 387,
                'scoreRangeTo' => 400,
                'ibaLevelName' => '３級',
            ),
            12=>array(
                'type' => 'READING',
                'testSet' => 'F',
                'scoreRangeFrom' => 291,
                'scoreRangeTo' => 300,
                'ibaLevelName' => '５級',
            ),
            13 => array(
                'type' => 'LISTENING',
                'testSet' => 'A',
                'scoreRangeFrom' => 690,
                'scoreRangeTo' => 720,
                'ibaLevelName' => '準１級',
            ),
            14 => array(
                'type' => 'LISTENING',
                'testSet' => 'B',
                'scoreRangeFrom' => 0,
                'scoreRangeTo' => 91,
                'ibaLevelName' => '５級受験',
            ),
            15=>array(
                'type' => 'LISTENING',
                'testSet' => 'C',
                'scoreRangeFrom' => 430,
                'scoreRangeTo' => 466,
                'ibaLevelName' =>'準２級',
            ),
            16=>array(
                'type' => 'LISTENING',
                'testSet' => 'D',
                'scoreRangeFrom' => 359,
                'scoreRangeTo' => 394,
                'ibaLevelName' => '３級',
            ),
            17=>array(
                'type' => 'LISTENING',
                'testSet' => 'E',
                'scoreRangeFrom' => 326,
                'scoreRangeTo' => 358,
                'ibaLevelName' => '４級',
            ),
            18=>array(
                'type' => 'LISTENING',
                'testSet' => 'F',
                'scoreRangeFrom' => 281,
                'scoreRangeTo' => 300,
                'ibaLevelName' => '５級',
            )
        
    );
    
    private $ibaScoreMasterDataTotal = array(
            1 => array(
                'type' => 'TOTAL',
                'testSet' => 'A',
                'score' => 0,
                'ibaLevelName' => '５級受験',
            ),
            2 => array(
                'type' => 'TOTAL',
                'testSet' => 'B',
                'score' => 420,
                'ibaLevelName' => '５級',
            ),
            3=>array(
                'type' => 'TOTAL',
                'testSet' => 'C',
                'score' => 1100,
                'ibaLevelName' =>'２級',
            ),
            4=>array(
                'type' => 'TOTAL',
                'testSet' => 'D',
                'score' => 1000,
                'ibaLevelName' => '準２級',
            ),
            5=>array(
                'type' => 'TOTAL',
                'testSet' => 'E',
                'score' => 625,
                'ibaLevelName' => '４級',
            ),
            6=>array(
                'type' => 'TOTAL',
                'testSet' => 'F',
                'score' => 600,
                'ibaLevelName' => '５級',
            ),
        
        );
        
    private $ibaScoreMasterDataReading = array(
            1 => array(
                'type' => 'READING',
                'testSet' => 'A',
                'score' => 0,
                'ibaLevelName' => '５級受験',
            ),
            2 => array(
                'type' => 'READING',
                'testSet' => 'B',
                'score' => 240,
                'ibaLevelName' => '5級',
            ),
            3=>array(
                'type' => 'READING',
                'testSet' => 'C',
                'score' => 350,
                'ibaLevelName' =>'４級',
            ),
            4=>array(
                'type' => 'READING',
                'testSet' => 'D',
                'score' => 340,
                'ibaLevelName' => '４級',
            ),
            5=>array(
                'type' => 'READING',
                'testSet' => 'E',
                'score' => 400,
                'ibaLevelName' => '３級',
            ),
            6=>array(
                'type' => 'READING',
                'testSet' => 'F',
                'score' => 300,
                'ibaLevelName' => '５級',
            )
        );
    private $ibaScoreMasterDataListening = array(
            1 => array(
                'type' => 'LISTENING',
                'testSet' => 'A',
                'score' => 720,
                'ibaLevelName' => '準１級',
            ),
            2 => array(
                'type' => 'LISTENING',
                'testSet' => 'B',
                'score' => 0,
                'ibaLevelName' => '５級受験',
            ),
            3=>array(
                'type' => 'LISTENING',
                'testSet' => 'C',
                'score' => 450,
                'ibaLevelName' =>'準２級',
            ),
            4=>array(
                'type' => 'LISTENING',
                'testSet' => 'D',
                'score' => 360,
                'ibaLevelName' => '３級',
            ),
            5=>array(
                'type' => 'LISTENING',
                'testSet' => 'E',
                'score' => 350,
                'ibaLevelName' => '４級',
            ),
            6=>array(
                'type' => 'LISTENING',
                'testSet' => 'F',
                'score' => 300,
                'ibaLevelName' => '５級',
            )
        );
    public function testGetKyuName(){
        
        // assert equals between ibaLevelName and return of getKyuName have type : TOTAL, testSet(A, B, C, D, E, F)
        $this->assertEquals($this->ibaScoreMasterDataTotal[1]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataTotal[1]['type'],$this->ibaScoreMasterDataTotal[1]['testSet'], $this->ibaScoreMasterDataTotal[1]['score']));
        $this->assertEquals($this->ibaScoreMasterDataTotal[2]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataTotal[2]['type'],$this->ibaScoreMasterDataTotal[2]['testSet'], $this->ibaScoreMasterDataTotal[2]['score']));
        $this->assertEquals($this->ibaScoreMasterDataTotal[3]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataTotal[3]['type'],$this->ibaScoreMasterDataTotal[3]['testSet'], $this->ibaScoreMasterDataTotal[3]['score']));
        $this->assertEquals($this->ibaScoreMasterDataTotal[4]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataTotal[4]['type'],$this->ibaScoreMasterDataTotal[4]['testSet'], $this->ibaScoreMasterDataTotal[4]['score']));
        $this->assertEquals($this->ibaScoreMasterDataTotal[5]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataTotal[5]['type'],$this->ibaScoreMasterDataTotal[5]['testSet'], $this->ibaScoreMasterDataTotal[5]['score']));
        $this->assertEquals($this->ibaScoreMasterDataTotal[6]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataTotal[6]['type'],$this->ibaScoreMasterDataTotal[6]['testSet'], $this->ibaScoreMasterDataTotal[6]['score']));

        // assert equals between ibaLevelName and return of getKyuName have type : READING, testSet(A, B, C, D, E, F)
        $this->assertEquals($this->ibaScoreMasterDataReading[1]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataReading[1]['type'],$this->ibaScoreMasterDataReading[1]['testSet'], $this->ibaScoreMasterDataReading[1]['score']));
        $this->assertEquals($this->ibaScoreMasterDataReading[2]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataReading[2]['type'],$this->ibaScoreMasterDataReading[2]['testSet'], $this->ibaScoreMasterDataReading[2]['score']));
        $this->assertEquals($this->ibaScoreMasterDataReading[3]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataReading[3]['type'],$this->ibaScoreMasterDataReading[3]['testSet'], $this->ibaScoreMasterDataReading[3]['score']));
        $this->assertEquals($this->ibaScoreMasterDataReading[4]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataReading[4]['type'],$this->ibaScoreMasterDataReading[4]['testSet'], $this->ibaScoreMasterDataReading[4]['score']));
        $this->assertEquals($this->ibaScoreMasterDataReading[5]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataReading[5]['type'],$this->ibaScoreMasterDataReading[5]['testSet'], $this->ibaScoreMasterDataReading[5]['score']));
        $this->assertEquals($this->ibaScoreMasterDataReading[6]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataReading[6]['type'],$this->ibaScoreMasterDataReading[6]['testSet'], $this->ibaScoreMasterDataReading[6]['score']));
        
        // assert equals between ibaLevelName and return of getKyuName have type : LISTENING, testSet(A, B, C, D, E, F)
        $this->assertEquals($this->ibaScoreMasterDataListening[1]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataListening[1]['type'],$this->ibaScoreMasterDataListening[1]['testSet'], $this->ibaScoreMasterDataListening[1]['score']));
        $this->assertEquals($this->ibaScoreMasterDataListening[2]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataListening[2]['type'],$this->ibaScoreMasterDataListening[2]['testSet'], $this->ibaScoreMasterDataListening[2]['score']));
        $this->assertEquals($this->ibaScoreMasterDataListening[3]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataListening[3]['type'],$this->ibaScoreMasterDataListening[3]['testSet'], $this->ibaScoreMasterDataListening[3]['score']));
        $this->assertEquals($this->ibaScoreMasterDataListening[4]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataListening[4]['type'],$this->ibaScoreMasterDataListening[4]['testSet'], $this->ibaScoreMasterDataListening[4]['score']));
        $this->assertEquals($this->ibaScoreMasterDataListening[5]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataListening[5]['type'],$this->ibaScoreMasterDataListening[5]['testSet'], $this->ibaScoreMasterDataListening[5]['score']));
        $this->assertEquals($this->ibaScoreMasterDataListening[6]['ibaLevelName'], MappingUtility::getKyuName($this->ibaScoreMasterData,$this->ibaScoreMasterDataListening[6]['type'],$this->ibaScoreMasterDataListening[6]['testSet'], $this->ibaScoreMasterDataListening[6]['score']));
        
    }
}