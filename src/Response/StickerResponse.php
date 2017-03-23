<?php

namespace InstagramAPI\Response;

class StickerResponse extends \InstagramAPI\Response
{
    public $version;
    /**
     * @var Model\StaticStickers[]
     */
    public $static_stickers;
}
