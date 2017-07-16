<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getRankToken()
 * @method Model\Suggested[] getSuggested()
 * @method bool isRankToken()
 * @method bool isSuggested()
 * @method setRankToken(mixed $value)
 * @method setSuggested(Model\Suggested[] $value)
 */
class SuggestedUsersFacebookResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\Suggested[]
     */
    public $suggested;
    public $rank_token;
}
