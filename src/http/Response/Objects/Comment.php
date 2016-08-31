<?php

namespace InstagramAPI;

class Comment
{
    protected $status;
    protected $username_id;
    protected $created_at_utc;
    protected $created_at;
    protected $bit_flags;
    protected $user;
    protected $comment;
    protected $media_id;
    protected $pk;
    

    public function __construct($commentData)
    {
        $this->status = $commentData['status'];
        $this->username_id = $commentData['user_id'];
        $this->created_at_utc = $commentData['created_at_utc'];
        $this->created_at = $commentData['created_at'];
        $this->bit_flags = $commentData['bit_flags'];
        $this->user = new User($commentData['user']);
        $this->comment = $commentData['text'];
        $this->media_id = $commentData['media_id'];
        $this->pk = $commentData['pk'];
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

    public function getMediaId()
    {
        return $this->media_id;
    }

    public function getCommentId()
    {
        return $this->pk;
    }
}
