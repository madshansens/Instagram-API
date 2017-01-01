<?php

namespace InstagramAPI;

class Utils
{
    /**
     * @return string
     */
    public static function generateUploadId()
    {
        return number_format(round(microtime(true) * 1000), 0, '', '');
    }

    /**
     * Length of the file in Seconds.
     *
     * @param string $file
     *                     path to the file name
     *
     * @return int
     *             length of the file in seconds
     */
    public static function getSeconds($file)
    {
        $ffmpeg = self::checkFFMPEG();
        if ($ffmpeg) {
            $time = exec("$ffmpeg -i ".$file." 2>&1 | grep 'Duration' | cut -d ' ' -f 4");
            $duration = explode(':', $time);
            $seconds = $duration[0] * 3600 + $duration[1] * 60 + round($duration[2]);

            return $seconds;
        }

        return mt_rand(15, 300);
    }

    /**
     * Check for ffmpeg/avconv dependencies.
     *
     * @return string/boolean
     *                        name of the library if present, false otherwise
     */
    public static function checkFFMPEG()
    {
        @exec('ffmpeg -version 2>&1', $output, $returnvalue);
        if ($returnvalue === 0) {
            return 'ffmpeg';
        }
        @exec('avconv -version 2>&1', $output, $returnvalue);
        if ($returnvalue === 0) {
            return 'avconv';
        }

        return false;
    }

    /**
     * Creating a video icon/thumbnail.
     *
     * @param string $file
     *                     path to the video file
     *
     * @return image
     *               icon/thumbnail for the video
     */
    public static function createVideoIcon($file)
    {
        /* should install ffmpeg for the method to work successfully  */
        $ffmpeg = self::checkFFMPEG();
        if ($ffmpeg) {
            //generate thumbnail
            $preview = sys_get_temp_dir().'/'.md5($file).'.jpg';
            @unlink($preview);

            //capture video preview
            $command = $ffmpeg.' -i "'.$file.'" -f mjpeg -ss 00:00:01 -vframes 1 "'.$preview.'" 2>&1';
            @exec($command);

            return file_get_contents($preview);
        }
    }

    /**
     * Implements the actual logic behind creating the icon/thumbnail.
     *
     * @param string $file
     *                     path to the file name
     *
     * @return image
     *               icon/thumbnail for the video
     */
    public static function createIconGD($file, $size = 100, $raw = true)
    {
        list($width, $height) = getimagesize($file);
        if ($width > $height) {
            $y = 0;
            $x = ($width - $height) / 2;
            $smallestSide = $height;
        } else {
            $x = 0;
            $y = ($height - $width) / 2;
            $smallestSide = $width;
        }

        $image_p = imagecreatetruecolor($size, $size);
        $image = imagecreatefromstring(file_get_contents($file));

        imagecopyresampled($image_p, $image, 0, 0, $x, $y, $size, $size, $smallestSide, $smallestSide);
        ob_start();
        imagejpeg($image_p, null, 95);
        $i = ob_get_contents();
        ob_end_clean();

        imagedestroy($image);
        imagedestroy($image_p);

        return $i;
    }

    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'kB', 'mB', 'gB', 'tB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).''.$units[$pow];
    }

    public static function colouredString($string, $colour)
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

    public static function getFilterCode($filter)
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
