<?php

namespace InstagramAPI;

/**
 * Automatic image resizer.
 *
 * Resizes and crops/expands an image to match Instagram's requirements, if
 * necessary. You can also use this with your own parameters, to force your
 * image into different aspects, ie square, or for adding borders to images.
 *
 * Usage:
 *
 * - Create an instance of the class with your image file and requirements.
 * - Call getFile() to get the path to an image matching the requirements. This
 *   will be the same as the input file if no processing was required.
 * - Optionally, call deleteFile() if you want to delete the temporary file
 *   ahead of time instead of automatically when PHP does its object garbage
 *   collection. This function is safe and won't delete the original input file.
 *
 * Remember to thank Abyr Valg for the brilliant image processing algorithm!
 *
 * @author Abyr Valg <valga.github@abyrga.ru>
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class ImageAutoResizer
{
    /** @var int Crop Operation. */
    const CROP = 1;

    /** @var int Expand Operation. */
    const EXPAND = 2;

    /**
     * Lowest allowed aspect ratio (4:5, meaning portrait).
     *
     * These are decided by Instagram. Not by us!
     *
     * @var float
     *
     * @see https://help.instagram.com/1469029763400082
     */
    const MIN_RATIO = 0.8;

    /**
     * Highest allowed aspect ratio (1.91:1, meaning landscape).
     *
     * These are decided by Instagram. Not by us!
     *
     * @var float
     */
    const MAX_RATIO = 1.91;

    /**
     * Maximum allowed image width.
     *
     * These are decided by Instagram. Not by us!
     *
     * @var int
     *
     * @see https://help.instagram.com/1631821640426723
     */
    const MAX_WIDTH = 1080;

    /**
     * Maximum allowed image height.
     *
     * This is derived from 1080 / 0.8 (tallest portrait aspect allowed).
     * Instagram enforces the width & aspect. Height is auto-derived from that.
     *
     * @var int
     */
    const MAX_HEIGHT = 1350;

    /**
     * Output JPEG quality.
     *
     * This value was chosen because 100 is very wasteful. And don't tweak this
     * number, because the JPEG quality number is actually totally meaningless
     * (it is non-standardized) and Instagram can't even read it from the file.
     * They have no idea what quality we've used, and it can be harmful to go
     * lower since different JPEG compressors (like PHP's implementation) use
     * different quality scales and are often awful at lower qualities! We know
     * that PHP's JPEG quality at 95 is great, so there's no reason to lower it.
     *
     * @var int
     */
    const JPEG_QUALITY = 95;

    /**
     * Override for the default temp path used by all class instances.
     *
     * If you don't provide any tmpPath to the constructor, we'll use this value
     * instead (if non-null). Otherwise we'll use the default system tmp folder.
     *
     * TIP: If your default system temp folder isn't writable, it's NECESSARY
     * for you to set this value to another, writable path, like this:
     *
     * \InstagramAPI\ImageAutoResizer::$defaultTmpPath = '/home/example/foo/';
     */
    public static $defaultTmpPath = null;

    /** @var string Input file path. */
    protected $_inputFile;

    /** @var float|null Minimum allowed aspect ratio. */
    protected $_minAspectRatio;

    /** @var float|null Maximum allowed aspect ratio. */
    protected $_maxAspectRatio;

    /** @var int Crop focus position (-50 .. 50) when cropping. */
    protected $_cropFocus;

    /** @var array Background color [R, G, B] for the final image. */
    protected $_bgColor;

    /** @var int Operation to perform on the image. */
    protected $_operation;

    /** @var string Path to a tmp directory. */
    protected $_tmpPath;

    /** @var string Output file path. */
    protected $_outputFile;

    /** @var int Width of the original image. */
    protected $_width;

    /** @var int Height of the original image. */
    protected $_height;

    /** @var float Aspect ratio of the original image. */
    protected $_aspectRatio;

    /** @var int Type of the original image. */
    protected $_imageType;

    /** @var int|null Orientation of the original image. */
    protected $_imageOrientation;

    /** @var bool Rotated image flag. */
    protected $_isRotated;

    /** @var bool Horizontally flipped image flag (used for cropFocus auto-detection). */
    protected $_isHorFlipped;

    /** @var bool Vertically flipped image flag (used for cropFocus auto-detection). */
    protected $_isVerFlipped;

    /**
     * Constructor.
     *
     * @param string $inputFile Path to an input file.
     * @param array  $options   An associative array of optional parameters, including:
     *                          "cropFocus" (int) - Crop focus position (-50 .. 50) when cropping, uses intelligent guess if not set;
     *                          "minAspectRatio" (float) - Minimum allowed aspect ratio, uses self::MIN_RATIO if not set;
     *                          "maxAspectRatio" (float) - Maximum allowed aspect ratio, uses self::MAX_RATIO if not set;
     *                          "bgColor" (array) - Array with 3 color components [R, G, B] (0-255/0x00-0xFF) for the background, uses white if not set;
     *                          "operation" (int) - Operation to perform on the image (CROP or EXPAND), uses self::CROP if not set;
     *                          "tmpPath" (string) - Path to temp directory, uses system temp location or class-default if not set.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $inputFile,
        array $options = [])
    {
        // Assign variables for all options, to avoid bulky code repetition.
        $cropFocus = isset($options['cropFocus']) ? $options['cropFocus'] : null;
        $minAspectRatio = isset($options['minAspectRatio']) ? $options['minAspectRatio'] : null;
        $maxAspectRatio = isset($options['maxAspectRatio']) ? $options['maxAspectRatio'] : null;
        $bgColor = isset($options['bgColor']) ? $options['bgColor'] : null;
        $operation = isset($options['operation']) ? $options['operation'] : null;
        $tmpPath = isset($options['tmpPath']) ? $options['tmpPath'] : null;

        // Input file.
        if (!is_file($inputFile)) {
            throw new \InvalidArgumentException(sprintf('Input file "%s" doesn\'t exist.', $inputFile));
        }
        $this->_inputFile = $inputFile;

        // Crop focus.
        if ($cropFocus !== null && ($cropFocus < -50 || $cropFocus > 50)) {
            throw new \InvalidArgumentException('Crop focus must be between -50 and 50.');
        }
        $this->_cropFocus = $cropFocus;

        // Aspect ratios.
        if ($minAspectRatio !== null && ($minAspectRatio < self::MIN_RATIO || $minAspectRatio > self::MAX_RATIO)) {
            throw new \InvalidArgumentException(sprintf('Minimum aspect ratio must be between %.2f and %.2f.',
                self::MIN_RATIO, self::MAX_RATIO));
        } elseif ($minAspectRatio === null) {
            $minAspectRatio = self::MIN_RATIO;
        }
        if ($maxAspectRatio !== null && ($maxAspectRatio < self::MIN_RATIO || $maxAspectRatio > self::MAX_RATIO)) {
            throw new \InvalidArgumentException(sprintf('Maximum aspect ratio must be between %.2f and %.2f.',
                self::MIN_RATIO, self::MAX_RATIO));
        } elseif ($maxAspectRatio === null) {
            $maxAspectRatio = self::MAX_RATIO;
        }
        if ($minAspectRatio !== null && $maxAspectRatio !== null && $minAspectRatio > $maxAspectRatio) {
            throw new \InvalidArgumentException('Maximum aspect ratio must be greater or equal to minimum.');
        }
        $this->_minAspectRatio = $minAspectRatio;
        $this->_maxAspectRatio = $maxAspectRatio;

        // Background color.
        if ($bgColor !== null && (!is_array($bgColor) || count($bgColor) != 3 || !isset($bgColor[0]) || !isset($bgColor[1]) || !isset($bgColor[2]))) {
            throw new \InvalidArgumentException('The background color must be a 3-element array [R, G, B].');
        } elseif ($bgColor === null) {
            $bgColor = [255, 255, 255]; // White.
        }
        $this->_bgColor = $bgColor;

        // Image operation.
        if ($operation !== null && $operation !== self::CROP && $operation !== self::EXPAND) {
            throw new \InvalidArgumentException('The operation must be one of the class constants CROP or EXPAND.');
        } elseif ($operation === null) {
            $operation = self::CROP;
        }
        $this->_operation = $operation;

        // Temporary directory path.
        if ($tmpPath === null) {
            $tmpPath = self::$defaultTmpPath !== null
                       ? self::$defaultTmpPath
                       : sys_get_temp_dir();
        }
        if (!is_dir($tmpPath) || !is_writable($tmpPath)) {
            throw new \InvalidArgumentException(sprintf('Directory %s does not exist or is not writable.', $tmpPath));
        }
        $this->_tmpPath = realpath($tmpPath);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->deleteFile();
    }

    /**
     * Removes the output file if it exists and differs from input file.
     *
     * This function is safe and won't delete the original input file.
     *
     * Is automatically called when the class instance is destroyed by PHP.
     * But you can manually call it ahead of time if you want to force cleanup.
     *
     * Note that getFile() will still work afterwards, but will have to process
     * the image again to a new temp file if the input file required processing.
     *
     * @return bool
     */
    public function deleteFile()
    {
        // Only delete if outputfile exists and isn't the same as input file.
        if ($this->_outputFile !== null && $this->_outputFile != $this->_inputFile && is_file($this->_outputFile)) {
            $result = @unlink($this->_outputFile);
            $this->_outputFile = null; // Reset so getFile() will work again.
            return $result;
        }

        return true;
    }

    /**
     * Gets the path to an image file matching the requirements.
     *
     * The automatic processing is performed the first time that this function
     * is called. Which means that no CPU time is wasted if you never call this
     * function at all.
     *
     * Due to the processing, the first call to this function may take a moment.
     *
     * If the input file already fits all of the specifications, we simply
     * return the input path instead, without any need to re-process it.
     *
     * @throws \Exception
     * @throws \RuntimeException
     *
     * @return string The path to the image file.
     *
     * @see ImageAutoResizer::_shouldProcess() For the criteria that determines processing.
     */
    public function getFile()
    {
        if ($this->_outputFile === null) {
            if ($this->_shouldProcess()) {
                $this->_process();
            } else {
                $this->_outputFile = $this->_inputFile;
            }
        }

        return $this->_outputFile;
    }

    /**
     * Checks whether we should process the input file.
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    protected function _shouldProcess()
    {
        $info = @getimagesize($this->_inputFile);
        if ($info === false) {
            throw new \RuntimeException(sprintf('File "%s" is not an image.', $this->_inputFile));
        }

        // Get basic image info.
        list($this->_width, $this->_height, $this->_imageType) = $info;
        $this->_aspectRatio = $this->_width / $this->_height;
        $isJpeg = $this->_imageType == IMAGETYPE_JPEG;

        // Detect image orientation.
        $this->_imageOrientation = null;
        $this->_isRotated = false;
        $this->_isHorFlipped = false;
        $this->_isVerFlipped = false;
        if ($isJpeg && ($exif = @exif_read_data($this->_inputFile)) !== false) {
            if (isset($exif['Orientation'])) {
                $this->_imageOrientation = $exif['Orientation'];
                $this->_isRotated = in_array($this->_imageOrientation, [5, 6, 7, 8]);
                $this->_isHorFlipped = in_array($this->_imageOrientation, [2, 3, 6, 7]);
                $this->_isVerFlipped = in_array($this->_imageOrientation, [3, 4, 7, 8]);
            }
        }

        // If image is rotated, swap width and height.
        if ($this->_isRotated) {
            $width = $this->_width;
            $this->_width = $this->_height;
            $this->_height = $width;
            $this->_aspectRatio = 1 / $this->_aspectRatio;
        }

        // Process everything that's not already a JPEG file.
        if (!$isJpeg) {
            return true;
        }

        // Process if image requires reorientation.
        if ($this->_imageOrientation !== null && $this->_imageOrientation != 1) {
            return true;
        }

        // Process if any side > maximum allowed.
        if ($this->_width > self::MAX_WIDTH || $this->_height > self::MAX_HEIGHT) {
            return true;
        }

        // Process if aspect ratio < minimum allowed.
        if ($this->_minAspectRatio !== null && $this->_aspectRatio < $this->_minAspectRatio) {
            return true;
        }

        // Process if aspect ratio > maximum allowed.
        if ($this->_maxAspectRatio !== null && $this->_aspectRatio > $this->_maxAspectRatio) {
            return true;
        }

        // No need to do any processing.
        return false;
    }

    /**
     * Creates an empty temp file with a unique filename.
     *
     * @return string
     */
    protected function _makeTempFile()
    {
        return tempnam($this->_tmpPath, 'IMG');
    }

    /**
     * Wrapper for imagerotate function.
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
     * @param resource $source The original image loaded as a resource.
     * @param int      $src_x  X-coordinate of source point to copy from.
     * @param int      $src_y  Y-coordinate of source point to copy from.
     * @param int      $src_w  Source width (how many pixels to copy).
     * @param int      $src_h  Source height (how many pixels to copy).
     * @param int      $dst_x  X-coordinate of destination point to copy to.
     * @param int      $dst_y  Y-coordinate of destination point to copy to.
     * @param int      $dst_w  Destination width (how many pixels to scale to).
     * @param int      $dst_h  Destination height (how many pixels to scale to).
     * @param int      $cnv_w  Width of the new image canvas to create.
     * @param int      $cnv_h  Height of the new image canvas to create.
     *
     * @throws \Exception
     * @throws \RuntimeException
     */
    protected function _createNewImage(
        $source,
        $src_x,
        $src_y,
        $src_w,
        $src_h,
        $dst_x,
        $dst_y,
        $dst_w,
        $dst_h,
        $cnv_w,
        $cnv_h)
    {
        // Create an output canvas with our desired size.
        $output = imagecreatetruecolor($cnv_w, $cnv_h);
        if ($output === false) {
            throw new \RuntimeException('Failed to create output image.');
        }
        try {
            // Fill the output canvas with our background color.
            // NOTE: If cropping, this is just to have a nice background in
            // the resulting JPG if a transparent image was used as input.
            // If expanding, this will be the color of the border as well.
            $bgColor = imagecolorallocate($output, $this->_bgColor[0], $this->_bgColor[1], $this->_bgColor[2]);
            if ($bgColor === false) {
                throw new \RuntimeException('Failed to allocate background color.');
            }
            if (imagefilledrectangle($output, 0, 0, $cnv_w - 1, $cnv_h - 1, $bgColor) === false) {
                throw new \RuntimeException('Failed to fill image with background color.');
            }

            // Copy the resized (and resampled) image onto the new canvas.
            if (imagecopyresampled($output, $source, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) === false) {
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

            // Write the result to disk.
            $tempFile = null;
            try {
                $tempFile = $this->_makeTempFile();
                if (imagejpeg($output, $tempFile, self::JPEG_QUALITY) === false) {
                    throw new \RuntimeException('Failed to create JPEG image file.');
                }
                $this->_outputFile = $tempFile;
            } catch (\Exception $e) {
                $this->_outputFile = null;
                if ($tempFile !== null && is_file($tempFile)) {
                    @unlink($tempFile);
                }
                throw $e; // Re-throw.
            }
        } finally {
            @imagedestroy($output);
        }
    }

    /**
     * @param resource $resource
     *
     * @throws \Exception
     * @throws \RuntimeException
     */
    protected function _processResource(
        $resource)
    {
        $x1 = $y1 = 0;
        $x2 = $width = $this->_width;
        $y2 = $height = $this->_height;

        // Check aspect ratio and crop/expand to fit requirements if needed.
        if ($this->_minAspectRatio !== null && $this->_aspectRatio < $this->_minAspectRatio) {
            $aspectRatio = $this->_minAspectRatio;
            if ($this->_operation === self::CROP) {
                // We need to limit the height, so floor is used intentionally to
                // AVOID rounding height upwards to a still-illegal aspect ratio.
                $height = floor($this->_width / $this->_minAspectRatio);

                // Crop vertical images from top by default, to keep faces, etc.
                $cropFocus = $this->_cropFocus !== null ? $this->_cropFocus : -50;

                // Apply fix for flipped images.
                if ($this->_isVerFlipped) {
                    $cropFocus = -$cropFocus;
                }

                // Calculate difference and divide it by cropFocus.
                $diff = $this->_height - $height;
                $y1 = round($diff * (50 + $cropFocus) / 100);
                $y2 = $y2 - ($diff - $y1);
            } elseif ($this->_operation === self::EXPAND) {
                // We need to expand the width with left/right borders. We use
                // ceil to guarantee that the final image is wide enough to be
                // above the minimum allowed aspect ratio.
                // NOTE: Beware that it may actually exceed maxAspectRatio if
                // their values are very close to each other! For example with
                // 450x600 input and min/max aspect of 1.2625, it'll create a
                // 758x600 expanded image (ratio 1.2633). That's unavoidable.
                $width = ceil($this->_height * $this->_minAspectRatio);
            }
        } elseif ($this->_maxAspectRatio !== null && $this->_aspectRatio > $this->_maxAspectRatio) {
            $aspectRatio = $this->_maxAspectRatio;
            if ($this->_operation === self::CROP) {
                // We need to limit the width. We use floor to guarantee cutting
                // enough pixels, since our width exceeds the maximum allowed ratio.
                $width = floor($this->_height * $this->_maxAspectRatio);

                // Crop horizontal images from center by default.
                $cropFocus = $this->_cropFocus !== null ? $this->_cropFocus : 0;

                // Apply fix for flipped images.
                if ($this->_isHorFlipped) {
                    $cropFocus = -$cropFocus;
                }

                // Calculate difference and divide it by cropFocus.
                $diff = $this->_width - $width;
                $x1 = round($diff * (50 + $cropFocus) / 100);
                $x2 = $x2 - ($diff - $x1);
            } elseif ($this->_operation === self::EXPAND) {
                // We need to expand the height with top/bottom borders. We use
                // ceil to guarantee that the final image is tall enough to be
                // below the maximum allowed aspect ratio.
                // NOTE: Beware that it may actually be below minAspectRatio if
                // their values are very close to each other! For example with
                // 600x450 input and min/max aspect of 0.8625, it'll create a
                // 600x696 expanded image (ratio 0.86206). That's unavoidable.
                $height = ceil($this->_width / $this->_maxAspectRatio);
            }
        } else {
            // The image's aspect ratio is already within the legal range.
            $aspectRatio = $this->_aspectRatio;
        }

        // Handle square target ratios or too-large target dimensions.
        if ($aspectRatio == 1) {
            // Ratio = 1: Square.
            // NOTE: Our square will be the size of the shortest side when
            // cropping or the longest side when expanding, but never more
            // than the maximum allowed image width by Instagram.
            $squareWidth = $this->_operation === self::CROP ? min($width, $height) : max($width, $height);
            if ($squareWidth > self::MAX_WIDTH) {
                $squareWidth = self::MAX_WIDTH;
            }
            $width = $height = $squareWidth;
        } else {
            // All other ratios: Ensure the final width fits Instagram's limit.
            // If ratio > 1: Landscape (wider than tall). Limit by width.
            // If ratio < 1: Portrait (taller than wide). Limit by width.
            // NOTE: Maximum "allowed" height is 1350, which is EXACTLY what you
            // get with a maxwidth of 1080 / 0.8 (4:5 aspect ratio). Instagram
            // enforces width & aspect ratio, which in turn auto-limits height.
            if ($width > self::MAX_WIDTH) {
                // Target exceeds Instagram's pixel-limit. Set width to max.
                $width = self::MAX_WIDTH;
                // Re-calculate the target height via our chosen aspect ratio.
                // NOTE: Must use ceil() if aspect ratio is above 1 (landscape),
                // otherwise result may not be tall enough to get a legal ratio!
                // This is safe since the height will always be lower than its
                // original even via ceil(), since we've reduced the width.
                $height = $aspectRatio > 1 ? ceil($width / $aspectRatio) : floor($width / $aspectRatio);
            }
        }

        // Determine the image operation's resampling parameters and perform it.
        if ($this->_operation === self::CROP) {
            // Cropping coordinates are swapped for rotated images.
            if (!$this->_isRotated) {
                $this->_createNewImage($resource, $x1, $y1, $x2 - $x1, $y2 - $y1, 0, 0, $width, $height, $width, $height);
            } else {
                $this->_createNewImage($resource, $y1, $x1, $y2 - $y1, $x2 - $x1, 0, 0, $height, $width, $height, $width);
            }
        } elseif ($this->_operation === self::EXPAND) {
            // For expansion, we'll calculate all operation parameters now. We
            // ignore all of the various x/y and crop-focus parameters used by
            // the cropping code above. None of them are used for expansion!

            // We'll create a new canvas with the desired dimensions.
            $cnv_w = !$this->_isRotated ? $width : $height;
            $cnv_h = !$this->_isRotated ? $height : $width;

            // Always copy from the absolute top left of the original image.
            $src_x = $src_y = 0;

            // We'll copy the entire input image onto the new canvas.
            $src_w = !$this->_isRotated ? $this->_width : $this->_height;
            $src_h = !$this->_isRotated ? $this->_height : $this->_width;

            // Determine the target dimensions to fit it on the new canvas,
            // because the input image's dimensions may have been too large.
            // This will not scale anything (uses scale=1) if the input fits.
            // NOTE: We use ceil to guarantee that it'll never scale a side
            // badly and leave a 1px gap between the image and canvas sides.
            // Also note that ceil will never produce bad values, since PHP
            // allows the dst_w/dst_h to exceed beyond canvas dimensions!
            $scale = min($cnv_w / $src_w, $cnv_h / $src_h);
            $dst_w = ceil($scale * $src_w);
            $dst_h = ceil($scale * $src_h);

            // Now calculate the centered destination offset on the canvas.
            $dst_x = floor(($cnv_w - $dst_w) / 2);
            $dst_y = floor(($cnv_h - $dst_h) / 2);

            // Create the new, expanded image!
            $this->_createNewImage($resource, $src_x, $src_y, $src_w, $src_h, $dst_x, $dst_y, $dst_w, $dst_h, $cnv_w, $cnv_h);
        }
    }

    /**
     * @throws \Exception
     * @throws \RuntimeException
     */
    protected function _process()
    {
        // Read the correct input file format.
        switch ($this->_imageType) {
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

        // Attempt to process the input file.
        try {
            $this->_processResource($resource);
        } finally {
            @imagedestroy($resource);
        }
    }
}
