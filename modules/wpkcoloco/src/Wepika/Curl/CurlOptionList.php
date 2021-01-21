<?php

namespace WpkColoco\Wepika\Curl;

use InvalidArgumentException;

/**
 * Class CurlOptionList
 *
 * @package WpkColoco\Wepika\Curl
 */
class CurlOptionList
{
    /**
     * @var \WpkColoco\Wepika\Curl\CurlOption[]
     */
    private $optionList;

    /**
     * CurlOptionList constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return \WpkColoco\Wepika\Curl\CurlOption[]
     */
    public function getOptionList()
    {
        return $this->optionList;
    }

    /**
     * @param \WpkColoco\Wepika\Curl\CurlOption $curlOption
     */
    public function addOption($curlOption)
    {
        if (!($curlOption instanceof CurlOption)) {
            throw new InvalidArgumentException("Type of curlOption parameter must be CurlOption");
        }

        $this->optionList[] = $curlOption;
    }
}