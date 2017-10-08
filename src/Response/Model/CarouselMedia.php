<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

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
