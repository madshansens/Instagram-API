<?php

namespace InstagramAPI;

class ThreadItemMedia extends Response
{
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
