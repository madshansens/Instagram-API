<?php

namespace InstagramAPI\Devices;

class Device
{
    /**
     * The Android version of Instagram currently runs on Android OS 2.2+.
     *
     * They may raise this requirement in the future.
     *
     * @var string
     *
     * @see https://help.instagram.com/513067452056347
     */
    const REQUIRED_ANDROID_VERSION = '2.2';

    /**
     * Which device string we were built with internally.
     *
     * @var string
     */
    protected $_deviceString;

    /**
     * The user agent to use for this device. Built from properties.
     *
     * @var string
     */
    protected $_userAgent;

    // Properties parsed from the device string...

    protected $_androidVersion;
    protected $_androidRelease;
    protected $_dpi;
    protected $_resolution;
    protected $_manufacturer;
    protected $_model;
    protected $_device;
    protected $_cpu;

    /**
     * Constructor.
     *
     * @param string|null $deviceString (optional) The device string to attempt
     *                                  to construct from. If NULL or not a good
     *                                  device, we'll use a random good device.
     */
    public function __construct(
        $deviceString = null)
    {
        // Use the provided device if a valid good device. Otherwise use random.
        if (!is_string($deviceString) || !GoodDevices::isGoodDevice($deviceString)) {
            $deviceString = GoodDevices::getRandomGoodDevice();
        }

        // Initialize ourselves from the device string.
        $this->_initFromDeviceString($deviceString);
    }

    /**
     * Parses a device string into its component parts and sets internal fields.
     *
     * Does no validation to make sure the string is one of the good devices.
     *
     * @param string $deviceString
     *
     * @throws \RuntimeException If the device string is invalid.
     */
    protected function _initFromDeviceString(
        $deviceString)
    {
        // Split the device identifier into its components and verify it.
        $parts = explode('; ', $deviceString);
        if (count($parts) !== 7) {
            throw new \RuntimeException(sprintf('Device string "%s" does not conform to the required device format.', $deviceString));
        }

        // Check the android version.
        $androidOS = explode('/', $parts[0]);
        if (version_compare($androidOS[1], self::REQUIRED_ANDROID_VERSION, '<')) {
            throw new \RuntimeException(sprintf('Device string "%s" does not meet the minimum required Android version "%s" for Instagram.', $deviceString, self::REQUIRED_ANDROID_VERSION));
        }

        // Check the screen resolution.
        $resolution = explode('x', $parts[2]);
        $pixelCount = (int) $resolution[0] * (int) $resolution[1];
        if ($pixelCount < 2073600) { // 1920x1080.
            throw new \RuntimeException(sprintf('Device string "%s" does not meet the minimum resolution requirement of 1920x1080.', $deviceString));
        }

        // Store all field values.
        $this->_deviceString = $deviceString;
        $this->_androidVersion = $androidOS[0]; // "23".
        $this->_androidRelease = $androidOS[1]; // "6.0.1".
        $this->_dpi = $parts[1];
        $this->_resolution = $parts[2];
        $this->_manufacturer = $parts[3];
        $this->_model = $parts[4];
        $this->_device = $parts[5];
        $this->_cpu = $parts[6];

        // Build our user agent.
        $this->_userAgent = UserAgent::buildUserAgent($this);
    }

    // Getters for all properties...

    public function getDeviceString()
    {
        return $this->_deviceString;
    }

    public function getUserAgent()
    {
        return $this->_userAgent;
    }

    public function getAndroidVersion()
    {
        return $this->_androidVersion;
    }

    public function getAndroidRelease()
    {
        return $this->_androidRelease;
    }

    public function getDPI()
    {
        return $this->_dpi;
    }

    public function getResolution()
    {
        return $this->_resolution;
    }

    public function getManufacturer()
    {
        return $this->_manufacturer;
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function getDevice()
    {
        return $this->_device;
    }

    public function getCPU()
    {
        return $this->_cpu;
    }
}
