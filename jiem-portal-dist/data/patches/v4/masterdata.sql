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


DROP PROCEDURE IF EXISTS addRoleAction;
delimiter //
CREATE PROCEDURE addRoleAction()
BEGIN   
    -- Insert role record
    IF (EXISTS (SELECT id FROM `Role` WHERE `id` = 1) ) THEN
        UPDATE `Role` SET `RoleName` = 'システム管理者' WHERE `id` = 1; 
    ELSE
        INSERT INTO `Role` (`id`, `RoleName`, `Status`, `IsDelete`) VALUES 
        (1, 'システム管理者', '0', '0');
    END IF;

    IF (EXISTS (SELECT id FROM `Role` WHERE `id` = 2) ) THEN
        UPDATE `Role` SET `RoleName` = 'サービス運営者' WHERE `id` = 2; 
    ELSE
        INSERT INTO `Role` (`id`, `RoleName`, `Status`, `IsDelete`) VALUES 
        (2, 'サービス運営者', '0', '0');
    END IF;

    IF (EXISTS (SELECT id FROM `Role` WHERE `id` = 3) ) THEN
        UPDATE `Role` SET `RoleName` = '団体統括者' WHERE `id` = 3; 
    ELSE
        INSERT INTO `Role` (`id`, `RoleName`, `Status`, `IsDelete`) VALUES 
        (3, '団体統括者', '0', '0');
    END IF;

    IF (EXISTS (SELECT id FROM `Role` WHERE `id` = 4) ) THEN
        UPDATE `Role` SET `RoleName` = '団体責任者（管理者）' WHERE `id` = 4; 
    ELSE
        INSERT INTO `Role` (`id`, `RoleName`, `Status`, `IsDelete`) VALUES 
        (4, '団体責任者（管理者）', '0', '0');
    END IF;

    IF (EXISTS (SELECT id FROM `Role` WHERE `id` = 5) ) THEN
        UPDATE `Role` SET `RoleName` = '団体責任者（利用者）' WHERE `id` = 5; 
    ELSE
        INSERT INTO `Role` (`id`, `RoleName`, `Status`, `IsDelete`) VALUES 
        (5, '団体責任者（利用者）', '0', '0');
    END IF;

    IF (EXISTS (SELECT id FROM `Role` WHERE `id` = 6) ) THEN
        UPDATE `Role` SET `RoleName` = '閲覧のみユーザ' WHERE `id` = 6; 
    ELSE
        INSERT INTO `Role` (`id`, `RoleName`, `Status`, `IsDelete`) VALUES 
        (6, '閲覧のみユーザ', '0', '0');
    END IF;

END //
delimiter ;
-- Append role into Database
CALL addRoleAction();

-- Append role action into Database for role 6 (Viewer)

-- Manage Grade
CALL appendRoleAction('orgmnt/org/index', '1,2,3,6');
CALL appendRoleAction('orgmnt/org/show', '1,2,3,4,5,6');
CALL appendRoleAction('orgmnt/org/getAPI', '1,2,3,4,5,6');
CALL appendRoleAction('orgmnt/org/transform', '1,2,3,6');
-- Manage Class in grade
CALL appendRoleAction('orgmnt/orgschoolyear/index', '1,2,3,4,5,6');
CALL appendRoleAction('orgmnt/orgschoolyear/show', '1,2,3,4,5,6');
-- Manage User
CALL appendRoleAction('orgmnt/user/index', '1,2,3,4,5,6');
CALL appendRoleAction('orgmnt/user/show', '1,2,3,4,5,6');
-- Manage Class in grade
CALL appendRoleAction('orgmnt/class/index', '1,2,3,4,5,6');
CALL appendRoleAction('orgmnt/class/show', '1,2,3,4,5,6');
-- Manage Pupils
CALL appendRoleAction('pupilmnt/pupil/index', '1,2,3,4,5,6');
CALL appendRoleAction('pupilmnt/pupil/show', '1,2,3,4,5,6');
CALL appendRoleAction('pupilmnt/pupil/ajaxGetListClassName', '1,2,3,4,5,6');
-- Manage Standard Level
CALL appendRoleAction('invitationmnt/standard/index', '1,2,3,4,5,6');
CALL appendRoleAction('invitationmnt/standard/show', '1,2,3,4,5,6');
CALL appendRoleAction('invitationmnt/standard/getschoolyear', '1,2,3,4,5,6');
-- Set recommended level
CALL appendRoleAction('invitationmnt/recommended/index', '1,2,3,4,5,6');
CALL appendRoleAction('invitationmnt/recommended/getSchoolYear', '1,2,3,4,5,6');
CALL appendRoleAction('invitationmnt/recommended/getclass', '1,2,3,4,5,6');
CALL appendRoleAction('invitationmnt/recommended/getKai', '1,2,3,4,5,6');
-- Create Invitation Setting
CALL appendRoleAction('invitationmnt/setting/index', '1,2,3,4,5,6');
CALL appendRoleAction('invitationmnt/setting/show', '1,2,3,4,5,6');
-- View Generate Invitation Letter
CALL appendRoleAction('invitationmnt/generate/index', '1,2,3,4,5,6');
CALL appendRoleAction('invitationmnt/generate/show', '1,2,3,4,5,6');
CALL appendRoleAction('invitationmnt/generate/getClass', '1,2,3,4,5,6');
CALL appendRoleAction('invitationmnt/generate/getKai', '1,2,3,4,5,6');
CALL appendRoleAction('invitationmnt/generate/getSchoolYear', '1,2,3,4,5,6');
-- Appy Eiken Exam
CALL appendRoleAction('eiken/eikenorg/applyeikendetails', '1,2,3,4,5,6');
CALL appendRoleAction('eiken/payment/paymentstatus', '1,2,3,4,5,6');
CALL appendRoleAction('eiken/payment/paymentdetails', '1,2,3,4,5,6');
CALL appendRoleAction('eiken/eikenorg/index', '1,2,3,4,5,6');
CALL appendRoleAction('eiken/payment/getclass', '1,2,3,4,5,6');
CALL appendRoleAction('eiken/payment/getkai', '1,2,3,4,5,6');
-- Study Gear
CALL appendRoleAction('goalsetting/studygear/index', '1,2,3,4,5,6');
CALL appendRoleAction('goalsetting/studygear/show', '1,2,3,4,5,6');
CALL appendRoleAction('goalsetting/studygear/ajaxgetlistclass', '1,2,3,4,5,6');
-- Create target
CALL appendRoleAction('goalsetting/graduationgoalsetting/index', '1,2,3,4,5,6');
CALL appendRoleAction('goalsetting/graduationgoalsetting/graduationgoalsearch', '1,2,3,4,5,6');
CALL appendRoleAction('goalsetting/graduationgoalsetting/listschoolyear', '1,2,3,4,5,6');
-- Application History and Results
CALL appendRoleAction('goalsetting/eikenscheduleinquiry/index', '1,2,3,4,5,6');
CALL appendRoleAction('goalsetting/eikenscheduleinquiry/get-eiken-schedules', '1,2,3,4,5,6');
CALL appendRoleAction('goalsetting/eikenscheduleinquiry/get-eiken-schedule-holidays', '1,2,3,4,5,6');
-- View Study Gear results
CALL appendRoleAction('goalsetting/studygear/studygeardetail', '1,2,3,4,5,6');
CALL appendRoleAction('goalsetting/studygear/listhistorystudy', '1,2,3,4,5,6');
-- Analyze Eiken Results
CALL appendRoleAction('report/report/csescoretotal', '1,2,3,4,5,6');
CALL appendRoleAction('report/report/loadClass', '1,2,3,4,5,6');
-- R2
-- IBA
CALL appendRoleAction('iba/iba/show', '1,2,3,4,5,6');
-- R3
-- List of Exam_Download Excel
CALL appendRoleAction('history/iba/pupil-achievement', '1,2,3,4,5,6');
CALL appendRoleAction('history/iba/get-data-by-exam-date', '1,2,3,4,5,6');
CALL appendRoleAction('history/iba/detail', '1,2,3,4,5,6');
CALL appendRoleAction('history/iba/iba-history-pupil', '1,2,3,4,5,6');
CALL appendRoleAction('history/iba/get-data-iba', '1,2,3,4,5,6');
CALL appendRoleAction('history/iba/show-popup-comfirm-auto-mapping', '1,2,3,4,5,6');
CALL appendRoleAction('history/eiken/exam-result', '1,2,3,4,5,6');
CALL appendRoleAction('history/eiken/exam-history-list', '1,2,3,4,5,6');
CALL appendRoleAction('history/eiken/eiken-history-pupil', '1,2,3,4,5,6');
CALL appendRoleAction('history/eiken/get-data-eiken', '1,2,3,4,5,6');
CALL appendRoleAction('history/eiken/personal-achievement', '1,2,3,4,5,6');
CALL appendRoleAction('history/eiken/show-popup-comfirm-auto-mapping', '1,2,3,4,5,6');
CALL appendRoleAction('history/eiken/get-data-by-year-and-kai', '1,2,3,4,5,6');
CALL appendRoleAction('history/eiken/pupil-achievement', '1,2,3,4,5,6');

-- View undetermined Organization list
CALL appendRoleAction('orgmnt/org/undetermined', '1,2,6');
-- View Writing Test Exemption List
CALL appendRoleAction('eiken/exemption/list', '1,2,3,4,5,6');
CALL appendRoleAction('eiken/exemption/show-data-paging', '1,2,3,4,5,6');
-- Activity Log
CALL appendRoleAction('logs/activity/index', '1,2,3,4,5,6');

-- R5
CALL appendRoleAction('eiken/exemption/export', '1,2,3,4,5');
-- import master data
CALL appendRoleAction('orgmnt/importmasterdata/index', '1,2,3');