<?php

namespace InstagramAPI;

class UserAgent
{
    protected $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    protected function getDeviceData()
    {
        $csvfile = __DIR__.'/devices.csv';
        $lines = @file($csvfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new \Exception('Unable to read devices.csv.');
        }
        $randomLine = $lines[array_rand($lines, 1)];
        $deviceData = explode(';', $randomLine);

        return $deviceData;
    }

    public function buildUserAgent()
    {
        $deviceData = $this->getDeviceData();
        $this->parent->settings->set('manufacturer', $deviceData[0]);
        $this->parent->settings->set('device', $deviceData[1]);
        $this->parent->settings->set('model', $deviceData[2]);

        return sprintf('Instagram %s Android (18/4.3; 320dpi; 720x1280; %s; %s; %s; qcom; en_US)', Constants::VERSION, $deviceData[0], $deviceData[1], $deviceData[2]);
    }
}
