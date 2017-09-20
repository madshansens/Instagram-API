<?php

namespace InstagramAPI\Media;

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

    /**
     * Check if the input video's pixel data is rotated.
     *
     * @return bool
     */
    protected function _isRotated()
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
        if ($this->_isRotated()) {
            $result = $result->createSwappedAxes();
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
            $outputFile = $this->_makeTempFile();
            // Attempt to process the input file.
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

    /**
     * Creates an empty temp file with a unique filename.
     *
     * @return string
     */
    protected function _makeTempFile()
    {
        return tempnam($this->_outputDir, 'VID');
    }
}
