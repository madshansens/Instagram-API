<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * VoiceMedia.
 *
 * @method Item getMedia()
 * @method bool isMedia()
 * @method $this setMedia(Item $value)
 * @method $this unsetMedia()
 */
class VoiceMedia extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media'                            => 'DirectThreadItemMedia',
    ];
}
