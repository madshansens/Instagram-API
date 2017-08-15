<?php

namespace InstagramAPI\Exception;

use InstagramAPI\ResponseInterface;
use Psr\Http\Message\ResponseInterface as HttpResponseInterface;

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
     * WARNING TO CONTRIBUTORS: Do NOT "contribute" a bunch of endpoint function
     * specific garbage exceptions here, such as "User not found", "Duplicate
     * comment", "No permission to view profile" or other garbage. Those server
     * messages are human-readable, unreliable (they can change at any time) and
     * are also totally non-critical. You should handle them yourself in your
     * end-user applications by simply catching their EndpointException and
     * looking at the contents of its getMessage() property, or checking if it
     * hasResponse() and then getResponse() to see the full server response. The
     * exceptions listed below are *critical* exceptions related to the CORE of
     * the API! Nothing else.
     *
     * @var array
     */
    const EXCEPTION_MAP = [
        'LoginRequiredException'       => ['login_required'],
        'ChallengeRequiredException'   => ['challenge_required'],
        'CheckpointRequiredException'  => [
            'checkpoint_required', // message
            'checkpoint_challenge_required', // error_type
        ],
        'FeedbackRequiredException'    => ['feedback_required'],
        'IncorrectPasswordException'   => [
            // "The password you entered is incorrect".
            '/password(.*?)incorrect/', // message
            'bad_password', // error_type
        ],
        'InvalidSmsCodeException'      => [
            // "Please check the security code we sent you and try again".
            '/check(.*?)security(.*?)code/', // message
            'sms_code_validation_code_invalid', // error_type
        ],
        'AccountDisabledException'     => [
            // "Your account has been disabled for violating our terms".
            '/account(.*?)disabled(.*?)violating/',
        ],
        'SentryBlockException'         => ['sentry_block'],
        'InvalidUserException'         => [
            // "The username you entered doesn't appear to belong to an account"
            '/username(.*?)doesn\'t(.*?)belong/', // message
            'invalid_user', // error_type
        ],
        'ForcedPasswordResetException' => ['/reset(.*?)password/'],
    ];

    /**
     * Parses a server message and throws the appropriate exception.
     *
     * Uses the generic EndpointException if no other exceptions match.
     *
     * @param string|null                $prefixString   What prefix to use for the message in
     *                                                   the final exception. Should be something
     *                                                   helpful such as the name of the class or
     *                                                   function which threw. Can be NULL.
     * @param string                     $serverMessage  The failure string from Instagram's API.
     * @param ResponseInterface|null     $serverResponse The complete server response object,
     *                                                   if one is available (optional).
     * @param HttpResponseInterface|null $httpResponse   The HTTP response object (if available).
     *
     * @throws InstagramException The appropriate exception.
     */
    public static function autoThrow(
        $prefixString,
        $serverMessage,
        ResponseInterface $serverResponse = null,
        HttpResponseInterface $httpResponse = null)
    {
        $messages = [$serverMessage];
        if ($serverResponse instanceof ResponseInterface) {
            $fullResponse = $serverResponse->getFullResponse();
            if (isset($fullResponse->error_type)
                && is_string($fullResponse->error_type)) {
                $messages[] = $fullResponse->error_type;
            }
        }

        $exceptionClass = null;

        // Check if the server message is in our CRITICAL exception table.
        foreach ($messages as $message) {
            foreach (self::EXCEPTION_MAP as $className => $patterns) {
                foreach ($patterns as $pattern) {
                    if ($pattern[0] == '/') {
                        // Regex check.
                        if (preg_match($pattern, $message)) {
                            $exceptionClass = $className;
                            break 3;
                        }
                    } else {
                        // Regular string search.
                        if (strpos($message, $pattern) !== false) {
                            $exceptionClass = $className;
                            break 3;
                        }
                    }
                }
            }
        }

        // Check the HTTP status code if no critical exception has been found.
        if ($exceptionClass === null) {
            // NOTE FOR CONTRIBUTORS: All HTTP status exceptions below MUST be
            // derived from EndpointException, since all HTTP errors are
            // endpoint-error-related responses and MUST be easily catchable!
            $httpStatusCode = $httpResponse !== null ? $httpResponse->getStatusCode() : null;
            switch ($httpStatusCode) {
                case 400:
                    $exceptionClass = 'BadRequestException';
                    break;
                case 404:
                    $exceptionClass = 'NotFoundException';
                    break;
                default:
                    // No critical exceptions and no HTTP code exceptions have
                    // been found, so use the generic "API function exception"!
                    $exceptionClass = 'EndpointException';
            }
        }

        // We need to specify the full namespace path to the exception class.
        $fullClassPath = '\\'.__NAMESPACE__.'\\'.$exceptionClass;

        // Some Instagram messages already have punctuation, and others need it.
        $serverMessage = self::prettifyMessage($serverMessage);

        // Create an instance of the final exception class, with the pretty msg.
        $e = new $fullClassPath(
            $prefixString !== null
            ? sprintf('%s: %s', $prefixString, $serverMessage)
            : $serverMessage
        );

        // Attach the server response to the exception, IF a response exists.
        // NOTE: Only possible on exceptions derived from InstagramException.
        if ($serverResponse instanceof ResponseInterface
            && $e instanceof \InstagramAPI\Exception\InstagramException) {
            $e->setResponse($serverResponse);
        }

        throw $e;
    }

    /**
     * Nicely reformats externally generated exception messages.
     *
     * This is used for guaranteeing consistent message formatting with full
     * English sentences, ready for display to the user.
     *
     * @param string $message The original message.
     *
     * @return string The cleaned-up message.
     */
    public static function prettifyMessage(
        $message)
    {
        // Some messages already have punctuation, and others need it. Prettify
        // the message by ensuring that it ALWAYS ends in punctuation, for
        // consistency with all of our internal error messages.
        $lastChar = substr($message, -1);
        if ($lastChar !== '' && $lastChar !== '.' && $lastChar !== '!' && $lastChar !== '?') {
            $message .= '.';
        }

        // Guarantee that the first letter is uppercase.
        $message = ucfirst($message);

        // Replace all underscores (ie. "Login_required.") with spaces.
        $message = str_replace('_', ' ', $message);

        return $message;
    }
}
