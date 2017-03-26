<?php

namespace InstagramAPI\Response;

class BroadcastInfoResponse extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $id;
    public $broadcast_message;
    public $organic_tracking_token;
    public $published_time;
    public $broadcast_status;
    /**
     * @var string
     */
    public $media_id;
    /**
     * @var Model\User
     */
    public $broadcast_owner;
}
