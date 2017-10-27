<?php

namespace InstagramAPI\Media\Video;

class FFmpegWrapper
{
    /** @var string */
    protected $_ffmpegBinary;

    /** @var bool */
    protected $_hasNoAutorotate;

    /**
     * Run a command and wrap errors into an Exception (if any).
     *
     * @param string $command
     *
     * @throws \RuntimeException
     */
    public function run(
        $command)
    {
        exec(
            sprintf('%s -v error %s 2>&1', escapeshellarg($this->_ffmpegBinary), $command),
            $output,
            $returnCode
        );

        if ($returnCode) {
            $errorMsg = sprintf('FFmpeg Errors: ["%s"], Command: "%s".', implode('"], ["', $output), $command);

            throw new \RuntimeException($errorMsg, $returnCode);
        }
    }

    /**
     * Fetch the features set from the ffmpeg binary.
     */
    protected function _fetchFeatures()
    {
        try {
            $this->run('-noautorotate -f lavfi -i color=color=red -t 1 -f null -');
            $this->_hasNoAutorotate = true;
        } catch (\RuntimeException $e) {
            $this->_hasNoAutorotate = false;
        }
    }

    /**
     * FFmpegWrapper constructor.
     *
     * @param string $ffmpegBinary
     */
    public function __construct(
        $ffmpegBinary)
    {
        $this->_ffmpegBinary = $ffmpegBinary;

        $this->_fetchFeatures();
    }

    /**
     * Get a path to the ffmpeg binary.
     *
     * @return string
     */
    public function getFFmpegBinary()
    {
        return $this->_ffmpegBinary;
    }

    /**
     * Check whether ffmpeg has -noautorotate flag.
     *
     * @return bool
     */
    public function hasNoAutorotate()
    {
        return $this->_hasNoAutorotate;
    }
}
