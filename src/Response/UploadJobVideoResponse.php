<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method string getUploadId()
 * @method Model\VideoUploadUrl[] getVideoUploadUrls()
 * @method bool isUploadId()
 * @method bool isVideoUploadUrls()
 * @method setUploadId(string $value)
 * @method setVideoUploadUrls(Model\VideoUploadUrl[] $value)
 */
class UploadJobVideoResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var string
     */
    public $upload_id;
    /** @var Model\VideoUploadUrl[] */
    public $video_upload_urls;
}
