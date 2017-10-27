<?php

namespace InstagramAPI\Media\Video;

use InstagramAPI\Media\MediaDetails;

class VideoDetails extends MediaDetails
{
    /** @var float */
    private $_duration;

    /** @var string */
    private $_codec;

    /** @var int */
    private $_rotation;

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
     * @return int
     */
    public function getRotation()
    {
        return $this->_rotation;
    }

    /**
     * @param $rotation
     *
     * @return int
     */
    private function _normalizeRotation(
        $rotation)
    {
        // The angle must be in 0..359 degrees range.
        $result = $rotation % 360;
        // Negative angle can be normalized by adding it to 360:
        // 360 + (-90) = 270.
        if ($result < 0) {
            $result = 360 + $result;
        }
        // The final angle must be one of 0, 90, 180 or 270 degrees.
        // So we are rounding it to the closest one.
        $result = round($result / 90) * 90;

        return (int) $result;
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
        if (isset($details['rotation'])) {
            $this->_rotation = $this->_normalizeRotation($details['rotation']);
            // Swap axes when rotation angle equals to 90 or 270 degrees.
            if ($this->_rotation % 180 && isset($details['width'], $details['height'])) {
                $tmp = $details['width'];
                $details['width'] = $details['height'];
                $details['height'] = $tmp;
            }
        }
        parent::__construct($filename, $details);
    }
}
