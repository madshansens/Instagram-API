<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method string getClientSidecarId()
 * @method Model\Item getMedia()
 * @method string getUploadId()
 * @method bool isClientSidecarId()
 * @method bool isMedia()
 * @method bool isUploadId()
 * @method setClientSidecarId(string $value)
 * @method setMedia(Model\Item $value)
 * @method setUploadId(string $value)
 */
class ConfigureResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var string
     */
    public $upload_id;
    /**
     * @var Model\Item
     */
    public $media;
    /**
     * @var string
     */
    public $client_sidecar_id;
}
