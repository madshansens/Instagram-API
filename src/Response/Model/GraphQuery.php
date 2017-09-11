<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getError()
 * @method QueryResponse getResponse()
 * @method bool isError()
 * @method bool isResponse()
 * @method setError(mixed $value)
 * @method setResponse(QueryResponse $value)
 */
class GraphQuery extends AutoPropertyHandler
{
    /**
     * @var QueryResponse
     */
    public $response;
    public $error;
}
