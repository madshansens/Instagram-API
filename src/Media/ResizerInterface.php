<?php

namespace InstagramAPI\Media;

interface ResizerInterface
{
    /**
     * Get the media details.
     *
     * @return MediaDetails
     */
    public function getMediaDetails();

    /**
     * Check if media requires processing.
     *
     * This must return TRUE if the media resizer sees any other problems with
     * the input file (such as needing rotation or media format transcoding).
     *
     * @return bool
     */
    public function isProcessingRequired();

    /**
     * Check if media is horizontally flipped (used for cropFocus auto-detection).
     *
     * @return bool
     */
    public function isHorFlipped();

    /**
     * Check if media is vertically flipped (used for cropFocus auto-detection).
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
     * @return string The path to the output file.
     */
    public function resize(
        Rectangle $srcRect,
        Rectangle $dstRect,
        Dimensions $canvas);

    /**
     * Get the minimum allowed media width for this media type.
     *
     * @return int
     */
    public function getMinWidth();

    /**
     * Get the maximum allowed media width for this media type.
     *
     * @return int
     */
    public function getMaxWidth();

    /**
     * Get the original dimensions for the input media.
     *
     * @return Dimensions
     */
    public function getInputDimensions();
}
