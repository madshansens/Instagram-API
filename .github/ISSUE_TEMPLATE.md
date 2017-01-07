## Please follow the guide below

- You will be asked some questions and requested to provide some information, please read them **carefully** and answer completely.
- Put an `x` into all the boxes [ ] relevant to your issue (like so [x]).
- Use the *Preview* tab to see how your issue will actually look like.

---

### Before submitting an issue make sure you have:
- [ ] [Searched](https://github.com/mgp25/Instagram-API/search?type=Issues) the bugtracker for similar issues including **closed** ones
- [ ] [Read the FAQ](https://github.com/mgp25/Instagram-API/wiki/FAQ)
- [ ] [Read the wiki](https://github.com/mgp25/Instagram-API/wiki)
- [ ] [Reviewed the examples](https://github.com/mgp25/Instagram-API/tree/master/examples)
- [ ] [Installed the api using ``composer``](https://github.com/mgp25/Instagram-API#installation)

### Purpose of your issue?
- [ ] Bug report (encountered problems/errors)
- [ ] Feature request (request for a new functionality)
- [ ] Question
- [ ] Other

---

### The following sections requests more details for particular types of issues, you can remove any section (the contents between the triple ---) not applicable to your issue.

---

### For a *bug report*, you must include below *code* that will replicate the error, and the *error log/traceback*.

Code:

```php
// Please provide your own code here
require("./vendor/autoload.php");

try {
    $debug = true;
    $api = new \InstagramAPI\Instagram($debug);
    $api->setUser('yourusername', 'yourpassword');
    $api->login();
    $result = $api->comment('14123451234567890_1234567890', "Hello World");
    var_dump($result);
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
```

Error/Log/var_dump:

```php
// Please provide your error log/dump here
RESPONSE: {"status": "fail", "message": "Sorry, the comment data may have been corrupted."}

InstagramAPI\CommentResponse : Sorry, the comment data may have been corrupted.
```

---

### For a new endpoint *feature request*, you should include below the *capture of the request and response*.

Request:

```http
# Please provide your capture below
GET /api/v1/si/fetch_headers/?guid=123456abcdeff19cc2f123456&challenge_type=signup HTTP/1.1
Host: i.instagram.com
Connection: keep-alive
X-IG-Connection-Type: mobile(UMTS)
X-IG-Capabilities: 3ToAAA==
Accept-Language: en-US
Cookie: csrftoken=g79dofABCDEFGII3LI7YdHei1234567; mid=WFI52QABAAGrbKL-ABCDEFGHIJK
User-Agent: Instagram 10.3.0 Android (18/4.3; 320dpi; 720x1280; Xiaomi; HM 1SW; armani; qcom; en_US)
Accept-Encoding: gzip, deflate, sdch
```

Response:

```http
# Please provide your capture below
HTTP/1.1 200 OK
Content-Language: en
Expires: Sat, 01 Jan 2000 00:00:00 GMT
Vary: Cookie, Accept-Language
Pragma: no-cache
Cache-Control: private, no-cache, no-store, must-revalidate
Date: Thu, 15 Dec 2016 08:50:19 GMT
Content-Type: application/json
Set-Cookie: csrftoken=g79dofABCDEFGII3LI7YdHei1234567; expires=Thu, 14-Dec-2017 08:50:19 GMT; Max-Age=31449600; Path=/; secure
Connection: keep-alive
Content-Length: 16

{"status": "ok"}
```
---

### Describe your issue

Explanation of your issue goes here. Please make sure the description is worded well enough to be understood with as much context and examples as possible.
