<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getFooter()
 * @method string getId()
 * @method Item getMedia()
 * @method mixed getTitle()
 * @method mixed getTrackingToken()
 * @method mixed getType()
 * @method bool isFooter()
 * @method bool isId()
 * @method bool isMedia()
 * @method bool isTitle()
 * @method bool isTrackingToken()
 * @method bool isType()
 * @method setFooter(mixed $value)
 * @method setId(string $value)
 * @method setMedia(Item $value)
 * @method setTitle(mixed $value)
 * @method setTrackingToken(mixed $value)
 * @method setType(mixed $value)
 */
class Ad4ad extends AutoPropertyHandler
{
    public $type;
    public $title;
    /**
     * @var Item
     */
    public $media;
    public $footer;
    /**
     * @var string
     */
    public $id;
    public $tracking_token;
}
