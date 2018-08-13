<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * GraphData.
 *
 * @method mixed getError()
 * @method ShadowInstagramUser getUser()
 * @method bool isError()
 * @method bool isUser()
 * @method $this setError(mixed $value)
 * @method $this setUser(ShadowInstagramUser $value)
 * @method $this unsetError()
 * @method $this unsetUser()
 */
class GraphData extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'user'     => 'ShadowInstagramUser',
        'error'    => '',
    ];
}
