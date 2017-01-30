<?php

require_once __DIR__.'/../src/InstagramException.php';

$c = new \InstagramAPI\InstagramException('login_required');
echo($c->getCode() == \InstagramAPI\IG_LOGIN_REQUIRED)."\n";

$c = new \InstagramAPI\InstagramException('feedback_required');
echo($c->getCode() == \InstagramAPI\IG_FEEDBACK_REQUIRED)."\n";

$c = new \InstagramAPI\InstagramException('checkpoint_required');
echo($c->getCode() == \InstagramAPI\IG_CHECKPOINT_REQUIRED)."\n";

$c = new \InstagramAPI\InstagramException('The password you entered is incorrect. Please try again.');
echo($c->getCode() == \InstagramAPI\IG_INCORRECT_PASSWORD)."\n";

$c = new \InstagramAPI\InstagramException('Your account has been disabled for violating our terms. Learn how you may be able to restore your account.');
echo($c->getCode() == \InstagramAPI\IG_ACCOUNT_DISABLED)."\n";
