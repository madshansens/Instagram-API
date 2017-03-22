<?php

namespace InstagramAPI;

class Utils
{
    /**
     * Name of the detected ffmpeg executable, or FALSE if none found.
     *
     * @var string|bool|null
     */
    public static $ffmpegBin = null;

    /**
     * @return string
     */
    public static function generateUploadId()
    {
        return number_format(round(microtime(true) * 1000), 0, '', '');
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
     * Get detailed information about a video file.
     *
     * This also validates that a file is actually a video, since FFmpeg will
     * fail to read details from badly broken / non-video files.
     *
     * @param string $videoFilename Path to the video file.
     *
     * @throws \InvalidArgumentException If the video file is missing.
     * @throws \RuntimeException         If FFmpeg isn't working properly.
     * @throws \Exception                In case of various processing errors.
     *
     * @return array Video codec name, float duration, int width and height.
     */
    public static function getVideoFileDetails(
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

        // Load with FFMPEG. Shows details and exits, since we give no outfile.
        $command = $ffmpeg.' -hide_banner -i '.escapeshellarg($videoFilename).' 2>&1';
        @exec($command, $output, $statusCode);

        // Extract the video details if available.
        $videoDetails = [
            'codec'    => '', // string
            'duration' => -1.0, // float (length in seconds, with decimals)
            'width'    => -1, // int
            'height'   => -1, //int
        ];
        foreach ($output as $line) {
            if (preg_match('/Video: (\S+)[^,]*, [^,]+, (\d+)x(\d+)/', $line, $matches)) {
                $videoDetails['codec'] = $matches[1];
                $videoDetails['width'] = intval($matches[2], 10);
                $videoDetails['height'] = intval($matches[3], 10);
            }
            if (preg_match('/Duration: (\d{2}):(\d{2}):(\d{2}\.\d{2})/', $line, $matches)) {
                $videoDetails['duration'] = (float) ($matches[1] * 3600 + $matches[2] * 60 + floatval($matches[3]));
            }
        }

        // Verify that we have ALL details.
        // NOTE: Since width+height are found together with codec, we only need
        // to check 1 of those 3 fields, so I'm checking the codec field.
        if ($videoDetails['duration'] < 0 || $videoDetails['codec'] === '') {
            throw new \RuntimeException('FFmpeg failed to detect the video format details. Is this a valid video file?');
        }

        return $videoDetails;
    }

    /**
     * Verifies that a video's details follow Instagram's requirements.
     *
     * @param string $type          What type of video ("timeline", "story" or "album").
     * @param string $videoFilename The video filename.
     * @param array  $videoDetails  An array created by getVideoFileDetails().
     *
     * @throws \InvalidArgumentException If Instagram won't allow this video.
     */
    public static function throwIfIllegalVideoDetails(
        $type,
        $videoFilename,
        array $videoDetails)
    {
        // Validate video length.
        // NOTE: Instagram has no disk size limit, but this length validation
        // also ensures we can only upload small files exactly as intended.
        if ($type == 'story') {
            // Instagram only allows 3-15 seconds for stories.
            if ($videoDetails['duration'] < 3 || $videoDetails['duration'] > 15) {
                throw new \InvalidArgumentException(sprintf('Instagram only accepts story videos that are between 3 and 15 seconds long. Your story video "%s" is %d seconds long.', $videoFilename, $videoDetails['duration']));
            }
        } else {
            // Validate video length. Instagram only allows 3-60 seconds.
            //SEE: https://help.instagram.com/270963803047681
            if ($videoDetails['duration'] < 3 || $videoDetails['duration'] > 60) {
                throw new \InvalidArgumentException(sprintf('Instagram only accepts videos that are between 3 and 60 seconds long. Your video "%s" is %d seconds long.', $videoFilename, $videoDetails['duration']));
            }
        }

        // Validate resolution. Instagram allows between 320px-1080px width.
        if (($videoDetails['width'] < 320 )|| ($videoDetails['width'] > 1080)) {
            throw new \InvalidArgumentException(sprintf('Instagram only accepts videos that are between 320 and 1080 pixels wide. Your video "%s" is %d pixels wide.', $videoFilename, $videoDetails['width']));
        }

        // Validate aspect ratio. Instagram has SAME requirements as for photos!
        // NOTE: See ImageAutoResizer for latest up-to-date allowed ratios.
        $aspectRatio = $videoDetails['width'] / $videoDetails['height'];
        if ($aspectRatio < ImageAutoResizer::MIN_RATIO || $aspectRatio > ImageAutoResizer::MAX_RATIO) {
            throw new \InvalidArgumentException(sprintf('Instagram only accepts videos with aspect ratios between %.2f and %.2f. Your video "%s" has a %.2f aspect ratio.', ImageAutoResizer::MIN_RATIO, ImageAutoResizer::MAX_RATIO, $videoFilename, $aspectRatio));
        }
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
     * @throws \Exception                In case of various processing errors.
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
        $tmpFilename = sys_get_temp_dir().'/'.md5($videoFilename).'.jpg';
        if (is_file($tmpFilename)) {
            @unlink($tmpFilename);
        }

        try {
            // Capture a video preview snapshot to that file via FFMPEG.
            $command = $ffmpeg.' -i '.escapeshellarg($videoFilename).' -f singlejpeg -ss 00:00:01 -vframes 1 '.escapeshellarg($tmpFilename).' 2>&1';
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
        $filters[108] = 'Charmes';
        $filters[116] = 'Ashby';
        $filters[117] = 'Helena';
        $filters[115] = 'Brooklyn';
        $filters[105] = 'Dogpatch';
        $filters[113] = 'Skyline';
        $filters[107] = 'Ginza';
        $filters[118] = 'Maven';
        $filters[16] = 'Kelvin';
        $filters[14] = '1977';
        $filters[20] = 'Walden';
        $filters[19] = 'Toaster';
        $filters[18] = 'Sutro';
        $filters[22] = 'Brannan';
        $filters[3] = 'Earlybird';
        $filters[106] = 'Vesper';
        $filters[109] = 'Stinson';
        $filters[15] = 'Nashville';
        $filters[21] = 'Hefe';
        $filters[10] = 'Inkwell';
        $filters[2] = 'Lo-Fi';
        $filters[28] = 'Willow';
        $filters[27] = 'Sierra';
        $filters[1] = 'X Pro II';
        $filters[25] = 'Valencia';
        $filters[26] = 'Hudson';
        $filters[23] = 'Rise';
        $filters[17] = 'Mayfair';
        $filters[24] = 'Amaro';
        $filters[608] = 'Perpetua';
        $filters[612] = 'Aden';
        $filters[603] = 'Ludwig';
        $filters[616] = 'Crema';
        $filters[605] = 'Slumber';
        $filters[613] = 'Juno';
        $filters[614] = 'Reyes';
        $filters[615] = 'Lark';
        $filters[111] = 'Moon';
        $filters[114] = 'Gingham';
        $filters[112] = 'Clarendon';
        $filters[0] = 'Normal';

        return array_search($filter, $filters);
    }
}
