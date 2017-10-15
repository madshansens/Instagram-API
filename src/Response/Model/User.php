<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * User.
 *
 * @method mixed getAddressStreet()
 * @method mixed getAggregatePromoteEngagement()
 * @method mixed getAllowContactsSync()
 * @method mixed getAllowedCommenterType()
 * @method mixed getAutoExpandChaining()
 * @method mixed getBiography()
 * @method mixed getBirthday()
 * @method mixed getBlockAt()
 * @method mixed getBusinessContactMethod()
 * @method mixed getByline()
 * @method mixed getCanBoostPost()
 * @method mixed getCanConvertToBusiness()
 * @method mixed getCanCreateSponsorTags()
 * @method mixed getCanSeeOrganicInsights()
 * @method mixed getCategory()
 * @method string getCityId()
 * @method mixed getCityName()
 * @method mixed getCoeffWeight()
 * @method mixed getContactPhoneNumber()
 * @method mixed getConvertFromPages()
 * @method mixed getCountryCode()
 * @method mixed getDirectMessaging()
 * @method mixed getEmail()
 * @method mixed getExternalLynxUrl()
 * @method mixed getExternalUrl()
 * @method string getFbPageCallToActionId()
 * @method mixed getFbuid()
 * @method mixed getFollowerCount()
 * @method mixed getFollowingCount()
 * @method FriendshipStatus getFriendshipStatus()
 * @method mixed getFullName()
 * @method mixed getGender()
 * @method mixed getGeoMediaCount()
 * @method mixed getHasAnonymousProfilePicture()
 * @method mixed getHasBiographyTranslation()
 * @method mixed getHasChaining()
 * @method mixed getHasUnseenBestiesMedia()
 * @method ImageCandidate getHdProfilePicUrlInfo()
 * @method ImageCandidate[] getHdProfilePicVersions()
 * @method string getId()
 * @method mixed getIncludeDirectBlacklistStatus()
 * @method mixed getIsActive()
 * @method mixed getIsBusiness()
 * @method mixed getIsCallToActionEnabled()
 * @method mixed getIsFavorite()
 * @method mixed getIsNeedy()
 * @method mixed getIsPrivate()
 * @method mixed getIsProfileActionNeeded()
 * @method mixed getIsUnpublished()
 * @method mixed getIsVerified()
 * @method string getLatestReelMedia()
 * @method float getLatitude()
 * @method float getLongitude()
 * @method mixed getMediaCount()
 * @method mixed getMutualFollowersCount()
 * @method mixed getNationalNumber()
 * @method mixed getNeedsEmailConfirm()
 * @method string getPageId()
 * @method mixed getPageName()
 * @method mixed getPhoneNumber()
 * @method string getPk()
 * @method mixed getProfileContext()
 * @method Link[] getProfileContextLinksWithUserIds()
 * @method string[] getProfileContextMutualFollowIds()
 * @method string getProfilePicId()
 * @method mixed getProfilePicUrl()
 * @method mixed getPublicEmail()
 * @method mixed getPublicPhoneCountryCode()
 * @method mixed getPublicPhoneNumber()
 * @method mixed getSearchSocialContext()
 * @method mixed getShowBusinessConversionIcon()
 * @method mixed getShowConversionEditEntry()
 * @method mixed getShowFeedBizConversionIcon()
 * @method mixed getShowInsightsTerms()
 * @method mixed getSocialContext()
 * @method mixed getUnseenCount()
 * @method string getUserId()
 * @method mixed getUsername()
 * @method mixed getUsertagReviewEnabled()
 * @method mixed getUsertagsCount()
 * @method mixed getZip()
 * @method bool isAddressStreet()
 * @method bool isAggregatePromoteEngagement()
 * @method bool isAllowContactsSync()
 * @method bool isAllowedCommenterType()
 * @method bool isAutoExpandChaining()
 * @method bool isBiography()
 * @method bool isBirthday()
 * @method bool isBlockAt()
 * @method bool isBusinessContactMethod()
 * @method bool isByline()
 * @method bool isCanBoostPost()
 * @method bool isCanConvertToBusiness()
 * @method bool isCanCreateSponsorTags()
 * @method bool isCanSeeOrganicInsights()
 * @method bool isCategory()
 * @method bool isCityId()
 * @method bool isCityName()
 * @method bool isCoeffWeight()
 * @method bool isContactPhoneNumber()
 * @method bool isConvertFromPages()
 * @method bool isCountryCode()
 * @method bool isDirectMessaging()
 * @method bool isEmail()
 * @method bool isExternalLynxUrl()
 * @method bool isExternalUrl()
 * @method bool isFbPageCallToActionId()
 * @method bool isFbuid()
 * @method bool isFollowerCount()
 * @method bool isFollowingCount()
 * @method bool isFriendshipStatus()
 * @method bool isFullName()
 * @method bool isGender()
 * @method bool isGeoMediaCount()
 * @method bool isHasAnonymousProfilePicture()
 * @method bool isHasBiographyTranslation()
 * @method bool isHasChaining()
 * @method bool isHasUnseenBestiesMedia()
 * @method bool isHdProfilePicUrlInfo()
 * @method bool isHdProfilePicVersions()
 * @method bool isId()
 * @method bool isIncludeDirectBlacklistStatus()
 * @method bool isIsActive()
 * @method bool isIsBusiness()
 * @method bool isIsCallToActionEnabled()
 * @method bool isIsFavorite()
 * @method bool isIsNeedy()
 * @method bool isIsPrivate()
 * @method bool isIsProfileActionNeeded()
 * @method bool isIsUnpublished()
 * @method bool isIsVerified()
 * @method bool isLatestReelMedia()
 * @method bool isLatitude()
 * @method bool isLongitude()
 * @method bool isMediaCount()
 * @method bool isMutualFollowersCount()
 * @method bool isNationalNumber()
 * @method bool isNeedsEmailConfirm()
 * @method bool isPageId()
 * @method bool isPageName()
 * @method bool isPhoneNumber()
 * @method bool isPk()
 * @method bool isProfileContext()
 * @method bool isProfileContextLinksWithUserIds()
 * @method bool isProfileContextMutualFollowIds()
 * @method bool isProfilePicId()
 * @method bool isProfilePicUrl()
 * @method bool isPublicEmail()
 * @method bool isPublicPhoneCountryCode()
 * @method bool isPublicPhoneNumber()
 * @method bool isSearchSocialContext()
 * @method bool isShowBusinessConversionIcon()
 * @method bool isShowConversionEditEntry()
 * @method bool isShowFeedBizConversionIcon()
 * @method bool isShowInsightsTerms()
 * @method bool isSocialContext()
 * @method bool isUnseenCount()
 * @method bool isUserId()
 * @method bool isUsername()
 * @method bool isUsertagReviewEnabled()
 * @method bool isUsertagsCount()
 * @method bool isZip()
 * @method $this setAddressStreet(mixed $value)
 * @method $this setAggregatePromoteEngagement(mixed $value)
 * @method $this setAllowContactsSync(mixed $value)
 * @method $this setAllowedCommenterType(mixed $value)
 * @method $this setAutoExpandChaining(mixed $value)
 * @method $this setBiography(mixed $value)
 * @method $this setBirthday(mixed $value)
 * @method $this setBlockAt(mixed $value)
 * @method $this setBusinessContactMethod(mixed $value)
 * @method $this setByline(mixed $value)
 * @method $this setCanBoostPost(mixed $value)
 * @method $this setCanConvertToBusiness(mixed $value)
 * @method $this setCanCreateSponsorTags(mixed $value)
 * @method $this setCanSeeOrganicInsights(mixed $value)
 * @method $this setCategory(mixed $value)
 * @method $this setCityId(string $value)
 * @method $this setCityName(mixed $value)
 * @method $this setCoeffWeight(mixed $value)
 * @method $this setContactPhoneNumber(mixed $value)
 * @method $this setConvertFromPages(mixed $value)
 * @method $this setCountryCode(mixed $value)
 * @method $this setDirectMessaging(mixed $value)
 * @method $this setEmail(mixed $value)
 * @method $this setExternalLynxUrl(mixed $value)
 * @method $this setExternalUrl(mixed $value)
 * @method $this setFbPageCallToActionId(string $value)
 * @method $this setFbuid(mixed $value)
 * @method $this setFollowerCount(mixed $value)
 * @method $this setFollowingCount(mixed $value)
 * @method $this setFriendshipStatus(FriendshipStatus $value)
 * @method $this setFullName(mixed $value)
 * @method $this setGender(mixed $value)
 * @method $this setGeoMediaCount(mixed $value)
 * @method $this setHasAnonymousProfilePicture(mixed $value)
 * @method $this setHasBiographyTranslation(mixed $value)
 * @method $this setHasChaining(mixed $value)
 * @method $this setHasUnseenBestiesMedia(mixed $value)
 * @method $this setHdProfilePicUrlInfo(ImageCandidate $value)
 * @method $this setHdProfilePicVersions(ImageCandidate[] $value)
 * @method $this setId(string $value)
 * @method $this setIncludeDirectBlacklistStatus(mixed $value)
 * @method $this setIsActive(mixed $value)
 * @method $this setIsBusiness(mixed $value)
 * @method $this setIsCallToActionEnabled(mixed $value)
 * @method $this setIsFavorite(mixed $value)
 * @method $this setIsNeedy(mixed $value)
 * @method $this setIsPrivate(mixed $value)
 * @method $this setIsProfileActionNeeded(mixed $value)
 * @method $this setIsUnpublished(mixed $value)
 * @method $this setIsVerified(mixed $value)
 * @method $this setLatestReelMedia(string $value)
 * @method $this setLatitude(float $value)
 * @method $this setLongitude(float $value)
 * @method $this setMediaCount(mixed $value)
 * @method $this setMutualFollowersCount(mixed $value)
 * @method $this setNationalNumber(mixed $value)
 * @method $this setNeedsEmailConfirm(mixed $value)
 * @method $this setPageId(string $value)
 * @method $this setPageName(mixed $value)
 * @method $this setPhoneNumber(mixed $value)
 * @method $this setPk(string $value)
 * @method $this setProfileContext(mixed $value)
 * @method $this setProfileContextLinksWithUserIds(Link[] $value)
 * @method $this setProfileContextMutualFollowIds(string[] $value)
 * @method $this setProfilePicId(string $value)
 * @method $this setProfilePicUrl(mixed $value)
 * @method $this setPublicEmail(mixed $value)
 * @method $this setPublicPhoneCountryCode(mixed $value)
 * @method $this setPublicPhoneNumber(mixed $value)
 * @method $this setSearchSocialContext(mixed $value)
 * @method $this setShowBusinessConversionIcon(mixed $value)
 * @method $this setShowConversionEditEntry(mixed $value)
 * @method $this setShowFeedBizConversionIcon(mixed $value)
 * @method $this setShowInsightsTerms(mixed $value)
 * @method $this setSocialContext(mixed $value)
 * @method $this setUnseenCount(mixed $value)
 * @method $this setUserId(string $value)
 * @method $this setUsername(mixed $value)
 * @method $this setUsertagReviewEnabled(mixed $value)
 * @method $this setUsertagsCount(mixed $value)
 * @method $this setZip(mixed $value)
 * @method $this unsetAddressStreet()
 * @method $this unsetAggregatePromoteEngagement()
 * @method $this unsetAllowContactsSync()
 * @method $this unsetAllowedCommenterType()
 * @method $this unsetAutoExpandChaining()
 * @method $this unsetBiography()
 * @method $this unsetBirthday()
 * @method $this unsetBlockAt()
 * @method $this unsetBusinessContactMethod()
 * @method $this unsetByline()
 * @method $this unsetCanBoostPost()
 * @method $this unsetCanConvertToBusiness()
 * @method $this unsetCanCreateSponsorTags()
 * @method $this unsetCanSeeOrganicInsights()
 * @method $this unsetCategory()
 * @method $this unsetCityId()
 * @method $this unsetCityName()
 * @method $this unsetCoeffWeight()
 * @method $this unsetContactPhoneNumber()
 * @method $this unsetConvertFromPages()
 * @method $this unsetCountryCode()
 * @method $this unsetDirectMessaging()
 * @method $this unsetEmail()
 * @method $this unsetExternalLynxUrl()
 * @method $this unsetExternalUrl()
 * @method $this unsetFbPageCallToActionId()
 * @method $this unsetFbuid()
 * @method $this unsetFollowerCount()
 * @method $this unsetFollowingCount()
 * @method $this unsetFriendshipStatus()
 * @method $this unsetFullName()
 * @method $this unsetGender()
 * @method $this unsetGeoMediaCount()
 * @method $this unsetHasAnonymousProfilePicture()
 * @method $this unsetHasBiographyTranslation()
 * @method $this unsetHasChaining()
 * @method $this unsetHasUnseenBestiesMedia()
 * @method $this unsetHdProfilePicUrlInfo()
 * @method $this unsetHdProfilePicVersions()
 * @method $this unsetId()
 * @method $this unsetIncludeDirectBlacklistStatus()
 * @method $this unsetIsActive()
 * @method $this unsetIsBusiness()
 * @method $this unsetIsCallToActionEnabled()
 * @method $this unsetIsFavorite()
 * @method $this unsetIsNeedy()
 * @method $this unsetIsPrivate()
 * @method $this unsetIsProfileActionNeeded()
 * @method $this unsetIsUnpublished()
 * @method $this unsetIsVerified()
 * @method $this unsetLatestReelMedia()
 * @method $this unsetLatitude()
 * @method $this unsetLongitude()
 * @method $this unsetMediaCount()
 * @method $this unsetMutualFollowersCount()
 * @method $this unsetNationalNumber()
 * @method $this unsetNeedsEmailConfirm()
 * @method $this unsetPageId()
 * @method $this unsetPageName()
 * @method $this unsetPhoneNumber()
 * @method $this unsetPk()
 * @method $this unsetProfileContext()
 * @method $this unsetProfileContextLinksWithUserIds()
 * @method $this unsetProfileContextMutualFollowIds()
 * @method $this unsetProfilePicId()
 * @method $this unsetProfilePicUrl()
 * @method $this unsetPublicEmail()
 * @method $this unsetPublicPhoneCountryCode()
 * @method $this unsetPublicPhoneNumber()
 * @method $this unsetSearchSocialContext()
 * @method $this unsetShowBusinessConversionIcon()
 * @method $this unsetShowConversionEditEntry()
 * @method $this unsetShowFeedBizConversionIcon()
 * @method $this unsetShowInsightsTerms()
 * @method $this unsetSocialContext()
 * @method $this unsetUnseenCount()
 * @method $this unsetUserId()
 * @method $this unsetUsername()
 * @method $this unsetUsertagReviewEnabled()
 * @method $this unsetUsertagsCount()
 * @method $this unsetZip()
 */
class User extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'username'                            => '',
        'has_anonymous_profile_picture'       => '',
        'is_favorite'                         => '',
        'profile_pic_url'                     => '',
        'full_name'                           => '',
        'user_id'                             => 'string',
        'pk'                                  => 'string',
        'id'                                  => 'string',
        'is_verified'                         => '',
        'is_private'                          => '',
        'coeff_weight'                        => '',
        'friendship_status'                   => 'FriendshipStatus',
        'hd_profile_pic_versions'             => 'ImageCandidate[]',
        'byline'                              => '',
        'search_social_context'               => '',
        'unseen_count'                        => '',
        'mutual_followers_count'              => '',
        'follower_count'                      => '',
        'social_context'                      => '',
        'media_count'                         => '',
        'following_count'                     => '',
        'is_business'                         => '',
        'usertags_count'                      => '',
        'profile_context'                     => '',
        'biography'                           => '',
        'geo_media_count'                     => '',
        'is_unpublished'                      => '',
        'allow_contacts_sync'                 => '',
        'show_feed_biz_conversion_icon'       => '',
        'profile_pic_id'                      => 'string',
        'auto_expand_chaining'                => '',
        'can_boost_post'                      => '',
        'is_profile_action_needed'            => '',
        'has_chaining'                        => '',
        'include_direct_blacklist_status'     => '',
        'can_see_organic_insights'            => '',
        'can_convert_to_business'             => '',
        'convert_from_pages'                  => '',
        'show_business_conversion_icon'       => '',
        'show_conversion_edit_entry'          => '',
        'show_insights_terms'                 => '',
        'can_create_sponsor_tags'             => '',
        'hd_profile_pic_url_info'             => 'ImageCandidate',
        'usertag_review_enabled'              => '',
        'profile_context_mutual_follow_ids'   => 'string[]',
        'profile_context_links_with_user_ids' => 'Link[]',
        'has_biography_translation'           => '',
        'business_contact_method'             => '',
        'category'                            => '',
        'direct_messaging'                    => '',
        'page_name'                           => '',
        'fb_page_call_to_action_id'           => 'string',
        'is_call_to_action_enabled'           => '',
        'public_phone_country_code'           => '',
        'public_phone_number'                 => '',
        'contact_phone_number'                => '',
        'latitude'                            => 'float',
        'longitude'                           => 'float',
        'address_street'                      => '',
        'zip'                                 => '',
        'city_id'                             => 'string',
        'city_name'                           => '',
        'public_email'                        => '',
        'is_needy'                            => '',
        'external_url'                        => '',
        'external_lynx_url'                   => '',
        'email'                               => '',
        'country_code'                        => '',
        'birthday'                            => '',
        'national_number'                     => '',
        'gender'                              => '',
        'phone_number'                        => '',
        'needs_email_confirm'                 => '',
        'is_active'                           => '',
        'block_at'                            => '',
        'aggregate_promote_engagement'        => '',
        'fbuid'                               => '',
        'page_id'                             => 'string',
        /*
         * Unix "taken_at" timestamp of the newest item in their story reel.
         */
        'latest_reel_media'                   => 'string',
        'has_unseen_besties_media'            => '',
        'allowed_commenter_type'              => '',
    ];
}
