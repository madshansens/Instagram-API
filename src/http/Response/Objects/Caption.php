<?php

namespace InstagramAPI;

class Caption extends Response
{
    public $status;
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
    public $media_id;
    public $pk;
    public $type;
    public $has_translation;
}
