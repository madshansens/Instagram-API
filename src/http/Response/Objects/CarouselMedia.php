<?php

namespace InstagramAPI;

class CarouselMedia extends Response
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
     * @var VideoVersions[]|null
     */
    public $video_versions;
    public $has_audio = false;
    public $video_duration = '';
    public $original_height;
    public $original_width;
    public $media_type;
}
