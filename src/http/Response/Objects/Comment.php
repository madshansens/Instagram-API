<?php

namespace InstagramAPI;

class Comment extends Response
{
    public $status;
    public $username_id = null;
    public $created_at_utc;
    public $created_at;
    public $bit_flags = null;
    /**
     * @var User
     */
    public $user;
    public $pk;
    public $text;
}
