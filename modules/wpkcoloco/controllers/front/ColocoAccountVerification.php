<?php

use WpkColoco\Model\AwaitingVerificationCustomer;
use WpkColoco\Model\ColocoCustomer;
use WpkColoco\Service\AccountVerificationKeyService;
use WpkColoco\Service\ColocoCustomer\ColocoCustomerHydrationService;
use WpkColoco\Wepika\Curl\Exception\CurlErrorException;
use WpkColoco\Wepika\OrkardApi\Entity\OrkardCustomer;
use WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException;

/**
 * Class WpkColocoColocoAccountVerificationModuleFrontController
 */
class WpkColocoColocoAccountVerificationModuleFrontController extends ModuleFrontController
{
    /**
     * WpkColocoColocoAccountVerificationModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();

        $this->setTemplate('module:' . $this->module->name . '/views/templates/front/accountVerification.tpl');
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function postProcess()
    {
        $key = Tools::getValue("key");
        $redirect = true;

        // If a key was submitted
        if ($key) {
            $accountVerificationKeyService = new AccountVerificationKeyService();

            // Decrypt key
            $values = $accountVerificationKeyService->getValuesFromKey($key);

            // Check if $values contains expected values
            if (
                is_array($values)
                && isset($values['id_customer'])
                && isset($values[AwaitingVerificationCustomer::$definition['primary']])) {
                // Load objects and verify them
                $awaitingVerificationCustomer = new AwaitingVerificationCustomer(
                    $values[AwaitingVerificationCustomer::$definition['primary']]
                );

                if (Validate::isLoadedObject($awaitingVerificationCustomer)) {
                    $customer = new Customer($values['id_customer']);

                    if (Validate::isLoadedObject($customer)) {
                        // Check if verification concern the given customer
                        if ($awaitingVerificationCustomer->id_customer == $customer->id) {
                            // Everything is alright, we can start binding PS and Coloco
                            $redirect = false;
                            /* @var WpkColoco $module */
                            $module = $this->module;
                            $error = false;

                            try {
                                // Get Coloco customer
                                // If $awaitingVerificationCustomer contains an "idcli", use it to fetch api data
                                if ($awaitingVerificationCustomer->idcli) {
                                    $orkardCustomer = $module->getColocoApiHandler()->getClientWithAttributes(
                                        $awaitingVerificationCustomer->idcli
                                    );
                                } else {
                                    // Otherwise use client email
                                    $orkardCustomer = $module->getColocoApiHandler()->getClientByEmailWithAttributes(
                                        $customer->email
                                    );
                                }

                                // Check if we found a card or not
                                if ($orkardCustomer->getIdcli()) {
                                    // If user used form where he has to choose between accounts, do
                                    if (Tools::isSubmit('chooseBetweenAccount')) {
                                        $this->processChooseBetweenAccounts($customer, $orkardCustomer);
                                    }

                                    // Check if this card is not already bind to another customer
                                    if (!$this->isCardAlreadyBind($orkardCustomer->getIdcli())) {
                                        // If name / firstname is are different in PS and Coloco
                                        if ($this->isSameCustomer($orkardCustomer, $customer)) {
                                            $this->bindCustomer($awaitingVerificationCustomer, $orkardCustomer);
                                        } else {
                                            $idPsAddress = (int)Address::getFirstCustomerAddressId($customer->id);
                                            $psAddress =
                                                (Validate::isLoadedObject($address = new Address($idPsAddress)))
                                                    ? $address
                                                    : null;
                                            $psCountry = Country::getNameById(
                                                Context::getContext()->language->id,
                                                $psAddress->id_country
                                            );

                                            Context::getContext()->smarty->assign(
                                                array(
                                                    'ps_coloco_conflict' => true,
                                                    'colocoCustomer' => $orkardCustomer,
                                                    'psCustomer' => $customer,
                                                    'psAddress' => $psAddress,
                                                    'psCountryName' => $psCountry,
                                                    'formLink' => Context::getContext()->link->getModuleLink(
                                                        $this->module->name,
                                                        'ColocoAccountVerification',
                                                        array(
                                                            'key' => $key,
                                                        )
                                                    )
                                                )
                                            );
                                        }
                                    } else {
                                        $colocoCustomer = ColocoCustomer::getByIdCli($orkardCustomer->getIdcli());

                                        // This should be always true
                                        if (Validate::isLoadedObject($colocoCustomer)) {
                                            // Assign welcome voucher to client
                                            $module->getColocoApiHandler()->assignWelcomeVoucherToClient(
                                                $colocoCustomer->idcli
                                            );

                                            // Customer is no longer in awaiting state
                                            $awaitingVerificationCustomer->delete();

                                            Context::getContext()->smarty->assign(
                                                array(
                                                    'welcome_voucher_given' => true
                                                )
                                            );
                                        } else {
                                            $error = true;
                                            $module->getLogger()->log(
                                                "An error occurred for the customer #" . $colocoCustomer->id_customer . ". is bound but no colocoCustomer found"
                                            );
                                        }
                                    }
                                }
                            } catch (CurlErrorException $e) {
                                $error = true;
                                $module->getLogger()->log($e);
                            } catch (OrkardApiErrorException $e) {
                                $error = true;
                                $module->getLogger()->log($e);
                            } catch (PrestaShopException $e) {
                                $error = true;
                                $module->getLogger()->log($e);
                            }

                            if ($error) {
                                Context::getContext()->smarty->assign(
                                    array(
                                        'gotError' => true
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }

        if ($redirect) {
            Tools::redirect('index');
        }

        parent::postProcess();
    }

    /**
     * @param Customer $customer
     * @param OrkardCustomer $orkardCustomer
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \WpkColoco\Wepika\Curl\Exception\CurlErrorException
     * @throws \WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException
     */
    private function processChooseBetweenAccounts($customer, $orkardCustomer)
    {
        $chosenInformationType = Tools::getValue("chosen_information");

        // Check submit type
        if ($chosenInformationType == 'ps' || $chosenInformationType == 'coloco') {
            if ($chosenInformationType == 'ps') {
                // If user chose PS update Orkard
                $orkardCustomer->setPrenom($customer->firstname);
                $orkardCustomer->setNom($customer->lastname);

                // Update orkard customer with coloco informations
                /* @var \WpkColoco $module */
                $module = $this->module;
                $module->getColocoApiHandler()->updateClientWithAttributes(
                    $orkardCustomer
                );
            } elseif ($chosenInformationType == 'coloco') {
                // If user chose coloco update PS
                $customer->firstname = $orkardCustomer->getPrenom();
                $customer->lastname = $orkardCustomer->getNom();
                $customer->save();
            }
        }
    }

    /**
     * @param string $idcli
     * @return false|string|null
     * @throws \PrestaShopException
     */
    protected function isCardAlreadyBind($idcli)
    {
        $query = new DbQuery();
        $query->select(Customer::$definition['primary']);
        $query->from(ColocoCustomer::$definition['table']);
        $query->where('idcli = ' . pSQL($idcli));

        return Db::getInstance()->getValue($query->build());
    }

    /**
     * Check if orkardCustomer name and firstname is equal to PsCustomer name and firstname
     *
     * @param OrkardCustomer $orkardCustomer
     * @param Customer $customer
     * @return bool
     */
    private function isSameCustomer($orkardCustomer, $customer)
    {
        $colocoFirstname = strtolower(trim($orkardCustomer->getPrenom()));
        $colocoLastName = strtolower(trim($orkardCustomer->getNom()));
        $psFirstname = strtolower(trim($customer->firstname));
        $psLastName = strtolower(trim($customer->lastname));

        return $colocoFirstname == $psFirstname && $colocoLastName == $psLastName;
    }

    /**
     * @param AwaitingVerificationCustomer $awaitingVerificationCustomer
     * @param OrkardCustomer $orkardCustomer
     * @throws \PrestaShopException
     */
    private function bindCustomer($awaitingVerificationCustomer, $orkardCustomer)
    {
        // Create a new entry in PS
        $colocoCustomer = new ColocoCustomer();
        $colocoCustomer->id_customer = (int)$awaitingVerificationCustomer->id_customer;

        // Init colocoCustomer object with api informations
        $colocoCustomerHydrationService = new ColocoCustomerHydrationService();
        $colocoCustomer = $colocoCustomerHydrationService->hydrateFromOrkardCustomer(
            $colocoCustomer,
            $orkardCustomer
        );

        $address = $colocoCustomerHydrationService->hydrateAddressFromOrkardCustomer(new Address(), $orkardCustomer);
        if ($address->validateFields(false)) {
            $address->id_customer = $colocoCustomer->id_customer;
            $address->save();

            $colocoCustomer->id_address = $address->id;
        }

        // Save new colocoCustomer
        if ($colocoCustomer->save()) {
            // Customer is no longer in awaiting state
            $awaitingVerificationCustomer->delete();

            // Assign welcome voucher to client
            /* @var WpkColoco $module */
            $module = $this->module;
            try {
                $module->getColocoApiHandler()->assignWelcomeVoucherToClient($colocoCustomer->idcli);
            } catch (CurlErrorException $e) {
                $module->getLogger()->log($e);
                $module->getLogger()->log(
                    'Concerned client for previous error : ' . $colocoCustomer->idcli . ' (PS ID : ' . $colocoCustomer->id_customer . ')'
                );
            } catch (OrkardApiErrorException $e) {
                $module->getLogger()->log($e);
                $module->getLogger()->log(
                    'Concerned client for previous error : ' . $colocoCustomer->idcli . ' (PS ID : ' . $colocoCustomer->id_customer . ')'
                );
            }

            Context::getContext()->smarty->assign(
                array(
                    'account_verified' => true,
                )
            );
        }
    }
}