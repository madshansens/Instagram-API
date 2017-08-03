<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getAllowResolutionChange()
 * @method mixed getAvcRtmpPayload()
 * @method string getBroadcastId()
 * @method mixed getBroadcasterUpdateFrequency()
 * @method mixed getConnectWith1rtt()
 * @method mixed getDisableSpeedTest()
 * @method mixed getHeartbeatInterval()
 * @method mixed getMaxTimeInSeconds()
 * @method mixed getSpeedTestMinimumBandwidthThreshold()
 * @method mixed getSpeedTestRetryMaxCount()
 * @method mixed getSpeedTestRetryTimeDelay()
 * @method mixed getSpeedTestUiTimeout()
 * @method mixed getStreamAudioBitRate()
 * @method mixed getStreamAudioChannels()
 * @method mixed getStreamAudioSampleRate()
 * @method mixed getStreamNetworkConnectionRetryCount()
 * @method mixed getStreamNetworkConnectionRetryDelayInSeconds()
 * @method mixed getStreamNetworkSpeedTestPayloadChunkSizeInBytes()
 * @method mixed getStreamNetworkSpeedTestPayloadSizeInBytes()
 * @method mixed getStreamNetworkSpeedTestPayloadTimeoutInSeconds()
 * @method mixed getStreamVideoAdaptiveBitrateConfig()
 * @method mixed getStreamVideoAllowBFrames()
 * @method mixed getStreamVideoBitRate()
 * @method mixed getStreamVideoFps()
 * @method mixed getStreamVideoWidth()
 * @method mixed getUploadUrl()
 * @method bool isAllowResolutionChange()
 * @method bool isAvcRtmpPayload()
 * @method bool isBroadcastId()
 * @method bool isBroadcasterUpdateFrequency()
 * @method bool isConnectWith1rtt()
 * @method bool isDisableSpeedTest()
 * @method bool isHeartbeatInterval()
 * @method bool isMaxTimeInSeconds()
 * @method bool isSpeedTestMinimumBandwidthThreshold()
 * @method bool isSpeedTestRetryMaxCount()
 * @method bool isSpeedTestRetryTimeDelay()
 * @method bool isSpeedTestUiTimeout()
 * @method bool isStreamAudioBitRate()
 * @method bool isStreamAudioChannels()
 * @method bool isStreamAudioSampleRate()
 * @method bool isStreamNetworkConnectionRetryCount()
 * @method bool isStreamNetworkConnectionRetryDelayInSeconds()
 * @method bool isStreamNetworkSpeedTestPayloadChunkSizeInBytes()
 * @method bool isStreamNetworkSpeedTestPayloadSizeInBytes()
 * @method bool isStreamNetworkSpeedTestPayloadTimeoutInSeconds()
 * @method bool isStreamVideoAdaptiveBitrateConfig()
 * @method bool isStreamVideoAllowBFrames()
 * @method bool isStreamVideoBitRate()
 * @method bool isStreamVideoFps()
 * @method bool isStreamVideoWidth()
 * @method bool isUploadUrl()
 * @method setAllowResolutionChange(mixed $value)
 * @method setAvcRtmpPayload(mixed $value)
 * @method setBroadcastId(string $value)
 * @method setBroadcasterUpdateFrequency(mixed $value)
 * @method setConnectWith1rtt(mixed $value)
 * @method setDisableSpeedTest(mixed $value)
 * @method setHeartbeatInterval(mixed $value)
 * @method setMaxTimeInSeconds(mixed $value)
 * @method setSpeedTestMinimumBandwidthThreshold(mixed $value)
 * @method setSpeedTestRetryMaxCount(mixed $value)
 * @method setSpeedTestRetryTimeDelay(mixed $value)
 * @method setSpeedTestUiTimeout(mixed $value)
 * @method setStreamAudioBitRate(mixed $value)
 * @method setStreamAudioChannels(mixed $value)
 * @method setStreamAudioSampleRate(mixed $value)
 * @method setStreamNetworkConnectionRetryCount(mixed $value)
 * @method setStreamNetworkConnectionRetryDelayInSeconds(mixed $value)
 * @method setStreamNetworkSpeedTestPayloadChunkSizeInBytes(mixed $value)
 * @method setStreamNetworkSpeedTestPayloadSizeInBytes(mixed $value)
 * @method setStreamNetworkSpeedTestPayloadTimeoutInSeconds(mixed $value)
 * @method setStreamVideoAdaptiveBitrateConfig(mixed $value)
 * @method setStreamVideoAllowBFrames(mixed $value)
 * @method setStreamVideoBitRate(mixed $value)
 * @method setStreamVideoFps(mixed $value)
 * @method setStreamVideoWidth(mixed $value)
 * @method setUploadUrl(mixed $value)
 */
class CreateLiveResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var string
     */
    public $broadcast_id;
    public $upload_url;
    public $max_time_in_seconds;
    public $speed_test_ui_timeout;
    public $stream_network_speed_test_payload_chunk_size_in_bytes;
    public $stream_network_speed_test_payload_size_in_bytes;
    public $stream_network_speed_test_payload_timeout_in_seconds;
    public $speed_test_minimum_bandwidth_threshold;
    public $speed_test_retry_max_count;
    public $speed_test_retry_time_delay;
    public $disable_speed_test;
    public $stream_video_allow_b_frames;
    public $stream_video_width;
    public $stream_video_bit_rate;
    public $stream_video_fps;
    public $stream_audio_bit_rate;
    public $stream_audio_sample_rate;
    public $stream_audio_channels;
    public $heartbeat_interval;
    public $broadcaster_update_frequency;
    public $stream_video_adaptive_bitrate_config;
    public $stream_network_connection_retry_count;
    public $stream_network_connection_retry_delay_in_seconds;
    public $connect_with_1rtt;
    public $avc_rtmp_payload;
    public $allow_resolution_change;
}
