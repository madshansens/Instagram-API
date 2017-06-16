<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

/**
 * Functions related to collections and lists of media.
 */
class Collection extends RequestCollection
{
    /**
     * Creates a collection list to help organize bookmarked (saved) medias.
     *
     * @param string     $name Name of the list.
     * @param null|array $mediaIds    (optional) Array containing media ids that will be saved in the list.
     * @param string     $moduleName From which module (page) have you created the list.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CreateListResponse
     */
    public function createList(
        $name,
        array $mediaIds = [],
        $moduleName = 'feed_contextual_post')
    {

        return $this->ig->request('collections/create/')
            ->addPost('module_name', $moduleName)
            ->addPost('added_media_ids', json_encode($mediaIds, true))
            ->addPost('name', $name)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\CreateListResponse());
    }
}
