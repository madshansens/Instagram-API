<?php

namespace InstagramAPI;

class CheckEmailResponse extends Response
{
    var $username;
    var $confirmed;
    var $status;
    var $valid;
    var $username_suggestions = null;

}
