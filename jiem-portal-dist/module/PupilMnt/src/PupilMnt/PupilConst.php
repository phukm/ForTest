<?php

namespace PupilMnt;

class PupilConst
{
    const DUPLICATE_IN_FILE_IMPORT = 1;
    const DUPLICATE_IN_DATABASE = 2;
    const DUPLICATE_IN_FILE_IMPORT_AND_DATABASE = 3;

    const EXPORT_TYPE_XLSX = 0;
    const EXPORT_TYPE_XLS = 1;
    const EXPORT_TYPE_CSV = 2;
    
    const EXPORT_TEMPLATE_TYPE_XLSX = 0;
    const EXPORT_TEMPLATE_TYPE_XLS = 1;
    const EXPORT_TEMPLATE_TYPE_CSV = 2;
    const EXPORT_TEMPLATE_TYPE_NORMAL = 1;
    const EXPORT_TEMPLATE_TYPE_SEPERATE = 2;
    
    const IMPORT_DUPLICATE_PUPIL_NAME = 0;
    const IMPORT_SUCCESS = 1;
    const IMPORT_FAILED = -1;

    // session eiken test result and iba test result check test result pupil.
    const TEST_RESULT_PUPIL_LIST = 'TEST_RESULT_PUPIL_LIST';
    const SESSION_KEY_SEARCH_INDEX = 'FilterCriteria/PupilMnt/Controller/Pupil/index';
    
    const DELIMITER_VALUE = '||-||';
    
    const PASS_ALL_FILE_VALIDATION = 'Success';
    const COOKIES_TOKEN = 'cookiesToken';
}