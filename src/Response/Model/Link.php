<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getEnd()
 * @method string getId()
 * @method LinkContext getLinkContext()
 * @method mixed getStart()
 * @method mixed getText()
 * @method mixed getType()
 * @method bool isEnd()
 * @method bool isId()
 * @method bool isLinkContext()
 * @method bool isStart()
 * @method bool isText()
 * @method bool isType()
 * @method setEnd(mixed $value)
 * @method setId(string $value)
 * @method setLinkContext(LinkContext $value)
 * @method setStart(mixed $value)
 * @method setText(mixed $value)
 * @method setType(mixed $value)
 */
class Link extends AutoPropertyHandler
{
    public $start;
    public $end;
    /**
     * @var string
     */
    public $id;
    public $type;
    public $text;
    /**
     * @var LinkContext
     */
    public $link_context;
}
