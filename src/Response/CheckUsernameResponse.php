<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getAvailable()
 * @method mixed getError()
 * @method mixed getErrorType()
 * @method mixed getUsername()
 * @method bool isAvailable()
 * @method bool isError()
 * @method bool isErrorType()
 * @method bool isUsername()
 * @method setAvailable(mixed $value)
 * @method setError(mixed $value)
 * @method setErrorType(mixed $value)
 * @method setUsername(mixed $value)
 */
class CheckUsernameResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $username;
    public $available;
    public $error;
    public $error_type;
}
