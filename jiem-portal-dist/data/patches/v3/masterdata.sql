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
CALL appendRoleAction('pupilmnt/importpupil/index', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/importpupil/save', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/importpupil/save-pupil', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/pupil/exporttemplate', '1,2,3,4,5');
CALL appendRoleAction('orgmnt/org/undetermined', '1,2');
CALL appendRoleAction('orgmnt/org/export-undermined-org', '1,2');
CALL appendRoleAction('pupilmnt/importpupil/duplicate', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/importpupil/detail-duplicate', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/pupil/exporttemplate', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/importpupil/show-data-paging', '1,2,3,4,5');
CALL appendRoleAction('eiken/exemption/list', '1,2,3,4,5');
CALL appendRoleAction('eiken/exemption/show-data-paging', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/show-popup-comfirm-auto-mapping', '1,2,3,4,5');
CALL appendRoleAction('history/iba/show-popup-comfirm-auto-mapping', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/eiken-mapping-result', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/eiken-confirm-result', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/confirm-status', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/search', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/clear', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/ajax-get-list-class', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/get-students', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/call-save-next-pupil', '1,2,3,4,5');
CALL appendRoleAction('history/iba/iba-mapping-result', '1,2,3,4,5');
CALL appendRoleAction('history/iba/iba-confirm-result', '1,2,3,4,5');
CALL appendRoleAction('history/iba/confirm-status', '1,2,3,4,5');
CALL appendRoleAction('history/iba/search', '1,2,3,4,5');
CALL appendRoleAction('history/iba/clear', '1,2,3,4,5');
CALL appendRoleAction('history/iba/ajax-get-list-class', '1,2,3,4,5');
CALL appendRoleAction('history/iba/get-students', '1,2,3,4,5');
CALL appendRoleAction('history/iba/call-save-next-pupil', '1,2,3,4,5');
CALL appendRoleAction('history/iba/exportiba', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/export-eiken', '1,2,3,4,5');
CALL appendRoleAction('eiken/eikenorg/invalid', '1,2,3,4,5');
CALL appendRoleAction('history/eiken/export-eiken-history-pupil', '1,2,3,4,5');
CALL appendRoleAction('history/iba/export-iba-history-pupil', '1,2,3,4,5');
CALL appendRoleAction('orgmnt/org/paymentRefundStatus', '1,2,3');
CALL appendRoleAction('pupilmnt/importpupil/mapping-school-year', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/pupil/ajaxGetListClassName', '1,2,3,4,5');
CALL appendRoleAction('orgmnt/class/checkduplicateupdate', '1,2,3,4,5');
CALL appendRoleAction('eiken/eikenorg/funding', '1,2,3,4,5');
CALL appendRoleAction('eiken/eikenorg/payment', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/importpupil/mapping-school-year', '1,2,3,4,5');
CALL appendRoleAction('orgmnt/org/ajaxSetSemiMainVenue', '1,2,3');
CALL appendRoleAction('eiken/eikenorg/check-payment-type', '1,2,3,4,5');
CALL appendRoleAction('invitationmnt/setting/load-expired-payment-date', '1,2,3,4,5');
CALL appendRoleAction('logs/applyeiken/index', '1,2,3,4,5');
CALL appendRoleAction('eiken/eikenorg/save-status', '1,2,3,4,5');
CALL appendRoleAction('history/iba/empty-name-kana', '1,2,3,4,5');
CALL appendRoleAction('logs/activity/index', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/importpupil/seperate-name', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/importpupil/export-template-seperate', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/pupil/save', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/pupil/checkDuplicatePupil', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/pupil/checkNameKanji', '1,2,3,4,5');
CALL appendRoleAction('pupilmnt/pupil/check-pupil-had-apply-eiken-to-delete', '1,2,3,4,5');
