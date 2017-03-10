<?php

namespace InstagramAPI;

class Item extends Response
{
    const PHOTO = 1;
    const VIDEO = 2;
    const ALBUM = 8;

    public $taken_at;
    /**
     * @var string
     */
    public $pk;
    /**
     * @var string
     */
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
    /**
     * @var string
     */
    public $next_max_id;
    /**
     * @var CarouselMedia[]|null
     */
    public $carousel_media;
    /**
     * @var Comment[]
     */
    public $comments;
    public $comment_count = 0;
    /**
     * @var Caption|null
     */
    public $caption;
    public $caption_is_edited;
    public $photo_of_you;
    /**
     * @var VideoVersions[]|null
     */
    public $video_versions;
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
    /**
     * @var string[]
     */
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
    public $usertags;
    public $media_or_ad;
    /**
     * @var Media
     */
    public $media;
    public $stories;
    public $top_likers;
    /**
     * @var SuggestedUsers
     */
    public $suggested_users;
    public $comment_likes_enabled;
    public $can_viewer_save;
    public $has_viewer_saved;
    /**
     * @var Location
     */
    public $location;
    /**
     * @var float
     */
    public $lat;
    /**
     * @var float
     */
    public $lng;
    /**
     * @var StoryLocation[]
     */
    public $story_locations;
    public $algorithm;

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
