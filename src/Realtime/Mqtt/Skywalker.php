<?php

namespace InstagramAPI\Realtime\Mqtt;

class Skywalker extends Thrift
{
    const TYPE_DIRECT = 1;
    const TYPE_LIVE = 2;
    const TYPE_LIVEWITH = 3;

    const FIELD_TYPE = 1;
    const FIELD_PAYLOAD = 2;

    /** @var int */
    protected $_type;
    /** @var string */
    protected $_payload;

    /**
     * {@inheritdoc}
     */
    protected function _handleField(
        $field,
        $value)
    {
        switch ($field) {
            case self::FIELD_TYPE:
                $this->_type = (int) $value;
                break;
            case self::FIELD_PAYLOAD:
                $this->_payload = (string) $value;
                break;
        }
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->_payload;
    }
}
