<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class HiddenEntities extends AutoPropertyMapper
{
    // TODO: The server returns each of these fields as [] arrays, but we don't
    // know what kind of objects those arrays can contain since we've never seen
    // any values in them. So for now, these are left as default types. Most
    // likely, they'll need to be User[], Tag[] and Location[].
    const JSON_PROPERTY_MAP = [
        'user'    => '',
        'hashtag' => '',
        'place'   => '',
    ];
}
