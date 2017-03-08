<?php

namespace InstagramAPI;

class Broadcast extends Response
{
    /**
     * @var BroadcastOwner
     */
    public $broadcast_owner;
    public $broadcast_status;
    public $cover_frame_url;
    public $published_time;
    public $broadcast_message;
    public $muted;
    public $media_id;
    /**
     * @var string
     */
    public $id;
    public $rtmp_playback_url;
    public $dash_abr_playback_url;
    public $dash_playback_url;
    public $ranked_position;
    public $organic_tracking_token;
    public $seen_ranked_position;
    public $viewer_count;
}
