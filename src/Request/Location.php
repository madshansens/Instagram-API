<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

/**
 * Functions related to finding and exploring locations.
 */
class Location extends RequestCollection
{
    /**
     * Search for nearby Instagram locations by geographical coordinates.
     *
     * NOTE: The locations found by this endpoint can be used for attaching
     * locations to media uploads. This is the endpoint used by the real app!
     *
     * @param string      $latitude  Latitude.
     * @param string      $longitude Longitude.
     * @param null|string $query     (optional) If provided, Instagram does a
     *                               worldwide location text search, but lists
     *                               locations closest to your lat/lng first.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LocationResponse
     */
    public function search(
        $latitude,
        $longitude,
        $query = null)
    {
        $locations = $this->ig->request('location_search/')
            ->addParam('rank_token', $this->ig->rank_token)
            ->addParam('latitude', $latitude)
            ->addParam('longitude', $longitude);

        if ($query === null) {
            $locations->addParam('timestamp', time());
        } else {
            $locations->addParam('search_query', $query);
        }

        return $locations->getResponse(new Response\LocationResponse());
    }

    /**
     * Search for Facebook locations by name.
     *
     * WARNING: The locations found by this function DO NOT work for attaching
     * locations to media uploads. Use Location::search() instead!
     *
     * @param string $query
     * @param int    $count (optional) Facebook will return up to this many results.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FBLocationResponse
     */
    public function searchFacebook(
        $query,
        $count = null)
    {
        $location = $this->ig->request('fbsearch/places/')
            ->addParam('rank_token', $this->ig->rank_token)
            ->addParam('query', $query);

        if ($count !== null) {
            $location->addParam('count', $count);
        }

        return $location->getResponse(new Response\FBLocationResponse());
    }

    /**
     * Search for Facebook locations by geographical location.
     *
     * WARNING: The locations found by this function DO NOT work for attaching
     * locations to media uploads. Use Location::search() instead!
     *
     * @param string $latitude  Latitude.
     * @param string $longitude Longitude.
     * @param int    $count     (optional) Facebook will return up to this many results.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FBLocationResponse
     */
    public function searchFacebookByPoint(
        $latitude,
        $longitude,
        $count = null)
    {
        $location = $this->ig->request('fbsearch/places/')
            ->addParam('rank_token', $this->ig->rank_token)
            ->addParam('lat', $latitude)
            ->addParam('lng', $longitude);

        if ($count !== null) {
            $location->addParam('count', $count);
        }

        return $location->getResponse(new Response\FBLocationResponse());
    }

    /**
     * Get related locations by location ID.
     *
     * Note that this endpoint almost never succeeds, because most locations do
     * not have ANY related locations!
     *
     * @param string $locationId The internal ID of a location (from a field
     *                           such as "pk", "external_id" or "facebook_places_id").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\RelatedLocationResponse
     */
    public function getRelated(
        $locationId)
    {
        return $this->ig->request("locations/{$locationId}/related/")
            ->addParam('visited', json_encode(['id' => $locationId, 'type' => 'location']))
            ->addParam('related_types', json_encode(['location']))
            ->getResponse(new Response\RelatedLocationResponse());
    }

    /**
     * Get the media feed for a location.
     *
     * Note that if your location is a "group" (such as a city), the feed will
     * include media from multiple locations within that area. But if your
     * location is a very specific place such as a specific night club, it will
     * usually only include media from that exact location.
     *
     * @param string      $locationId The internal ID of a location (from a field
     *                                such as "pk", "external_id" or "facebook_places_id").
     * @param null|string $maxId      Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LocationFeedResponse
     */
    public function getFeed(
        $locationId,
        $maxId = null)
    {
        $locationFeed = $this->ig->request("feed/location/{$locationId}/");
        if ($maxId !== null) {
            $locationFeed->addParam('max_id', $maxId);
        }

        return $locationFeed->getResponse(new Response\LocationFeedResponse());
    }

    /**
     * Mark LocationFeedResponse story media items as seen.
     *
     * The "story" property of a `LocationFeedResponse` only gives you a
     * list of story media. It doesn't actually mark any stories as "seen",
     * so the user doesn't know that you've seen their story. Actually
     * marking the story as "seen" is done via this endpoint instead. The
     * official app calls this endpoint periodically (with 1 or more items
     * at a time) while watching a story.
     *
     * This tells the user that you've seen their story, and also helps
     * Instagram know that it shouldn't give you those seen stories again
     * if you request the same location feed multiple times.
     *
     * Tip: You can pass in the whole "getItems()" array from the location's
     * "story" property, to easily mark all of the LocationFeedResponse's story
     * media items as seen.
     *
     * @param Response\LocationFeedResponse $locationFeed The location feed
     *                                                    response object which
     *                                                    the story media items
     *                                                    came from. The story
     *                                                    items MUST belong to it.
     * @param Response\Model\Item[]         $items        Array of one or more
     *                                                    story media Items.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaSeenResponse
     *
     * @see Story::markMediaSeen()
     * @see Hashtag::markStoryMediaSeen()
     */
    public function markStoryMediaSeen(
        Response\LocationFeedResponse $locationFeed,
        array $items)
    {
        // Extract the Location Story-Tray ID from the user's location response.
        // NOTE: This can NEVER fail if the user has properly given us the exact
        // same location response that they got the story items from!
        $sourceId = '';
        if ($locationFeed->getStory() instanceof Response\Model\StoryTray) {
            $sourceId = $locationFeed->getStory()->getId();
        }
        if (!strlen($sourceId)) {
            throw new \InvalidArgumentException('Your provided LocationFeedResponse is invalid and does not contain any Location Story-Tray ID.');
        }

        // Ensure they only gave us valid items for this location response.
        // NOTE: We validate since people cannot be trusted to use their brain.
        $validIds = [];
        foreach ($locationFeed->getStory()->getItems() as $item) {
            $validIds[$item->getId()] = true;
        }
        foreach ($items as $item) {
            // NOTE: We only check Items here. Other data is rejected by Internal.
            if ($item instanceof Response\Model\Item && !isset($validIds[$item->getId()])) {
                throw new \InvalidArgumentException(sprintf(
                    'The item with ID "%s" does not belong to this LocationFeedResponse.',
                    $item->getId()
                ));
            }
        }

        // Mark the story items as seen, with the location as source ID.
        return $this->ig->internal->markStoryMediaSeen($items, $sourceId);
    }
}
