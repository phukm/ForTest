<?php
namespace ConsoleInvitation\View\Helper;

class ConvertWdayJapan extends \Zend\View\Helper\AbstractHelper {
    
    public function __invoke($day) {
        switch ($day) {
            case 'Fri':
                $dayJapanese = '金';
                break;
            case 'Sat':
                $dayJapanese = '土';
                break;
            case 'Sun':
                $dayJapanese = '日';
                break;
            case 'Mon':
                $dayJapanese = '月';
                break;
            case 'Tue':
                $dayJapanese = '火';
                break;
            case 'Wed':
                $dayJapanese = '水';
                break;
            case 'Thu':
                $dayJapanese = '木';
                break;
            default:
                $dayJapanese = '';
                break;
        }
        return $dayJapanese;
    }
}
