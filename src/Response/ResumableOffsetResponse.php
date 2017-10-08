<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class ResumableOffsetResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'offset' => 'int',
    ];

    /**
     * Checks if the response was successful.
     *
     * @return bool
     */
    public function isOk()
    {
        $offset = $this->_getProperty('offset');
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
