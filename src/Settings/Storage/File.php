<?php

namespace InstagramAPI\Settings\Storage;

use InstagramAPI\Constants;

class File implements \InstagramAPI\Settings\StorageInterface
{
    /**
     * Key-value cache which holds all settings in memory.
     *
     * @var array
     */
    private $_sets;

    /**
     * Path to the cookies file.
     *
     * @var string
     */
    private $_cookiesPath;

    /**
     * Path to the settings file.
     *
     * @var string
     */
    private $_settingsPath;

    /**
     * Constructor.
     *
     * @param string $username     The instagram username the settings belong to.
     * @param string $settingsPath Where to store the settings files.
     */
    public function __construct(
        $username,
        $settingsPath = null)
    {
        // Decide which settings-file paths to use.
        if (empty($settingsPath)) {
            $settingsPath = Constants::SRC_DIR.'/../sessions/';
        }
        $this->_cookiesPath = $settingsPath.$username.DIRECTORY_SEPARATOR.$username.'-cookies.dat';
        $this->_settingsPath = $settingsPath.$username.DIRECTORY_SEPARATOR.$username.'-settings.dat';

        // Test write-permissions to the settings file and create if necessary.
        $this->_checkPermissions();

        // Read all existing settings.
        $this->_loadSettingsFromDisk();
    }

    /**
     * Does a preliminary guess about whether we're logged in.
     *
     * The session it looks for may be expired, so there's no guarantee.
     *
     * @return bool
     */
    public function maybeLoggedIn()
    {
        return file_exists($this->_cookiesPath)
            && !empty($this->get('username_id'))
            && !empty($this->get('token'));
    }

    /**
     * Read a settings value.
     *
     * @param string $key     Name of setting.
     * @param mixed  $default What to return if setting not found.
     *
     * @return mixed
     */
    public function get(
        $key,
        $default = null)
    {
        if ($key == 'sets') {
            return $this->_sets; // Return 'sets' itself which contains all data.
        }

        return isset($this->_sets[$key])
                ? $this->_sets[$key]
                : $default;
    }

    /**
     * Store a settings value.
     *
     * @param string $key   Name of setting.
     * @param string $value The data to store. Must be castable to string.
     *
     * @throws \InvalidArgumentException
     */
    public function set(
        $key,
        $value)
    {
        if ($key == 'sets') {
            throw new \InvalidArgumentException('You are not allowed to write to the special "sets" key.');
        }

        // Cast the value to a string to ensure we don't try writing objects.
        $value = (string) $value;

        // Remove all trailing newline characters and spaces.
        $value = rtrim($value, "\r\n ");

        // Check if the value differs from our cached on-disk value.
        // NOTE: This optimizes disk writes by only writing when values change!
        if (!array_key_exists($key, $this->_sets) || $this->_sets[$key] !== $value) {
            // The value differs, so save to memory cache and write to disk.
            $this->_sets[$key] = $value;
            $this->save();
        }
    }

    /**
     * Loads all settings from disk.
     */
    private function _loadSettingsFromDisk()
    {
        $this->_sets = [];

        if (file_exists($this->_settingsPath)) {
            $lines = @file($this->_settingsPath, FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                foreach ($lines as $line) {
                    // Remove all trailing newline characters and spaces.
                    $line = rtrim($line, "\r\n ");

                    // Key must be at least one character but we allow empty values.
                    // NOTE: Settings commented out with a leading "#" will still be
                    // stored in our internal "sets" array, so that it gets written
                    // back to disk and won't get discarded. But the leading "#"
                    // ensures that we won't use the setting-value internally!
                    if (preg_match('/^([^=]+)=(.*)$/', $line, $matches)) {
                        $key = $matches[1];
                        $value = $matches[2];

                        // Cache the value internally.
                        $this->_sets[$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * Writes the in-memory settings cache to disk.
     *
     * Don't call this manually. It is automatically done by set() whenever a
     * setting is changed compared to its value on disk.
     */
    public function save()
    {
        // Generate a text representation of all settings.
        $data = '';
        foreach ($this->_sets as $key => $value) {
            $data .= "{$key}={$value}\n";
        }

        // Perform an atomic diskwrite, which prevents accidental truncation.
        // NOTE: If we had just written directly to settingsPath, the file would
        // have become corrupted if the script was killed mid-write. The atomic
        // write process guarantees that the data is fully written to disk.
        $this->_atomicwrite($this->_settingsPath, $data);
    }

    /**
     * Atomic filewriter.
     *
     * Safely writes new contents to a file using an atomic two-step process.
     * If the script is killed before the write is complete, only the temporary
     * trash file will be corrupted.
     *
     * @param string $filename     Filename to write the data to.
     * @param string $data         Data to write to file.
     * @param string $atomicSuffix Lets you optionally provide a different
     *                             suffix for the temporary file.
     *
     * @return mixed Number of bytes written on success, otherwise FALSE.
     */
    private function _atomicwrite(
        $filename,
        $data,
        $atomicSuffix = 'atomictmp')
    {
        // Perform an exclusive (locked) overwrite to a temporary file.
        $filenameTmp = sprintf('%s.%s', $filename, $atomicSuffix);
        $writeResult = @file_put_contents($filenameTmp, $data, LOCK_EX);
        if ($writeResult !== false) {
            // Now move the file to its real destination (replaced if exists).
            $moveResult = @rename($filenameTmp, $filename);
            if ($moveResult === true) {
                // Successful write and move. Return number of bytes written.
                return $writeResult;
            }
        }

        return false; // Failed.
    }

    /**
     * Checks whether we can write to the settings folder.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     *
     * @return bool
     */
    private function _checkPermissions()
    {
        $folder = dirname($this->_settingsPath);
        if (is_writable($folder)) {
            return true;
        } elseif (mkdir($folder, 0755, true)) {
            return true;
        } elseif (chmod($folder, 0755)) {
            return true;
        }

        throw new \InstagramAPI\Exception\SettingsException('The settings folder is not writable.');
    }

    /**
     * Get the path to the cookies file.
     *
     * This is used by \InstagramAPI\Client!
     *
     * @return string
     */
    public function getCookiesPath()
    {
        return $this->_cookiesPath;
    }
}
