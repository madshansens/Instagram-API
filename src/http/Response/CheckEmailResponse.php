<?php

namespace InstagramAPI;

class CheckEmailResponse extends Response
{
    public $username;
    public $confirmed;
    public $status;
    public $valid;
    public $username_suggestions = null;
}
