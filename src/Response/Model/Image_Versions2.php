<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method ImageCandidate[] getCandidates()
 * @method mixed getTraceToken()
 * @method bool isCandidates()
 * @method bool isTraceToken()
 * @method setCandidates(ImageCandidate[] $value)
 * @method setTraceToken(mixed $value)
 */
class Image_Versions2 extends AutoPropertyHandler
{
    /**
     * @var ImageCandidate[]
     */
    public $candidates;
    public $trace_token;
}
