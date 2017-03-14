<?php

namespace InstagramAPI;

class ThreadItemMedia extends Response
{
    const PHOTO = 1;
    const VIDEO = 2;

    public $media_type;
    /**
     * @var Image_Versions2
     */
    public $image_versions2;
    /**
     * @var VideoVersions[]
     */
    public $video_versions;
}
