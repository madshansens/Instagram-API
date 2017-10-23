<?php

namespace InstagramAPI\Media\Video;

use InstagramAPI\Media\Dimensions;
use InstagramAPI\Media\Rectangle;
use InstagramAPI\Media\ResizerInterface;
use InstagramAPI\Utils;

class VideoResizer implements ResizerInterface
{
    /**
     * Minimum allowed video width.
     *
     * These are decided by Instagram. Not by us!
     *
     * This value is the same for both stories and general media.
     *
     * @var int
     */
    const MIN_WIDTH = 480;

    /**
     * Maximum allowed video width.
     *
     * These are decided by Instagram. Not by us!
     *
     * This value is the same for both stories and general media.
     *
     * @var int
     */
    const MAX_WIDTH = 720;

    /** @var string Input file path. */
    protected $_inputFile;

    /** @var VideoDetails Media details for the input file. */
    protected $_details;

    /** @var string Output directory. */
    protected $_outputDir;

    /** @var array Background color [R, G, B] for the final video. */
    protected $_bgColor;

    /**
     * Constructor.
     *
     * @param string $inputFile
     * @param string $outputDir
     * @param array  $bgColor
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct(
        $inputFile,
        $outputDir,
        array $bgColor)
    {
        $this->_inputFile = $inputFile;
        $this->_outputDir = $outputDir;
        $this->_bgColor = $bgColor;

        $this->_loadVideoDetails();
    }

    /** {@inheritdoc} */
    public function getMediaDetails()
    {
        return $this->_details;
    }

    /** {@inheritdoc} */
    public function isProcessingRequired()
    {
        // TODO: Ensure that video is mp4 + h264 + aac.
        return false;
    }

    /** {@inheritdoc} */
    public function isMod2CanvasRequired()
    {
        return true;
    }

    /**
     * Check if the input video's axes are swapped.
     *
     * @return bool
     */
    protected function _hasSwappedAxes()
    {
        // TODO: Research and implement how to handle rotated videos.
        // Videos can have metadata with a rotation flag:
        // https://addpipe.com/blog/mp4-rotation-metadata-in-mobile-video-files/
        // But ffmpeg has some autorotation-code enabled by default, so we
        // should check how it works: https://trac.ffmpeg.org/ticket/515
        return false;
    }

    /** {@inheritdoc} */
    public function isHorFlipped()
    {
        return false;
    }

    /** {@inheritdoc} */
    public function isVerFlipped()
    {
        return false;
    }

    /** {@inheritdoc} */
    public function getMinWidth()
    {
        return self::MIN_WIDTH;
    }

    /** {@inheritdoc} */
    public function getMaxWidth()
    {
        return self::MAX_WIDTH;
    }

    /** {@inheritdoc} */
    public function getInputDimensions()
    {
        $result = new Dimensions($this->_details->getWidth(), $this->_details->getHeight());

        // Swap to correct dimensions if the video pixels are stored rotated.
        if ($this->_hasSwappedAxes()) {
            $result = $result->withSwappedAxes();
        }

        return $result;
    }

    /** {@inheritdoc} */
    public function resize(
        Rectangle $srcRect,
        Rectangle $dstRect,
        Dimensions $canvas)
    {
        $outputFile = null;

        try {
            // Prepare output file.
            $outputFile = Utils::createTempFile($this->_outputDir, 'VID');
            // Attempt to process the input file.
            // --------------------------------------------------------------
            // WARNING: This calls ffmpeg, which can run for a long time. The
            // user may be running in a CLI. In that case, if they press Ctrl-C
            // to abort, PHP won't run ANY of our shutdown/destructor handlers!
            // Therefore they'll still have the temp file if they abort ffmpeg
            // conversion with Ctrl-C, since our auto-cleanup won't run. There's
            // absolutely nothing good we can do about that (except a signal
            // handler to interrupt their Ctrl-C, which is a terrible idea).
            // Their OS should clear its temp folder periodically. Or if they
            // use a custom temp folder, it's THEIR own job to clear it!
            // --------------------------------------------------------------
            $this->_processVideo($srcRect, $dstRect, $canvas, $outputFile);
        } catch (\Exception $e) {
            if ($outputFile !== null && is_file($outputFile)) {
                @unlink($outputFile);
            }

            throw $e; // Re-throw.
        }

        return $outputFile;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function _loadVideoDetails()
    {
        $this->_details = new VideoDetails($this->_inputFile, Utils::getVideoFileDetails($this->_inputFile));
    }

    /**
     * @param Rectangle  $srcRect    Rectangle to copy from the input.
     * @param Rectangle  $dstRect    Destination place and scale of copied pixels.
     * @param Dimensions $canvas     The size of the destination canvas.
     * @param string     $outputFile
     *
     * @throws \RuntimeException
     */
    protected function _processVideo(
        Rectangle $srcRect,
        Rectangle $dstRect,
        Dimensions $canvas,
        $outputFile)
    {
        // The user must have FFmpeg.
        $ffmpeg = Utils::checkFFMPEG();
        if ($ffmpeg === false) {
            throw new \RuntimeException('You must have FFmpeg to resize videos.');
        }

        $bgColor = sprintf('0x%02X%02X%02X', ...$this->_bgColor);
        $filters = [
            sprintf('crop=w=%d:h=%d:x=%d:y=%d', $srcRect->getWidth(), $srcRect->getHeight(), $srcRect->getX(), $srcRect->getY()),
            sprintf('scale=w=%d:h=%d', $dstRect->getWidth(), $dstRect->getHeight()),
            sprintf('pad=w=%d:h=%d:x=%d:y=%d:color=%s', $canvas->getWidth(), $canvas->getHeight(), $dstRect->getX(), $dstRect->getY(), $bgColor),
        ];

        // TODO: Force to h264 + aac audio, but if audio input is already aac then use "copy" for lossless audio processing.
        // Video format can't copy since we always need to re-encode due to video filtering.
        $command = sprintf(
            '%s -y -i %s -vf %s -c:a copy -f mp4 %s 2>&1',
            $ffmpeg,
            escapeshellarg($this->_inputFile),
            escapeshellarg(implode(',', $filters)),
            escapeshellarg($outputFile)
        );

        exec($command, $output, $returnCode);
        if ($returnCode) {
            // Extract important error messages and build a summary of them.
            $errorLines = [];
            foreach ($output as $line) {
                if (preg_match('/^(?:\[.+?\]\s+)?(?:fail|error|warn|critical)/i', $line)) {
                    $errorLines[] = $line;
                }
            }
            $errorMsg = sprintf('FFmpeg Errors: ["%s"], Command: "%s".', implode('"], ["', $errorLines), $command);

            throw new \RuntimeException($errorMsg, $returnCode);
        }
    }
}
