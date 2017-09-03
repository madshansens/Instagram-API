<?php

namespace InstagramAPI\Realtime\Mqtt;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method string getErrorMessage()
 * @method int getErrorType()
 * @method int getSeqId()
 * @method bool getSucceeded()
 * @method bool isErrorMessage()
 * @method bool isErrorType()
 * @method bool isSeqId()
 * @method bool isSucceeded()
 * @method setErrorMessage(string $value)
 * @method setErrorType(int $value)
 * @method setSeqId(int $value)
 * @method setSucceeded(bool $value)
 */
class Iris extends AutoPropertyHandler
{
    /** @var int */
    public $seq_id;
    /** @var bool */
    public $succeeded;
    /** @var int */
    public $error_type;
    /** @var string */
    public $error_message;
}
