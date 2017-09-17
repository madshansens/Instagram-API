<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

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
 * @method Caption getCaption()
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
 * @method setActorFbid(string $value)
 * @method setAdAction(mixed $value)
 * @method setAdHeaderStyle(mixed $value)
 * @method setAdId(string $value)
 * @method setAdLinkType(mixed $value)
 * @method setAdMetadata(AdMetadata[] $value)
 * @method setAlgorithm(mixed $value)
 * @method setAndroidLinks(AndroidLinks[] $value)
 * @method setAttribution(Attribution $value)
 * @method setBoostUnavailableReason(mixed $value)
 * @method setBoostedStatus(mixed $value)
 * @method setCanViewerSave(mixed $value)
 * @method setCaption(Caption $value)
 * @method setCaptionIsEdited(mixed $value)
 * @method setCaptionPosition(mixed $value)
 * @method setCarouselMedia(CarouselMedia[] $value)
 * @method setCarouselMediaType(mixed $value)
 * @method setChannel(Channel $value)
 * @method setClientCacheKey(mixed $value)
 * @method setCode(mixed $value)
 * @method setCollapseComments(mixed $value)
 * @method setCollectionId(string $value)
 * @method setCollectionName(mixed $value)
 * @method setCommentCount(mixed $value)
 * @method setCommentLikesEnabled(mixed $value)
 * @method setCommentThreadingEnabled(mixed $value)
 * @method setComments(Comment[] $value)
 * @method setCommentsDisabled(mixed $value)
 * @method setCoverMedia(CoverMedia $value)
 * @method setDeviceTimestamp(mixed $value)
 * @method setDrAdType(mixed $value)
 * @method setExpiringAt(mixed $value)
 * @method setExplore(Explore $value)
 * @method setExploreContext(mixed $value)
 * @method setExploreSourceToken(mixed $value)
 * @method setFilterType(mixed $value)
 * @method setForceOverlay(mixed $value)
 * @method setGating(Gating $value)
 * @method setHasAudio(mixed $value)
 * @method setHasLiked(mixed $value)
 * @method setHasMoreComments(mixed $value)
 * @method setHasViewerSaved(mixed $value)
 * @method setHideNuxText(mixed $value)
 * @method setITunesItem(mixed $value)
 * @method setIcon(mixed $value)
 * @method setId(string $value)
 * @method setImageVersions2(Image_Versions2 $value)
 * @method setImportedTakenAt(mixed $value)
 * @method setImpressionToken(mixed $value)
 * @method setInjected(Injected $value)
 * @method setIsDashEligible(mixed $value)
 * @method setIsNewSuggestion(mixed $value)
 * @method setIsReelMedia(mixed $value)
 * @method setIsSidecarChild(mixed $value)
 * @method setLargeUrls(mixed $value)
 * @method setLat(float $value)
 * @method setLeadGenFormId(string $value)
 * @method setLikeCount(mixed $value)
 * @method setLikers(User[] $value)
 * @method setLink(mixed $value)
 * @method setLinkHintText(mixed $value)
 * @method setLinkText(mixed $value)
 * @method setLng(float $value)
 * @method setLocation(Location $value)
 * @method setMaxNumVisiblePreviewComments(mixed $value)
 * @method setMedia(Media $value)
 * @method setMediaId(string $value)
 * @method setMediaIds(string[] $value)
 * @method setMediaInfos(mixed $value)
 * @method setMediaType(mixed $value)
 * @method setMultiAuthorReelNames(mixed $value)
 * @method setNextMaxId(string $value)
 * @method setNumberOfQualities(mixed $value)
 * @method setOrganicPostId(string $value)
 * @method setOrganicTrackingToken(mixed $value)
 * @method setOriginalHeight(mixed $value)
 * @method setOriginalWidth(mixed $value)
 * @method setOverlaySubtitle(mixed $value)
 * @method setOverlayText(mixed $value)
 * @method setOverlayTitle(mixed $value)
 * @method setPhotoOfYou(mixed $value)
 * @method setPk(string $value)
 * @method setPlaceholder(Placeholder $value)
 * @method setPlaybackDurationSecs(mixed $value)
 * @method setPreview(mixed $value)
 * @method setPreviewComments(Comment[] $value)
 * @method setReelMentions(mixed $value)
 * @method setReelShare(ReelShare $value)
 * @method setSavedCollectionIds(string[] $value)
 * @method setSocialContext(mixed $value)
 * @method setSponsorTags(User[] $value)
 * @method setStories(Stories $value)
 * @method setStoryCta(mixed $value)
 * @method setStoryHashtags(mixed $value)
 * @method setStoryLocations(StoryLocation[] $value)
 * @method setStoryPollVoterInfos(mixed $value)
 * @method setStoryPolls(mixed $value)
 * @method setSuggestedUsers(SuggestedUsers $value)
 * @method setTakenAt(mixed $value)
 * @method setThumbnailUrls(mixed $value)
 * @method setTopLikers(mixed $value)
 * @method setTotalViewerCount(mixed $value)
 * @method setUrlExpireAtSecs(mixed $value)
 * @method setUser(User $value)
 * @method setUsertags(Usertag $value)
 * @method setValue(mixed $value)
 * @method setVideoDashManifest(mixed $value)
 * @method setVideoDuration(mixed $value)
 * @method setVideoVersions(VideoVersions[] $value)
 * @method setViewCount(mixed $value)
 * @method setViewerCount(mixed $value)
 * @method setViewerCursor(mixed $value)
 * @method setViewers(User[] $value)
 * @method setVisibility(mixed $value)
 */
class Item extends AutoPropertyHandler
{
    const PHOTO = 1;
    const VIDEO = 2;
    const ALBUM = 8;

    /**
     * @var string
     */
    public $pk;
    /**
     * @var string
     */
    public $id;
    public $media_type;
    public $code;
    public $visibility;
    public $taken_at;
    public $device_timestamp;
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
    public $carousel_media_type;
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
    public $is_new_suggestion;
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
    public $is_dash_eligible;
    public $video_dash_manifest;
    public $number_of_qualities;
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
    /**
     * @var string
     */
    public $media_id;
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
    public $link_text;
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
    public $playback_duration_secs;
    public $url_expire_at_secs;
    public $is_sidecar_child;
    public $comment_threading_enabled;
    /**
     * @var string
     */
    public $collection_id;
    public $collection_name;
    /**
     * @var CoverMedia
     */
    public $cover_media;
    /**
     * @var string[]
     */
    public $saved_collection_ids;
    public $boosted_status;
    public $boost_unavailable_reason;
    /**
     * @var User[]
     */
    public $viewers;
    public $viewer_cursor;
    public $total_viewer_count;
    public $multi_author_reel_names;
    /**
     * @var ReelShare
     */
    public $reel_share;
    public $story_polls;
    /**
     * @var string
     */
    public $organic_post_id;
    /**
     * @var User[]
     */
    public $sponsor_tags;
    public $story_poll_voter_infos;
    public $imported_taken_at;
    /**
     * @var string
     */
    public $lead_gen_form_id;
    /**
     * @var string
     */
    public $ad_id;
    /**
     * @var string
     */
    public $actor_fbid;

    /**
     * Get the web URL for this media item.
     *
     * @return string
     */
    public function getItemUrl()
    {
        return 'https://www.instagram.com/p/'.$this->code.'/';
    }

    /**
     * Checks whether this media item is an advertisement.
     *
     * @return bool
     */
    public function isAd()
    {
        return $this->dr_ad_type !== null;
    }
}
