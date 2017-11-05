<?php
/*
 * @Author Huy Manh(manhnh5)
 */
namespace Eiken;

use Dantai\PrivateSession;
use stdClass;

class ApplyEikenDetailOrgFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase
{    
    public function testUpdateDatabaseIfOrganizationExistsRecordTableApplyEikenOrgDetailElseInsertNewRecord()
    {
        $data = $this->getDataDefine();
        $eikenService = $this->getApplicationServiceLocator()->get('Eiken\Service\ApplyEikenOrgServiceInterface');
        $eikenService->saveDetailOrg($data->eikenOrgId, $data->hallType['standard'], $data->price, $data->theParams);
        $eikenService->saveDetailOrg($data->eikenOrgId, $data->hallType['main'], $data->price, $data->theParams);
        $eikenOrgMainHall = $this->getDetailOrgID($data->eikenOrgId, $data->hallType['main']);
        $eikenOrgStandardHall = $this->getDetailOrgID($data->eikenOrgId, $data->hallType['standard']);
        if ($eikenOrgMainHall) {
            $this->assertEquals($eikenOrgMainHall->getApplyEikenOrgId(), $data->eikenOrgId);
        }
        if ($eikenOrgStandardHall) {
            $this->assertEquals($eikenOrgStandardHall->getApplyEikenOrgId(), $data->eikenOrgId);
        }
    }

    public function testPriceKyuInOrgDetail()
    {
        /* @var $eikenOrgMainHall \Application\Entity\ApplyEikenOrgDetails */
        /* @var $eikenOrgStandardHall \Application\Entity\ApplyEikenOrgDetails */
        $data = $this->getDataDefine();
        $eikenService = $this->getApplicationServiceLocator()->get('Eiken\Service\ApplyEikenOrgServiceInterface');
        $eikenService->saveDetailOrg($data->eikenOrgId, $data->hallType['standard'], $data->price, $data->theParams);
        $eikenService->saveDetailOrg($data->eikenOrgId, $data->hallType['main'], $data->price, $data->theParams);
        $eikenOrgMainHall = $this->getDetailOrgID($data->eikenOrgId, $data->hallType['main']);
        $eikenOrgStandardHall = $this->getDetailOrgID($data->eikenOrgId, $data->hallType['standard']);
        if ($eikenOrgMainHall) {
            $this->assertEquals($eikenOrgMainHall->getPriceLev1(), $data->price[$data->hallType['main']][1]['price']);
            $this->assertEquals($eikenOrgMainHall->getPricePreLev1(), $data->price[$data->hallType['main']][2]['price']);
            $this->assertEquals($eikenOrgMainHall->getPriceLev2(), $data->price[$data->hallType['main']][3]['price']);
            $this->assertEquals($eikenOrgMainHall->getPricePreLev2(), $data->price[$data->hallType['main']][4]['price']);
            $this->assertEquals($eikenOrgMainHall->getPriceLev3(), $data->price[$data->hallType['main']][5]['price']);
            $this->assertEquals($eikenOrgMainHall->getPriceLev4(), $data->price[$data->hallType['main']][6]['price']);
            $this->assertEquals($eikenOrgMainHall->getPriceLev5(), $data->price[$data->hallType['main']][7]['price']);
        }
        if ($eikenOrgStandardHall) {
            $this->assertNull($eikenOrgStandardHall->getPriceLev1());
            $this->assertNull($eikenOrgStandardHall->getPricePreLev1());
            $this->assertEquals($eikenOrgStandardHall->getPriceLev2(), $data->price[$data->hallType['standard']][3]['price']);
            $this->assertEquals($eikenOrgStandardHall->getPricePreLev2(), $data->price[$data->hallType['standard']][4]['price']);
            $this->assertEquals($eikenOrgStandardHall->getPriceLev3(), $data->price[$data->hallType['standard']][5]['price']);
            $this->assertEquals($eikenOrgStandardHall->getPriceLev4(), $data->price[$data->hallType['standard']][6]['price']);
            $this->assertEquals($eikenOrgStandardHall->getPriceLev5(), $data->price[$data->hallType['standard']][7]['price']);
        }
    }

    public function testCheckEmptyResultKynPrice()
    {
        /* @var $eikenOrgMainHall \Application\Entity\ApplyEikenOrgDetails */
        /* @var $eikenOrgStandardHall \Application\Entity\ApplyEikenOrgDetails */
        $data = $this->getDataDefine();
        $data->price = Array
        (
            1 => Array
            (
                5 => Array
                (
                    'price' => 3200,
                    'name'  => '3級'
                ),
                6 => Array
                (
                    'price' => 2100,
                    'name'  => '4級'
                ),
                7 => Array
                (
                    'price' => 2000,
                    'name'  => '5級'
                )
            ),
            0 => Array()
        );
        $eikenService = $this->getApplicationServiceLocator()->get('Eiken\Service\ApplyEikenOrgServiceInterface');
        $eikenService->saveDetailOrg($data->eikenOrgId, $data->hallType['standard'], $data->price, $data->theParams);
        $eikenService->saveDetailOrg($data->eikenOrgId, $data->hallType['main'], $data->price, $data->theParams);
        $eikenOrgMainHall = $this->getDetailOrgID($data->eikenOrgId, $data->hallType['main']);
        $eikenOrgStandardHall = $this->getDetailOrgID($data->eikenOrgId, $data->hallType['standard']);
        if ($eikenOrgMainHall) {
            $this->assertEquals($eikenOrgMainHall->getPriceLev1(), 0);
            $this->assertEquals($eikenOrgMainHall->getPricePreLev1(), 0);
            $this->assertEquals($eikenOrgMainHall->getPriceLev2(), 0);
            $this->assertEquals($eikenOrgMainHall->getPricePreLev2(), 0);
        }
        if ($eikenOrgStandardHall) {
            $this->assertEquals($eikenOrgStandardHall->getPriceLev2(), 0);
            $this->assertEquals($eikenOrgStandardHall->getPricePreLev2(), 0);
            $this->assertEquals($eikenOrgStandardHall->getPriceLev3(), 0);
            $this->assertEquals($eikenOrgStandardHall->getPriceLev4(), 0);
            $this->assertEquals($eikenOrgStandardHall->getPriceLev5(), 0);
        }
        $this->cleanup($data->organizationId, $data->eikenOrgId);
    }

    public function getDataDefine()
    {
        PrivateSession::setData('userIdentity', $this->getIdentityDataSession());
        $user = PrivateSession::getData('userIdentity');
        $hallType = array('main' => 1, 'standard' => 0);
        $theParams = Array
        (
            'isHallMain'             => true,
            'isDraft'                => 0,
            'MainHallExpectApplyNo7' => 0,
            'MainHallExpectApplyNo6' => 0,
            'MainHallExpectApplyNo5' => 0,
            'MainHallExpectApplyNo4' => 0,
            'MainHallExpectApplyNo3' => 0,
            'MainHallExpectApplyNo2' => 0,
            'MainHallExpectApplyNo1' => 0,
            'ExpectApplyNo7'         => 5,
            'ExpectApplyNo6'         => 4,
            'ExpectApplyNo5'         => 3,
            'ExpectApplyNo4'         => 2,
            'ExpectApplyNo3'         => 33,
            'date0'                  => 1,
            'date1'                  => 0,
            'date2'                  => 0,
            'date3'                  => 0,
            'date4'                  => 0,
            'date5'                  => 0,
            'totalcd'                => 12,
            'locationType'           => 0,
            'locationType1'          => '',
            'EikenOrgNo1'            => '',
            'EikenOrgNo2'            => '',
            'EikenOrgNo123'          => '',
            'FirtNameKanji'          => '',
            'LastNameKanji'          => '',
            'confirmMailAddress'     => '',
            'cityId'                 => '',
            'districtId'             => '',
            'MailAddress'            => '',
            'PhoneNumber'            => '',
            'hasRegisterd7'          => 0,
            'hasRegisterd6'          => 0,
            'hasRegisterd5'          => 0,
            'hasRegisterd4'          => 0,
            'hasRegisterd3'          => 0,
            'hasRegisterd2'          => 0,
            'hasRegisterd1'          => 0
        );
        $price = Array
        (
            1 => Array
            (
                1 => Array
                (
                    'price' => 8400,
                    'name'  => '1級'
                ),
                2 => Array
                (
                    'price' => 6900,
                    'name'  => '準1級'
                ),
                3 => Array
                (
                    'price' => 5000,
                    'name'  => '2級'
                ),
                4 => Array
                (
                    'price' => 4500,
                    'name'  => '準2級'
                ),
                5 => Array
                (
                    'price' => 3200,
                    'name'  => '3級'
                ),
                6 => Array
                (
                    'price' => 2100,
                    'name'  => '4級'
                ),
                7 => Array
                (
                    'price' => 2000,
                    'name'  => '5級'
                )
            ),
            0 => Array
            (
                1 => Array
                (
                    'price' => 8400,
                    'name'  => '1級'
                ),
                2 => Array
                (
                    'price' => 6900,
                    'name'  => '準1級'
                ),
                3 => Array
                (
                    'price' => 4600,
                    'name'  => '2級'
                ),
                4 => Array
                (
                    'price' => 4100,
                    'name'  => '準2級'
                ),
                5 => Array
                (
                    'price' => 2800,
                    'name'  => '3級'
                ),
                6 => Array
                (
                    'price' => 1600,
                    'name'  => '4級'
                ),
                7 => Array
                (
                    'price' => 1500,
                    'name'  => '5級'
                )
            )
        );
        $applyEikenOrg = $this->getApplyEikenOrg($user['organizationId'], $user['organizationNo']);
        $eikenOrgId = $applyEikenOrg ? $applyEikenOrg->getId() : null;
        if (!$eikenOrgId) {
            $applyEikenOrg = $this->getApplyEikenOrg($user['organizationId'], $user['organizationNo']);
            $eikenOrgId = $applyEikenOrg->getId();
        }

        return (object)array(
            'organizationId' => $user['organizationId'],
            'eikenOrgId'     => $eikenOrgId,
            'theParams'      => $theParams,
            'price'          => $price,
            'hallType'       => $hallType
        );
    }

    private function getDetailOrgID($eikenOrgId, $hallType)
    {
        $detailEikenOrg = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenOrgDetails')->findOneBy(array(
            'hallType'        => $hallType,
            'applyEikenOrgId' => $eikenOrgId
        ));

        return $detailEikenOrg;
    }

    private function getApplyEikenOrg($orgId, $orgNo)
    {
        $applyEikenOrg = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array('organizationId' => $orgId));
        if (!$applyEikenOrg) {
            $sql = $this->getOrg($orgId, $orgNo) . "INSERT INTO `ApplyEikenOrg` (`OrganizationId`, `EikenScheduleId`, `Total`, `TypeExamDate`, `ActualExamDate`, `CD`, `FirtNameKanji`, `LastNameKanji`, `MailAddress`, `PhoneNumber`, `LocationType`, `LocationType1`, `EikenOrgNo1`, `EikenOrgNo2`, `EikenOrgNo123`, `ApplyStatus`, `Status`, `NoApiCalls`, `HasMainHall`, `UpdateAt`, `UpdateBy`, `InsertAt`, `InsertBy`, `IsDelete`, `CityId`, `DistrictId`, `ConfirmEmail`, `isSentStandardHall`, `isSentMainHall`, `StatusMapping`, `StatusImporting`, `TotalImport`, `ManagerName`, `RegistrationDate`)
                   VALUES (" . $orgId . ", 2, 3, 1, 0, '22', '46346', '346346', 'thoalt@fsoft.com.vn', '57547', 0, 0, '', '', '', '変更通知', 'SUBMITTED', 4, 1, '2015-11-16 19:57:03', '10566000.USER001', '2015-10-09 16:55:45', '99965200.Thoalt01', 0, NULL, NULL, '', NULL, NULL, 0, 1, 6, '', '2015-10-13 00:00:00');";
            $connection = $this->getEntityManager()->getConnection();
            $connection->executeUpdate($sql);
            $this->tearDown();
        }

        return $applyEikenOrg;
    }

    private function getOrg($orgId, $orgNo)
    {
        $organization = $this->getEntityManager()->getRepository('Application\Entity\Organization')->find($orgId);
        if (empty($organization)) {
            $sql = "INSERT INTO `Organization` (`id`, `CityId`, `OrganizationNo`, `OrganizationCode`, `OrgNameKanji`, `OrgNameKana`, `Department`, `ReceptionTime`, `SchoolDivision`, `FlagRegister`, `Group`, `ExamLocation`, `ExamLand`, `OfficerName`, `CityCode`, `Address1`, `StateCode`, `TownCode`, `Address2`, `Email`, `TelNo`, `Fax`, `Passcode`, `UpdateAt`, `UpdateBy`, `InsertAt`, `InsertBy`, `Status`, `IsDelete`)
                   VALUES(" . $orgId . ", NULL , " . $orgNo . ", '00', '検査01', 'テスト01', 'デパートメント', NULL, 12, 12, 'Org 1', 'Org 1', 'Org 1', 'Org 1', '1231', 'Org 1', '12', 'Org 1', 'Org 1', 'dunghp@fsoft.com.vn', '3123456780', 'Org 1', 'Org 1', '2015-07-30 04:13:15', 'DungHP', '2015-07-30 04:13:15', 'DungHP', 'Enable', 0);";

            return $sql;
        }

        return;
    }

    private function cleanup($orgId, $eikenOrgId)
    {
        try {
            $sql = "DELETE FROM ApplyEikenOrgDetails WHERE ApplyEikenOrgId = " . $eikenOrgId . ";
                    DELETE FROM ApplyEikenOrg WHERE OrganizationId = " . $orgId . ";
                    DELETE FROM Organization WHERE Id = " . $orgId;
            $connection = $this->getEntityManager()->getConnection();
            $connection->executeUpdate($sql);
            $this->tearDown();
        }
        catch (Exception $e) {
            return;
        }
    }
}
