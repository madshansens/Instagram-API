<?php

namespace InstagramAPI\Media;

use InstagramAPI\Media\Photo\PhotoResizer;
use InstagramAPI\Media\Video\VideoResizer;

class ResizerFactory
{
    /**
     * Determines the media type of a file.
     *
     * @param string $filePath The file to evaluate.
     *
     * @return string|null Either "image", "video" or NULL (if another type).
     */
    protected static function _determineFileType(
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
     * Get an appropriate resizer class name based on a given source file.
     *
     * @param string $filePath The source file.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function detectResizerForFile(
        $filePath)
    {
        // Create an appropriate media resizer based on the input media type.
        $fileType = self::_determineFileType($filePath);
        switch ($fileType) {
            case 'image':
                $result = PhotoResizer::class;
                break;
            case 'video':
                $result = VideoResizer::class;
                break;
            default:
                throw new \InvalidArgumentException('Unsupported input media type.');
        }

        return $result;
    }
}
