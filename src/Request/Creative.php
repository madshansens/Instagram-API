<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

/**
 * Functions related to Instagram's "creative assets", such as stickers.
 */
class Creative extends RequestCollection
{
    /**
     * Get sticker assets.
     *
     * NOTE: This gives you a list of the stickers that the app can "paste" on
     * top of story media. If you want to use any of them, you will have to
     * apply them MANUALLY via some external image/video editor or library!
     *
     * @param string     $stickerType Type of sticker (currently only "static_stickers").
     * @param null|array $location    (optional) Array containing lat, lng and horizontalAccuracy.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\StickerAssetsResponse
     */
    public function getStickerAssets(
        $stickerType = 'static_stickers',
        array $location = null)
    {
        if ($stickerType != 'static_stickers') {
            throw new \InvalidArgumentException('You must provide a valid sticker type.');
        }
        if (!is_null($location) && (!isset($location['lat'])
                                    || !isset($location['lng'])
                                    || !isset($location['horizontalAccuracy']))) {
            throw new \InvalidArgumentException('Your location array must contain keys for "lat", "lng" and "horizontalAccuracy".');
        }

        $request = $this->ig->request('creatives/assets/')
            ->addPost('type', $stickerType);

        if (!is_null($location)) {
            $request
                ->addPost('lat', $location['lat'])
                ->addPost('lng', $location['lat'])
                ->addPost('horizontalAccuracy', $location['horizontalAccuracy']);
        }

        return $request->getResponse(new Response\StickerAssetsResponse());
    }
}
