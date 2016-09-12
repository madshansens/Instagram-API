<?php

namespace InstagramAPI;

class _Message
{
    protected $key;
    protected $time;

    public function __construct($data)
    {
        $this->key = $data['key'];
        $this->time = $data['time'];
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getTime()
    {
        return $this->time;
    }
}
