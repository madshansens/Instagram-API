<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Media.
 *
 * @method mixed getExpiringAt()
 * @method string getId()
 * @method mixed getImage()
 * @method User getUser()
 * @method bool isExpiringAt()
 * @method bool isId()
 * @method bool isImage()
 * @method bool isUser()
 * @method $this setExpiringAt(mixed $value)
 * @method $this setId(string $value)
 * @method $this setImage(mixed $value)
 * @method $this setUser(User $value)
 * @method $this unsetExpiringAt()
 * @method $this unsetId()
 * @method $this unsetImage()
 * @method $this unsetUser()
 */
class Media extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'image'       => '',
        'id'          => 'string',
        'user'        => 'User',
        'expiring_at' => '',
    ];
}
