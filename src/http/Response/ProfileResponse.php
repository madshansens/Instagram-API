<?php

namespace InstagramAPI;

class ProfileResponse extends Response
{
    protected $username;
    protected $phone_number;
    protected $has_anonymous_profile_picture;
    protected $hd_profile_pic_versions;
    protected $gender;
    protected $birthday;
    protected $needs_email_confirm;
    protected $national_number;
    protected $profile_pic_url;
    protected $profile_pic_id;
    protected $biography;
    protected $full_name;
    protected $pk;
    protected $country_code;
    protected $hd_profile_pic_url_info;
    protected $email;
    protected $is_private;
    protected $external_url;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            foreach ($response['user'] as $p => $v) {
                $this->$p = $v;
            }
            $this->hd_profile_pic_url_info = new HdProfilePicUrlInfo($this->hd_profile_pic_url_info);
            if (isset($this->hd_profile_pic_versions)) {
                $profile_pics_vers = [];
                foreach ($this->hd_profile_pic_versions as $profile_pic) {
                    $profile_pics_vers[] = new HdProfilePicUrlInfo($profile_pic);
                }
                $this->hd_profile_pic_versions = $profile_pics_vers;
            }
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    public function hasAnonymousProfilePicture()
    {
        return $this->has_anonymous_profile_picture;
    }

    /**
     * @return HdProfilePicUrlInfo[]
     */
    public function getHdProfilePicVersions()
    {
        return $this->hd_profile_pic_versions;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function getBirthday()
    {
        return $this->birthday;
    }

    public function needsEmailConfirm()
    {
        return $this->needs_email_confirm;
    }

    public function getNationalNumber()
    {
        return $this->national_number;
    }

    public function getProfilePicUrl()
    {
        return $this->profile_pic_url;
    }

    public function getProfilePicId()
    {
        return $this->profile_pic_id;
    }

    public function getBiography()
    {
        return $this->biography;
    }

    public function getFullName()
    {
        return $this->full_name;
    }

    public function getUsernameId()
    {
        return $this->pk;
    }

    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @return HdProfilePicUrlInfo
     */
    public function getHdProfilePicUrlInfo()
    {
        return $this->hd_profile_pic_url_info;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function isPrivate()
    {
        return $this->is_private;
    }

    public function getExternalUrl()
    {
        return $this->external_url;
    }
}
