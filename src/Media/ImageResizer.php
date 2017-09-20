<?php

namespace InstagramAPI\Media;

use InstagramAPI\Utils;

class ImageResizer implements ResizerInterface
{
    /**
     * Minimum allowed image width.
     *
     * These are decided by Instagram. Not by us!
     *
     * This value is the same for both stories and general media.
     *
     * @var int
     *
     * @see https://help.instagram.com/1631821640426723
     */
    const MIN_WIDTH = 320;

    /**
     * Maximum allowed image width.
     *
     * These are decided by Instagram. Not by us!
     *
     * This value is the same for both stories and general media.
     *
     * Note that Instagram doesn't enforce any max-height. Instead, it checks
     * the width and aspect ratio which ensures that the height is legal too.
     *
     * @var int
     */
    const MAX_WIDTH = 1080;

    /**
     * Output JPEG quality.
     *
     * This value was chosen because 100 is very wasteful. And don't tweak this
     * number, because the JPEG quality number is actually totally meaningless
     * (it is non-standardized) and Instagram can't even read it from the file.
     * They have no idea what quality we've used, and it can be harmful to go
     * lower since different JPEG compressors (such as PHP's implementation) use
     * different quality scales and are often awful at lower qualities! We know
     * that PHP's JPEG quality at 95 is great, so there's no reason to lower it.
     *
     * @var int
     */
    const JPEG_QUALITY = 95;

    /** @var string Input file path. */
    protected $_inputFile;

    /** @var PhotoDetails Media details for the input file. */
    protected $_details;

    /** @var int|null Orientation of the original image. */
    protected $_imageOrientation;

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
     */
    public function __construct(
        $inputFile,
        $outputDir,
        array $bgColor)
    {
        $this->_inputFile = $inputFile;
        $this->_outputDir = $outputDir;
        $this->_bgColor = $bgColor;

        $this->_loadImageDetails();
    }

    /** {@inheritdoc} */
    public function getMediaDetails()
    {
        return $this->_details;
    }

    /** {@inheritdoc} */
    public function isProcessingRequired()
    {
        // Process everything that's not already a JPEG file.
        if (!$this->_isJpeg()) {
            return true;
        }

        // Process if image requires reorientation.
        if ($this->_imageOrientation !== null && $this->_imageOrientation != 1) {
            return true;
        }

        return false;
    }

    /**
     * Check if the input image's pixel data is rotated.
     *
     * @return bool
     */
    protected function _isRotated()
    {
        return in_array($this->_imageOrientation, [5, 6, 7, 8]);
    }

    /** {@inheritdoc} */
    public function isHorFlipped()
    {
        return in_array($this->_imageOrientation, [2, 3, 6, 7]);
    }

    /** {@inheritdoc} */
    public function isVerFlipped()
    {
        return in_array($this->_imageOrientation, [3, 4, 7, 8]);
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

        // Swap to correct dimensions if the image pixels are stored rotated.
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
            // Attempt to process the input file.
            $resource = $this->_loadImage();

            try {
                $output = $this->_processResource($resource, $srcRect, $dstRect, $canvas);
            } finally {
                @imagedestroy($resource);
            }

            // Write the result to disk.
            $outputFile = $this->_makeTempFile();

            try {
                if (!imagejpeg($output, $outputFile, self::JPEG_QUALITY)) {
                    throw new \RuntimeException('Failed to create JPEG image file.');
                }
            } finally {
                @imagedestroy($output);
            }
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
     */
    protected function _loadImageDetails()
    {
        $this->_details = new PhotoDetails($this->_inputFile, Utils::getPhotoFileDetails($this->_inputFile));

        // Detect JPEG EXIF orientation if it exists.
        if ($this->_isJpeg() && ($exif = @exif_read_data($this->_inputFile)) !== false) {
            $this->_imageOrientation = isset($exif['Orientation']) ? $exif['Orientation'] : null;
        }
    }

    /**
     * Loads image into a resource.
     *
     * @throws \RuntimeException
     *
     * @return resource
     */
    protected function _loadImage()
    {
        // Read the correct input file format.
        switch ($this->_details->getType()) {
            case IMAGETYPE_JPEG:
                $resource = imagecreatefromjpeg($this->_inputFile);
                break;
            case IMAGETYPE_PNG:
                $resource = imagecreatefrompng($this->_inputFile);
                break;
            case IMAGETYPE_GIF:
                $resource = imagecreatefromgif($this->_inputFile);
                break;
            default:
                throw new \RuntimeException('Unsupported image type.');
        }
        if ($resource === false) {
            throw new \RuntimeException('Failed to load image.');
        }

        return $resource;
    }

    /**
     * @return bool
     */
    protected function _isJpeg()
    {
        return $this->_details->getType() === IMAGETYPE_JPEG;
    }

    /**
     * @param resource   $source  The original image loaded as a resource.
     * @param Rectangle  $srcRect Rectangle to copy from the input image.
     * @param Rectangle  $dstRect Destination place and scale of copied pixels.
     * @param Dimensions $canvas  The size of the destination canvas.
     *
     * @throws \Exception
     * @throws \RuntimeException
     *
     * @return resource
     */
    protected function _processResource(
        $source,
        Rectangle $srcRect,
        Rectangle $dstRect,
        Dimensions $canvas
    ) {
        // If our input image pixels are stored rotated, swap all coordinates.
        if ($this->_isRotated()) {
            $srcRect = $srcRect->createSwappedAxes();
            $dstRect = $dstRect->createSwappedAxes();
            $canvas = $canvas->createSwappedAxes();
        }

        // Create an output canvas with our desired size.
        $output = imagecreatetruecolor($canvas->getWidth(), $canvas->getHeight());
        if ($output === false) {
            throw new \RuntimeException('Failed to create output image.');
        }

        // Fill the output canvas with our background color.
        // NOTE: If cropping, this is just to have a nice background in
        // the resulting JPG if a transparent image was used as input.
        // If expanding, this will be the color of the border as well.
        $bgColor = imagecolorallocate($output, $this->_bgColor[0], $this->_bgColor[1], $this->_bgColor[2]);
        if ($bgColor === false) {
            throw new \RuntimeException('Failed to allocate background color.');
        }
        if (imagefilledrectangle($output, 0, 0, $canvas->getWidth() - 1, $canvas->getHeight() - 1, $bgColor) === false) {
            throw new \RuntimeException('Failed to fill image with background color.');
        }

        // Copy the resized (and resampled) image onto the new canvas.
        if (imagecopyresampled(
                $output, $source,
                $dstRect->getX(), $dstRect->getY(),
                $srcRect->getX(), $srcRect->getY(),
                $dstRect->getWidth(), $dstRect->getHeight(),
                $srcRect->getWidth(), $srcRect->getHeight()
            ) === false) {
            throw new \RuntimeException('Failed to resample image.');
        }

        // Handle image rotation.
        switch ($this->_imageOrientation) {
            case 2:
                $output = $this->_rotateResource($output, 0, $bgColor, IMG_FLIP_HORIZONTAL);
                break;
            case 3:
                $output = $this->_rotateResource($output, 0, $bgColor, IMG_FLIP_BOTH);
                break;
            case 4:
                $output = $this->_rotateResource($output, 0, $bgColor, IMG_FLIP_VERTICAL);
                break;
            case 5:
                $output = $this->_rotateResource($output, 90, $bgColor, IMG_FLIP_HORIZONTAL);
                break;
            case 6:
                $output = $this->_rotateResource($output, -90, $bgColor);
                break;
            case 7:
                $output = $this->_rotateResource($output, -90, $bgColor, IMG_FLIP_HORIZONTAL);
                break;
            case 8:
                $output = $this->_rotateResource($output, 90, $bgColor);
                break;
        }

        return $output;
    }

    /**
     * Wrapper for PHP's imagerotate function.
     *
     * @param resource $original
     * @param int      $angle
     * @param int      $bgColor
     * @param int|null $flip
     *
     * @throws \RuntimeException
     *
     * @return resource
     */
    protected function _rotateResource(
        $original,
        $angle,
        $bgColor,
        $flip = null)
    {
        // Flip the image resource if needed. Does not create a new resource.
        if ($flip !== null) {
            if (imageflip($original, $flip) === false) {
                throw new \RuntimeException('Failed to flip image.');
            }
        }

        // Return original resource if no rotation is needed.
        if ($angle === 0) {
            return $original;
        }

        // Attempt to create a new, rotated image resource.
        $result = imagerotate($original, $angle, $bgColor);
        if ($result === false) {
            throw new \RuntimeException('Failed to rotate image.');
        }

        // Destroy the original resource since we'll return the new resource.
        @imagedestroy($original);

        return $result;
    }

    /**
     * Creates an empty temp file with a unique filename.
     *
     * @return string
     */
    protected function _makeTempFile()
    {
        return tempnam($this->_outputDir, 'IMG');
    }
}
