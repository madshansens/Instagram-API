<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DirectRankedRecipientsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'expires'           => '',
        'ranked_recipients' => 'Model\DirectRankedRecipient[]',
        'filtered'          => '',
        'request_id'        => 'string',
        'rank_token'        => '',
    ];
}
