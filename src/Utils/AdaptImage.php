<?php

namespace InstagramAPI;

class AdaptImage
{
    protected $sizes;
    protected $images;
    protected $width;
    protected $height;
    protected $newWidth;
    protected $newHeight;
    protected $x;
    protected $y;

    public function checkAndProcess($photo = null)
    {
        try {
            //Get image size
            if ($photo == null) {
                throw new Exception('Error: You did not specify image.');
                exit;
            }
            $this->images = @imagecreatefromstring($photo);
            $this->width = imagesx($this->images);
            $this->height = imagesy($this->images);
            //IMAGE PROCESS
            if ($this->width <= 320 && $this->height <= 320) {
                $this->newHeight = round((320 / $this->width) * $this->height);
                $this->newWidth = round(($this->newHeight / $this->height) * $this->width);
                $im = imagecreatetruecolor(320, 320);
                $wb = imagecolorallocate($im, 255, 255, 255);
                imagefill($im, 0, 0, $wb);
                $this->x = (320 - $this->newHeight) / 2;
                $this->y = (320 - $this->newWidth) / 2;
                imagecopyresized($im, $this->images, $this->x, $this->y, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
            } elseif ($this->width >= $this->height && $this->width >= 1080) {
                $this->newHeight = round((1080 / $this->width) * $this->height);
                $this->newWidth = round(($this->newHeight / $this->height) * $this->width);
                if ($this->height < 575) {
                    $this->newHeight = 575;
                } elseif ($this->height > 1080) {
                    $this->newHeight = 1080;
                }
                $im = imagecreatetruecolor(1080, 1080);
                $wb = imagecolorallocate($im, 255, 255, 255);
                imagefill($im, 0, 0, $wb);
                if (1080 > $this->newHeight) {
                    $this->y = (1080 - $this->newHeight) / 2;
                } else {
                    $this->y = ($this->newHeight - 1080) / 2;
                }
                if (1080 > $this->newWidth) {
                    $this->x = (1080 - $this->newWidth) / 2;
                } else {
                    $this->x = ($this->newWidth - 1080) / 2;
                }
                imagecopyresized($im, $this->images, $this->x, $this->y, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
            } elseif ($this->width >= $this->height && $this->width < 1080) {
                $this->newHeight = round((640 / $this->width) * $this->height);
                $this->newWidth = round(($this->newHeight / $this->height) * $this->width);
                $im = imagecreatetruecolor(640, $this->newHeight);
                $wb = imagecolorallocate($im, 255, 255, 255);
                imagefill($im, 0, 0, $wb);
                if (640 > $this->newWidth) {
                    $this->x = (640 - $this->newWidth) / 2;
                } else {
                    $this->x = ($this->newWidth - 640) / 2;
                }
                imagecopyresized($im, $this->images, $this->x, $this->y, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
            } elseif ($this->height >= $this->width && $this->height > 1100) {
                $this->newHeight = round((1080 / $this->width) * $this->height);
                $this->newWidth = round((1349 / $this->height) * $this->width);
                $im = imagecreatetruecolor(1080, 1349);
                $wb = imagecolorallocate($im, 255, 255, 255);
                imagefill($im, 0, 0, $wb);
                if (1379 > $this->newHeight) {
                    $this->y = (1379 - $this->newHeight) / 2;
                } else {
                    $this->y = ($this->newHeight - 1379) / 2;
                }
                if (1080 > $this->newWidth) {
                    $this->x = (1080 - $this->newWidth) / 2;
                } else {
                    $this->x = ($this->newWidth - 1080) / 2;
                }
                imagecopyresized($im, $this->images, $this->x, 0, 0, 0, $this->newWidth, 1349, $this->width, $this->height);
            } elseif ($this->height > $this->width && $this->height <= 1100) {
                $this->newHeight = round((640 / $this->width) * $this->height);
                $this->newWidth = round((799 / $this->height) * $this->width);
                $im = imagecreatetruecolor(640, 799);
                $wb = imagecolorallocate($im, 255, 255, 255);
                imagefill($im, 0, 0, $wb);
                if (799 > $this->newHeight) {
                    $this->y = (799 - $this->newHeight) / 2;
                } else {
                    $this->y = ($this->newHeight - 799) / 2;
                }
                if (640 > $this->newWidth) {
                    $this->x = (640 - $this->newWidth) / 2;
                } else {
                    $this->x = ($this->newWidth - 640) / 2;
                }

                imagecopyresized($im, $this->images, $this->x, 0, 0, 0, $this->newWidth, 799, $this->width, $this->height);
            }
            ob_start();
            imagejpeg($im);
            $data = ob_get_contents();
            ob_end_clean();

            return $data;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
