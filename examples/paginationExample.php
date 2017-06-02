<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->setUser($username, $password);
    $ig->login();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $followings = [];

    // Starting at "null" means starting at the first page.
    $maxId = null;
    do {
        // Request the page corresponding to maxId.
        $response = $ig->people->getSelfFollowing(null, $maxId);

        // In this example we're merging the response array, but we can do anything.
        $followings = array_merge($followings, $response->getUsers());

        // Now we must update the maxId variable to the "next page".
        // This will be a null value again when we've reached the last page!
        // And we will stop looping through pages as soon as maxId becomes null.
        $maxId = $response->getNextMaxId();
    } while ($maxId !== null); // Must use "!==" for comparison instead of "!=".

    echo "Who I am following:\n";
    foreach ($followings as $following) {
        echo '- '.$following->getUsername().".\n";
    }
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
