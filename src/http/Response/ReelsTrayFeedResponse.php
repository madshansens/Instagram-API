<?php

namespace InstagramAPI;

class ReelsTrayFeedResponse extends Response
{
    protected $trays;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $trays = [];
            if ((isset($response['tray'])) && (!empty($response['tray']))) {
                foreach ($response['tray'] as $tray) {
                    $items = [];
                    if ((isset($tray['items'])) && (!empty($tray['items']))) {
                        foreach ($tray['items'] as $item) {
                            $items[] = new Item($item);
                        }
                    }

                    $trays[] = new Tray($items, $tray['user'], $tray['can_reply'], $tray['expiring_at']);
                }
            }
            $this->trays = $trays;
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getTrays()
    {
        return $this->trays;
    }
}
