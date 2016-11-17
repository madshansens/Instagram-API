<?php

namespace InstagramAPI;

class User extends Response
{
    var $username;
    var $has_anonymous_profile_picture = false;
    var $is_favorite = false;
    var $profile_pic_url;
    var $full_name;
    var $pk;
    var $is_verified = false;
    var $is_private = false;
    var $coeff_weight = 0;
    /**
    * @var FriendshipStatus
    */
    var $friendship_status = null;
    var $hd_profile_pic_versions;
    var $byline;
    var $search_social_context;
    var $unseen_count;
    var $mutual_followers_count;
    var $follower_count;
    var $social_context;
    var $media_count;
    var $following_count;
    var $is_business;
    var $usertags_count;
    var $profile_context;
    var $biography;
    var $geo_media_count;
    var $is_unpublished;
    var $allow_contacts_sync; // login prop
    var $show_feed_biz_conversion_icon; // login prop
    var $profile_pic_id; // Ranked recipents response prop
    var $auto_expand_chaining; // getUsernameInfo prop
    var $can_boost_post; // getUsernameInfo prop
    var $is_profile_action_needed; // getUsernameInfo prop
    var $has_chaining; // getUsernameInfo prop
    var $include_direct_blacklist_status; // getUsernameInfo prop
    var $can_see_organic_insights; // getUsernameInfo prop
    var $can_convert_to_business; // getUsernameInfo prop
    var $show_business_conversion_icon; // getUsernameInfo prop
    var $show_conversion_edit_entry; // getUsernameInfo prop
    var $show_insights_terms; // getUsernameInfo prop
    var $hd_profile_pic_url_info; // getUsernameInfo prop
    var $usertag_review_enabled; // getUsernameInfo prop
    var $is_needy; // getUsernameInfo prop
    var $external_url; // getUsernameInfo prop
    var $external_lynx_url; // getUsernameInfo prop

}
