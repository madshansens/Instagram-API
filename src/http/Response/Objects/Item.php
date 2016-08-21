<?php

namespace InstagramAPI;

class Item
{
    const PHOTO = 1;
    const VIDEO = 2;

    protected $taken_at;
    protected $pk;
    protected $id;
    protected $device_timestamp;
    protected $media_type;
    protected $code;
    protected $client_cache_key;
    protected $filter_type;
    protected $image_versions2;
    protected $original_width;
    protected $original_height;
    protected $view_count = 0;
    protected $organic_tracking_token;
    protected $has_more_comments;
    protected $max_num_visible_preview_comments;
    protected $comments;
    protected $comment_count = 0;
    protected $caption = null;
    protected $caption_is_edited;
    protected $photo_of_you;
    protected $video_versions = '';
    protected $has_audio = false;
    protected $video_duration = '';
    protected $user;
    protected $likers = '';
    protected $like_count = 0;
    protected $preview = '';
    protected $has_liked = false;
    protected $explore_context = '';
    protected $explore_source_token = '';
    protected $explore = '';
    protected $impression_token = '';
    protected $usertags = null;


    public function __construct($item)
    {
        $this->taken_at = $item['taken_at'];
        $this->pk = $item['pk'];
        $this->id = $item['id'];
        $this->device_timestamp = $item['device_timestamp'];
        $this->media_type = $item['media_type'];
        $this->code = $item['code'];
        $this->client_cache_key = $item['client_cache_key'];
        $this->filter_type = $item['filter_type'];
        $images = [];
        foreach($item['image_versions2']['candidates'] as $image) {
            $images[] = new HdProfilePicUrlInfo($image);
        }
        $this->image_versions2 = $images;
        $this->original_width = $item['original_width'];
        if (array_key_exists('view_count', $item)) {
            $this->view_count = $item['view_count'];
        }
        $this->organic_tracking_token = $item['organic_tracking_token'];
        $this->has_more_comments = $item['has_more_comments'];
        $this->max_num_visible_preview_comments = $item['max_num_visible_preview_comments'];
        $comments = [];
        foreach($item['comments'] as $comment) {
            $comments[] = new Comment($comment);
        }
        $this->comments = $comments;
        $this->comment_count = $item['comment_count'];
        $this->caption = $item['caption'];
        $this->caption_is_edited = $item['caption_is_edited'];
        $this->photo_of_you = $item['photo_of_you'];
        if (array_key_exists('video_versions', $item)) {
            $videos = [];
            foreach ($item['video_versions'] as $video) {
                $videos[] = new VideoVersions($video);
            }
            $this->video_versions = $videos;
        }
        if (array_key_exists('has_audio', $item)) {
            $this->has_audio = $item['has_audio'];
        }
        if (array_key_exists('video_duration', $item)) {
            $this->video_duration = $item['video_duration'];
        }
        $this->user = new User($item['user']);
        if (array_key_exists('likers', $item)) {
            $this->likers = $item['likers'];
        }
        if (array_key_exists('like_count', $item)) {
            $this->like_count = $item['like_count'];
        }
        if (array_key_exists('preview', $item)) {
            $this->preview = $item['preview'];
        }
        if (array_key_exists('has_liked', $item)) {
            $this->has_liked = $item['has_liked'];
        }
        if (array_key_exists('explore_context', $item)) {
            $this->explore_context = $item['explore_context'];
        }
        if (array_key_exists('explore_source_token', $item)) {
            $this->explore_source_token = $item['explore_source_token'];
        }
        if (array_key_exists('explore', $item)) {
            $this->explore = new Explore($item['explore']);
        }
        if (array_key_exists('impression_token', $item)) {
            $this->impression_token = $item['impression_token'];
        }
        if (array_key_exists('usertags', $item)) {
            $this->usertags = $item['usertags'];
        }
    }

    public function getTakenAt()
    {
        return $this->taken_at;
    }

    public function getUsernameId()
    {
        return $this->pk;
    }

    public function getMediaId()
    {
        return $this->id;
    }

    public function getDeviceTimestamp()
    {
        return $this->device_timestamp;
    }

    public function isVideo()
    {
        return $this->media_type == self::VIDEO;
    }

    public function isPhoto()
    {
        return $this->media_type == self::PHOTO;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getClientCacheKey()
    {
        return $this->client_cache_key;
    }

    public function getFilterType()
    {
        return $this->filter_type;
    }

    public function getImageVersions()
    {
        return $this->image_versions2;
    }

    public function getOriginalWidth()
    {
        return $this->original_width;
    }

    public function getOriginalHeight()
    {
        return $this->original_height;
    }

    public function getViewCount()
    {
        return $this->view_count;
    }

    public function getOrganicTrackingToken()
    {
        return $this->organic_tracking_token;
    }

    public function hasMoreComments()
    {
        return $this->has_more_comments;
    }

    public function getMaxNumVisiblePreviewComments()
    {
        return $this->max_num_visible_preview_comments;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function getCommentCount()
    {
        return $this->comment_count;
    }

    public function getCaption()
    {
        return $this->caption;
    }

    public function isCaptionEdited()
    {
        return $this->caption_is_edited;
    }

    public function isPhotoOfYou()
    {
        return $this->photo_of_you;
    }

    public function getVideoVersions()
    {
        return $this->video_versions;
    }

    public function hasAudio()
    {
        return $this->has_audio;
    }

    public function getVideoDuration()
    {
        return $this->video_duration;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getMediaLikers()
    {
        return $this->likers;
    }

    public function getLikeCount()
    {
        return $this->like_count;
    }

    public function getPreview()
    {
        return $this->preview;
    }

    public function hasLiked()
    {
        return $this->has_liked;
    }

    public function getExploreContext()
    {
        return $this->explore_context;
    }

    public function getExploreSourceToken()
    {
        return $this->explore_source_token;
    }

    public function getExplore()
    {
        return $this->explore;
    }

    public function getImpressionToken()
    {
        return $this->impression_token;
    }

    public function getUsertags()
    {
        return $this->usertags;
    }
}
