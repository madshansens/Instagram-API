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
     * @param string      $latitude
     * @param string      $longitude
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
     * @param string $lat   Latitude.
     * @param string $lng   Longitude.
     * @param int    $count (optional) Facebook will return up to this many results.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FBLocationResponse
     */
    public function searchFacebookByPoint(
        $lat,
        $lng,
        $count = null)
    {
        $location = $this->ig->request('fbsearch/places/')
            ->addParam('rank_token', $this->ig->rank_token)
            ->addParam('lat', $lat)
            ->addParam('lng', $lng);

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
}
