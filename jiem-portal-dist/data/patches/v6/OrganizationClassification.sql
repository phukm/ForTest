SET NAMES UTF8;
DROP PROCEDURE IF EXISTS organizationClassification;
delimiter //
CREATE PROCEDURE organizationClassification(IN orgCode VARCHAR(256), IN orgName VARCHAR(256))
BEGIN   
    IF (EXISTS (SELECT Code FROM `OrganizationClassification` WHERE `Code` = orgCode  COLLATE utf8_unicode_ci) ) THEN
        UPDATE `OrganizationClassification` SET `Name` = orgName WHERE `Code` =  orgCode;
    ELSE
        INSERT INTO `OrganizationClassification` (`Code`, `Name`, `UpdateBy`, `InsertBy`) VALUES 
        (orgCode, orgName, 'SystemAdmin', 'SystemAdmin');
    END IF;


END //
delimiter ;

CALL organizationClassification('58', '県教委');
CALL organizationClassification('59', '市教委'); 