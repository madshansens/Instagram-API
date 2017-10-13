<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Story.
 *
 * @method Args getArgs()
 * @method Counts getCounts()
 * @method string getPk()
 * @method mixed getStoryType()
 * @method mixed getType()
 * @method bool isArgs()
 * @method bool isCounts()
 * @method bool isPk()
 * @method bool isStoryType()
 * @method bool isType()
 * @method $this setArgs(Args $value)
 * @method $this setCounts(Counts $value)
 * @method $this setPk(string $value)
 * @method $this setStoryType(mixed $value)
 * @method $this setType(mixed $value)
 * @method $this unsetArgs()
 * @method $this unsetCounts()
 * @method $this unsetPk()
 * @method $this unsetStoryType()
 * @method $this unsetType()
 */
class Story extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'pk'         => 'string',
        'counts'     => 'Counts',
        'args'       => 'Args',
        'type'       => '',
        'story_type' => '',
    ];
}
