<?php
namespace Satellite\View\Helper;
use Zend\View\Helper\AbstractHelper;

class SatelliteCommon extends AbstractHelper
{
    static function generateSelectOptions ($entinies, $nameGetter)
    {
        $options = array('' => '');
        if (!empty($entinies))
            foreach ($entinies as $entinie)
            {
                $options[$entinie->getId()] = $entinie->{$nameGetter}();
            }
        return $options;
    }
    
    static function generateSelectOptionsWithCustomValue ($entinies, $nameSetter, $nameGetter)
    {
        $options = array('' => '');
        if (!empty($entinies))
            foreach ($entinies as $entinie)
            {
                $options[$entinie->{$nameSetter}()] = $entinie->{$nameGetter}();
            }
        return $options;
    }
}