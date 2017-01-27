<?php

require_once __DIR__.'/../src/InstagramException.php';

$c = new \InstagramAPI\InstagramException("login_required");
echo $c->getCode()."\n";

$c = new \InstagramAPI\InstagramException("feedback_required");
echo $c->getCode()."\n";

$c = new \InstagramAPI\InstagramException("checkpoint_required");
echo $c->getCode()."\n";

$c = new \InstagramAPI\InstagramException("The password you entered is incorrect. Please try again.");
echo $c->getCode()."\n";
