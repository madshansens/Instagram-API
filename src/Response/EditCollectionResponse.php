<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class EditCollectionResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var string
     */
    public $collection_id;
    public $collection_name;
}
