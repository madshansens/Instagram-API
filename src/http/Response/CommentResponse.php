<?php

namespace InstagramAPI;

class CommentResponse extends Response
{
    protected $comments;
    protected $has_more_comments;
    protected $next_max_id;
    

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {

            $this->next_max_id = $response['next_max_id'];
            $comments = [];
            foreach ($response['comments'] as $comment) {
                $comments[] = new Comment($comment);
            }
            $this->comments = $comments;
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function getNextMaxId()
    {
        return $this->next_max_id;
    }

    public function has_more_comments()
    {
        return $this->has_more_comments;
    }

}

