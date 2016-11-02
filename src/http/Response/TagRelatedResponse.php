<?php

namespace InstagramAPI;

class TagRelatedResponse extends Response
{
    protected $related;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $tags = [];
            foreach ($response['related'] as $item) {
                array_push($tags, $item['name']);
            }
            $this->related = $tags;
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    /**
     * @return string[]
     */
    public function getTags()
    {
        return $this->related;
    }
}
