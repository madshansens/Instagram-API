<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getLat()
 * @method mixed getLng()
 * @method Location getLocationDict()
 * @method mixed getName()
 * @method string getPk()
 * @method mixed getProfilePicUrl()
 * @method mixed getProfilePicUsername()
 * @method mixed getShortName()
 * @method mixed getType()
 * @method bool isLat()
 * @method bool isLng()
 * @method bool isLocationDict()
 * @method bool isName()
 * @method bool isPk()
 * @method bool isProfilePicUrl()
 * @method bool isProfilePicUsername()
 * @method bool isShortName()
 * @method bool isType()
 * @method setLat(mixed $value)
 * @method setLng(mixed $value)
 * @method setLocationDict(Location $value)
 * @method setName(mixed $value)
 * @method setPk(string $value)
 * @method setProfilePicUrl(mixed $value)
 * @method setProfilePicUsername(mixed $value)
 * @method setShortName(mixed $value)
 * @method setType(mixed $value)
 */
class Owner extends AutoPropertyHandler
{
    public $type;
    /**
     * @var string
     */
    public $pk;
    public $name;
    public $profile_pic_url;
    public $profile_pic_username;
    public $short_name;
    public $lng;
    public $lat;
    /**
     * @var Location
     */
    public $location_dict;
}
