<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Eiken;

class EikenConst
{
    const NOT_EXIST = 0;
    const EXIST = 1;
    
    const SAVE_FAIL_FUNDINGSTATUS_INTO_SESSION = 0;
    const SAVE_SUCCESS_FUNDINGSTATUS_INTO_SESSION = 1;
    
    const SAVE_DATA_INTO_DATABASE_FAIL = 0;
    const SAVE_DATA_INTO_DATABASE_SUCCESS = 1;
    const SAVE_WITH_NO_CHANGING = 2;
    
    const APPLY_ACTION = 'applyAciton';
    const APPLY_DATA_FROM_POST = 'applyData';
    const APPLY_REFUND_STATUS_FROM_DATABASE = 'refundStatus';
    
    const HASS_TYPE_IS_MAIN_HALL = 1;
    const HASS_TYPE_IS_STANDER_HALL = 0;

    
    const PAYMENT_TYPE_IS_INVIDIUAL = 0;
    const PAYMENT_TYPE_IS_COLLECTTIVE = 1;
    
    const BENEFICIARY_IS_DANTAI = 1;
    const BENEFICIARY_IS_STUDENT = 2;
    
    const NON_SEMI_COLLECTTIVE = 61;
    const NON_SEMI_INDIVIDUAL = 62;
    const SEMI_DANTAI_COLLECTTIVE = 63;
    const SEMI_DANTAI_INDIVIDUAL = 64;
    const SEMI_STUDENT_COLLECTTIVE = 65;
    const SEMI_STUDENT_INDIVIDUAL = 66;
    
    const PUBLIC_TEMPLATE_MAIL = 71;
}