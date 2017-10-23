<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Comment.
 *
 * @method mixed getBitFlags()
 * @method int getChildCommentCount()
 * @method mixed getCommentLikeCount()
 * @method mixed getContentType()
 * @method string getCreatedAt()
 * @method string getCreatedAtUtc()
 * @method mixed getDidReportAsSpam()
 * @method mixed getHasLikedComment()
 * @method bool getHasMoreHeadChildComments()
 * @method bool getHasMoreTailChildComments()
 * @method mixed getHasTranslation()
 * @method string getMediaId()
 * @method string getNextMaxChildCursor()
 * @method User[] getOtherPreviewUsers()
 * @method string getParentCommentId()
 * @method string getPk()
 * @method Comment[] getPreviewChildComments()
 * @method mixed getStatus()
 * @method mixed getText()
 * @method mixed getType()
 * @method User getUser()
 * @method string getUserId()
 * @method bool isBitFlags()
 * @method bool isChildCommentCount()
 * @method bool isCommentLikeCount()
 * @method bool isContentType()
 * @method bool isCreatedAt()
 * @method bool isCreatedAtUtc()
 * @method bool isDidReportAsSpam()
 * @method bool isHasLikedComment()
 * @method bool isHasMoreHeadChildComments()
 * @method bool isHasMoreTailChildComments()
 * @method bool isHasTranslation()
 * @method bool isMediaId()
 * @method bool isNextMaxChildCursor()
 * @method bool isOtherPreviewUsers()
 * @method bool isParentCommentId()
 * @method bool isPk()
 * @method bool isPreviewChildComments()
 * @method bool isStatus()
 * @method bool isText()
 * @method bool isType()
 * @method bool isUser()
 * @method bool isUserId()
 * @method $this setBitFlags(mixed $value)
 * @method $this setChildCommentCount(int $value)
 * @method $this setCommentLikeCount(mixed $value)
 * @method $this setContentType(mixed $value)
 * @method $this setCreatedAt(string $value)
 * @method $this setCreatedAtUtc(string $value)
 * @method $this setDidReportAsSpam(mixed $value)
 * @method $this setHasLikedComment(mixed $value)
 * @method $this setHasMoreHeadChildComments(bool $value)
 * @method $this setHasMoreTailChildComments(bool $value)
 * @method $this setHasTranslation(mixed $value)
 * @method $this setMediaId(string $value)
 * @method $this setNextMaxChildCursor(string $value)
 * @method $this setOtherPreviewUsers(User[] $value)
 * @method $this setParentCommentId(string $value)
 * @method $this setPk(string $value)
 * @method $this setPreviewChildComments(Comment[] $value)
 * @method $this setStatus(mixed $value)
 * @method $this setText(mixed $value)
 * @method $this setType(mixed $value)
 * @method $this setUser(User $value)
 * @method $this setUserId(string $value)
 * @method $this unsetBitFlags()
 * @method $this unsetChildCommentCount()
 * @method $this unsetCommentLikeCount()
 * @method $this unsetContentType()
 * @method $this unsetCreatedAt()
 * @method $this unsetCreatedAtUtc()
 * @method $this unsetDidReportAsSpam()
 * @method $this unsetHasLikedComment()
 * @method $this unsetHasMoreHeadChildComments()
 * @method $this unsetHasMoreTailChildComments()
 * @method $this unsetHasTranslation()
 * @method $this unsetMediaId()
 * @method $this unsetNextMaxChildCursor()
 * @method $this unsetOtherPreviewUsers()
 * @method $this unsetParentCommentId()
 * @method $this unsetPk()
 * @method $this unsetPreviewChildComments()
 * @method $this unsetStatus()
 * @method $this unsetText()
 * @method $this unsetType()
 * @method $this unsetUser()
 * @method $this unsetUserId()
 */
class Comment extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'status'                       => '',
        'user_id'                      => 'string',
        /*
         * Unix timestamp (UTC) of when the comment was posted.
         */
        'created_at_utc'               => 'string',
        'created_at'                   => 'string',
        'bit_flags'                    => '',
        'user'                         => 'User',
        'pk'                           => 'string',
        'media_id'                     => 'string',
        'text'                         => '',
        'content_type'                 => '',
        'type'                         => '',
        'comment_like_count'           => '',
        'has_liked_comment'            => '',
        'has_translation'              => '',
        'did_report_as_spam'           => '',
        /*
         * If this is a child in a thread, this is the ID of its parent thread.
         */
        'parent_comment_id'            => 'string',
        /*
         * Number of child comments in this comment thread.
         */
        'child_comment_count'          => 'int',
        /*
         * Previews of some of the child comments. Compare it to the child
         * comment count. If there are more, you must request the comment thread.
         */
        'preview_child_comments'       => 'Comment[]',
        /*
         * Previews of users in very long comment threads.
         */
        'other_preview_users'          => 'User[]',
        /*
         * This is somehow related to pagination of child-comments in CERTAIN
         * comments with children. The value seems to ONLY appear when a comment
         * has MORE child-comments than what exists in "preview_child_comments".
         * So it probably somehow describes the missing child comments offset.
         */
        'next_max_child_cursor'        => 'string',
        'has_more_tail_child_comments' => 'bool',
        'has_more_head_child_comments' => 'bool',
    ];
}
