<?php
namespace Satellite;

class Constants{
    const PAYMENT_TYPE = 0;
    const PERSONAL_PAYMENT = 0;
    const PAYMENT_STATUS_SUCCESS = 1;
    const KYU_PAID_ERROR = 2;
    const HALL_TYPE_EXAM_DATE = array('friDate' => 1, 'satDate' => 2, 'sunDate' => 3, 'friDateAndSatDate' => 4);
    const DOUBLE_EIKEN = 3; // 3 - 対応不可 - Not support
    const SESSION_SATELLITE = 'satellite';
    const SESSION_APPLYEIKEN = 'dataApplyEiken';
    const DATA_TEST_SITE_EXEMPTION = 'dataTestSiteExemption';
    const LIST_KYU_PRICE = 'kyu';
    const PAYMENT_EIKEN_EXAN = 'paymentEikenExam';
    const CSRF_TOKEN_SERVER = 'CSRF_TOKEN_SERVER';
    const CSRF_TOKEN_SERVER_CONFIRM = 'CSRF_TOKEN_SERVER_CONFIRM';
    const TOTAL_KYU = 'TOTAL_KYU';
    
    const RESPONSE_DELETE_SUCCESS = 1;
    const RESPONSE_DELETE_FALSE = 0;
    const RESPONSE_NOT_ALLOWED_DELETE = 2;
    
    const LOG_APPLY_EIKEN_JUKENSHA = 'LogApplyEikenJukenSha';
    const LOG_PAYMENT_CREDIT = 'LogPaymentCredit';
    
    const EIKEN_APPLIED_SUCCESS = 10;
    const EIKEN_APPLIED_ERROR_CRYPTKEY = 0;
    const EIKEN_APPLIED_ERROR = 99;
}