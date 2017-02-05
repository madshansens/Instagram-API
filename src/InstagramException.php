<?php

namespace InstagramAPI;

/**
 * Instagram API-related errors.
 *
 * Parses a regex from Instagram response to set error code.
 * When throwing a new Instagram-related error, make sure to
 * include the "message" field as a part of the string argument
 * used for the InstagramException's constructor.
 */
class ErrorCode {
    // Error codes:
    // 0: Unrecognized by parser
    const UNKNOWN = 0;

    // 1: On Instagram login_required response
    const IG_LOGIN_REQUIRED = 1;
    const IG_LOGIN_REQUIRED_REGEX = '/login_required/';

    // 2: On Instagram feedback_required response
    const IG_FEEDBACK_REQUIRED = 2;
    const IG_FEEDBACK_REQUIRED_REGEX = '/feedback_required/';

    // 3: On Instagram checkpoint_required response
    const IG_CHECKPOINT_REQUIRED = 3;
    const IG_CHECKPOINT_REQUIRED_REGEX = '/checkpoint_required/';

    // 4: On Instagram "The password you entered is incorrect" response
    const IG_INCORRECT_PASSWORD = 4;
    const IG_INCORRECT_PASSWORD_REGEX = '/password(.*)incorrect/';

    // 5: On empty response from IG
    const EMPTY_RESPONSE = 5;

    // 6: On Instagram "Your account has been disabled for violating our terms" response
    const IG_ACCOUNT_DISABLED = 6;
    const IG_ACCOUNT_DISABLED_REGEX = '/account(.*)disabled(.*)violating/';

    // 7: On Instagram "sentry_block" response (?)
    const IG_SENTRY_BLOCK = 7;
    const IG_SENTRY_BLOCK_REGEX = '/sentry_block/';

    // 8: On Instagram "invalid_user" response (?)
    const IG_INVALID_USER = 8;
    const IG_INVALID_USER_REGEX = '/invalid_user/';

    // 9: On Instagram forced password reset response
    const IG_RESET_PASSWORD = 9;
    const IG_RESET_PASSWORD_REGEX = '/reset(.*)password/';

    /**
     * 1XX: Internal Errors.
     */
    const INTERNAL_LOGIN_REQUIRED = 101;
    const INTERNAL_PROXY_ERROR = 102;
    const INTERNAL_CSRF_TOKEN_ERROR = 103;
    const INTERNAL_SETTINGS_ERROR = 104;
}

class InstagramException extends \Exception
{
    public function __construct($message, $code = null, Exception $previous = null)
    {
        if (is_null($code)) {
            if (preg_match(ErrorCode::IG_LOGIN_REQUIRED_REGEX, $message) === 1) {
                $code = ErrorCode::IG_LOGIN_REQUIRED;
            } elseif (preg_match(ErrorCode::IG_FEEDBACK_REQUIRED_REGEX, $message) === 1) {
                $code = ErrorCode::IG_FEEDBACK_REQUIRED;
            } elseif (preg_match(ErrorCode::IG_CHECKPOINT_REQUIRED_REGEX, $message) === 1) {
                $code = ErrorCode::IG_CHECKPOINT_REQUIRED;
            } elseif (preg_match(ErrorCode::IG_INCORRECT_PASSWORD_REGEX, $message) === 1) {
                $code = ErrorCode::IG_INCORRECT_PASSWORD;
            } elseif (preg_match(ErrorCode::IG_ACCOUNT_DISABLED_REGEX, $message) === 1) {
                $code = ErrorCode::IG_ACCOUNT_DISABLED;
            } elseif (preg_match(ErrorCode::IG_SENTRY_BLOCK_REGEX, $message) === 1) {
                $code = ErrorCode::IG_SENTRY_BLOCK;
            } elseif (preg_match(ErrorCode::IG_INVALID_USER_REGEX, $message) === 1) {
                $code = ErrorCode::IG_INVALID_USER;
            } elseif (preg_match(ErrorCode::IG_RESET_PASSWORD_REGEX, $message) === 1) {
                $code = ErrorCode::IG_RESET_PASSWORD;
            } else {
                $code = 0;
            }
        }

        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return 'Code '.$this->code.': '.$this->getMessage();
    }
}
