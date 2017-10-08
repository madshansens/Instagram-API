<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * @method mixed getButtons()
 * @method Model\Challenge getChallenge()
 * @method mixed getCheckpointUrl()
 * @method mixed getErrorTitle()
 * @method mixed getErrorType()
 * @method mixed getFullName()
 * @method mixed getHasAnonymousProfilePicture()
 * @method mixed getHelpUrl()
 * @method mixed getInvalidCredentials()
 * @method mixed getIsPrivate()
 * @method mixed getLock()
 * @method Model\User getLoggedInUser()
 * @method string getMessage()
 * @method Model\PhoneVerificationSettings getPhoneVerificationSettings()
 * @method string getPk()
 * @method string getProfilePicId()
 * @method mixed getProfilePicUrl()
 * @method string getStatus()
 * @method Model\TwoFactorInfo getTwoFactorInfo()
 * @method mixed getTwoFactorRequired()
 * @method mixed getUsername()
 * @method Model\_Message[] get_Messages()
 * @method bool isButtons()
 * @method bool isChallenge()
 * @method bool isCheckpointUrl()
 * @method bool isErrorTitle()
 * @method bool isErrorType()
 * @method bool isFullName()
 * @method bool isHasAnonymousProfilePicture()
 * @method bool isHelpUrl()
 * @method bool isInvalidCredentials()
 * @method bool isIsPrivate()
 * @method bool isLock()
 * @method bool isLoggedInUser()
 * @method bool isMessage()
 * @method bool isPhoneVerificationSettings()
 * @method bool isPk()
 * @method bool isProfilePicId()
 * @method bool isProfilePicUrl()
 * @method bool isStatus()
 * @method bool isTwoFactorInfo()
 * @method bool isTwoFactorRequired()
 * @method bool isUsername()
 * @method bool is_Messages()
 * @method $this setButtons(mixed $value)
 * @method $this setChallenge(Model\Challenge $value)
 * @method $this setCheckpointUrl(mixed $value)
 * @method $this setErrorTitle(mixed $value)
 * @method $this setErrorType(mixed $value)
 * @method $this setFullName(mixed $value)
 * @method $this setHasAnonymousProfilePicture(mixed $value)
 * @method $this setHelpUrl(mixed $value)
 * @method $this setInvalidCredentials(mixed $value)
 * @method $this setIsPrivate(mixed $value)
 * @method $this setLock(mixed $value)
 * @method $this setLoggedInUser(Model\User $value)
 * @method $this setMessage(mixed $value)
 * @method $this setPhoneVerificationSettings(Model\PhoneVerificationSettings $value)
 * @method $this setPk(string $value)
 * @method $this setProfilePicId(string $value)
 * @method $this setProfilePicUrl(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setTwoFactorInfo(Model\TwoFactorInfo $value)
 * @method $this setTwoFactorRequired(mixed $value)
 * @method $this setUsername(mixed $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetButtons()
 * @method $this unsetChallenge()
 * @method $this unsetCheckpointUrl()
 * @method $this unsetErrorTitle()
 * @method $this unsetErrorType()
 * @method $this unsetFullName()
 * @method $this unsetHasAnonymousProfilePicture()
 * @method $this unsetHelpUrl()
 * @method $this unsetInvalidCredentials()
 * @method $this unsetIsPrivate()
 * @method $this unsetLock()
 * @method $this unsetLoggedInUser()
 * @method $this unsetMessage()
 * @method $this unsetPhoneVerificationSettings()
 * @method $this unsetPk()
 * @method $this unsetProfilePicId()
 * @method $this unsetProfilePicUrl()
 * @method $this unsetStatus()
 * @method $this unsetTwoFactorInfo()
 * @method $this unsetTwoFactorRequired()
 * @method $this unsetUsername()
 * @method $this unset_Messages()
 */
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
