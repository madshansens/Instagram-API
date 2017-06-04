<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class StickerAssetsResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $version;
    /**
     * @var Model\StaticStickers[]
     */
    public $static_stickers;
}
