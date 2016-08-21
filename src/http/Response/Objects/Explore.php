<?php

namespace InstagramAPI;

class Explore
{
    protected $explanation;
    protected $actor_id;
    protected $source_token;

    public function __construct($data)
    {
        $this->explanation = $data['explanation'];
        $this->actor_id = $data['actor_id'];
        $this->source_token = $data['source_token'];
    }

    public function getExplanation()
    {
        return $this->explanation;
    }

    public function getActorId()
    {
        return $this->actor_id;
    }

    public function getSourceToken()
    {
        return $this->source_token;
    }
}
