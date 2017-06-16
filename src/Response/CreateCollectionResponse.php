<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method string getCollectionId()
 * @method mixed getCollectionName()
 * @method bool isCollectionId()
 * @method bool isCollectionName()
 * @method setCollectionId(string $value)
 * @method setCollectionName(mixed $value)
 */
class CreateCollectionResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var string
     */
    public $collection_id;
    public $collection_name;
}
