<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * @method string getActorFbid()
 * @method mixed getAdAction()
 * @method mixed getAdHeaderStyle()
 * @method string getAdId()
 * @method mixed getAdLinkType()
 * @method AdMetadata[] getAdMetadata()
 * @method mixed getAlgorithm()
 * @method AndroidLinks[] getAndroidLinks()
 * @method Attribution getAttribution()
 * @method mixed getBoostUnavailableReason()
 * @method mixed getBoostedStatus()
 * @method mixed getCanViewerSave()
 * @method mixed getCaption()
 * @method mixed getCaptionIsEdited()
 * @method mixed getCaptionPosition()
 * @method CarouselMedia[] getCarouselMedia()
 * @method mixed getCarouselMediaType()
 * @method Channel getChannel()
 * @method mixed getClientCacheKey()
 * @method mixed getCode()
 * @method mixed getCollapseComments()
 * @method string getCollectionId()
 * @method mixed getCollectionName()
 * @method mixed getCommentCount()
 * @method mixed getCommentLikesEnabled()
 * @method mixed getCommentThreadingEnabled()
 * @method mixed getCommentingDisabledForViewer()
 * @method Comment[] getComments()
 * @method mixed getCommentsDisabled()
 * @method CoverMedia getCoverMedia()
 * @method mixed getDeviceTimestamp()
 * @method mixed getDrAdType()
 * @method mixed getExpiringAt()
 * @method Explore getExplore()
 * @method mixed getExploreContext()
 * @method mixed getExploreSourceToken()
 * @method mixed getFilterType()
 * @method mixed getForceOverlay()
 * @method Gating getGating()
 * @method mixed getHasAudio()
 * @method mixed getHasLiked()
 * @method mixed getHasMoreComments()
 * @method mixed getHasViewerSaved()
 * @method mixed getHideNuxText()
 * @method mixed getITunesItem()
 * @method mixed getIcon()
 * @method string getId()
 * @method Image_Versions2 getImageVersions2()
 * @method mixed getImportedTakenAt()
 * @method mixed getImpressionToken()
 * @method Injected getInjected()
 * @method mixed getIsAd4ad()
 * @method mixed getIsDashEligible()
 * @method mixed getIsNewSuggestion()
 * @method mixed getIsReelMedia()
 * @method mixed getIsSidecarChild()
 * @method mixed getLargeUrls()
 * @method float getLat()
 * @method string getLeadGenFormId()
 * @method mixed getLikeCount()
 * @method User[] getLikers()
 * @method mixed getLink()
 * @method mixed getLinkHintText()
 * @method mixed getLinkText()
 * @method float getLng()
 * @method Location getLocation()
 * @method mixed getMaxNumVisiblePreviewComments()
 * @method Media getMedia()
 * @method string getMediaId()
 * @method string[] getMediaIds()
 * @method mixed getMediaInfos()
 * @method mixed getMediaType()
 * @method mixed getMultiAuthorReelNames()
 * @method string getNextMaxId()
 * @method mixed getNumberOfQualities()
 * @method string getOrganicPostId()
 * @method mixed getOrganicTrackingToken()
 * @method mixed getOriginalHeight()
 * @method mixed getOriginalWidth()
 * @method mixed getOverlaySubtitle()
 * @method mixed getOverlayText()
 * @method mixed getOverlayTitle()
 * @method mixed getPhotoOfYou()
 * @method string getPk()
 * @method Placeholder getPlaceholder()
 * @method mixed getPlaybackDurationSecs()
 * @method mixed getPreview()
 * @method Comment[] getPreviewComments()
 * @method mixed getReelMentions()
 * @method ReelShare getReelShare()
 * @method string[] getSavedCollectionIds()
 * @method mixed getSocialContext()
 * @method User[] getSponsorTags()
 * @method Stories getStories()
 * @method mixed getStoryCta()
 * @method mixed getStoryEvents()
 * @method mixed getStoryFeedMedia()
 * @method mixed getStoryHashtags()
 * @method StoryLocation[] getStoryLocations()
 * @method mixed getStoryPollVoterInfos()
 * @method mixed getStoryPolls()
 * @method SuggestedUsers getSuggestedUsers()
 * @method mixed getTakenAt()
 * @method mixed getThumbnailUrls()
 * @method mixed getTopLikers()
 * @method mixed getTotalViewerCount()
 * @method mixed getUrlExpireAtSecs()
 * @method User getUser()
 * @method Usertag getUsertags()
 * @method mixed getValue()
 * @method mixed getVideoDashManifest()
 * @method mixed getVideoDuration()
 * @method VideoVersions[] getVideoVersions()
 * @method mixed getViewCount()
 * @method mixed getViewerCount()
 * @method mixed getViewerCursor()
 * @method User[] getViewers()
 * @method mixed getVisibility()
 * @method bool isActorFbid()
 * @method bool isAdAction()
 * @method bool isAdHeaderStyle()
 * @method bool isAdId()
 * @method bool isAdLinkType()
 * @method bool isAdMetadata()
 * @method bool isAlgorithm()
 * @method bool isAndroidLinks()
 * @method bool isAttribution()
 * @method bool isBoostUnavailableReason()
 * @method bool isBoostedStatus()
 * @method bool isCanViewerSave()
 * @method bool isCaption()
 * @method bool isCaptionIsEdited()
 * @method bool isCaptionPosition()
 * @method bool isCarouselMedia()
 * @method bool isCarouselMediaType()
 * @method bool isChannel()
 * @method bool isClientCacheKey()
 * @method bool isCode()
 * @method bool isCollapseComments()
 * @method bool isCollectionId()
 * @method bool isCollectionName()
 * @method bool isCommentCount()
 * @method bool isCommentLikesEnabled()
 * @method bool isCommentThreadingEnabled()
 * @method bool isCommentingDisabledForViewer()
 * @method bool isComments()
 * @method bool isCommentsDisabled()
 * @method bool isCoverMedia()
 * @method bool isDeviceTimestamp()
 * @method bool isDrAdType()
 * @method bool isExpiringAt()
 * @method bool isExplore()
 * @method bool isExploreContext()
 * @method bool isExploreSourceToken()
 * @method bool isFilterType()
 * @method bool isForceOverlay()
 * @method bool isGating()
 * @method bool isHasAudio()
 * @method bool isHasLiked()
 * @method bool isHasMoreComments()
 * @method bool isHasViewerSaved()
 * @method bool isHideNuxText()
 * @method bool isITunesItem()
 * @method bool isIcon()
 * @method bool isId()
 * @method bool isImageVersions2()
 * @method bool isImportedTakenAt()
 * @method bool isImpressionToken()
 * @method bool isInjected()
 * @method bool isIsAd4ad()
 * @method bool isIsDashEligible()
 * @method bool isIsNewSuggestion()
 * @method bool isIsReelMedia()
 * @method bool isIsSidecarChild()
 * @method bool isLargeUrls()
 * @method bool isLat()
 * @method bool isLeadGenFormId()
 * @method bool isLikeCount()
 * @method bool isLikers()
 * @method bool isLink()
 * @method bool isLinkHintText()
 * @method bool isLinkText()
 * @method bool isLng()
 * @method bool isLocation()
 * @method bool isMaxNumVisiblePreviewComments()
 * @method bool isMedia()
 * @method bool isMediaId()
 * @method bool isMediaIds()
 * @method bool isMediaInfos()
 * @method bool isMediaType()
 * @method bool isMultiAuthorReelNames()
 * @method bool isNextMaxId()
 * @method bool isNumberOfQualities()
 * @method bool isOrganicPostId()
 * @method bool isOrganicTrackingToken()
 * @method bool isOriginalHeight()
 * @method bool isOriginalWidth()
 * @method bool isOverlaySubtitle()
 * @method bool isOverlayText()
 * @method bool isOverlayTitle()
 * @method bool isPhotoOfYou()
 * @method bool isPk()
 * @method bool isPlaceholder()
 * @method bool isPlaybackDurationSecs()
 * @method bool isPreview()
 * @method bool isPreviewComments()
 * @method bool isReelMentions()
 * @method bool isReelShare()
 * @method bool isSavedCollectionIds()
 * @method bool isSocialContext()
 * @method bool isSponsorTags()
 * @method bool isStories()
 * @method bool isStoryCta()
 * @method bool isStoryEvents()
 * @method bool isStoryFeedMedia()
 * @method bool isStoryHashtags()
 * @method bool isStoryLocations()
 * @method bool isStoryPollVoterInfos()
 * @method bool isStoryPolls()
 * @method bool isSuggestedUsers()
 * @method bool isTakenAt()
 * @method bool isThumbnailUrls()
 * @method bool isTopLikers()
 * @method bool isTotalViewerCount()
 * @method bool isUrlExpireAtSecs()
 * @method bool isUser()
 * @method bool isUsertags()
 * @method bool isValue()
 * @method bool isVideoDashManifest()
 * @method bool isVideoDuration()
 * @method bool isVideoVersions()
 * @method bool isViewCount()
 * @method bool isViewerCount()
 * @method bool isViewerCursor()
 * @method bool isViewers()
 * @method bool isVisibility()
 * @method $this setActorFbid(string $value)
 * @method $this setAdAction(mixed $value)
 * @method $this setAdHeaderStyle(mixed $value)
 * @method $this setAdId(string $value)
 * @method $this setAdLinkType(mixed $value)
 * @method $this setAdMetadata(AdMetadata[] $value)
 * @method $this setAlgorithm(mixed $value)
 * @method $this setAndroidLinks(AndroidLinks[] $value)
 * @method $this setAttribution(Attribution $value)
 * @method $this setBoostUnavailableReason(mixed $value)
 * @method $this setBoostedStatus(mixed $value)
 * @method $this setCanViewerSave(mixed $value)
 * @method $this setCaption(mixed $value)
 * @method $this setCaptionIsEdited(mixed $value)
 * @method $this setCaptionPosition(mixed $value)
 * @method $this setCarouselMedia(CarouselMedia[] $value)
 * @method $this setCarouselMediaType(mixed $value)
 * @method $this setChannel(Channel $value)
 * @method $this setClientCacheKey(mixed $value)
 * @method $this setCode(mixed $value)
 * @method $this setCollapseComments(mixed $value)
 * @method $this setCollectionId(string $value)
 * @method $this setCollectionName(mixed $value)
 * @method $this setCommentCount(mixed $value)
 * @method $this setCommentLikesEnabled(mixed $value)
 * @method $this setCommentThreadingEnabled(mixed $value)
 * @method $this setCommentingDisabledForViewer(mixed $value)
 * @method $this setComments(Comment[] $value)
 * @method $this setCommentsDisabled(mixed $value)
 * @method $this setCoverMedia(CoverMedia $value)
 * @method $this setDeviceTimestamp(mixed $value)
 * @method $this setDrAdType(mixed $value)
 * @method $this setExpiringAt(mixed $value)
 * @method $this setExplore(Explore $value)
 * @method $this setExploreContext(mixed $value)
 * @method $this setExploreSourceToken(mixed $value)
 * @method $this setFilterType(mixed $value)
 * @method $this setForceOverlay(mixed $value)
 * @method $this setGating(Gating $value)
 * @method $this setHasAudio(mixed $value)
 * @method $this setHasLiked(mixed $value)
 * @method $this setHasMoreComments(mixed $value)
 * @method $this setHasViewerSaved(mixed $value)
 * @method $this setHideNuxText(mixed $value)
 * @method $this setITunesItem(mixed $value)
 * @method $this setIcon(mixed $value)
 * @method $this setId(string $value)
 * @method $this setImageVersions2(Image_Versions2 $value)
 * @method $this setImportedTakenAt(mixed $value)
 * @method $this setImpressionToken(mixed $value)
 * @method $this setInjected(Injected $value)
 * @method $this setIsAd4ad(mixed $value)
 * @method $this setIsDashEligible(mixed $value)
 * @method $this setIsNewSuggestion(mixed $value)
 * @method $this setIsReelMedia(mixed $value)
 * @method $this setIsSidecarChild(mixed $value)
 * @method $this setLargeUrls(mixed $value)
 * @method $this setLat(float $value)
 * @method $this setLeadGenFormId(string $value)
 * @method $this setLikeCount(mixed $value)
 * @method $this setLikers(User[] $value)
 * @method $this setLink(mixed $value)
 * @method $this setLinkHintText(mixed $value)
 * @method $this setLinkText(mixed $value)
 * @method $this setLng(float $value)
 * @method $this setLocation(Location $value)
 * @method $this setMaxNumVisiblePreviewComments(mixed $value)
 * @method $this setMedia(Media $value)
 * @method $this setMediaId(string $value)
 * @method $this setMediaIds(string[] $value)
 * @method $this setMediaInfos(mixed $value)
 * @method $this setMediaType(mixed $value)
 * @method $this setMultiAuthorReelNames(mixed $value)
 * @method $this setNextMaxId(string $value)
 * @method $this setNumberOfQualities(mixed $value)
 * @method $this setOrganicPostId(string $value)
 * @method $this setOrganicTrackingToken(mixed $value)
 * @method $this setOriginalHeight(mixed $value)
 * @method $this setOriginalWidth(mixed $value)
 * @method $this setOverlaySubtitle(mixed $value)
 * @method $this setOverlayText(mixed $value)
 * @method $this setOverlayTitle(mixed $value)
 * @method $this setPhotoOfYou(mixed $value)
 * @method $this setPk(string $value)
 * @method $this setPlaceholder(Placeholder $value)
 * @method $this setPlaybackDurationSecs(mixed $value)
 * @method $this setPreview(mixed $value)
 * @method $this setPreviewComments(Comment[] $value)
 * @method $this setReelMentions(mixed $value)
 * @method $this setReelShare(ReelShare $value)
 * @method $this setSavedCollectionIds(string[] $value)
 * @method $this setSocialContext(mixed $value)
 * @method $this setSponsorTags(User[] $value)
 * @method $this setStories(Stories $value)
 * @method $this setStoryCta(mixed $value)
 * @method $this setStoryEvents(mixed $value)
 * @method $this setStoryFeedMedia(mixed $value)
 * @method $this setStoryHashtags(mixed $value)
 * @method $this setStoryLocations(StoryLocation[] $value)
 * @method $this setStoryPollVoterInfos(mixed $value)
 * @method $this setStoryPolls(mixed $value)
 * @method $this setSuggestedUsers(SuggestedUsers $value)
 * @method $this setTakenAt(mixed $value)
 * @method $this setThumbnailUrls(mixed $value)
 * @method $this setTopLikers(mixed $value)
 * @method $this setTotalViewerCount(mixed $value)
 * @method $this setUrlExpireAtSecs(mixed $value)
 * @method $this setUser(User $value)
 * @method $this setUsertags(Usertag $value)
 * @method $this setValue(mixed $value)
 * @method $this setVideoDashManifest(mixed $value)
 * @method $this setVideoDuration(mixed $value)
 * @method $this setVideoVersions(VideoVersions[] $value)
 * @method $this setViewCount(mixed $value)
 * @method $this setViewerCount(mixed $value)
 * @method $this setViewerCursor(mixed $value)
 * @method $this setViewers(User[] $value)
 * @method $this setVisibility(mixed $value)
 * @method $this unsetActorFbid()
 * @method $this unsetAdAction()
 * @method $this unsetAdHeaderStyle()
 * @method $this unsetAdId()
 * @method $this unsetAdLinkType()
 * @method $this unsetAdMetadata()
 * @method $this unsetAlgorithm()
 * @method $this unsetAndroidLinks()
 * @method $this unsetAttribution()
 * @method $this unsetBoostUnavailableReason()
 * @method $this unsetBoostedStatus()
 * @method $this unsetCanViewerSave()
 * @method $this unsetCaption()
 * @method $this unsetCaptionIsEdited()
 * @method $this unsetCaptionPosition()
 * @method $this unsetCarouselMedia()
 * @method $this unsetCarouselMediaType()
 * @method $this unsetChannel()
 * @method $this unsetClientCacheKey()
 * @method $this unsetCode()
 * @method $this unsetCollapseComments()
 * @method $this unsetCollectionId()
 * @method $this unsetCollectionName()
 * @method $this unsetCommentCount()
 * @method $this unsetCommentLikesEnabled()
 * @method $this unsetCommentThreadingEnabled()
 * @method $this unsetCommentingDisabledForViewer()
 * @method $this unsetComments()
 * @method $this unsetCommentsDisabled()
 * @method $this unsetCoverMedia()
 * @method $this unsetDeviceTimestamp()
 * @method $this unsetDrAdType()
 * @method $this unsetExpiringAt()
 * @method $this unsetExplore()
 * @method $this unsetExploreContext()
 * @method $this unsetExploreSourceToken()
 * @method $this unsetFilterType()
 * @method $this unsetForceOverlay()
 * @method $this unsetGating()
 * @method $this unsetHasAudio()
 * @method $this unsetHasLiked()
 * @method $this unsetHasMoreComments()
 * @method $this unsetHasViewerSaved()
 * @method $this unsetHideNuxText()
 * @method $this unsetITunesItem()
 * @method $this unsetIcon()
 * @method $this unsetId()
 * @method $this unsetImageVersions2()
 * @method $this unsetImportedTakenAt()
 * @method $this unsetImpressionToken()
 * @method $this unsetInjected()
 * @method $this unsetIsAd4ad()
 * @method $this unsetIsDashEligible()
 * @method $this unsetIsNewSuggestion()
 * @method $this unsetIsReelMedia()
 * @method $this unsetIsSidecarChild()
 * @method $this unsetLargeUrls()
 * @method $this unsetLat()
 * @method $this unsetLeadGenFormId()
 * @method $this unsetLikeCount()
 * @method $this unsetLikers()
 * @method $this unsetLink()
 * @method $this unsetLinkHintText()
 * @method $this unsetLinkText()
 * @method $this unsetLng()
 * @method $this unsetLocation()
 * @method $this unsetMaxNumVisiblePreviewComments()
 * @method $this unsetMedia()
 * @method $this unsetMediaId()
 * @method $this unsetMediaIds()
 * @method $this unsetMediaInfos()
 * @method $this unsetMediaType()
 * @method $this unsetMultiAuthorReelNames()
 * @method $this unsetNextMaxId()
 * @method $this unsetNumberOfQualities()
 * @method $this unsetOrganicPostId()
 * @method $this unsetOrganicTrackingToken()
 * @method $this unsetOriginalHeight()
 * @method $this unsetOriginalWidth()
 * @method $this unsetOverlaySubtitle()
 * @method $this unsetOverlayText()
 * @method $this unsetOverlayTitle()
 * @method $this unsetPhotoOfYou()
 * @method $this unsetPk()
 * @method $this unsetPlaceholder()
 * @method $this unsetPlaybackDurationSecs()
 * @method $this unsetPreview()
 * @method $this unsetPreviewComments()
 * @method $this unsetReelMentions()
 * @method $this unsetReelShare()
 * @method $this unsetSavedCollectionIds()
 * @method $this unsetSocialContext()
 * @method $this unsetSponsorTags()
 * @method $this unsetStories()
 * @method $this unsetStoryCta()
 * @method $this unsetStoryEvents()
 * @method $this unsetStoryFeedMedia()
 * @method $this unsetStoryHashtags()
 * @method $this unsetStoryLocations()
 * @method $this unsetStoryPollVoterInfos()
 * @method $this unsetStoryPolls()
 * @method $this unsetSuggestedUsers()
 * @method $this unsetTakenAt()
 * @method $this unsetThumbnailUrls()
 * @method $this unsetTopLikers()
 * @method $this unsetTotalViewerCount()
 * @method $this unsetUrlExpireAtSecs()
 * @method $this unsetUser()
 * @method $this unsetUsertags()
 * @method $this unsetValue()
 * @method $this unsetVideoDashManifest()
 * @method $this unsetVideoDuration()
 * @method $this unsetVideoVersions()
 * @method $this unsetViewCount()
 * @method $this unsetViewerCount()
 * @method $this unsetViewerCursor()
 * @method $this unsetViewers()
 * @method $this unsetVisibility()
 */
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
        'caption'                          => '',
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
        'story_events'                     => '',
        'story_feed_media'                 => '',
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
