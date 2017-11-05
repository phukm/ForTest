<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Dantai\Utility;

/**
 * Charset converter helper
 * A helper class to connect convert
 */
class CharsetConverter
{

    /**
     * Convert string from UTF-8 to Shift_JIS encoding
     *
     * @param string $fromUtf8            
     */
    public static function utf8ToShiftJis($fromUtf8 = '')
    {
        return mb_convert_encoding($fromUtf8, 'SJIS', 'UTF-8');
    }

    /**
     * Convert string from Shift_JIS to UTF-8 encoding
     *
     * @param string $fromShiftJis            
     * @return string
     */
    public static function shiftJisToUtf8($fromShiftJis = '')
    {
        $encodingDetected = mb_detect_encoding($fromShiftJis, 'JIS,SJIS,sjis-win,eucjp-win');
        
        if ($encodingDetected === false) {
            return $fromShiftJis;
        }
        
        return mb_convert_encoding($fromShiftJis, 'UTF-8', $encodingDetected);
    }

    /**
     * Convert string from any charset to UTF-8 encoding
     *
     * @param string $fromAuto            
     */
    public static function toUtf8($fromAuto = '')
    {
        return mb_convert_encoding($fromAuto, 'UTF-8', 'auto');
    }

    /**
     * Convert string from any charset to Shift_JIS encoding
     *
     * @param string $fromAuto            
     */
    public static function toShiftJis($fromAuto = '')
    {
        return mb_convert_encoding($fromAuto, 'SJIS', 'auto');
    }

    /**
     * check validate hiragana
     * @date 2015.07.23
     *
     * @return boolean
     */
    public static function checkHiragana($check, $allowSpace = true)
    {
        if ($allowSpace) {
            $pattern = '/^(\xe3(\x80\x80|\x81[\x81-\xbf]|\x82[\x80-\x96]|\x83\xbc))*$/';
        } else {
            $pattern = '/^(\xe3(\x81[\x81-\xbf]|\x82[\x80-\x96]|\x83\xbc))*$/';
        }
        return preg_match($pattern, $check) !== false;
    }

    /**
     * check validate only accept katakana
     * @date 2015.07.23
     *
     * @return boolean
     */
    public static function checkKatakana($check, $allowSpace = true)
    {
        if ($allowSpace) {
            $pattern = '/^(\xe3(\x80\x80|\x82[\xa1-\xbf]|\x83[\x80-\xb6]|\x83\xbc))*$/';
        } else {
            $pattern = '/^(\xe3(\x82[\xa1-\xbf]|\x83[\x80-\xb6]|\x83\xbc))*$/';
        }
        return preg_match($pattern, $check) !== false;
    }

    /**
     * Check input string whether is Full size or not
     * Return true if is full size
     *
     * @param string $fullSize            
     * @return boolean
     */
    public static function checkFullSize($fullSize)
    {
        
        //return preg_match("/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7E]/u", $fullSize) === false;
        return preg_match("/(?:[\x{30A0}-\x{30FF}]|[\x{FF01}-\x{FF5E}])/u", $fullSize);
    }

    /**
     * Check input string whether is Half size or not
     * Return true if is half size
     *
     * @param string $halfSize            
     * @return boolean
     */
    public static function checkHalfSize($halfSize)
    {
        //return preg_match("/(?:[\x{30A0}-\x{30FF}]|[\x{FF01}-\x{FF5E}])/u", $fullSize) !== false;
        return preg_match("/(?:[\x{ff5F}-\x{ff9F}])/u", $halfSize);
    }
}