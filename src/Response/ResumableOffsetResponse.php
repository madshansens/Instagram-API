<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method int getOffset()
 * @method bool isOffset()
 * @method setOffset(int $value)
 */
class ResumableOffsetResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /** @var int */
    public $offset;

    /**
     * Checks if the response was successful.
     *
     * @return bool
     */
    public function isOk()
    {
        $offset = $this->getOffset();
        if ($offset !== null && $offset >= 0) {
            return true;
        } else {
            // Set a nice message for exceptions.
            if ($this->getMessage() === null) {
                $this->setMessage('Offset for resumable uploader is missing or invalid.');
            }

            return false;
        }
    }
}
