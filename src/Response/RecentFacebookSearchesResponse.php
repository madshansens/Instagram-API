<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * RecentFacebookSearchesResponse.
 *
 * @method mixed getMessage()
 * @method Model\Suggested[] getRecent()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isRecent()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setRecent(Model\Suggested[] $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetRecent()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class RecentFacebookSearchesResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'recent' => 'Model\Suggested[]',
    ];
}
