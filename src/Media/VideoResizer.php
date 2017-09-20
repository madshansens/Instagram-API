<?php

namespace InstagramAPI\Media;

use InstagramAPI\Request\Metadata\VideoDetails;
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

    /** @var string */
    protected $_inputFile;

    /** @var VideoDetails */
    protected $_details;

    /** @var string Output directory. */
    protected $_outputDir;

    /** @var array Background color [R, G, B] for the final image. */
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

    /**
     * Returns true, if the video is rotated.
     *
     * @return bool
     */
    protected function _isRotated()
    {
        // TODO: Research and implement how to handle rotated videos.
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
    public function resize(
        Rectangle $srcRect,
        Rectangle $dstRect,
        Dimensions $canvas)
    {
        $result = null;

        try {
            // Prepare output file.
            $result = $this->_makeTempFile();
            // Attempt to process the input file.
            $this->_processVideo($srcRect, $dstRect, $canvas, $result);
        } catch (\Exception $e) {
            if ($result !== null && is_file($result)) {
                @unlink($result);
            }

            throw $e; // Re-throw.
        }

        return $result;
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

        // Swap coordinates for the rotated video.
        if ($this->_isRotated()) {
            $result = $result->swapAxes();
        }

        return $result;
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
     * Creates an empty temp file with a unique filename.
     *
     * @return string
     */
    protected function _makeTempFile()
    {
        return tempnam($this->_outputDir, 'VID');
    }

    /**
     * @param Rectangle  $srcRect
     * @param Rectangle  $dstRect
     * @param Dimensions $canvas
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
            throw new \RuntimeException($output, $returnCode);
        }
    }
}
