<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DirectVisualInboxResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'unseen_count'    => '',
        'has_more_unread' => '',
        'read_cursor'     => '',
        'has_more_read'   => '',
        'unread_cursor'   => '',
        'threads'         => 'Model\DirectThread[]',
    ];
}
