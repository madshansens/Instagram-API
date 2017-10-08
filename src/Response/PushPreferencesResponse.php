<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class PushPreferencesResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'push_settings'                         => 'Model\PushSettings[]',
        'likes'                                 => '',
        'comments'                              => '',
        'comment_likes'                         => '',
        'like_and_comment_on_photo_user_tagged' => '',
        'live_broadcast'                        => '',
        'new_follower'                          => '',
        'follow_request_accepted'               => '',
        'contact_joined'                        => '',
        'pending_direct_share'                  => '',
        'direct_share_activity'                 => '',
        'user_tagged'                           => '',
        'notification_reminders'                => '',
        'first_post'                            => '',
        'announcements'                         => '',
        'ads'                                   => '',
        'view_count'                            => '',
        'report_updated'                        => '',
    ];
}
