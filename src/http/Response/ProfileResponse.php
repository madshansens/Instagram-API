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
            foreach($response['user'] as $p=>$v){
                $this->$p=$v;
            }
            $this->hd_profile_pic_url_info = new HdProfilePicUrlInfo($this->hd_profile_pic_url_info);
            if (isset($this->hd_profile_pic_versions)) {
                $profile_pics_vers = [];
                foreach($this->hd_profile_pic_versions as $profile_pic) {
                    $profile_pics_vers[] = new HdProfilePicUrlInfo($profile_pic);
                }
                $this->hd_profile_pic_versions = $profile_pics_vers;
            }

        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getProperty($property){
        return $this->$property;
    }
}

