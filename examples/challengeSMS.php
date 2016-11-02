<?php

include __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/ChallengeSMS.php';

$debug = true;

echo "#####################\n";
echo "#                   #\n";
echo "#   SMS CHALLENGE   #\n";
echo "#                   #\n";
echo "#####################\n";

echo "\n\nYour username: ";
$username = trim(fgets(STDIN));
echo $username."\n";

if ($username == '') {
    echo "\n\nYou have to set your username\n";
    exit();
}

echo "\n\nYour settings path folder ([ENTER] if dedault): ";
$settingsPath = trim(fgets(STDIN));

if ($settingsPath == '') {
    $settingsPath = null;
}

$c = new \InstagramAPI\ChallengeSMS($username, $settingsPath, $debug);

$c->startChallenge();

while ($c->getStep() < 3) {
    switch ($c->getStep()) {
        case 1:
            echo "\n\nInsert Phone number: ";
            $code = trim(fgets(STDIN));
            $c->setPhone($code);
            break;

        case 2:
            echo "\n\nInsert Security Code (leavy empty to reset): ";
            $code = trim(fgets(STDIN));
            if ('' == $code) {
                $c->reset();
            } else {
                $c->setCode($code);
            }
            break;

        default:
            echo "No function for this! Press Y to reset, enter to retry\n";
            $code = trim(fgets(STDIN));
            if ($code == 'Y') {
                $c->reset();
            } else {
                $c->startChallenge();
            }
            break;
    }
}

echo "\n\nDone";
