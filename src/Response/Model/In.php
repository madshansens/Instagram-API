<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getDurationInVideoInSec()
 * @method mixed getPosition()
 * @method mixed getStartTimeInVideoInSec()
 * @method mixed getTimeInVideo()
 * @method mixed getUser()
 * @method bool isDurationInVideoInSec()
 * @method bool isPosition()
 * @method bool isStartTimeInVideoInSec()
 * @method bool isTimeInVideo()
 * @method bool isUser()
 * @method setDurationInVideoInSec(mixed $value)
 * @method setPosition(mixed $value)
 * @method setStartTimeInVideoInSec(mixed $value)
 * @method setTimeInVideo(mixed $value)
 * @method setUser(mixed $value)
 */
class In extends AutoPropertyHandler
{
    /*
     * @var Position
     */
    public $position;
    /*
     * @var User
     */
    public $user;
    public $time_in_video;
    public $start_time_in_video_in_sec;
    public $duration_in_video_in_sec;
}
