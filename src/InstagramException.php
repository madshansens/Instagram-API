<?php

namespace InstagramAPI;

// Error codes:
// 0: Unrecognized by parser
const UNKNOWN = 0;

// 1: On Instagram login_required response
const IG_LOGIN_REQUIRED = 1;
const IG_LOGIN_REQUIRED_REGEX = "/login_required/";

// 2: On Instagram feedback_required response
const IG_FEEDBACK_REQUIRED = 2;
const IG_FEEDBACK_REQUIRED_REGEX = "/feedback_required/";

// 3: On Instagram checkpoint_required response
const IG_CHECKPOINT_REQUIRED = 3;
const IG_CHECKPOINT_REQUIRED_REGEX = "/checkpoint_required/";

// 4: On Instagram "The password you entered is incorrect" response
const IG_INCORRECT_PASSWORD = 4;
const IG_INCORRECT_PASSWORD_REGEX = "/password(.*)incorrect/";

// 5: On empty response from IG
const EMPTY_RESPONSE = 5;

class InstagramException extends \Exception
{
    public function __construct($message, $code = null, Exception $previous = null) {
        if (is_null($code)) {
            if (preg_match(IG_LOGIN_REQUIRED_REGEX, $message) === 1) {
                $code = IG_LOGIN_REQUIRED;
            } else if (preg_match(IG_FEEDBACK_REQUIRED_REGEX, $message) === 1) {
                $code = IG_FEEDBACK_REQUIRED;
            } else if (preg_match(IG_CHECKPOINT_REQUIRED_REGEX, $message) === 1) {
                $code = IG_CHECKPOINT_REQUIRED;
            } else if (preg_match(IG_INCORRECT_PASSWORD_REGEX, $message) === 1) {
                $code = IG_INCORRECT_PASSWORD;
            } else {
                $code = 0;
            }
        }

        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return "Code " . $this->code . ": " . $this->getMessage();
    }
}
