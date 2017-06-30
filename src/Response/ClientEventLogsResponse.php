<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getAppData()
 * @method mixed getChecksum()
 * @method mixed getConfig()
 * @method mixed getError()
 * @method bool isAppData()
 * @method bool isChecksum()
 * @method bool isConfig()
 * @method bool isError()
 * @method setAppData(mixed $value)
 * @method setChecksum(mixed $value)
 * @method setConfig(mixed $value)
 * @method setError(mixed $value)
 */
class ClientEventLogsResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $checksum;
    public $config;
    public $app_data;
    public $error;
}
