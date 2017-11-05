<?php

namespace Dantai\Utility;

class CsvHelper{
    const EXTENSION = ".csv";

    public static function isCsvExtension($filename){
        return substr($filename, -4) == CsvHelper::EXTENSION;
    }

    public static function saveArrayToCsvFile($array, $filePath,$delimiter = ',',$enclosure = '"'){
        $handle = fopen($filePath, 'w');
        
        foreach ($array as $row){
            fputcsv($handle, $row,$delimiter,$enclosure);
        }
        
        fclose($handle);
    }
    
    public static function arrayToStrCsv($array,$delimiter = ',',$enclosure = '"'){
        $handle = fopen('php://memory', 'r+');
        foreach ($array as $row){
            fputcsv($handle, $row,$delimiter,$enclosure);
        }
        rewind($handle);
        $strCsv = stream_get_contents($handle);
        fclose($handle);
        return $strCsv;
    }
    
    public static function csvStrToArray($input, $delimiter = ",", $enclosure = '"', $escape = "\\"){
        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $input);
        rewind($handle);
        
        $rows = array();
        while (!feof($handle) ) {
            $row = fgetcsv($handle, 10240,$delimiter, $enclosure, $escape);
            if(!$row || empty($row)){
                continue;
            }
            $rows[] = $row;
        }
        fclose($handle);
        
        return $rows;
    }

    public static function csvFileToArray($file,$removeHeader=false,$numOfHeaderLine=1){
        $rows = array();
        if(!file_exists($file) || !is_readable($file)){
            throw new Exception($file.' doesn`t exist or is not readable.');
        }
        
        $handle = fopen($file, 'r');
        $currentLine = 0;
        while (!feof($handle) ) {
            $row = fgetcsv($handle, 10240);
            $currentLine++;
            if($removeHeader && $currentLine <= $numOfHeaderLine){
                continue;
            }
            $rows[] = $row;
        }
        fclose($handle);
        
        return $rows;
    }

    /**
     * @param $data
     * @param $headers
     * @return array
     * @throws \Exception
     */
    public static function convertCSVArrayToAssociateArray($data, $headers){
        $result = array();
        for ($i = 1; $i < count($data); $i++) {
            if (count($data[$i]) !== count($headers)) throw new \Exception('length of header and number column aren\'t equal');
            $row = array();
            for ($k = 0; $k < count($headers); $k++) {
                $row[$headers[$k]] = $data[$i][$k];
            }
            $result[] = $row;
        }

        return $result;
    }

    /**
     * @param $data
     * @param $headers
     * @return array
     * @throws \Exception
     */
    public static function convertAssociateArrayToCSVArray($data, $headers){
        $result = array();
        $result[] = $headers;
        for ($i = 0; $i < count($data); $i++) {
            if (count($data[$i]) !== count($headers)) throw new \Exception('length of header and number column aren\'t equal');
            $row = array_values($data[$i]);
            for ($k = 0; $k < count($row); $k++) {
                $row[$k] = !is_a($row[$k], 'Datetime') ? $row[$k]
                    : $row[$k]->format(DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
            }
            $result[] = $row;
        }

        return $result;
    }
}