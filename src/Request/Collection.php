<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

/**
 * Functions related to collections of media.
 */
class Collection extends RequestCollection
{
    /**
     * Creates a collection to help organize bookmarked (saved) medias.
     *
     * @param string     $name       Name of the collection.
     * @param null|array $mediaIds   (optional) Array containing media ids that will be saved in the collection.
     * @param string     $moduleName From which module (page) have you created the collection.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CreateCollectionResponse
     */
    public function createCollection(
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
            ->getResponse(new Response\CreateCollectionResponse());
    }

    /**
     * Delete a collection.
     *
     * @param string     $collectionId ID of the collection.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DeleteCollectionResponse
     */
    public function deleteCollection(
        $collectionId)
    {
        return $this->ig->request("collections/{$collectionId}/delete/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\DeleteCollectionResponse());
    }

    /**
     * Gets collection list.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GetCollectionsResponse
     */
    public function getCollections()
    {

        return $this->ig->request('collections/list/')
            ->getResponse(new Response\GetCollectionsResponse());
    }


}
