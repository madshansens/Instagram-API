<?php

namespace InstagramAPI;

/**
 * Class for converting media IDs to/from Instagram's shortcode system.
 *
 * The shortcode is the https://instagram.com/p/SHORTCODE/ part of the URL.
 * There are many reasons why you would want to be able to convert back and
 * forth between shortcodes and internal ID numbers. This library helps you!
 *
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class InstagramID
{
    /**
     * Base64 URL Safe Character Map.
     *
     * This is the Base64 "URL Safe" alphabet, which is what Instagram uses.
     *
     * @var string
     *
     * @see https://tools.ietf.org/html/rfc4648
     */
    const BASE64URL_CHARMAP = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';

    /**
     * Converts an Instagram ID to their shortcode system.
     *
     * @param string|int $id The ID to convert. Must be provided as a string if
     *                       it's larger than the size of an integer, which MOST
     *                       Instagram IDs are!
     *
     * @throws \InvalidArgumentException If bad parameters are provided.
     *
     * @return string The shortcode.
     */
    public static function toCode(
        $id)
    {
        if (!ctype_digit($id) && (!is_int($id) || $id < 0)) {
            throw new \InvalidArgumentException('Input must be a positive integer.');
        }

        $id = gmp_init($id, 10);
        $encoded = '';
        do {
            $encoded = self::BASE64URL_CHARMAP[(int) ($id & 63)].$encoded;
        } while (($id >>= 6) > 0);

        return $encoded;
    }

    /**
     * Converts an Instagram shortcode to a numeric ID.
     *
     * @param string $code The shortcode.
     *
     * @throws \InvalidArgumentException If bad parameters are provided.
     *
     * @return string The numeric ID.
     */
    public static function fromCode(
        $code)
    {
        if (!is_string($code) || strlen($code) !== strspn($code, self::BASE64URL_CHARMAP)) {
            throw new \InvalidArgumentException('Input must be a valid Instagram shortcode.');
        }

        $id = gmp_init(0, 10);
        for ($i = 0, $len = strlen($code); $i < $len; ++$i) {
            $id = $id << 6 | strpos(self::BASE64URL_CHARMAP, $code[$i]);
        }

        return gmp_strval($id);
    }
}
