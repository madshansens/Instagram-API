<?php

namespace InstagramAPI;

class RankedRecipientsResponse extends Response
{
    var $expires;
    /**
    * 
    * @var RankedRecipientsUserList[]
    */
    var $ranked_recipients;
    var $filtered;

}


class RankedRecipientsUserList extends Response {
    /**
    * 
    * @var User
    */
    var $user;
}
