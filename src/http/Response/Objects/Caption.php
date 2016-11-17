<?php

namespace InstagramAPI;

class Caption extends Response
{
    var $status;
    var $user_id;
    var $created_at_utc;
    var $created_at;
    var $bit_flags;
    /**
    * @var User
    */
    var $user;
    var $content_type;
    var $text;
    var $media_id;
    var $pk;
    var $type;
    var $has_translation;

}
