<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method string getClientContext()
 * @method string getClientRequestId()
 * @method string getItemId()
 * @method string getMessage()
 * @method string getThreadId()
 * @method string getTimestamp()
 * @method bool isClientContext()
 * @method bool isClientRequestId()
 * @method bool isItemId()
 * @method bool isMessage()
 * @method bool isThreadId()
 * @method bool isTimestamp()
 * @method setClientContext(string $value)
 * @method setClientRequestId(string $value)
 * @method setItemId(string $value)
 * @method setMessage(string $value)
 * @method setThreadId(string $value)
 * @method setTimestamp(string $value)
 */
class DirectSendItemPayload extends AutoPropertyHandler
{
    /** @var string */
    public $client_request_id;
    /** @var string */
    public $client_context;
    /** @var string */
    public $message;
    /** @var string */
    public $item_id;
    /** @var string */
    public $timestamp;
    /** @var string */
    public $thread_id;
}
