<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Tag.
 *
 * @method mixed getAllowFollowing()
 * @method mixed getAllowMutingStory()
 * @method mixed getDebugInfo()
 * @method mixed getFollowStatus()
 * @method mixed getFollowing()
 * @method string getId()
 * @method int getMediaCount()
 * @method string getName()
 * @method mixed getNonViolating()
 * @method mixed getProfilePicUrl()
 * @method mixed getRelatedTags()
 * @method bool isAllowFollowing()
 * @method bool isAllowMutingStory()
 * @method bool isDebugInfo()
 * @method bool isFollowStatus()
 * @method bool isFollowing()
 * @method bool isId()
 * @method bool isMediaCount()
 * @method bool isName()
 * @method bool isNonViolating()
 * @method bool isProfilePicUrl()
 * @method bool isRelatedTags()
 * @method $this setAllowFollowing(mixed $value)
 * @method $this setAllowMutingStory(mixed $value)
 * @method $this setDebugInfo(mixed $value)
 * @method $this setFollowStatus(mixed $value)
 * @method $this setFollowing(mixed $value)
 * @method $this setId(string $value)
 * @method $this setMediaCount(int $value)
 * @method $this setName(string $value)
 * @method $this setNonViolating(mixed $value)
 * @method $this setProfilePicUrl(mixed $value)
 * @method $this setRelatedTags(mixed $value)
 * @method $this unsetAllowFollowing()
 * @method $this unsetAllowMutingStory()
 * @method $this unsetDebugInfo()
 * @method $this unsetFollowStatus()
 * @method $this unsetFollowing()
 * @method $this unsetId()
 * @method $this unsetMediaCount()
 * @method $this unsetName()
 * @method $this unsetNonViolating()
 * @method $this unsetProfilePicUrl()
 * @method $this unsetRelatedTags()
 */
class Tag extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'                 => 'string',
        'name'               => 'string',
        'media_count'        => 'int',
        'follow_status'      => '',
        'following'          => '',
        'allow_following'    => '',
        'allow_muting_story' => '',
        'profile_pic_url'    => '',
        'non_violating'      => '',
        'related_tags'       => '',
        'debug_info'         => '',
    ];
}
