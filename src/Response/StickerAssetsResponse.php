<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class StickerAssetsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'version'         => '',
        'static_stickers' => 'Model\StaticStickers[]',
    ];
}
