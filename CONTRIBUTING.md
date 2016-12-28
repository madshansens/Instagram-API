## Contributing

A brief guide for contributors.

In order to add endpints to the API you will need to capture the requests first. For this, you can use the proxy you want. You can find a lot of information in internet. Remember that you need to install CA in your device so the proxy can decrypt the requests and show to you.


Once you have the endpoint and params, how to add them? Easy, you can follow this example:

```php
    public function myExampleEndpint()
    {
        return $this->request('awesome/endpoint/')
        ->setSignedPost(false)
        ->addPost('_uuid', $this->uuid)
        ->addPost('user_ids', implode(',', $userList))
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new awesomeResponse());
    }
```

In the example above you can see `('awesome/endpoint/')` which contains the endpoint captured. We are simulating a POST request, so you can add them easily by doing `->addPost('_uuid', $this->uuid)`

Which is basically:

```php
->addPost(key, value)
```

Where key is the name of the POST param, and value (i think its obvious).

Some of the requests are signed, this means there is a hash concatenated to the JSON, in order to make a signed request, we can enable or disable with the following line:

```php
->setSignedPost($isSigned)
```

`$isSigned` is boolean, if you want a signed request, you can set it to `true`

If the request is a GET request, you can add the params like this:

```php
->addParams(key, value)
```

And finally, we always add the `getResponse` function, which will read the response and return us an object with all the values:

```php
->getResponse(new awesomeResponse());
```

Now you might be wondering, how do you create that response class now, but there is nothing to worry about, it's very simple.

Imagine you have the following response:

```json
{"items": [{"user": {"is_verified": false, "has_anonymous_profile_picture": false, "is_private": false, "full_name": "awesome", "username": "awesome", "pk": "uid", "profile_pic_url": "profilepic"}, "large_urls": [], "caption": "", "thumbnail_urls": ["thumb1", "thumb2", "thumb3", "thumb4"]}], "status": "ok"}
```

You can use [http://jsoneditoronline.org](http://jsoneditoronline.org/) for better visualization:

<img src="https://s29.postimg.org/3xyopcbg7/insta_help.jpg" width="300">

So `awesomeResponse` class should contain one public var named `items`, JSONMapper needs also a comment to know if its a class, string array, etc, by default, if you dont specify any comment, it will read it as a string.

In this scenario:

```php
    /**
     * @var Suggestion[]
     */
    public $items;
 ```
 
 `items` will contain an array of Suggestion object. And `Suggestion` will look like this:

```php
<?php

namespace InstagramAPI;

class Suggestion
{
    public $media_infos;
    public $social_context;
    public $algorithm;
    /**
     * @var string[]
     */
    public $thumbnail_urls;
    public $value;
    public $caption;
    /**
     * @var User
     */
    public $user;
    /**
     * @var string[]
     */
    public $large_urls;
    public $media_ids;
    public $icon;
}
```

Here in `Suggestion` you see vars that doesnt appear in this request, but many others shares the same object and depending the request, the responses may change.

So our `awesomeResponse.php` is like the following code:

```php
<?php

namespace InstagramAPI;

class awesomeResponse extends Response
{
    /**
     * @var Suggestion[]
     */
    public $items;
}
```

Now you can test your new endpoint, in order to see the response object:

```
$a = $i->myNewRequest();
var_dump($a); // this will print the response object
```

And finally, how do you access to the objects data? 

```php
$items = $a->getItems();

$user = $items[0]->getMediaInfos();
```

Hope you find this useful.