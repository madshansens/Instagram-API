<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getHasMore()
 * @method Model\LocationItem[] getItems()
 * @method mixed getRankToken()
 * @method bool isHasMore()
 * @method bool isItems()
 * @method bool isRankToken()
 * @method setHasMore(mixed $value)
 * @method setItems(Model\LocationItem[] $value)
 * @method setRankToken(mixed $value)
 */
class FBLocationResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $has_more;
    /**
     * @var Model\LocationItem[]
     */
    public $items;
    public $rank_token;
}
