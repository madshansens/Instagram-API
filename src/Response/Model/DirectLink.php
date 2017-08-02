<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method LinkContext getLinkContext()
 * @method string getText()
 * @method bool isLinkContext()
 * @method bool isText()
 * @method setLinkContext(LinkContext $value)
 * @method setText(string $value)
 */
class DirectLink extends AutoPropertyHandler
{
    /** @var string */
    public $text;
    /** @var LinkContext */
    public $link_context;
}
