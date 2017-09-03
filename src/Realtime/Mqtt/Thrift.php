<?php

namespace InstagramAPI\Realtime\Mqtt;

/**
 * @see https://thrift.apache.org/
 */
abstract class Thrift
{
    const COMPACT_STOP = 0x00;
    const COMPACT_I32 = 0x05;
    const COMPACT_BINARY = 0x08;

    /** @var int */
    protected $_position;
    /** @var int */
    protected $_length;
    /** @var string */
    protected $_buffer;

    /**
     * Constructor.
     *
     * @param string $string
     */
    public function __construct(
        $string = '')
    {
        $this->_buffer = $string;
        $this->_position = 0;
        $this->_length = strlen($string);
        $this->_parse();
    }

    /**
     * Parse buffer.
     */
    protected function _parse()
    {
        $field = 0;
        while ($this->_position < $this->_length) {
            $typeAndDelta = ord($this->_buffer[$this->_position++]);
            $delta = $typeAndDelta >> 4;
            if ($delta == 0) {
                $field = $this->_fromZigZag($this->_readVarint());
            } else {
                $field += $delta;
            }
            $type = $typeAndDelta & 0x0f;
            switch ($type) {
                case self::COMPACT_I32:
                    $this->_handleField($field, $this->_fromZigZag($this->_readVarint()));
                    break;
                case self::COMPACT_BINARY:
                    $this->_handleField($field, $this->_readString($this->_readVarint()));
                    break;
                case self::COMPACT_STOP:
                    return;
            }
        }
    }

    /**
     * Handle field.
     *
     * @param int   $field
     * @param mixed $value
     */
    abstract protected function _handleField(
        $field,
        $value);

    /**
     * @return int
     */
    protected function _readVarint()
    {
        $shift = 0;
        $result = 0;
        while ($this->_position < $this->_length) {
            $byte = ord($this->_buffer[$this->_position++]);
            $result |= ($byte & 0x7f) << $shift;
            if (($byte >> 7) === 0) {
                break;
            }
            $shift += 7;
        }

        return $result;
    }

    /**
     * @param int $n
     *
     * @return int
     */
    protected function _fromZigZag(
        $n)
    {
        return ($n >> 1) ^ -($n & 1);
    }

    /**
     * @param int $length
     *
     * @return string
     */
    protected function _readString(
        $length)
    {
        $result = substr($this->_buffer, $this->_position, $length);
        $this->_position += $length;

        return $result;
    }
}
