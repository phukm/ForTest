<?php
namespace Eiken\Helper;

use Zend\View\Helper\AbstractHelper;

class EikenCommon extends AbstractHelper
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
}