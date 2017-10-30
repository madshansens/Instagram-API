<?php

namespace InstagramAPI\Media\Video;

class FFmpegWrapper
{
    /** @var string */
    protected $_ffmpegBinary;

    /** @var bool */
    protected $_hasNoAutorotate;

    /** @var bool */
    protected $_hasLibFdkAac;

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
     * Check whether ffmpeg has specified audio encoder.
     *
     * @param string $encoder
     *
     * @return bool
     */
    protected function _hasAudioEncoder(
        $encoder)
    {
        try {
            $this->run(sprintf(
                '-f lavfi -i anullsrc=channel_layout=stereo:sample_rate=44100 -c:a %s -t 1 -f null -',
                escapeshellarg($encoder)
            ));

            return true;
        } catch (\RuntimeException $e) {
            return false;
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

        $this->_hasLibFdkAac = $this->_hasAudioEncoder('libfdk_aac');
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

    /**
     * Check whether ffmpeg has libfdk_aac audio encoder.
     *
     * @return bool
     */
    public function hasLibFdkAac()
    {
        return $this->_hasLibFdkAac;
    }
}
