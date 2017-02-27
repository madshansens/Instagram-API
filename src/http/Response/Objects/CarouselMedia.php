<?php

namespace InstagramAPI;

class CarouselMedia extends Response
{
    public $pk;
    public $id;
    public $carousel_parent_id;
    /**
     * @var Image_Versions2
     */
    public $image_versions2;
    /**
     * @var VideoVersions[]|null
     */
    public $video_versions = null;
    public $has_audio = false;
    public $video_duration = '';
    public $original_height;
    public $original_width;
    public $media_type;
}
