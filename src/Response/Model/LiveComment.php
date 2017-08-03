<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method Comment getComment()
 * @method mixed getEvent()
 * @method mixed getOffset()
 * @method bool isComment()
 * @method bool isEvent()
 * @method bool isOffset()
 * @method setComment(Comment $value)
 * @method setEvent(mixed $value)
 * @method setOffset(mixed $value)
 */
class LiveComment extends AutoPropertyHandler
{
    /**
     * @var Comment
     */
    public $comment;
    public $offset;
    public $event;
}
