<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getHeight()
 * @method string getId()
 * @method mixed getType()
 * @method mixed getUrl()
 * @method mixed getWidth()
 * @method bool isHeight()
 * @method bool isId()
 * @method bool isType()
 * @method bool isUrl()
 * @method bool isWidth()
 * @method setHeight(mixed $value)
 * @method setId(string $value)
 * @method setType(mixed $value)
 * @method setUrl(mixed $value)
 * @method setWidth(mixed $value)
 */
class VideoVersions extends AutoPropertyHandler
{
    public $url;
    public $type;
    public $width;
    public $height;
    /**
     * @var string
     */
    public $id;
}
