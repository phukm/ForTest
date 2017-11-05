<?php

namespace Application;

class DantaiServiceFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase {

    public function testGetListPriceOfOrganization() {
        $this->login();

        $expectResult = array(
            1 => [
                1 => ['price' => 7400, 'name' => '1級',],
                2 => ['price' => 5900, 'name' => '準1級',],
            ],
            0 => [
                1 => ['price' => 7400, 'name' => '1級',],
                2 => ['price' => 5900, 'name' => '準1級',],
            ]
        );

        $dantaiService = $this->getApplicationServiceLocator()
                ->get('Application\Service\DantaiServiceInterface');

        $dantaiService->setEntityManager($this->getEntityMock());

        $listPriceOfOrganization = $this->getApplicationServiceLocator()
                ->get('Application\Service\DantaiServiceInterface')
                ->getListPriceOfOrganization(10566000, array(1, 2), $this->mockupToGetSpecialPrice());

        $this->assertEquals($listPriceOfOrganization, $expectResult);
    }

    public function testGetListPriceOfOrganizationWithEikenLevelIdsIsNull() {
        $this->login();

        $expectResult = array(
            1 => [
                1 => ['price' => 7400, 'name' => '1級',],
                2 => ['price' => 5900, 'name' => '準1級',],
                3 => ['price' => 4800, 'name' => '2級',],
                4 => ['price' => 3500, 'name' => '準2級',],
                5 => ['price' => 2200, 'name' => '3級',],
                6 => ['price' => 1600, 'name' => '4級',],
                7 => ['price' => 1500, 'name' => '5級',],
            ],
            0 => [
                1 => ['price' => 7400, 'name' => '1級',],
                2 => ['price' => 5900, 'name' => '準1級',],
                3 => ['price' => 4800, 'name' => '2級',],
                4 => ['price' => 3500, 'name' => '準2級',],
                5 => ['price' => 2200, 'name' => '3級',],
                6 => ['price' => 1600, 'name' => '4級',],
                7 => ['price' => 1500, 'name' => '5級',],
            ]
        );

        $dantaiService = $this->getApplicationServiceLocator()
                ->get('Application\Service\DantaiServiceInterface');

        $dantaiService->setEntityManager($this->getEntityMock());

        $listPriceOfOrganization = $this->getApplicationServiceLocator()
                ->get('Application\Service\DantaiServiceInterface')
                ->getListPriceOfOrganization(10566000, null, $this->mockupToGetSpecialPrice());

        $this->assertEquals($listPriceOfOrganization, $expectResult);
    }

    public function mockupToGetSpecialPrice() {
        return array(
            'orgSchoolYearId' => 3,
            'year' => 2016,
            'kai' => 2,
            'unitTestData' => array(
                0 => [
                    'organizationNo' => 10566000,
                    'schoolYearCode' => 3,
                    'schoolClassification' => 00,
                    'year' => 2016,
                    'kai' => 2,
                    'hallType' => 1,
                    'lev1' => 7400,
                    'preLev1' => 5900,
                    'lev2' => 4800,
                    'preLev2' => 3500,
                    'lev3' => 2200,
                    'lev4' => 1600,
                    'lev5' => 1500,
                ],
                1 => [
                    'organizationNo' => 10566000,
                    'schoolYearCode' => 3,
                    'schoolClassification' => 00,
                    'year' => 2016,
                    'kai' => 2,
                    'hallType' => 0,
                    'lev1' => '',
                    'preLev1' => '',
                    'lev2' => 4800,
                    'preLev2' => 3500,
                    'lev3' => 2200,
                    'lev4' => 1600,
                    'lev5' => 1500,
                ],
            ),
        );
    }

    public function getEntityMock() {
        $repositoryMock = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository',), array(), '', false);

        $repositoryMock->expects($this->any())
                ->method('getRepository')
                ->will(
                        $this->returnValueMap(
                                array(
                                    array('\Application\Entity\SpecialPrice', $this->getEntitySpecialPriceMock()),
                                    array('\Application\Entity\EikenLevel', $this->getEntityEikenLevelMock()),
                                )
                        )
        );

        return $repositoryMock;
    }

    public function getEntitySpecialPriceMock() {
        $specialPriceMock = $this->getMockBuilder('Application\Entity\Repository\SpecialPriceRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $specialPriceMock->expects($this->any())
                ->method('getSpecialPrice')
                ->will($this->returnValue($this->mockupToGetSpecialPrice()['unitTestData']));

        return $specialPriceMock;
    }

    public function getEntityEikenLevelMock() {
        $data = array(
            1 => array(
                'id' => 1,
                'levelName' => '1級'
            ),
            2 => array(
                'id' => 2,
                'levelName' => '準1級'
            ),
            3 => array(
                'id' => 3,
                'levelName' => '2級'
            ),
            4 => array(
                'id' => 4,
                'levelName' => '準2級'
            ),
            5 => array(
                'id' => 5,
                'levelName' => '3級'
            ),
            6 => array(
                'id' => 6,
                'levelName' => '4級'
            ),
            7 => array(
                'id' => 7,
                'levelName' => '5級'
            ),
        );
        $eikenLevelMock = $this->getMockBuilder('Application\Entity\Repository\EikenLevelRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $eikenLevelMock->expects($this->any())
                ->method('listEikenLevelName')
                ->will($this->returnValue($data));

        return $eikenLevelMock;
    }

    public function testFunctionCheckFirstCharacter() {
        $this->login();

        $dantaiService = $this->getApplicationServiceLocator()
                ->get('Application\Service\DantaiServiceInterface');

        //        pass
        $listGrade = array('Ｏ1', '2', '3');
        $listClass = array(
            '2015A' . ApplicationConst::DELIMITER_VALUE . 'Ｏ11',
            '2016B' . ApplicationConst::DELIMITER_VALUE . '22',
            '2017C' . ApplicationConst::DELIMITER_VALUE . '22');
        $this->assertEquals($dantaiService->isAlphanumericCharacter($listGrade, 1, array()), true);
        $this->assertEquals($dantaiService->isAlphanumericCharacter($listClass, 2, array()), true);

//        duplicate
        $listGrade = array('Ｏ1', 'A12', 'A13');
        $listClass = array(
            '2015A' . ApplicationConst::DELIMITER_VALUE . 'Ｏ11',
            '2016B' . ApplicationConst::DELIMITER_VALUE . '22',
            '2016B' . ApplicationConst::DELIMITER_VALUE . '22');
        $this->assertEquals($dantaiService->isAlphanumericCharacter($listGrade, 1, array()), false);
        $this->assertEquals($dantaiService->isAlphanumericCharacter($listClass, 2, array()), false);


        $listGrade = array('Ｏ1', 'A22', 'A13');
        $listClass = array(
            '2015A' . ApplicationConst::DELIMITER_VALUE . 'Ｏ11',
            '2016B' . ApplicationConst::DELIMITER_VALUE . '22',
            '2016C' . ApplicationConst::DELIMITER_VALUE . '22');
        $this->assertEquals($dantaiService->isAlphanumericCharacter($listGrade, 1, array('A224')), false);
        $this->assertEquals($dantaiService->isAlphanumericCharacter($listClass, 2, array('2016C' . ApplicationConst::DELIMITER_VALUE . '222')), false);

//        had special char
        $listGrade = array('スGrade', 'A12', 'A13');
        $listClass = array(
            '2015A' . ApplicationConst::DELIMITER_VALUE . 'Ｏ11',
            '2016B' . ApplicationConst::DELIMITER_VALUE . '22',
            '2016B' . ApplicationConst::DELIMITER_VALUE . '22');
        $this->assertEquals($dantaiService->isAlphanumericCharacter($listGrade, 1, array()), false);
        $this->assertEquals($dantaiService->isAlphanumericCharacter($listClass, 2, array()), false);

        $listGrade = array('AGrade', 'BGrade', 'Cださ');
        $listClass = array(
            '2015A' . ApplicationConst::DELIMITER_VALUE . 'Ｏ11',
            '2016B' . ApplicationConst::DELIMITER_VALUE . '22',
            '2016C' . ApplicationConst::DELIMITER_VALUE . '22');
        $this->assertEquals($dantaiService->isAlphanumericCharacter($listGrade, 1, array('A113')), false);
        $this->assertEquals($dantaiService->isAlphanumericCharacter($listClass, 2, array('2016C' . ApplicationConst::DELIMITER_VALUE . '222')), false);
    }

}
