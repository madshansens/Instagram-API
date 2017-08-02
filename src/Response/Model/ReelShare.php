<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getBroadcasts()
 * @method Item getMedia()
 * @method mixed getStickerVersion()
 * @method mixed getStoryRankingToken()
 * @method mixed getText()
 * @method Item[] getTray()
 * @method mixed getType()
 * @method bool isBroadcasts()
 * @method bool isMedia()
 * @method bool isStickerVersion()
 * @method bool isStoryRankingToken()
 * @method bool isText()
 * @method bool isTray()
 * @method bool isType()
 * @method setBroadcasts(mixed $value)
 * @method setMedia(Item $value)
 * @method setStickerVersion(mixed $value)
 * @method setStoryRankingToken(mixed $value)
 * @method setText(mixed $value)
 * @method setTray(Item[] $value)
 * @method setType(mixed $value)
 */
class ReelShare extends AutoPropertyHandler
{
    /**
     * @var Item[]
     */
    public $tray;
    public $story_ranking_token;
    public $broadcasts;
    public $sticker_version;
    public $text;
    public $type;
    /**
     * @var Item
     */
    public $media;
}
