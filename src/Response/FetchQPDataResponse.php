<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getErrorMsg()
 * @method mixed getExtraInfo()
 * @method mixed getQpData()
 * @method mixed getRequestStatus()
 * @method bool isErrorMsg()
 * @method bool isExtraInfo()
 * @method bool isQpData()
 * @method bool isRequestStatus()
 * @method setErrorMsg(mixed $value)
 * @method setExtraInfo(mixed $value)
 * @method setQpData(mixed $value)
 * @method setRequestStatus(mixed $value)
 */
class FetchQPDataResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $qp_data;
    public $request_status;
    public $extra_info;
    public $error_msg;
}
