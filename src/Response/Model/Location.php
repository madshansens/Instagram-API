<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Location.
 *
 * @method mixed getAddress()
 * @method mixed getCity()
 * @method mixed getEndTime()
 * @method string getExternalId()
 * @method string getExternalIdSource()
 * @method mixed getExternalSource()
 * @method string getFacebookEventsId()
 * @method string getFacebookPlacesId()
 * @method float getLat()
 * @method float getLng()
 * @method Location getLocationDict()
 * @method mixed getName()
 * @method string getPk()
 * @method mixed getProfilePicUrl()
 * @method mixed getProfilePicUsername()
 * @method mixed getShortName()
 * @method mixed getStartTime()
 * @method mixed getTimeGranularity()
 * @method mixed getTimezone()
 * @method mixed getType()
 * @method bool isAddress()
 * @method bool isCity()
 * @method bool isEndTime()
 * @method bool isExternalId()
 * @method bool isExternalIdSource()
 * @method bool isExternalSource()
 * @method bool isFacebookEventsId()
 * @method bool isFacebookPlacesId()
 * @method bool isLat()
 * @method bool isLng()
 * @method bool isLocationDict()
 * @method bool isName()
 * @method bool isPk()
 * @method bool isProfilePicUrl()
 * @method bool isProfilePicUsername()
 * @method bool isShortName()
 * @method bool isStartTime()
 * @method bool isTimeGranularity()
 * @method bool isTimezone()
 * @method bool isType()
 * @method $this setAddress(mixed $value)
 * @method $this setCity(mixed $value)
 * @method $this setEndTime(mixed $value)
 * @method $this setExternalId(string $value)
 * @method $this setExternalIdSource(string $value)
 * @method $this setExternalSource(mixed $value)
 * @method $this setFacebookEventsId(string $value)
 * @method $this setFacebookPlacesId(string $value)
 * @method $this setLat(float $value)
 * @method $this setLng(float $value)
 * @method $this setLocationDict(Location $value)
 * @method $this setName(mixed $value)
 * @method $this setPk(string $value)
 * @method $this setProfilePicUrl(mixed $value)
 * @method $this setProfilePicUsername(mixed $value)
 * @method $this setShortName(mixed $value)
 * @method $this setStartTime(mixed $value)
 * @method $this setTimeGranularity(mixed $value)
 * @method $this setTimezone(mixed $value)
 * @method $this setType(mixed $value)
 * @method $this unsetAddress()
 * @method $this unsetCity()
 * @method $this unsetEndTime()
 * @method $this unsetExternalId()
 * @method $this unsetExternalIdSource()
 * @method $this unsetExternalSource()
 * @method $this unsetFacebookEventsId()
 * @method $this unsetFacebookPlacesId()
 * @method $this unsetLat()
 * @method $this unsetLng()
 * @method $this unsetLocationDict()
 * @method $this unsetName()
 * @method $this unsetPk()
 * @method $this unsetProfilePicUrl()
 * @method $this unsetProfilePicUsername()
 * @method $this unsetShortName()
 * @method $this unsetStartTime()
 * @method $this unsetTimeGranularity()
 * @method $this unsetTimezone()
 * @method $this unsetType()
 */
class Location extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'name'                 => '',
        'external_id_source'   => 'string',
        'external_source'      => '',
        'address'              => '',
        'lat'                  => 'float',
        'lng'                  => 'float',
        'external_id'          => 'string',
        'facebook_places_id'   => 'string',
        'city'                 => '',
        'pk'                   => 'string',
        'short_name'           => '',
        'facebook_events_id'   => 'string',
        'start_time'           => '',
        'end_time'             => '',
        'location_dict'        => 'Location',
        'type'                 => '',
        'profile_pic_url'      => '',
        'profile_pic_username' => '',
        'time_granularity'     => '',
        'timezone'             => '',
    ];
}
