<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Dantai\Api;

/**
 * Json client
 * A helper class to connect customers API system
 */
class UkestukeClient extends JsonClient
{

    /**
     * This function is used at UC5 - Refer Group information
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callEir2a01(array $config, array $functionParameters)
    {
        $config['data1'] = $config['data2'] = $functionParameters['dantaino'];
        $functionParameters['cryptkey'] = $this->prepareCryptKey($config);

        return $this->callFunction('step-eir/EIR2A01', $config, $functionParameters);
    }

    /**
     * This function is used at UC21 - Organization English registration for personal
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callEir2a02(array $config, array $functionParameters)
    {
        $config['data1'] = $functionParameters['dantaino'];
        $config['data2'] = $functionParameters['dantaino'];
        $functionParameters['cryptkey'] = $this->prepareCryptKey($config);

        return $this->callFunction('step-eir/EIR2A02', $config, $functionParameters);
    }

    /**
     * This function is used at UC21 - Organization English registration
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callEir2a03(array $config, array $functionParameters)
    {
        $eikenSize = count($functionParameters['eikenArray']);
        for ($i = 0; $i < $eikenSize; $i ++) {
            $config['data1'] = $functionParameters['eikenArray'][$i]['eikenid'];
            $config['data2'] = $functionParameters['eikenArray'][$i]['kyucd'];
            $functionParameters['eikenArray'][$i]['cryptkey'] = $this->prepareCryptKey($config);
        }

        return $this->callFunction('step-eir/EIR2A03', $config, $functionParameters);
    }

    /**
     * This function is used at UC23 - EikenID reference
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callEir1e02(array $config, array $functionParameters)
    {
        $config['data1'] = $functionParameters['eikenid'];
        $config['data2'] = $functionParameters['eikenpass'];
        $functionParameters['cryptkey'] = $this->prepareCryptKey($config);

        return $this->callFunction('step-eir/EIR1E02', $config, $functionParameters);
    }

    /**
     * This function is used at UC23 - EikenID new registration
     *
     * LangDD: Update to call ER1C03 instead of ER1C01
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callEir1c03(array $config, array $functionParameters)
    {
        // ã€Œeiken IDã€�â†’ã€Œç”Ÿå¹´æœˆæ—¥/ngay thang nam sinhã€�(yyyymmdd)
        $bdArray = explode('/', $functionParameters['birthday']);
        $bdArray[1] = str_pad($bdArray[1], 2, '0', STR_PAD_LEFT);
        $bdArray[2] = str_pad($bdArray[2], 2, '0', STR_PAD_LEFT);
        $config['data1'] = implode($bdArray, '');
        $config['data2'] = $functionParameters['eikenpass'];
        $functionParameters['cryptkey'] = $this->prepareCryptKey($config);
        $functionParameters['birthday'] = implode($bdArray, '/');
        return $this->callFunction('step-eir/EIR1C03', $config, $functionParameters);
    }
    
    /**
     * Get eiken pupil test result : /step-eir/EIR2B01 
     * 個人試験結果取得
     * 
     * @param array $config
     * @param array $functionParameters
     */
    public function callEir2b01(array $config,array $functionParameters)
    {
    	$config['data1'] =  $functionParameters['dantaino'];
    	$config['data2'] =  $functionParameters['nendo'] . $functionParameters['kai'];
        $config['timeout'] = 0;
    	$functionParameters['cryptkey'] = $this->prepareCryptKey($config);
    	
    	return $this->callFunction('step-eir/EIR2B01', $config, $functionParameters);
    }

    /**
     * Get IBA test result : /step-eir/EIR2C02
     * IBA試験結果取得
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callEir2c02(array $config,array $functionParameters)
    {
        $config['data1'] =  $functionParameters['examkbn'];
        $config['data2'] =  $functionParameters['jisshiid'];
        $config['timeout'] = 0;
        $functionParameters['cryptkey'] = $this->prepareCryptKey($config);
         
        return $this->callFunction('/step-eir/EIR2C02', $config, $functionParameters);
    }

    /**
     * Get header IBA test result : /step-eir/EIR2C03
     * IBA試験結果取得
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callEir2c03(array $config, array $functionParameters)
    {
        $config['data1'] = $config['data2'] = $functionParameters['dantaino'];
        $functionParameters['cryptkey'] = $this->prepareCryptKey($config);

        return $this->callFunction('step-eir/EIR2C03', $config, $functionParameters);
    }

    /**
     * IBA申込情報登録
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callEir2c01($config, $functionParameters)
    {
        $functionParameters['cryptkey'] = $this->prepareCryptKey($config);
         
        return $this->callFunction('/step-eir/EIR2C01', $config, $functionParameters);
    }
    /**
     * This function is used at UC21 - Policy screen
     * @author LangDD
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callEir2a05(array $config, array $functionParameters)
    {
        $config['data1'] = $config['data2'] = $functionParameters['dantaino'];
        $functionParameters['cryptkey'] = $this->prepareCryptKey($config);
    
        return $this->callFunction('step-eir/EIR2A05', $config, $functionParameters);
    }
    
    public function callNEconRcvOdr(array $config, array $functionParameters) {

        $functionParameters['sessionID'] = 1;
        $functionParameters['paymentFlg'] = 5;
        $functionParameters['shippmentFlg'] = 2;
        $functionParameters['commission'] = 0;
        $functionParameters['ordAmountbfTax'] = 0;
        $functionParameters['ordAmountTax'] = 0;
        $functionParameters['shipDtflg'] = 0;
        $functionParameters['barCode'] = 0;

        $functionName = $config['function'];
        return $this->callFunctionEContext($functionName, $config, $functionParameters);
    }
    
    public function callEconRcvEnd(array $config, array $functionParameters){
        $functionName = $config['function'];
        return $this->callFunctionEContext($functionName, $config, $functionParameters);
    }

    public function callEconRcvCancelOrder(array $config, array $functionParameters){
        $functionName = $config['function'];
        return $this->callFunctionEContext($functionName, $config, $functionParameters);
    }
    
    
    /**
     * This function is used at R3-UC16 - Refer Group information
     *
     * @param array $config
     * @param array $functionParameters
     * @return mixed
     */
    public function callEir2a04(array $config, array $functionParameters)
    {
        $config['data1'] = $config['data2'] = $functionParameters['dantaino'];
        $functionParameters['cryptkey'] = $this->prepareCryptKey($config);
        return $this->callFunction('step-eir/EIR2A04', $config, $functionParameters);
    }

    public function callDantaiApiEir2c02(array $config, array $functionParameters){
        return $this->callFunction('rest-api/eir2c02', $config, $functionParameters);
    }

    public function callDantaiApiEir2c03(array $config, array $functionParameters){
        return $this->callFunction('rest-api/eir2c03', $config, $functionParameters);
    }

    public function callDantaiApiMappingEiken(array $config, array $functionParameters){
        return $this->callFunction('api/mappingapi', $config, $functionParameters);
    }
    
    public function callEikenApplied(array $config, array $functionParameters){
        $config['data1'] = intval($functionParameters['eikenid']);
        $config['data2'] = intval($functionParameters['nendo'].$functionParameters['kai']);
        $functionParameters['cryptkey'] = $this->prepareCryptKey($config);
        
        return $this->callFunction('step-eir/EIR2A06', $config, $functionParameters);
    }

}