<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getBitFlags()
 * @method mixed getContentType()
 * @method string getCreatedAt()
 * @method string getCreatedAtUtc()
 * @method string getMediaId()
 * @method string getPk()
 * @method mixed getStatus()
 * @method mixed getText()
 * @method mixed getType()
 * @method User getUser()
 * @method string getUserId()
 * @method bool isBitFlags()
 * @method bool isContentType()
 * @method bool isCreatedAt()
 * @method bool isCreatedAtUtc()
 * @method bool isMediaId()
 * @method bool isPk()
 * @method bool isStatus()
 * @method bool isText()
 * @method bool isType()
 * @method bool isUser()
 * @method bool isUserId()
 * @method setBitFlags(mixed $value)
 * @method setContentType(mixed $value)
 * @method setCreatedAt(string $value)
 * @method setCreatedAtUtc(string $value)
 * @method setMediaId(string $value)
 * @method setPk(string $value)
 * @method setStatus(mixed $value)
 * @method setText(mixed $value)
 * @method setType(mixed $value)
 * @method setUser(User $value)
 * @method setUserId(string $value)
 */
class Headline extends AutoPropertyHandler
{
    public $content_type;
    /**
     * @var User
     */
    public $user;
    /**
     * @var string
     */
    public $user_id;
    /**
     * @var string
     */
    public $pk;
    public $text;
    public $type;
    /**
     * @var string
     */
    public $created_at;
    /**
     * @var string
     */
    public $created_at_utc;
    /**
     * @var string
     */
    public $media_id;
    public $bit_flags;
    public $status;
}
