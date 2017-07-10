<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getDisabled()
 * @method bool isDisabled()
 * @method setDisabled(mixed $value)
 */
class CommentCategoryFilterResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $disabled;
}
