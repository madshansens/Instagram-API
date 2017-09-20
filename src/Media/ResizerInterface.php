<?php

namespace InstagramAPI\Media;

use InstagramAPI\Request\Metadata\MediaDetails;

interface ResizerInterface
{
    /**
     * Returns media details.
     *
     * @return MediaDetails
     */
    public function getMediaDetails();

    /**
     * Returns true, if media requires processing.
     *
     * @return bool
     */
    public function isProcessingRequired();

    /**
     * Returns true, if media is horizontally flipped (used for cropFocus auto-detection).
     *
     * @return bool
     */
    public function isHorFlipped();

    /**
     * Returns true, if media is vertically flipped (used for cropFocus auto-detection).
     *
     * @return bool
     */
    public function isVerFlipped();

    /**
     * Resizes the media.
     *
     * @param Rectangle  $srcRect
     * @param Rectangle  $dstRect
     * @param Dimensions $canvas
     *
     * @return string
     */
    public function resize(
        Rectangle $srcRect,
        Rectangle $dstRect,
        Dimensions $canvas);

    /**
     * Returns minimum allowed media width.
     *
     * @return int
     */
    public function getMinWidth();

    /**
     * Returns maximum allowed media width.
     *
     * @return int
     */
    public function getMaxWidth();

    /**
     * Returns dimensions object for input media.
     *
     * @return Dimensions
     */
    public function getInputDimensions();
}
