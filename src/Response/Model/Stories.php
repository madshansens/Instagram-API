<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method string getId()
 * @method mixed getIsPortrait()
 * @method TopLive getTopLive()
 * @method StoryTray[] getTray()
 * @method bool isId()
 * @method bool isIsPortrait()
 * @method bool isTopLive()
 * @method bool isTray()
 * @method setId(string $value)
 * @method setIsPortrait(mixed $value)
 * @method setTopLive(TopLive $value)
 * @method setTray(StoryTray[] $value)
 */
class Stories extends AutoPropertyHandler
{
    public $is_portrait;
    /**
     * @var StoryTray[]
     */
    public $tray;
    /**
     * @var string
     */
    public $id;
    /**
     * @var TopLive
     */
    public $top_live;
}
