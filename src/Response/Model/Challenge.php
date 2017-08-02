<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getApiPath()
 * @method mixed getHideWebviewHeader()
 * @method mixed getLock()
 * @method mixed getLogout()
 * @method mixed getNativeFlow()
 * @method mixed getUrl()
 * @method bool isApiPath()
 * @method bool isHideWebviewHeader()
 * @method bool isLock()
 * @method bool isLogout()
 * @method bool isNativeFlow()
 * @method bool isUrl()
 * @method setApiPath(mixed $value)
 * @method setHideWebviewHeader(mixed $value)
 * @method setLock(mixed $value)
 * @method setLogout(mixed $value)
 * @method setNativeFlow(mixed $value)
 * @method setUrl(mixed $value)
 */
class Challenge extends AutoPropertyHandler
{
    public $url;
    public $api_path;
    public $hide_webview_header;
    public $lock;
    public $logout;
    public $native_flow;
}
