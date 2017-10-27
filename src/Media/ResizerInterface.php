<?php

namespace InstagramAPI\Media;

interface ResizerInterface
{
    /**
     * Constructor.
     *
     * @param string $inputFile Path to the input file.
     * @param string $outputDir Path to the output directory.
     * @param array  $bgColor   Array with 3 color components [R, G, B] (0-255/0x00-0xFF) for the background.
     */
    public function __construct(
        $inputFile,
        $outputDir,
        array $bgColor);

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
     * Whether this resizer requires Mod2 width and height canvas dimensions.
     *
     * If this returns FALSE, the calculated `MediaAutoResizer` canvas passed to
     * this resizer during processing _may_ contain uneven width and/or height
     * as the selected output dimensions.
     *
     * Therefore, this function must return TRUE if (and ONLY IF) perfectly even
     * dimensions are necessary for this particular resizer's output format.
     *
     * For example, JPEG images accept any dimensions and must therefore return
     * FALSE. But H264 videos require EVEN dimensions and must return TRUE.
     *
     * @return bool
     */
    public function isMod2CanvasRequired();

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
     * Resize the media.
     *
     * @param Rectangle  $srcRect Rectangle to copy from the input.
     * @param Rectangle  $dstRect Destination place and scale of copied pixels.
     * @param Dimensions $canvas  The size of the destination canvas.
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
