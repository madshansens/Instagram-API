<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getAdAction()
 * @method mixed getAdLinkType()
 * @method AdMetadata[] getAdMetadata()
 * @method AndroidLinks[] getAndroidLinks()
 * @method string getCarouselParentId()
 * @method mixed getForceOverlay()
 * @method mixed getHasAudio()
 * @method Headline getHeadline()
 * @method mixed getHideNuxText()
 * @method string getId()
 * @method Image_Versions2 getImageVersions2()
 * @method mixed getLink()
 * @method mixed getLinkHintText()
 * @method mixed getLinkText()
 * @method mixed getMediaType()
 * @method mixed getOriginalHeight()
 * @method mixed getOriginalWidth()
 * @method mixed getOverlaySubtitle()
 * @method mixed getOverlayText()
 * @method mixed getOverlayTitle()
 * @method string getPk()
 * @method mixed getPreview()
 * @method Usertag getUsertags()
 * @method mixed getVideoDuration()
 * @method mixed getVideoSubtitlesUri()
 * @method VideoVersions[] getVideoVersions()
 * @method bool isAdAction()
 * @method bool isAdLinkType()
 * @method bool isAdMetadata()
 * @method bool isAndroidLinks()
 * @method bool isCarouselParentId()
 * @method bool isForceOverlay()
 * @method bool isHasAudio()
 * @method bool isHeadline()
 * @method bool isHideNuxText()
 * @method bool isId()
 * @method bool isImageVersions2()
 * @method bool isLink()
 * @method bool isLinkHintText()
 * @method bool isLinkText()
 * @method bool isMediaType()
 * @method bool isOriginalHeight()
 * @method bool isOriginalWidth()
 * @method bool isOverlaySubtitle()
 * @method bool isOverlayText()
 * @method bool isOverlayTitle()
 * @method bool isPk()
 * @method bool isPreview()
 * @method bool isUsertags()
 * @method bool isVideoDuration()
 * @method bool isVideoSubtitlesUri()
 * @method bool isVideoVersions()
 * @method setAdAction(mixed $value)
 * @method setAdLinkType(mixed $value)
 * @method setAdMetadata(AdMetadata[] $value)
 * @method setAndroidLinks(AndroidLinks[] $value)
 * @method setCarouselParentId(string $value)
 * @method setForceOverlay(mixed $value)
 * @method setHasAudio(mixed $value)
 * @method setHeadline(Headline $value)
 * @method setHideNuxText(mixed $value)
 * @method setId(string $value)
 * @method setImageVersions2(Image_Versions2 $value)
 * @method setLink(mixed $value)
 * @method setLinkHintText(mixed $value)
 * @method setLinkText(mixed $value)
 * @method setMediaType(mixed $value)
 * @method setOriginalHeight(mixed $value)
 * @method setOriginalWidth(mixed $value)
 * @method setOverlaySubtitle(mixed $value)
 * @method setOverlayText(mixed $value)
 * @method setOverlayTitle(mixed $value)
 * @method setPk(string $value)
 * @method setPreview(mixed $value)
 * @method setUsertags(Usertag $value)
 * @method setVideoDuration(mixed $value)
 * @method setVideoSubtitlesUri(mixed $value)
 * @method setVideoVersions(VideoVersions[] $value)
 */
class CarouselMedia extends AutoPropertyHandler
{
    const PHOTO = 1;
    const VIDEO = 2;

    /**
     * @var string
     */
    public $pk;
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $carousel_parent_id;
    /**
     * @var Image_Versions2
     */
    public $image_versions2;
    /**
     * @var VideoVersions[]
     */
    public $video_versions;
    public $has_audio;
    public $video_duration;
    public $video_subtitles_uri;
    public $original_height;
    public $original_width;
    public $media_type;
    /**
     * @var Usertag
     */
    public $usertags;
    public $preview;
    /**
     * @var Headline
     */
    public $headline;
    public $link;
    public $link_text;
    public $link_hint_text;
    /**
     * @var AndroidLinks[]
     */
    public $android_links;
    /**
     * @var AdMetadata[]
     */
    public $ad_metadata;
    public $ad_action;
    public $ad_link_type;
    public $force_overlay;
    public $hide_nux_text;
    public $overlay_text;
    public $overlay_title;
    public $overlay_subtitle;
}
