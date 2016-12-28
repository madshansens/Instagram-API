<?php

namespace InstagramAPI;

class Item extends Response
{
    const PHOTO = 1;
    const VIDEO = 2;

    public $taken_at;
    public $pk;
    public $id;
    public $device_timestamp;
    public $media_type;
    public $code;
    public $client_cache_key;
    public $filter_type;
    /**
     * @var Image_Versions2
     */
    public $image_versions2;
    public $original_width;
    public $original_height;
    public $view_count = 0;
    public $organic_tracking_token;
    public $has_more_comments;
    public $max_num_visible_preview_comments;
    public $preview_comments;
    public $reel_mentions;
    public $story_cta;
    public $caption_position;
    public $expiring_at;
    public $is_reel_media;
    public $next_max_id = null;

    /**
     * @var Comment[]
     */
    public $comments;
    public $comment_count = 0;
    /**
     * @var Caption|null
     */
    public $caption = null;
    public $caption_is_edited;
    public $photo_of_you;
    /**
     * @var VideoVersions[]|null
     */
    public $video_versions = null;
    public $has_audio = false;
    public $video_duration = '';
    /**
     * @var User
     */
    public $user;
    /**
     * @var User[]
     */
    public $likers = '';
    public $like_count = 0;
    public $preview = '';
    public $has_liked = false;
    public $explore_context = '';
    public $explore_source_token = '';
    /**
     * @var Explore|null
     */
    public $explore = '';
    public $impression_token = '';
    /**
     * @var Usertag|null
     */
    public $usertags = null;
    public $media_or_ad;
    /**
     * @var Media
     */
    public $media;
    public $stories;
    public $top_likers;

    public function setMediaOrAd($params)
    {
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
    }

    public function getItemUrl()
    {
        return 'https://www.instagram.com/p/'.$this->getCode().'/';
    }
}

class Image_Versions2 extends Response
{
    /**
     * @var HdProfilePicUrlInfo[]
     */
    public $candidates;
}
