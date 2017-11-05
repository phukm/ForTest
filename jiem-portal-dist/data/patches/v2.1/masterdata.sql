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

END //
delimiter ;

CALL appendRoleAction('application/index/zipcode', '1,2,3,4,5');
CALL appendRoleAction('iba/iba/add', '1,2,3,4,5');
CALL appendRoleAction('iba/iba/is-register-test-date', '1,2,3,4,5');
CALL appendRoleAction('iba/iba/show', '1,2,3,4,5');
CALL appendRoleAction('iba/iba/policy', '1,2,3,4,5');
CALL appendRoleAction('iba/iba/save-draft', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/pupil/checkDuplicatePupilNumber', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/getDataByYearAndKai', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/exam-result', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/check-kekka-value', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/confirm-exam-result', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/mapping-result', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/pupil-achievement', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/personal-achievement', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/get-data-by-year-and-kai', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/get-kai', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/exam-history-list', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/eiken-history-pupil', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/save-eiken-exam-result', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/save-eiken-exam-result-only', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/mapping-eiken-exam-result', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/save-maping-eiken', '1,2,3,4,5');
CALL appendRoleAction('history/iba/save-maping-iba', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/find-eiken', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/mapping-error', '1,2,3,4,5');
CALL appendRoleAction('history/iba/mapping-error', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/mapping-success', '1,2,3,4,5');
CALL appendRoleAction('history/iba/mapping-success', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/mapping-data', '1,2,3,4,5');
CALL appendRoleAction('history/iba/exam-result', '1,2,3,4,5');
CALL appendRoleAction('history/iba/confirm-result', '1,2,3,4,5');
CALL appendRoleAction('history/iba/pupil-achievement', '1,2,3,4,5');
CALL appendRoleAction('history/iba/ajax-get-classes', '1,2,3,4,5');
CALL appendRoleAction('history/iba/get-data-by-exam-date', '1,2,3,4,5');
CALL appendRoleAction('history/iba/confirm-exam-result', '1,2,3,4,5');
CALL appendRoleAction('history/iba/mapping-result', '1,2,3,4,5');
CALL appendRoleAction('history/iba/mapping-data', '1,2,3,4,5');
CALL appendRoleAction('history/iba/detail', '1,2,3,4,5');
CALL appendRoleAction('history/iba/iba-history-pupil', '1,2,3,4,5');
CALL appendRoleAction('history/iba/check-kekka-value', '1,2,3,4,5');
CALL appendRoleAction('history/iba/save-exam-result', '1,2,3,4,5');
CALL appendRoleAction('history/iba/save-exam-result-only', '1,2,3,4,5');
CALL appendRoleAction('history/iba/', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/get-data-eiken', '1,2,3,4,5');
CALL appendRoleAction('history/iba/get-data-iba', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/ajax-get-list-class', '1,2,3,4,5');
CALL appendRoleAction('goalsetting/studygear/listhistorystudy', '1,2,3,4,5');
CALL appendRoleAction('goalsetting/studygear/studygeardetail/', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/clear-session', '1,2,3,4,5');
CALL appendRoleAction('history/iba/clear-session', '1,2,3,4,5');
CALL appendRoleAction('history/iba/exportiba', '1,2,3,4,5');
CALL appendRoleAction('goalsetting/eikenscheduleinquiry/index', '1,2,3,4,5');
CALL appendRoleAction('goalsetting/eikenscheduleinquiry/get-eiken-schedules', '1,2,3,4,5');
CALL appendRoleAction('goalsetting/eikenscheduleinquiry/get-eiken-schedule-holidays', '1,2,3,4,5');


CALL appendRoleAction('report/report/actualgoallevel', '1,2,3,4,5');
CALL appendRoleAction('report/report/actualgoalyear', '1,2,3,4,5');
CALL appendRoleAction('report/report/csescoretotal', '1,2,3,4,5');
CALL appendRoleAction('report/report/loadClass', '1,2,3,4,5');

CALL appendRoleAction('goalsetting/graduationgoalsetting/index', '1,2,3,4,5');
CALL appendRoleAction('goalsetting/graduationgoalsetting/graduationgoalsearch', '1,2,3,4,5');
CALL appendRoleAction('goalsetting/graduationgoalsetting/updategraduationgoal', '1,2,3,4,5');
CALL appendRoleAction('goalsetting/graduationgoalsetting/listschoolyear', '1,2,3,4,5');
CALL appendRoleAction('orgmnt/class/check-duplicate-update', '1,2,3,4,5');
CALL appendRoleAction('logs/activity/index', '1,2,3,4,5');
CALL appendRoleAction('iba/iba/onlyPolicy', '1,2,3,4,5');

INSERT IGNORE INTO `SchoolYear` (`id`, `Ordinal`, `Name`, `UpdateAt`, `UpdateBy`, `InsertAt`, `InsertBy`, `Status`, `IsDelete`) VALUES
(17, 0, '短大1年生', '2015-07-05 10:59:02', 'SystemAdmin', '2015-07-05 10:59:02', 'SystemAdmin', '0', 0),
(18, 0, '短大2年生', '2015-07-05 10:59:02', 'SystemAdmin', '2015-07-05 10:59:02', 'SystemAdmin', '0', 0),
(19, 0, '高専1年生', '2015-07-05 10:59:02', 'SystemAdmin', '2015-07-05 10:59:02', 'SystemAdmin', '0', 0),
(20, 0, '高専2年生', '2015-07-05 10:59:02', 'SystemAdmin', '2015-07-05 10:59:02', 'SystemAdmin', '0', 0),
(21, 0, '高専3年生', '2015-07-05 10:59:02', 'SystemAdmin', '2015-07-05 10:59:02', 'SystemAdmin', '0', 0),
(22, 0, '高専4年生', '2015-07-05 10:59:02', 'SystemAdmin', '2015-07-05 10:59:02', 'SystemAdmin', '0', 0),
(23, 0, '高専5年生', '2015-07-05 10:59:02', 'SystemAdmin', '2015-07-05 10:59:02', 'SystemAdmin', '0', 0);

UPDATE Role Set RoleName = 'サービス運営者' where id = 2;


