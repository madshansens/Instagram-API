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

    public function getFriendshipStatus()
    {
        return $this->friendship_status;
    }
}
