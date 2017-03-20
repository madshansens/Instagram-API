<?php

namespace InstagramAPI\Exception;

/**
 * Means that you have become throttled by Instagram's API server
 * because of too many requests. You should yourself down!
 */
class ThrottledException extends RequestException
{
}
