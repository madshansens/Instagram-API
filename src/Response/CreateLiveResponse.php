<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class CreateLiveResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'broadcast_id'                                          => 'string',
        'upload_url'                                            => '',
        'max_time_in_seconds'                                   => '',
        'speed_test_ui_timeout'                                 => '',
        'stream_network_speed_test_payload_chunk_size_in_bytes' => '',
        'stream_network_speed_test_payload_size_in_bytes'       => '',
        'stream_network_speed_test_payload_timeout_in_seconds'  => '',
        'speed_test_minimum_bandwidth_threshold'                => '',
        'speed_test_retry_max_count'                            => '',
        'speed_test_retry_time_delay'                           => '',
        'disable_speed_test'                                    => '',
        'stream_video_allow_b_frames'                           => '',
        'stream_video_width'                                    => '',
        'stream_video_bit_rate'                                 => '',
        'stream_video_fps'                                      => '',
        'stream_audio_bit_rate'                                 => '',
        'stream_audio_sample_rate'                              => '',
        'stream_audio_channels'                                 => '',
        'heartbeat_interval'                                    => '',
        'broadcaster_update_frequency'                          => '',
        'stream_video_adaptive_bitrate_config'                  => '',
        'stream_network_connection_retry_count'                 => '',
        'stream_network_connection_retry_delay_in_seconds'      => '',
        'connect_with_1rtt'                                     => '',
        'avc_rtmp_payload'                                      => '',
        'allow_resolution_change'                               => '',
    ];
}
