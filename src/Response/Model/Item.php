<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Item extends AutoPropertyHandler
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
     * @var Attribution
     */
    public $attribution;
    /**
     * @var Image_Versions2
     */
    public $image_versions2;
    public $original_width;
    public $original_height;
    public $view_count;
    public $viewer_count;
    public $organic_tracking_token;
    public $comment_count;
    public $has_more_comments;
    public $max_num_visible_preview_comments;
    /**
     * Preview of comments via feed replies.
     *
     * If "has_more_comments" is FALSE, then this has ALL of the comments.
     * Otherwise, you'll need to get all comments by querying the media.
     *
     * @var Comment[]
     */
    public $preview_comments;
    /**
     * Comments for the item.
     *
     * TODO: As of mid-2017, this field seems to no longer be used for timeline
     * feed items? They now use "preview_comments" instead. But we won't delete
     * it, since some other feed MAY use this property for ITS Item object.
     *
     * @var Comment[]
     */
    public $comments;
    public $comments_disabled;
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
     * @var CarouselMedia[]
     */
    public $carousel_media;
    /**
     * @var Caption
     */
    public $caption;
    public $caption_is_edited;
    public $photo_of_you;
    /**
     * @var VideoVersions[]
     */
    public $video_versions;
    public $has_audio;
    public $video_duration;
    /**
     * @var User
     */
    public $user;
    /**
     * @var User[]
     */
    public $likers;
    public $like_count;
    public $preview;
    public $has_liked;
    public $explore_context;
    public $explore_source_token;
    /**
     * @var Explore
     */
    public $explore;
    public $impression_token;
    /**
     * @var Usertag
     */
    public $usertags;
    public $media_or_ad;
    /**
     * @var Media
     */
    public $media;
    /**
     * @var Stories
     */
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
    /**
     * @var Channel
     */
    public $channel;
    /**
     * @var Gating
     */
    public $gating;
    public $story_hashtags;
    public $ad_action;
    public $link_text;
    public $is_dash_eligible;
    public $video_dash_manifest;
    /**
     * @var Injected
     */
    public $injected;
    /**
     * @var Placeholder
     */
    public $placeholder;
    public $social_context;
    public $icon;
    /**
     * @var string[]
     */
    public $media_ids;
    public $thumbnail_urls;
    public $large_urls;
    public $media_infos;
    public $value;
    public $collapse_comments;
    /**
     * @var AdMetadata[]
     */
    public $ad_metadata;
    public $link;
    public $link_hint_text;
    public $iTunesItem;
    public $ad_link_type;
    public $ad_header_style;
    public $dr_ad_type;
    /**
     * @var AndroidLinks[]
     */
    public $android_links;
    public $force_overlay;
    public $hide_nux_text;
    public $overlay_text;
    public $overlay_title;
    public $overlay_subtitle;

    public function setMediaOrAd(
        $params)
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
