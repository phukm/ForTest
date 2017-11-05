<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @author minhbn1<minhbn1@fsoft.com.vn>
 *
 * @link      http://github.com/zendframework/BasicConstruction for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ConsoleInvitationTest;
use ConsoleInvitation\Service\ConfigDateService;
use ConsoleInvitation\Service\Factory\ConfigDateServiceFactory;
use Dantai\Test\AbstractHttpControllerTestCase;

class ConfigDateTest extends AbstractHttpControllerTestCase {

    private $csvData = array(
        0 => array(
            0  => 'Year',
            1  => 'Kai',
            2  => 'Application Deadline From',
            3  => 'Application Deadline To',
            4  => 'Combini Payment Deadline',
            5  => 'Credit Card Payment Deadline',
            6  => 'Satellite Site Application Deadline by Student',
            7  => 'Exam Date_1st Stage_Friday',
            8  => 'Exam Date_1st Stage_Saturday',
            9  => 'Exam Date_1st Stage_Sunday',
            10 => 'Exam Date_2nd Stage_A',
            11 => 'Exam Date_2nd Stage_B',
            12 => 'Results Date_1st Stage',
            13 => 'Results Date_2nd Stage',
        ),
        1 => array(
            0  => '2016',
            1  => '2',
            2  => '2016-11-10 13:22:01',
            3  => '2016-11-10 14:22:01',
            4  => '2016-11-10 14:22:01',
            5  => '2016-11-10 14:22:01',
            6  => '2016-11-10 14:22:01',
            7  => '2016-11-10 14:22:01',
            8  => '2016-11-10 14:22:01',
            9  => '2016-11-10 14:22:01',
            10 => '2016-11-10 14:22:01',
            11 => '2016-11-11 14:22:01',
            12 => '2016-11-10 14:22:01',
            13 => '2016-11-10 14:22:01',
        ),
        2 => array(
            0  => '2016',
            1  => '3',
            2  => '2016-11-10 13:22:01',
            3  => '2016-11-10 14:22:01',
            4  => '2016-11-10 14:22:01',
            5  => '2016-11-10 14:22:01',
            6  => '2016-11-10 14:22:01',
            7  => '2016-11-10 14:22:01',
            8  => '2016-11-10 14:22:01',
            9  => '2016-11-10 14:22:01',
            10 => '2016-11-10 14:22:01',
            11 => '2016-11-10 14:22:01',
            12 => '2016-11-11 14:22:01',
            13 => '2016-11-10 14:22:01',
        ),
    );

    public function testValidateFormatWhenWrongHeader(){
        $configDateServiceFactory = new ConfigDateServiceFactory();
        $configDateService = $configDateServiceFactory->createService($this->getApplicationServiceLocator());

        // Case 1: headers not enough
        $data = $this->csvData;
        array_pop($data[0]);
        $result = $configDateService->validateFormatTemplateConfigDate($data);
        $this->assertFalse($result, 'test headers not enough');

        // Case 2: headers empty
        $data = $this->csvData;
        $data[0] = array();
        $result = $configDateService->validateFormatTemplateConfigDate($data);
        $this->assertFalse($result, 'test headers empty');

        // Case 3: header wrong name
        $data = $this->csvData;
        $data[2] = 'Application Deadline';
        $result = $configDateService->validateFormatTemplateConfigDate($data);
        $this->assertFalse($result, 'test header wrong name');

    }

    public function testValidateFormatWhenWrongData(){
        $configDateServiceFactory = new ConfigDateServiceFactory();
        $configDateService = $configDateServiceFactory->createService($this->getApplicationServiceLocator());

        // Case 1: row cell not enough
        $data = $this->csvData;
        array_pop($data[1]);
        $result = $configDateService->validateFormatTemplateConfigDate($data);
        $this->assertFalse($result, 'test row cell not enough');

        //Case 2s: wrong format datetime
        $data = $this->csvData;
        $data[1][8] = '2016/11/10 14:22:01';
        $result = $configDateService->validateFormatTemplateConfigDate($data);
        $this->assertFalse($result, 'test header wrong name');

    }

    public function testValidateFormatWhenCorrectData(){
        $configDateServiceFactory = new ConfigDateServiceFactory();
        $configDateService = $configDateServiceFactory->createService($this->getApplicationServiceLocator());

        $data = $this->csvData;
        $result = $configDateService->validateFormatTemplateConfigDate($data);
        $this->assertTrue($result);
    }

    public function testUploadConfigToS3Success(){
        // mock AwsS3Client and writeObject function
        $awsS3Mock = $this->getMockBuilder('Dantai\Aws\AwsS3Client')
            ->disableOriginalConstructor()
            ->setMethods(array('writeObject'))
            ->getMock();
        $awsS3Mock->expects($this->any())
            ->method('writeObject')
            ->will($this->returnValue(array('status' => 1)));

        // mock ConfigDateService and function getAwsS3Client return AwsS3Client Mock
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('getAwsS3Client'))
            ->getMock();
        $configDateServiceMock->expects($this->any())
            ->method('getAwsS3Client')
            ->will($this->returnValue($awsS3Mock));

        $result = $configDateServiceMock->uploadConfigDateToS3('mockData','abc.csv');
        $this->assertTrue($result['isSuccess']);
    }

    public function testUploadConfigToS3Fail(){
        // mock AwsS3Client and writeObject function
        $awsS3Mock = $this->getMockBuilder('Dantai\Aws\AwsS3Client')
            ->disableOriginalConstructor()
            ->setMethods(array('writeObject'))
            ->getMock();
        $awsS3Mock->expects($this->any())
            ->method('writeObject')
            ->will($this->returnValue(array('status' => 0)));

        // mock ConfigDateService and function getAwsS3Client return AwsS3Client Mock
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('getAwsS3Client'))
            ->getMock();
        $configDateServiceMock->expects($this->any())
            ->method('getAwsS3Client')
            ->will($this->returnValue($awsS3Mock));

        $result = $configDateServiceMock->uploadConfigDateToS3('mockData','abc.csv');
        $this->assertFalse($result['isSuccess']);
    }

    public function testDownloadConfigDateFromS3Success(){
        // mock AwsS3Client and writeObject function
        $awsS3Mock = $this->getMockBuilder('Dantai\Aws\AwsS3Client')
            ->disableOriginalConstructor()
            ->setMethods(array('readObject'))
            ->getMock();
        $awsS3Mock->expects($this->any())
            ->method('readObject')
            ->will($this->returnValue(array('status' => 1)));

        // mock ConfigDateService and function getAwsS3Client return AwsS3Client Mock
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('getAwsS3Client'))
            ->getMock();
        $configDateServiceMock->expects($this->any())
            ->method('getAwsS3Client')
            ->will($this->returnValue($awsS3Mock));

        $result = $configDateServiceMock->downloadConfigDateFromS3('filename.csv');
        $this->assertFalse($result['isSuccess']);
    }

    public function testDownloadConfigDateFromS3FileNotFound(){
        // mock AwsS3Client and writeObject function
        $awsS3Mock = $this->getMockBuilder('Dantai\Aws\AwsS3Client')
            ->disableOriginalConstructor()
            ->setMethods(array('readObject'))
            ->getMock();
        $awsS3Mock->expects($this->any())
            ->method('readObject')
            ->will($this->returnValue(array('status' => 404)));

        // mock ConfigDateService and function getAwsS3Client return AwsS3Client Mock
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('getAwsS3Client'))
            ->getMock();
        $configDateServiceMock->expects($this->any())
            ->method('getAwsS3Client')
            ->will($this->returnValue($awsS3Mock));

        $result = $configDateServiceMock->downloadConfigDateFromS3('filename.csv');
        $this->assertFalse($result['isSuccess']);
    }

    public function testDownloadConfigDateFromS3BucketNotExist(){
        // mock AwsS3Client and writeObject function
        $awsS3Mock = $this->getMockBuilder('Dantai\Aws\AwsS3Client')
            ->disableOriginalConstructor()
            ->setMethods(array('readObject'))
            ->getMock();
        $awsS3Mock->expects($this->any())
            ->method('readObject')
            ->will($this->returnValue(array('status' => 0)));

        // mock ConfigDateService and function getAwsS3Client return AwsS3Client Mock
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('getAwsS3Client'))
            ->getMock();
        $configDateServiceMock->expects($this->any())
            ->method('getAwsS3Client')
            ->will($this->returnValue($awsS3Mock));

        $result = $configDateServiceMock->downloadConfigDateFromS3('filename.csv');
        $this->assertFalse($result['isSuccess']);
    }

    public function testDownloadConfigDateFromS3WrongExtension(){
        // mock ConfigDateService and function getAwsS3Client return AwsS3Client Mock
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('getAwsS3Client'))
            ->getMock();
        $configDateServiceMock->expects($this->any())
            ->method('getAwsS3Client')
            ->will($this->returnValue(false));

        $result = $configDateServiceMock->downloadConfigDateFromS3('filename.abc');
        $this->assertFalse($result['isSuccess']);
        $this->assertEquals($result['message'], "Error: Please select CSV file");
    }

    public function testImportConfigDateSuccess(){
        // mock ConfigDateService
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('downloadConfigDateFromS3', 'validateFormatTemplateConfigDate', 'updateConfigDate'))
            ->getMock();
        $configDateServiceMock->expects($this->any())
            ->method('downloadConfigDateFromS3')
            ->will(
                $this->returnValue(array(
                                       'isSuccess' => true,
                                       'data'      => $this->csvData,
                                       'message'   => 'test'
                                   ))
            );
        $configDateServiceMock->expects($this->any())
            ->method('validateFormatTemplateConfigDate')
            ->will($this->returnValue(true));
        $configDateServiceMock->expects($this->any())
            ->method('updateConfigDate')
            ->will($this->returnValue(true));

        $result = $configDateServiceMock->importConfigDate('filename.csv');
        $this->assertTrue($result);
    }

    public function testImportConfigDateDownloadFail(){
        // mock ConfigDateService
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('downloadConfigDateFromS3', 'updateConfigDate'))
            ->getMock();
        $configDateServiceMock->expects($this->any())
            ->method('downloadConfigDateFromS3')
            ->will(
                $this->returnValue(array(
                                       'isSuccess' => false,
                                       'data'      => $this->csvData,
                                       'message'   => 'test'
                                   ))
            );
        $configDateServiceMock->expects($this->any())
            ->method('updateConfigDate')
            ->will($this->returnValue(true));

        $result = $configDateServiceMock->importConfigDate('filename.csv');
        $this->assertFalse($result);
    }

    public function testImportConfigDateInValidFormatTemplate(){
        // mock ConfigDateService
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('downloadConfigDateFromS3', 'updateConfigDate'))
            ->getMock();
        $data = $this->csvData;
        array_pop($data[0]);
        $configDateServiceMock->expects($this->any())
            ->method('downloadConfigDateFromS3')
            ->will(
                $this->returnValue(array(
                                       'isSuccess' => true,
                                       'data'      => $data,
                                       'message'   => 'test'
                                   ))
            );
        $configDateServiceMock->expects($this->any())
            ->method('updateConfigDate')
            ->will($this->returnValue(true));

        $result = $configDateServiceMock->importConfigDate('filename.csv');
        $this->assertFalse($result);
    }

    public function testImportConfigDateUpdateDbFail(){
        // mock ConfigDateService
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('downloadConfigDateFromS3', 'updateConfigDate'))
            ->getMock();
        $data = $this->csvData;
        $configDateServiceMock->expects($this->any())
            ->method('downloadConfigDateFromS3')
            ->will(
                $this->returnValue(array(
                                       'isSuccess' => true,
                                       'data'      => $data,
                                       'message'   => 'test'
                                   ))
            );
        $configDateServiceMock->expects($this->any())
            ->method('updateConfigDate')
            ->will($this->returnValue(false));

        $result = $configDateServiceMock->importConfigDate('filename.csv');
        $this->assertFalse($result);
    }

    public function testExportConfigDateSuccess(){
        // mock ConfigDateService
        /** @var ConfigDateService $configDateServiceMock */
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('getExportConfigDateFromDB', 'uploadConfigDateToS3'))
            ->getMock();
        $configDateServiceMock->expects($this->any())
            ->method('getExportConfigDateFromDB')
            ->will(
                $this->returnValue($this->csvData)
            );
        $configDateServiceMock->expects($this->any())
            ->method('uploadConfigDateToS3')
            ->will(
                $this->returnValue(
                    array('isSuccess' => true, 'message' => 'ABC')
                )
            );

        $result = $configDateServiceMock->exportConfigDate(2016, 1);
        $this->assertTrue($result);
    }

    public function testExportConfigDateEmpty(){
        // mock ConfigDateService
        /** @var ConfigDateService $configDateServiceMock */
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('getExportConfigDateFromDB', 'uploadConfigDateToS3'))
            ->getMock();
        $configDateServiceMock->expects($this->any())
            ->method('getExportConfigDateFromDB')
            ->will(
                $this->returnValue(array())
            );
        $configDateServiceMock->expects($this->any())
            ->method('uploadConfigDateToS3')
            ->will(
                $this->returnValue(
                    array('isSuccess' => true, 'message' => 'ABC')
                )
            );

        $result = $configDateServiceMock->exportConfigDate(2016, 1);
        $this->assertFalse($result);
    }

    public function testExportConfigDateFail(){
        // mock ConfigDateService
        $configDateServiceMock = $this->getMockBuilder('\ConsoleInvitation\Service\ConfigDateService')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('getExportConfigDateFromDB', 'uploadConfigDateToS3'))
            ->getMock();
        $configDateServiceMock->expects($this->any())
            ->method('getExportConfigDateFromDB')
            ->will(
                $this->returnValue($this->csvData)
            );
        $configDateServiceMock->expects($this->any())
            ->method('uploadConfigDateToS3')
            ->will(
                $this->returnValue(
                    array('isSuccess' => false, 'message' => 'ABC')
                )
            );

        $result = $configDateServiceMock->exportConfigDate(2016, 1);
        $this->assertFalse($result);
    }
}
