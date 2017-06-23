<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method string getPhoneNumber()
 * @method string getUrl()
 * @method bool isPhoneNumber()
 * @method bool isUrl()
 * @method setPhoneNumber(string $value)
 * @method setUrl(string $value)
 */
class MsisdnHeaderResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /** @var string */
    public $phone_number;

    /** @var string */
    public $url;
}
