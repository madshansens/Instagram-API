<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * @method Model\Caption getCaption()
 * @method mixed getCaptionIsEdited()
 * @method mixed getCommentCount()
 * @method mixed getCommentLikesEnabled()
 * @method Model\Comment[] getComments()
 * @method mixed getHasMoreComments()
 * @method mixed getHasMoreHeadloadComments()
 * @method string getMessage()
 * @method string getNextMaxId()
 * @method mixed getPreviewComments()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isCaption()
 * @method bool isCaptionIsEdited()
 * @method bool isCommentCount()
 * @method bool isCommentLikesEnabled()
 * @method bool isComments()
 * @method bool isHasMoreComments()
 * @method bool isHasMoreHeadloadComments()
 * @method bool isMessage()
 * @method bool isNextMaxId()
 * @method bool isPreviewComments()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setCaption(Model\Caption $value)
 * @method $this setCaptionIsEdited(mixed $value)
 * @method $this setCommentCount(mixed $value)
 * @method $this setCommentLikesEnabled(mixed $value)
 * @method $this setComments(Model\Comment[] $value)
 * @method $this setHasMoreComments(mixed $value)
 * @method $this setHasMoreHeadloadComments(mixed $value)
 * @method $this setMessage(mixed $value)
 * @method $this setNextMaxId(string $value)
 * @method $this setPreviewComments(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetCaption()
 * @method $this unsetCaptionIsEdited()
 * @method $this unsetCommentCount()
 * @method $this unsetCommentLikesEnabled()
 * @method $this unsetComments()
 * @method $this unsetHasMoreComments()
 * @method $this unsetHasMoreHeadloadComments()
 * @method $this unsetMessage()
 * @method $this unsetNextMaxId()
 * @method $this unsetPreviewComments()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class MediaCommentsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'comments'                   => 'Model\Comment[]',
        'comment_count'              => '',
        'comment_likes_enabled'      => '',
        'next_max_id'                => 'string',
        'caption'                    => 'Model\Caption',
        'has_more_comments'          => '',
        'caption_is_edited'          => '',
        'preview_comments'           => '',
        'has_more_headload_comments' => '',
    ];
}
