<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * QPData.
 *
 * @method Viewer getViewer()
 * @method bool isViewer()
 * @method $this setViewer(Viewer $value)
 * @method $this unsetViewer()
 */
class QPData extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'viewer'   => 'Viewer',
    ];
}
