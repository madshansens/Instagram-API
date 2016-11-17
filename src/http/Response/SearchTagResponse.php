<?php

namespace InstagramAPI;

class SearchTagResponse extends Response
{
    public $has_more;
    public $status;


    /*
    * @var Tag[] $results
    */
    public $results;
}
