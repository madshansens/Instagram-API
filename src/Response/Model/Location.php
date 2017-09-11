<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
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
 * @method setAddress(mixed $value)
 * @method setCity(mixed $value)
 * @method setEndTime(mixed $value)
 * @method setExternalId(string $value)
 * @method setExternalIdSource(string $value)
 * @method setExternalSource(mixed $value)
 * @method setFacebookEventsId(string $value)
 * @method setFacebookPlacesId(string $value)
 * @method setLat(float $value)
 * @method setLng(float $value)
 * @method setLocationDict(Location $value)
 * @method setName(mixed $value)
 * @method setPk(string $value)
 * @method setProfilePicUrl(mixed $value)
 * @method setProfilePicUsername(mixed $value)
 * @method setShortName(mixed $value)
 * @method setStartTime(mixed $value)
 * @method setTimeGranularity(mixed $value)
 * @method setTimezone(mixed $value)
 * @method setType(mixed $value)
 */
class Location extends AutoPropertyHandler
{
    public $name;
    /**
     * @var string
     */
    public $external_id_source;
    public $external_source;
    public $address;
    /**
     * @var float
     */
    public $lat;
    /**
     * @var float
     */
    public $lng;
    /**
     * @var string
     */
    public $external_id;
    /**
     * @var string
     */
    public $facebook_places_id;
    public $city;
    /**
     * @var string
     */
    public $pk;
    public $short_name;
    /**
     * @var string
     */
    public $facebook_events_id;
    public $start_time;
    public $end_time;
    /**
     * @var Location
     */
    public $location_dict;
    public $type;
    public $profile_pic_url;
    public $profile_pic_username;
    public $time_granularity;
    public $timezone;
}
