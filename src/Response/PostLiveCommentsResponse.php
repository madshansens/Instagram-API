<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method Model\LiveComment[] getComments()
 * @method mixed getEndingOffset()
 * @method mixed getNextFetchOffset()
 * @method Model\LiveComment[] getPinnedComments()
 * @method mixed getStartingOffset()
 * @method bool isComments()
 * @method bool isEndingOffset()
 * @method bool isNextFetchOffset()
 * @method bool isPinnedComments()
 * @method bool isStartingOffset()
 * @method setComments(Model\LiveComment[] $value)
 * @method setEndingOffset(mixed $value)
 * @method setNextFetchOffset(mixed $value)
 * @method setPinnedComments(Model\LiveComment[] $value)
 * @method setStartingOffset(mixed $value)
 */
class PostLiveCommentsResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $starting_offset;
    public $ending_offset;
    public $next_fetch_offset;
    /**
     * @var Model\LiveComment[]
     */
    public $comments;
    /**
     * @var Model\LiveComment[]
     */
    public $pinned_comments;
}
