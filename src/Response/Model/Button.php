<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getAction()
 * @method mixed getActionInfo()
 * @method mixed getBackgroundColor()
 * @method mixed getBorderColor()
 * @method mixed getText()
 * @method mixed getTextColor()
 * @method mixed getUrl()
 * @method bool isAction()
 * @method bool isActionInfo()
 * @method bool isBackgroundColor()
 * @method bool isBorderColor()
 * @method bool isText()
 * @method bool isTextColor()
 * @method bool isUrl()
 * @method setAction(mixed $value)
 * @method setActionInfo(mixed $value)
 * @method setBackgroundColor(mixed $value)
 * @method setBorderColor(mixed $value)
 * @method setText(mixed $value)
 * @method setTextColor(mixed $value)
 * @method setUrl(mixed $value)
 */
class Button extends AutoPropertyHandler
{
    public $text;
    public $url;
    public $action;
    public $background_color;
    public $border_color;
    public $text_color;
    public $action_info;
}
