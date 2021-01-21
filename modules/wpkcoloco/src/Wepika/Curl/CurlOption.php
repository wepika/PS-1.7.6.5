<?php

namespace WpkColoco\Wepika\Curl;

use InvalidArgumentException;

/**
 * Class CurlOption
 *
 * @package WpkColoco\Wepika\Curl
 */
class CurlOption
{
    /**
     * @var int
     */
    private $curlOptCode;
    /**
     * @var mixed
     */
    private $value;

    /**
     * CurlOption constructor.
     *
     * @param int $curlOptCode
     * @param mixed $value
     */
    public function __construct($curlOptCode, $value)
    {
        if (!is_int($curlOptCode)) {
            throw new InvalidArgumentException("The type of curlOptCode parameter must be integer");
        }

        $this->curlOptCode = $curlOptCode;
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getCurlOptCode()
    {
        return $this->curlOptCode;
    }

    /**
     * @param int $curlOptCode
     */
    public function setCurlOptCode($curlOptCode)
    {
        $this->curlOptCode = $curlOptCode;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}