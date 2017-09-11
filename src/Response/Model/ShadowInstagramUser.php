<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method BusinessManager getBusinessManager()
 * @method mixed getError()
 * @method string getId()
 * @method ProfilePicture getProfilePicture()
 * @method mixed getUsername()
 * @method bool isBusinessManager()
 * @method bool isError()
 * @method bool isId()
 * @method bool isProfilePicture()
 * @method bool isUsername()
 * @method setBusinessManager(BusinessManager $value)
 * @method setError(mixed $value)
 * @method setId(string $value)
 * @method setProfilePicture(ProfilePicture $value)
 * @method setUsername(mixed $value)
 */
class ShadowInstagramUser extends AutoPropertyHandler
{
    /**
     * @var string
     */
    public $id;
    public $username;
    /**
     * @var ProfilePicture
     */
    public $profile_picture;
    /**
     * @var BusinessManager
     */
    public $business_manager;
    public $error;
}
