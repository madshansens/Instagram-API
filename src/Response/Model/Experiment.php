<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Experiment.
 *
 * @method mixed getGroup()
 * @method mixed getName()
 * @method Param[] getParams()
 * @method bool isGroup()
 * @method bool isName()
 * @method bool isParams()
 * @method $this setGroup(mixed $value)
 * @method $this setName(mixed $value)
 * @method $this setParams(Param[] $value)
 * @method $this unsetGroup()
 * @method $this unsetName()
 * @method $this unsetParams()
 */
class Experiment extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'params' => 'Param[]',
        'group'  => '',
        'name'   => '',
    ];
}
