<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method ActionBadge getActionBadge()
 * @method mixed getCanonical()
 * @method mixed getHasNewer()
 * @method mixed getHasOlder()
 * @method User getInviter()
 * @method mixed getIsSpam()
 * @method DirectThreadItem[] getItems()
 * @method mixed getLastActivityAt()
 * @method mixed getLastActivityAtSecs()
 * @method DirectThreadLastSeenAt[] getLastSeenAt()
 * @method User[] getLeftUsers()
 * @method mixed getMuted()
 * @method mixed getNamed()
 * @method mixed getNewestCursor()
 * @method mixed getOldestCursor()
 * @method mixed getPending()
 * @method string getThreadId()
 * @method mixed getThreadTitle()
 * @method mixed getThreadType()
 * @method mixed getUnseenCount()
 * @method User[] getUsers()
 * @method string getViewerId()
 * @method bool isActionBadge()
 * @method bool isCanonical()
 * @method bool isHasNewer()
 * @method bool isHasOlder()
 * @method bool isInviter()
 * @method bool isIsSpam()
 * @method bool isItems()
 * @method bool isLastActivityAt()
 * @method bool isLastActivityAtSecs()
 * @method bool isLastSeenAt()
 * @method bool isLeftUsers()
 * @method bool isMuted()
 * @method bool isNamed()
 * @method bool isNewestCursor()
 * @method bool isOldestCursor()
 * @method bool isPending()
 * @method bool isThreadId()
 * @method bool isThreadTitle()
 * @method bool isThreadType()
 * @method bool isUnseenCount()
 * @method bool isUsers()
 * @method bool isViewerId()
 * @method setActionBadge(ActionBadge $value)
 * @method setCanonical(mixed $value)
 * @method setHasNewer(mixed $value)
 * @method setHasOlder(mixed $value)
 * @method setInviter(User $value)
 * @method setIsSpam(mixed $value)
 * @method setItems(DirectThreadItem[] $value)
 * @method setLastActivityAt(mixed $value)
 * @method setLastActivityAtSecs(mixed $value)
 * @method setLastSeenAt(DirectThreadLastSeenAt[] $value)
 * @method setLeftUsers(User[] $value)
 * @method setMuted(mixed $value)
 * @method setNamed(mixed $value)
 * @method setNewestCursor(mixed $value)
 * @method setOldestCursor(mixed $value)
 * @method setPending(mixed $value)
 * @method setThreadId(string $value)
 * @method setThreadTitle(mixed $value)
 * @method setThreadType(mixed $value)
 * @method setUnseenCount(mixed $value)
 * @method setUsers(User[] $value)
 * @method setViewerId(string $value)
 */
class DirectThread extends AutoPropertyHandler
{
    public $named;
    /**
     * @var User[]
     */
    public $users;
    public $has_newer;
    /**
     * @var string
     */
    public $viewer_id;
    /**
     * @var string
     */
    public $thread_id;
    public $last_activity_at;
    public $newest_cursor;
    public $is_spam;
    public $has_older;
    public $oldest_cursor;
    /**
     * @var User[]
     */
    public $left_users;
    public $muted;
    /**
     * @var DirectThreadItem[]
     */
    public $items;
    public $thread_type;
    public $thread_title;
    public $canonical;
    /**
     * @var User
     */
    public $inviter;
    public $pending;
    /**
     * @var DirectThreadLastSeenAt[]
     */
    public $last_seen_at;
    public $unseen_count;
    /**
     * @var ActionBadge
     */
    public $action_badge;
    public $last_activity_at_secs;
}
