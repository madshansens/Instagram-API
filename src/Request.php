<?php

namespace InstagramAPI;

/**
 * Bridge between HttpInterface object & mapper & response.
 */
class Request
{
    protected $_url;
    protected $_params = [];
    protected $_posts = [];
    protected $_requireLogin = false;
    protected $_checkStatus = true;
    protected $_signedPost = true;

    public function __construct(
        $url)
    {
        $this->_url = $url;

        return $this;
    }

    public function addParams(
        $key,
        $value)
    {
        if ($value === true) {
            $value = 'true';
        }
        $this->_params[$key] = $value;

        return $this;
    }

    public function addPost(
        $key,
        $value)
    {
        $this->_posts[$key] = $value;

        return $this;
    }

    public function requireLogin(
        $requireLogin = false)
    {
        $this->_requireLogin = $requireLogin;

        return $this;
    }

    public function setCheckStatus(
        $checkStatus = true)
    {
        $this->_checkStatus = $checkStatus;

        return $this;
    }

    public function setSignedPost(
        $signedPost = true)
    {
        $this->_signedPost = $signedPost;

        return $this;
    }

    public function getResponse(
        $baseClass,
        $includeHeader = false)
    {
        $instagramObj = Instagram::getInstance();

        if ($this->_params) {
            $endpoint = $this->_url.'?'.http_build_query($this->_params);
        } else {
            $endpoint = $this->_url;
        }

        if ($this->_posts) {
            if ($this->_signedPost) {
                $post = Signatures::generateSignature(json_encode($this->_posts));
            } else {
                $post = http_build_query($this->_posts);
            }
        } else {
            $post = null;
        }

        $response = $instagramObj->http->api($endpoint, $post, $this->_requireLogin, false);

        $responseObject = $instagramObj->http->getMappedResponseObject(
            $baseClass,
            $response[1], // [0] = Token. [1] = The actual server response.
            $this->_checkStatus, // Whether to validate that API response "status" MUST be Ok.
            ($includeHeader ? $response : null) // null = Reuse $response[1].
        );

        return $responseObject;
    }
}
