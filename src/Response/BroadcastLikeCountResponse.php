<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getLikeTs()
 * @method Model\User[] getLikers()
 * @method mixed getLikes()
 * @method bool isLikeTs()
 * @method bool isLikers()
 * @method bool isLikes()
 * @method setLikeTs(mixed $value)
 * @method setLikers(Model\User[] $value)
 * @method setLikes(mixed $value)
 */
class BroadcastLikeCountResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $like_ts;
    public $likes;
    /**
     * @var Model\User[]
     */
    public $likers;
}
