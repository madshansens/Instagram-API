<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * InlineFollow.
 *
 * @method mixed getFollowing()
 * @method mixed getOutgoingRequest()
 * @method User getUserInfo()
 * @method bool isFollowing()
 * @method bool isOutgoingRequest()
 * @method bool isUserInfo()
 * @method $this setFollowing(mixed $value)
 * @method $this setOutgoingRequest(mixed $value)
 * @method $this setUserInfo(User $value)
 * @method $this unsetFollowing()
 * @method $this unsetOutgoingRequest()
 * @method $this unsetUserInfo()
 */
class InlineFollow extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'user_info'        => 'User',
        'following'        => '',
        'outgoing_request' => '',
    ];
}
