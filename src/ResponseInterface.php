<?php

namespace InstagramAPI;

use Psr\Http\Message\ResponseInterface as HttpResponseInterface;

/**
 * The interface that all response-classes must implement.
 *
 * Remember that all response-classes must "extend AutoPropertyHandler",
 * "implements ResponseInterface", and "use ResponseTrait", otherwise they
 * won't work properly.
 */
interface ResponseInterface
{
    /**
     * Checks if the response was successful.
     *
     * @return bool
     */
    public function isOk();

    /**
     * Sets the status.
     *
     * @param string|null $status
     */
    public function setStatus(
        $status);

    /**
     * Gets the status.
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Checks if a status value exists.
     *
     * @return bool
     */
    public function isStatus();

    /**
     * Sets the message.
     *
     * @param string|null $message
     */
    public function setMessage(
        $message);

    /**
     * Gets the message.
     *
     * @throws \Exception If the message object is of an unsupported type.
     *
     * @return string|null A message string if one exists, otherwise NULL.
     */
    public function getMessage();

    /**
     * Checks if a message value exists.
     *
     * @return bool
     */
    public function isMessage();

    /**
     * Sets the special API status messages.
     *
     * @param Response\Model\_Message[]|null $_messages
     */
    public function set_Messages(
        $_messages);

    /**
     * Gets the special API status messages.
     *
     * This can exist in any Instagram API response, and carries special status
     * information. Known messages: "fb_needs_reauth", "vkontakte_needs_reauth",
     * "twitter_needs_reauth", "ameba_needs_reauth", "update_push_token".
     *
     * @return Response\Model\_Message[]|null Messages if any, otherwise NULL.
     */
    public function get_Messages();

    /**
     * Checks if any API status messages value exists.
     *
     * @return bool
     */
    public function is_Messages();

    /**
     * Sets the full response.
     *
     * @param mixed $response
     */
    public function setFullResponse(
        $response);

    /**
     * Gets the full response.
     *
     * @return mixed
     */
    public function getFullResponse();

    /**
     * Checks if a full response value exists.
     *
     * @return bool
     */
    public function isFullResponse();

    /**
     * Sets the HTTP response.
     *
     * @param HttpResponseInterface $response
     */
    public function setHttpResponse(
        HttpResponseInterface $response);

    /**
     * Gets the HTTP response.
     *
     * @return HttpResponseInterface
     */
    public function getHttpResponse();

    /**
     * Checks if an HTTP response value exists.
     *
     * @return bool
     */
    public function isHttpResponse();
}
