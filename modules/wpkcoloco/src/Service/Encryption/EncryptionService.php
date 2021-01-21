<?php

namespace WpkColoco\Service;

class EncryptionService
{
    /**
     * @var string
     */
    private static $secret_key = 'QQ5jdMSXF8SaK3uwEZxJH7uXw7L3EgXK82kLNJNMZS8GWb7RrG6Gu8MHvaT4vUWg';

    /**
     * @param string $value
     * @return bool|string
     */
    public function encrypt($value)
    {
        if (!$value) {
            return false;
        }

        $crypttext = openssl_encrypt($value, 'bf-ecb', self::$secret_key, true);

        return $this->safe_b64encode($crypttext);
    }

    /**
     * @param string $string
     * @return string
     */
    private function safe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);

        return trim($data);
    }

    /**
     * @param string $value
     * @return bool|string
     */
    public function decrypt($value)
    {
        if (!$value) {
            return false;
        }

        $crypttext = $this->safe_b64decode($value);
        $decrypttext = openssl_decrypt($crypttext, 'bf-ecb', self::$secret_key, true);

        return trim($decrypttext);
    }

    /**
     * @param string $string
     * @return false|string
     */
    private function safe_b64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;

        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        return base64_decode($data);
    }
}