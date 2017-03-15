<?php

namespace InstagramAPI;

class SearchTagResponse extends Response
{
    public $has_more;
    /**
     * @var Tag[]
     */
    public $results;
}
