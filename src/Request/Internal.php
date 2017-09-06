<?php

namespace InstagramAPI\Request;

use GuzzleHttp\Psr7\LimitStream;
use GuzzleHttp\Psr7\Stream;
use InstagramAPI\Constants;
use InstagramAPI\Exception\CheckpointRequiredException;
use InstagramAPI\Exception\FeedbackRequiredException;
use InstagramAPI\Exception\InstagramException;
use InstagramAPI\Exception\LoginRequiredException;
use InstagramAPI\Exception\NetworkException;
use InstagramAPI\Exception\ThrottledException;
use InstagramAPI\Request;
use InstagramAPI\Request\Metadata\Internal as InternalMetadata;
use InstagramAPI\Response;
use InstagramAPI\ResponseInterface;
use InstagramAPI\Signatures;
use InstagramAPI\Utils;

/**
 * Collection of various INTERNAL library functions.
 *
 * THESE FUNCTIONS ARE NOT FOR PUBLIC USE! DO NOT TOUCH!
 */
class Internal extends RequestCollection
{
    /** @var int Number of retries for each video chunk. */
    const MAX_CHUNK_RETRIES = 5;

    /** @var int Number of retries for resumable uploader. */
    const MAX_RESUMABLE_RETRIES = 15;

    /** @var int Number of retries for each media configuration. */
    const MAX_CONFIGURE_RETRIES = 5;

    /** @var int Minimum video chunk size in bytes. */
    const MIN_CHUNK_SIZE = 204800;

    /** @var int Maximum video chunk size in bytes. */
    const MAX_CHUNK_SIZE = 5242880;

    /**
     * UPLOADS A *SINGLE* PHOTO.
     *
     * This is NOT used for albums!
     *
     * @param int                   $targetFeed       One of the FEED_X constants.
     * @param string                $photoFilename    The photo filename.
     * @param InternalMetadata|null $internalMetadata (optional) Internal library-generated metadata object.
     * @param array                 $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSinglePhoto() for available metadata fields.
     */
    public function uploadSinglePhoto(
        $targetFeed,
        $photoFilename,
        InternalMetadata $internalMetadata = null,
        array $externalMetadata = [])
    {
        // Make sure we only allow these particular feeds for this function.
        if ($targetFeed != Constants::FEED_TIMELINE && $targetFeed != Constants::FEED_STORY && $targetFeed != Constants::FEED_DIRECT_STORY) {
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        if ($internalMetadata === null) {
            $internalMetadata = new InternalMetadata();
        }
        if ($internalMetadata->getPhotoDetails() === null) {
            $internalMetadata->setPhotoDetails($targetFeed, $photoFilename);
        }

        // Perform the upload.
        $internalMetadata->setPhotoUploadResponse($this->uploadPhotoData($targetFeed, $internalMetadata));

        // Configure the uploaded image and attach it to our timeline/story.
        $configure = $this->configureSinglePhoto($targetFeed, $internalMetadata, $externalMetadata);

        return $configure;
    }

    /**
     * Upload the data for a photo to Instagram.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UploadPhotoResponse
     */
    public function uploadPhotoData(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        // Make sure we disallow some feeds for this function.
        if ($targetFeed == Constants::FEED_DIRECT) {
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        $isVideoThumbnail = false;
        // Determine which file contents to upload.
        if ($internalMetadata->getPhotoDetails() !== null) {
            $photoData = file_get_contents($internalMetadata->getPhotoDetails()->getFilename());
        } elseif ($internalMetadata->getVideoDetails() !== null) {
            // Generate a thumbnail from a video file.
            try {
                $photoData = Utils::createVideoIcon($targetFeed, $internalMetadata->getVideoDetails()->getFilename());
            } catch (\Exception $e) {
                // Re-package as InternalException, but keep the stack trace.
                throw new \InstagramAPI\Exception\InternalException($e->getMessage(), 0, $e);
            }
            $isVideoThumbnail = true;
        } else {
            throw new \InvalidArgumentException('Could not find any photo file to upload (the photoDetails and videoDetails are both unset).');
        }

        // Prepare payload for the upload request.
        $request = $this->ig->request('upload/photo/')
            ->setSignedPost(false)
            ->addPost('upload_id', $internalMetadata->getUploadId())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('image_compression', '{"lib_name":"jt","lib_version":"1.3.0","quality":"87"}')
            ->addFileData('photo', $photoData, 'pending_media_'.Utils::generateUploadId().'.jpg');

        if ($targetFeed == Constants::FEED_TIMELINE_ALBUM) {
            $request->addPost('is_sidecar', '1');
            if ($isVideoThumbnail) {
                $request->addPost('media_type', '2');
            }
        }

        return $request->getResponse(new Response\UploadPhotoResponse());
    }

    /**
     * Configures parameters for a *SINGLE* uploaded photo file.
     *
     * WARNING TO CONTRIBUTORS: THIS IS ONLY FOR *TIMELINE* AND *STORY* -PHOTOS-.
     * USE "configureTimelineAlbum()" FOR ALBUMS and "configureSingleVideo()" FOR VIDEOS.
     * AND IF FUTURE INSTAGRAM FEATURES NEED CONFIGURATION AND ARE NON-TRIVIAL,
     * GIVE THEM THEIR OWN FUNCTION LIKE WE DID WITH "configureTimelineAlbum()",
     * TO AVOID ADDING BUGGY AND UNMAINTAINABLE SPIDERWEB CODE!
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     * @param array            $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     */
    public function configureSinglePhoto(
        $targetFeed,
        InternalMetadata $internalMetadata,
        array $externalMetadata = [])
    {
        // Determine the target endpoint for the photo.
        switch ($targetFeed) {
        case Constants::FEED_TIMELINE:
            $endpoint = 'media/configure/';
            break;
        case Constants::FEED_DIRECT_STORY:
        case Constants::FEED_STORY:
            $endpoint = 'media/configure_to_story/';
            break;
        default:
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Available external metadata parameters:
        /** @var string Caption to use for the media. NOT USED FOR STORY MEDIA! */
        $captionText = isset($externalMetadata['caption']) ? $externalMetadata['caption'] : '';
        /** @var Response\Model\Location|null A Location object describing where
         * the media was taken. NOT USED FOR STORY MEDIA! */
        $location = (isset($externalMetadata['location']) && $targetFeed != Constants::FEED_STORY) ? $externalMetadata['location'] : null;
        /** @var array|null Array of usertagging instructions, in the format
         * [['position'=>[0.5,0.5], 'user_id'=>'123'], ...]. ONLY FOR TIMELINE PHOTOS! */
        $usertags = (isset($externalMetadata['usertags']) && $targetFeed == Constants::FEED_TIMELINE) ? $externalMetadata['usertags'] : null;
        /** @var string|null Link to attach to the media. ONLY USED FOR STORY MEDIA,
         * AND YOU MUST HAVE A BUSINESS INSTAGRAM ACCOUNT TO POST A STORY LINK! */
        $link = (isset($externalMetadata['link']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['link'] : null;
        /** @var void Photo filter. THIS DOES NOTHING! All real filters are done in the mobile app. */
        // $filter = isset($externalMetadata['filter']) ? $externalMetadata['filter'] : null;
        $filter = null; // COMMENTED OUT SO USERS UNDERSTAND THEY CAN'T USE THIS!
        /** @var array Hashtags to use for the media. ONLY STORY MEDIA! */
        $hashtags = (isset($externalMetadata['hashtags']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['hashtags'] : null;

        // Fix very bad external user-metadata values.
        if (!is_string($captionText)) {
            $captionText = '';
        }

        // Critically important internal library-generated metadata parameters:
        /** @var string The ID of the entry to configure. */
        $uploadId = $internalMetadata->getPhotoUploadResponse()->getUploadId();
        /** @var int Width of the photo. */
        $photoWidth = $internalMetadata->getPhotoDetails()->getWidth();
        /** @var int Height of the photo. */
        $photoHeight = $internalMetadata->getPhotoDetails()->getHeight();

        // Build the request...
        $request = $this->ig->request($endpoint)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('edits',
                [
                    'crop_original_size'    => [$photoWidth, $photoHeight],
                    'crop_zoom'             => 1,
                    'crop_center'           => [0.0, -0.0],
                ])
            ->addPost('device',
                [
                    'manufacturer'      => $this->ig->device->getManufacturer(),
                    'model'             => $this->ig->device->getModel(),
                    'android_version'   => $this->ig->device->getAndroidVersion(),
                    'android_release'   => $this->ig->device->getAndroidRelease(),
                ])
            ->addPost('extra',
                [
                    'source_width'  => $photoWidth,
                    'source_height' => $photoHeight,
                ]);

        switch ($targetFeed) {
            case Constants::FEED_TIMELINE:
                $request
                    ->addPost('caption', $captionText)
                    ->addPost('source_type', '4')
                    ->addPost('media_folder', 'Camera')
                    ->addPost('upload_id', $uploadId);

                if ($usertags !== null) {
                    $usertags = ['in' => $usertags]; // Wrap in container array.
                    Utils::throwIfInvalidUsertags($usertags);
                    $request->addPost('usertags', json_encode($usertags));
                }
                break;
            case Constants::FEED_STORY:
                $request
                    ->addPost('client_shared_at', (string) time())
                    ->addPost('source_type', '3')
                    ->addPost('configure_mode', '1')
                    ->addPost('client_timestamp', (string) (time() - mt_rand(3, 10)))
                    ->addPost('upload_id', $uploadId);

                if (is_string($link) && Utils::hasValidWebURLSyntax($link)) {
                    $story_cta = '[{"links":[{"webUri":'.json_encode($link).'}]}]';
                    $request->addPost('story_cta', $story_cta);
                }
                if (!is_null($hashtags) && $captionText != '') {
                    Utils::throwIfInvalidStoryHashtags($captionText, $hashtags);
                    $request
                        ->addPost('story_hashtags', json_encode($hashtags))
                        ->addPost('caption', $captionText)
                        ->addPost('mas_opt_in', 'NOT_PROMPTED');
                }
                break;
            case Constants::FEED_DIRECT_STORY:
                $request
                    ->addPost('recipient_users', $internalMetadata->getDirectUsers())
                    ->addPost('thread_ids', $internalMetadata->getDirectThreads())
                    ->addPost('client_shared_at', (string) time())
                    ->addPost('source_type', '3')
                    ->addPost('configure_mode', '2')
                    ->addPost('client_timestamp', (string) (time() - mt_rand(3, 10)))
                    ->addPost('upload_id', $uploadId);
                break;
        }

        if ($location instanceof Response\Model\Location) {
            $request
                ->addPost('location', Utils::buildMediaLocationJSON($location))
                ->addPost('geotag_enabled', '1')
                ->addPost('posting_latitude', $location->getLat())
                ->addPost('posting_longitude', $location->getLng())
                ->addPost('media_latitude', $location->getLat())
                ->addPost('media_longitude', $location->getLng())
                ->addPost('av_latitude', 0.0)
                ->addPost('av_longitude', 0.0);
        }

        $configure = $request->getResponse(new Response\ConfigureResponse());

        return $configure;
    }

    /**
     * Uploads a raw video file.
     *
     * @param int                   $targetFeed       One of the FEED_X constants.
     * @param string                $videoFilename    The video filename.
     * @param InternalMetadata|null $internalMetadata (optional) Internal library-generated metadata object.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video upload fails.
     *
     * @return InternalMetadata Updated internal metadata object.
     */
    public function uploadVideo(
        $targetFeed,
        $videoFilename,
        InternalMetadata $internalMetadata = null)
    {
        if ($internalMetadata === null) {
            $internalMetadata = new InternalMetadata();
        }
        if ($internalMetadata->getVideoDetails() === null) {
            $internalMetadata->setVideoDetails($targetFeed, $videoFilename);
        }

        if ($this->_useResumableUploader($targetFeed, $internalMetadata)) {
            $this->_uploadResumableVideo($targetFeed, $internalMetadata);
        } else {
            // Request parameters for uploading a new video.
            $internalMetadata->setVideoUploadUrls($this->_requestVideoUploadURL($targetFeed, $internalMetadata));

            // Attempt to upload the video data.
            $internalMetadata->setVideoUploadResponse($this->_uploadVideoChunks($targetFeed, $internalMetadata));
        }

        return $internalMetadata;
    }

    /**
     * UPLOADS A *SINGLE* VIDEO.
     *
     * This is NOT used for albums!
     *
     * @param int                   $targetFeed       One of the FEED_X constants.
     * @param string                $videoFilename    The video filename.
     * @param InternalMetadata|null $internalMetadata (optional) Internal library-generated metadata object.
     * @param array                 $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video upload fails.
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSingleVideo() for available metadata fields.
     */
    public function uploadSingleVideo(
        $targetFeed,
        $videoFilename,
        InternalMetadata $internalMetadata = null,
        array $externalMetadata = [])
    {
        // Make sure we only allow these particular feeds for this function.
        if ($targetFeed != Constants::FEED_TIMELINE && $targetFeed != Constants::FEED_STORY && $targetFeed != Constants::FEED_DIRECT_STORY) {
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Attempt to upload the video.
        $internalMetadata = $this->uploadVideo($targetFeed, $videoFilename, $internalMetadata);

        // Attempt to upload the thumbnail, associated with our video's ID.
        $internalMetadata->setPhotoUploadResponse($this->uploadPhotoData($targetFeed, $internalMetadata));

        // Configure the uploaded video and attach it to our timeline/story.
        /** @var \InstagramAPI\Response\ConfigureResponse $configure */
        $configure = $this->ig->internal->configureWithRetries(
            $videoFilename,
            function () use ($targetFeed, $internalMetadata, $externalMetadata) {
                // Attempt to configure video parameters.
                return $this->configureSingleVideo($targetFeed, $internalMetadata, $externalMetadata);
            }
        );

        return $configure;
    }

    /**
     * Asks Instagram for parameters for uploading a new video.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \InstagramAPI\Exception\InstagramException If the request fails.
     *
     * @return \InstagramAPI\Response\UploadJobVideoResponse
     */
    protected function _requestVideoUploadURL(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        $request = $this->ig->request('upload/video/')
            ->setSignedPost(false)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid);

        foreach ($this->_getVideoUploadParams($targetFeed, $internalMetadata) as $key => $value) {
            $request->addPost($key, $value);
        }

        // Perform the "pre-upload" API request.
        /** @var Response\UploadJobVideoResponse $response */
        $response = $request->getResponse(new Response\UploadJobVideoResponse());

        return $response;
    }

    /**
     * Configures parameters for a *SINGLE* uploaded video file.
     *
     * WARNING TO CONTRIBUTORS: THIS IS ONLY FOR *TIMELINE* AND *STORY* -VIDEOS-.
     * USE "configureTimelineAlbum()" FOR ALBUMS and "configureSinglePhoto()" FOR PHOTOS.
     * AND IF FUTURE INSTAGRAM FEATURES NEED CONFIGURATION AND ARE NON-TRIVIAL,
     * GIVE THEM THEIR OWN FUNCTION LIKE WE DID WITH "configureTimelineAlbum()",
     * TO AVOID ADDING BUGGY AND UNMAINTAINABLE SPIDERWEB CODE!
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     * @param array            $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     */
    public function configureSingleVideo(
        $targetFeed,
        InternalMetadata $internalMetadata,
        array $externalMetadata = [])
    {
        // Determine the target endpoint for the video.
        switch ($targetFeed) {
        case Constants::FEED_TIMELINE:
            $endpoint = 'media/configure/';
            break;
        case Constants::FEED_DIRECT_STORY:
        case Constants::FEED_STORY:
            $endpoint = 'media/configure_to_story/';
            break;
        default:
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Available external metadata parameters:
        /** @var string Caption to use for the media. */
        $captionText = isset($externalMetadata['caption']) ? $externalMetadata['caption'] : '';
        /** @var string[]|null Array of numerical UserPK IDs of people tagged in
         * your video. ONLY USED IN STORY VIDEOS! TODO: Actually, it's not even
         * implemented for stories. */
        $usertags = (isset($externalMetadata['usertags']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['usertags'] : null;
        /** @var Response\Model\Location|null A Location object describing where
         * the media was taken. NOT USED FOR STORY MEDIA! */
        $location = (isset($externalMetadata['location']) && $targetFeed != Constants::FEED_STORY) ? $externalMetadata['location'] : null;
        /** @var string|null Link to attach to the media. ONLY USED FOR STORY MEDIA,
         * AND YOU MUST HAVE A BUSINESS INSTAGRAM ACCOUNT TO POST A STORY LINK! */
        $link = (isset($externalMetadata['link']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['link'] : null;
        /** @var array Hashtags to use for the media. ONLY STORY MEDIA! */
        $hashtags = (isset($externalMetadata['hashtags']) && $targetFeed == Constants::FEED_STORY) ? $externalMetadata['hashtags'] : null;

        // Fix very bad external user-metadata values.
        if (!is_string($captionText)) {
            $captionText = '';
        }

        $uploadId = $internalMetadata->getUploadId();
        $videoDetails = $internalMetadata->getVideoDetails();

        // Build the request...
        $request = $this->ig->request($endpoint)
            ->addParam('video', 1)
            ->addPost('video_result', $internalMetadata->getVideoUploadResponse() !== null ? (string) $internalMetadata->getVideoUploadResponse()->getResult() : '')
            ->addPost('upload_id', $uploadId)
            ->addPost('poster_frame_index', 0)
            ->addPost('length', round($videoDetails->getDuration(), 1))
            ->addPost('audio_muted', false)
            ->addPost('filter_type', 0)
            ->addPost('source_type', 4)
            ->addPost('device',
                [
                    'manufacturer'      => $this->ig->device->getManufacturer(),
                    'model'             => $this->ig->device->getModel(),
                    'android_version'   => $this->ig->device->getAndroidVersion(),
                    'android_release'   => $this->ig->device->getAndroidRelease(),
                ])
            ->addPost('extra',
                [
                    'source_width'  => $videoDetails->getWidth(),
                    'source_height' => $videoDetails->getHeight(),
                ])
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id);

        switch ($targetFeed) {
            case Constants::FEED_TIMELINE:
                $request->addPost('caption', $captionText);
                break;
            case Constants::FEED_STORY:
                $request
                    ->addPost('configure_mode', 1) // 1 - REEL_SHARE
                    ->addPost('story_media_creation_date', time() - mt_rand(10, 20))
                    ->addPost('client_shared_at', time() - mt_rand(3, 10))
                    ->addPost('client_timestamp', time());

                if (is_string($link) && Utils::hasValidWebURLSyntax($link)) {
                    $story_cta = '[{"links":[{"webUri":'.json_encode($link).'}]}]';
                    $request->addPost('story_cta', $story_cta);
                }
                if (!is_null($hashtags) && $captionText != '') {
                    Utils::throwIfInvalidStoryHashtags($captionText, $hashtags);
                    $request
                        ->addPost('story_hashtags', json_encode($hashtags))
                        ->addPost('caption', $captionText)
                        ->addPost('mas_opt_in', 'NOT_PROMPTED');
                }
                break;
            case Constants::FEED_DIRECT_STORY:
                $request
                    ->addPost('configure_mode', 2) // 2 - DIRECT_STORY_SHARE
                    ->addPost('recipient_users', $internalMetadata->getDirectUsers())
                    ->addPost('thread_ids', $internalMetadata->getDirectThreads())
                    ->addPost('story_media_creation_date', time() - mt_rand(10, 20))
                    ->addPost('client_shared_at', time() - mt_rand(3, 10))
                    ->addPost('client_timestamp', time());
                break;
        }

        if ($targetFeed == Constants::FEED_STORY) {
            $request->addPost('story_media_creation_date', time());
            if (!is_null($usertags)) {
                // Reel Mention example:
                // [{\"y\":0.3407772676161919,\"rotation\":0,\"user_id\":\"USER_ID\",\"x\":0.39892578125,\"width\":0.5619921875,\"height\":0.06011525487256372}]
                // NOTE: The backslashes are just double JSON encoding, ignore
                // that and just give us an array with these clean values, don't
                // try to encode it in any way, we do all encoding to match the above.
                // This post field will get wrapped in another json_encode call during transfer.
                $request->addPost('reel_mentions', json_encode($usertags));
            }
        }

        if ($location instanceof Response\Model\Location) {
            $request
                ->addPost('location', Utils::buildMediaLocationJSON($location))
                ->addPost('geotag_enabled', '1')
                ->addPost('posting_latitude', $location->getLat())
                ->addPost('posting_longitude', $location->getLng())
                ->addPost('media_latitude', $location->getLat())
                ->addPost('media_longitude', $location->getLng())
                ->addPost('av_latitude', 0.0)
                ->addPost('av_longitude', 0.0);
        }

        $configure = $request->getResponse(new Response\ConfigureResponse());

        return $configure;
    }

    /**
     * Configures parameters for a whole album of uploaded media files.
     *
     * WARNING TO CONTRIBUTORS: THIS IS ONLY FOR *TIMELINE ALBUMS*. DO NOT MAKE
     * IT DO ANYTHING ELSE, TO AVOID ADDING BUGGY AND UNMAINTAINABLE SPIDERWEB
     * CODE!
     *
     * @param array            $media            Extended media array coming from Timeline::uploadAlbum(),
     *                                           containing the user's per-file metadata,
     *                                           and internally generated per-file metadata.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object for the album itself.
     * @param array            $externalMetadata (optional) User-provided metadata key-value pairs
     *                                           for the album itself (its caption, location, etc).
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     */
    public function configureTimelineAlbum(
        array $media,
        InternalMetadata $internalMetadata,
        array $externalMetadata = [])
    {
        $endpoint = 'media/configure_sidecar/';

        $albumUploadId = $internalMetadata->getUploadId();

        // Available external metadata parameters:
        /** @var string Caption to use for the album. */
        $captionText = isset($externalMetadata['caption']) ? $externalMetadata['caption'] : '';
        /** @var Response\Model\Location|null A Location object describing where
         * the album was taken. */
        $location = isset($externalMetadata['location']) ? $externalMetadata['location'] : null;

        // Fix very bad external user-metadata values.
        if (!is_string($captionText)) {
            $captionText = '';
        }

        // Build the album's per-children metadata.
        $date = date('Y:m:d H:i:s');
        $childrenMetadata = [];
        foreach ($media as $item) {
            /** @var InternalMetadata $itemInternalMetadata */
            $itemInternalMetadata = $item['internalMetadata'];
            // Get all of the common, INTERNAL per-file metadata.
            $uploadId = $itemInternalMetadata->getUploadId();

            switch ($item['type']) {
            case 'photo':
                // Build this item's configuration.
                $photoConfig = [
                    'date_time_original'  => $date,
                    'scene_type'          => 1,
                    'disable_comments'    => false,
                    'upload_id'           => $uploadId,
                    'source_type'         => 0,
                    'scene_capture_type'  => 'standard',
                    'date_time_digitized' => $date,
                    'geotag_enabled'      => false,
                    'camera_position'     => 'back',
                    'edits'               => [
                        'filter_strength' => 1,
                        'filter_name'     => 'IGNormalFilter',
                    ],
                ];

                // This usertag per-file EXTERNAL metadata is only supported for PHOTOS!
                if (isset($item['usertags'])) {
                    // NOTE: These usertags were validated in Timeline::uploadAlbum.
                    $photoConfig['usertags'] = json_encode(['in' => $item['usertags']]);
                }

                $childrenMetadata[] = $photoConfig;
                break;
            case 'video':
                // Get all of the INTERNAL per-VIDEO metadata.
                $videoDetails = $itemInternalMetadata->getVideoDetails();

                // Build this item's configuration.
                $videoConfig = [
                    'length'              => round($videoDetails->getDuration(), 1),
                    'date_time_original'  => $date,
                    'scene_type'          => 1,
                    'poster_frame_index'  => 0,
                    'trim_type'           => 0,
                    'disable_comments'    => false,
                    'upload_id'           => $uploadId,
                    'source_type'         => 'library',
                    'geotag_enabled'      => false,
                    'edits'               => [
                        'length'          => round($videoDetails->getDuration(), 1),
                        'cinema'          => 'unsupported',
                        'original_length' => round($videoDetails->getDuration(), 1),
                        'source_type'     => 'library',
                        'start_time'      => 0,
                        'camera_position' => 'unknown',
                        'trim_type'       => 0,
                    ],
                ];

                $childrenMetadata[] = $videoConfig;
                break;
            }
        }

        // Build the request...
        $request = $this->ig->request($endpoint)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('client_sidecar_id', $albumUploadId)
            ->addPost('caption', $captionText)
            ->addPost('children_metadata', $childrenMetadata);

        if ($location instanceof Response\Model\Location) {
            $request
                ->addPost('location', Utils::buildMediaLocationJSON($location))
                ->addPost('geotag_enabled', '1')
                ->addPost('posting_latitude', $location->getLat())
                ->addPost('posting_longitude', $location->getLng())
                ->addPost('media_latitude', $location->getLat())
                ->addPost('media_longitude', $location->getLng())
                ->addPost('exif_latitude', 0.0)
                ->addPost('exif_longitude', 0.0);
        }

        $configure = $request->getResponse(new Response\ConfigureResponse());

        return $configure;
    }

    /**
     * Saves active experiments.
     *
     * @param Response\SyncResponse $syncResponse
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    protected function _saveExperiments(
        Response\SyncResponse $syncResponse)
    {
        $experiments = [];
        foreach ($syncResponse->experiments as $experiment) {
            if (!isset($experiment->name)) {
                continue;
            }

            $group = $experiment->name;
            if (!isset($experiments[$group])) {
                $experiments[$group] = [];
            }

            if (!isset($experiment->params)) {
                continue;
            }

            foreach ($experiment->params as $param) {
                if (!isset($param->name)) {
                    continue;
                }

                $experiments[$group][$param->name] = $param->value;
            }
        }

        // Save the experiments and the last time we refreshed them.
        $this->ig->experiments = $this->ig->settings->setExperiments($experiments);
        $this->ig->settings->set('last_experiments', time());
    }

    /**
     * Perform an Instagram "feature synchronization" call for device.
     *
     * @param bool $prelogin
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SyncResponse
     */
    public function syncDeviceFeatures(
        $prelogin = false)
    {
        $request = $this->ig->request('qe/sync/')
            ->addPost('id', $this->ig->uuid)
            ->addPost('experiments', Constants::LOGIN_EXPERIMENTS);
        if ($prelogin) {
            $request->setNeedsAuth(false);
        } else {
            $request
                ->addPost('_uuid', $this->ig->uuid)
                ->addPost('_uid', $this->ig->account_id)
                ->addPost('_csrftoken', $this->ig->client->getToken());
        }

        return $request->getResponse(new Response\SyncResponse());
    }

    /**
     * Perform an Instagram "feature synchronization" call for account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SyncResponse
     */
    public function syncUserFeatures()
    {
        $result = $this->ig->request('qe/sync/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('id', $this->ig->account_id)
            ->addPost('experiments', Constants::EXPERIMENTS)
            ->getResponse(new Response\SyncResponse());

        // Save the updated experiments for this user.
        $this->_saveExperiments($result);

        return $result;
    }

    /**
     * Registers advertising identifier.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function logAttribution()
    {
        return $this->ig->request('attribution/log_attribution/')
            ->setNeedsAuth(false)
            ->addPost('adid', $this->ig->advertising_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Reads MSISDN header.
     *
     * WARNING. DON'T USE. UNDER RESEARCH.
     *
     * @param string $subnoKey Encoded subscriber number.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MsisdnHeaderResponse
     *
     * @since 10.24.0 app version.
     */
    public function readMsisdnHeader(
        $subnoKey = null)
    {
        $request = $this->ig->request('accounts/read_msisdn_header/')
            ->setNeedsAuth(false)
            // UUID is used as device_id intentionally.
            ->addPost('device_id', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken());
        if ($subnoKey !== null) {
            $request->addPost('subno_key', $subnoKey);
        }

        return $request->getResponse(new Response\MsisdnHeaderResponse());
    }

    /**
     * Bootstraps MSISDN header.
     *
     * WARNING. DON'T USE. UNDER RESEARCH.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MsisdnHeaderResponse
     *
     * @since 10.24.0 app version.
     */
    public function bootstrapMsisdnHeader()
    {
        $request = $this->ig->request('accounts/msisdn_header_bootstrap/')
            ->setNeedsAuth(false)
            // UUID is used as device_id intentionally.
            ->addPost('device_id', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken());

        return $request->getResponse(new Response\MsisdnHeaderResponse());
    }

    /**
     * Get megaphone log.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MegaphoneLogResponse
     */
    public function getMegaphoneLog()
    {
        return $this->ig->request('megaphone/log/')
            ->setSignedPost(false)
            ->addPost('type', 'feed_aysf')
            ->addPost('action', 'seen')
            ->addPost('reason', '')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('uuid', md5(time()))
            ->getResponse(new Response\MegaphoneLogResponse());
    }

    /**
     * Get hidden entities for users, places and hashtags via Facebook's algorithm.
     *
     * TODO: We don't know what this function does. If we ever discover that it
     * has a useful purpose, then we should move it somewhere else.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FacebookHiddenEntitiesResponse
     */
    public function getFacebookHiddenSearchEntities()
    {
        return $this->ig->request('fbsearch/get_hidden_search_entities/')
            ->getResponse(new Response\FacebookHiddenEntitiesResponse());
    }

    /**
     * Get Facebook OTA (Over-The-Air) update information.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FacebookOTAResponse
     */
    public function getFacebookOTA()
    {
        return $this->ig->request('facebook_ota/')
            ->addParam('fields', Constants::FACEBOOK_OTA_FIELDS)
            ->addParam('custom_user_id', $this->ig->account_id)
            ->addParam('signed_body', Signatures::generateSignature('').'.')
            ->addParam('ig_sig_key_version', Constants::SIG_KEY_VERSION)
            ->addParam('version_code', Constants::VERSION_CODE)
            ->addParam('version_name', Constants::IG_VERSION)
            ->addParam('custom_app_id', Constants::FACEBOOK_ORCA_APPLICATION_ID)
            ->addParam('custom_device_id', $this->ig->uuid)
            ->getResponse(new Response\FacebookOTAResponse());
    }

    /**
     * Get profile "notices".
     *
     * This is just for some internal state information, such as
     * "has_change_password_megaphone". It's not for public use.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ProfileNoticeResponse
     */
    public function getProfileNotice()
    {
        return $this->ig->request('users/profile_notice/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\ProfileNoticeResponse());
    }

    /**
     * Fetch qp data.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FetchQPDataResponse
     */
    public function getQPFetch()
    {
        return $this->ig->request('qp/fetch/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('vc_policy', 'default')
            ->addPost('surface_param', Constants::SURFACE_PARAM)
            ->addPost('version', 1)
            ->addPost('query', "viewer() {\n  eligible_promotions.surface_nux_id(<surface>).external_gating_permitted_qps(<external_gating_permitted_qps>) {\n    edges {\n      priority,\n      time_range {\n        start,\n        end\n      },\n      node {\n        id,\n        promotion_id,\n        max_impressions,\n        triggers,\n        creatives {\n          title {\n            text\n          },\n          content {\n            text\n          },\n          footer {\n            text\n          },\n          social_context {\n            text\n          },\n          primary_action{\n            title {\n              text\n            },\n            url,\n            limit,\n            dismiss_promotion\n          },\n          secondary_action{\n            title {\n              text\n            },\n            url,\n            limit,\n            dismiss_promotion\n          },\n          dismiss_action{\n            title {\n              text\n            },\n            url,\n            limit,\n            dismiss_promotion\n          },\n          image {\n            uri\n          }\n        }\n      }\n    }\n  }\n}\n")
            ->getResponse(new Response\FetchQPDataResponse());
    }

    /**
     * Send analytics and events to Instagram's Analytics Server.
     *
     * @param array $data Analytics and event data array.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ClientEventLogsResponse
     */
    public function sendClientEventLogs(
        array $data)
    {
        $message = base64_encode(gzcompress(json_encode($data)));
        $message = urlencode($message); // Yep, we must URL-encode this data!

        return $this->ig->request(Constants::GRAPH_URL.'logging_client_events')
            ->addPost('message', $message)
            ->addPost('compressed', '1')
            ->addPost('access_token', Constants::ANALYTICS_ACCESS_TOKEN)
            ->addPost('format', 'json')
            ->getResponse(new Response\ClientEventLogsResponse());
    }

    /**
     * Configure media entity (album, video, ...) with retries.
     *
     * @param string   $entity       Entity to display in error messages.
     * @param callable $configurator Configurator function.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\UploadFailedException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ResponseInterface
     */
    public function configureWithRetries(
        $entity,
        callable $configurator)
    {
        $attempt = 0;
        while (true) {
            // Check for max retry-limit, and throw if we exceeded it.
            if (++$attempt > self::MAX_CONFIGURE_RETRIES) {
                throw new \InstagramAPI\Exception\UploadFailedException(sprintf(
                    'Configuration of "%s" failed. All retries have failed.',
                    $entity
                ));
            }

            $result = null;

            try {
                /** @var ResponseInterface $result */
                $result = call_user_func($configurator);
            } catch (ThrottledException $e) {
                throw $e;
            } catch (LoginRequiredException $e) {
                throw $e;
            } catch (FeedbackRequiredException $e) {
                throw $e;
            } catch (CheckpointRequiredException $e) {
                throw $e;
            } catch (InstagramException $e) {
                if ($e->hasResponse()) {
                    $result = $e->getResponse();
                }
            } catch (\Exception $e) {
                // Ignore everything else.
            }

            // We had a network error or something like that, let's continue to the next attempt.
            if ($result === null) {
                sleep(1);
                continue;
            }

            $httpResponse = $result->getHttpResponse();
            $fullResponse = $result->getFullResponse();
            $delay = 1;
            switch ($httpResponse->getStatusCode()) {
                case 200:
                    // Instagram uses "ok" status for this error, so we need to check it first:
                    // {"message": "media_needs_reupload", "error_title": "staged_position_not_found", "status": "ok"}
                    if (strtolower($result->getMessage()) === 'media_needs_reupload') {
                        throw new \InstagramAPI\Exception\UploadFailedException(sprintf(
                            'Configuration of "%s" failed. You need to reupload the media (%s).',
                            $entity,
                            (isset($fullResponse->error_title) ? $fullResponse->error_title : 'unknown error')
                        ));
                    } elseif ($result->isOk()) {
                        return $result;
                    }
                    // Continue to the next attempt.
                    break;
                case 202:
                    if (isset($fullResponse->cooldown_time_in_seconds)) {
                        $delay = max((int) $fullResponse->cooldown_time_in_seconds, 1);
                    }
                    break;
                default:
            }
            sleep($delay);
        }

        throw new \InstagramAPI\Exception\UploadFailedException(sprintf(
            'Configuration of "%s" failed.',
            $entity
        ));
    }

    /**
     * Get the first missing range (start-end) from a HTTP "Range" header.
     *
     * @param string $ranges
     *
     * @return array|null
     */
    protected function _getFirstMissingRange(
        $ranges)
    {
        preg_match_all('/(?<start>\d+)-(?<end>\d+)\/(?<total>\d+)/', $ranges, $matches, PREG_SET_ORDER);
        if (!count($matches)) {
            return;
        }
        $pairs = [];
        $length = 0;
        foreach ($matches as $match) {
            $pairs[] = [$match['start'], $match['end']];
            $length = $match['total'];
        }
        // Sort pairs by start.
        usort($pairs, function (array $pair1, array $pair2) {
            return $pair1[0] - $pair2[0];
        });
        $first = $pairs[0];
        $second = count($pairs) > 1 ? $pairs[1] : null;
        if ($first[0] == 0) {
            $result = [$first[1] + 1, ($second === null ? $length : $second[0]) - 1];
        } else {
            $result = [0, $first[0] - 1];
        }

        return $result;
    }

    /**
     * Performs a chunked upload of a video file, with support for retries.
     *
     * Note that chunk uploads often get dropped when their server is overloaded
     * at peak hours, which is why our chunk-retry mechanism exists. We will
     * try several times to upload all chunks. The retries will only re-upload
     * the exact chunks that have been dropped from their server, and it won't
     * waste time with chunks that are already successfully uploaded.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the upload fails.
     *
     * @return \InstagramAPI\Response\UploadVideoResponse
     */
    protected function _uploadVideoChunks(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        $videoFilename = $internalMetadata->getVideoDetails()->getFilename();

        // To support video uploads to albums, we MUST fake-inject the
        // "sessionid" cookie from "i.instagram" into our "upload.instagram"
        // request, otherwise the server will reply with a "StagedUpload not
        // found" error when the final chunk has been uploaded.
        $sessionIDCookie = null;
        if ($targetFeed == Constants::FEED_TIMELINE_ALBUM) {
            $foundCookie = $this->ig->client->getCookie('sessionid', 'i.instagram.com');
            if ($foundCookie !== null) {
                $sessionIDCookie = $foundCookie->getValue();
            }
            if ($sessionIDCookie === null) { // Verify value.
                throw new \InstagramAPI\Exception\UploadFailedException(
                    'Unable to find the necessary SessionID cookie for uploading video album chunks.'
                );
            }
        }

        // Verify the upload URLs.
        $uploadUrls = $internalMetadata->getVideoUploadUrls();
        if (!is_array($uploadUrls) || !count($uploadUrls)) {
            throw new \InstagramAPI\Exception\UploadFailedException('No video upload URLs found.');
        }

        // Init state.
        $length = $internalMetadata->getVideoDetails()->getFilesize();
        $uploadId = $internalMetadata->getUploadId();
        $sessionId = sprintf('%s-%d', $uploadId, Utils::hashCode($videoFilename));
        $uploadUrl = array_shift($uploadUrls);
        $offset = 0;
        $chunk = min($length, self::MIN_CHUNK_SIZE);
        $attempt = 0;

        // Open file handle.
        $handle = fopen($videoFilename, 'rb');
        if ($handle === false) {
            throw new \InstagramAPI\Exception\UploadFailedException(sprintf(
                'Failed to open "%s" for reading.',
                $videoFilename
            ));
        }

        try {
            // Create a stream for the opened file handle.
            $stream = new Stream($handle);
            while (true) {
                // Check for this server's max retry-limit, and switch server?
                if (++$attempt > self::MAX_CHUNK_RETRIES) {
                    $uploadUrl = null;
                }

                // Try to switch to another server.
                if ($uploadUrl === null) {
                    $uploadUrl = array_shift($uploadUrls);
                    // Fail if there are no upload URLs left.
                    if ($uploadUrl === null) {
                        throw new \InstagramAPI\Exception\UploadFailedException(sprintf(
                            'Upload of "%s" failed. There are no more upload URLs.',
                            $videoFilename
                        ));
                    }
                    // Reset state.
                    $attempt = 1; // As if "++$attempt" had ran once, above.
                    $offset = 0;
                    $chunk = min($length, self::MIN_CHUNK_SIZE);
                }

                // Prepare request.
                $request = new Request($this->ig, $uploadUrl->getUrl());
                $request
                    ->setAddDefaultHeaders(false)
                    ->addHeader('Content-Type', 'application/octet-stream')
                    ->addHeader('Session-ID', $sessionId)
                    ->addHeader('Content-Disposition', 'attachment; filename="video.mov"')
                    ->addHeader('Content-Range', 'bytes '.$offset.'-'.($offset + $chunk - 1).'/'.$length)
                    ->addHeader('job', $uploadUrl->getJob())
                    ->setBody(new LimitStream($stream, $chunk, $offset));

                // When uploading videos to albums, we must fake-inject the
                // "sessionid" cookie (the official app fake-injects it too).
                if ($targetFeed == Constants::FEED_TIMELINE_ALBUM && $sessionIDCookie !== null) {
                    // We'll add it with the default options ("single use")
                    // so the fake cookie is only added to THIS request.
                    $this->ig->client->getMiddleware()->addFakeCookie('sessionid', $sessionIDCookie);
                }

                // Perform the upload of the current chunk.
                $start = microtime(true);

                try {
                    $httpResponse = $request->getHttpResponse();
                } catch (NetworkException $e) {
                    // Ignore network exceptions.
                    continue;
                }

                // Determine new chunk size based on upload duration.
                $newChunkSize = (int) ($chunk / (microtime(true) - $start) * 5);
                // Ensure that the new chunk size is in valid range.
                $newChunkSize = min(self::MAX_CHUNK_SIZE, max(self::MIN_CHUNK_SIZE, $newChunkSize));

                $result = null;

                try {
                    /** @var Response\UploadVideoResponse $result */
                    $result = $request->getResponse(new Response\UploadVideoResponse());
                } catch (CheckpointRequiredException $e) {
                    throw $e;
                } catch (LoginRequiredException $e) {
                    throw $e;
                } catch (FeedbackRequiredException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    // Ignore everything else.
                }

                // Process the server response...
                switch ($httpResponse->getStatusCode()) {
                    case 200:
                        // All chunks are uploaded, but if we don't have a
                        // response-result now then we must retry a new server.
                        if ($result === null) {
                            $uploadUrl = null;
                            break;
                        }

                        // SUCCESS! :-)
                        return $result;
                    case 201:
                        // The server has given us a regular reply. We expect it
                        // to be a range-reply, such as "0-3912399/23929393".
                        // Their server often drops chunks during peak hours,
                        // and in that case the first range may not start at
                        // zero, or there may be gaps or multiple ranges, such
                        // as "0-4076155/8152310,6114234-8152309/8152310". We'll
                        // handle that by re-uploading whatever they've dropped.
                        if (!$httpResponse->hasHeader('Range')) {
                            $uploadUrl = null;
                            break;
                        }
                        $range = $this->_getFirstMissingRange($httpResponse->getHeaderLine('Range'));
                        if ($range !== null) {
                            $offset = $range[0];
                            $chunk = min($newChunkSize, $range[1] - $range[0] + 1);
                        } else {
                            $chunk = min($newChunkSize, $length - $offset);
                        }

                        // Reset attempts count on successful upload.
                        $attempt = 0;
                        break;
                    case 400:
                    case 403:
                    case 511:
                        throw new \InstagramAPI\Exception\UploadFailedException(sprintf(
                            "Upload of \"%s\" failed. Instagram's server returned HTTP status \"%d\".",
                            $videoFilename, $httpResponse->getStatusCode()
                        ));
                    case 422:
                        throw new \InstagramAPI\Exception\UploadFailedException(sprintf(
                            "Upload of \"%s\" failed. Instagram's server says that the video is corrupt.",
                            $videoFilename, $httpResponse->getStatusCode()
                        ));
                    default:
                }
            }
        } finally {
            // Guaranteed to release handle even if something bad happens above!
            Utils::safe_fclose($handle);
        }

        throw new \InstagramAPI\Exception\UploadFailedException(sprintf(
            'Upload of \"%s\" failed.',
            $videoFilename
        ));
    }

    /**
     * Performs a resumable upload of a video file, with support for retries.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the upload fails.
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    protected function _uploadResumableVideo(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        $videoFilename = $internalMetadata->getVideoDetails()->getFilename();

        $rurCookie = $this->ig->client->getCookie('rur', 'i.instagram.com');
        if ($rurCookie === null || !strlen($rurCookie->getValue())) {
            throw new \InstagramAPI\Exception\UploadFailedException(
                'Unable to find the necessary "rur" cookie for uploading video.'
            );
        }

        $endpoint = sprintf('https://i.instagram.com/rupload_igvideo/%s_%d_%d?target=%s',
            $internalMetadata->getUploadId(),
            0,
            Utils::hashCode($videoFilename),
            $rurCookie->getValue()
        );

        $uploadParams = $this->_getVideoUploadParams($targetFeed, $internalMetadata);
        $uploadParams = Utils::reorderByHashCode($uploadParams);

        $offsetTemplate = new Request($this->ig, $endpoint);
        $offsetTemplate
            ->setAddDefaultHeaders(false)
            // TODO: Store waterfall ID in internalMetadata?
            ->addHeader('X_FB_VIDEO_WATERFALL_ID', Signatures::generateUUID(true))
            ->addHeader('X-Instagram-Rupload-Params', json_encode($uploadParams));

        $videoDetails = $internalMetadata->getVideoDetails();
        $length = $videoDetails->getFilesize();
        $uploadTemplate = clone $offsetTemplate;
        $uploadTemplate
            ->addHeader('X-Entity-Type', 'video/mp4')
            ->addHeader('X-Entity-Name', basename(parse_url($endpoint, PHP_URL_PATH)))
            ->addHeader('X-Entity-Length', $length);

        $attempt = 0;

        // Open file handle.
        $handle = fopen($videoFilename, 'rb');
        if ($handle === false) {
            throw new \InstagramAPI\Exception\UploadFailedException(sprintf(
                'Failed to open "%s" for reading.',
                $videoFilename
            ));
        }

        try {
            // Create a stream for the opened file handle.
            $stream = new Stream($handle);

            while (true) {
                // Check for max retry-limit, and throw if we exceeded it.
                if (++$attempt > self::MAX_RESUMABLE_RETRIES) {
                    throw new \InstagramAPI\Exception\UploadFailedException(sprintf(
                        'Upload of "%s" failed. All retries have failed.',
                        $videoFilename
                    ));
                }

                try {
                    // Get current offset.
                    $offsetRequest = clone $offsetTemplate;
                    /** @var Response\ResumableOffsetResponse $offsetResponse */
                    $offsetResponse = $offsetRequest->getResponse(new Response\ResumableOffsetResponse());
                    $offset = $offsetResponse->getOffset();

                    // Resume upload from given offset.
                    $uploadRequest = clone $uploadTemplate;
                    $uploadRequest
                        ->addHeader('Offset', $offset)
                        ->setBody(new LimitStream($stream, $length - $offset, $offset));
                    /** @var Response\GenericResponse $response */
                    $response = $uploadRequest->getResponse(new Response\GenericResponse());

                    return $response;
                } catch (ThrottledException $e) {
                    throw $e;
                } catch (LoginRequiredException $e) {
                    throw $e;
                } catch (FeedbackRequiredException $e) {
                    throw $e;
                } catch (CheckpointRequiredException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    // Ignore everything else.
                }
            }
        } finally {
            // Guaranteed to release handle even if something bad happens above!
            Utils::safe_fclose($handle);
        }

        throw new \InstagramAPI\Exception\UploadFailedException(sprintf(
            'Upload of \"%s\" failed.',
            $videoFilename
        ));
    }

    /**
     * Determine whether to use resumable uploader based on target feed and internal metadata.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @return bool
     */
    protected function _useResumableUploader(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        // TODO: use $internalMetadata object for additional checks.
        switch ($targetFeed) {
            case Constants::FEED_TIMELINE_ALBUM:
                $result = false;
                break;
            case Constants::FEED_TIMELINE:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_upload_reliability_universe',
                    'is_enabled_fbupload_followers_share');
                break;
            case Constants::FEED_DIRECT:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_upload_reliability_universe',
                    'is_enabled_fbupload_direct_share');
                break;
            case Constants::FEED_STORY:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_upload_reliability_universe',
                    'is_enabled_fbupload_reel_share');
                break;
            case Constants::FEED_DIRECT_STORY:
                $result = $this->ig->isExperimentEnabled(
                    'ig_android_upload_reliability_universe',
                    'is_enabled_fbupload_story_share');
                break;
            default:
                $result = false;
        }

        return $result;
    }

    /**
     * Get params for upload job.
     *
     * @param int              $targetFeed       One of the FEED_X constants.
     * @param InternalMetadata $internalMetadata Internal library-generated metadata object.
     *
     * @return array
     */
    protected function _getVideoUploadParams(
        $targetFeed,
        InternalMetadata $internalMetadata)
    {
        $videoDetails = $internalMetadata->getVideoDetails();
        if ($videoDetails === null) {
            throw new \InvalidArgumentException('Video details are missing from internal metadata.');
        }
        // Common params.
        $result = [
            'upload_id'                => (string) $internalMetadata->getUploadId(),
            'upload_media_height'      => (string) $videoDetails->getHeight(),
            'upload_media_width'       => (string) $videoDetails->getWidth(),
            'upload_media_duration_ms' => (string) $videoDetails->getDurationInMsec(),
            'media_type'               => (string) Response\Model\Item::VIDEO,
        ];
        // Target feed's specific params.
        switch ($targetFeed) {
            case Constants::FEED_TIMELINE_ALBUM:
                $result['is_sidecar'] = '1';
                break;
            case Constants::FEED_DIRECT:
                $result['direct_v2'] = '1';
                $result['rotate'] = '0';
                $result['hflip'] = 'false';
                break;
            case Constants::FEED_STORY:
                $result['for_album'] = '1';
                break;
            case Constants::FEED_DIRECT_STORY:
                $result['for_direct_story'] = '1';
                break;
            default:
        }

        return $result;
    }
}
