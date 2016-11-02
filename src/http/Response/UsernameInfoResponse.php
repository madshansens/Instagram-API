<?php

namespace InstagramAPI;

class UsernameInfoResponse extends Response
{
    protected $usertags_count;
    protected $has_anonymous_profile_picture;
    protected $full_name;
    protected $following_count;
    protected $auto_expand_chaining;
    protected $external_lynx_url = '';
    protected $can_boost_post = false;
    protected $hd_profile_pic_versions;
    protected $biography;
    protected $has_chaining;
    protected $media_count;
    protected $follower_count;
    protected $pk;
    protected $username;
    protected $geo_media_count;
    protected $profile_pic_url;
    protected $can_see_organic_insights = false;
    protected $is_private;
    protected $can_convert_to_business = false;
    protected $is_business;
    protected $show_insights_terms = false;
    protected $hd_profile_pic_url_info;
    protected $usertag_review_enabled = false;
    protected $external_url;
    protected $is_favorite;
    protected $is_verified;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->usertags_count = $response['user']['usertags_count'];
            $this->has_anonymous_profile_picture = $response['user']['has_anonymous_profile_picture'];
            $this->full_name = $response['user']['full_name'];
            $this->following_count = $response['user']['following_count'];
            $this->auto_expand_chaining = $response['user']['auto_expand_chaining'];
            if (array_key_exists('external_lynx_url', $response['user'])) {
                $this->external_lynx_url = $response['user']['external_lynx_url'];
            }
            if (array_key_exists('can_boost_post', $response['user'])) {
                $this->can_boost_post = $response['user']['can_boost_post'];
            }
            if (array_key_exists('hd_profile_pic_versions', $response['user'])) {
                $profile_pics_vers = [];
                foreach ($response['user']['hd_profile_pic_versions'] as $profile_pic) {
                    $profile_pics_vers[] = new HdProfilePicUrlInfo($profile_pic);
                }
                $this->hd_profile_pic_versions = $profile_pics_vers;
            }
            $this->biography = $response['user']['biography'];
            $this->has_chaining = $response['user']['has_chaining'];
            $this->media_count = $response['user']['media_count'];
            $this->follower_count = $response['user']['follower_count'];
            $this->pk = $response['user']['pk'];
            $this->username = $response['user']['username'];
            if (array_key_exists('geo_media_count', $response['user'])) {
                $this->geo_media_count = $response['user']['geo_media_count'];
            }
            $this->profile_pic_url = $response['user']['profile_pic_url'];
            if (array_key_exists('can_see_organic_insights', $response['user'])) {
                $this->can_see_organic_insights = $response['user']['can_see_organic_insights'];
            }
            $this->is_private = $response['user']['is_private'];
            if (array_key_exists('is_favorite', $response['user'])) {
                $this->is_favorite = $response['user']['is_favorite'];
            }
            if (array_key_exists('is_verified', $response['user'])) {
                $this->is_verified = $response['user']['is_verified'];
            }
            if (array_key_exists('can_convert_to_business', $response['user'])) {
                $this->can_convert_to_business = $response['user']['can_convert_to_business'];
            }
            $this->is_business = $response['user']['is_business'];
            if (array_key_exists('show_insights_terms', $response['user'])) {
                $this->show_insights_terms = $response['user']['show_insights_terms'];
            }
            $this->hd_profile_pic_url_info = new HdProfilePicUrlInfo($response['user']['hd_profile_pic_url_info']);
            if (array_key_exists('usertag_review_enabled', $response['user'])) {
                $this->usertag_review_enabled = $response['user']['usertag_review_enabled'];
            }
            $this->external_url = $response['user']['external_url'];
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getUsertagCount()
    {
        return $this->usertags_count;
    }

    public function getHasAnonymousProfilePicture()
    {
        return $this->has_anonymous_profile_picture;
    }

    public function getFullName()
    {
        return $this->full_name;
    }

    public function getFollowingCount()
    {
        return $this->following_count;
    }

    public function autoExpandChaining()
    {
        return $this->auto_expand_chaining;
    }

    public function getExternalLynxUrl()
    {
        return $this->external_lynx_url;
    }

    public function canBoostPost()
    {
        return $this->can_boost_post;
    }

    /**
     * @return HdProfilePicUrlInfo[]
     */
    public function getProfilePicVersions()
    {
        return $this->hd_profile_pic_versions;
    }

    public function getBiography()
    {
        return $this->biography;
    }

    public function hasChaining()
    {
        return $this->has_chaining;
    }

    public function getMediaCount()
    {
        return $this->media_count;
    }

    public function getFollowerCount()
    {
        return $this->follower_count;
    }

    public function getUsernameId()
    {
        return $this->pk;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getGeoMediaCount()
    {
        return $this->geo_media_count;
    }

    public function getProfilePicUrl()
    {
        return $this->profile_pic_url;
    }

    public function canSeeOrganicInsights()
    {
        return $this->can_see_organic_insights;
    }

    public function isPrivate()
    {
        return $this->is_private;
    }

    public function isFavorite()
    {
        return $this->is_favorite;
    }

    public function isVerified()
    {
        return $this->is_verified;
    }

    public function canConvertToBusiness()
    {
        return $this->can_convert_to_business;
    }

    public function isBusiness()
    {
        return $this->is_business;
    }

    public function showInsightsTerms()
    {
        return $this->show_insights_terms;
    }

    /**
     * @return HdProfilePicUrlInfo
     */
    public function getHdProfilePicUrlInfo()
    {
        return $this->hd_profile_pic_url_info;
    }

    public function getUsertagReviewEnabled()
    {
        return $this->usertag_review_enabled;
    }

    public function getExternalUrl()
    {
        return $this->external_url;
    }
}
