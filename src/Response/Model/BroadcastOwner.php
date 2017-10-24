<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * BroadcastOwner.
 *
 * @method FriendshipStatus getFriendshipStatus()
 * @method string getFullName()
 * @method bool getIsPrivate()
 * @method bool getIsVerified()
 * @method string getPk()
 * @method string getProfilePicId()
 * @method string getProfilePicUrl()
 * @method string getUsername()
 * @method bool isFriendshipStatus()
 * @method bool isFullName()
 * @method bool isIsPrivate()
 * @method bool isIsVerified()
 * @method bool isPk()
 * @method bool isProfilePicId()
 * @method bool isProfilePicUrl()
 * @method bool isUsername()
 * @method $this setFriendshipStatus(FriendshipStatus $value)
 * @method $this setFullName(string $value)
 * @method $this setIsPrivate(bool $value)
 * @method $this setIsVerified(bool $value)
 * @method $this setPk(string $value)
 * @method $this setProfilePicId(string $value)
 * @method $this setProfilePicUrl(string $value)
 * @method $this setUsername(string $value)
 * @method $this unsetFriendshipStatus()
 * @method $this unsetFullName()
 * @method $this unsetIsPrivate()
 * @method $this unsetIsVerified()
 * @method $this unsetPk()
 * @method $this unsetProfilePicId()
 * @method $this unsetProfilePicUrl()
 * @method $this unsetUsername()
 */
class BroadcastOwner extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'pk'                => 'string',
        'friendship_status' => 'FriendshipStatus',
        'full_name'         => 'string',
        'is_verified'       => 'bool',
        'profile_pic_url'   => 'string',
        'profile_pic_id'    => 'string',
        'is_private'        => 'bool',
        'username'          => 'string',
    ];
}
