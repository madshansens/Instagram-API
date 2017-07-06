<?php

namespace InstagramAPI\Realtime\Client\Mqtt;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method string getPayload()
 * @method string getSubtopic()
 * @method string getTopic()
 * @method bool isPayload()
 * @method bool isSubtopic()
 * @method bool isTopic()
 * @method setPayload(string $value)
 * @method setSubtopic(string $value)
 * @method setTopic(string $value)
 */
class GraphQl extends AutoPropertyHandler
{
    /** @var string */
    public $topic;
    /** @var string */
    public $subtopic;
    /** @var string */
    public $payload;
}
