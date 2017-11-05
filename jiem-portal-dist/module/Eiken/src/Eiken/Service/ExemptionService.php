<?php
namespace Eiken\Service;

use Dantai\Utility\CharsetConverter;
use Dantai\Utility\PHPExcel;
use Eiken\Service\ServiceInterface\ExemptionServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\PrivateSession;
use Dantai\Api\UkestukeClient;
use Zend\View\Model\ViewModel;

class ExemptionService implements ExemptionServiceInterface,ServiceLocatorAwareInterface
{    
    use ServiceLocatorAwareTrait;  

    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
    
    private $exemption = '';
    public function setExemptionApiClient($mock = '')
    {
        if($mock){
            $this->exemption = $mock;
        }else{
            $this->exemption = UkestukeClient::getInstance();
        }
    }
    
    public function getExemptionFromAPI($orgNo, $config)
    {  
        try {
            if(!$this->exemption){
                $this->setExemptionApiClient();
            }
            $result = $this->exemption->callEir2a04($config, array(
                'dantaino' => $orgNo
            ));
        } catch (\Exception $e) {
            return false;
        } 
        return $result;        
    }

    public function searchArray($array, $key, $value) {       
        if($value=='') return $array;    
       
        $return = array();      
        foreach ($array as $k => $subarray) {          
            if (isset($subarray->$key)) {
                if ((strpos($subarray->$key, $value) !== false )||($subarray->$key == $value)) {                    
                    $return[] = $subarray;
                }
            }
            if($key == 'name')
            {               
                if(strpos(str_replace('　','',$subarray->shimei),$value)!==false|| strpos(str_replace('　','',$subarray->shimei_kana),$value)!==false) {
                   $return[] = $subarray;    
                }
            }                 
        }     
        return $return;
    }  
    
     public function getDataPagingOfDataImport($dataApi, $eikenid, $name, $currentPage = 1) {
         if (intval($currentPage) < 1)
            $currentPage = 1;  

        $dataApi = $this->searchArray($dataApi, 'eikenid', $eikenid);
        $dataApi = $this->searchArray($dataApi, 'name', $name);
        
        $rowPerPage = 20;
        $maxPage = ceil(count($dataApi) / $rowPerPage);
        
        $dataShow = $this->mappingDataFromApi($dataApi, $currentPage, $rowPerPage);
        return array($dataShow, $maxPage);
    }
    public function getHtmlOutPutOfTemplate($template, $params) {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true)
                ->setTemplate($template)
                ->setVariables($params);
        $htmlOutput = $this->getServiceLocator()->get('viewrenderer')->render($viewModel);
        return $htmlOutput;
    }

    function mappingDataFromApi($data, $currentPage = null, $rowPerPage = null){
        $config = $this->getServiceLocator()->get('Config');
        $mappingLevel = $config['MappingLevel'];
        $mappingGender = $config['sex'];
        $jobCode = $config['Job_Code'];
        $schoolCode = $config['School_Code'];

        $dataShow = array();
        foreach ($data as $key => $value) {
            $value->shimei = str_replace('　', '', $value->shimei);
            $value->shimei_kana = str_replace('　', '', $value->shimei_kana);
            if(array_key_exists($value->kyucd, $mappingLevel)){
                $value->kyucd = $mappingLevel[$value->kyucd];
            }
            if(array_key_exists($value->seibetsu, $mappingGender)){
                $value->seibetsu = $mappingGender[$value->seibetsu];
            }
            if(array_key_exists($value->shokugyono, $jobCode)){
                $value->shokugyono = $jobCode[$value->shokugyono];
            }
            if(array_key_exists($value->gakkouno, $schoolCode)){
                $value->gakkouno = $schoolCode[$value->gakkouno];
            }
            if(isset($currentPage) && isset($rowPerPage)){
                $begin = $rowPerPage * ($currentPage - 1);
                $end = $rowPerPage * $currentPage;
                if ($key >= $begin && $key < $end) {
                    $dataShow[] = $value;
                }
            }else {
                $dataShow[] = $value;
            }
        }

        return $dataShow;
    }

    public function getExemptionDataList($organizationNo, $eikenId = '', $name = '', $config)
    {
        $dataExemptionApi = $this->getExemptionFromAPI($organizationNo, $config);
        $dataApi = $dataExemptionApi ? $dataExemptionApi->eikenArray : array();
        $dataApi = $this->mappingDataFromApi($dataApi);
        $dataApi = $this->searchArray($dataApi, 'kekka', 10);
        $dataApi = $this->searchArray($dataApi, 'eikenid', $eikenId);
        $dataApi = $this->searchArray($dataApi, 'name', $name);
        $dataApi = $this->array_orderby($dataApi, 'shimei_kana', SORT_ASC, 'birthdt', SORT_ASC, 'eikenid', SORT_ASC);
        return json_decode(json_encode($dataApi), true);
    }

    public function exportToExcel($exemptionData, $response, $filename)
    {
        $objFileName = new CharsetConverter();
        $filename = $objFileName->utf8ToShiftJis($filename);
        $phpExcel = new PHPExcel();
        $phpExcel->export($exemptionData, $filename, 'default', 1, '', 'xls');
        return $response;
    }

    public function createExportExcelData($headers, $data)
    {
        foreach($data as $key => $value){
            // Remove redundant column.
            foreach($value as $k => $item){
                if(!in_array($k, $headers)) unset($data[$key][$k]);
            }
            // re-order column.
            $data[$key] = array_merge(array_flip(array_values($headers)), $data[$key]);
        }
        return $data;
    }

    public function getExportExcelDataExemptionList($organizationNo, $eikenId = '', $name = '')
    {
        $config = $this->getServiceLocator()->get('Config')['orgmnt_config']['api'];
        $exportData = $this->getExemptionDataList($organizationNo, $eikenId, $name, $config);
        $headerApi = $this->getServiceLocator()->get('Config')['headerExcelExport']['listOfExemptionList']['api'];
        $headerExcel = $this->getServiceLocator()->get('Config')['headerExcelExport']['listOfExemptionList']['excel'];
        $exportData = $this->createExportExcelData($headerApi, $exportData);
        array_unshift($exportData, $headerExcel);
        return $exportData;
    }

    public function getUserIdentity()
    {
        return PrivateSession::getData('userIdentity');
    }

    function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row->$field;
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);

        return array_pop($args);
    }

    public function refreshData($array)
    {
        foreach ($array as $key => $val) {
            if ($val->kekka != 10 || empty($val->eikenid)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

}