<?php

namespace InstagramAPI\Media\Video;

use InstagramAPI\Constants;
use InstagramAPI\Utils;

/**
 * Automatically creates a video thumbnail according to Instagram's rules.
 */
class InstagramThumbnail extends InstagramVideo
{
    /** @var float Thumbnail offset in secs, with milliseconds (decimals). */
    protected $_thumbnailTimestamp;

    /**
     * Constructor.
     *
     * @param string             $inputFile     Path to an input file.
     * @param array              $options       An associative array of optional parameters.
     * @param FFmpegWrapper|null $ffmpegWrapper Custom FFmpeg wrapper.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @see InstagramMedia::__construct() description for the list of parameters.
     */
    public function __construct(
        $inputFile,
        array $options = [],
        FFmpegWrapper $ffmpegWrapper = null)
    {
        parent::__construct($inputFile, $options, $ffmpegWrapper);

        // Handle custom thumbnail timestamp (only supported by timeline media).
        // NOTE: Like the real app, which only allows custom covers on timeline.
        $this->_thumbnailTimestamp = 1.0; // Default: 00:00:01.000
        if (
            isset($options['targetFeed'])
            && $options['targetFeed'] === Constants::FEED_TIMELINE
            && isset($options['thumbnailTimestamp'])
        ) {
            $customTimestamp = $options['thumbnailTimestamp'];
            // If custom timestamp is a number, use as-is. Otherwise assume it's
            // a "HH:MM:SS[.000]" string and attempt to convert. Throws if bad.
            $this->_thumbnailTimestamp = is_int($customTimestamp) || is_float($customTimestamp)
                                       ? (float) $customTimestamp
                                       : Utils::hmsTimeToSeconds($customTimestamp);
        }

        // Ensure the timestamp is 0+ and never longer than the video duration.
        if ($this->_thumbnailTimestamp > $this->_details->getDuration()) {
            $this->_thumbnailTimestamp = $this->_details->getDuration();
        }
        if ($this->_thumbnailTimestamp < 0.0) {
            throw new \InvalidArgumentException('Thumbnail timestamp must be a positive number.');
        }
    }

    /**
     * Get thumbnail timestamp as a float.
     *
     * @return float Thumbnail offset in secs, with milliseconds (decimals).
     */
    public function getTimestamp()
    {
        return $this->_thumbnailTimestamp;
    }

    /**
     * Get thumbnail timestamp as a formatted string.
     *
     * @return string The time formatted as `HH:MM:SS.###` (`###` is millis).
     */
    public function getTimestampString()
    {
        return Utils::hmsTimeFromSeconds($this->_thumbnailTimestamp);
    }

    /** {@inheritdoc} */
    protected function _shouldProcess()
    {
        // We must always process the video to get its thumbnail.
        return true;
    }

    /** {@inheritdoc} */
    protected function _getInputFlags()
    {
        // The seektime *must* be specified here, before the input file.
        // Otherwise ffmpeg will do a slow conversion of the whole file
        // (but discarding converted frames) until it gets to target time.
        // See: https://trac.ffmpeg.org/wiki/Seeking
        return [
            sprintf('-ss %s', $this->getTimestampString()),
        ];
    }

    /** {@inheritdoc} */
    protected function _getOutputFlags()
    {
        return [
            '-f mjpeg',
            '-vframes 1',
        ];
    }
}
