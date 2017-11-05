<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Satellite\View\Helper;

class CallIfCallable extends \Zend\View\Helper\AbstractHelper
{

    public function __invoke($source, $callMethod)
    {
        if (! is_array($callMethod)) {
            $callMethod = array(
                $callMethod
            );
        }
        foreach ($callMethod as $method) {
            if (!method_exists($source, $method)) {
                return (gettype($source) == 'object') ? null : $source;
            }
            $source = $source->{$method}();
        }
        return $source;
    }
}
