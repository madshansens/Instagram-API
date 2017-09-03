<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getActionInfo()
 * @method mixed getBackgroundColor()
 * @method mixed getButtonLayout()
 * @method mixed getButtonLocation()
 * @method Button[] getButtons()
 * @method mixed getDismissible()
 * @method mixed getIcon()
 * @method mixed getMegaphoneVersion()
 * @method mixed getMessage()
 * @method mixed getMessageColor()
 * @method mixed getTitle()
 * @method mixed getTitleColor()
 * @method mixed getType()
 * @method string getUuid()
 * @method bool isActionInfo()
 * @method bool isBackgroundColor()
 * @method bool isButtonLayout()
 * @method bool isButtonLocation()
 * @method bool isButtons()
 * @method bool isDismissible()
 * @method bool isIcon()
 * @method bool isMegaphoneVersion()
 * @method bool isMessage()
 * @method bool isMessageColor()
 * @method bool isTitle()
 * @method bool isTitleColor()
 * @method bool isType()
 * @method bool isUuid()
 * @method setActionInfo(mixed $value)
 * @method setBackgroundColor(mixed $value)
 * @method setButtonLayout(mixed $value)
 * @method setButtonLocation(mixed $value)
 * @method setButtons(Button[] $value)
 * @method setDismissible(mixed $value)
 * @method setIcon(mixed $value)
 * @method setMegaphoneVersion(mixed $value)
 * @method setMessage(mixed $value)
 * @method setMessageColor(mixed $value)
 * @method setTitle(mixed $value)
 * @method setTitleColor(mixed $value)
 * @method setType(mixed $value)
 * @method setUuid(string $value)
 */
class GenericMegaphone extends AutoPropertyHandler
{
    public $type;
    public $title;
    public $message;
    public $dismissible;
    public $icon;
    /**
     * @var Button[]
     */
    public $buttons;
    public $megaphone_version;
    public $button_layout;
    public $action_info;
    public $button_location;
    public $background_color;
    public $title_color;
    public $message_color;
    /**
     * @var string
     */
    public $uuid;
}
