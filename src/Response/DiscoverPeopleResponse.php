<?php

namespace InstagramAPI\Response;

class DiscoverPeopleResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\Groups[]
     */
    public $groups;
    public $more_available;
    /**
     * @var string
     */
    public $max_id;
}
