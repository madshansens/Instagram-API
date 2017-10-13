<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * ClientEventLogsResponse.
 *
 * @method mixed getAppData()
 * @method mixed getChecksum()
 * @method mixed getConfig()
 * @method mixed getError()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isAppData()
 * @method bool isChecksum()
 * @method bool isConfig()
 * @method bool isError()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setAppData(mixed $value)
 * @method $this setChecksum(mixed $value)
 * @method $this setConfig(mixed $value)
 * @method $this setError(mixed $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetAppData()
 * @method $this unsetChecksum()
 * @method $this unsetConfig()
 * @method $this unsetError()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class ClientEventLogsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'checksum' => '',
        'config'   => '',
        'app_data' => '',
        'error'    => '',
    ];
}
