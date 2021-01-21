<?php

namespace WpkColoco\Service;

use InvalidArgumentException;
use Validate;
use WpkColoco\Model\AwaitingVerificationCustomer;

/**
 * Class AccountVerificationKeyService
 *
 * @package WpkColoco\Service
 */
class AccountVerificationKeyService
{
    /**
     * @var \WpkColoco\Service\EncryptionService
     */
    private $encryptionService;

    /**
     * AccountVerificationKeyService constructor.
     *
     * @param AwaitingVerificationCustomer $colocoAccountVerification
     */
    public function __construct()
    {
        $this->encryptionService = new EncryptionService();
    }

    /**
     * @param AwaitingVerificationCustomer $colocoAccountVerification
     * @return bool|string
     */
    public function generateKey($colocoAccountVerification)
    {
        if (!Validate::isLoadedObject($colocoAccountVerification)) {
            throw new InvalidArgumentException("colocoAccountVerification must be a loaded ObjectModel");
        }

        $value = array(
            'id_customer' => $colocoAccountVerification->id_customer,
            AwaitingVerificationCustomer::$definition['primary'] => $colocoAccountVerification->id,
        );

        return $this->encryptionService->encrypt(json_encode($value));
    }

    /**
     * @param string $key
     * @return array
     */
    public function getValuesFromKey($key)
    {
        $value = $this->encryptionService->decrypt($key);

        return json_decode($value, true);
    }
}