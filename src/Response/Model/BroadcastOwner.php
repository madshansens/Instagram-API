<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * BroadcastOwner.
 *
 * @method FriendshipStatus getFriendshipStatus()
 * @method mixed getFullName()
 * @method mixed getIsPrivate()
 * @method mixed getIsVerified()
 * @method string getPk()
 * @method string getProfilePicId()
 * @method mixed getProfilePicUrl()
 * @method mixed getUsername()
 * @method bool isFriendshipStatus()
 * @method bool isFullName()
 * @method bool isIsPrivate()
 * @method bool isIsVerified()
 * @method bool isPk()
 * @method bool isProfilePicId()
 * @method bool isProfilePicUrl()
 * @method bool isUsername()
 * @method $this setFriendshipStatus(FriendshipStatus $value)
 * @method $this setFullName(mixed $value)
 * @method $this setIsPrivate(mixed $value)
 * @method $this setIsVerified(mixed $value)
 * @method $this setPk(string $value)
 * @method $this setProfilePicId(string $value)
 * @method $this setProfilePicUrl(mixed $value)
 * @method $this setUsername(mixed $value)
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
        'full_name'         => '',
        'is_verified'       => '',
        'profile_pic_url'   => '',
        'profile_pic_id'    => 'string',
        'is_private'        => '',
        'username'          => '',
    ];
}
