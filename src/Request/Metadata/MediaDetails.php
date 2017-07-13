<?php

namespace InstagramAPI\Request\Metadata;

abstract class MediaDetails
{
    /** @var int */
    private $_filesize;

    /** @var string */
    private $_filename;

    /** @var int */
    private $_width;

    /** @var int */
    private $_height;

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * @return int
     */
    public function getFilesize()
    {
        return $this->_filesize;
    }

    /**
     * Constructor.
     *
     * @param string $filename
     * @param array  $details
     */
    public function __construct(
        $filename,
        array $details)
    {
        $this->_filename = $filename;
        if (isset($details['height'])) {
            $this->_height = $details['height'];
        }
        if (isset($details['width'])) {
            $this->_width = $details['width'];
        }
        if (isset($details['filesize'])) {
            $this->_filesize = $details['filesize'];
        }
    }
}
