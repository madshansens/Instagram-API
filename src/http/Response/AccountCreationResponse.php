<?php

namespace InstagramAPI;

class AccountCreationResponse extends Response
{
    public $username;
    public $has_anonymous_profile_picture;
    public $allow_contacts_sync;
    public $nux_private_first_page;
    public $profile_pic_url;
    public $full_name;
    public $pk;
    /*
     * @var HdProfilePicUrlInfo
     */
    public $hd_profile_pic_url_info;
    public $nux_private_enabled;
    public $is_private;
    public $account_created = false;
    public $feedback_title = '';
    public $feedback_message = '';
    public $spam = false;
    public $feedback_action = '';
    public $feedback_url = '';
    public $errors = null;
}
