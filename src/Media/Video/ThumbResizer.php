<?php

namespace InstagramAPI\Media\Video;

use InstagramAPI\Media\Photo\PhotoResizer;

class ThumbResizer extends VideoResizer
{
    /** {@inheritdoc} */
    public function __construct(
        $inputFile,
        $outputDir,
        array $bgColor,
        FFmpegWrapper $ffmpegWrapper = null)
    {
        parent::__construct($inputFile, $outputDir, $bgColor, $ffmpegWrapper);
        $this->_outputFormat = '-f mjpeg -ss 00:00:01 -vframes 1';
    }

    /** {@inheritdoc} */
    public function getMinWidth()
    {
        return PhotoResizer::MIN_WIDTH;
    }

    /** {@inheritdoc} */
    public function getMaxWidth()
    {
        return PhotoResizer::MAX_WIDTH;
    }
}
