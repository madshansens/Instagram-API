<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getAdLinkType()
 * @method Item getMediaOrAd()
 * @method SuggestedUsers getSuggestedUsers()
 * @method bool isAdLinkType()
 * @method bool isMediaOrAd()
 * @method bool isSuggestedUsers()
 * @method setAdLinkType(mixed $value)
 * @method setMediaOrAd(Item $value)
 * @method setSuggestedUsers(SuggestedUsers $value)
 */
class FeedItem extends AutoPropertyHandler
{
    /**
     * @var Item
     */
    public $media_or_ad;
    /**
     * @var SuggestedUsers
     */
    public $suggested_users;
    public $ad_link_type;
}
