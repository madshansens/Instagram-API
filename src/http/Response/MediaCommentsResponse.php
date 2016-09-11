<?php

namespace InstagramAPI;

class MediaCommentsResponse extends Response
{
    protected $has_more_comments = false;
    protected $comments = [];
    protected $next_max_id = null;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            if (isset($response['comments'])) {
                foreach ($response['comments'] as $comment) {
                    $this->comments[] = new Comment($comment);
                }    
            }
            $this->has_more_comments = $response['has_more_comments'];
            $this->next_max_id = $response['next_max_id'];
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getNextMaxId()
    {
        return $this->next_max_id;
    }
    public function hasMoreComments()
    {
        return $this->has_more_comments;
    }
    public function getComments()
    {
        return $this->comments;
    }
}
