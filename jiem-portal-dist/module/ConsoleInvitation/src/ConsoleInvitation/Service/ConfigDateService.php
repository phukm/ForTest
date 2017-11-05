<?php

/**
 * @description this function process business for Org Goal and CSE score
 * @author minhbn1<minhbn1@fsoft.com.vn>
 */

namespace ConsoleInvitation\Service;

use Application\Entity\EikenSchedule;
use Application\Entity\Repository\EikenScheduleRepository;
use ConsoleInvitation\ConsoleInvitationConst;
use ConsoleInvitation\Service\ServiceInterface\ConfigDateServiceInterface;
use Dantai\Aws\AwsS3Client;
use Dantai\Utility\CsvHelper;
use Dantai\Utility\DateHelper;
use Dantai\Utility\ValidateHelper;
use DateTime;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ConfigDateService implements ConfigDateServiceInterface, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    protected $awsS3Client = null;
    protected $entityManager;
    protected $eikenScheduleRepo;
    protected $config;

    // constructor
    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager)
    {
        $this->setServiceLocator($serviceManager);
    }

    /**
     * @return AwsS3Client
     */
    public function getAwsS3Client()
    {
        if (!$this->awsS3Client) {
            $this->awsS3Client = AwsS3Client::getInstance();
        }

        return $this->awsS3Client;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if (!$this->entityManager) {
            $this->entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }

        return $this->entityManager;
    }

    public function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->getServiceLocator()->get('config');
        }

        return $this->config;
    }

    /**
     * @return EikenScheduleRepository
     */
    public function getEikenScheduleRepo()
    {
        if (!$this->eikenScheduleRepo) {
            $this->eikenScheduleRepo = $this->getEntityManager()->getRepository('\Application\Entity\EikenSchedule');
        }

        return $this->eikenScheduleRepo;
    }

    public function importConfigDate($fileName)
    {
        try {
            $result = $this->downloadConfigDateFromS3($fileName);
            echo $result['message'] . PHP_EOL;
            if (!$result['isSuccess']) return false;

            $isValid = $this->validateFormatTemplateConfigDate($result['data']);
            if (!$isValid) {
                echo "File $fileName is wrong format. Please fix it and try import again!" . PHP_EOL;

                return false;
            }

            $dbHeader = array_keys($this->getConfig()['headerExcelExport']['configDate']);
            $convertedData = CsvHelper::convertCSVArrayToAssociateArray($result['data'], $dbHeader);
            $result = $this->updateConfigDate($convertedData);
            if (!$result) {
                echo "Import into DB fail!" . PHP_EOL;

                return false;
            }
            echo "Import into DB successfully!" . PHP_EOL;

            return true;
        } catch (\Exception $ex) {
            echo $ex->getMessage() . PHP_EOL;

            return false;
        }
    }


    /**
     * Function export eikenSchedule to S3
     * Template CSV file
     * @param $year
     * @param $kai
     * @return array isSuccess: bool
     * isSuccess: bool
     * message: error message
     */
    public function exportConfigDate($year, $kai)
    {
        try {
            $eikenScheduleData = $this->getExportConfigDateFromDB($year, $kai);
            // if empty data, throw msg
            if (empty($eikenScheduleData)) {
                echo "Error: Doesn't exist data for $year and kai $kai" . PHP_EOL;

                return false;
            }
            $eikenScheduleStreamCsv = CsvHelper::arrayToStrCsv($eikenScheduleData);
            $filename = $this->createFileName($year, $kai);
            $result = $this->uploadConfigDateToS3($eikenScheduleStreamCsv, $filename);
            echo $result['message'] . PHP_EOL;
            if (!$result['isSuccess']) {
                return false;
            }

            return true;
        } catch (\Exception $ex) {
            echo $ex->getMessage() . PHP_EOL;

            return false;
        }
    }

    public function downloadConfigDateFromS3($fileName)
    {
        $bucket = AwsS3Client::BUCKET_PREFIX . getenv('APP_ENV');
        $path = AwsS3Client::S3_CONFIG_DATE_IMPORT_PATH;
        $keyObject = "$path/$fileName";
        try {
            if (!CsvHelper::isCsvExtension($fileName)) {
                throw new \Exception("Please select CSV file");
            }
            $response = $this->getAwsS3Client()->readObject($bucket, $keyObject);

            // when success
            if ($response['status'] == 1) {
                // convert file
                $configDateData = CsvHelper::csvStrToArray($response['content']['Body']);

                return array(
                    'isSuccess' => true,
                    'message'   => "File $fileName is downloaded successfully!",
                    'data'      => $configDateData,
                );
            }

            // when fail
            $errorMessage = 'Error';
            if ($response['status'] == 404) {
                $errorMessage = "File $fileName doesn't exist in bucket $bucket/$path";
            } else if ($response['status'] == 0) {
                $errorMessage = "The bucket $bucket doesn't exist!";
            }

            return array(
                'isSuccess' => false,
                'message'   => "Error: " . $errorMessage,
            );
        } catch (\Exception $ex) {
            return array(
                'isSuccess' => false,
                'message'   => "Error: " . $ex->getMessage(),
            );
        }
    }

    public function updateConfigDate($configDateData)
    {
        if (!empty($configDateData)) {
            $em = $this->getEntityManager();
            $em->getConnection()->beginTransaction();
            try {
                foreach ($configDateData as $configDate) {
                    $eikenScheduleObject = $this->getEikenScheduleRepo()
                        ->findOneBy(array(
                                        'year' => $configDate['year'],
                                        'kai'  => $configDate['kai'],
                                    ));
                    $configDate['round2ExamDate'] = $configDate['round2Day2ExamDate'];
                    $eikenScheduleObject = empty($eikenScheduleObject) ? new EikenSchedule() : $eikenScheduleObject;
                    $hydrator = new DoctrineObject($em, '\Application\Entity\EikenSchedule');
                    $eikenScheduleObject = $hydrator->hydrate($configDate, $eikenScheduleObject, $byValue = false);
                    $eikenScheduleObject->setExamName('英検');
                    $eikenScheduleObject->setInsertBy('ConsoleSystem');
                    $eikenScheduleObject->setUpdateBy('ConsoleSystem');

                    $em->persist($eikenScheduleObject);
                }
                $em->flush();
                $em->getConnection()->commit();

                return true;
            } catch (\Exception $ex) {
                $em->getConnection()->rollback();
            }
        }

        return false;
    }

    public function getExportConfigDateFromDB($year, $kai)
    {
        $eikenScheduleData = $this->getListEikenSchedule($year, $kai);
        $dbHeader = array_values($this->getConfig()['headerExcelExport']['configDate']);

        return !empty($eikenScheduleData)
            ? CsvHelper::convertAssociateArrayToCSVArray($eikenScheduleData, $dbHeader)
            : null;
    }

    public function getListEikenSchedule($year, $kai)
    {
        return $this->getEikenScheduleRepo()->getListEikenSchedule($year, $kai);
    }

    public function uploadConfigDateToS3($content, $filename)
    {
        $bucket = AwsS3Client::BUCKET_PREFIX . getenv('APP_ENV');
        $path = AwsS3Client::S3_CONFIG_DATE_EXPORT_PATH;
        $keyObject = "$path/$filename";
        try {
            $response = $this->getAwsS3Client()->writeObject($bucket, $keyObject, $content);

            // when success
            if ($response['status'] == 1) {
                return array(
                    'isSuccess' => true,
                    'message'   => "File $filename is uploaded successfully to $bucket/$path",
                );
            }

            // when fail
            return array(
                'isSuccess' => false,
                'message'   => "Error: Fail to upload file $filename to $bucket/$path",
            );
        } catch (\Exception $ex) {
            return array(
                'isSuccess' => false,
                'message'   => "Error: " . $ex->getMessage(),
            );
        }
    }

    /**
     * Function validate format of import file
     * check header name, require data and format data
     * @param $configDateData
     * @return bool
     */
    public function validateFormatTemplateConfigDate($configDateData)
    {
        $headers = array_values($this->getConfig()['headerExcelExport']['configDate']);
        $headerKeys = array_keys($this->getConfig()['headerExcelExport']['configDate']);
        // check empty file, check number column
        if (empty($configDateData) || (count($configDateData[0]) != count($headers))) {
            return false;
        }

        // validate header
        $CsvHeaders = $configDateData[0];
        for ($i = 0; $i < count($headers); $i++) {
            if ($CsvHeaders[$i] != $headers[$i]) return false;
        }

        // get order index value
        $idxKai = array_search('kai', $headerKeys);
        $idxYear = array_search('year', $headerKeys);
        $idxDeadlineFrom = array_search('deadlineFrom', $headerKeys);
        $idxDeadlineTo = array_search('deadlineTo', $headerKeys);

        // validate data
        // order of columns is following [0: year, 1: kai, 2:datelineFrom, 3:datelineTo,...]
        for ($i = 1; $i < count($configDateData); $i++) {
            $row = $configDateData[$i];
            // check number cell / row
            if (count($row) != count($headers)) return false;
            // check cell empty, format datetime
            for ($k = 0; $k < count($row); $k++) {
                $cell = $row[$k];
                if (empty($cell) || ($k > $idxKai && !DateHelper::isDateFormat($cell, DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT))) {
                    return false;
                } elseif ($k == $idxKai && !ValidateHelper::isNumeric($cell)) {
                    return false;
                } elseif ($k == $idxYear && !ValidateHelper::isYear($cell)) {
                    return false;
                }
            }
            // validate datelineFrom < datelineTo
            $datelineFrom = Datetime::createFromFormat(DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT, $row[$idxDeadlineFrom]);
            $datelineTo = Datetime::createFromFormat(DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT, $row[$idxDeadlineTo]);
            if($datelineFrom >= $datelineTo){
                return false;
            }
            
        }

        return true;
    }

    /**
     * Function used to create filename for export config date
     * @param $year
     * @param $kai
     * @return string
     */
    public function createFileName($year, $kai)
    {
        $strDate = date(DateHelper::DATE_FORMAT_EXPORT_EXCEL);
        $strTime = date(DateHelper::TIME_FORMAT_EXPORT_EXCEL);
        if (isset($year) && isset($kai)) {
            $subFixFilename = $year . "_" . $kai . "_" . $strDate . "_" . $strTime;
        } else if (isset($year)) {
            $subFixFilename = $year . "_" . $strDate . "_" . $strTime;
        } else {
            $subFixFilename = $strDate . "_" . $strTime;
        }
        $fileName = sprintf(ConsoleInvitationConst::EXPORT_CONFIG_DATE_FILENAME, $subFixFilename);

        return $fileName;
    }
}
