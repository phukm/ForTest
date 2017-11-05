<?php
namespace PupilMnt;

class ValidateYearSchoolyearGradeAndClassTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    private $dataImport = array(
        'year'=>'2015',
        'schoolYear'=>'小学校1年相当',
        'orgSchoolYear'=>'中学1年生',
        'class'=>'１組',
    ) ;
    private $dataUniversalGrade = array(
        'UniversalGrade1'=>'Grade1',
        'UniversalGrade2'=>'Grade2',
    );
    
    private $resultOrgSchoolYear = array(
        0 => array(
            'orgSchoolYearId' => 111,
            'displayName' => '小学1年生',
            'schoolYearId' => 1,
            'name' => '小学1年生',
        ),
        1 => array(
            'orgSchoolYearId' => 2,
            'displayName' => '小学2年生',
            'schoolYearId' => 2,
            'name' => '小学2年生',
        )
    );
    
    private $resultClass = array(
        0 => array(
            'name' => '小学1年生',
            'displayName' => '小学1年生',
            'className' => 'A',
            'year' => 2016,
        ),
        1 => array(
            'name' => '小学2年生',
            'displayName' => '小学2年生',
            'className' => 'B',
            'year' => 2016,
        )
    );
    private $masterData = array(
        0 => array(
            'SchoolYear' => 'UniversalGrade1',
            'OrgSchoolYear' => 'S'
        )
    );

    public function getMockOrgSchoolYear(){
        
        $orgSchoolYearMock = $this->getMockBuilder('Application\Entity\Repository\OrgSchoolYearRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $orgSchoolYearMock->expects($this->any())
                ->method('getOrgSchoolYearName')
                ->will($this->returnValue($this->resultOrgSchoolYear));
        return $orgSchoolYearMock;
    }
    
    public function getMockClassJ(){

        $classjMock = $this->getMockBuilder('Application\Entity\Repository\ClassJRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $classjMock->expects($this->any())
                ->method('getListClassByOrgAndGrade')
                ->will($this->returnValue($this->resultClass));
        return $classjMock;
    }

    /* @var $pupilService \PupilMnt\Service\ImportPupilService */
    
    public function testReturnNewListDataImportInFunctionValidateCommonMasterData() {
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $orgSchoolYearMock = $this->getMockOrgSchoolYear();
        $pupilService->setOrgSchoolYearRepository($orgSchoolYearMock);
        for ($i = 0; $i < 10; $i++) {
            $dataImport[$i] = array(
                'year' => 2015,
                'orgSchoolYear' => $i == 0 ? '小学2年生' : '小学2年生' . $i,
                'class' => '１生',
            );
        }
        list($errors, $orgSchoolYearsNew, $schoolYearIdsUsed, $dataImport) = $pupilService->validateCommonMasterData($dataImport);
        
        $this->assertEquals('小学2年生', $dataImport[0]['schoolYear']);
    }
    
    public function testReturnEmptyListNewOrgSchoolYearInFunctionValidateCommonMasterData() {
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $orgSchoolYearMock = $this->getMockOrgSchoolYear();
        $pupilService->setOrgSchoolYearRepository($orgSchoolYearMock);
        for ($i = 0; $i < 3; $i++) {
            $dataImport[$i] = array(
                'year' => 2015,
                'orgSchoolYear' => '小学2年生',
                'class' => '１生',
            );
        }
        list($errors, $orgSchoolYearsNew, $schoolYearIdsUsed, $dataImport) = $pupilService->validateCommonMasterData($dataImport);
        
        $resultExpect = array();
        $this->assertEquals($resultExpect, $orgSchoolYearsNew);
    }
    
    public function testReturnListNewOrgSchoolYearInFunctionValidateCommonMasterData() {
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $orgSchoolYearMock = $this->getMockOrgSchoolYear();
        $pupilService->setOrgSchoolYearRepository($orgSchoolYearMock);
        for ($i = 0; $i < 3; $i++) {
            $dataImport[$i] = array(
                'year' => 2015,
                'orgSchoolYear' => '中学1年生' . $i,
                'class' => '１生',
            );
        }
        list($errors, $orgSchoolYearsNew, $schoolYearIdsUsed, $dataImport) = $pupilService->validateCommonMasterData($dataImport);
        
        $resultExpect = array(
            '中学1年生0' => '中学1年生0',
            '中学1年生1' => '中学1年生1',
            '中学1年生2' => '中学1年生2',
        );
        $this->assertEquals($resultExpect, $orgSchoolYearsNew);
    }
    
    public function testReturnEmptyListSchoolYearIdsUsedInFunctionValidateCommonMasterData(){
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $this->resultOrgSchoolYear = array();
        $orgSchoolYearMock = $this->getMockOrgSchoolYear();
        $pupilService->setOrgSchoolYearRepository($orgSchoolYearMock);
        for ($i = 0; $i < 3; $i++) {
            $dataImport[$i] = array(
                'year' => 2015,
                'orgSchoolYear' => '中学1年生',
                'class' => '１生',
            );
        }
        list($errors, $orgSchoolYearsNew, $schoolYearIdsUsed, $dataImport) = $pupilService->validateCommonMasterData($dataImport);
        $resultExpect = array();
        $this->assertEquals($resultExpect, $schoolYearIdsUsed);
    }
    
    public function testReturnListSchoolYearIdsUsedInFunctionValidateCommonMasterData(){
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $orgSchoolYearMock = $this->getMockOrgSchoolYear();
        $pupilService->setOrgSchoolYearRepository($orgSchoolYearMock);
        for ($i = 0; $i < 3; $i++) {
            $dataImport[$i] = array(
                'year' => 2015,
                'orgSchoolYear' => '中学1年生',
                'class' => '１生',
            );
        }
        list($errors, $orgSchoolYearsNew, $schoolYearIdsUsed, $dataImport) = $pupilService->validateCommonMasterData($dataImport);
        $resultExpect = array(1, 2);
        $this->assertEquals($resultExpect, $schoolYearIdsUsed);
    }

    public function testValidateYearFormatFunction(){
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $resule = $pupilService->isYearFormat(2015);
        $this->assertEquals($resule, true);
    }
    public function testValidateEmptyYearInFunctionValidateCommonMasterData(){
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $yearName = $pupilService->translate('year');
        $msg = $pupilService->translate('MsgYearError1');
        $this->dataImport['year'] = '';
        $data[0] = $this->dataImport;
        list($errors, $orgSchoolYearsNew, $schoolYearIdsUsed, $dataImport) = $pupilService->validateCommonMasterData($data);
        $this->assertEquals($errors[1][$yearName], $msg);
    }

    public function testValidateEmptyOrgSchoolYearInFunctionValidateCommonMasterData(){
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $yearName = $pupilService->translate('grade');
        $msg = $pupilService->translate('MsgSchoolYearError1');
        $this->dataImport['orgSchoolYear'] = '';
        $data[0] = $this->dataImport;
        list($errors, $orgSchoolYearsNew, $schoolYearIdsUsed, $dataImport) = $pupilService->validateCommonMasterData($data);
        $this->assertEquals($errors[1][$yearName], $msg);
    }
    public function testValidateEmptyClassInFunctionValidateCommonMasterData(){
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $yearName = $pupilService->translate('class');
        $msg = $pupilService->translate('MsgClassError1');
        $this->dataImport['class'] = '';
        $data[0] = $this->dataImport;
        list($errors, $orgSchoolYearsNew, $schoolYearIdsUsed, $dataImport) = $pupilService->validateCommonMasterData($data);
        $this->assertEquals($errors[1][$yearName], $msg);
    }
    public function testValidateYearFormatInFunctionValidateCommonMasterData(){
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $yearName = $pupilService->translate('year');
        $msg = $pupilService->translate('MsgYearError2');
        $this->dataImport['year'] = 1;
        $data[0] = $this->dataImport;
        list($errors, $orgSchoolYearsNew, $schoolYearIdsUsed, $dataImport) = $pupilService->validateCommonMasterData($data);
        $this->assertEquals($errors[1][$yearName], $msg);
    }
    public function testValidateUniversalGradeAndGradeBeforeSaveMasterData(){
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $pupilService->setListGrade($this->dataUniversalGrade);
        $result = $pupilService->isValidMappingGrade($this->masterData);
        $this->assertEquals($result, 0);
    }
  
}