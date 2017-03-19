<?php

namespace InstagramAPI;

/**
 * Instagram API-related errors.
 *
 * The error codes below are various critical *CORE* problems that can happen in
 * ANY Instagram API call. We do NOT want to add tons of slow regexes for the
 * millions of different problems that can happen in individual API functions,
 * such as "User not found" when doing a username lookup, etc. That would just
 * bloat and slow down this function, and all of those message strings may
 * change at any moment.
 *
 * End-users should handle such messages MANUALLY in their projects, by catching
 * the general "InstagramException" and reading its "getMessage()" property to
 * look for strings such as "User not found". That is NOT the job of THIS class.
 *
 * Again: The error codes below are ONLY for critical *CORE* API problems.
 */
class ErrorCode
{
    // Error codes:
    // 0: Other Instagram error message which isn't mapped to any code.
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

    // 10: Throttled by Instagram's server because of too many requests
    const IG_API_THROTTLED = 10;

    /**
     * 1XX: Internal Errors.
     */
    const INTERNAL_INVALID_ARGUMENT = 100; // Used for all bad user-provided args.
    const INTERNAL_LOGIN_REQUIRED = 101;
    const INTERNAL_PROXY_ERROR = 102;
    const INTERNAL_CSRF_TOKEN_ERROR = 103;
    const INTERNAL_SETTINGS_ERROR = 104;
    // const INTERNAL_HTTP_NOTFOUND = 105; // NOT USED! See HttpInterface for reason.
    const INTERNAL_UPLOAD_FAILED = 106;
}

class InstagramException extends \Exception
{
    /**
     * Constructor.
     *
     * Always tag INTERNAL exceptions with a helpful code! It's the ONLY way
     * that library users can identify what an exception is about! Messages are
     * unreliable and subject to change, but error codes always stay the same.
     *
     * As for Instagram API exceptions coming from THEIR server; always leave
     * the $code empty and let this class figure it out based on the $message!
     *
     * Note that regardless of the message origin, this class always guarantees
     * that the message ends in proper punctuation, for perfect consistency.
     *
     * @param string          $message  The message to display. Can come from
     *                                  Instagram's server or from this library.
     *                                  If from this library, you MUST write a
     *                                  proper English sentence, ending in a period.
     * @param int|null        $code     (optional) If the message comes from
     *                                  Instagram's server, leave this at null.
     *                                  If from this library, you MUST ALWAYS
     *                                  use one of the INTERNAL_* error codes
     *                                  above, otherwise your message could be
     *                                  misidentified as coming from Instagram's
     *                                  server due to typing a similar message!
     * @param \Exception|null $previous (optional) Lets you chain exceptions.
     */
    public function __construct($message, $code = null, \Exception $previous = null)
    {
        // If no code was provided, this wasn't an internal exception,
        // so attempt to detect Instagram's message and map it to an error code.
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
                $code = ErrorCode::UNKNOWN;
            }
        }

        // Some Instagram messages already have punctuation, and others need it.
        // Prettify the message by ensuring that it ALWAYS ends in punctuation,
        // for consistency with all of our internal error messages.
        $lastChar = substr($message, -1);
        if ($lastChar !== '' && $lastChar !== '.' && $lastChar !== '!' && $lastChar !== '?') {
            $message .= '.';
        }

        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return 'Code '.$this->getCode().': '.$this->getMessage().PHP_EOL.'Stack trace:'.PHP_EOL.$this->getTraceAsString();
    }
}
