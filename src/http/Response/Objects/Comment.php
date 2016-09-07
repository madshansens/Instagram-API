<?php

namespace InstagramAPI;

class Comment
{
    protected $status;
    protected $username_id = null;
    protected $created_at_utc;
    protected $created_at;
    protected $bit_flags = null;
    protected $user;
    protected $comment;
    protected $pk;
    protected $type = null;
    protected $media_id = null;

    public function __construct($commentData)
    {
        $this->status = $commentData['status'];
        if ((isset($commentData['user_id'])) && (!empty($commentData['user_id']))) {
            $this->username_id = $commentData['user_id'];
        }
        $this->created_at_utc = $commentData['created_at_utc'];
        $this->created_at = $commentData['created_at'];
        if ((isset($commentData['bit_flags'])) && (!empty($commentData['bit_flags']))) {
            $this->bit_flags = $commentData['bit_flags'];
        }
        $this->user = new User($commentData['user']);
        $this->comment = $commentData['text'];
        $this->pk = $commentData['pk'];
        if ((isset($commentData['type'])) && (!empty($commentData['type']))) {
            $this->type = $commentData['type'];
        }
        if ((isset($commentData['media_id'])) && (!empty($commentData['media_id']))) {
            $this->media_id = $commentData['media_id'];
        }
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getUsernameId()
    {
        return $this->username_id;
    }

    public function getCreatedAtUtc()
    {
        return $this->created_at_utc;
    }

    public function created_at()
    {
        return $this->created_at;
    }

    public function getBitFlags()
    {
        return $this->bit_flags;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function getCommentId()
    {
        return $this->pk;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMediaId()
    {
        return $this->media_id;
    }
}
