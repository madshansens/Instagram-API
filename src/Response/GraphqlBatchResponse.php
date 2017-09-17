<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getIsError()
 * @method mixed getIsSkipped()
 * @method mixed getIsSuccessful()
 * @method Model\GraphQuery getQ0()
 * @method bool isIsError()
 * @method bool isIsSkipped()
 * @method bool isIsSuccessful()
 * @method bool isQ0()
 * @method setIsError(mixed $value)
 * @method setIsSkipped(mixed $value)
 * @method setIsSuccessful(mixed $value)
 * @method setQ0(Model\GraphQuery $value)
 */
class GraphqlBatchResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\GraphQuery
     */
    public $q0;
    public $is_successful;
    public $is_error;
    public $is_skipped;

    public function isOk()
    {
        if ($this->getQ0() !== null && $this->getIsSuccessful() === 1) {
            return true;
        } else {
            // Set a nice message for exceptions.
            if ($this->getMessage() === null) {
                $this->setMessage('There was an error while fetching account statistics. Try again later.');
            }

            return false;
        }
    }
}
