<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Dantai\Api;

use Zend\Json\Encoder;
use Zend\Json\Decoder;

/**
 * Json client
 * A helper class to connect customers API system
 */
class EinaviClient extends JsonClient
{
    /**
     * Prepare API parameters structure before sending
     *
     * @param array $functionParameters
     * @return multitype:string
     */
    protected function prepareParameters(array $functionParameters)
    {
        $functionParameters['bkeapi'] = Encoder::encode($functionParameters['bkeapi']);

        return $functionParameters;
    }

    /**
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callGetGMResult(array $config, array $functionParameters)
    {
        $functionParameters['c'] = 'GetGMResult';
        $functionParameters['version'] = '1.0';
        $functionParameters['bkeapi']['owner_id'] = $config['owner_id'];
        $functionParameters['bkeapi']['app_id'] = $config['app_id'];
        $config['data1'] = $functionParameters['bkeapi']['proc_day'];
        $config['data2'] = $functionParameters['bkeapi']['attestation'];
        $functionParameters['bkeapi']['crypt_key'] = $this->prepareCryptKey($config);

        return $this->callFunction('bke/api/index.php', $config, $functionParameters);
    }

    /**
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callGroupGetGMResult(array $config, array $functionParameters)
    {
        $functionParameters['c'] = 'Group_GetGMResult';
        $functionParameters['version'] = '1.0';
        $functionParameters['bkeapi']['owner_id'] = $config['owner_id'];
        $functionParameters['bkeapi']['app_id'] = $config['app_id'];
        $config['data1'] = $functionParameters['bkeapi']['proc_day'];
        $config['data2'] = $functionParameters['bkeapi']['group_id'];
        $functionParameters['bkeapi']['crypt_key'] = $this->prepareCryptKey($config);

        return $this->callFunction('bke/api/index.php', $config, $functionParameters);
    }

    /**
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     * This function allows pupils to log into Ei-navi system
     */
    public function callCheckLoginUser(array $config, array $functionParameters)
    {
        $functionParameters['c'] = 'CheckLoginUser';
        $functionParameters['version'] = '1.0';
        $functionParameters['bkeapi']['owner_id'] = $config['owner_id'];
        $functionParameters['bkeapi']['app_id'] = $config['app_id'];
        $config['data1'] = $functionParameters['bkeapi']['proc_day'];
        $config['data2'] = $functionParameters['bkeapi']['login_string'];
        $config['cryptmethod'] = 'sha512';
        $functionParameters['bkeapi']['crypt_key'] = $this->prepareCryptKey($config);

        return $this->callFunction('bke/api/index.php', $config, $functionParameters);
    }

    /**
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     * Check email address exits to Ei-navi system
     */
    public function callCheckUserId(array $config, array $functionParameters)
    {
        $functionParameters['c'] = 'CheckUserId';
        $functionParameters['version'] = '1.0';
        $functionParameters['bkeapi']['owner_id'] = $config['owner_id'];
        $functionParameters['bkeapi']['app_id'] = $config['app_id'];
        $config['data1'] = $functionParameters['bkeapi']['proc_day'];
        $config['data2'] = $functionParameters['bkeapi']['password'];
        $config['cryptmethod'] = 'sha512';
        $functionParameters['bkeapi']['crypt_key'] = $this->prepareCryptKey($config);

        return $this->callFunction('bke/api/index.php', $config, $functionParameters);
    }

    /**
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     * This function allows pupils to register for new Einavi ID
     */
    public function callSetUserData(array $config, array $functionParameters)
    {
        $functionParameters['c'] = 'SetUserData';
        $functionParameters['version'] = '1.0';
        $functionParameters['bkeapi']['owner_id'] = $config['owner_id'];
        $functionParameters['bkeapi']['app_id'] = $config['app_id'];
        $config['data1'] = $functionParameters['bkeapi']['proc_day'];
        $config['data2'] = $config['app_id'];
        $config['cryptmethod'] = 'sha512';
        $functionParameters['bkeapi']['crypt_key'] = $this->prepareCryptKey($config);

        return $this->callFunction('bke/api/index.php', $config, $functionParameters);
    }

    /**
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callGroupGetSGHistory(array $config, array $functionParameters)
    {
        $functionParameters['c'] = 'Group_GetSGHistory';
        $functionParameters['version'] = '1.0';
        $functionParameters['bkeapi']['owner_id'] = $config['owner_id'];
        $functionParameters['bkeapi']['app_id'] = $config['app_id'];
        $config['data1'] = $functionParameters['bkeapi']['proc_day'];
        $config['data2'] = $functionParameters['bkeapi']['group_id'];
        $config['cryptmethod'] = 'sha512';
        $functionParameters['bkeapi']['crypt_key'] = $this->prepareCryptKey($config);

        return $this->callFunction('bke/api/index.php', $config, $functionParameters);
    }

    public function callGroupGetSGUserHistory(array $config, array $functionParameters)
    {
        $functionParameters['c'] = 'Group_GetSGUserHistory';
        $functionParameters['version'] = '1.0';
        $functionParameters['bkeapi']['owner_id'] = $config['owner_id'];
        $functionParameters['bkeapi']['app_id'] = $config['app_id'];
        $config['data1'] = $functionParameters['bkeapi']['proc_day'];
        $config['data2'] = $functionParameters['bkeapi']['group_id'];
        $config['cryptmethod'] = 'sha512';
        $functionParameters['bkeapi']['crypt_key'] = $this->prepareCryptKey($config);

        return $this->callFunction('bke/api/index.php', $config, $functionParameters);
    }

    public function callCheckBasicCoupon(array $config, array $functionParameters)
    {
        $functionParameters['c'] = 'CheckBasicCoupon';
        $functionParameters['version'] = '1.0';
        $functionParameters['bkeapi']['owner_id'] = $config['owner_id'];
        $functionParameters['bkeapi']['app_id'] = $config['app_id'];
        $config['data1'] = $functionParameters['bkeapi']['proc_day'];
        $config['data2'] = $functionParameters['bkeapi']['attestation'];
        $config['cryptmethod'] = 'sha512';
        $functionParameters['bkeapi']['crypt_key'] = $this->prepareCryptKey($config);

        return $this->callFunction('bke/api/index.php', $config, $functionParameters);
    }

    public function callGroupGetBasicTicketInfo(array $config, array $functionParameters)
    {
        $functionParameters['c'] = 'Group_GetBasicTicketInfo';
        $functionParameters['version'] = '1.0';
        $functionParameters['bkeapi']['owner_id'] = $config['owner_id'];
        $functionParameters['bkeapi']['app_id'] = $config['app_id'];
        $config['data1'] = $functionParameters['bkeapi']['proc_day'];
        $config['data2'] = $functionParameters['bkeapi']['attestation'];
        $config['cryptmethod'] = 'sha512';
        $functionParameters['bkeapi']['crypt_key'] = $this->prepareCryptKey($config);

        return $this->callFunction('bke/api/index.php', $config, $functionParameters);
    }

    public function callRequestChangePassword(array $config, array $functionParameters)
    {
        $functionParameters['c'] = 'RequestChangePassword';
        $functionParameters['version'] = '1.0';
        $functionParameters['bkeapi']['owner_id'] = $config['owner_id'];
        $functionParameters['bkeapi']['app_id'] = 'FRED';//$config['app_id'];
        $config['data1'] = $functionParameters['bkeapi']['proc_day'];
        $config['data2'] = $functionParameters['bkeapi']['mail_address'];
        $config['cryptmethod'] = 'sha512';
        $functionParameters['bkeapi']['crypt_key'] = $this->prepareCryptKey($config);

        return $this->callFunction('bke/api/index.php', $config, $functionParameters);
    }
}