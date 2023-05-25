<?php

namespace C;

const SUPPORT_EMAIL = 'operations@cratos.net';
const TWO_FA_CODE_PLACEHOLDER = '676835';

const MONEY_SCALE = 8;
const MONEY_LENGTH = MONEY_SCALE + 12;
const CURRENCY_NAME_LENGTH = 16;

// @todo rename for common money
const RATES_SCALE = MONEY_SCALE;
const RATES_DEFAULT_PRECISION = 2;



const COMMON_ERROR_KEY = 'error_unknown';

const PHONE_CC_MAX = 6;
const PHONE_NO_MAX = 20;
const PHONE_NO_MIN = 6;

const PHONE_RULES = [
    'phone_cc_part' => ['required', 'string', 'digits_between:1,' . PHONE_CC_MAX],
    'phone_no_part' => ['required', 'string', 'digits_between:' . PHONE_NO_MIN . ',' . PHONE_NO_MAX],
];

const PASSWORD_MIN = 8;
const PASSWORD_MAX = 100;
const PASSWORD_SPECIALS = '$#@!%^&*(),.+-/*';

const PHONE_ERROR_KEY = 'error_phone';
const PASSWORD_ERROR_KEY = 'error_password';
const EMAIL_ERROR_KEY = 'error_email';

const REGISTER_SESSION_DATA_KEY = 'cuser_temporary_register_data';
const REGISTER_SESSION_DATA_TTL = '10m';

const REMEMBER_MY_USERNAME_TTL = 60*24*365*10;  // minutes
const REMEMBER_MY_USERNAME_COOKIE = 'remember_my_username';

const TWO_FA_CODE_SIZE = 6;
const TWO_FA_CODE_TTL = '3m';
const TWO_FA_GOOGLE_WINDOW = 1;

// @note good name until only 1 SMS code type in use
const SMS_ATTEMPTS = 3;
const CODE_VERIFY_TRY_ATTEMPTS = 3;
const CRYPTO_CODE_VERIFY_TRY_ATTEMPTS = 2;
const SMS_BLOCK_TTL = '24h';
const SMS_SIZE = 6;

const MERCHANT_PAYMENT_DATA_KEY = 'merchant_temporary_payment_data';
const MERCHANT_PAYMENT_SMS_ATTEMPTS = 3;
const MERCHANT_PAYMENT_SMS_BLOCK_TTL = '5m';

