<?php

namespace InstagramAPI\Exception;

/**
 * All general API function call server response problems use this exception.
 *
 * This encapsulates function-call specific problems such as "User not found"
 * and so on. To see what happened, simply getMessage() on this exception.
 */
class FunctionException extends RequestException {}