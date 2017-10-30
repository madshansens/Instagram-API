<?php

namespace InstagramAPI\Media\Video;

class ThumbResizer extends VideoResizer
{
    /** {@inheritdoc} */
    protected function _shouldProcess()
    {
        // We must always process the video to get its thumbnail.
        return true;
    }

    /** {@inheritdoc} */
    protected function _getOutputFormat()
    {
        // TODO Allow custom timestamp.
        return '-f mjpeg -ss 00:00:01 -vframes 1';
    }
}
