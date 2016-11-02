<?php

namespace InstagramAPI;

class User
{
    protected $username;
    protected $has_anonymous_profile_picture = false;
    protected $is_favorite = false;
    protected $profile_pic_url;
    protected $full_name;
    protected $pk;
    protected $is_verified = false;
    protected $is_private = false;
    protected $coeff_weight = 0;
    protected $friendship_status = null;
    protected $hd_profile_pic_versions;
    protected $byline;
    protected $search_social_context;
    protected $unseen_count;
    protected $mutual_followers_count;
    protected $follower_count;
    protected $social_context;
    protected $media_count;
    protected $following_count;
    protected $is_business;
    protected $usertags_count;
    protected $profile_context;
    protected $biography;
    protected $geo_media_count;

    public function __construct($userData)
    {
        $this->username = $userData['username'];
        $this->profile_pic_url = $userData['profile_pic_url'];
        $this->full_name = $userData['full_name'];
        $this->pk = $userData['pk'];
        if (isset($userData['is_verified'])) {
            $this->is_verified = $userData['is_verified'];
        }
        $this->is_private = $userData['is_private'];
        if (isset($userData['has_anonymous_profile_picture'])) {
            $this->has_anonymous_profile_picture = $userData['has_anonymous_profile_picture'];
        }
        if (isset($userData['is_favorite'])) {
            $this->is_favorite = $userData['is_favorite'];
        }
        if (isset($userData['coeff_weight'])) {
            $this->coeff_weight = $userData['coeff_weight'];
        }
        if (isset($userData['friendship_status'])) {
            $this->friendship_status = new FriendshipStatus($userData['friendship_status']);
        }
        if (isset($userData['byline'])) {
            $this->byline = $userData['byline'];
        }
        if (isset($userData['search_social_context'])) {
            $this->search_social_context = $userData['search_social_context'];
        }
        if (isset($userData['unseen_count'])) {
            $this->unseen_count = $userData['unseen_count'];
        }
        if (isset($userData['mutual_followers_count'])) {
            $this->mutual_followers_count = $userData['mutual_followers_count'];
        }
        if (isset($userData['follower_count'])) {
            $this->follower_count = $userData['follower_count'];
        }
        if (isset($userData['social_context'])) {
            $this->social_context = $userData['social_context'];
        }
        if (isset($userData['media_count'])) {
            $this->media_count = $userData['media_count'];
        }
        if (isset($userData['following_count'])) {
            $this->following_count = $userData['following_count'];
        }
        if (isset($userData['is_business'])) {
            $this->is_business = $userData['is_business'];
        }
        if (isset($userData['usertags_count'])) {
            $this->usertags_count = $userData['usertags_count'];
        }
        if (isset($userData['profile_context'])) {
            $this->profile_context = $userData['profile_context'];
        }
        if (isset($userData['biography'])) {
            $this->biography = $userData['biography'];
        }
        if (isset($userData['geo_media_count'])) {
            $this->geo_media_count = $userData['geo_media_count'];
        }
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getProfilePicUrl()
    {
        return $this->profile_pic_url;
    }

    public function getFullName()
    {
        return $this->full_name;
    }

    public function getUsernameId()
    {
        return $this->pk;
    }

    public function isVerified()
    {
        return $this->is_verified;
    }

    public function isPrivate()
    {
        return $this->is_private;
    }

    public function hasAnonymousProfilePicture()
    {
        return $this->has_anonymous_profile_picture;
    }

    public function isFavorite()
    {
        return $this->is_favorite;
    }

    public function getCoeffWeight()
    {
        return $this->coeff_weight;
    }

    /**
     * @return FriendshipStatus|null
     */
    public function getFriendshipStatus()
    {
        return $this->friendship_status;
    }

    /**
     * @param FriendshipStatus
     */
    public function setFriendshipStatus($friendship)
    {
        $this->friendship_status = $friendship;
    }

    public function getByline()
    {
        return $this->byline;
    }

    public function searchSocialContext()
    {
        return $this->search_social_context;
    }

    public function getUnseenCount()
    {
        return $this->unseen_count;
    }

    public function getMutualFollowersCount()
    {
        return $this->mutual_followers_count;
    }

    public function getFollowerCount()
    {
        return $this->follower_count;
    }

    public function getSocialContext()
    {
        return $this->social_context;
    }

    public function getMediaCount()
    {
        return $this->media_count;
    }

    public function getFollowingCount()
    {
        return $this->following_count;
    }

    public function isBusiness()
    {
        return $this->is_business;
    }

    public function getUsertagsCount()
    {
        return $this->usertags_count;
    }

    public function getProfileContext()
    {
        return $this->profile_context;
    }

    public function getBiography()
    {
        return $this->biography;
    }

    public function getGeoMediaCount()
    {
        return $this->geo_media_count;
    }
}
