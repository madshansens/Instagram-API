<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method ShadowInstagramUser getShadowInstagramUser()
 * @method bool isShadowInstagramUser()
 * @method setShadowInstagramUser(ShadowInstagramUser $value)
 */
class QueryResponse extends AutoPropertyHandler
{
    /**
     * @var ShadowInstagramUser
     */
    public $shadow_instagram_user;
}
