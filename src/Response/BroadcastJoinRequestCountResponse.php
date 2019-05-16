<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * BroadcastJoinRequestCountResponse.
 *
 * @method string getFetchTs()
 * @method int getNumNewRequests()
 * @method int getNumTotalRequests()
 * @method int getNumUnseenRequests()
 * @method Model\User[] getUsers()
 * @method bool isFetchTs()
 * @method bool isNumNewRequests()
 * @method bool isNumTotalRequests()
 * @method bool isNumUnseenRequests()
 * @method bool isUsers()
 * @method $this setFetchTs(string $value)
 * @method $this setNumNewRequests(int $value)
 * @method $this setNumTotalRequests(int $value)
 * @method $this setNumUnseenRequests(int $value)
 * @method $this setUsers(Model\User[] $value)
 * @method $this unsetFetchTs()
 * @method $this unsetNumNewRequests()
 * @method $this unsetNumTotalRequests()
 * @method $this unsetNumUnseenRequests()
 * @method $this unsetUsers()
 */

class BroadcastJoinRequestCountResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'fetch_ts'            => 'string',
        'num_total_requests'  => 'int',
        'num_new_requests'    => 'int',
        'users'               => 'Model\User[]',
        'num_unseen_requests' => 'int',
    ];
}
