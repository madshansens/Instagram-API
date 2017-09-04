<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getCanonical()
 * @method mixed getHasNewer()
 * @method mixed getHasOlder()
 * @method \InstagramAPI\Response\Model\User getInviter()
 * @method mixed getIsPin()
 * @method \InstagramAPI\Response\Model\DirectThreadItem[] getItems()
 * @method mixed getLastActivityAt()
 * @method mixed getLastSeenAt()
 * @method \InstagramAPI\Response\Model\User[] getLeftUsers()
 * @method mixed getMuted()
 * @method mixed getNamed()
 * @method mixed getPending()
 * @method string getThreadId()
 * @method mixed getThreadTitle()
 * @method mixed getThreadType()
 * @method \InstagramAPI\Response\Model\User[] getUsers()
 * @method string getViewerId()
 * @method bool isCanonical()
 * @method bool isHasNewer()
 * @method bool isHasOlder()
 * @method bool isInviter()
 * @method bool isIsPin()
 * @method bool isItems()
 * @method bool isLastActivityAt()
 * @method bool isLastSeenAt()
 * @method bool isLeftUsers()
 * @method bool isMuted()
 * @method bool isNamed()
 * @method bool isPending()
 * @method bool isThreadId()
 * @method bool isThreadTitle()
 * @method bool isThreadType()
 * @method bool isUsers()
 * @method bool isViewerId()
 * @method setCanonical(mixed $value)
 * @method setHasNewer(mixed $value)
 * @method setHasOlder(mixed $value)
 * @method setInviter(\InstagramAPI\Response\Model\User $value)
 * @method setIsPin(mixed $value)
 * @method setItems(\InstagramAPI\Response\Model\DirectThreadItem[] $value)
 * @method setLastActivityAt(mixed $value)
 * @method setLastSeenAt(mixed $value)
 * @method setLeftUsers(\InstagramAPI\Response\Model\User[] $value)
 * @method setMuted(mixed $value)
 * @method setNamed(mixed $value)
 * @method setPending(mixed $value)
 * @method setThreadId(string $value)
 * @method setThreadTitle(mixed $value)
 * @method setThreadType(mixed $value)
 * @method setUsers(\InstagramAPI\Response\Model\User[] $value)
 * @method setViewerId(string $value)
 */
class DirectCreateGroupThreadResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var string
     */
    public $thread_id;
    /**
     * @var \InstagramAPI\Response\Model\User[]
     */
    public $users;
    /**
     * @var \InstagramAPI\Response\Model\User[]
     */
    public $left_users;
    /**
     * @var \InstagramAPI\Response\Model\DirectThreadItem[]
     */
    public $items;
    public $last_activity_at;
    public $muted;
    public $named;
    public $canonical;
    public $pending;
    public $thread_type;
    /**
     * @var string
     */
    public $viewer_id;
    public $thread_title;
    /**
     * @var \InstagramAPI\Response\Model\User
     */
    public $inviter;
    public $has_older;
    public $has_newer;
    public $last_seen_at;
    public $is_pin;
}
