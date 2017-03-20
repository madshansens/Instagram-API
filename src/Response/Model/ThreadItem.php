<?php

namespace InstagramAPI;

class ThreadItem extends Response
{
    /**
     * @var string
     */
    public $item_id;
    public $item_type;
    public $text;
    /**
     * @var Item
     */
    public $media_share;
    /**
     * @var ThreadItemMedia
     */
    public $media;
    /**
     * @var string
     */
    public $user_id;
    public $timestamp;
    public $client_context;
}
