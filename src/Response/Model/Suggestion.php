<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * @method mixed getAlgorithm()
 * @method mixed getCaption()
 * @method mixed getIcon()
 * @method string[] getLargeUrls()
 * @method mixed getMediaIds()
 * @method mixed getMediaInfos()
 * @method mixed getSocialContext()
 * @method string[] getThumbnailUrls()
 * @method User getUser()
 * @method mixed getValue()
 * @method bool isAlgorithm()
 * @method bool isCaption()
 * @method bool isIcon()
 * @method bool isLargeUrls()
 * @method bool isMediaIds()
 * @method bool isMediaInfos()
 * @method bool isSocialContext()
 * @method bool isThumbnailUrls()
 * @method bool isUser()
 * @method bool isValue()
 * @method $this setAlgorithm(mixed $value)
 * @method $this setCaption(mixed $value)
 * @method $this setIcon(mixed $value)
 * @method $this setLargeUrls(string[] $value)
 * @method $this setMediaIds(mixed $value)
 * @method $this setMediaInfos(mixed $value)
 * @method $this setSocialContext(mixed $value)
 * @method $this setThumbnailUrls(string[] $value)
 * @method $this setUser(User $value)
 * @method $this setValue(mixed $value)
 * @method $this unsetAlgorithm()
 * @method $this unsetCaption()
 * @method $this unsetIcon()
 * @method $this unsetLargeUrls()
 * @method $this unsetMediaIds()
 * @method $this unsetMediaInfos()
 * @method $this unsetSocialContext()
 * @method $this unsetThumbnailUrls()
 * @method $this unsetUser()
 * @method $this unsetValue()
 */
class Suggestion extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media_infos'    => '',
        'social_context' => '',
        'algorithm'      => '',
        'thumbnail_urls' => 'string[]',
        'value'          => '',
        'caption'        => '',
        'user'           => 'User',
        'large_urls'     => 'string[]',
        'media_ids'      => '',
        'icon'           => '',
    ];
}
