<?php

namespace InstagramAPI;

class Comment extends Response
{
    public $status;
    /**
     * @var string
     */
    public $username_id;
    public $created_at_utc;
    public $created_at;
    public $bit_flags;
    /**
     * @var User
     */
    public $user;
    /**
     * @var string
     */
    public $pk;
    public $text;
}
