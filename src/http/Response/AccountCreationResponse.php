<?php

namespace InstagramAPI;

class AccountCreationResponse extends Response
{
    var $username;
    var $has_anonymous_profile_picture;
    var $allow_contacts_sync;
    var $nux_private_first_page;
    var $profile_pic_url;
    var $full_name;
    var $pk;
    /**
     * @var HdProfilePicUrlInfo
     */
    var $hd_profile_pic_url_info;
    var $nux_private_enabled;
    var $is_private;
    var $account_created = false;
    var $feedback_title = '';
    var $feedback_message = '';
    var $spam = false;
    var $feedback_action = '';
    var $feedback_url = '';
    var $errors = null;
}
