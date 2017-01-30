<?php

include __DIR__.'/../../../../vendor/autoload.php';
require_once __DIR__.'/../src/Checkpoint.php';

$debug = true;

echo "####################\n";
echo "#                  #\n";
echo "#    CHECKPOINT    #\n";
echo "#                  #\n";
echo "####################\n";

echo "\n\nYour username: ";
$username = trim(fgets(STDIN));

if ($username == '') {
    echo "\n\nYou have to set your username\n";
    exit();
}

$c = new \InstagramAPI\Checkpoint($username, $debug);

$token = $c->doCheckpoint();

echo "\n\nCode you have received via mail: ";
$code = trim(fgets(STDIN));

$c->checkpointThird($code, $token);

echo "\n\nDone";
