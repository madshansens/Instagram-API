<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getAvailable()
 * @method mixed getConfirmed()
 * @method mixed getErrorType()
 * @method string[] getUsernameSuggestions()
 * @method mixed getValid()
 * @method bool isAvailable()
 * @method bool isConfirmed()
 * @method bool isErrorType()
 * @method bool isUsernameSuggestions()
 * @method bool isValid()
 * @method setAvailable(mixed $value)
 * @method setConfirmed(mixed $value)
 * @method setErrorType(mixed $value)
 * @method setUsernameSuggestions(string[] $value)
 * @method setValid(mixed $value)
 */
class CheckEmailResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $valid;
    public $available;
    public $confirmed;
    /**
     * @var string[]
     */
    public $username_suggestions;
    public $error_type;
}
