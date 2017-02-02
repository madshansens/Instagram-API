<?php

namespace InstagramAPI;

class LoginResponse extends Response
{
    public $username;
    public $has_anonymous_profile_picture;
    public $profile_pic_url;
    public $profile_pic_id;
    public $full_name;
    public $pk;
    public $is_private;
    public $error_title; // on wrong pass
    public $error_type; // on wrong pass
    public $buttons; // on wrong pass
    public $invalid_credentials; // on wrong pass
    /**
     * @var User
     */
    public $logged_in_user;
}
