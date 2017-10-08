<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class GraphqlBatchResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'q0'            => 'Model\GraphQuery',
        'is_successful' => '',
        'is_error'      => '',
        'is_skipped'    => '',
    ];

    /**
     * Checks if the response was successful.
     *
     * @return bool
     */
    public function isOk()
    {
        if ($this->_getProperty('q0') !== null && $this->_getProperty('is_successful') == 1) {
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
