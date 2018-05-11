<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * ShadowInstagramUser.
 *
 * @method BusinessManager getBusinessManager()
 * @method mixed getError()
 * @method string getId()
 * @method Image getProfilePicture()
 * @method string getUsername()
 * @method bool isBusinessManager()
 * @method bool isError()
 * @method bool isId()
 * @method bool isProfilePicture()
 * @method bool isUsername()
 * @method $this setBusinessManager(BusinessManager $value)
 * @method $this setError(mixed $value)
 * @method $this setId(string $value)
 * @method $this setProfilePicture(Image $value)
 * @method $this setUsername(string $value)
 * @method $this unsetBusinessManager()
 * @method $this unsetError()
 * @method $this unsetId()
 * @method $this unsetProfilePicture()
 * @method $this unsetUsername()
 */
class ShadowInstagramUser extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'               => 'string',
        'username'         => 'string',
        'profile_picture'  => 'Image',
        'business_manager' => 'BusinessManager',
        'error'            => '',
    ];
}
