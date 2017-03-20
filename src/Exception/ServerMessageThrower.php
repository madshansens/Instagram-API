<?php

namespace InstagramAPI\Exception;

/**
 * Parses Instagram's API error messages and throws an appropriate exception.
 *
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class ServerMessageThrower
{
    /**
     * Map from server messages to various exceptions.
     *
     * If the first letter of a pattern is "/", we treat it as a regex.
     *
     * The exceptions should be roughly arranged by how common they are, with
     * the most common ones checked first, at the top.
     *
     * Note that not all exceptions are listed below. Some are thrown via other
     * methods than this automatic message parser.
     *
     * @var array
     */
    const EXCEPTION_MAP = [
        'LoginRequiredException'       => ['login_required'],
        'FeedbackRequiredException'    => ['feedback_required'],
        'CheckpointRequiredException'  => ['checkpoint_required'],
        'IncorrectPasswordException'   => [
            // "The password you entered is incorrect".
            '/password(.*)incorrect/',
        ],
        'AccountDisabledException'     => [
            // "Your account has been disabled for violating our terms"
            '/account(.*)disabled(.*)violating/',
        ],
        'SentryBlockException'         => ['sentry_block'],
        'InvalidUserException'         => ['invalid_user'],
        'ForcedPasswordResetException' => ['/reset(.*)password/'],
    ];

    /**
     * Parses a server message and throws the appropriate exception.
     *
     * Uses the generic FunctionException if no other exceptions match.
     *
     * @param string|null $prefixString  What prefix to use for the message in
     *                                   the final exception. Should be something
     *                                   helpful such as the name of the class or
     *                                   function which threw. Can be NULL.
     * @param string      $serverMessage The failure string from Instagram's API.
     *
     * @throws InstagramException The appropriate exception.
     */
    public static function throw(
        $prefixString,
        $serverMessage)
    {
        foreach (self::EXCEPTION_MAP as $exceptionClass => $patterns) {
            foreach ($patterns as $pattern) {
                if ($pattern[0] == '/') {
                    // Regex check.
                    if (preg_match($pattern, $serverMessage)) {
                        return self::_throw($exceptionClass, $prefixString, $serverMessage);
                    }
                } else {
                    // Regular string search.
                    if (strpos($serverMessage, $pattern) !== false) {
                        return self::_throw($exceptionClass, $prefixString, $serverMessage);
                    }
                }
            }
        }

        // Nothing found. Use generic function exception.
        throw new FunctionException($serverMessage);
    }

    /**
     * Internal function which performs the actual throwing.
     *
     * @param string      $exceptionClass
     * @param string|null $prefixString
     * @param string      $serverMessage
     */
    private static function _throw($exceptionClass, $prefixString, $serverMessage)
    {
        // We need to specify the full namespace path to the class.
        $fullClassPath = '\\'.__NAMESPACE__.'\\'.$exceptionClass;

        // Some Instagram messages already have punctuation, and others need it.
        // Prettify the message by ensuring that it ALWAYS ends in punctuation,
        // for consistency with all of our internal error messages.
        $lastChar = substr($serverMessage, -1);
        if ($lastChar !== '' && $lastChar !== '.' && $lastChar !== '!' && $lastChar !== '?') {
            $serverMessage .= '.';
        }

        throw new $fullClassPath(
            $prefixString !== null
            ? $prefixString.': '.$serverMessage
            : $serverMessage
        );
    }
}
