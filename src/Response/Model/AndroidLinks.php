<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class AndroidLinks extends AutoPropertyHandler
{
    public $linkType;
    public $webUri;
    public $androidClass;
    public $package;
    public $deeplinkUri;
    public $callToActionTitle;
}
