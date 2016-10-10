<?php

namespace InstagramAPI;

class EditMediaResponse extends Response
{
    protected $taken_at;
    protected $image_url;
    protected $like_count;
    protected $likers;
    protected $comments;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->taken_at = $response['media']['taken_at'];
            $this->image_url = $response['media']['image_versions2']['candidates']['0']['url'];
            $this->like_count = $response['media']['like_count'];
            if (isset($response['media']['likers'])) {
                $likers = [];
                foreach ($response['media']['likers'] as $liker) {
                    $likers[] = new User($liker);
                }
                $this->likers = $likers;
            }
            if (isset($response['media']['comments'])) {
                $comments = [];
                foreach ($response['media']['comments'] as $comment) {
                    $comments[] = new Comment($comment);
                }
                $this->comments = $comments;
            }
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
        $this->setFullResponse($response);
    }

    public function getTakenTime()
    {
        return $this->taken_at;
    }

    public function getImageUrl()
    {
        return $this->image_url;
    }

    public function getLikeCount()
    {
        return $this->like_count;
    }

    /**
     * @return User[]
     */
    public function getLikers()
    {
        return $this->likers;
    }

    /**
     * @return Comment
     */
    public function getComments()
    {
        return $this->comments;
    }
}
