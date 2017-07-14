<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getButtonText()
 * @method mixed getCameraTarget()
 * @method mixed getCardId()
 * @method mixed getImageUrl()
 * @method mixed getMessage()
 * @method mixed getTitle()
 * @method bool isButtonText()
 * @method bool isCameraTarget()
 * @method bool isCardId()
 * @method bool isImageUrl()
 * @method bool isMessage()
 * @method bool isTitle()
 * @method setButtonText(mixed $value)
 * @method setCameraTarget(mixed $value)
 * @method setCardId(mixed $value)
 * @method setImageUrl(mixed $value)
 * @method setMessage(mixed $value)
 * @method setTitle(mixed $value)
 */
class DismissCard extends AutoPropertyHandler
{
    public $card_id;
    public $image_url;
    public $title;
    public $message;
    public $button_text;
    public $camera_target;
}
