<?php

namespace InstagramAPI;

class User extends Response
{
    public $username;
    public $has_anonymous_profile_picture = false;
    public $is_favorite = false;
    public $profile_pic_url;
    public $full_name;
    public $pk;
    public $is_verified = false;
    public $is_private = false;
    public $coeff_weight = 0;
    /**
     * @var FriendshipStatus
     */
    public $friendship_status = null;
    public $hd_profile_pic_versions;
    public $byline;
    public $search_social_context;
    public $unseen_count;
    public $mutual_followers_count;
    public $follower_count;
    public $social_context;
    public $media_count;
    public $following_count;
    public $is_business;
    public $usertags_count;
    public $profile_context;
    public $biography;
    public $geo_media_count;
    public $is_unpublished;
    public $allow_contacts_sync; // login prop
    public $show_feed_biz_conversion_icon; // login prop
    public $profile_pic_id; // Ranked recipents response prop
    public $auto_expand_chaining; // getUsernameInfo prop
    public $can_boost_post; // getUsernameInfo prop
    public $is_profile_action_needed; // getUsernameInfo prop
    public $has_chaining; // getUsernameInfo prop
    public $include_direct_blacklist_status; // getUsernameInfo prop
    public $can_see_organic_insights; // getUsernameInfo prop
    public $can_convert_to_business; // getUsernameInfo prop
    public $show_business_conversion_icon; // getUsernameInfo prop
    public $show_conversion_edit_entry; // getUsernameInfo prop
    public $show_insights_terms; // getUsernameInfo prop
    public $hd_profile_pic_url_info; // getUsernameInfo prop
    public $usertag_review_enabled; // getUsernameInfo prop
    public $is_needy; // getUsernameInfo prop
    public $external_url; // getUsernameInfo prop
    public $external_lynx_url; // getUsernameInfo prop
}
