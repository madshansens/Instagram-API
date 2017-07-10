<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method float getExpires()
 * @method string getJob()
 * @method string getUrl()
 * @method bool isExpires()
 * @method bool isJob()
 * @method bool isUrl()
 * @method setExpires(float $value)
 * @method setJob(string $value)
 * @method setUrl(string $value)
 */
class VideoUploadUrl extends AutoPropertyHandler
{
    /** @var string */
    public $url;
    /** @var string */
    public $job;
    /** @var float */
    public $expires;
}
