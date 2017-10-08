<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Item extends AutoPropertyMapper
{
    const PHOTO = 1;
    const VIDEO = 2;
    const ALBUM = 8;

    const JSON_PROPERTY_MAP = [
        'pk'                               => 'string',
        'id'                               => 'string',
        'media_type'                       => '',
        'code'                             => '',
        'visibility'                       => '',
        'taken_at'                         => '',
        'device_timestamp'                 => '',
        'client_cache_key'                 => '',
        'filter_type'                      => '',
        'attribution'                      => 'Attribution',
        'image_versions2'                  => 'Image_Versions2',
        'original_width'                   => '',
        'original_height'                  => '',
        'view_count'                       => '',
        'viewer_count'                     => '',
        'organic_tracking_token'           => '',
        'comment_count'                    => '',
        'has_more_comments'                => '',
        'max_num_visible_preview_comments' => '',
        /*
         * Preview of comments via feed replies.
         *
         * If "has_more_comments" is FALSE, then this has ALL of the comments.
         * Otherwise, you'll need to get all comments by querying the media.
         */
        'preview_comments'                 => 'Comment[]',
        /*
         * Comments for the item.
         *
         * TODO: As of mid-2017, this field seems to no longer be used for
         * timeline feed items? They now use "preview_comments" instead. But we
         * won't delete it, since some other feed MAY use this property for ITS
         * Item object.
         */
        'comments'                         => 'Comment[]',
        'comments_disabled'                => '',
        'reel_mentions'                    => '',
        'story_cta'                        => '',
        'caption_position'                 => '',
        'expiring_at'                      => '',
        'is_reel_media'                    => '',
        'next_max_id'                      => 'string',
        'carousel_media'                   => 'CarouselMedia[]',
        'carousel_media_type'              => '',
        'caption'                          => 'Caption',
        'caption_is_edited'                => '',
        'photo_of_you'                     => '',
        'video_versions'                   => 'VideoVersions[]',
        'has_audio'                        => '',
        'video_duration'                   => '',
        'user'                             => 'User',
        'likers'                           => 'User[]',
        'like_count'                       => '',
        'preview'                          => '',
        'has_liked'                        => '',
        'explore_context'                  => '',
        'explore_source_token'             => '',
        'explore'                          => 'Explore',
        'impression_token'                 => '',
        'usertags'                         => 'Usertag',
        'media'                            => 'Media',
        'stories'                          => 'Stories',
        'top_likers'                       => '',
        'suggested_users'                  => 'SuggestedUsers',
        'is_new_suggestion'                => '',
        'comment_likes_enabled'            => '',
        'can_viewer_save'                  => '',
        'has_viewer_saved'                 => '',
        'location'                         => 'Location',
        'lat'                              => 'float',
        'lng'                              => 'float',
        'story_locations'                  => 'StoryLocation[]',
        'algorithm'                        => '',
        'channel'                          => 'Channel',
        'gating'                           => 'Gating',
        'story_hashtags'                   => '',
        'ad_action'                        => '',
        'is_dash_eligible'                 => '',
        'video_dash_manifest'              => '',
        'number_of_qualities'              => '',
        'injected'                         => 'Injected',
        'placeholder'                      => 'Placeholder',
        'social_context'                   => '',
        'icon'                             => '',
        'media_ids'                        => 'string[]',
        'media_id'                         => 'string',
        'thumbnail_urls'                   => '',
        'large_urls'                       => '',
        'media_infos'                      => '',
        'value'                            => '',
        'collapse_comments'                => '',
        'ad_metadata'                      => 'AdMetadata[]',
        'link'                             => '',
        'link_text'                        => '',
        'link_hint_text'                   => '',
        'iTunesItem'                       => '',
        'ad_link_type'                     => '',
        'ad_header_style'                  => '',
        'dr_ad_type'                       => '',
        'android_links'                    => 'AndroidLinks[]',
        'force_overlay'                    => '',
        'hide_nux_text'                    => '',
        'overlay_text'                     => '',
        'overlay_title'                    => '',
        'overlay_subtitle'                 => '',
        'playback_duration_secs'           => '',
        'url_expire_at_secs'               => '',
        'is_sidecar_child'                 => '',
        'comment_threading_enabled'        => '',
        'collection_id'                    => 'string',
        'collection_name'                  => '',
        'cover_media'                      => 'CoverMedia',
        'saved_collection_ids'             => 'string[]',
        'boosted_status'                   => '',
        'boost_unavailable_reason'         => '',
        'viewers'                          => 'User[]',
        'viewer_cursor'                    => '',
        'total_viewer_count'               => '',
        'multi_author_reel_names'          => '',
        'reel_share'                       => 'ReelShare',
        'story_polls'                      => '',
        'organic_post_id'                  => 'string',
        'sponsor_tags'                     => 'User[]',
        'story_poll_voter_infos'           => '',
        'imported_taken_at'                => '',
        'lead_gen_form_id'                 => 'string',
        'ad_id'                            => 'string',
        'actor_fbid'                       => 'string',
        'is_ad4ad'                         => '',
        'commenting_disabled_for_viewer'   => '',
    ];

    /**
     * Get the web URL for this media item.
     *
     * @return string
     */
    public function getItemUrl()
    {
        return sprintf('https://www.instagram.com/p/%s/', $this->_getProperty('code'));
    }

    /**
     * Checks whether this media item is an advertisement.
     *
     * @return bool
     */
    public function isAd()
    {
        return $this->_getProperty('dr_ad_type') !== null;
    }
}
