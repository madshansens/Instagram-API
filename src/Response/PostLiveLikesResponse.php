<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getEndingOffset()
 * @method mixed getNextFetchOffset()
 * @method mixed getStartingOffset()
 * @method mixed getTimeSeries()
 * @method bool isEndingOffset()
 * @method bool isNextFetchOffset()
 * @method bool isStartingOffset()
 * @method bool isTimeSeries()
 * @method setEndingOffset(mixed $value)
 * @method setNextFetchOffset(mixed $value)
 * @method setStartingOffset(mixed $value)
 * @method setTimeSeries(mixed $value)
 */
class PostLiveLikesResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $starting_offset;
    public $ending_offset;
    public $next_fetch_offset;
    public $time_series;
}
