<?php

namespace InstagramAPI\Response;

use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class BlockedReelsResponse extends Model\BlockedReels implements ResponseInterface
{
    use ResponseTrait;

    // NOTE: This is a special response object which extends
    // Model\BlockedReels to inherit all of its properties!

    /**
     * @var string
     */
    public $next_max_id;
}
