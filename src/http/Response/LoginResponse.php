<?php

namespace InstagramAPI;

class LoginResponse extends Response
{
    var $username;
    var $has_anonymous_profile_picture;
    var $profile_pic_url;
    var $profile_pic_id;
    var $full_name;
    var $pk;
    var $is_private;
    var $error_title; // on wrong pass
    var $error_type; // on wrong pass
    var $buttons; // on wrong pass
    var $invalid_credentials; // on wrong pass
    /**
    * @var User
    */
    var $logged_in_user;

}
