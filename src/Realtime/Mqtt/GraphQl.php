<?php

namespace InstagramAPI\Realtime\Mqtt;

class GraphQl extends Thrift
{
    const TOPIC_DIRECT = 'direct';

    const FIELD_TOPIC = 1;
    const FIELD_PAYLOAD = 2;

    /** @var string */
    protected $_topic;
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
            case self::FIELD_TOPIC:
                $this->_topic = (string) $value;
                break;
            case self::FIELD_PAYLOAD:
                $this->_payload = (string) $value;
                break;
        }
    }

    /**
     * @return string
     */
    public function getTopic()
    {
        return $this->_topic;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->_payload;
    }
}
