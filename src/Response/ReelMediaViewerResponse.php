<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * ReelMediaViewerResponse.
 *
 * @method mixed getMessage()
 * @method string getNextMaxId()
 * @method string getStatus()
 * @method mixed getTotalViewerCount()
 * @method mixed getUserCount()
 * @method Model\User[] getUsers()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isNextMaxId()
 * @method bool isStatus()
 * @method bool isTotalViewerCount()
 * @method bool isUserCount()
 * @method bool isUsers()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setNextMaxId(string $value)
 * @method $this setStatus(string $value)
 * @method $this setTotalViewerCount(mixed $value)
 * @method $this setUserCount(mixed $value)
 * @method $this setUsers(Model\User[] $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetNextMaxId()
 * @method $this unsetStatus()
 * @method $this unsetTotalViewerCount()
 * @method $this unsetUserCount()
 * @method $this unsetUsers()
 * @method $this unset_Messages()
 */
class ReelMediaViewerResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'users'              => 'Model\User[]',
        'next_max_id'        => 'string',
        'user_count'         => '',
        'total_viewer_count' => '',
    ];
}
