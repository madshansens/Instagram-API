<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

class Hashtag extends RequestCollection
{
    /**
     * Get detailed hashtag information.
     *
     * @param string $hashtag The hashtag, not including the "#".
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TagInfoResponse
     */
    public function getInfo(
        $hashtag)
    {
        return $this->ig->request("tags/{$hashtag}/info")
            ->getResponse(new Response\TagInfoResponse());
    }

    /**
     * Search for hashtags.
     *
     * @param string $query Finds hashtags containing this string.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SearchTagResponse
     */
    public function search(
        $query)
    {
        return $this->ig->request('tags/search/')
            ->addParam('is_typeahead', true)
            ->addParam('q', $query)
            ->addParam('rank_token', $this->ig->rank_token)
            ->getResponse(new Response\SearchTagResponse());
    }

    /**
     * Get the feed for a hashtag.
     *
     * @param string      $hashtag The hashtag, not including the "#".
     * @param null|string $maxId   Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TagFeedResponse
     */
    public function getFeed(
        $hashtag,
        $maxId = null)
    {
        $hashtagFeed = $this->ig->request("feed/tag/{$hashtag}/");
        if (!is_null($maxId)) {
            $hashtagFeed->addParam('max_id', $maxId);
        }

        return $hashtagFeed->getResponse(new Response\TagFeedResponse());
    }

    /**
     * Get related hashtags.
     *
     * @param string $hashtag The hashtag, not including the "#".
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TagRelatedResponse
     */
    public function getRelated(
        $hashtag)
    {
        return $this->ig->request("tags/{$hashtag}/related")
            ->addParam('visited', '[{"id":"'.$hashtag.'","type":"hashtag"}]')
            ->addParam('related_types', '["hashtag"]')
            ->getResponse(new Response\TagRelatedResponse());
    }
}