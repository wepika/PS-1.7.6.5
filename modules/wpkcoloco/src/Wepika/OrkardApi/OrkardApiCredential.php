<?php

namespace WpkColoco\Wepika\OrkardApi;

/**
 * Class OrkardApiCredential
 *
 * @package WpkColoco\Wepika\OrkardApi
 */
class OrkardApiCredential
{
    /**
     * @var string
     */
    private $ip;
    /**
     * @var int
     */
    private $port;
    /**
     * @var string
     */
    private $user;
    /**
     * @var string
     */
    private $password;

    /**
     * OrkardApiCredential constructor.
     *
     * @param string $ip
     * @param int $port
     * @param string $user
     * @param string $password
     */
    public function __construct($ip = "", $port = "", $user = "", $password = "")
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
