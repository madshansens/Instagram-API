<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class BlockedReels extends AutoPropertyHandler
{
    // NOTE: We must use full paths to all model objects in THIS class, because
    // "BlockedReelsResponse" re-uses this object and JSONMapper won't be
    // able to find these sub-objects if the paths aren't absolute!

    /**
     * @var \InstagramAPI\Response\Model\User[]
     */
    public $users;
    public $page_size;
    public $big_list;
}
