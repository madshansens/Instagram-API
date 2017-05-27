<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;
use InstagramAPI\Signatures;
use InstagramAPI\Utils;

class Direct extends RequestCollection
{
    /**
     * Get direct inbox messages for your account.
     *
     * @param string|null $cursorId
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\V2InboxResponse
     */
    public function getInbox(
        $cursorId = null)
    {
        $request = $this->ig->request('direct_v2/inbox/')
            ->addParams('persistentBadging', 'true');
        if ($this->ig->hasUnifiedInbox()) {
            $request->addParams('use_unified_inbox', 'true');
        }
        if ($cursorId !== null) {
            $request->addParams('cursor', $cursorId);
        }

        return $request->getResponse(new Response\V2InboxResponse());
    }

    /**
     * Get visual inbox data.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\VisualInboxResponse
     */
    public function getVisualInbox()
    {
        return $this->ig->request('direct_v2/visual_inbox')
            ->addParams('persistentBadging', 'true')
            ->getResponse(new Response\VisualInboxResponse());
    }

    /**
     * Get direct share inbox.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DirectShareInboxResponse
     */
    public function getShareInbox()
    {
        return $this->ig->request('direct_share/inbox/?')
            ->getResponse(new Response\DirectShareInboxResponse());
    }

    /**
     * Get pending inbox data.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PendingInboxResponse
     */
    public function getPendingInbox()
    {
        $request = $this->ig->request('direct_v2/pending_inbox')
                 ->addParams('persistentBadging', 'true');
        if ($this->ig->hasUnifiedInbox()) {
            $request->addParams('use_unified_inbox', 'true');
        }

        return $request->getResponse(new Response\PendingInboxResponse());
    }

    /**
     * Get ranked list of recipients.
     *
     * @param string $mode        Either "reshare" or "raven".
     * @param bool   $showThreads Whether to include existing threads into response.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\RankedRecipientsResponse
     */
    public function getRankedRecipients(
        $mode,
        $showThreads)
    {
        $request = $this->ig->request('direct_v2/ranked_recipients')
            ->addParams('mode', $mode)
            ->addParams('show_threads', $showThreads ? 'true' : 'false');
        if ($this->ig->hasUnifiedInbox()) {
            $request->addParams('use_unified_inbox', 'true');
        }

        return $request
            ->getResponse(new Response\RankedRecipientsResponse());
    }

    /**
     * Get recent recipients.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\RecentRecipientsResponse
     */
    public function getRecentRecipients()
    {
        return $this->ig->request('direct_share/recent_recipients/')
            ->getResponse(new Response\RecentRecipientsResponse());
    }

    /**
     * Send a direct message to specific users or thread.
     *
     * @param string $type       One of: "media_share", "message", "like", "hashtag", "location", "profile".
     * @param array  $recipients An array with "users" or "thread" keys.
     *                           To start a new thread, provide "users" as an array
     *                           of numerical UserPK IDs. To use an existing thread
     *                           instead, provide "thread" with the thread ID.
     * @param array  $data       Depends on $type:
     *                           "media_share" uses "media_id", "media_type" and "text";
     *                           "message" uses "text";
     *                           "like" uses nothing;
     *                           "hashtag" uses "hashtag" and "text";
     *                           "location" uses "venue_id" and "text";
     *                           "profile" uses "profile_user_id" and "text".
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendItemResponse
     */
    protected function _sendDirectItem(
        $type,
        $recipients,
        array $data = [])
    {
        // Determine which endpoint to use and validate input.
        $post = [];
        $params = [];
        switch ($type) {
            case 'media_share':
                $endpoint = 'direct_v2/threads/broadcast/media_share/';
                // Check and set media_id.
                if (!isset($data['media_id'])) {
                    throw new \InvalidArgumentException('You must provide a media id.');
                }
                $post['media_id'] = $data['media_id'];
                // Check and set text.
                if (isset($data['text'])) {
                    $post['text'] = $data['text'];
                }
                // Check and set media_type.
                if (isset($data['media_type']) && $data['media_type'] === 'video') {
                    $params['media_type'] = 'video';
                } else {
                    $params['media_type'] = 'photo';
                }
                break;
            case 'message':
                $endpoint = 'direct_v2/threads/broadcast/text/';
                // Check and set text.
                if (!isset($data['text'])) {
                    throw new \InvalidArgumentException('No text message provided.');
                }
                $post['text'] = $data['text'];
                break;
            case 'like':
                $endpoint = 'direct_v2/threads/broadcast/like/';
                break;
            case 'hashtag':
                $endpoint = 'direct_v2/threads/broadcast/hashtag/';
                // Check and set hashtag.
                if (!isset($data['hashtag'])) {
                    throw new \InvalidArgumentException('No hashtag provided.');
                }
                $post['hashtag'] = $data['hashtag'];
                // Check and set text.
                if (isset($data['text'])) {
                    $post['text'] = $data['text'];
                }
                break;
            case 'location':
                $endpoint = 'direct_v2/threads/broadcast/location/';
                // Check and set venue_id.
                if (!isset($data['venue_id'])) {
                    throw new \InvalidArgumentException('No venue_id provided.');
                }
                $post['venue_id'] = $data['venue_id'];
                // Check and set text.
                if (isset($data['text'])) {
                    $post['text'] = $data['text'];
                }
                break;
            case 'profile':
                $endpoint = 'direct_v2/threads/broadcast/profile/';
                // Check and set profile_user_id.
                if (!isset($data['profile_user_id'])) {
                    throw new \InvalidArgumentException('No profile_user_id provided.');
                }
                $post['profile_user_id'] = $data['profile_user_id'];
                // Check and set text.
                if (isset($data['text'])) {
                    $post['text'] = $data['text'];
                }
                break;
            default:
                throw new \InvalidArgumentException('Unsupported parameter value for type.');
        }

        // Prepare request.
        $request = $this->ig->request($endpoint)
            ->setSignedPost(false)
            ->addPost('action', 'send_item');
        // Fill query params.
        foreach ($params as $key => $value) {
            $request->addParams($key, $value);
        }
        // Add recipients.
        $recipients = Utils::prepareRecipients($recipients);
        if (isset($recipients['users'])) {
            $request->addPost('recipient_users', $recipients['users']);
        } elseif (isset($recipients['thread'])) {
            $request->addPost('thread_ids', $recipients['thread']);
        } else {
            throw new \InvalidArgumentException('Please provide at least one recipient.');
        }
        // Fill post data.
        foreach ($post as $key => $value) {
            $request->addPost($key, $value);
        }

        return $request
            // WARNING: Must be random every time otherwise we can only
            // make a single post per direct-discussion thread.
            ->addPost('client_context', Signatures::generateUUID(true))
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->getResponse(new Response\SendItemResponse());
    }

    /**
     * Share an existing media item via direct message to a user's inbox.
     *
     * @param array  $recipients An array with "users" or "thread" keys.
     *                           To start a new thread, provide "users" as an array
     *                           of numerical UserPK IDs. To use an existing thread
     *                           instead, provide "thread" with the thread ID.
     * @param string $mediaId    The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $text       Text message.
     * @param string $mediaType  Either "photo" or "video".
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendItemResponse
     */
    public function sendMedia(
        $recipients,
        $mediaId,
        $text = null,
        $mediaType = 'photo')
    {
        return $this->_sendDirectItem(
            'media_share',
            $recipients,
            [
                'text'       => $text,
                'media_id'   => $mediaId,
                'media_type' => $mediaType,
            ]
        );
    }

    /**
     * Send a direct message to a user's inbox.
     *
     * @param array  $recipients An array with "users" or "thread" keys.
     *                           To start a new thread, provide "users" as an array
     *                           of numerical UserPK IDs. To use an existing thread
     *                           instead, provide "thread" with the thread ID.
     * @param string $text       Text message.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendItemResponse
     */
    public function sendText(
        $recipients,
        $text)
    {
        return $this->_sendDirectItem(
            'message',
            $recipients,
            [
                'text' => $text,
            ]
        );
    }

    /**
     * Send a photo via direct message to a user's inbox.
     *
     * @param array  $recipients    An array with "users" or "thread" keys.
     *                              To start a new thread, provide "users" as an array
     *                              of numerical UserPK IDs. To use an existing thread
     *                              instead, provide "thread" with the thread ID.
     * @param string $photoFilename The photo filename.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendItemResponse
     */
    public function sendPhoto(
        $recipients,
        $photoFilename)
    {
        return $this->ig->client->directShareFile(
            'photo',
            $recipients,
            [
                'filepath' => $photoFilename,
            ]
        );
    }

    /**
     * Send a like to a user's inbox.
     *
     * @param array $recipients An array with "users" or "thread" keys.
     *                          To start a new thread, provide "users" as an array
     *                          of numerical UserPK IDs. To use an existing thread
     *                          instead, provide "thread" with the thread ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendItemResponse
     */
    public function sendLike(
        $recipients)
    {
        return $this->_sendDirectItem(
            'like',
            $recipients
        );
    }

    /**
     * Send a hashtag to a user's inbox.
     *
     * @param array       $recipients An array with "users" or "thread" keys.
     *                                To start a new thread, provide "users" as an array
     *                                of numerical UserPK IDs. To use an existing thread
     *                                instead, provide "thread" with the thread ID.
     * @param string      $hashtag    Hashtag to share.
     * @param null|string $text       Text message, optional.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendItemResponse
     */
    public function sendHashtag(
        $recipients,
        $hashtag,
        $text = null)
    {
        return $this->_sendDirectItem(
            'hashtag',
            $recipients,
            [
                'hashtag' => $hashtag,
                'text'    => $text,
            ]
        );
    }

    /**
     * Send a location to a user's inbox.
     *
     * You must provide a valid Instagram location ID, which you get via other
     * functions such as searchLocation().
     *
     * @param array       $recipients An array with "users" or "thread" keys.
     *                                To start a new thread, provide "users" as an array
     *                                of numerical UserPK IDs. To use an existing thread
     *                                instead, provide "thread" with the thread ID.
     * @param string      $venueId    Instagram's internal ID for the location.
     * @param null|string $text       Text message, optional.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendItemResponse
     */
    public function sendLocation(
        $recipients,
        $venueId,
        $text = null)
    {
        return $this->_sendDirectItem(
            'location',
            $recipients,
            [
                'venue_id' => $venueId,
                'text'     => $text,
            ]
        );
    }

    /**
     * Send a profile to a user's inbox.
     *
     * @param array       $recipients An array with "users" or "thread" keys.
     *                                To start a new thread, provide "users" as an array
     *                                of numerical UserPK IDs. To use an existing thread
     *                                instead, provide "thread" with the thread ID.
     * @param string      $userId     Numerical UserPK ID.
     * @param null|string $text       Text message, optional.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendItemResponse
     */
    public function sendProfile(
        $recipients,
        $userId,
        $text = null)
    {
        return $this->_sendDirectItem(
            'profile',
            $recipients,
            [
                'profile_user_id' => $userId,
                'text'            => $text,
            ]
        );
    }

    /**
     * Get direct message thread.
     *
     * @param string      $threadId Thread ID.
     * @param string|null $cursorId
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DirectThreadResponse
     */
    public function getThread(
        $threadId,
        $cursorId = null)
    {
        $request = $this->ig->request("direct_v2/threads/$threadId/");
        if ($cursorId !== null) {
            $request->addParams('cursor', $cursorId);
        }
        if ($this->ig->hasUnifiedInbox()) {
            $request->addParams('use_unified_inbox', 'true');
        }

        return $request->getResponse(new Response\DirectThreadResponse());
    }

    /**
     * Update thread title.
     *
     * @param string $threadId Thread ID.
     * @param string $title    New title.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DirectThreadResponse
     */
    public function updateThreadTitle(
        $threadId,
        $title)
    {
        return $this->ig->request("direct_v2/threads/{$threadId}/update_title/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('title', trim($title))
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response\DirectThreadResponse());
    }

    /**
     * Mute direct thread.
     *
     * @param string $threadId Thread ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function muteThread(
        $threadId)
    {
        return $this->ig->request("direct_v2/threads/{$threadId}/mute/")
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Unmute direct thread.
     *
     * @param string $threadId Thread ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function unmuteThread(
        $threadId)
    {
        return $this->ig->request("direct_v2/threads/{$threadId}/unmute/")
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Add users to thread.
     *
     * @param string         $threadId Thread ID.
     * @param string[]|int[] $users    Array of numerical UserPK IDs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DirectThreadResponse
     */
    public function addUsersToThread(
        $threadId,
        array $users)
    {
        if (!count($users)) {
            throw new \InvalidArgumentException('Please provide at least one user.');
        }
        foreach ($users as &$user) {
            if (!is_scalar($user)) {
                throw new \InvalidArgumentException('User identifier must be scalar.');
            } elseif (!ctype_digit($user) && (!is_int($user) || $user < 0)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid user identifier.', $user));
            }
            $user = (string) $user;
        }

        return $this->ig->request("direct_v2/threads/{$threadId}/add_user/")
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('user_ids', json_encode($users))
            ->addPost('_uuid', $this->ig->uuid)
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response\DirectThreadResponse());
    }

    /**
     * Leave direct thread.
     *
     * @param string $threadId Thread ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function leaveThread(
        $threadId)
    {
        return $this->ig->request("direct_v2/threads/{$threadId}/leave/")
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Hide direct thread.
     *
     * @param string $threadId Thread ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function hideThread(
        $threadId)
    {
        $request = $this->ig->request("direct_v2/threads/{$threadId}/hide/");
        if ($this->ig->hasUnifiedInbox()) {
            $request->addParams('use_unified_inbox', 'true');
        }

        return $request
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Delete an item from given thread.
     *
     * @param string $threadId     Thread ID.
     * @param string $threadItemId Thread item ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function deleteItem(
        $threadId,
        $threadItemId)
    {
        return $this->ig->request("direct_v2/threads/{$threadId}/items/{$threadItemId}/delete/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Marks an item from given thread as seen.
     *
     * @param string $threadId     Thread ID.
     * @param string $threadItemId Thread item ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SeenResponse
     */
    public function markItemSeen(
        $threadId,
        $threadItemId)
    {
        $request = $this->ig->request("direct_v2/threads/{$threadId}/items/{$threadItemId}/seen/");
        if ($this->ig->hasUnifiedInbox()) {
            $request->addPost('use_unified_inbox', 'true');
        }

        return $request
            ->addPost('action', 'mark_seen')
            ->addPost('thread_id', $threadId)
            ->addPost('item_id', $threadItemId)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response\SeenResponse());
    }

    /**
     * Approve pending threads by given identifiers.
     *
     * @param array $threads One or more thread identifiers.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function approvePendingThreads(
        array $threads)
    {
        if (!count($threads)) {
            throw new \InvalidArgumentException('Please provide at least one thread to approve.');
        }
        // Validate threads.
        foreach ($threads as &$thread) {
            if (!is_scalar($thread)) {
                throw new \InvalidArgumentException('Thread identifier must be scalar.');
            } elseif (!ctype_digit($thread) && (!is_int($thread) || $thread < 0)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid thread identifier.', $thread));
            }
            $thread = (string) $thread;
        }
        unset($thread);
        // Choose appropriate endpoint.
        if (count($threads) > 1) {
            $request = $this->ig->request('direct_v2/threads/approve_multiple/')
                ->addPost('thread_ids', json_encode($threads));
        } else {
            /** @var string $thread */
            $thread = reset($threads);
            $request = $this->ig->request("direct_v2/threads/{$thread}/approve/");
        }

        return $request
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Decline pending threads by given identifiers.
     *
     * @param array $threads One or more thread identifiers.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function declinePendingThreads(
        array $threads)
    {
        if (!count($threads)) {
            throw new \InvalidArgumentException('Please provide at least one thread to decline.');
        }
        // Validate threads.
        foreach ($threads as &$thread) {
            if (!is_scalar($thread)) {
                throw new \InvalidArgumentException('Thread identifier must be scalar.');
            } elseif (!ctype_digit($thread) && (!is_int($thread) || $thread < 0)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid thread identifier.', $thread));
            }
            $thread = (string) $thread;
        }
        unset($thread);
        // Choose appropriate endpoint.
        if (count($threads) > 1) {
            $request = $this->ig->request('direct_v2/threads/decline_multiple/')
                ->addPost('thread_ids', json_encode($threads));
        } else {
            /** @var string $thread */
            $thread = reset($threads);
            $request = $this->ig->request("direct_v2/threads/{$thread}/decline/");
        }

        return $request
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Decline all pending threads.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function declineAllPendingThreads()
    {
        return $this->ig->request('direct_v2/threads/decline_all/')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response());
    }
}
