<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class LoginResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'username'                      => '',
        'has_anonymous_profile_picture' => '',
        'profile_pic_url'               => '',
        'profile_pic_id'                => 'string',
        'full_name'                     => '',
        'pk'                            => 'string',
        'is_private'                    => '',
        'error_title'                   => '', // On wrong pass.
        'error_type'                    => '', // On wrong pass.
        'buttons'                       => '', // On wrong pass.
        'invalid_credentials'           => '', // On wrong pass.
        'logged_in_user'                => 'Model\User',
        'two_factor_required'           => '',
        'phone_verification_settings'   => 'Model\PhoneVerificationSettings',
        'two_factor_info'               => 'Model\TwoFactorInfo',
        'checkpoint_url'                => '',
        'lock'                          => '',
        'help_url'                      => '',
        'challenge'                     => 'Model\Challenge',
    ];
}
