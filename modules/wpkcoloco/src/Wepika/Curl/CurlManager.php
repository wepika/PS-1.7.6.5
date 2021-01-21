<?php

namespace WpkColoco\Wepika\Curl;

use InvalidArgumentException;
use WpkColoco\Wepika\Curl\Exception\CurlErrorException;

/**
 * Class CurlManager
 *
 * @package WpkColoco\Wepika\Curl
 */
class CurlManager
{
    /**
     * @var \WpkColoco\Wepika\Curl\CurlOptionList
     */
    private $curlOptionList;

    /**
     * @param \WpkColoco\Wepika\Curl\CurlOptionList $curlOptionList
     */
    public function setCurlOptionList($curlOptionList)
    {
        if (!($curlOptionList instanceof CurlOptionList)) {
            throw new InvalidArgumentException("The type of curlOptionList parameter must be CurlOptionList");
        }

        $this->curlOptionList = $curlOptionList;
    }

    /**
     * @return \WpkColoco\Wepika\Curl\CurlOptionList
     */
    private function getCurlOptionList()
    {
        return $this->curlOptionList;
    }

    /**
     * @return bool|string
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     */
    public function makeCall()
    {
        $ch = curl_init();

        foreach ($this->getCurlOptionList()->getOptionList() as $curlOption) {
            /* @var \WpkColoco\Wepika\Curl\CurlOption $curlOption */
            curl_setopt($ch, $curlOption->getCurlOptCode(), $curlOption->getValue());
        }

        $error_msg = "";
        if (($server_output = curl_exec($ch)) === false) {
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
        }

        curl_close($ch);

        if (!empty($error_msg)) {
            throw new CurlErrorException($error_msg);
        }

        return $server_output;
    }
}
