<?php

namespace WpkColoco\Wepika\OrkardApi;

use WpkColoco\Wepika\Curl\CurlManager;
use WpkColoco\Wepika\Curl\CurlOption;
use WpkColoco\Wepika\Curl\CurlOptionList;
use WpkColoco\Wepika\OrkardApi\Entity\OrkardCustomer;
use WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException;

/**
 * Class OrkardApiHandler
 *
 * @package WpkColoco\Wepika\OrkardApi
 */
class OrkardApiHandler
{
    /**
     * @var \WpkColoco\Wepika\OrkardApi\OrkardApiCredential
     */
    private $orkardApiCredential;

    /**
     * OrkardApiHandler constructor.
     *
     * @param \WpkColoco\Wepika\OrkardApi\OrkardApiCredential $orkardApiCredential
     */
    public function __construct($orkardApiCredential)
    {
        $this->orkardApiCredential = $orkardApiCredential;
    }

    /**
     * @return bool|array
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     * @throws \WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException
     */
    public function getNewDigitalCard()
    {
        $payload = $this->getBasePayload();
        $payload['action'] = __FUNCTION__;

        return $this->makePostCall($payload);
    }

    /**
     * @return array
     */
    private function getBasePayload()
    {
        return array(
            "user" => $this->orkardApiCredential->getUser(),
            "pwd" => $this->orkardApiCredential->getPassword(),
        );
    }

    /**
     * @param array $payload
     * @return false|array
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     * @throws \WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException
     */
    private function makePostCall($payload)
    {
        $curlOptionList = new CurlOptionList();

        $curlOptionList->addOption(new CurlOption(CURLOPT_URL, $this->getApiUrl()));
        $curlOptionList->addOption(new CurlOption(CURLOPT_POST, 1));
        $curlOptionList->addOption(new CurlOption(CURLOPT_HTTPHEADER, $this->getHeaders()));
        $curlOptionList->addOption(new CurlOption(CURLOPT_RETURNTRANSFER, true));
        $curlOptionList->addOption(new CurlOption(CURLOPT_FAILONERROR, true));
        $curlOptionList->addOption(new CurlOption(CURLOPT_POSTFIELDS, json_encode($payload)));

        $curlManager = new CurlManager();
        $curlManager->setCurlOptionList($curlOptionList);

        $response = $curlManager->makeCall();

        if ($response) {
            $responseArray = $this->convertJsonToArray($response);

            if ($responseArray['status'] != 0) {
                if (isset($responseArray['errors']) && !empty($responseArray)) {
                    throw new OrkardApiErrorException(array_shift($responseArray['errors']));
                } else {
                    throw new OrkardApiErrorException("Unknown error");
                }
            }

            return $responseArray;
        }

        return false;
    }

    /**
     * @return string
     */
    private function getApiUrl()
    {
        return "http://" . $this->getHost();
    }

    /**
     * @return string
     */
    private function getHost()
    {
        return $this->orkardApiCredential->getIp() . ":" . $this->orkardApiCredential->getPort();
    }

    /**
     * @return string[]
     */
    private function getHeaders()
    {
        return array(
            "Content-Type: application/json",
            "Host: " . $this->getHost(),
            "cache-control: no-cache",
        );
    }

    /**
     * @param string $json
     * @return mixed
     */
    private function convertJsonToArray($json)
    {
        return json_decode($json, true);
    }

    /**
     * @param string $idCli
     * @return \WpkColoco\Wepika\OrkardApi\Entity\OrkardCustomer
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     * @throws \WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException
     */
    public function getClientWithAttributes($idCli)
    {
        $payload = $this->getBasePayload();
        $payload["action"] = __FUNCTION__;
        $payload["data"] = array(
            "idcli" => $idCli,
        );

        $response = $this->makePostCall($payload);

        if (!isset($response['data'])) {
            throw new OrkardApiErrorException("Response did not contain a data key");
        }

        $orkardCustomer = new OrkardCustomer();
        $orkardCustomer->hydrateFromArray($response['data']);

        return $orkardCustomer;
    }

    /**
     * @param string $email
     * @return \WpkColoco\Wepika\OrkardApi\Entity\OrkardCustomer
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     * @throws \WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException
     */
    public function getClientByEmailWithAttributes($email)
    {
        $payload = $this->getBasePayload();
        $payload['action'] = __FUNCTION__;
        $payload['data'] = array(
            'email' => $email,
        );

        $response = $this->makePostCall($payload);

        if (!isset($response['data'])) {
            throw new OrkardApiErrorException("Response did not contain a data key");
        }

        $orkardCustomer = new OrkardCustomer();
        $orkardCustomer->hydrateFromArray($response['data']);

        return $orkardCustomer;
    }

    /**
     * @param string $idcli
     * @return array|false
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     * @throws \WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException
     */
    public function getClientBalancePoint($idcli)
    {
        $payload = $this->getBasePayload();
        $payload['action'] = __FUNCTION__;
        $payload['data'] = array(
            'idcli' => $idcli
        );

        return $this->makePostCall($payload);
    }

    /**
     * @param int $idcli
     * @return array|false
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     * @throws \WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException
     */
    public function getClientDetailBalancePoint($idcli)
    {
        $payload = $this->getBasePayload();
        $payload['action'] = __FUNCTION__;
        $payload['data'] = array(
            'idcli' => $idcli
        );

        return $this->makePostCall($payload);
    }

    /**
     *
     */
    public function lostCard()
    {
        throw new \Symfony\Component\Intl\Exception\NotImplementedException("");
    }

    /**
     * @param OrkardCustomer $orkardCustomer
     * @return array|false
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     * @throws \WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException
     */
    public function updateClientWithAttributes($orkardCustomer)
    {
        $payload = $this->getBasePayload();
        $payload['action'] = __FUNCTION__;
        $payload['data'] = $orkardCustomer->toArray();

        return $this->makePostCall($payload);
    }

    /**
     * @param $idcli
     * @param null $date_from
     * @param null $date_to
     * @return array|false
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     * @throws \WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException
     */
    public function getJSONCustomerTickets($idcli, $date_from = null, $date_to = null)
    {
        if ($date_from == null) {
            $date_from = date('Y-m-d', 0);
        }

        if ($date_to == null) {
            $date_to = date('Y-m-d');
        }

        $payload = $this->getBasePayload();
        $payload['action'] = __FUNCTION__;
        $payload['data'] = array(
            'idcli' => $idcli,
            'date_from' => date('Y-m-d', strtotime($date_from)),
            'date_to' => date('Y-m-d', strtotime($date_to)),
        );

        return $this->makePostCall($payload);
    }

    /**
     * @param string $idcli
     * @param int $idcpt
     * @param int $balance
     * @return array|false
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     * @throws \WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException
     */
    public function assignBonusToClient($idcli, $idcpt, $balance)
    {
        $payload = $this->getBasePayload();
        $payload['action'] = __FUNCTION__;
        $payload['data'] = array(
            "idcli" => $idcli,
            "idcpt" => $idcpt,
            "balance" => $balance,
        );

        return $this->makePostCall($payload);
    }

    /**
     * @param string $idcli
     * @return array|false
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     * @throws \WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException
     */
    public function assignWelcomeVoucherToClient($idcli)
    {
        return $this->assignBonusToClient($idcli, 100, 1);
    }
}