<?php
namespace Satellite\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\PhpEnvironment\Request;

abstract class BaseController extends AbstractActionController
{

    public $isMobile = false;
    protected $request;

    public function __construct()
    {
        $this->request = new Request();
        $this->isMobile = $this->checkIsMobile();
    }

    public function checkIsMobile()
    {
        $mobileBrowser = 0;
        $httpAccept = $this->request->getServer('HTTP_ACCEPT');
        $httpXWapProfile = $this->request->getServer('HTTP_X_WAP_PROFILE');
        $httpUserAgent = $this->request->getServer('HTTP_USER_AGENT');
        $httpProfile = $this->request->getServer('HTTP_PROFILE');
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($httpUserAgent))) {
            $mobileBrowser++;
        }
        if ((strpos(strtolower($httpAccept), 'application/vnd.wap.xhtml+xml') > 0) or ((isset($httpXWapProfile) or isset($httpProfile)))) {
            $mobileBrowser++;
        }
        $mobileUa = strtolower(substr($httpUserAgent, 0, 4));
        $agents = array('w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac', 'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno', 'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-', 'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-', 'newt', 'noki', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox', 'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-', 'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp', 'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-');
        if (in_array($mobileUa, $agents)) {
            $mobileBrowser++;
        }
        if (strpos(strtolower($httpUserAgent), 'opera mini') > 0) {
            $mobileBrowser++;
        }
        if ($mobileBrowser > 0) {
            $this->isMobile = true;
        }

        return $this->isMobile;
    }
}