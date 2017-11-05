<?php

namespace OrgMnt\Service;

use OrgMnt\Service\ServiceInterface\ImportMasterDataServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\Utility\CharsetConverter;
use Dantai\Utility\CsvHelper;
use Dantai\Utility\DateHelper;

class ImportMasterDataService implements ImportMasterDataServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private $accessKeyRepos;
    private $organizationRepos;
    private $cityRepos;
    private $entityManager;

    public function setOrganizationRepository($organizationRepos = Null) {
        $this->organizationRepos = $organizationRepos ? $organizationRepos : $this->getEntityManager()->getRepository('Application\Entity\Organization');
    }

    public function getOrganizationRepository() {
        if (!$this->organizationRepos) {
            $this->setOrganizationRepository();
        }
        return $this->organizationRepos;
    }

    public function setAccessRepository($accessKeyRepos = Null) {
        $this->accessKeyRepos = $accessKeyRepos ? $accessKeyRepos : $this->getEntityManager()->getRepository('Application\Entity\AccessKey');
    }

    public function setCityRepository($cityRepos = Null) {
        $this->cityRepos = $cityRepos ? $cityRepos : $this->getEntityManager()->getRepository('Application\Entity\City');
    }

    public function getAccessRepository() {
        if (!$this->accessKeyRepos) {
            $this->setAccessRepository();
        }
        return $this->accessKeyRepos;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        if (!$this->entityManager) {
            $this->setEntityManager();
        }
        return $this->entityManager;
    }

    public function setEntityManager($entityManager = null) {
        $this->entityManager = $entityManager ? $entityManager : $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function getDantaiMasterData() {
        if (!$this->organizationRepos) {
            $this->setOrganizationRepository();
        }
        $data = $this->organizationRepos->getDantaiMasterData();
        $result = array();
        if ($data) {
            foreach ($data as $row) {
                if (!empty(trim($row['organizationNo']))) {
                    $result[trim($row['organizationNo'])] = trim($row['organizationNo']);
                }
            }
        }

        return $result;
    }

    public function getAccessKeyMasterData($year, $kai) {
        if (!$this->accessKeyRepos) {
            $this->setAccessRepository();
        }
        $data = $this->accessKeyRepos->getAccessKeyMasterData($year, $kai);
        $result = array();
        if ($data) {
            foreach ($data as $row) {
                if (!empty(trim($row['organizationNo']))) {
                    $result[trim($row['organizationNo'])] = trim($row['organizationNo']);
                }
            }
        }

        return $result;
    }

    public function getCityMasterData() {
        if (empty($this->entityManager)) {
            $this->entityManager = $this->setEntityManager();
        }
        $em = $this->entityManager;
        if (!$this->cityRepos) {
            $this->setCityRepository();
        }
        $data = $this->cityRepos->getCityMasterData();
        $result = array();
        if ($data) {
            foreach ($data as $row) {
                if ($row['cityCode'] !== NULL) {
                    $result[trim($row['cityCode'])] = trim($row['id']);
                }
            }
        }

        return $result;
    }

    public function splitMasterData($data, $year, $kai) {
        $dantaiInsert = array();
        $dantaiUpdate = array();
        $accessKeyInsert = array();
        $accessKeyUpdate = array();

        if ($data) {
            $dantaiMaster = $this->getDantaiMasterData();
            $accessKeyMaster = $this->getAccessKeyMasterData($year, $kai);
            $cityMaster = $this->getCityMasterData();

            foreach ($data as $row) {
                if (isset($row['organizationNo'])) {

                    $row['year'] = $year;
                    $row['kai'] = $kai;

                    // replace "-" to "" in telNo
                    $row["telNo"] =  !empty($row["telNo"]) ? preg_replace("/[^0-9]+|\s|-/", '',$row["telNo"]) : ('0'.$row['organizationNo'].rand(0,9));

                    // add colum cityId width citycode
                    $row["cityId"] = '';
                    if (array_key_exists(trim($row["cityCode"]), $cityMaster)) {
                        $row["cityId"] = intval($cityMaster[trim($row["cityCode"])]);
                    }

                    // split dantai Master Data to insert and update data
                    if (array_key_exists(trim($row["organizationNo"]), $dantaiMaster)) {
                        $dantaiUpdate[trim($row["organizationNo"])] = $row;
                    } else {
                        $dantaiInsert[trim($row["organizationNo"])] = $row;
                    }

                    // split accessKey Master Data to insert and update data
                    if (array_key_exists(trim($row["organizationNo"]), $accessKeyMaster)) {
                        $accessKeyUpdate[trim($row["organizationNo"])] = $row;
                    } else {
                        $accessKeyInsert[trim($row["organizationNo"])] = $row;
                    }
                }
            }
        }

        return array($dantaiInsert, $dantaiUpdate, $accessKeyInsert, $accessKeyUpdate);
    }

    public function validateImportMasterData($sources) {
        if (isset($sources['formDataSubmited'])) {
            $validateFormDataSubmited = $this->validateFormDataSubmited($sources['formDataSubmited']);
            if (isset($validateFormDataSubmited['passed']) &&
                    $validateFormDataSubmited['passed'] !== true) {
                return $validateFormDataSubmited;
            }
        }

        return $this->validateFileDataSubmited($sources['fileDataSubmited']);
    }

    public function validateFormDataSubmited($sources) {
        if (!empty($requiredFields = $sources['rules']['required'])) {
            $unvalidateFields = array();
            foreach ($requiredFields as $requiredField) {
                if (empty($sources['data'][$requiredField['field']])) {
                    $unvalidateFields[] = $requiredField;
                }
            }
            if (!empty($unvalidateFields)) {
                return $this->getImportFileMessages(array(
                            'message' => 'VIMD_MSG11',
                            'requiredFields' => $unvalidateFields,
                ));
            }
        }

        return $this->getImportFileMessages();
    }

    public function validateFileDataSubmited($sources = array()) {
        if (!empty($sourceFile = $sources['data'][$sources['inputFileName']])) {
            if (!empty($sourceFile['name'])) {
                $validateFileExtention = $this->validateFileExtention($sources['rules']['accepted'], $sourceFile['name']);
                if (!$validateFileExtention) {
                    return $this->getImportFileMessages(array('message' => 'VIMD_MSG5'));
                }

                $fileContent = isset($sourceFile['unitTestData']) ? $sourceFile['unitTestData'] :
                        @file_get_contents($sourceFile['tmp_name']);
                $validateFileContent = $this->validateFileContent($fileContent, $sources['rules']);

                if (isset($validateFileContent['passed']) && $validateFileContent['passed'] == false) {
                    return $validateFileContent;
                }

                $numberOfSuccessfullyImported = isset($validateFileContent['data']) ?
                        count($validateFileContent['data']) : 0;

                $duplicatedDantaiNo = (!empty($validateFileContent['duplicateData'])) ?
                        array_map(function ($item) {
                            $keyMapper = $this->getKeyMapper();
                            if (isset($item['organizationNo'])) {
                                return $item['organizationNo'];
                            }
                            if (isset($item[$keyMapper['organizationNo']])) {
                                return $item[$keyMapper['organizationNo']];
                            }
                        }, $validateFileContent['duplicateData']) : [];

                $numberOfDantaisHavingDuplicated = (!empty($validateFileContent['duplicateData'])) ?
                        count($validateFileContent['duplicateData']) : 0;

                $numberOfEmptyDantaiNoOrAccessKey = 0;
                if ((!empty($emptyData = $validateFileContent['emptyData']))) {
                    if (!empty($emptyData['dantaiNo'])) {
                        $numberOfEmptyDantaiNoOrAccessKey += count($emptyData['dantaiNo']);
                    }

                    if (!empty($emptyData['accessKey'])) {
                        foreach ($emptyData['accessKey'] as $accessKey) {
                            $numberOfEmptyDantaiNoOrAccessKey += count($accessKey);
                        }
                    }

                    if (!empty($emptyData['bothDantaiNoAndAccessKey'])) {
                        $numberOfEmptyDantaiNoOrAccessKey += count($emptyData['bothDantaiNoAndAccessKey']);
                    }
                }

                $validation = array();

                if ((!empty($validateFileContent['emptyData']) &&
                        (!empty($validateFileContent['duplicateData'])))) {
                    $validation = $this->getImportFileMessages(array(
                        'message' => 'VIMD_MSG9',
                        'X1' => $numberOfSuccessfullyImported,
                        'X2' => $numberOfEmptyDantaiNoOrAccessKey,
                        'X3' => $numberOfDantaisHavingDuplicated,
                        'duplicatedDantaiNo' => implode(', ', array_unique($duplicatedDantaiNo)),
                    ));
                } else if (!empty($validateFileContent['emptyData'])) {
                    $validation = $this->getImportFileMessages(array(
                        'message' => 'VIMD_MSG7',
                        'X1' => $numberOfSuccessfullyImported,
                        'X2' => $numberOfEmptyDantaiNoOrAccessKey,
                    ));
                } else if (!empty($validateFileContent['duplicateData'])) {
                    $validation = $this->getImportFileMessages(array(
                        'message' => 'VIMD_MSG8',
                        'X1' => $numberOfSuccessfullyImported,
                        'X3' => $numberOfDantaisHavingDuplicated,
                        'duplicatedDantaiNo' => implode(', ', array_unique($duplicatedDantaiNo)),
                    ));
                } else {
                    $validation = $this->getImportFileMessages(array(
                        'message' => 'VIMD_MSG10',
                        'X1' => $numberOfSuccessfullyImported,
                    ));
                }
                $validation['data'] = !empty($validateFileContent['data']) ? $validateFileContent['data'] : [];

                return $validation;
            }

            return $this->getImportFileMessages(array('message' => 'VIMD_MSG4'));
        }

        return $this->getImportFileMessages(array('message' => 'NFID',));
    }

    public function validateFileContent($fileContent, $rules) {
        $results = array();

        if (isset($fileContent) && is_string($fileContent)) {
            if (isset($rules['isEmpty']) && $rules['isEmpty'] === false &&
                    $this->isEmptyFile($fileContent)) {
                return $this->getImportFileMessages(array('message' => 'VIMD_MSG6'));
            }

            $fileContent = $this->parseDataFromCsvFile(CharsetConverter::shiftJisToUtf8($fileContent));

            if (isset($fileContent['passed']) && $fileContent['passed'] == false) {
                return $fileContent;
            } else if (isset($rules['hasHeader']) && $rules['hasHeader'] == false &&
                    $fileContent['checkHeader'] == true) {
                return $this->getImportFileMessages(array('message' => 'VIMD_MSG6'));
            }

            if (isset($rules['numberOfRow']) && $rules['numberOfRow'] > 0 &&
                    $fileContent['numberOfRow'] > $rules['numberOfRow']) {
                return $this->getImportFileMessages(array('message' => 'VIMD_MSG6'));
            }

            if (isset($rules['numberOfColumn']) && $rules['numberOfColumn'] > 0 &&
                    $fileContent['numberOfColumn'] != $rules['numberOfColumn']) {
                return $this->getImportFileMessages(array('message' => 'VIMD_MSG6'));
            }

            $results['data'] = isset($fileContent['standarData']) ? $fileContent['standarData'] : [];
            $results['emptyData'] = isset($fileContent['emptyData']) ? $fileContent['emptyData'] : [];
            $results['duplicateData'] = isset($fileContent['duplicateData']) ? $fileContent['duplicateData'] : [];
        }

        return $results;
    }

    public function parseDataFromCsvFile($input, $delimiter = ",", $enclosure = '"', $escape = "\\") {
        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $input);
        rewind($handle);

        $keyMapper = $this->getKeyMapper();
        $rowIndex = 0;
        $columnIndex = 0;
        $listDantaiNo = array();
        $data = array();

        while (!feof($handle)) {
            $row = fgetcsv($handle, 10240, $delimiter, $enclosure, $escape);
            if ($columnIndex < $maxColumn = count($row)) {
                $columnIndex = $maxColumn;
            }
            if ($rowIndex > 0) {
                $data['isHeader'] = false;
            } else if ($rowIndex === 0) {
                if ($columnIndex <= 1) {
                    return $this->getImportFileMessages(array('message' => 'VIMD_MSG6'));
                } else {
                    $data['checkHeader'] = $this->isHeaderFile($row);
                    $data['isHeader'] = $data['checkHeader'];
                }
            }
            if (!$data['isHeader']) {
                if (!empty($row[$keyMapper['organizationNo']]) && !empty($row[$keyMapper['accessKey']])) {
                    if (!in_array($row[$keyMapper['organizationNo']], $listDantaiNo)) {
                        $listDantaiNo[] = $row[$keyMapper['organizationNo']];
                        $data['stores'][$row[$keyMapper['organizationNo']]] = $row;
                        $data['standarData'][$row[$keyMapper['organizationNo']]] = $this->convertToStandarData($row);
                    } else {
                        $this->processDuplicateStores($data, $row, $keyMapper);
                        if (isset($data['standarData'][$row[$keyMapper['organizationNo']]])) {
                            unset($data['standarData'][$row[$keyMapper['organizationNo']]]);
                        }
                    }
                } else if (!empty($row[$keyMapper['organizationNo']]) && empty($row[$keyMapper['accessKey']])) {
                    if (!in_array($row[$keyMapper['organizationNo']], $listDantaiNo)) {
                        $listDantaiNo[] = $row[$keyMapper['organizationNo']];
                        $data['stores'][$row[$keyMapper['organizationNo']]] = $row;
                    } else {
                        $this->processDuplicateStores($data, $row, $keyMapper);
                    }
                    $data['emptyData']['accessKey'][$row[$keyMapper['organizationNo']]][] = $row;
                } else if (empty($row[$keyMapper['organizationNo']]) && !empty($row[$keyMapper['accessKey']])) {
                    $data['emptyData']['dantaiNo'][] = $row;
                } else {
                    if ($count = count($row) > 1) {
                        $data['emptyData']['bothDantaiNoAndAccessKey'][] = $row;
                    }
                }
            }

            $rowIndex++;
        }

        if (isset($data['isHeader'])) {
            unset($data['isHeader']);
        }
        if (isset($data['stores'])) {
            unset($data['stores']);
        }
        $data['numberOfRow'] = $rowIndex;
        $data['numberOfColumn'] = $columnIndex;

        return $data;
    }

    public function processDuplicateStores(&$data, &$row, $keyMapper) {
        if (isset($data['stores'][$row[$keyMapper['organizationNo']]])) {
            $data['duplicateData'][] = $data['stores'][$row[$keyMapper['organizationNo']]];
            unset($data['stores'][$row[$keyMapper['organizationNo']]]);
            if (isset($data['standarData'][$row[$keyMapper['organizationNo']]])) {
                unset($data['standarData'][$row[$keyMapper['organizationNo']]]);
            }
        }
        $data['duplicateData'][] = $row;
    }

    public function isHeaderFile($sources) {
        $keyMapper = $this->getKeyMapper();
        $mapHeader = array(
            $keyMapper['organizationNo'] => 'OrganizationNo',
            $keyMapper['accessKey'] => 'AccessKey',
            $keyMapper['stateCode'] => 'StateCode',
            $keyMapper['schoolDivision'] => 'SchoolDivision',
        );

        if (!empty($sources[$keyMapper['organizationNo']])) {
            $sourceDantaiNo = strtolower($sources[$keyMapper['organizationNo']]);
            $mapDatainNo = strtolower($mapHeader[$keyMapper['organizationNo']]);
            if (strpos($sourceDantaiNo, $mapDatainNo) !== false || (!((int) $sourceDantaiNo) > 0)) {
                return true;
            }
        }

        if (!empty($sources[$keyMapper['accessKey']])) {
            $sourceAccessKey = strtolower($sources[$keyMapper['accessKey']]);
            $mapAccessKey = strtolower($mapHeader[$keyMapper['accessKey']]);
            if (strpos($sourceAccessKey, $mapAccessKey) !== false) {
                return true;
            }
        }

        return false;
    }

    public function convertToStandarData($sources = array()) {
        if (!empty($sources)) {
            $keyMapper = $this->getKeyMapper();

            $data['organizationNo'] = isset($sources[$keyMapper['organizationNo']]) ?
                    $sources[$keyMapper['organizationNo']] : '';
            $data['organizationCode'] = isset($sources[$keyMapper['organizationCode']]) ?
                    $sources[$keyMapper['organizationCode']] : '';
            $data['flagRegister'] = isset($sources[$keyMapper['flagRegister']]) ?
                    $sources[$keyMapper['flagRegister']] : '';
            $data['examLocation'] = isset($sources[$keyMapper['examLocation']]) ?
                    $sources[$keyMapper['examLocation']] : '';
            $data['markChangeAfter'] = isset($sources[$keyMapper['undefined1']]) ?
                    $sources[$keyMapper['undefined1']] : '';
            $data['orgNameKanji'] = isset($sources[$keyMapper['orgNameKanji']]) ?
                    $sources[$keyMapper['orgNameKanji']] : '';
            $data['orgNameKana'] = isset($sources[$keyMapper['orgNameKana']]) ?
                    $sources[$keyMapper['orgNameKana']] : '';
            $data['department'] = isset($sources[$keyMapper['department']]) ?
                    $sources[$keyMapper['department']] : '';
            $data['officerName'] = isset($sources[$keyMapper['officerName']]) ?
                    $sources[$keyMapper['officerName']] : '';
            $data['accessKey'] = isset($sources[$keyMapper['accessKey']]) ?
                    $sources[$keyMapper['accessKey']] : '';
            $data['email'] = isset($sources[$keyMapper['email']]) ?
                    $sources[$keyMapper['email']] : '';
            $data['zipCode'] = isset($sources[$keyMapper['zipCode']]) ?
                    $sources[$keyMapper['zipCode']] : '';
            $data['townCode'] = isset($sources[$keyMapper['townCode']]) ?
                    $sources[$keyMapper['townCode']] : '';
            $data['address1'] = isset($sources[$keyMapper['address1']]) ?
                    $sources[$keyMapper['address1']] : '';
            $data['address2'] = isset($sources[$keyMapper['address2']]) ?
                    $sources[$keyMapper['address2']] : '';
            $data['telNo'] = isset($sources[$keyMapper['telNo']]) ?
                    $sources[$keyMapper['telNo']] : '';
            $data['fax'] = isset($sources[$keyMapper['fax']]) ?
                    $sources[$keyMapper['fax']] : '';
            $data['cityCode'] = isset($sources[$keyMapper['cityCode']]) ?
                    $sources[$keyMapper['cityCode']] : '';
            $data['stateCode'] = isset($sources[$keyMapper['stateCode']]) ?
                    $sources[$keyMapper['stateCode']] : '';
            $data['schoolDivision'] = isset($sources[$keyMapper['schoolDivision']]) ?
                    $sources[$keyMapper['schoolDivision']] : '';
            $data['passcode'] = isset($sources[$keyMapper['passcode']]) ?
                    $sources[$keyMapper['passcode']] : '';

            return $data;
        }

        return $sources;
    }

    public function getExtentionOfFile($fileName) {
        return (!empty($fileName)) ?
                strtolower(end(explode('.', $fileName))) : '';
    }

    public function validateFileExtention($extentions, $fileName) {
        if ($extentions) {
            if ($extentions !== '.*') {
                $extention = $this->getExtentionOfFile($fileName);
                if (in_array($extention, explode('.', strtolower($extentions)))) {
                    return true;
                }
                return false;
            }
            return true;
        }

        return false;
    }

    public function isEmptyFile($fileContent) {
        if (trim($fileContent) == '') {
            return true;
        }

        return false;
    }

    public function getKeyMapper() {
        return array(
            'organizationNo' => 0,
            'organizationCode' => 1,
            'flagRegister' => 2,
            'examLocation' => 3,
            'undefined1' => 4,
            'orgNameKanji' => 5,
            'orgNameKana' => 6,
            'department' => 7,
            'officerName' => 8,
            'accessKey' => 9,
            'email' => 11,
            'zipCode' => 12,
            'townCode' => 13,
            'address1' => 14,
            'address2' => 15,
            'telNo' => 16,
            'fax' => 17,
            'cityCode' => 18,
            'stateCode' => 19,
            'schoolDivision' => 20,
            'passcode' => 21,
            'undefined2' => 22,
        );
    }

    public function getImportFileMessages($options = array()) {
        $results = array();

        if (isset($options['message'])) {
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            if(!empty($options['X2'])) $x2text = intval($options['X2']) < 10 ? $options['X2'] . 'つ' : $options['X2'];
            if(!empty($options['X3'])) $x3text = intval($options['X3']) < 10 ? $options['X3'] . 'つ' : $options['X3'];
            switch ($options['message']) {
                case 'VIMD_MSG4':
                    $results = array(
                        'messageId' => $options['message'],
                        'passed' => false,
                        'messages' => $translator->translate($options['message'])
                    );
                    break;
                case 'VIMD_MSG5':
                    $results = array(
                        'messageId' => $options['message'],
                        'passed' => false,
                        'messages' => $translator->translate($options['message'])
                    );
                    break;
                case 'VIMD_MSG6':
                    $results = array(
                        'messageId' => $options['message'],
                        'passed' => false,
                        'messages' => $translator->translate($options['message'])
                    );
                    break;
                case 'VIMD_MSG7':
                    $results = array(
                        'messageId' => $options['message'],
                        'passed' => true,
                        'messages' => $options['X1'] .
                        $translator->translate($options['message'] . 'P1') . $x2text .
                        $translator->translate($options['message'] . 'P2')
                    );
                    break;
                case 'VIMD_MSG8':
                    $results = array(
                        'messageId' => $options['message'],
                        'passed' => true,
                        'messages' =>
                        $options['X1'] .
                        $translator->translate($options['message'] . 'P1') . $options['X3'] .
                        $translator->translate($options['message'] . 'P2') . '<br>' .
                        $translator->translate($options['message'] . 'P3') . $options['duplicatedDantaiNo']
                    );
                    break;
                case 'VIMD_MSG9':
                   
                    $results = array(
                        'messageId' => $options['message'],
                        'passed' => true,
                        'messages' =>
                        $options['X1'] .
                        $translator->translate($options['message'] . 'P1') . $x2text .
                        $translator->translate($options['message'] . 'P2') . $x3text .
                        $translator->translate($options['message'] . 'P3') . '<br>' .
                        $translator->translate($options['message'] . 'P4') . $options['duplicatedDantaiNo']
                    );
                    break;
                case 'VIMD_MSG10':
                    $results = array(
                        'messageId' => $options['message'],
                        'passed' => true,
                        'messages' => $options['X1'] .
                        $translator->translate($options['message'])
                    );
                    break;
                case 'VIMD_MSG11':
                    $messages = array();
                    foreach ($options['requiredFields'] as $required) {
                        $messages[][$required['field']] = $translator->translate($options['message']);
                    }
                    $results = array(
                        'messageId' => $options['message'],
                        'passed' => false,
                        'messages' => $messages
                    );
                    break;
                case 'NFID' :
                    $results = array(
                        'messageId' => $options['message'],
                        'passed' => false,
                        'messages' => 'Not found data to validate.'
                    );
                    break;
            }
        } else {
            $results = array(
                'messageId' => 'OK',
                'passed' => true,
                'messages' => 'OK'
            );
        }

        return $results;
    }

    public function insertAccessKeyMasterData($data) {
        $result = $this->getAccessRepository()->insertAccessKeyMasterData($data);
        return $result;
    }

    public function updateAccessKeyMasterData($data) {
        $result = $this->getAccessRepository()->updateAccessKeyMasterData($data);
        return $result;
    }

    public function insertDantaiMasterData($data) {
        $result = $this->getOrganizationRepository()->insertDantaiMasterData($data);
        return $result;
    }

    public function updateDantaiMasterData($data) {
        $result = $this->getOrganizationRepository()->updateDantaiMasterData($data);
        return $result;
    }

}
