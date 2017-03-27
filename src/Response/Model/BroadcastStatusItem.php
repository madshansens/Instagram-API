<?php

namespace InstagramAPI\Response\Model;

class BroadcastStatusItem extends \InstagramAPI\Response
{
    public $broadcast_status;
    public $has_reduced_visibility;
    public $cover_frame_url;
    public $viewer_count;
    /**
     * @var string
     */
    public $id;
}
