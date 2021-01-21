<?php

namespace WpkColoco\Service\ColocoCustomer;

use Address;
use Country;
use Language;
use WpkColoco\Wepika\Formatter\AddressFormatter;

/**
 * Class ColocoCustomerHydrationService
 *
 * Use this service to hydrate a ColocoCustomer model
 *
 * @package WpkColoco\Service\ColocoCustomer
 */
class ColocoCustomerHydrationService
{
    /**
     * @param \WpkColoco\Model\ColocoCustomer $colocoCustomer
     * @param \WpkColoco\Wepika\OrkardApi\Entity\OrkardCustomer $orkardCustomer
     * @return \WpkColoco\Model\ColocoCustomer
     */
    public function hydrateFromOrkardCustomer($colocoCustomer, $orkardCustomer)
    {
        foreach ($orkardCustomer->toArray() as $key => $value) {
            if (property_exists($colocoCustomer, $key)) {
                $colocoCustomer->{$key} = $value;
            }
        }

        return $colocoCustomer;
    }

    /**
     * @param Address $address
     * @param \WpkColoco\Wepika\OrkardApi\Entity\OrkardCustomer $orkardCustomer
     * @return \Address
     */
    public function hydrateAddressFromOrkardCustomer($address, $orkardCustomer)
    {
        $id_country = (int)Country::getIdByName(Language::getIdByIso('en'), $orkardCustomer->getPays());
        $address->id_country = $id_country;
        $address->postcode = $orkardCustomer->getCp();
        $address->lastname = $orkardCustomer->getNom();
        $address->firstname = $orkardCustomer->getPrenom();
        if ($orkardCustomer->getAdr1()) {
            $addressFormatter = new AddressFormatter();
            $address->address1 = $addressFormatter->mergeAddressParts(
                $orkardCustomer->getAdr1(),
                $orkardCustomer->getAdr2(),
                $orkardCustomer->getAdr3()
            );
        }
        $address->address2 = $orkardCustomer->getAdr3();
        $address->city = $orkardCustomer->getVille();
        $address->phone_mobile = $orkardCustomer->getGsm();
        $address->alias = 'Mon adresse';

        return $address;
    }
}