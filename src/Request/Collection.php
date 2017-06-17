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
     * @param string $collectionId ID of the collection.
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

    /**
     * Edit name of the collection.
     *
     * @param string $collectionId ID of the collection.
     * @param string $name         Name of the collection.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\EditCollectionResponse
     */
    public function editCollection(
        $collectionId,
        $name)
    {
        return $this->ig->request("collections/{$collectionId}/edit/")
            ->addPost('name', $name)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\EditCollectionResponse());
    }

    /**
     * Add saved media to collection.
     *
     * @param string $collectionId ID of the collection.
     * @param array  $mediaIds     Array containing media ids that will be saved in the collection.
     * @param string $moduleName   From which module (page) have you created the collection.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\EditCollectionResponse
     */
    public function addSavedMediaToCollection(
        $collectionId,
        array $mediaIds = [],
        $moduleName = 'feed_saved_add_to_collection')
    {
        return $this->ig->request("collections/{$collectionId}/edit/")
            ->addPost('module_name', $moduleName)
            ->addPost('added_media_ids', json_encode($mediaIds, true))
            ->addPost('radio_type', 'wifi-none')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\EditCollectionResponse());
    }

    /**
     * Remove media from collection.
     *
     * @param array  $collectionId ID of the collection.
     * @param string $mediaId      Array containing media ids that will be saved in the collection.
     * @param string $moduleName   From which module (page) have you created the collection.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\EditCollectionResponse
     */
    public function RemoveMediaFromCollection(
        array $collectionIds,
        $mediaId,
        $moduleName = 'feed_contextual_saved_collections')
    {
        return $this->ig->request("media/{$mediaId}/save/")
            ->addPost('module_name', $moduleName)
            ->addPost('removed_collection_ids', json_encode($collectionIds, true))
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('radio_type', 'wifi-none')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\EditCollectionResponse());
    }
}
