<?php

namespace InstagramAPI;

/**
 * Bridge between Instagram Client calls, the object mapper & Response objects.
 */
class Request
{
    /**
     * The Instagram class instance we belong to.
     *
     * @var \InstagramAPI\Instagram
     */
    protected $_parent;

    /**
     * Which API version to use for this request.
     *
     * @var int
     */
    protected $_apiVersion = 1;

    protected $_url;
    protected $_params = [];
    protected $_posts = [];

    /**
     * Whether this API call needs authorization.
     *
     * On by default since most calls require authorization.
     *
     * @var bool
     */
    protected $_needsAuth = true;

    protected $_signedPost = true;

    public function __construct(
        \InstagramAPI\Instagram $parent,
        $url)
    {
        $this->_parent = $parent;
        $this->_url = $url;

        return $this;
    }

    public function setVersion(
        $apiVersion = 1)
    {
        $this->_apiVersion = $apiVersion;

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

    public function setNeedsAuth(
        $needsAuth = true)
    {
        $this->_needsAuth = $needsAuth;

        return $this;
    }

    public function setSignedPost(
        $signedPost = true)
    {
        $this->_signedPost = $signedPost;

        return $this;
    }

    public function getResponse(
        $baseClass = null)
    {
        if ($this->_params) {
            $endpoint = $this->_url.'?'.http_build_query($this->_params);
        } else {
            $endpoint = $this->_url;
        }

        if ($this->_posts) {
            if ($this->_signedPost) {
                $post = Signatures::generateSignatureForPost(json_encode($this->_posts));
            } else {
                $post = http_build_query($this->_posts);
            }
        } else {
            $post = null;
        }

        $response = $this->_parent->client->api($this->_apiVersion, $endpoint, $post, $this->_needsAuth, false);

        // Decode to base class if provided, or otherwise return raw object.
        return $baseClass !== null
               ? $this->_parent->client->getMappedResponseObject($baseClass, $response)
               : $response;
    }
}
