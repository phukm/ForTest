<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Satellite\View\Helper;

class To1ByteNumber extends \Zend\View\Helper\AbstractHelper
{

    protected $keyMap = array(
        '０' => '0',
        '１' => '1',
        '２' => '2',
        '３' => '3',
        '４' => '4',
        '５' => '5',
        '６' => '6',
        '７' => '7',
        '８' => '8',
        '９' => '9',
        '0' => '0',
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
        '6' => '6',
        '7' => '7',
        '8' => '8',
        '9' => '9'
    );

    public function __invoke($str)
    {
        $len = mb_strlen($str, 'UTF-8');
        $res = '';
        for ($i = 0; $i < $len; $i ++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');
            if (array_key_exists($char, $this->keyMap)) {
                $res .= $this->keyMap[$char];
            }
        }
        return $res;
    }
}
