SET NAMES UTF8;
-- Update Action and Set Action To Role
DROP PROCEDURE IF EXISTS appendRoleAction;
delimiter //
CREATE PROCEDURE appendRoleAction(IN linkAction VARCHAR(256), IN roleList VARCHAR(256))
BEGIN
    SET @titleAction = REPLACE(linkAction, '/', '.');
        
    -- Insert Action record
    IF (EXISTS (SELECT id FROM `Action` WHERE `Title` = @titleAction COLLATE utf8_unicode_ci) ) THEN
        SET @lastInsertActionID = (SELECT id FROM `Action` WHERE `Title` = @titleAction COLLATE utf8_unicode_ci); 
    ELSE
        INSERT INTO `Action` (`Title`, `Link`, `Description`, `Status`, `IsDelete`, `InsertBy`, `UpdateBy`) VALUES 
        (@titleAction, linkAction, 'Description', 'Enable', '0', 'SystemAdmin', 'SystemAdmin');
        SET @lastInsertActionID = LAST_INSERT_ID(); 
    END IF;

    -- Insert record for each specified role
    SET @roleId = 1;
    SET @roleIndex = FIND_IN_SET(@roleId, roleList COLLATE utf8_unicode_ci);
    IF (@roleIndex != 0 AND NOT EXISTS (SELECT `id` FROM `RoleAction` WHERE `RoleId` = @roleId AND `ActionId` = @lastInsertActionID LIMIT 1) ) THEN
        INSERT INTO `RoleAction`(`RoleId`, `ActionId`, `Status`, `IsDelete`, `InsertBy`, `UpdateBy`) VALUES 
        (@roleId, @lastInsertActionID, 'Enable', '0', 'SystemAdmin', 'SystemAdmin');
    END IF;
    
    SET @roleId = 2;
    SET @roleIndex = FIND_IN_SET(@roleId, roleList COLLATE utf8_unicode_ci);
    IF (@roleIndex != 0 AND NOT EXISTS (SELECT `id` FROM `RoleAction` WHERE `RoleId` = @roleId AND `ActionId` = @lastInsertActionID LIMIT 1) ) THEN
        INSERT INTO `RoleAction`(`RoleId`, `ActionId`, `Status`, `IsDelete`, `InsertBy`, `UpdateBy`) VALUES 
        (@roleId, @lastInsertActionID, 'Enable', '0', 'SystemAdmin', 'SystemAdmin');
    END IF;   

    SET @roleId = 3;
    SET @roleIndex = FIND_IN_SET(@roleId, roleList COLLATE utf8_unicode_ci);
    IF (@roleIndex != 0 AND NOT EXISTS (SELECT `id` FROM `RoleAction` WHERE `RoleId` = @roleId AND `ActionId` = @lastInsertActionID LIMIT 1) ) THEN
        INSERT INTO `RoleAction`(`RoleId`, `ActionId`, `Status`, `IsDelete`, `InsertBy`, `UpdateBy`) VALUES 
        (@roleId, @lastInsertActionID, 'Enable', '0', 'SystemAdmin', 'SystemAdmin');
    END IF;

    SET @roleId = 4;
    SET @roleIndex = FIND_IN_SET(@roleId, roleList COLLATE utf8_unicode_ci);
    IF (@roleIndex != 0 AND NOT EXISTS (SELECT `id` FROM `RoleAction` WHERE `RoleId` = @roleId AND `ActionId` = @lastInsertActionID LIMIT 1) ) THEN
        INSERT INTO `RoleAction`(`RoleId`, `ActionId`, `Status`, `IsDelete`, `InsertBy`, `UpdateBy`) VALUES 
        (@roleId, @lastInsertActionID, 'Enable', '0', 'SystemAdmin', 'SystemAdmin');
    END IF;

    SET @roleId = 5;
    SET @roleIndex = FIND_IN_SET(@roleId, roleList COLLATE utf8_unicode_ci);
    IF (@roleIndex != 0 AND NOT EXISTS (SELECT `id` FROM `RoleAction` WHERE `RoleId` = @roleId AND `ActionId` = @lastInsertActionID LIMIT 1) ) THEN
        INSERT INTO `RoleAction`(`RoleId`, `ActionId`, `Status`, `IsDelete`, `InsertBy`, `UpdateBy`) VALUES 
        (@roleId, @lastInsertActionID, 'Enable', '0', 'SystemAdmin', 'SystemAdmin');
    END IF;
    SET @roleId = 6;
    SET @roleIndex = FIND_IN_SET(@roleId, roleList COLLATE utf8_unicode_ci);
    IF (@roleIndex != 0 AND NOT EXISTS (SELECT `id` FROM `RoleAction` WHERE `RoleId` = @roleId AND `ActionId` = @lastInsertActionID LIMIT 1) ) THEN
        INSERT INTO `RoleAction`(`RoleId`, `ActionId`, `Status`, `IsDelete`, `InsertBy`, `UpdateBy`) VALUES 
        (@roleId, @lastInsertActionID, 'Enable', '0', 'SystemAdmin', 'SystemAdmin');
    END IF;

END //
delimiter ;

CALL appendRoleAction('invitationmnt/setting/clear', '1,2,3,4,5,6');
CALL appendRoleAction('invitationmnt/generate/is-first-generated', '1,2,3,4,5'); 
CALL appendRoleAction('orgmnt/class/is-first-character', '1,2,3,4,5');
CALL appendRoleAction('orgmnt/class/is-first-character-update', '1,2,3,4,5');
CALL appendRoleAction('invitationmnt/setting/is-not-show-popup', '1,2,3,4,5,6');
CALL appendRoleAction('orgmnt/org/policy-grade-class', '1,2,3,4,5,6');
CALL appendRoleAction('pupilmnt/pupil/cannot-delete', '1,2,3,4,5');
CALL appendRoleAction('homepage/homepage/downloadeikenid', '1,2,3,4,5');


