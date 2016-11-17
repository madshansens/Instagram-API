<?php

namespace InstagramAPI;

class Comment extends Response
{
    var $status;
    var $username_id = null;
    var $created_at_utc;
    var $created_at;
    var $bit_flags = null;
    /**
    * @var User
    */
    var $user;
    var $pk;
    var $text;

}
