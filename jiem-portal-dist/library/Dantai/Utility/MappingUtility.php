<?php

namespace Dantai\Utility;

class MappingUtility {

    public static function getKyuName($arrayIbaMasterData, $type, $testSet, $score) {
        $score = $score ? $score : 0;
        if (!empty($arrayIbaMasterData)) {
            foreach ($arrayIbaMasterData as $ibaMasterData => $value) {
                if ($value['type'] == $type && $value['testSet'] == $testSet && $value['scoreRangeFrom'] <= $score && $value['scoreRangeTo'] >= $score)
                    return $value['ibaLevelName'];
            }
        }
        return '';
    }
}
