<?php

namespace History;

class HistoryConst
{
    const DELIMITER_VALUE = '||-||';
    const STATUS_WAITTING_MAP = 0; // (un-confirmed):
    const STATUS_MAPPED = 1;  // (confirmed)
    const STATUS_MAPPING = 2;  // (confirmed)
    const BATCH_UPDATE_EIKEN_TEST_RESULT = 300;
    const BATCH_UPDATE_IBA_TEST_RESULT = 300;

    const NOT_IMPORT_STATUS = 0;
    const IMPORTED_STATUS = 1;
    const IMPORTING_STATUS = 2;
    
    const SAVE_TO_DATABASE_FAIL = 0;
    const SAVE_TO_DATABASE_SUCCESS = 1;
    
    const UNCONFIRM_STATUS = 0;
    const CONFIRMED_STATUS = 1;
    
    const FORMAT_DATE = 'Y/m/d';
    const FORMAT_DATE_2 = 'Y/m/d';
    const sessionSearchEiken = 'mappingEikenExamResult';
    const sessionSearchIBA = 'mappingIBAExamResult';
   
    const CANNOT_FIND_DATA = 0;
    const SAVE_DATABASE_SUCCESS = 1;
    const SAVE_DATABASE_FALSE = 0;
    const EXISTING_SESSION = 2;
    
    const STATUS_AUTO_IMPORT_NOT_RUN = 0;
    const STATUS_AUTO_IMPORT_EIKEN_ROUND1_RUNNING = 1;
    const STATUS_AUTO_IMPORT_EIKEN_ROUND2_RUNNING = 2;
    const STATUS_AUTO_IMPORT_EIKEN_ROUND1_COMPLETE = 3;
    const STATUS_AUTO_IMPORT_EIKEN_ROUND2_COMPLETE = 4;
    const STATUS_AUTO_IMPORT_EIKEN_ROUND1_CONFIRMED = 5;
    const STATUS_AUTO_IMPORT_EIKEN_ROUND2_CONFIRMED = 6;
    const STATUS_AUTO_IMPORT_EIKEN_FAILURE = 7;
    
    const STATUS_AUTO_IMPORT_IBA_RUNNING = 1;
    const STATUS_AUTO_IMPORT_IBA_COMPLETE = 2;
    const STATUS_AUTO_IMPORT_IBA_CONFIRMED = 3;
    const STATUS_AUTO_IMPORT_IBA_FAILURE = 7;
    
    const IMPORT_SUCCESS = 1;
    const IMPORT_FAILED = 0;
    const IMPORT_EMPTY_DATA = 2;
    
    const ROUND_ONE_CONFIRMED = 5;
    const ROUND_TWO_CONFIRMED = 6;
    
    const IBA_CONFIRMED = 3;
    
    const PREFIX_KANA = 'kn';
    const PREFIX_KANJI = 'kj';
    
    const GROUP_BY_CLASS_NAME = 'className';
    const GROUP_BY_SCHOOL_YEAR_NAME = 'schoolYearName';

    const MONDAI_KEY = '問';
    const TEN_KEY = '点';
    
    const HAS_INLAND= 1;
    const HAS_OUT_INLAND= 0;
    const CODE_OUT_LAND = 9901;
    const LIM_PROGES_BAR_R1_12367= 300;
    const LIM_PROGES_BAR_R2_345367= 100;
    const LIM_PROGES_BAR_R1_45= 200;
    const LIM_PROGES_BAR_R2_12= 150;

    // exam name
    const EXAM_NAME_EIKEN = '英検';
    const EXAM_NAME_IBA = 'IBA';

    //exam name for IBA
    const EXAM_TYPE_NAME_IBA = '英検IBA';

    // iba type constant
    const IBA_RESULT_TOTAL = 'TOTAL';
    const IBA_RESULT_READING = 'READING';
    const IBA_RESULT_LISTENING = 'LISTENING';
}