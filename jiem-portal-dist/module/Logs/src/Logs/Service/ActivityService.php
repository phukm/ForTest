<?php
namespace Logs\Service;

use Logs\Service\ServiceInterface\ActivityServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Dantai\PrivateSession;
use Doctrine\ORM\EntityManager;
use Dantai\Utility\JsonModelHelper;
class ActivityService implements ActivityServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    public function convertToExport($data,$header) {
        $dataExport = array();
        if ($data && $header) {
            $index = 0;
            foreach ($data as $row) {
                foreach ($header as $field => $titleHeader) {
                    $dataExport[$index][$field] = '';
                        $dataExport[$index][$field] = array_key_exists($field, $row) ? $row[$field] : '';
                        if($field == 'insertAt'){
                            if(array_key_exists($field, $row)){
                                $dataExport[$index][$field] = date_format($row[$field],"Y/m/d H:i");
                            }
                        }
                        if (method_exists($this, $field)) {
                            $dataExport[$index] = array_merge($dataExport[$index],$this->$field($row,$field));
                        }
                        
                }
                $index++;
            }
        }
        array_unshift($dataExport, $header);
        return $dataExport;
    }
}