<?php

namespace InstagramAPI;

class MediaCommentsResponse extends Response
{
    protected $has_more_comments = false;
    protected $caption_is_edited = false;
    protected $comments = [];
    protected $next_max_id = null;
    protected $comment_count = 0;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            if (isset($response['comments'])) {
                foreach ($response['comments'] as $comment) {
                    $this->comments[] = new Comment($comment);
                }
            }
            $this->has_more_comments = $response['has_more_comments'];
            $this->next_max_id = isset($response['next_max_id']) ? $response['next_max_id'] : null;

            if (isset($response['caption_is_edited'])) {
                $this->caption_is_edited = $response['caption_is_edited'];
            }
            if (isset($response['caption_is_edited'])) {
                $this->comment_count = $response['comment_count'];
            }

            $this->setFullResponse($response);
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

    /**
     * @return Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    public function isCaptionEdited()
    {
        return $this->caption_is_edited;
    }

    public function getCommentsCounter()
    {
        return $this->comment_count;
    }
}
