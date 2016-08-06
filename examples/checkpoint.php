<?php

include __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/Checkpoint.php';

$username = ''; // Your username
$settingsPath = null;
$debug = false;

$c = new \InstagramAPI\Checkpoint($username, $settingsPath, $debug);

echo "####################\n";
echo "#                  #\n";
echo "#    CHECKPOINT    #\n";
echo "#                  #\n";
echo "####################\n";

if ($username == '') {
    echo "\n\nYou have to set your username\n";
    exit();
}

$token = $c->doCheckpoint();

echo "\n\nCode you have received via mail: ";
$code = trim(fgets(STDIN));

$c->checkpointThird($code, $token);

echo "\n\nDone";
