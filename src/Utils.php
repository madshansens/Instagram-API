<?php

namespace InstagramAPI;

use InstagramAPI\Request\Metadata\MediaDetails;
use InstagramAPI\Request\Metadata\PhotoDetails;
use InstagramAPI\Request\Metadata\VideoDetails;
use InstagramAPI\Response\Model\Item;

class Utils
{
    /**
     * Override for the default temp path used by various class functions.
     *
     * If this value is non-null, we'll use it. Otherwise we'll use the default
     * system tmp folder.
     *
     * TIP: If your default system temp folder isn't writable, it's NECESSARY
     * for you to set this value to another, writable path, like this:
     *
     * \InstagramAPI\Utils::$defaultTmpPath = '/home/example/foo/';
     */
    public static $defaultTmpPath = null;

    /**
     * Used for multipart boundary generation.
     *
     * @var string
     */
    const BOUNDARY_CHARS = '-_1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Length of generated multipart boundary.
     *
     * @var int
     */
    const BOUNDARY_LENGTH = 30;

    /**
     * Name of the detected ffmpeg executable, or FALSE if none found.
     *
     * @var string|bool|null
     */
    public static $ffmpegBin = null;

    /**
     * Name of the detected ffprobe executable, or FALSE if none found.
     *
     * @var string|bool|null
     */
    public static $ffprobeBin = null;

    /**
     * Last uploadId generated with microtime().
     *
     * @var string|null
     */
    protected static $_lastUploadId = null;

    /**
     * @param bool $useNano Whether to return result in usec instead of msec.
     *
     * @return string
     */
    public static function generateUploadId(
        $useNano = false)
    {
        if (!$useNano) {
            $result = number_format(round(microtime(true) * 1000), 0, '', '');
            if (self::$_lastUploadId !== null && $result === self::$_lastUploadId) {
                // NOTE: Fast machines can process files too quick (< 0.001 sec), which leads to
                // identical upload IDs, which leads to "500 Oops, an error occurred" errors.
                // So we sleep 0.001 sec to ensure that we will get different upload IDs per each call.
                usleep(1000);
                $result = number_format(round(microtime(true) * 1000), 0, '', '');
            }
            self::$_lastUploadId = $result;
        } else {
            // Emulate System.nanoTime().
            $result = number_format(microtime(true) - strtotime('Last Monday'), 6, '', '');
            // Append nanoseconds.
            $result .= str_pad((string) mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        }

        return $result;
    }

    /**
     * Calculates Java hashCode() for a given string.
     *
     * WARNING: This method is not Unicode-aware, so use it only on ANSI strings.
     *
     * @param string $string
     *
     * @return int
     *
     * @see https://en.wikipedia.org/wiki/Java_hashCode()#The_java.lang.String_hash_function
     */
    public static function hashCode(
        $string)
    {
        $result = 0;
        for ($i = 0, $len = strlen($string); $i < $len; $i++) {
            $result = (-$result + ($result << 5) + ord($string[$i])) & 0xFFFFFFFF;
        }
        if (PHP_INT_SIZE > 4) {
            if ($result > 0x7FFFFFFF) {
                $result -= 0x100000000;
            } elseif ($result < -0x80000000) {
                $result += 0x100000000;
            }
        }

        return $result;
    }

    /**
     * Reorders array by hashCode() of its keys.
     *
     * @param array $data
     *
     * @return array
     */
    public static function reorderByHashCode(
        array $data)
    {
        $hashCodes = [];
        foreach ($data as $key => $value) {
            $hashCodes[$key] = self::hashCode($key);
        }

        uksort($data, function ($a, $b) use ($hashCodes) {
            $a = $hashCodes[$a];
            $b = $hashCodes[$b];
            if ($a < $b) {
                return -1;
            } elseif ($a > $b) {
                return 1;
            } else {
                return 0;
            }
        });

        return $data;
    }

    /**
     * Generates random multipart boundary string.
     *
     * @return string
     */
    public static function generateMultipartBoundary()
    {
        $result = '';
        $max = strlen(self::BOUNDARY_CHARS) - 1;
        for ($i = 0; $i < self::BOUNDARY_LENGTH; ++$i) {
            $result .= self::BOUNDARY_CHARS[mt_rand(0, $max)];
        }

        return $result;
    }

    /**
     * Generates user breadcrumb for use when posting a comment.
     *
     * @return string
     */
    public static function generateUserBreadcrumb(
        $size)
    {
        $key = 'iN4$aGr0m';
        $date = (int) (microtime(true) * 1000);

        // typing time
        $term = rand(2, 3) * 1000 + $size * rand(15, 20) * 100;

        // android EditText change event occur count
        $text_change_event_count = round($size / rand(2, 3));
        if ($text_change_event_count == 0) {
            $text_change_event_count = 1;
        }

        // generate typing data
        $data = $size.' '.$term.' '.$text_change_event_count.' '.$date;

        return base64_encode(hash_hmac('sha256', $data, $key, true))."\n".base64_encode($data)."\n";
    }

    /**
     * Check for ffmpeg/avconv dependencies.
     *
     * TIP: If your binary isn't findable via the PATH environment locations,
     * you can manually set the correct path to it. Before calling any functions
     * that need FFmpeg, you must simply assign a manual value (ONCE) to tell us
     * where to find your FFmpeg, like this:
     *
     * \InstagramAPI\Utils::$ffmpegBin = '/home/exampleuser/ffmpeg/bin/ffmpeg';
     *
     * @return string|bool Name of the library if present, otherwise FALSE.
     */
    public static function checkFFMPEG()
    {
        // We only resolve this once per session and then cache the result.
        if (self::$ffmpegBin === null) {
            @exec('ffmpeg -version 2>&1', $output, $statusCode);
            if ($statusCode === 0) {
                self::$ffmpegBin = 'ffmpeg';
            } else {
                @exec('avconv -version 2>&1', $output, $statusCode);
                if ($statusCode === 0) {
                    self::$ffmpegBin = 'avconv';
                } else {
                    self::$ffmpegBin = false; // Nothing found!
                }
            }
        }

        return self::$ffmpegBin;
    }

    /**
     * Check for ffprobe dependency.
     *
     * TIP: If your binary isn't findable via the PATH environment locations,
     * you can manually set the correct path to it. Before calling any functions
     * that need FFprobe, you must simply assign a manual value (ONCE) to tell
     * us where to find your FFprobe, like this:
     *
     * \InstagramAPI\Utils::$ffprobeBin = '/home/exampleuser/ffmpeg/bin/ffprobe';
     *
     * @return string|bool Name of the library if present, otherwise FALSE.
     */
    public static function checkFFPROBE()
    {
        // We only resolve this once per session and then cache the result.
        if (self::$ffprobeBin === null) {
            @exec('ffprobe -version 2>&1', $output, $statusCode);
            if ($statusCode === 0) {
                self::$ffprobeBin = 'ffprobe';
            } else {
                self::$ffprobeBin = false; // Nothing found!
            }
        }

        return self::$ffprobeBin;
    }

    /**
     * Get detailed information about a photo file.
     *
     * @param string $photoFilename Path to the photo file.
     *
     * @throws \InvalidArgumentException If the photo file is missing or invalid.
     *
     * @return array Int image type, width and height.
     */
    public static function getPhotoFileDetails(
        $photoFilename)
    {
        // Check if input file exists.
        if (empty($photoFilename) || !is_file($photoFilename)) {
            throw new \InvalidArgumentException(sprintf('The photo file "%s" does not exist on disk.', $photoFilename));
        }

        // Get image details.
        $result = @getimagesize($photoFilename);
        if ($result === false) {
            throw new \InvalidArgumentException(sprintf('The photo file "%s" is not a valid image.', $photoFilename));
        }

        return [
            'width'  => $result[0],
            'height' => $result[1],
            'type'   => $result[2],
        ];
    }

    /**
     * Get detailed information about a video file.
     *
     * This also validates that a file is actually a video, since FFmpeg will
     * fail to read details from badly broken / non-video files.
     *
     * @param string $videoFilename Path to the video file.
     *
     * @throws \InvalidArgumentException If the video file is missing.
     * @throws \RuntimeException         If FFmpeg isn't working properly.
     *
     * @return array Video codec name, float duration, int width and height.
     */
    public static function getVideoFileDetails(
        $videoFilename)
    {
        // The user must have FFprobe.
        $ffprobe = self::checkFFPROBE();
        if ($ffprobe === false) {
            throw new \RuntimeException('You must have FFprobe to analyze video details.');
        }

        // Check if input file exists.
        if (empty($videoFilename) || !is_file($videoFilename)) {
            throw new \InvalidArgumentException(sprintf('The video file "%s" does not exist on disk.', $videoFilename));
        }

        // Load with FFPROBE. Shows details as JSON and exits.
        $command = escapeshellarg($ffprobe).' -v quiet -print_format json -show_format -show_streams '.escapeshellarg($videoFilename);
        $jsonInfo = @shell_exec($command);

        // Check for processing errors.
        if ($jsonInfo === null) {
            throw new \RuntimeException(sprintf('FFprobe failed to analyze your video file "%s".', $videoFilename));
        }

        // Attempt to decode the JSON.
        $probeResult = @json_decode($jsonInfo, true);
        if ($probeResult === null) {
            throw new \RuntimeException(sprintf('FFprobe gave us invalid JSON for "%s".', $videoFilename));
        }

        // Now analyze all streams to find the first video stream.
        // We ignore all audio and subtitle streams.
        $videoDetails = [];
        foreach ($probeResult['streams'] as $streamIdx => $streamInfo) {
            if ($streamInfo['codec_type'] == 'video') {
                $videoDetails['codec'] = $streamInfo['codec_name']; // string
                $videoDetails['width'] = intval($streamInfo['width'], 10);
                $videoDetails['height'] = intval($streamInfo['height'], 10);
                // NOTE: Duration is a float such as "230.138000".
                $videoDetails['duration'] = floatval($streamInfo['duration']);

                break; // Stop checking streams.
            }
        }

        // Make sure we have found format details.
        if (count($videoDetails) === 0) {
            throw new \RuntimeException(sprintf('FFprobe failed to detect any video format details. Is "%s" a valid video file?', $videoFilename));
        }

        return $videoDetails;
    }

    /**
     * Verifies that a piece of media follows Instagram's size/aspect rules.
     *
     * Currently all photos and videos everywhere have the exact same rules.
     * We bring in the up-to-date rules from the ImageAutoResizer class.
     *
     * @param string       $targetFeed   Target feed for this media ("timeline", "story", "direct_story", "album" or "direct_v2").
     * @param MediaDetails $mediaDetails Media details.
     *
     * @throws \InvalidArgumentException If Instagram won't allow this file.
     *
     * @see ImageAutoResizer
     */
    public static function throwIfIllegalMediaResolution(
        $targetFeed,
        MediaDetails $mediaDetails)
    {
        $width = $mediaDetails->getWidth();
        $height = $mediaDetails->getHeight();
        // WARNING TO CONTRIBUTORS: $mediaFilename is for ERROR DISPLAY to
        // users. Do NOT use it to read from the hard disk!
        $mediaFilename = $mediaDetails->getFilename();
        // Check Resolution.
        if ($mediaDetails instanceof PhotoDetails) {
            // Validate photo resolution.
            if ($width > ImageAutoResizer::MAX_WIDTH || $height > ImageAutoResizer::MAX_HEIGHT) {
                throw new \InvalidArgumentException(sprintf(
                    'Instagram only accepts photos with a maximum resolution up to %dx%d. Your file "%s" has a %dx%d resolution.',
                    ImageAutoResizer::MAX_WIDTH, ImageAutoResizer::MAX_HEIGHT, $mediaFilename, $width, $height
                ));
            }
        } elseif ($mediaDetails instanceof VideoDetails) {
            // Validate video resolution. Instagram allows between 320px-1080px width.
            // NOTE: They have height-limits too, but we automatically enforce
            // those when validating the aspect ratio further down.
            if ($width < 320 || $width > 1080) {
                throw new \InvalidArgumentException(sprintf(
                    'Instagram only accepts videos that are between 320 and 1080 pixels wide. Your file "%s" is %d pixels wide.',
                    $mediaFilename, $width
                ));
            }

            // TODO: Support for landscape/portrait photos and videos in
            // Instagram Direct is coming soon to Android:
            // http://blog.instagram.com/post/161060792262/
            if ($targetFeed == 'direct_v2' && $width != $height) {
                throw new \InvalidArgumentException('Instagram Direct only accepts square videos.');
            }
        }

        // Check Aspect Ratio.
        // NOTE: This Instagram rule is the same for both videos and photos.
        // See ImageAutoResizer for the latest up-to-date allowed ratios.
        $aspectRatio = $width / $height;
        if ($aspectRatio < ImageAutoResizer::MIN_RATIO || $aspectRatio > ImageAutoResizer::MAX_RATIO) {
            throw new \InvalidArgumentException(sprintf(
                'Instagram only accepts media with aspect ratios between %.2f and %.2f. Your file "%s" has a %.2f aspect ratio.',
                ImageAutoResizer::MIN_RATIO, ImageAutoResizer::MAX_RATIO, $mediaFilename, $aspectRatio
            ));
        }
    }

    /**
     * Verifies that a video's details follow Instagram's requirements.
     *
     * @param string       $targetFeed   Target feed for this media ("timeline", "story", "direct_story", "album" or "direct_v2").
     * @param VideoDetails $videoDetails Video details.
     *
     * @throws \InvalidArgumentException If Instagram won't allow this video.
     */
    public static function throwIfIllegalVideoDetails(
        $targetFeed,
        VideoDetails $videoDetails)
    {
        $videoFilename = $videoDetails->getFilename();
        // Validate video length.
        // NOTE: Instagram has no disk size limit, but this length validation
        // also ensures we can only upload small files exactly as intended.
        $duration = $videoDetails->getDuration();
        switch ($targetFeed) {
        case 'story':
            // Instagram only allows 3-15 seconds for stories.
            if ($duration < 3 || $duration > 15) {
                throw new \InvalidArgumentException(sprintf(
                    'Instagram only accepts story videos that are between 3 and 15 seconds long. Your story video "%s" is %.3f seconds long.',
                    $videoFilename, $duration
                ));
            }
            break;
        case 'direct_v2':
        case 'direct_story':
            // Instagram only allows 0.1-15 seconds for direct messages.
            if ($duration < 0.1 || $duration > 15) {
                throw new \InvalidArgumentException(sprintf(
                    'Instagram only accepts direct videos that are between 0.1 and 15 seconds long. Your direct video "%s" is %.3f seconds long.',
                    $videoFilename, $duration
                ));
            }
            break;
        default: // timeline
            // Validate video length. Instagram only allows 3-60 seconds.
            // SEE: https://help.instagram.com/270963803047681
            if ($duration < 3 || $duration > 60) {
                throw new \InvalidArgumentException(sprintf(
                    'Instagram only accepts videos that are between 3 and 60 seconds long. Your video "%s" is %.3f seconds long.',
                    $videoFilename, $duration
                ));
            }
        }

        // Validate video resolution and aspect ratio.
        self::throwIfIllegalMediaResolution($targetFeed, $videoDetails);
    }

    /**
     * Verifies that a photo's details follow Instagram's requirements.
     *
     * @param string       $targetFeed   Target feed for this photo ("timeline", "story", "direct_story", "album" or "direct_v2").
     * @param PhotoDetails $photoDetails Photo details.
     *
     * @throws \InvalidArgumentException If Instagram won't allow this photo.
     */
    public static function throwIfIllegalPhotoDetails(
        $targetFeed,
        PhotoDetails $photoDetails)
    {
        $photoFilename = $photoDetails->getFilename();
        // Validate image type.
        // NOTE: It is confirmed that Instagram accepts only JPEG.
        $type = $photoDetails->getType();
        if ($type !== IMAGETYPE_JPEG) {
            throw new \InvalidArgumentException(sprintf('The photo file "%s" is not JPEG.', $photoFilename));
        }

        // Validate photo resolution and aspect ratio.
        self::throwIfIllegalMediaResolution($targetFeed, $photoDetails);
    }

    /**
     * Generate a video icon/thumbnail from a video file.
     *
     * Automatically guarantees that the generated image follows Instagram's
     * allowed image specifications, so that there won't be any upload issues.
     *
     * @param string $videoFilename Path to the video file.
     *
     * @throws \InvalidArgumentException If the video file is missing.
     * @throws \RuntimeException         If FFmpeg isn't working properly.
     *
     * @return string The JPEG binary data for the generated thumbnail.
     */
    public static function createVideoIcon(
        $videoFilename)
    {
        // The user must have FFmpeg.
        $ffmpeg = self::checkFFMPEG();
        if ($ffmpeg === false) {
            throw new \RuntimeException('You must have FFmpeg to generate video thumbnails.');
        }

        // Check if input file exists.
        if (empty($videoFilename) || !is_file($videoFilename)) {
            throw new \InvalidArgumentException(sprintf('The video file "%s" does not exist on disk.', $videoFilename));
        }

        // Generate a temp thumbnail filename and delete if file already exists.
        $tmpPath = self::$defaultTmpPath !== null
                   ? self::$defaultTmpPath
                   : sys_get_temp_dir();
        $tmpFilename = $tmpPath.'/'.md5($videoFilename).'.jpg';
        if (is_file($tmpFilename)) {
            @unlink($tmpFilename);
        }

        try {
            // Capture a video preview snapshot to that file via FFMPEG.
            $command = escapeshellarg($ffmpeg).' -i '.escapeshellarg($videoFilename).' -f mjpeg -ss 00:00:01 -vframes 1 '.escapeshellarg($tmpFilename).' 2>&1';
            @exec($command, $output, $statusCode);

            // Check for processing errors.
            if ($statusCode !== 0) {
                throw new \RuntimeException('FFmpeg failed to generate a video thumbnail.');
            }

            // Automatically crop&resize the thumbnail to Instagram's requirements.
            $resizer = new ImageAutoResizer($tmpFilename);
            $jpegContents = file_get_contents($resizer->getFile()); // Process&get.
            $resizer->deleteFile();

            return $jpegContents;
        } finally {
            @unlink($tmpFilename);
        }
    }

    /**
     * Verifies an array of media usertags.
     *
     * Ensures that the input strictly contains the exact keys necessary for
     * usertags, and with proper values for them. We cannot validate that the
     * user-id's actually exist, but that's the job of the library user!
     *
     * @param array $usertags The array of usertags, optionally with the "in" or
     *                        "removed" top-level keys holding the usertags. Example:
     *                        ['in'=>[['position'=>[0.5,0.5],'user_id'=>'123'], ...]].
     *
     * @throws \InvalidArgumentException If any tags are invalid.
     */
    public static function throwIfInvalidUsertags(
        array $usertags)
    {
        if (count($usertags) < 1) {
            throw new \InvalidArgumentException('Empty usertags array.');
        }

        foreach ($usertags as $k => $v) {
            if ($k === 'in' || $k === 'removed') {
                if (!is_array($v)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Invalid usertags array. The value for key "%s" must be an array.', $k
                    ));
                }

                // Skip the section if it's empty.
                if (count($v) < 1) {
                    continue;
                }

                // Handle ['in'=>[...], 'removed'=>[...]] top-level keys since
                // this input contained top-level array keys containing the usertags.
                if ($k === 'in') {
                    // Check the array of usertags to insert.
                    self::throwIfInvalidUsertags($v);
                } else { // 'removed'
                    // Check the array of userids to remove.
                    foreach ($v as $userId) {
                        if (!ctype_digit($userId) && (!is_int($userId) || $userId < 0)) {
                            throw new \InvalidArgumentException('Invalid user ID in usertags "removed" array.');
                        }
                    }
                }
            } else {
                // Verify this usertag entry, ensuring that the entry is format
                // ['position'=>[0.0,1.0],'user_id'=>'123'] and nothing else.
                if (!is_array($v) || count($v) != 2 || !isset($v['position'])
                    || !is_array($v['position']) || count($v['position']) != 2
                    || !isset($v['position'][0]) || !isset($v['position'][1])
                    || (!is_int($v['position'][0]) && !is_float($v['position'][0]))
                    || (!is_int($v['position'][1]) && !is_float($v['position'][1]))
                    || $v['position'][0] < 0.0 || $v['position'][0] > 1.0
                    || $v['position'][1] < 0.0 || $v['position'][1] > 1.0
                    || !isset($v['user_id']) || !is_scalar($v['user_id'])
                    || (!ctype_digit($v['user_id']) && (!is_int($v['user_id']) || $v['user_id'] < 0))) {
                    throw new \InvalidArgumentException('Invalid user entry in usertags array.');
                }
            }
        }
    }

    /**
     * Checks and validates a media item's type.
     *
     * @param string|int $mediaType The type of the media item. One of: "PHOTO", "VIDEO"
     *                              "ALBUM", or the raw value of the Item's "getMediaType()" function.
     *
     * @throws \InvalidArgumentException If the type is invalid.
     *
     * @return string The verified final type; either "PHOTO", "VIDEO" or "ALBUM".
     */
    public static function checkMediaType(
        $mediaType)
    {
        if (ctype_digit($mediaType) || is_int($mediaType)) {
            if ($mediaType == Item::PHOTO) {
                $mediaType = 'PHOTO';
            } elseif ($mediaType == Item::VIDEO) {
                $mediaType = 'VIDEO';
            } elseif ($mediaType == Item::ALBUM) {
                $mediaType = 'ALBUM';
            }
        }
        if (!in_array($mediaType, ['PHOTO', 'VIDEO', 'ALBUM'], true)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid media type.', $mediaType));
        }

        return $mediaType;
    }

    public static function formatBytes(
        $bytes,
        $precision = 2)
    {
        $units = ['B', 'kB', 'mB', 'gB', 'tB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).''.$units[$pow];
    }

    public static function colouredString(
        $string,
        $colour)
    {
        $colours['black'] = '0;30';
        $colours['dark_gray'] = '1;30';
        $colours['blue'] = '0;34';
        $colours['light_blue'] = '1;34';
        $colours['green'] = '0;32';
        $colours['light_green'] = '1;32';
        $colours['cyan'] = '0;36';
        $colours['light_cyan'] = '1;36';
        $colours['red'] = '0;31';
        $colours['light_red'] = '1;31';
        $colours['purple'] = '0;35';
        $colours['light_purple'] = '1;35';
        $colours['brown'] = '0;33';
        $colours['yellow'] = '1;33';
        $colours['light_gray'] = '0;37';
        $colours['white'] = '1;37';

        $colored_string = '';

        if (isset($colours[$colour])) {
            $colored_string .= "\033[".$colours[$colour].'m';
        }

        $colored_string .= $string."\033[0m";

        return $colored_string;
    }

    public static function getFilterCode(
        $filter)
    {
        $filters = [];
        $filters[0] = 'Normal';
        $filters[615] = 'Lark';
        $filters[614] = 'Reyes';
        $filters[613] = 'Juno';
        $filters[612] = 'Aden';
        $filters[608] = 'Perpetua';
        $filters[603] = 'Ludwig';
        $filters[605] = 'Slumber';
        $filters[616] = 'Crema';
        $filters[24] = 'Amaro';
        $filters[17] = 'Mayfair';
        $filters[23] = 'Rise';
        $filters[26] = 'Hudson';
        $filters[25] = 'Valencia';
        $filters[1] = 'X-Pro II';
        $filters[27] = 'Sierra';
        $filters[28] = 'Willow';
        $filters[2] = 'Lo-Fi';
        $filters[3] = 'Earlybird';
        $filters[22] = 'Brannan';
        $filters[10] = 'Inkwell';
        $filters[21] = 'Hefe';
        $filters[15] = 'Nashville';
        $filters[18] = 'Sutro';
        $filters[19] = 'Toaster';
        $filters[20] = 'Walden';
        $filters[14] = '1977';
        $filters[16] = 'Kelvin';
        $filters[-2] = 'OES';
        $filters[-1] = 'YUV';
        $filters[109] = 'Stinson';
        $filters[106] = 'Vesper';
        $filters[112] = 'Clarendon';
        $filters[118] = 'Maven';
        $filters[114] = 'Gingham';
        $filters[107] = 'Ginza';
        $filters[113] = 'Skyline';
        $filters[105] = 'Dogpatch';
        $filters[115] = 'Brooklyn';
        $filters[111] = 'Moon';
        $filters[117] = 'Helena';
        $filters[116] = 'Ashby';
        $filters[108] = 'Charmes';
        $filters[640] = 'BrightContrast';
        $filters[642] = 'CrazyColor';
        $filters[643] = 'SubtleColor';

        return array_search($filter, $filters);
    }

    /**
     * Creates a folder if missing, or ensures that it is writable.
     *
     * @param string $folder The directory path.
     *
     * @return bool TRUE if folder exists and is writable, otherwise FALSE.
     */
    public static function createFolder(
        $folder)
    {
        // Test write-permissions for the folder and create/fix if necessary.
        if ((is_dir($folder) && is_writable($folder))
            || (!is_dir($folder) && mkdir($folder, 0755, true))
            || chmod($folder, 0755)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Recursively deletes a file/directory tree.
     *
     * @param string $folder         The directory path.
     * @param bool   $keepRootFolder Whether to keep the top-level folder.
     *
     * @return bool TRUE on success, otherwise FALSE.
     */
    public static function deleteTree(
        $folder,
        $keepRootFolder = false)
    {
        // Handle bad arguments.
        if (empty($folder) || !file_exists($folder)) {
            return true; // No such file/folder exists.
        } elseif (is_file($folder) || is_link($folder)) {
            return @unlink($folder); // Delete file/link.
        }

        // Delete all children.
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            if (!@$action($fileinfo->getRealPath())) {
                return false; // Abort due to the failure.
            }
        }

        // Delete the root folder itself?
        return !$keepRootFolder ? @rmdir($folder) : true;
    }

    /**
     * Atomic filewriter.
     *
     * Safely writes new contents to a file using an atomic two-step process.
     * If the script is killed before the write is complete, only the temporary
     * trash file will be corrupted.
     *
     * @param string $filename     Filename to write the data to.
     * @param string $data         Data to write to file.
     * @param string $atomicSuffix Lets you optionally provide a different
     *                             suffix for the temporary file.
     *
     * @return mixed Number of bytes written on success, otherwise FALSE.
     */
    public static function atomicWrite(
        $filename,
        $data,
        $atomicSuffix = 'atomictmp')
    {
        // Perform an exclusive (locked) overwrite to a temporary file.
        $filenameTmp = sprintf('%s.%s', $filename, $atomicSuffix);
        $writeResult = @file_put_contents($filenameTmp, $data, LOCK_EX);
        if ($writeResult !== false) {
            // Now move the file to its real destination (replaced if exists).
            $moveResult = @rename($filenameTmp, $filename);
            if ($moveResult === true) {
                // Successful write and move. Return number of bytes written.
                return $writeResult;
            }
        }

        return false; // Failed.
    }
}
