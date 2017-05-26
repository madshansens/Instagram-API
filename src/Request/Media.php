<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

class Media extends RequestCollection
{
    /**
     * Get detailed media information.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaInfoResponse
     */
    public function getInfo(
        $mediaId)
    {
        return $this->ig->request("media/{$mediaId}/info/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('media_id', $mediaId)
            ->getResponse(new Response\MediaInfoResponse());
    }

    /**
     * Delete a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaDeleteResponse
     */
    public function delete(
        $mediaId)
    {
        return $this->ig->request("media/{$mediaId}/delete/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('media_id', $mediaId)
            ->getResponse(new Response\MediaDeleteResponse());
    }

    /**
     * Edit media.
     *
     * @param string   $mediaId     The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string   $captionText Caption text.
     * @param string[] $userTags    Array of numerical UserPK IDs of people tagged in your media.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\EditMediaResponse
     */
    public function edit(
        $mediaId,
        $captionText = '',
        $usertags = null)
    {
        if (is_null($usertags)) {
            return $this->ig->request("media/{$mediaId}/edit_media/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('caption_text', $captionText)
            ->getResponse(new Response\EditMediaResponse());
        } else {
            return $this->ig->request("media/{$mediaId}/edit_media/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('caption_text', $captionText)
            ->addPost('usertags', $usertags)
            ->getResponse(new Response\EditMediaResponse());
        }
    }

    /**
     * Like a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function like(
        $mediaId)
    {
        return $this->ig->request("media/{$mediaId}/like/")
        ->addPost('_uuid', $this->ig->uuid)
        ->addPost('_uid', $this->ig->account_id)
        ->addPost('_csrftoken', $this->ig->client->getToken())
        ->addPost('media_id', $mediaId)
        ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Unlike a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function unlike(
        $mediaId)
    {
        return $this->ig->request("media/{$mediaId}/unlike/")
         ->addPost('_uuid', $this->ig->uuid)
         ->addPost('_uid', $this->ig->account_id)
         ->addPost('_csrftoken', $this->ig->client->getToken())
         ->addPost('media_id', $mediaId)
         ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Get list of users who liked a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaLikersResponse
     */
    public function getLikers(
        $mediaId)
    {
        return $this->ig->request("media/{$mediaId}/likers/")->getResponse(new Response\MediaLikersResponse());
    }

    /**
     * Get a simplified, chronological list of users who liked a media item.
     *
     * WARNING! DANGEROUS! Although this function works, we don't know
     * whether it's used by Instagram's app right now. If it isn't used by
     * the app, then you can easily get BANNED for using this function!
     *
     * If you call this function, you do that AT YOUR OWN RISK and you
     * risk losing your Instagram account! This notice will be removed if
     * the function is safe to use. Otherwise this whole function will
     * be removed someday, if it wasn't safe.
     *
     * Only use this if you are OK with possibly losing your account!
     *
     * TODO: Research when/if the official app calls this function.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaLikersResponse
     */
    public function getLikersChrono(
        $mediaId)
    {
        return $this->ig->request("media/{$mediaId}/likers_chrono/")->getResponse(new Response\MediaLikersResponse());
    }

    /**
     * Enable comments for a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function enableComments(
        $mediaId)
    {
        return $this->ig->request("media/{$mediaId}/enable_comments/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Disable comments for a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function disableComments(
        $mediaId)
    {
        return $this->ig->request("media/{$mediaId}/disable_comments/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->setSignedPost(false)
            ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Get media comments.
     *
     * @param string      $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param null|string $maxId   Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaCommentsResponse
     */
    public function getComments(
        $mediaId,
        $maxId = null)
    {
        return $this->ig->request("media/{$mediaId}/comments/")
            ->addParams('ig_sig_key_version', Constants::SIG_KEY_VERSION)
            ->addParams('max_id', $maxId)
            ->getResponse(new Response\MediaCommentsResponse());
    }

    /**
     * Post a comment on a media item.
     *
     * @param string $mediaId     The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $commentText Your comment text.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentResponse
     */
    public function comment(
        $mediaId,
        $commentText)
    {
        return $this->ig->request("media/{$mediaId}/comment/")
            ->addPost('user_breadcrumb', Utils::generateUserBreadcrumb(mb_strlen($commentText)))
            ->addPost('idempotence_token', Signatures::generateUUID(true))
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('comment_text', $commentText)
            ->addPost('containermodule', 'comments_feed_timeline')
            ->addPost('radio_type', 'wifi-none')
            ->getResponse(new Response\CommentResponse());
    }

    /**
     * Delete a comment.
     *
     * @param string $mediaId   The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $commentId The comment's ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DeleteCommentResponse
     */
    public function deleteComment(
        $mediaId,
        $commentId)
    {
        return $this->ig->request("media/{$mediaId}/comment/{$commentId}/delete/")
        ->addPost('_uuid', $this->ig->uuid)
        ->addPost('_uid', $this->ig->account_id)
        ->addPost('_csrftoken', $this->ig->client->getToken())
        ->getResponse(new Response\DeleteCommentResponse());
    }

    /**
     * Delete multiple comments.
     *
     * @param string $mediaId    The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $commentIds List of comment IDs to delete.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DeleteCommentResponse
     */
    public function deleteComments(
        $mediaId,
        $commentIds)
    {
        if (!is_array($commentIds)) {
            $commentIds = [$commentIds];
        }

        $string = [];
        foreach ($commentIds as $commentId) {
            $string[] = "$commentId";
        }

        $comment_ids_to_delete = implode(',', $string);

        return $this->ig->request("media/{$mediaId}/comment/bulk_delete/")
        ->addPost('_uuid', $this->ig->uuid)
        ->addPost('_uid', $this->ig->account_id)
        ->addPost('_csrftoken', $this->ig->client->getToken())
        ->addPost('comment_ids_to_delete', $comment_ids_to_delete)
        ->getResponse(new Response\DeleteCommentResponse());
    }

    /**
     * Like a comment.
     *
     * @param string $commentId The comment's ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentLikeUnlikeResponse
     */
    public function likeComment(
        $commentId)
    {
        return $this->ig->request("media/{$commentId}/comment_like/")
        ->addPost('_uuid', $this->ig->uuid)
        ->addPost('_uid', $this->ig->account_id)
        ->addPost('_csrftoken', $this->ig->client->getToken())
        ->getResponse(new Response\CommentLikeUnlikeResponse());
    }

    /**
     * Unlike a comment.
     *
     * @param string $commentId The comment's ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentLikeUnlikeResponse
     */
    public function unlikeComment(
        $commentId)
    {
        return $this->ig->request("media/{$commentId}/comment_unlike/")
        ->addPost('_uuid', $this->ig->uuid)
        ->addPost('_uid', $this->ig->account_id)
        ->addPost('_csrftoken', $this->ig->client->getToken())
        ->getResponse(new Response\CommentLikeUnlikeResponse());
    }

    /**
     * Translates comments and/or media captions.
     *
     * Note that the text will be translated to American English (en-US).
     *
     * @param string|string[] $commentIds The IDs of one or more comments and/or media IDs
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TranslateResponse
     */
    public function translateComments(
        $commentIds)
    {
        if (is_array($commentIds)) {
            $commentIds = implode(',', $commentIds);
        }

        return $this->ig->request("language/bulk_translate/?comment_ids={$commentIds}")
        ->getResponse(new Response\TranslateResponse());
    }

    /**
     * Tag a user in a media item.
     *
     * @param string      $mediaId     The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string      $userId      Numerical UserPK ID.
     * @param array|float $position    Position relative to image where the tag should sit. Example: [0.4890625,0.6140625]
     * @param string      $captionText Caption text.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\EditMediaResponse
     */
    public function tagUser(
        $mediaId,
        $userId,
        $position,
        $captionText = '')
    {
        $usertag = '{"removed":[],"in":[{"position":['.$position[0].','.$position[1].'],"user_id":"'.$userId.'"}]}';

        return $this->edit($mediaId, $captionText, $usertag);
    }

    /**
     * Untag a user from a media item.
     *
     * @param string $mediaId     The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $userId      Numerical UserPK ID.
     * @param string $captionText Caption text.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\EditMediaResponse
     */
    public function untagUser(
        $mediaId,
        $userId,
        $captionText = '')
    {
        $usertag = '{"removed":["'.$userId.'"],"in":[]}';

        return $this->edit($mediaId, $captionText, $usertag);
    }

    /**
     * Remove yourself from a tagged media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaResponse
     */
    public function removeSelfTag(
        $mediaId)
    {
        return $this->ig->request("usertags/{$mediaId}/remove/")
        ->addPost('_uuid', $this->ig->uuid)
        ->addPost('_uid', $this->ig->account_id)
        ->addPost('_csrftoken', $this->ig->client->getToken())
        ->getResponse(new Response\MediaResponse());
    }

    /**
     * Save a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SaveAndUnsaveMedia
     */
    public function save(
        $mediaId)
    {
        return $this->ig->request("media/{$mediaId}/save/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->setSignedPost(true)
            ->getResponse(new Response\SaveAndUnsaveMedia());
    }

    /**
     * Unsave a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SaveAndUnsaveMedia
     */
    public function unsave(
        $mediaId)
    {
        return $this->ig->request("media/{$mediaId}/unsave/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->setSignedPost(true)
            ->getResponse(new Response\SaveAndUnsaveMedia());
    }

    /**
     * Get saved media items feed.
     *
     * @param null|string $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SavedFeedResponse
     */
    public function getSavedFeed(
        $maxId = null)
    {
        $requestData = $this->ig->request('feed/saved/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->setSignedPost(true);

        if (!is_null($maxId)) {
            $requestData->addParams('max_id', $maxId);
        }

        return $requestData->getResponse(new Response\SavedFeedResponse());
    }
}
