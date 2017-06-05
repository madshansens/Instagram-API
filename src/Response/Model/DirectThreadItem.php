<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method ActionLog getActionLog()
 * @method mixed getClientContext()
 * @method mixed getHideInThread()
 * @method string getItemId()
 * @method mixed getItemType()
 * @method DirectThreadItemMedia getMedia()
 * @method Item getMediaShare()
 * @method mixed getText()
 * @method mixed getTimestamp()
 * @method string getUserId()
 * @method bool isActionLog()
 * @method bool isClientContext()
 * @method bool isHideInThread()
 * @method bool isItemId()
 * @method bool isItemType()
 * @method bool isMedia()
 * @method bool isMediaShare()
 * @method bool isText()
 * @method bool isTimestamp()
 * @method bool isUserId()
 * @method setActionLog(ActionLog $value)
 * @method setClientContext(mixed $value)
 * @method setHideInThread(mixed $value)
 * @method setItemId(string $value)
 * @method setItemType(mixed $value)
 * @method setMedia(DirectThreadItemMedia $value)
 * @method setMediaShare(Item $value)
 * @method setText(mixed $value)
 * @method setTimestamp(mixed $value)
 * @method setUserId(string $value)
 */
class DirectThreadItem extends AutoPropertyHandler
{
    const PLACEHOLDER = 'placeholder';
    const TEXT = 'text';
    const HASHTAG = 'hashtag';
    const LOCATION = 'location';
    const PROFILE = 'profile';
    const MEDIA = 'media';
    const MEDIA_SHARE = 'media_share';
    const EXPIRING_MEDIA = 'raven_media';
    const LIKE = 'like';
    const ACTION_LOG = 'action_log';
    const REACTION = 'reaction';
    const REEL_SHARE = 'reel_share';
    const LINK = 'link';

    /**
     * @var string
     */
    public $item_id;
    public $item_type;
    public $text;
    /**
     * @var Item
     */
    public $media_share;
    /**
     * @var DirectThreadItemMedia
     */
    public $media;
    /**
     * @var string
     */
    public $user_id;
    public $timestamp;
    public $client_context;
    public $hide_in_thread;
    /**
     * @var ActionLog
     */
    public $action_log;
}
