<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

/**
 * Functions related to finding and exploring hashtags.
 */
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
        $urlHashtag = urlencode($hashtag); // Necessary for non-English chars.
        return $this->ig->request("tags/{$urlHashtag}/info/")
            ->getResponse(new Response\TagInfoResponse());
    }

    /**
     * Search for hashtags.
     *
     * Gives you search results ordered by best matches first.
     *
     * Note that you can get more than one "page" of hashtag search results by
     * excluding the numerical IDs of all tags from a previous search query.
     *
     * Also note that the excludes must be done via Instagram's internal,
     * numerical IDs for the tags, which you can get from this search-response.
     *
     * Lastly, be aware that they will never exclude any tag that perfectly
     * matches your search query, even if you provide its exact ID too.
     *
     * @param string         $query       Finds hashtags containing this string.
     * @param string[]|int[] $excludeList Array of numerical hashtag IDs (ie "17841562498105353")
     *                                    to exclude from the response, allowing you to skip tags
     *                                    from a previous call to get more results.
     *
     * @throws \InvalidArgumentException                  If trying to exclude too many tags.
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SearchTagResponse
     */
    public function search(
        $query,
        array $excludeList = [])
    {
        $request = $this->ig->request('tags/search/')
            ->addParam('q', $query)
            ->addParam('timezone_offset', date('Z'))
            ->addParam('count', 30)
            ->addParam('rank_token', $this->ig->rank_token);

        if (!empty($excludeList)) {
            // Safely restrict the amount of excludes we allow. Their server
            // hates high numbers, and around 150 they will literally disconnect
            // you from the API server without even answering the endpoint call.
            if (count($excludeList) > 65) { // Arbitrary safe number: 2*30 (two pages) of results plus a bit extra.
                throw new \InvalidArgumentException('You are not allowed to provide more than 65 hashtags to exclude from the search.');
            }
            $request->addParam('exclude_list', '['.implode(', ', $excludeList).']');
        }

        return $request->getResponse(new Response\SearchTagResponse());
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
        $urlHashtag = urlencode($hashtag); // Necessary for non-English chars.
        $hashtagFeed = $this->ig->request("feed/tag/{$urlHashtag}/");
        if ($maxId !== null) {
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
        $urlHashtag = urlencode($hashtag); // Necessary for non-English chars.
        return $this->ig->request("tags/{$urlHashtag}/related/")
            ->addParam('visited', '[{"id":"'.$hashtag.'","type":"hashtag"}]')
            ->addParam('related_types', '["hashtag"]')
            ->getResponse(new Response\TagRelatedResponse());
    }
}
