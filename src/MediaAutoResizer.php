<?php

namespace InstagramAPI;

use InstagramAPI\Media\Dimensions;
use InstagramAPI\Media\ImageResizer;
use InstagramAPI\Media\Rectangle;
use InstagramAPI\Media\ResizerInterface;
use InstagramAPI\Media\VideoResizer;

/**
 * Automatic media resizer.
 *
 * Resizes and crops/expands a media file to match Instagram's requirements,
 * if necessary. You can also use this with your own parameters, to force your
 * media into different aspects, ie square, or for adding borders to media.
 *
 * Usage:
 *
 * - Create an instance of the class with your media file and requirements.
 * - Call getFile() to get the path to a media file matching the requirements.
 *   This will be the same as the input file if no processing was required.
 * - Optionally, call deleteFile() if you want to delete the temporary file
 *   ahead of time instead of automatically when PHP does its object garbage
 *   collection. This function is safe and won't delete the original input file.
 *
 * Remember to thank Abyr Valg for the brilliant media processing algorithm!
 *
 * @author Abyr Valg <valga.github@abyrga.ru>
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class MediaAutoResizer
{
    /** @var int Crop Operation. */
    const CROP = 1;

    /** @var int Expand Operation. */
    const EXPAND = 2;

    /**
     * Lowest allowed general media aspect ratio (4:5, meaning portrait).
     *
     * These are decided by Instagram. Not by us!
     *
     * A different value (MIN_STORY_RATIO) will be used for story media.
     *
     * @var float
     *
     * @see https://help.instagram.com/1469029763400082
     */
    const MIN_RATIO = 0.8;

    /**
     * Highest allowed general media aspect ratio (1.91:1, meaning landscape).
     *
     * These are decided by Instagram. Not by us!
     *
     * A different value (MAX_STORY_RATIO) will be used for story media.
     *
     * @var float
     */
    const MAX_RATIO = 1.91;

    /**
     * Lowest allowed story aspect ratio.
     *
     * This range was decided through community research, which revealed that
     * all Instagram stories are in ~9:16 (0.5625, widescreen portrait) ratio,
     * with a small range of similar portrait ratios also being used sometimes.
     *
     * We have selected a photo/video story aspect range which supports all
     * story media aspects that are commonly used by the app: 0.56 - 0.67.
     * (That's ~1080x1611 to ~1080x1928.)
     *
     * However, note that we'll target the "best story aspect ratio range"
     * by default and that you must manually disable that constructor option
     * to get this extended story aspect range, if you REALLY want it...
     *
     * @var float
     *
     * @see https://github.com/mgp25/Instagram-API/issues/1420#issuecomment-318146010
     */
    const MIN_STORY_RATIO = 0.56;

    /**
     * Highest allowed story aspect ratio.
     *
     * This range was decided through community research.
     *
     * @var float
     */
    const MAX_STORY_RATIO = 0.67;

    /**
     * The best story aspect ratio.
     *
     * This is exactly 9:16 ratio, meaning a standard widescreen phone viewed in
     * portrait mode. It is the most common story ratio on Instagram, and it's
     * the one that looks the best on most devices. All other ratios will look
     * "cropped" when viewed on 16:9 widescreen devices, since the app "zooms"
     * the story until it fills the screen without any black bars. So unless the
     * story is exactly 16:9, it won't look great on 16:9 screens.
     *
     * Every manufacturer uses 16:9 screens. Even Apple since the iPhone 5.
     *
     * Therefore, this will be the final target aspect ratio used EVERY time
     * that media destined for a story feed is outside of the allowed range!
     * That's because it doesn't make sense to let people target non-9:16 final
     * story aspect ratios, since only 9:16 stories look good on most devices!
     *
     * @var float
     */
    const BEST_STORY_RATIO = 0.5625;

    /**
     * Lowest ratio allowed when enforcing the best story aspect ratio.
     *
     * These constants are used instead of MIN_STORY_RATIO and MAX_STORY_RATIO
     * whenever the user tells us to "use the best ~9:16 story ratio" (which is
     * enabled by default). We need to allow a bit above/below it to prevent
     * pointless processing when the media is a few pixels off from the perfect
     * ratio, since the perfect story ratio is often impossible to hit unless
     * the input media is already exactly 720x1280 or 1080x1920.
     *
     * @var float
     */
    const BEST_MIN_STORY_RATIO = 0.56;

    /**
     * Highest ratio allowed when enforcing the best story aspect ratio.
     *
     * @var float
     */
    const BEST_MAX_STORY_RATIO = 0.565;

    /**
     * Override for the default temp path used by all class instances.
     *
     * If you don't provide any tmpPath to the constructor, we'll use this value
     * instead (if non-null). Otherwise we'll use the default system tmp folder.
     *
     * TIP: If your default system temp folder isn't writable, it's NECESSARY
     * for you to set this value to another, writable path, like this:
     *
     * \InstagramAPI\MediaAutoResizer::$defaultTmpPath = '/home/example/foo/';
     */
    public static $defaultTmpPath = null;

    /** @var string Input file path. */
    protected $_inputFile;

    /** @var string Target feed (either "story" or "general"). */
    protected $_targetFeed;

    /** @var float|null Minimum allowed aspect ratio. */
    protected $_minAspectRatio;

    /** @var float|null Maximum allowed aspect ratio. */
    protected $_maxAspectRatio;

    /** @var int Crop focus position (-50 .. 50) when cropping. */
    protected $_cropFocus;

    /** @var array Background color [R, G, B] for the final media. */
    protected $_bgColor;

    /** @var int Operation to perform on the media. */
    protected $_operation;

    /** @var string Path to a tmp directory. */
    protected $_tmpPath;

    /** @var string Output file path. */
    protected $_outputFile;

    /** @var ResizerInterface The media resizer for our input file. */
    protected $_resizer;

    /**
     * Constructor.
     *
     * @param string $inputFile Path to an input file.
     * @param array  $options   An associative array of optional parameters, including:
     *                          "targetFeed" (int) - One of the FEED_X constants, MUST be used if you're targeting stories, defaults to FEED_TIMELINE;
     *                          "cropFocus" (int) - Crop focus position (-50 .. 50) when cropping, uses intelligent guess if not set;
     *                          "minAspectRatio" (float) - Minimum allowed aspect ratio, uses auto-selected class constants if not set;
     *                          "maxAspectRatio" (float) - Maximum allowed aspect ratio, uses auto-selected class constants if not set;
     *                          "useBestStoryRatio" (bool) - Enabled by default and affects which min/max aspect class constants are auto-selected for stories;
     *                          "bgColor" (array) - Array with 3 color components [R, G, B] (0-255/0x00-0xFF) for the background, uses white if not set;
     *                          "operation" (int) - Operation to perform on the media (CROP or EXPAND), uses self::CROP if not set;
     *                          "tmpPath" (string) - Path to temp directory, uses system temp location or class-default if not set.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct(
        $inputFile,
        array $options = [])
    {
        // Assign variables for all options, to avoid bulky code repetition.
        $targetFeed = isset($options['targetFeed']) ? $options['targetFeed'] : Constants::FEED_TIMELINE;
        $cropFocus = isset($options['cropFocus']) ? $options['cropFocus'] : null;
        $minAspectRatio = isset($options['minAspectRatio']) ? $options['minAspectRatio'] : null;
        $maxAspectRatio = isset($options['maxAspectRatio']) ? $options['maxAspectRatio'] : null;
        $useBestStoryRatio = isset($options['useBestStoryRatio']) ? (bool) $options['useBestStoryRatio'] : true;
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

        // Target feed. Turn it into a string for easier processing,
        // since we only care about story ratios vs general ratios.
        switch ($targetFeed) {
        case Constants::FEED_STORY:
        case Constants::FEED_DIRECT_STORY:
            $targetFeed = 'story';
            break;
        default:
            $targetFeed = 'general';
        }
        $this->_targetFeed = $targetFeed;

        // Determine the legal min/max aspect ratios for the target feed.
        if ($targetFeed === 'story') {
            if ($useBestStoryRatio) { // On by default.
                $allowedMinRatio = self::BEST_MIN_STORY_RATIO;
                $allowedMaxRatio = self::BEST_MAX_STORY_RATIO;
            } else {
                $allowedMinRatio = self::MIN_STORY_RATIO;
                $allowedMaxRatio = self::MAX_STORY_RATIO;
            }
        } else {
            $allowedMinRatio = self::MIN_RATIO;
            $allowedMaxRatio = self::MAX_RATIO;
        }

        // Select allowed aspect ratio range based on defaults and user input.
        if ($minAspectRatio !== null && ($minAspectRatio < $allowedMinRatio || $minAspectRatio > $allowedMaxRatio)) {
            throw new \InvalidArgumentException(sprintf('Minimum aspect ratio must be between %.3f and %.3f.',
                $allowedMinRatio, $allowedMaxRatio));
        } elseif ($minAspectRatio === null) {
            $minAspectRatio = $allowedMinRatio;
        }
        if ($maxAspectRatio !== null && ($maxAspectRatio < $allowedMinRatio || $maxAspectRatio > $allowedMaxRatio)) {
            throw new \InvalidArgumentException(sprintf('Maximum aspect ratio must be between %.3f and %.3f.',
                $allowedMinRatio, $allowedMaxRatio));
        } elseif ($maxAspectRatio === null) {
            $maxAspectRatio = $allowedMaxRatio;
        }
        if ($minAspectRatio !== null && $maxAspectRatio !== null && $minAspectRatio > $maxAspectRatio) {
            throw new \InvalidArgumentException('Maximum aspect ratio must be greater than or equal to minimum.');
        }
        $this->_minAspectRatio = $minAspectRatio;
        $this->_maxAspectRatio = $maxAspectRatio;

        // Background color.
        if ($bgColor !== null && (!is_array($bgColor) || count($bgColor) !== 3 || !isset($bgColor[0]) || !isset($bgColor[1]) || !isset($bgColor[2]))) {
            throw new \InvalidArgumentException('The background color must be a 3-element array [R, G, B].');
        } elseif ($bgColor === null) {
            $bgColor = [255, 255, 255]; // White.
        }
        $this->_bgColor = $bgColor;

        // Media operation.
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

        // Create an appropriate media resizer based on the input media type.
        $fileType = $this->_determineFileType($this->_inputFile);
        if ($fileType === 'image') {
            $this->_resizer = new ImageResizer($this->_inputFile, $this->_tmpPath, $this->_bgColor);
        } elseif ($fileType === 'video') {
            $this->_resizer = new VideoResizer($this->_inputFile, $this->_tmpPath, $this->_bgColor);
        } else {
            throw new \InvalidArgumentException('Unsupported input media type.');
        }
    }

    /**
     * Determines the media type of a file.
     *
     * @param string $filePath The file to evaluate.
     *
     * @return string|null Either "image", "video" or NULL (if another type).
     */
    protected function _determineFileType(
        $filePath)
    {
        $fileType = null;

        // Use PHP's binary MIME-type heuristic if available. It ignores file
        // extension and is therefore more accurate at finding the real type.
        $mimeType = false;
        if (function_exists('mime_content_type')) {
            $mimeType = @mime_content_type($filePath);
        }

        // Now determine whether the file is an image or a video.
        if ($mimeType !== false) {
            if (strncmp($mimeType, 'image/', 6) === 0) {
                $fileType = 'image';
            } elseif (strncmp($mimeType, 'video/', 6) === 0) {
                $fileType = 'video';
            }
        } else {
            // Fallback to guessing based on file-extension if MIME unavailable.
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            if (preg_match('#^(jpe?g|png|gif|bmp)$#iD', $extension)) {
                $fileType = 'image';
            } elseif (preg_match('#^(3g2|3gp|asf|asx|avi|dvb|f4v|fli|flv|fvt|h261|h263|h264|jpgm|jpgv|jpm|m1v|m2v|m4u|m4v|mj2|mjp2|mk3d|mks|mkv|mng|mov|movie|mp4|mp4v|mpe|mpeg|mpg|mpg4|mxu|ogv|pyv|qt|smv|uvh|uvm|uvp|uvs|uvu|uvv|uvvh|uvvm|uvvp|uvvs|uvvu|uvvv|viv|vob|webm|wm|wmv|wmx|wvx)$#iD', $extension)) {
                $fileType = 'video';
            }
        }

        return $fileType;
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
     * the media again to a new temp file if the input file required processing.
     *
     * @return bool
     */
    public function deleteFile()
    {
        // Only delete if outputfile exists and isn't the same as input file.
        if ($this->_outputFile !== null && $this->_outputFile !== $this->_inputFile && is_file($this->_outputFile)) {
            $result = @unlink($this->_outputFile);
            $this->_outputFile = null; // Reset so getFile() will work again.
            return $result;
        }

        return true;
    }

    /**
     * Gets the path to a media file matching the requirements.
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
     * @return string The path to the media file.
     *
     * @see MediaAutoResizer::_shouldProcess() For the criteria that determines processing.
     */
    public function getFile()
    {
        if ($this->_outputFile === null) {
            $this->_outputFile = $this->_shouldProcess() ? $this->_process() : $this->_inputFile;
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
        $inputDimensions = $this->_resizer->getInputDimensions();
        $inputWidth = $inputDimensions->getWidth();
        $inputAspectRatio = $inputWidth / $inputDimensions->getHeight();

        // Process if width < minimum allowed.
        if ($inputWidth < $this->_resizer->getMinWidth()) {
            return true;
        }

        // Process if width > maximum allowed.
        if ($inputWidth > $this->_resizer->getMaxWidth()) {
            return true;
        }

        // Process if aspect ratio < minimum allowed.
        if ($this->_minAspectRatio !== null && $inputAspectRatio < $this->_minAspectRatio) {
            return true;
        }

        // Process if aspect ratio > maximum allowed.
        if ($this->_maxAspectRatio !== null && $inputAspectRatio > $this->_maxAspectRatio) {
            return true;
        }

        // Process if the media resizer sees any other problems with the input
        // file (such as needing rotation or media format transcoding).
        if ($this->_resizer->isProcessingRequired()) {
            return true;
        }

        // No need to do any processing.
        return false;
    }

    /**
     * Process the input file and create the new file.
     *
     * @throws \RuntimeException
     *
     * @return string The path to the new file.
     */
    protected function _process()
    {
        // Get the dimensions of the original input file.
        $inputDimensions = $this->_resizer->getInputDimensions();
        $inputWidth = $inputDimensions->getWidth();
        $inputHeight = $inputDimensions->getHeight();

        // Initialize target canvas to original input dimensions & aspect ratio.
        $targetWidth = $inputWidth;
        $targetHeight = $inputHeight;
        $targetAspectRatio = $inputWidth / $inputHeight;

        // Initialize the crop-shifting variables. These control what range of
        // X/Y coordinates we'll copy from the ORIGINAL input to final canvas.
        $x1 = $y1 = 0;
        $x2 = $inputWidth;
        $y2 = $inputHeight;

        // Check aspect ratio and crop/expand final canvas to fit aspect if needed.
        $useFloorHeightRecalc = true; // Height-behavior in any later re-calculations.
        if ($this->_minAspectRatio !== null && $targetAspectRatio < $this->_minAspectRatio) {
            $useFloorHeightRecalc = true; // Use floor() so height is above minAspectRatio.
            // Determine target ratio; in case of stories we always target 9:16.
            $targetAspectRatio = $this->_targetFeed === 'story' ? self::BEST_STORY_RATIO : $this->_minAspectRatio;
            if ($this->_operation === self::CROP) {
                // We need to limit the height, so floor is used intentionally to
                // AVOID rounding height upwards to a still-illegal aspect ratio.
                $targetHeight = floor($targetWidth / $targetAspectRatio);

                // We must also calculate cropped input height, for focus-shift math.
                $inputCroppedHeight = floor($inputWidth / $targetAspectRatio);

                // Crop vertical media from top by default, to keep faces, etc.
                $cropFocus = $this->_cropFocus !== null ? $this->_cropFocus : -50;

                // Invert the cropFocus if this is vertically flipped media.
                if ($this->_resizer->isVerFlipped()) {
                    $cropFocus = -$cropFocus;
                }

                // Calculate difference and divide it by cropFocus to get shift.
                $diff = $inputHeight - $inputCroppedHeight;
                $y1 = round($diff * (50 + $cropFocus) / 100);
                $y2 = $y2 - ($diff - $y1);
            } elseif ($this->_operation === self::EXPAND) {
                // We need to expand the width with left/right borders. We use
                // ceil to guarantee that the final media is wide enough to be
                // above the minimum allowed aspect ratio.
                // NOTE: Beware that it may actually exceed maxAspectRatio if
                // their values are very close to each other! For example with
                // 450x600 input and min/max aspect of 1.2625, it'll create a
                // 758x600 expanded media (ratio 1.2633). That's unavoidable.
                $targetWidth = ceil($targetHeight * $targetAspectRatio);
            }
        } elseif ($this->_maxAspectRatio !== null && $targetAspectRatio > $this->_maxAspectRatio) {
            $useFloorHeightRecalc = false; // Use ceil() so height is below maxAspectRatio.
            // Determine target ratio; in case of stories we always target 9:16.
            $targetAspectRatio = $this->_targetFeed === 'story' ? self::BEST_STORY_RATIO : $this->_maxAspectRatio;
            if ($this->_operation === self::CROP) {
                // We need to limit the width. We use floor to guarantee cutting
                // enough pixels, since our width exceeds the maximum allowed ratio.
                $targetWidth = floor($targetHeight * $targetAspectRatio);

                // We must also calculate cropped input width, for focus-shift math.
                $inputCroppedWidth = floor($inputHeight * $targetAspectRatio);

                // Crop horizontal media from center by default.
                $cropFocus = $this->_cropFocus !== null ? $this->_cropFocus : 0;

                // Invert the cropFocus if this is horizontally flipped media.
                if ($this->_resizer->isHorFlipped()) {
                    $cropFocus = -$cropFocus;
                }

                // Calculate difference and divide it by cropFocus to get shift.
                $diff = $inputWidth - $inputCroppedWidth;
                $x1 = round($diff * (50 + $cropFocus) / 100);
                $x2 = $x2 - ($diff - $x1);
            } elseif ($this->_operation === self::EXPAND) {
                // We need to expand the height with top/bottom borders. We use
                // ceil to guarantee that the final media is tall enough to be
                // below the maximum allowed aspect ratio.
                // NOTE: Beware that it may actually be below minAspectRatio if
                // their values are very close to each other! For example with
                // 600x450 input and min/max aspect of 0.8625, it'll create a
                // 600x696 expanded media (ratio 0.86206). That's unavoidable.
                $targetHeight = ceil($targetWidth / $targetAspectRatio);
            }
        } else {
            // The media's aspect ratio is already within the legal range, but
            // we'll still need to set up a proper height re-calc variable if
            // our input needs to be re-scaled based on width limits further
            // below. So determine whether the input is closest to min or max.
            $minAspectDistance = abs(($this->_minAspectRatio !== null ? $this->_minAspectRatio : 0) - $targetAspectRatio);
            $maxAspectDistance = abs(($this->_maxAspectRatio !== null ? $this->_maxAspectRatio : 0) - $targetAspectRatio);
            // If it's closest to minimum allowed ratio, we'll use floor() to
            // ensure the result is above the minimum ratio. Otherwise we'll use
            // ceil() to ensure that the result is below the maximum ratio.
            $useFloorHeightRecalc = ($minAspectDistance < $maxAspectDistance);
        }

        // Handle square target ratios by making the final canvas into a square.
        if ($targetAspectRatio == 1) { // Ratio 1 = Square.
            // NOTE: Our square will be the size of the shortest side when
            // cropping or the longest side when expanding.
            $targetWidth = $targetHeight = $this->_operation === self::CROP
                         ? min($targetWidth, $targetHeight)
                         : max($targetWidth, $targetHeight);
        }

        // Lastly, enforce minimum and maximum width limits on our final canvas.
        // NOTE: Instagram only enforces width & aspect ratio, which in turn
        // auto-limits height (since we can only use legal height ratios).
        $mustRecalcHeight = false;
        if ($targetWidth > $this->_resizer->getMaxWidth()) {
            $targetWidth = $this->_resizer->getMaxWidth();
            $mustRecalcHeight = true;
        } elseif ($targetWidth < $this->_resizer->getMinWidth()) {
            $targetWidth = $this->_resizer->getMinWidth();
            $mustRecalcHeight = true;
        }
        if ($mustRecalcHeight) {
            // Use floor() or ceil() depending on whether we need the resulting
            // aspect ratio to be either >= or <= the target aspect ratio.
            $targetHeight = $useFloorHeightRecalc
                          ? floor($targetWidth / $targetAspectRatio) // >=
                          : ceil($targetWidth / $targetAspectRatio); // <=
        }

        // Determine the media operation's resampling parameters and perform it.
        if ($this->_operation === self::CROP) {
            $canvas = new Dimensions($targetWidth, $targetHeight);
            $srcRect = new Rectangle($x1, $y1, $x2 - $x1, $y2 - $y1);
            $dstRect = new Rectangle(0, 0, $targetWidth, $targetHeight);
        } elseif ($this->_operation === self::EXPAND) {
            // For expansion, we'll calculate all operation parameters now. We
            // ignore all of the various x/y and crop-focus parameters used by
            // the cropping code above. None of them are used for expansion!

            // We'll create a new canvas with the desired dimensions.
            $canvas = new Dimensions($targetWidth, $targetHeight);

            // We'll copy the entire original input media onto the new canvas.
            // Always copy from the absolute top left of the original media.
            $srcRect = new Rectangle(0, 0, $inputWidth, $inputHeight);

            // Determine the target dimensions to fit it on the new canvas,
            // because the input media's dimensions may have been too large.
            // This will not scale anything (uses scale=1) if the input fits.
            // NOTE: We use ceil to guarantee that it'll never scale a side
            // badly and leave a 1px gap between the media and canvas sides.
            // Also note that ceil will never produce bad values, since PHP
            // allows the dst_w/dst_h to exceed beyond canvas dimensions!
            $scale = min($canvas->getWidth() / $srcRect->getWidth(), $canvas->getHeight() / $srcRect->getHeight());
            $dst_w = ceil($scale * $srcRect->getWidth());
            $dst_h = ceil($scale * $srcRect->getHeight());

            // Now calculate the centered destination offset on the canvas.
            $dst_x = floor(($canvas->getWidth() - $dst_w) / 2);
            $dst_y = floor(($canvas->getHeight() - $dst_h) / 2);

            // Build the final destination rectangle for the expanded canvas!
            $dstRect = new Rectangle($dst_x, $dst_y, $dst_w, $dst_h);
        } else {
            throw new \RuntimeException(sprintf('Unsupported operation: %s.', $this->_operation));
        }

        return $this->_resizer->resize($srcRect, $dstRect, $canvas);
    }
}
