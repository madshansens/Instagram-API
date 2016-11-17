<?php

namespace InstagramAPI;

class MediaInfoResponse extends Response
{
    var $auto_load_more_enabled;
    var $status;
    var $num_results;
    var $more_available;
    /** 
    * @var Item[]
    */
    var $items;


}
