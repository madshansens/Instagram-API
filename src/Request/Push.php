<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

/**
 * Functions for managing your push notifications.
 */
class Push extends RequestCollection
{
    /**
     * Register to the MQTT or GCM push server.
     *
     * @param string $pushChannel The channel you want to register, it can be mqtt or gcm.
     * @param string $token       The token used to register to the push channel.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PushRegisterResponse
     */
    public function register(
        $pushChannel,
        $token)
    {
        // Make sure we only allow these for push channels.
        if ($pushChannel != 'mqtt' && $pushChannel != 'gcm') {
            throw new \InvalidArgumentException(sprintf('Bad push channel "%s".', $pushChannel));
        }

        $request = $this->ig->request('push/register/')
            ->addPost('phone_id', $this->ig->settings->get('phone_id'))
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('guid', $this->ig->uuid)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('users', $this->ig->account_id);

        if ($pushChannel == 'gcm') {
            $request
                ->addPost('is_main_push_channel', false)
                ->addPost('device_type', 'android_gcm')
                ->addPost('device_token', $token);
        } elseif ($pushChannel == 'mqtt') {
            $deviceToken = json_encode([
                'k' => $token,
                'v' => 0,
                't' => 'fbns-b64',
            ]);

            $request
                ->addPost('is_main_push_channel', true)
                ->addPost('device_type', 'android_mqtt')
                ->addPost('device_token', $deviceToken);
        }

        return $request->getResponse(new Response\PushRegisterResponse());
    }

    /**
     * Get push preferences.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PushPreferencesResponse
     */
    public function getPreferences()
    {
        return $this->ig->request('push/all_preferences/')
            ->getResponse(new Response\PushPreferencesResponse());
    }

    /**
     * Set push preferences.
     *
     * @param array $preferences Described in "extradocs/Push_setPreferences.txt".
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PushPreferencesResponse
     */
    public function setPreferences(
        array $preferences)
    {
        $request = $this->ig->request('push/preferences/');
        foreach ($preferences as $key => $value) {
            $request->addPost($key, $value);
        }
        $request->getResponse(new Response\PushPreferencesResponse());
    }
}
