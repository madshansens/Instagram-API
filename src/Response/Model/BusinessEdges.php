<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getCursor()
 * @method BusinessNode getNode()
 * @method bool isCursor()
 * @method bool isNode()
 * @method setCursor(mixed $value)
 * @method setNode(BusinessNode $value)
 */
class BusinessEdges extends AutoPropertyHandler
{
    /**
     * @var BusinessNode
     */
    public $node;
    public $cursor;
}
