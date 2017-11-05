<?php
/**
 * PHPExcel
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
namespace Dantai\Utility;
use PHPExcel_Style_NumberFormat;

class PHPExcel
{
    const EXCEL_NUMERIC = 1;
    const EXCEL_CURRENCY = 2;

    /**
     *
     * @param array $data
     * @param string $fileName
     * @param string $template
     */
    public static function export(array $data, $fileName = 'export.xlsx', $template = 'default',$startRow = 2, $outputPath = '', $extension = 'xlsx', $format_columns = array(), $isSetBorder = false)
    {
        $fileTemplate = DATA_PATH .'/exportExcel/templates/' . $template . '.' . $extension;
        $objReader = \PHPExcel_IOFactory::createReader($extension == 'xls' ? 'Excel5' : 'Excel2007');
        $objPHPExcel = $objReader->load($fileTemplate);
        if(!empty($data)) {
            $rowIndex = $startRow;
            foreach($data as $rowData) {
                self::fillRowData($objPHPExcel, $rowData, $rowIndex, $format_columns, $isSetBorder);
                $rowIndex++;
            }
        }
        /** at this point, we could do some manipulations with the template, but we skip this step */
        // Export to Excel2007 (.xlsx)        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, $extension == 'xls' ? 'Excel5' : 'Excel2007');
        ob_end_clean();
        // Redirect output to a client’s web browser (Excel2007)
        $contentType = $extension == 'xls' ? 'application/vnd.ms-excel' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        if($outputPath == ''){
            $outputPath = 'php://output';
        }
        
        return $objWriter->save($outputPath);
    }

    protected static function fillRowData($objPHPExcel,$rowData,$rowIndex, $format_columns, $isSetBorder){
        if(empty($rowData) || empty($objPHPExcel)){
            return;
        }
        $colIndex = 0;
        foreach($rowData as $value) {
            $column = \PHPExcel_Cell::stringFromColumnIndex($colIndex);
            if(array_key_exists($column, $format_columns) && $value !== '' && $value !== null){
                switch ($format_columns[$column]){
                    case PHPExcel::EXCEL_NUMERIC:
                        $objPHPExcel->getActiveSheet()
                            ->getStyle(\PHPExcel_Cell::stringFromColumnIndex($colIndex) . $rowIndex)
                            ->getNumberFormat()
                            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueExplicit(\PHPExcel_Cell::stringFromColumnIndex($colIndex) . $rowIndex, $value, \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        break;
                    case PHPExcel::EXCEL_CURRENCY:
                        $objPHPExcel->getActiveSheet()
                            ->getStyle(\PHPExcel_Cell::stringFromColumnIndex($colIndex) . $rowIndex)
                            ->getAlignment()
                            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $objPHPExcel->getActiveSheet()
                            ->setCellValueExplicit(\PHPExcel_Cell::stringFromColumnIndex($colIndex) . $rowIndex, $value);
                        break;
                }
            }else{
                $objPHPExcel->getActiveSheet()
                    ->setCellValueExplicit(\PHPExcel_Cell::stringFromColumnIndex($colIndex) . $rowIndex, $value);
            }
            if($isSetBorder){
                $objPHPExcel->getActiveSheet()
                    ->getStyle(\PHPExcel_Cell::stringFromColumnIndex($colIndex) . $rowIndex)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN)
                    ->getColor()
                    ->setRGB('000000');
            }

            $colIndex++; //Next Column will Be B, C, D....
        }
    }
    
    public static function excelToArray($filePath) {
        //Create excel reader after determining the file type
        $inputFileName = $filePath;
        /**  Identify the type of $inputFileName  * */
        $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
        /**  Create a new Reader of the type that has been identified  * */
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        /** Set read type to read cell data onl * */
        //$objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($inputFileName);
//        $sheet = $objPHPExcel->getSheet(0);    
//        $highestRow = $sheet->getHighestRow();
//        $highestColumn = $sheet->getHighestColumn();
//        return $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, NULL, FALSE, FALSE);
        
//        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet = $objPHPExcel->getSheet(0);
        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = \PHPExcel_Cell::columnIndexFromString($activeSheet->getHighestColumn());
        $dataFromExcel = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumn; $col++) {
                $cell = $activeSheet->getCellByColumnAndRow($col, $row);
                $dataFromExcel[($row - 1)][$col] = $cell->getValue();
                if (\PHPExcel_Shared_Date::isDateTime($cell) && is_numeric($cell->getValue())) {
                    $date = \PHPExcel_Shared_Date::ExcelToPHPObject($cell->getValue());
                    $dataFromExcel[($row - 1)][$col] = $date->format('Y/m/d');
                }
            }
        }
        return $dataFromExcel;
    }

}
