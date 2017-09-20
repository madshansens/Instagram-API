<?php

namespace InstagramAPI\Media;

class VideoDetails extends MediaDetails
{
    /** @var float */
    private $_duration;

    /** @var string */
    private $_codec;

    /**
     * @return float
     */
    public function getDuration()
    {
        return $this->_duration;
    }

    /**
     * @return int
     */
    public function getDurationInMsec()
    {
        // NOTE: ceil() is to round up and get rid of any MS decimals.
        return (int) ceil($this->getDuration() * 1000);
    }

    /**
     * @return string
     */
    public function getCodec()
    {
        return $this->_codec;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $filename,
        array $details)
    {
        if (isset($details['codec'])) {
            $this->_codec = $details['codec'];
        }
        if (isset($details['duration'])) {
            $this->_duration = $details['duration'];
        }
        parent::__construct($filename, $details);
    }
}
