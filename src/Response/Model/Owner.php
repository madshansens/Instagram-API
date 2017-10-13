<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Owner.
 *
 * @method float getLat()
 * @method float getLng()
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
 * @method $this setLat(float $value)
 * @method $this setLng(float $value)
 * @method $this setLocationDict(Location $value)
 * @method $this setName(mixed $value)
 * @method $this setPk(string $value)
 * @method $this setProfilePicUrl(mixed $value)
 * @method $this setProfilePicUsername(mixed $value)
 * @method $this setShortName(mixed $value)
 * @method $this setType(mixed $value)
 * @method $this unsetLat()
 * @method $this unsetLng()
 * @method $this unsetLocationDict()
 * @method $this unsetName()
 * @method $this unsetPk()
 * @method $this unsetProfilePicUrl()
 * @method $this unsetProfilePicUsername()
 * @method $this unsetShortName()
 * @method $this unsetType()
 */
class Owner extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'type'                 => '',
        'pk'                   => 'string',
        'name'                 => '',
        'profile_pic_url'      => '',
        'profile_pic_username' => '',
        'short_name'           => '',
        'lat'                  => 'float',
        'lng'                  => 'float',
        'location_dict'        => 'Location',
    ];
}
