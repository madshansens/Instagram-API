<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * AccountDetailsResponse.
 *
 * @method mixed getAdsInfo()
 * @method string getDateJoined()
 * @method mixed getMessage()
 * @method Model\PrimaryCountryInfo getPrimaryCountryInfo()
 * @method mixed getSharedFollowerAccountsInfo()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isAdsInfo()
 * @method bool isDateJoined()
 * @method bool isMessage()
 * @method bool isPrimaryCountryInfo()
 * @method bool isSharedFollowerAccountsInfo()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setAdsInfo(mixed $value)
 * @method $this setDateJoined(string $value)
 * @method $this setMessage(mixed $value)
 * @method $this setPrimaryCountryInfo(Model\PrimaryCountryInfo $value)
 * @method $this setSharedFollowerAccountsInfo(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetAdsInfo()
 * @method $this unsetDateJoined()
 * @method $this unsetMessage()
 * @method $this unsetPrimaryCountryInfo()
 * @method $this unsetSharedFollowerAccountsInfo()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class AccountDetailsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'date_joined'                   => 'string',
        'primary_country_info'          => 'Model\PrimaryCountryInfo',
        'shared_follower_accounts_info' => '',
        'ads_info'                      => '',
    ];
}
