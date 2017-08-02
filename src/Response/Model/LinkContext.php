<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getLinkImageUrl()
 * @method mixed getLinkSummary()
 * @method mixed getLinkTitle()
 * @method mixed getLinkUrl()
 * @method bool isLinkImageUrl()
 * @method bool isLinkSummary()
 * @method bool isLinkTitle()
 * @method bool isLinkUrl()
 * @method setLinkImageUrl(mixed $value)
 * @method setLinkSummary(mixed $value)
 * @method setLinkTitle(mixed $value)
 * @method setLinkUrl(mixed $value)
 */
class LinkContext extends AutoPropertyHandler
{
    public $link_url;
    public $link_title;
    public $link_summary;
    public $link_image_url;
}
