<?php

namespace InstagramAPI\Response\Model;

class User extends \InstagramAPI\Response
{
    public $username;
    public $has_anonymous_profile_picture;
    public $is_favorite;
    public $profile_pic_url;
    public $full_name;
    /**
     * @var string
     */
    public $user_id;
    /**
     * @var string
     */
    public $pk;
    /**
     * @var string
     */
    public $id;
    public $is_verified;
    public $is_private;
    public $coeff_weight;
    /**
     * @var FriendshipStatus
     */
    public $friendship_status;
    /**
     * @var ImageCandidate[]
     */
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
    /**
     * @var string
     */
    public $profile_pic_id; // Ranked recipents response prop
    public $auto_expand_chaining; // getInfoById prop
    public $can_boost_post; // getInfoById prop
    public $is_profile_action_needed; // getInfoById prop
    public $has_chaining; // getInfoById prop
    public $include_direct_blacklist_status; // getInfoById prop
    public $can_see_organic_insights; // getInfoById prop
    public $can_convert_to_business; // getInfoById prop
    public $convert_from_pages; // getInfoById prop
    public $show_business_conversion_icon; // getInfoById prop
    public $show_conversion_edit_entry; // getInfoById prop
    public $show_insights_terms; // getInfoById prop
    public $can_create_sponsor_tags; // getInfoById prop
    /**
     * @var ImageCandidate
     */
    public $hd_profile_pic_url_info; // getInfoById prop
    public $usertag_review_enabled; // getInfoById prop
    /**
     * @var string[]
     */
    public $profile_context_mutual_follow_ids; // getInfoById prop
    /**
     * @var Link[]
     */
    public $profile_context_links_with_user_ids; // getInfoById prop
    public $has_biography_translation; // getInfoById prop
    public $business_contact_method; // getInfoById prop
    public $category; // getInfoById prop
    public $direct_messaging; // getInfoById prop
    public $page_name; //getInfoById prop
    /**
     * @var string
     */
    public $fb_page_call_to_action_id; // getInfoById prop
    public $is_call_to_action_enabled; // getInfoById prop
    public $public_phone_country_code; // getInfoById prop
    public $public_phone_number; // getInfoById prop
    public $contact_phone_number; // getInfoById prop
    /**
     * @var float
     */
    public $latitude; // getInfoById prop
    /**
     * @var float
     */
    public $longitude; // getInfoById prop
    public $address_street; // getInfoById prop
    public $zip; // getInfoById prop
    /**
     * @var string
     */
    public $city_id; // getInfoById prop
    public $city_name; // getInfoById prop
    public $public_email; // getInfoById prop
    public $is_needy; // getInfoById prop
    public $external_url; // getInfoById prop
    public $external_lynx_url; // getInfoById prop
    public $email; // getCurrentUser prop
    public $country_code; // getCurrentUser prop
    public $birthday; // getCurrentUser prop
    public $national_number; // getCurrentUser prop
    public $gender; // getCurrentUser prop
    public $phone_number; // getCurrentUser prop
    public $needs_email_confirm; // getCurrentUser prop
    public $is_active;
    public $block_at; // getBlockedList prop
    public $aggregate_promote_engagement; // getSelfInfo prop
    public $fbuid;
}
