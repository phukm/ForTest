<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ConsoleInvitation;

class ConsoleInvitationConst
{
    const IMPORT_SUCCESS = 1;
    const IMPORT_FAILED = 0;
    const IMPORT_EMPTY_DATA = 2;

    const STATUS_WAITTING_MAP = 0; // (un-confirmed):
    const STATUS_MAPPED = 1;  // (confirmed)
    const STATUS_MAPPING = 2;  // (confirmed)

    const STATUS_AUTO_IMPORT_IBA_RUNNING = 1;
    const STATUS_AUTO_IMPORT_IBA_COMPLETE = 2;
    const STATUS_AUTO_IMPORT_IBA_CONFIRMED = 3;
    const STATUS_AUTO_IMPORT_IBA_FAILURE = 7;

    const NOT_IMPORT_STATUS = 0;
    const IMPORTED_STATUS = 1;
    const IMPORTING_STATUS = 2;

    const EXPORT_CONFIG_DATE_FILENAME = 'DS_Eiken_Date_Config_File_Export_%s.csv';
}