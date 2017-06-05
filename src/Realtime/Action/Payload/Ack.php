<?php

namespace InstagramAPI\Realtime\Action\Payload;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getActivityStatus()
 * @method mixed getClientContext()
 * @method mixed getIndicateActivityTs()
 * @method mixed getTtl()
 * @method bool isActivityStatus()
 * @method bool isClientContext()
 * @method bool isIndicateActivityTs()
 * @method bool isTtl()
 * @method setActivityStatus(mixed $value)
 * @method setClientContext(mixed $value)
 * @method setIndicateActivityTs(mixed $value)
 * @method setTtl(mixed $value)
 */
class Ack extends AutoPropertyHandler
{
    public $activity_status;
    public $client_context;
    public $indicate_activity_ts;
    public $ttl;
}
