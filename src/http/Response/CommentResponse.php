<?php

namespace InstagramAPI;

class CommentResponse extends Response
{
    protected $comment = null;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            if ((isset($response['comment'])) && (!empty($response['comment']))) {
                $this->comment = new Comment($response['comment']);
            }

            $this->next_max_id = isset($response['next_max_id']) ? $response['next_max_id'] : null;
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    /**
     * @return Comment|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    public function getNextMaxId()
    {
        return $this->next_max_id;
    }
}
