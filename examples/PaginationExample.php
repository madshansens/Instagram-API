<?php

include '../../../../vendor/autoload.php';
require '../src/Instagram.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = false;
//////////////////////

// THIS IS AN EXAMPLE OF HOW TO USE NEXT_MAX_ID TO PAGINATE
// IN THIS EXAMPLE, WE ARE RETRIEVING SELF FOLLOWERS
// BUT THE PROCESS IS SIMILAR IN OTHER REQUESTS

$i = new \InstagramAPI\Instagram($debug);

$i->setUser($username, $password);

try {
    $i->login();
} catch (Exception $e) {
    echo 'something went wrong '.$e->getMessage()."\n";
    exit(0);
}
try {
    $helper = null;
    $followers = [];

    do {
        if (is_null($helper)) {
            $helper = $i->getSelfUserFollowers();
        } else {
            $helper = $i->getSelfUserFollowers($helper->getNextMaxId());
        }

        $followers = array_merge($followers, $helper->getUsers());
    } while (!is_null($helper->getNextMaxId()));

    echo "My followers: \n";
    foreach ($followers as $follower) {
        echo '- '.$follower->getUsername()."\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
