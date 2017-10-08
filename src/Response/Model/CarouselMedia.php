<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

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
 * @method $this setAdAction(mixed $value)
 * @method $this setAdLinkType(mixed $value)
 * @method $this setAdMetadata(AdMetadata[] $value)
 * @method $this setAndroidLinks(AndroidLinks[] $value)
 * @method $this setCarouselParentId(string $value)
 * @method $this setForceOverlay(mixed $value)
 * @method $this setHasAudio(mixed $value)
 * @method $this setHeadline(Headline $value)
 * @method $this setHideNuxText(mixed $value)
 * @method $this setId(string $value)
 * @method $this setImageVersions2(Image_Versions2 $value)
 * @method $this setLink(mixed $value)
 * @method $this setLinkHintText(mixed $value)
 * @method $this setLinkText(mixed $value)
 * @method $this setMediaType(mixed $value)
 * @method $this setOriginalHeight(mixed $value)
 * @method $this setOriginalWidth(mixed $value)
 * @method $this setOverlaySubtitle(mixed $value)
 * @method $this setOverlayText(mixed $value)
 * @method $this setOverlayTitle(mixed $value)
 * @method $this setPk(string $value)
 * @method $this setPreview(mixed $value)
 * @method $this setUsertags(Usertag $value)
 * @method $this setVideoDuration(mixed $value)
 * @method $this setVideoSubtitlesUri(mixed $value)
 * @method $this setVideoVersions(VideoVersions[] $value)
 * @method $this unsetAdAction()
 * @method $this unsetAdLinkType()
 * @method $this unsetAdMetadata()
 * @method $this unsetAndroidLinks()
 * @method $this unsetCarouselParentId()
 * @method $this unsetForceOverlay()
 * @method $this unsetHasAudio()
 * @method $this unsetHeadline()
 * @method $this unsetHideNuxText()
 * @method $this unsetId()
 * @method $this unsetImageVersions2()
 * @method $this unsetLink()
 * @method $this unsetLinkHintText()
 * @method $this unsetLinkText()
 * @method $this unsetMediaType()
 * @method $this unsetOriginalHeight()
 * @method $this unsetOriginalWidth()
 * @method $this unsetOverlaySubtitle()
 * @method $this unsetOverlayText()
 * @method $this unsetOverlayTitle()
 * @method $this unsetPk()
 * @method $this unsetPreview()
 * @method $this unsetUsertags()
 * @method $this unsetVideoDuration()
 * @method $this unsetVideoSubtitlesUri()
 * @method $this unsetVideoVersions()
 */
class CarouselMedia extends AutoPropertyMapper
{
    const PHOTO = 1;
    const VIDEO = 2;

    const JSON_PROPERTY_MAP = [
        'pk'                  => 'string',
        'id'                  => 'string',
        'carousel_parent_id'  => 'string',
        'image_versions2'     => 'Image_Versions2',
        'video_versions'      => 'VideoVersions[]',
        'has_audio'           => '',
        'video_duration'      => '',
        'video_subtitles_uri' => '',
        'original_height'     => '',
        'original_width'      => '',
        'media_type'          => '',
        'usertags'            => 'Usertag',
        'preview'             => '',
        'headline'            => 'Headline',
        'link'                => '',
        'link_text'           => '',
        'link_hint_text'      => '',
        'android_links'       => 'AndroidLinks[]',
        'ad_metadata'         => 'AdMetadata[]',
        'ad_action'           => '',
        'ad_link_type'        => '',
        'force_overlay'       => '',
        'hide_nux_text'       => '',
        'overlay_text'        => '',
        'overlay_title'       => '',
        'overlay_subtitle'    => '',
    ];
}
