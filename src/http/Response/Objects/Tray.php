<?php

namespace InstagramAPI;

class Tray extends Response
{
    public $id;
    /**
     * @var Item[]
     */
    public $items;

    public $user;
    public $can_reply;
    public $expiring_at;
    public $seen_ranked_position;
    public $seen;
    public $latest_reel_media;
    public $ranked_position;
    public $is_nux;
    public $muted;
}
