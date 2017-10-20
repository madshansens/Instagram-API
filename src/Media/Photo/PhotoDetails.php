<?php

namespace InstagramAPI\Media\Photo;

use InstagramAPI\Media\MediaDetails;

class PhotoDetails extends MediaDetails
{
    /** @var int */
    private $_type;

    /**
     * @return int
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $filename,
        array $details)
    {
        if (isset($details['type'])) {
            $this->_type = $details['type'];
        }
        parent::__construct($filename, $details);
    }
}
