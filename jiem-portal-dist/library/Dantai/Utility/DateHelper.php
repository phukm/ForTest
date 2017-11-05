<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Dantai\Utility;

use DateTime;
use Zend\View\Helper\AbstractHelper;
/**
 * Date helper
 * A helper class to process date, day, month, year
 */
class DateHelper extends AbstractHelper
{
    const DATETIME_FORMAT_MYSQL_DEFAULT = 'Y-m-d H:i:s';
    const DATE_FORMAT_MYSQL_DEFAULT = 'Y-m-d';
    const DATETIME_FORMAT_DEFAULT = 'Y/m/d H:i:s';
    const DATE_FORMAT_EXPORT_EXCEL = 'Ymd';
    const DATE_FORMAT_DEFAULT = 'Y/m/d';
    const TIME_FORMAT_EXPORT_EXCEL = 'Hi';

    public function __invoke($objDatetime, $format = false)
    {
        return $this->convert2Datetime($objDatetime, $format);
    }
    /**
     * list all year in period of time
     *
     * @param interger $yearFrom
     * @param interger $yearTo
     * @param interger $sort
     */
    public static function getListYearToSearch($yearFrom = 0, $yearTo = 0, $sort = 0){
        $listYear = array();

        $yearFrom = ($yearFrom == 0) ? 2010 : intval($yearFrom);
        $yearTo = ($yearTo == 0) ? (date('Y') + 2) : intval($yearTo);

        if($sort == 0){
            for($i = $yearTo; $i  >= $yearFrom; $i--){
                $listYear[] = $i;
            }
        }else{
            for($i = $yearFrom; $i  <= $yearTo; $i++){
                $listYear[] = $i;
            }
        }
        return $listYear;
    }

    /**
     * @param object $objDatetime
     * @param string $format
     * @return string
     * Convert object from DB to string format
     */
    public function convert2Datetime ($objDatetime, $format = false)
    {
        if ($objDatetime)
        {
            return $objDatetime->format($format? $format:self::DATE_FORMAT_DEFAULT);
        }
        return '';
    }
     public function getCurrentYear()
    {
        $curMonth =(int)date('m');
        $curYear = (int)date('Y');

        if ($curMonth <4) {
           return $curYear-1;
        }
        return $curYear;
    }
    /**
     *
     */
    public static function gengo($value)
    {
        // tinh nam shouwa ex: value - 1925
        if ($value >= 1911 && $value <= 1925) {
            $no = $value - 1911;

            return $value . "(大正" . $no . ")";
        }

        // tinh nam shouwa ex: value - 1925
        if ($value > 1925 && $value <= 1988) {
            $no = $value - 1925;

            return $value . "(昭和" . $no . ")";
        }
        // tinh nam heisei
        if ($value >= 1989) {
            $no = $value - 1988;

            return $value . "(平成" . $no . ")";
        }

        return $value;
    }

    /**
     * convert japanese date format to Mysql date form
     * ex: convert H131117 to 2001-11-17, Date time may contain H or S
     * This function is not true if H100, after 87 year :), no need to check
     * @author FPT-DuongTD
     */
    public static function convertJapaneseDatetoMysqlFormat( $japanDate = ''){
        //validate japanese date
        $regexFormat = '/^H[\d]{6}$|^S[\d]{6}$/'; //date time missing Zero in the following example: H13 9 3
        if(!preg_match_all($regexFormat, $japanDate)){
            return null;
        }
        //seperate japanese to Year, Mon, Day
        if(preg_match_all('/^H[\d]{6}$/', $japanDate)){
            $yearNo = 1988 + substr($japanDate, 1, 2);
        } else {
            //昭和 thì chạy từ năm 1926 -> 1988
            $yearNo = 1925 + substr($japanDate, 1, 2);
        }
        $month = substr($japanDate, 3, 2);
        $day = substr($japanDate, 5, 2);
        if(!checkdate($month,$day,$yearNo)){
            return null;
        }
        return $yearNo . '-' . $month . '-' . $day . ' 00:00:00';

    }
    
    /*
     * check format date is Y/m/d
     * @param date $date (Y/m/d)
     * @return boolean
     */
    public static function isDateFormatYmd($date) {
        if (!preg_match("/^\d{4}[\/]\d{2}[\/]\d{2}$/", $date)) {
            return false;
        }
        $arrDate = explode('/', $date);
        $dtYear = intval($arrDate[0]);
        $dtMonth = intval($arrDate[1]);
        $dtDay = intval($arrDate[2]);
        if ($dtMonth < 1 || $dtMonth > 12) {
            return false;
        }
        if ($dtDay < 1 || $dtDay > 31) {
            return false;
        }
        if (($dtMonth == 4 || $dtMonth == 6 || $dtMonth == 9 || $dtMonth == 11) && $dtDay == 31) {
            return false;
        }
        if($dtMonth == 2){
            $check = ($dtYear % 4 == 0 && ($dtYear % 100 != 0 || $dtYear % 400 == 0));
            if ($dtDay > 29 || ($dtDay == 29 && !$check)) {
                return false;
            }
        }
        return true;
    }

    /**
     * validate string data is correct format
     * @param $date
     * @param $format
     * @return bool
     */
    public static function isDateFormat($date, $format){
        return DateTime::createFromFormat($format, $date) !== false;
    }

}