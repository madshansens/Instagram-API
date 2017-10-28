<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Item.
 *
 * @method Item getMedia()
 * @method string getText()
 * @method string getTitle()
 * @method string getMessage()
 * @method bool getIsLinked()
 * @method bool isMedia()
 * @method bool isText()
 * @method bool isTitle()
 * @method bool isMessage()
 * @method bool isIsLinked()
 * @method $this setMedia(Item $value)
 * @method $this setText(string $value)
 * @method $this setTitle(string $value)
 * @method $this setMessage(string $value)
 * @method $this setIsLinked(bool $value)
 * @method $this unsetMedia()
 * @method $this unsetText()
 * @method $this unsetTitle()
 * @method $this unsetMessage()
 * @method $this unsetIsLinked()
 */
class StoryShare extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media'                            => 'Item',
        'text'                             => 'string',
        'title'                            => 'string',
        'message'                          => 'string',
        'is_linked'                        => 'bool',
    ];
}
