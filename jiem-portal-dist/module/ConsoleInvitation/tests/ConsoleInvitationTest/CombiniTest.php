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

use Application\Entity\EikenLevel;
use Application\Entity\EikenSchedule;
use Application\Entity\InvitationSetting;
use Application\Entity\Organization;
use ConsoleInvitation\Service\Combini;
use Dantai\Test\AbstractHttpControllerTestCase;
use Doctrine\ORM\EntityManager;

class CombiniTest extends AbstractHttpControllerTestCase {

    function __construct() {

    }

    public function testFunctionGenerateTelNoLimit11()
    {
        $this->login();
        $numberKyu = 7;
        $combiniMock = $this->getMockBuilder('\ConsoleInvitation\Service\Combini')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('getLastestTelNoIndex'))
            ->getMock();

        $combiniMock->expects($this->any())
            ->method('getLastestTelNoIndex')
            ->will($this->returnValue(2));

        $invitationSetting = new InvitationSetting();
        $organization = new Organization();
        $organization->setId(123);
        $invitationSetting->setOrganization($organization);
        $telNo = $combiniMock->generateTelNoForTest($invitationSetting, $numberKyu);
        $this->assertEquals(11, strlen((string)$telNo));

        $organization->setId(123456789123);
        $invitationSetting->setOrganization($organization);
        $telNo = $combiniMock->generateTelNoForTest($invitationSetting, $numberKyu);
        $this->assertEquals(11, strlen((string)$telNo));
    }

    public function testFunctionGenerateTelno()
    {
        $this->login();
        $numberKyu = 7;
        $lastTelNoIndex = 1;
        $combiniMock = $this->getMockBuilder('\ConsoleInvitation\Service\Combini')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('getLastestTelNoIndex'))
            ->getMock();

        $combiniMock->expects($this->any())
            ->method('getLastestTelNoIndex')
            ->will($this->returnValue($lastTelNoIndex));

        $invitationSetting = new InvitationSetting();
        $organization = new Organization();
        $organization->setId(123);
        $invitationSetting->setOrganization($organization);
        $telNo = $combiniMock->generateTelNoForTest($invitationSetting, $numberKyu);
        $this->assertEquals('00012300002', $telNo);

    }

    public function testSavePaymentCombiniTempWhenPaymentNotExistAndNotSatellite()
    {
        /** @var Combini $combiniMock */
        $combiniMock = $this->getMockBuilder('\ConsoleInvitation\Service\Combini')
            ->setConstructorArgs(array($this->getApplicationServiceLocator()))
            ->setMethods(array('isSemiStudentDiscount','isExistPayment'))
            ->getMock();

        $combiniMock->expects($this->any())
            ->method('isSemiStudentDiscount')
            ->will($this->returnValue(0));
        $combiniMock->expects($this->any())
            ->method('isExistPayment')
            ->will($this->returnValue(1));

        $pupil = array(
            'id' => 1
        );
        $priceLevels = array(
            '1' => array(
                '1' => array(
                    'price' => 3200
                ),
                '2' => array(
                    'price' => 3000
                )
            ),
            '0' => array(
                '1' => array(
                    'price' => 2000
                ),
                '2' => array(
                    'price' => 1900
                )
            )
        );
        $listKyu = array(1, 2);
        $isSatellite = false; // split flow dantai and satellite.
        $invitationSetting = new InvitationSetting();
        $eikenSchedule = new EikenSchedule();
        $datetime = new \DateTime();
        $datetime->add(new \DateInterval('P10D'));
        $eikenSchedule->setCombiniDeadline($datetime);
        $invitationSetting->setEikenSchedule($eikenSchedule);
        $invitationSetting->setHallType(1);

        $result = $combiniMock->savePaymentCombiniTemp($pupil,$invitationSetting,$priceLevels,$listKyu,$isSatellite);

        $this->assertFalse($result,'payment had exist');
    }

    public function testSavePaymentCombiniTempWhenPaymentNotExistAndSatellite()
    {

        $applyEikenLevelRepoMock = $this->getMockBuilder('\Application\Entity\ApplyEikenLevelRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('findOneBy'))
            ->getMock();

        $applyEikenLevelRepoMock->expects($this->any())
        ->method('findOneBy')
        ->will($this->returnValue(null));

        $entityManagerMock = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository'))
            ->getMock();

        $entityManagerMock->expects($this->any())
            ->method('getRepository')
            ->with('\Application\Entity\ApplyEikenLevel')
            ->will($this->returnValue($applyEikenLevelRepoMock));

        $listEiken = array(
            '1' => new EikenLevel(),
            '2' => new EikenLevel(),
            '3' => new EikenLevel()
        );

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('doctrine.entitymanager.orm_default', $entityManagerMock);

        /** @var Combini $combiniMock */
        $combiniMock = $this->getMockBuilder('\ConsoleInvitation\Service\Combini')
            ->setConstructorArgs(array($serviceManager))
            ->setMethods(array('isSemiStudentDiscount','isExistPayment','getEntityManager','getEikenLevel'))
            ->getMock();

        $combiniMock->expects($this->any())
            ->method('isSemiStudentDiscount')
            ->will($this->returnValue(0));
        $combiniMock->expects($this->any())
            ->method('isExistPayment')
            ->will($this->returnValue(1));
        $combiniMock->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($entityManagerMock));
        $combiniMock->expects($this->any())
            ->method('getEikenLevel')
            ->will($this->returnValue($listEiken));

        $pupil = array(
            'id' => 1
        );
        $priceLevels = array(
            '1' => array(
                '1' => array(
                    'price' => 3200
                ),
                '2' => array(
                    'price' => 3000
                )
            ),
            '0' => array(
                '1' => array(
                    'price' => 2000
                ),
                '2' => array(
                    'price' => 1900
                )
            )
        );
        $listKyu = array(1, 2);
        $isSatellite = true; // split flow dantai and satellite.
        $invitationSetting = new InvitationSetting();
        $eikenSchedule = new EikenSchedule();
        $datetime = new \DateTime();
        $datetime->add(new \DateInterval('P10D'));
        $eikenSchedule->setCombiniDeadline($datetime);
        $invitationSetting->setEikenSchedule($eikenSchedule);
        $invitationSetting->setHallType(1);

        $result = $combiniMock->savePaymentCombiniTemp($pupil,$invitationSetting,$priceLevels,$listKyu,$isSatellite);

        $this->assertFalse($result,'apply eiken not exist - satellite site');
    }
}
