<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * SuggestedUsersFacebookResponse.
 *
 * @method mixed getMessage()
 * @method mixed getRankToken()
 * @method string getStatus()
 * @method Model\Suggested[] getSuggested()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isRankToken()
 * @method bool isStatus()
 * @method bool isSuggested()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setRankToken(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setSuggested(Model\Suggested[] $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetRankToken()
 * @method $this unsetStatus()
 * @method $this unsetSuggested()
 * @method $this unset_Messages()
 */
class SuggestedUsersFacebookResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'suggested'  => 'Model\Suggested[]',
        'rank_token' => '',
    ];
}
