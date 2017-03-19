<?php

namespace InstagramAPI\Devices;

use InstagramAPI\Constants;

/**
 * Android device User-Agent builder.
 *
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class UserAgent
{
    /**
     * How to format the user agent string.
     *
     * @var string
     */
    const USER_AGENT_FORMAT = 'Instagram %s Android (%s/%s; %s; %s; %s; %s; %s; %s; %s)';

    /**
     * Generates a User Agent string from a Device.
     *
     * @param \InstagramAPI\Devices\Device $device
     *
     * @throws \InvalidArgumentException If the device parameter is invalid.
     *
     * @return string
     */
    public static function buildUserAgent(
        Device $device)
    {
        if (!$device instanceof Device) {
            throw new \InvalidArgumentException('The device parameter must be a Device class instance.');
        }

        return sprintf(
            self::USER_AGENT_FORMAT,
            Constants::VERSION, // App version ("10.8.0").
            $device->getAndroidVersion(),
            $device->getAndroidRelease(),
            $device->getDPI(),
            $device->getResolution(),
            $device->getManufacturer(),
            $device->getModel(),
            $device->getDevice(),
            $device->getCPU(),
            Constants::USER_AGENT_LOCALE // Locale ("en_US").
        );
    }
}
