<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Caption extends AutoPropertyHandler
{
    public $status;
    /**
     * @var string
     */
    public $user_id;
    public $created_at_utc;
    public $created_at;
    public $bit_flags;
    /**
     * @var User
     */
    public $user;
    public $content_type;
    public $text;
    /**
     * @var string
     */
    public $media_id;
    /**
     * @var string
     */
    public $pk;
    public $type;
    public $has_translation;
}
