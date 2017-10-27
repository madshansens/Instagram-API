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

    /** @var string Output format definition. */
    protected $_outputFormat;

    /** @var FFmpegWrapper */
    protected $_ffmpegWrapper;

    /** {@inheritdoc} */
    public function __construct(
        $inputFile,
        $outputDir,
        array $bgColor,
        FFmpegWrapper $ffmpegWrapper = null)
    {
        $this->_inputFile = $inputFile;
        $this->_outputDir = $outputDir;
        $this->_bgColor = $bgColor;

        $this->_ffmpegWrapper = $ffmpegWrapper;
        if ($this->_ffmpegWrapper === null) {
            $this->_ffmpegWrapper = Utils::getFFmpegWrapper();
        }
        $this->_outputFormat = '-c:a copy -f mp4';

        $this->_loadVideoDetails();
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
        return $this->_details->getRotation() % 180;
    }

    /** {@inheritdoc} */
    public function isHorFlipped()
    {
        return $this->_details->getRotation() === 90;
    }

    /** {@inheritdoc} */
    public function isVerFlipped()
    {
        return $this->_details->getRotation() === 180;
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
        // Swap to correct dimensions if the video pixels are stored rotated.
        if ($this->_hasSwappedAxes()) {
            $srcRect = $srcRect->withSwappedAxes();
            $dstRect = $dstRect->withSwappedAxes();
            $canvas = $canvas->withSwappedAxes();
        }

        $bgColor = sprintf('0x%02X%02X%02X', ...$this->_bgColor);
        $filters = [
            sprintf('crop=w=%d:h=%d:x=%d:y=%d', $srcRect->getWidth(), $srcRect->getHeight(), $srcRect->getX(), $srcRect->getY()),
            sprintf('scale=w=%d:h=%d', $dstRect->getWidth(), $dstRect->getHeight()),
            sprintf('pad=w=%d:h=%d:x=%d:y=%d:color=%s', $canvas->getWidth(), $canvas->getHeight(), $dstRect->getX(), $dstRect->getY(), $bgColor),
        ];

        $inputFormat = '';
        $outputFormat = $this->_outputFormat;
        if ($this->_details->getRotation()) {
            if ($this->_ffmpegWrapper->hasNoAutorotate()) {
                $inputFormat = '-noautorotate';
            }
            $outputFormat .= ' -metadata:s:v rotate=""';
            switch ($this->_details->getRotation()) {
                case 90:
                    $filters[] = 'transpose=clock';
                    break;
                case 180:
                    $filters[] = 'hflip';
                    $filters[] = 'vflip';
                    break;
                case 270:
                    $filters[] = 'transpose=cclock';
                    break;
            }
        }

        // TODO: Force to h264 + aac audio, but if audio input is already aac then use "copy" for lossless audio processing.
        // Video format can't copy since we always need to re-encode due to video filtering.
        $this->_ffmpegWrapper->run(sprintf(
            '%s -i %s -y -vf %s %s %s',
            $inputFormat,
            escapeshellarg($this->_inputFile),
            escapeshellarg(implode(',', $filters)),
            $outputFormat,
            escapeshellarg($outputFile)
        ));
    }
}
