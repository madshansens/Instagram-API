<?php

namespace InstagramAPI;

class Item extends Response
{
    const PHOTO = 1;
    const VIDEO = 2;

    var $taken_at;
    var $pk;
    var $id;
    var $device_timestamp;
    var $media_type;
    var $code;
    var $client_cache_key;
    var $filter_type;
    /**
    * @var Image_Versions2
    */
    var $image_versions2;
    var $original_width;
    var $original_height;
    var $view_count = 0;
    var $organic_tracking_token;
    var $has_more_comments;
    var $max_num_visible_preview_comments;
    var $preview_comments;
    var $reel_mentions;
    var $story_cta;
    var $caption_position;
    var $expiring_at;
    var $is_reel_media;
    var $next_max_id = null;

    /**
    * @var Comment[]
    */
    var $comments;
    var $comment_count = 0;
    /**
    * @var Caption|null
    */
    var $caption = null;
    var $caption_is_edited;
    var $photo_of_you;
    /**
    * @var VideoVersions[]|null
    */
    var $video_versions = null;
    var $has_audio = false;
    var $video_duration = '';
    /**
    * @var User
    */
    var $user;
    /**
    * @var User[]
    */
    var $likers = '';
    var $like_count = 0;
    var $preview = '';
    var $has_liked = false;
    var $explore_context = '';
    var $explore_source_token = '';
    /**
    * @var Explore|string
    */
    var $explore = '';
    var $impression_token = '';
    /**
    * @var Usertag|null
    */
    var $usertags = null;
    var $media_or_ad;
    
    var $media;
    var $stories;
    var $top_likers;
    
    function setMediaOrAd($params) {
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
    }

}


class Image_Versions2 extends Response {
    /**
    * @var HdProfilePicUrlInfo[]
    */
    var $candidates;
}
