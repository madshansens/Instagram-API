<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getCanViewerSave()
 * @method mixed getCaption()
 * @method mixed getCaptionIsEdited()
 * @method mixed getCaptionPosition()
 * @method mixed getClientCacheKey()
 * @method mixed getCode()
 * @method mixed getCommentCount()
 * @method mixed getCommentLikesEnabled()
 * @method mixed getCommentThreadingEnabled()
 * @method mixed getDeviceTimestamp()
 * @method mixed getExpiringAt()
 * @method mixed getFilterType()
 * @method mixed getHasLiked()
 * @method mixed getHasMoreComments()
 * @method string getId()
 * @method mixed getImage()
 * @method Image_Versions2 getImageVersions2()
 * @method mixed getIsReelMedia()
 * @method mixed getLikeCount()
 * @method mixed getLikers()
 * @method mixed getMaxNumVisiblePreviewComments()
 * @method mixed getMediaType()
 * @method mixed getOrganicTrackingToken()
 * @method mixed getOriginalHeight()
 * @method mixed getOriginalWidth()
 * @method mixed getPhotoOfYou()
 * @method string getPk()
 * @method mixed getPreviewComments()
 * @method mixed getTakenAt()
 * @method User getUser()
 * @method bool isCanViewerSave()
 * @method bool isCaption()
 * @method bool isCaptionIsEdited()
 * @method bool isCaptionPosition()
 * @method bool isClientCacheKey()
 * @method bool isCode()
 * @method bool isCommentCount()
 * @method bool isCommentLikesEnabled()
 * @method bool isCommentThreadingEnabled()
 * @method bool isDeviceTimestamp()
 * @method bool isExpiringAt()
 * @method bool isFilterType()
 * @method bool isHasLiked()
 * @method bool isHasMoreComments()
 * @method bool isId()
 * @method bool isImage()
 * @method bool isImageVersions2()
 * @method bool isIsReelMedia()
 * @method bool isLikeCount()
 * @method bool isLikers()
 * @method bool isMaxNumVisiblePreviewComments()
 * @method bool isMediaType()
 * @method bool isOrganicTrackingToken()
 * @method bool isOriginalHeight()
 * @method bool isOriginalWidth()
 * @method bool isPhotoOfYou()
 * @method bool isPk()
 * @method bool isPreviewComments()
 * @method bool isTakenAt()
 * @method bool isUser()
 * @method setCanViewerSave(mixed $value)
 * @method setCaption(mixed $value)
 * @method setCaptionIsEdited(mixed $value)
 * @method setCaptionPosition(mixed $value)
 * @method setClientCacheKey(mixed $value)
 * @method setCode(mixed $value)
 * @method setCommentCount(mixed $value)
 * @method setCommentLikesEnabled(mixed $value)
 * @method setCommentThreadingEnabled(mixed $value)
 * @method setDeviceTimestamp(mixed $value)
 * @method setExpiringAt(mixed $value)
 * @method setFilterType(mixed $value)
 * @method setHasLiked(mixed $value)
 * @method setHasMoreComments(mixed $value)
 * @method setId(string $value)
 * @method setImage(mixed $value)
 * @method setImageVersions2(Image_Versions2 $value)
 * @method setIsReelMedia(mixed $value)
 * @method setLikeCount(mixed $value)
 * @method setLikers(mixed $value)
 * @method setMaxNumVisiblePreviewComments(mixed $value)
 * @method setMediaType(mixed $value)
 * @method setOrganicTrackingToken(mixed $value)
 * @method setOriginalHeight(mixed $value)
 * @method setOriginalWidth(mixed $value)
 * @method setPhotoOfYou(mixed $value)
 * @method setPk(string $value)
 * @method setPreviewComments(mixed $value)
 * @method setTakenAt(mixed $value)
 * @method setUser(User $value)
 */
class Media extends AutoPropertyHandler
{
    public $image;
    /**
     * @var string
     */
    public $pk;
    /**
     * @var string
     */
    public $id;
    /**
     * @var User
     */
    public $user;
    public $expiring_at;
    public $taken_at;
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
    public $caption_position;
    public $is_reel_media;
    public $caption;
    public $caption_is_edited;
    public $like_count;
    public $has_liked;
    public $likers;
    public $comment_likes_enabled;
    public $comment_threading_enabled;
    public $has_more_comments;
    public $max_num_visible_preview_comments;
    public $preview_comments;
    public $comment_count;
    public $photo_of_you;
    public $can_viewer_save;
    public $organic_tracking_token;
}
