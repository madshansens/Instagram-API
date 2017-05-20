<?php

namespace InstagramAPI\Realtime\Event;

use InstagramAPI\Realtime\Client;

class Patch extends \InstagramAPI\Realtime\Event
{
    public $id;
    /**
     * @var \InstagramAPI\Realtime\Event\Patch\Op[]
     */
    public $data;
    public $sequence;
    public $lazy;
    public $topic;

    /** {@inheritdoc} */
    public function handle(
        Client $client)
    {
        $client->onUpdateSequence($this->topic, $this->sequence);
        foreach ($this->data as $op) {
            $op->handle($client);
        }
    }
}
