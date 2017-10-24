<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Link.
 *
 * @method mixed getEnd()
 * @method string getId()
 * @method LinkContext getLinkContext()
 * @method mixed getStart()
 * @method string getText()
 * @method mixed getType()
 * @method bool isEnd()
 * @method bool isId()
 * @method bool isLinkContext()
 * @method bool isStart()
 * @method bool isText()
 * @method bool isType()
 * @method $this setEnd(mixed $value)
 * @method $this setId(string $value)
 * @method $this setLinkContext(LinkContext $value)
 * @method $this setStart(mixed $value)
 * @method $this setText(string $value)
 * @method $this setType(mixed $value)
 * @method $this unsetEnd()
 * @method $this unsetId()
 * @method $this unsetLinkContext()
 * @method $this unsetStart()
 * @method $this unsetText()
 * @method $this unsetType()
 */
class Link extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'start'        => '',
        'end'          => '',
        'id'           => 'string',
        'type'         => '',
        'text'         => 'string',
        'link_context' => 'LinkContext',
    ];
}
