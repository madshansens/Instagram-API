<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getFeedbackAction()
 * @method mixed getFeedbackAppealLabel()
 * @method mixed getFeedbackIgnoreLabel()
 * @method mixed getFeedbackMessage()
 * @method mixed getFeedbackRequired()
 * @method mixed getFeedbackTitle()
 * @method mixed getFeedbackUrl()
 * @method mixed getSpam()
 * @method bool isFeedbackAction()
 * @method bool isFeedbackAppealLabel()
 * @method bool isFeedbackIgnoreLabel()
 * @method bool isFeedbackMessage()
 * @method bool isFeedbackRequired()
 * @method bool isFeedbackTitle()
 * @method bool isFeedbackUrl()
 * @method bool isSpam()
 * @method setFeedbackAction(mixed $value)
 * @method setFeedbackAppealLabel(mixed $value)
 * @method setFeedbackIgnoreLabel(mixed $value)
 * @method setFeedbackMessage(mixed $value)
 * @method setFeedbackRequired(mixed $value)
 * @method setFeedbackTitle(mixed $value)
 * @method setFeedbackUrl(mixed $value)
 * @method setSpam(mixed $value)
 */
class GenericResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $spam;
    public $feedback_required;
    public $feedback_message;
    public $feedback_title;
    public $feedback_url;
    public $feedback_appeal_label;
    public $feedback_ignore_label;
    public $feedback_action;
}
