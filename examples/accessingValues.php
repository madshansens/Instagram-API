<?php

include __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/InstagramRegistration.php';

$i = new \InstagramAPI\Instagram(true, false);
$i->setUser($username, $password);

try {
    $i->login();
} catch (Exception $e) {
    echo "something went wrong ". $e->getMessage()."\n";
    exit(0);
}
try {
    $a = $i->getPopularFeed();
    $items = $a->getItems(); // PopularFeed Response has a items var, so you can get it with getItems()

    $firstItem_mediaId = $items[0]->getId(); // Item object has a id var, you can grab this value by using getId()
    $firstItem_device_timestamp = $items[0]->getDeviceTimestamp(); // Item object has a device_stamp var, you can get it by using getDeviceStamp

    // Something similar happens with. var is called image_versions_2, you can get it with getImageVersions2, and so on.
    $firstItem_image_versions = $items[0]->getImageVersions2()->getCandidates()[0]->getUrl();

    echo "There are ".count($items)."\n";

    echo "First item has media id: $firstItem_mediaId \n";
    echo "First item timestamp is: $firstItem_device_timestamp\n";
    echo "One of the first item image version candidates is: $firstItem_image_versions";

} catch (Exception $e) {
    echo $e->getMessage();
}
