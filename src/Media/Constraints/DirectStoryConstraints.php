<?php

namespace InstagramAPI\Media\Constraints;

class DirectStoryConstraints extends StoryConstraints
{
    /** {@inheritdoc} */
    public function getTitle()
    {
        return 'direct story';
    }

    /** {@inheritdoc} */
    public function getMinDuration()
    {
        return DirectConstraints::MIN_DURATION;
    }
}
