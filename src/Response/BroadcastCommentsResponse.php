<?php

namespace InstagramAPI\Response;

class BroadcastCommentsResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\Comment[]
     */
    public $comments;
    public $comment_count;
    public $live_seconds_per_comment;
    public $has_more_headload_comments;
    public $is_first_fetch;
    public $comment_likes_enabled;
    /**
     * @var Model\Comment
     */
    public $pinned_comment;
    public $system_comments;
    public $has_more_comments;
    public $caption_is_edited;
    public $caption;
    public $comment_muted;
}
