<?php

use WpkColoco\Model\AwaitingVerificationCustomer;
use WpkColoco\Model\ColocoCustomer;
use WpkColoco\Service\ColocoCustomer\ColocoCustomerHydrationService;
use WpkColoco\Wepika\Curl\Exception\CurlErrorException;
use WpkColoco\Wepika\Formatter\AddressFormatter;
use WpkColoco\Wepika\OrkardApi\Entity\OrkardCustomer;
use WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException;

require_once __DIR__ . '/PostAjaxLogin.php';


class WpkColocoLoyaltyCardModuleFrontController extends ModuleFrontController
{
    public $php_name = 'loyalty_card';

    public function init()
    {
        if (!$this->context->customer->isLogged()) {
            Tools::redirect(__PS_BASE_URI__);
        }

        $this->context = Context::getContext();
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();
       /* echo '<pre>';
        var_dump($_POST, $_GET);exit();*/
        // To avoid making api calls during ajax calls (store)
        if ($this->ajax) {
            return;
        }

        /* @var WpkColoco $module */
        $module = $this->module;
        $customer = $this->context->customer;
        /* @var ColocoCustomer $colocoCustomer */

        if (Tools::getValue('resend_email')) { //If the client re-want a confirmation mail.

            // Get the awaiting Customer.
            $awaitingVerificationCustomer = AwaitingVerificationCustomer::getAwaitingVerificationCustomerByIdCustomer(
                $customer->id
            );

            if ($awaitingVerificationCustomer != null) { // If it is not on standby, an error is generated (it shouldn't happen).

                $awaitingVerificationCustomer->request_count = ((int)$awaitingVerificationCustomer->request_count) + 1;
                if ($awaitingVerificationCustomer->save()) {
                    // Trying to send the mail
                    $isMailSent = WpkColocoPostAjaxLoginModuleFrontController::sendVerificationEmail($awaitingVerificationCustomer, $module);
                    if (!$isMailSent) { // The sendind failed
                        $this->errors[] = $this->trans('The mail was not sent', array(), 'Modules.Wpkcoloco.Loyaltycard');
                        $module->getLogger()->log(
                            "Email de vérification n'a pas pû être envoyé au client #$awaitingVerificationCustomer->id_customer"
                        );
                    } else {
                        // Post ajax is now completed
                        $this->success[] = $this->trans('We have a confirmation link to the email address of the card entered.', array(), 'Modules.Wpkcoloco.Loyaltycard');
                    }
                } else { // If the save of waiting customer doesn't work
                    $this->errors[] = $this->trans('You could not be put on hold for validation.', array(), 'Modules.Wpkcoloco.Loyaltycard');
                    $module->getLogger()->log(
                        "Client #$awaitingVerificationCustomer->id_customer could not be put on hold for validation."
                    );
                }
            } else {
                $this->errors[] = $this->trans('You have no pending cards', array(), 'Modules.Wpkcoloco.Loyaltycard');
            }
        } elseif (Tools::isSubmit('link_a_card')) { // If the customer wants to link a physical card
            $orkardCustomer = self::clientExistInApi('idcli', (string)Tools::getValue('card_number')); // Get OrkardCustomer if otherwise false
            if ($orkardCustomer) { // If the card exists, test whether it is linked to ps
                $card_exist_in_ps = ColocoCustomer::getByIdCli((string)$orkardCustomer->getIdcli());
                if (Validate::isLoadedObject($card_exist_in_ps)) {
                    $this->errors[] = $this->trans('The card number entered are already link to an other acount on this website', array(), 'Modules.Wpkcoloco.Loyaltycard');
                } else {
                    $this->sendConfirmLink($customer,$orkardCustomer);
                }
            } else { // If the card doesn't exists
                $this->errors[] = $this->trans('The card number entered does not exist', array(), 'Modules.Wpkcoloco.Loyaltycard');
            }
        } elseif (Tools::isSubmit('request_a_card')) { // Pressing "request a digital card"

            $colocoCustomer = ColocoCustomer::getByIdCustomer($customer->id);
            $orkardCustomer = self::clientExistInApi('email', $customer->email);
            if (Validate::isLoadedObject($colocoCustomer) || ($orkardCustomer && $orkardCustomer->getIdcli())) {// If already coloco customer, so having alrready a card
                $this->errors[] = $this->trans('You already have a card', array(), 'Modules.Wpkcoloco.Loyaltycard');
            } else {
                // test the prefered store
                $id_prefered_store = (int)Tools::getValue("wpkpickup_selected_store");
                
                if ($id_prefered_store == 0) {
                    $this->errors[] =  $this->trans('Choose a store', array(), 'Modules.Wpkcoloco.Loyaltycard');
                }else{
                    $sql = 'SELECT email 
                            FROM '._DB_PREFIX_.'store where id_store ='.$id_prefered_store;

                    $mail = DB::getInstance()->getValue($sql);
                    if(is_null($mail) || !$mail){
                        $this->errors[] = $this->trans('Store doesn\t exist', array(), 'Modules.Wpkcoloco.Loyaltycard');
                    }
                    $mail_splitted = explode('@',$mail);
                    $idm = $mail_splitted[0];
                }

                $id_address = Tools::getValue("id_address");
                $addressFormatter = new AddressFormatter();
                if ($id_address) {
                    $addressobj = new Address($id_address);
                    if ($customer->id != $addressobj->id_customer) {
                        $this->errors[] = $this->trans('You don\'t own this address, contact the support pls.', array(), 'Modules.Wpkcoloco.Loyaltycard');
                    }
                    $address= $addressFormatter->formatAddress($addressobj->address1,$addressobj->address2);
                    $address_splitted = $addressFormatter->getAddressParts($address);
                } else {
                    $addressobj = new Address();

                    //set the basic values
                    $addressobj->id_customer = $customer->id;
                    $addressobj->alias = 'Coloco';
                    $addressobj->lastname = $customer->lastname;
                    $addressobj->firstname = $customer->firstname;
                    $address_splitted['street']= Tools::getValue("address");
                    $address_splitted['number']= Tools::getValue("number");
                    $address_splitted['box']= Tools::getValue("box");


                    // Test all datas
                    $addressobj->address1 = $addressFormatter->mergeAddressParts($address_splitted['street'], $address_splitted['number'], $address_splitted['box']);
                    if (!empty($addressobj->address1) && !Validate::isAddress($addressobj->address1)) {
                        $this->errors[] = $this->trans('Invalid address', array(), 'Modules.Wpkcoloco.Loyaltycard');
                    }
                    $addressobj->address2 = Tools::getValue("address2");


                    $addressobj->postcode = Tools::getValue("postcode");
                    if (!empty($addressobj->postcode) && !Validate::isPostCode($addressobj->postcode)) {
                        $this->errors[] = $this->trans('Invalid postcode', array(), 'Modules.Wpkcoloco.Loyaltycard');
                    }

                    $addressobj->city = Tools::getValue("city");
                    if (!empty($addressobj->city) && !Validate::isCityName($addressobj->city)) {
                        $this->errors[] = $this->trans('Invalid city', array(), 'Modules.Wpkcoloco.Loyaltycard');
                    }

                    $addressobj->phone = Tools::getValue("phone");
                    if (!empty($addressobj->phone) && !Validate::isPhoneNumber($addressobj->phone)) {
                        $this->errors[] = $this->trans('Invalid phone number', array(), 'Modules.Wpkcoloco.Loyaltycard');
                    }

                    $idCountries = array_map(
                        static function ($row) {
                            return (int)$row['id_country'];
                        },
                        Country::getCountries($this->context->language->id, true)
                    );

                    $addressobj->id_country = Tools::getValue("country");
                    if (!in_array($addressobj->id_country, $idCountries)) {
                        $this->errors[] = $this->trans('Invalid country', array(), 'Modules.Wpkcoloco.Loyaltycard');
                    }
                }
                

                // If no errors
                if (empty($this->errors)) {
                    //try to save the address
                    if (!$addressobj->save()) {
                        $this->errors[] = $this->trans('Impossible to create your address', array(), 'Modules.Wpkcoloco.Loyaltycard');
                    } else {
                        try {
                            //Retrieves a new digital card
                            $call = $module->getColocoApiHandler()->getNewDigitalCard();
                            $new_digital_card = $call['data']['idcli'];
                        } catch (CurlErrorException $e) {
                            $module->getLogger()->log($e);
                        } catch (OrkardApiErrorException $e) {
                            $module->getLogger()->log($e);
                        }

                        if (isset($new_digital_card) && $new_digital_card !== '') { // If we receive a customer code
                            // Create an orkardCustomer and update the API
                            $customer = Context::getContext()->customer;
                            $orkardCustomer = new OrkardCustomer();
                            $orkardCustomer->setIdcli($new_digital_card);
                            $orkardCustomer->setIdm((int)$idm);
                            $orkardCustomer->setNom($customer->lastname);
                            $orkardCustomer->setPrenom($customer->firstname);
                            $orkardCustomer->setCiv((int)$customer->id_gender);
                            $orkardCustomer->setDtnai($customer->birthday);
                            $orkardCustomer->setEmail($customer->email);

                            $orkardCustomer->setAdr1($address_splitted['street']); //rue
                            $orkardCustomer->setAdr2($address_splitted['number']);// numéro
                            $orkardCustomer->setAdr3($address_splitted['box']); // boite

                            $orkardCustomer->setCp($addressobj->postcode);
                            $orkardCustomer->setGsm($addressobj->phone);
                            $orkardCustomer->setVille($addressobj->city);
                            $orkardCustomer->setPays($countryName = Country::getNameById((int)$this->context->language->id, (int)$addressobj->id_country));

                            $orkardCustomer->setIdlng(strtolower($this->context->language->iso_code));
                            $orkardCustomer->setAct(0);

                            //TODO gérer les Optins
                            $orkardCustomer->setOEmailing(0);
                            $orkardCustomer->setOTexting(0);
                            $orkardCustomer->setOTexting2(0);
                            $orkardCustomer->setStat1(0);
                            $orkardCustomer->setStat5(0);
                            $orkardCustomer->setTexting(0);

                            try {
                                if ($module->getColocoApiHandler()->updateClientWithAttributes($orkardCustomer)) { //Update the data in the api
                                    //Create the colocoCustomer
                                    $colocoCustomer = new ColocoCustomer();

                                    $colocoCustomerHydrationService = new ColocoCustomerHydrationService();
                                    $colocoCustomerHydrationService->hydrateFromOrkardCustomer(
                                        $colocoCustomer,
                                        $orkardCustomer
                                    );

                                    $colocoCustomer->id_customer = $customer->id;
                                    $colocoCustomer->id_address = $addressobj->id;

                                    if ($colocoCustomer->save()) {
                                        //TODO send a confirm mail
                                        $this->sendConfirmLink($customer,$orkardCustomer);
                                    } else {
                                        $this->errors[] = $this->trans('Impossible to create your card', array(), 'Modules.Wpkcoloco.Loyaltycard');
                                    }

                                } else {
                                    $this->errors[] = $this->trans('Impossible to set your card in our server', array(), 'Modules.Wpkcoloco.Loyaltycard');
                                }

                            } catch (CurlErrorException $e) {
                                $module->getLogger()->log($e);
                                $this->errors[] = $this->trans('Connection with the api impossible, please try again later.', array(), 'Modules.Wpkcoloco.Loyaltycard');

                                $module->getLogger()->log(
                                    'Concerned client for previous error : ' . $colocoCustomer->idcli . ' (PS ID : ' . $colocoCustomer->id_customer . ')'
                                );
                            } catch (OrkardApiErrorException $e) {
                                $module->getLogger()->log($e);
                                $this->errors[] = $this->trans('We encounter a problem with data lit, please try again later.', array(), 'Modules.Wpkcoloco.Loyaltycard');

                                $module->getLogger()->log(
                                    'Concerned client for previous error : ' . $colocoCustomer->idcli . ' (PS ID : ' . $colocoCustomer->id_customer . ')'
                                );
                            } catch (PrestaShopException $e) {
                                $module->getLogger()->log($e);
                            }

                        }
                    }
                }
            }
        }

        // Redefinition in case you have just applied for a digital card
        $colocoCustomer = ColocoCustomer::getByIdCustomer($customer->id);
        if (Validate::isLoadedObject($colocoCustomer)) { //If it's a coloco customer --> if he has a card
            try {
                if (!$cardType = $colocoCustomer->getCardType()) { // Test the type of the card
                    $this->errors[] = $this->trans('The card has no type, please contact the support', array(), 'Modules.Wpkcoloco.Loyaltycard');
                } else {
                    // TODO See if the other counters are displayed, if so, how?
                    $compteurs = $module->getColocoApiHandler()->getClientBalancePoint($colocoCustomer->idcli);
                    foreach ($compteurs['data'] as $compteur) {
                        switch ($compteur['idcpt']) {
                            case 2:
                                $points_coloco = $compteur['balance'];
                                break;
                            case 33:
                                $digital_bon = $compteur['balance'];
                                break;
                            case 100:
                                $welcome_voucher = $compteur['balance'];
                                break;
                        }
                    }
                    $is_valid_customer = true;
                    if (AwaitingVerificationCustomer::getAwaitingVerificationCustomerByIdCustomer($customer->id)) {
                        $is_valid_customer = false;
                    }

                    $this->getBreadcrumbLinks();
                    $this->context->smarty->assign(array(
                        "coloco_card" => $colocoCustomer->idcli,
                        "cardType" => $cardType,
                        "is_valid" => $is_valid_customer,
                        "coloco_points" => isset($points_coloco) ? $points_coloco : 0,
                        "digital_bon" => isset($digital_bon) ? $digital_bon : 0,
                        "welcome_voucher" => isset($welcome_voucher) ? $welcome_voucher : 0,
                        "bg_card_info" => _PS_BASE_URL_SSL_.'/modules/wpkcoloco/img/bg_card_info.png',
                    ));
                }

            } catch (CurlErrorException $e) {
                $module->getLogger()->log($e);
            } catch (OrkardApiErrorException $e) {
                $module->getLogger()->log($e);
            } catch (Exception $e) {
                $module->getLogger()->log($e);
            }
        } else { // If the customer does not have a card
            $awaitingCustomer = false;
            if (AwaitingVerificationCustomer::getAwaitingVerificationCustomerByIdCustomer($customer->id)) {
                $awaitingCustomer = true;
            }

            if (count($customer_addresses = $customer->getAddresses($this->context->language->id)) > 0) {
                $customer_address = $customer_addresses[0];
            } else {
                $customer_address = false;
            }


            $this->context->smarty->assign(array(
                "customer_address" => $customer_address,
                "cardType" => false,
                "countries" => Country::getCountries($this->context->language->id, true),
                "awaitingCustomer" => $awaitingCustomer,
            ));
        }

        $this->context->smarty->assign(array(
            "module_template_path" => __DIR__ . '/../../views/templates/front/',
            "link_to_contact" => $this->context->link->getPageLink('Contact'),
        ));

        $this->setTemplate('module:wpkcoloco/views/templates/front/loyalty-card.tpl');


    }

    public function setMedia()
    {
        $var_js = array(
            'loyaltyController' => $this->context->link->getModuleLink('wpkcoloco','LoyaltyCard'),
        );

        Media::addJsDef($var_js);
        return parent::setMedia(); // TODO: Change the autogenerated stub
    }

    private function getStoreIdList($withCity = false)
    {
        $stores = Store::getStores(Context::getContext()->language->id);

        $storeIdList = array();
        foreach ($stores as $key => $store) {
            if (isset($store['email']) && $store['email'] != "") {
                $explodedEmail = explode('@', $store['email']);

                if ($explodedEmail) {

                    if ($withCity) {
                        $storeIdList[$key]['id'] = (int)$explodedEmail[0];
                        $storeIdList[$key]['city'] = $store['city'];
                    } else {
                        $storeIdList[$key] = (int)$explodedEmail[0];
                    }
                }


            }
        }

        return $storeIdList;
    }

    /**
     * @param string $type
     * @param string $value
     * @return false|OrkardCustomer
     */
    private function clientExistInApi($type = 'email', $value)
    {
        /* @var WpkColoco $module */
        $module = $this->module;

        /* @var \WpkColoco\Wepika\OrkardApi\Entity\OrkardCustomer $orkardCustomer */
        try {
            switch ($type) {
                case 'email' :
                    $orkardCustomer = $module->getColocoApiHandler()->getClientByEmailWithAttributes($value);
                    break;
                case 'idcli':
                    $orkardCustomer = $module->getColocoApiHandler()->getClientWithAttributes($value);
                    break;
            }

        } catch (CurlErrorException $e) {
            $module->getLogger()->log($e);
        } catch (OrkardApiErrorException $e) {
            if ($e->getMessage() === 'Unknown client') {
                $orkardCustomer = false;
            } else {
                $module->getLogger()->log($e);
            }

        }

        return $orkardCustomer;
    }

    /**
     * @return array
     */
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();
        $breadcrumb['links'][] = [
            'title' => $this->trans('Coloco Card', array(), 'Modules.Wpkcoloco.Loyaltycard'),
            'url' => $this->context->link->getModuleLink('wpkcoloco', 'LoyaltyCard')
        ];

        return $breadcrumb;
    }

    /**
     * Ajax call when the customer wants to find the stores near his home
     */
    public function displayAjaxGetNearbyShop()
    {
        self::getStores(Tools::getValue('postal_code_search'), Tools::getValue('country_search'), Tools::getValue('range_search'));
    }

    /**
     * Look for stores close to the customer's request
     *
     * @param $postal_code
     * @param $country
     * @param $range
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \SmartyException
     */
    public function getStores($postal_code, $country, $range)
    {
        $search_distance = 0;

        $search_postcode = trim($postal_code);
        $search_distance = (int)trim($range);

        if ($search_distance < 5 || $search_distance > 250){
            $search_distance = 0;
        }

        $search_country = $country;
        $search_country_code = Country::getIsoById($search_country);

        if (!Validate::isPostCode($search_postcode) || empty($search_postcode) || strlen($search_postcode) > 6) {
            $this->context->controller->errors[] = $this->module->l('Invalid postcode format', get_class());
        } else {
            $coordinates = PickupTools::geolocatePostcode($search_country_code, $search_postcode);
            if ($coordinates) {
                if (!$search_distance){
                    $search_distance = $coordinates['range'];
                }
                $stores = PickupTools::searchStores($coordinates['lat'], $coordinates['lng'], $coordinates['id_country'], $search_distance);
            }
        }

        // If parameter "preselect" was submitted and current user is a logged customer
        $preselect = (bool)Tools::getValue("preselect");
        if ($preselect && Context::getContext()->customer->isLogged()) {
            // If customer has a bound coloco card, store can be preselected
            $colocoCustomer = ColocoCustomer::getByIdCustomer(Context::getContext()->customer->id);

            // Get the PS id_store from coloco informations
            $query = new DbQuery();
            $query->select(Store::$definition['primary']);
            $query->from(Store::$definition['table']);
            $query->where("active = 1");
            $query->where("email like '" . pSQL($colocoCustomer->idm) . "%'");

            $psIdStoreBasedOnColoco = (int)Db::getInstance()->getValue($query->build());
        }

        // Put exact postcode match at the top
        if (isset($search_postcode)) {
            $best_match_store = [];
            foreach ($stores as $key => &$row) {
                // if preselected parameter was given and we could fetch a PS id store, store is selected
                $row['selected'] =
                    $preselect &&
                    isset($psIdStoreBasedOnColoco) &&
                    $psIdStoreBasedOnColoco > 0 &&
                    $row['id_store'] == $psIdStoreBasedOnColoco
                ;

                if ($row['postcode'] == $search_postcode) {
                    $best_match_store[] = $row;
                    unset($stores[$key]);
                }
            }
            unset($row);
            $stores = array_merge($best_match_store, $stores);
        }

        if (count($stores)){
            $this->context->smarty->assign(array(
                "stores" => $stores,
            ));
            die($this->context->smarty->fetch('module:wpkcoloco/views/templates/front/partials/stores_nearby.tpl'));
        }else{ // If no store found
            die('<p>'.$this->trans('No stores within the scope of your search', array(), 'Modules.Wpkcoloco.Loyaltycard').'</p>');
        }


    }

    public function sendConfirmLink($customer,$orkardCustomer){
        // Customer is in awaiting verification state
        $awaitingVerificationCustomer = AwaitingVerificationCustomer::getAwaitingVerificationCustomerByIdCustomer(
            $customer->id
        );
        if ($awaitingVerificationCustomer == null) {
            $awaitingVerificationCustomer = new AwaitingVerificationCustomer();
            $awaitingVerificationCustomer->id_customer = (int)$customer->id;
            $awaitingVerificationCustomer->idcli = $orkardCustomer->getIdcli();
        }
        $awaitingVerificationCustomer->request_count = ((int)$awaitingVerificationCustomer->request_count) + 1;

        if ($awaitingVerificationCustomer->save()) {
            $isMailSent = WpkColocoPostAjaxLoginModuleFrontController::sendVerificationEmail($awaitingVerificationCustomer, $this->module);
            if (!$isMailSent) {
                $this->module->getLogger()->log(
                    "Email de vérification n'a pas pû être envoyé au client #$awaitingVerificationCustomer->id_customer"
                );
            } else {
                // Post ajax is now completed
                $this->success[] = $this->trans('We have a confirmation link to the email address of the card entered.', array(), 'Modules.Wpkcoloco.Loyaltycard');
            }
        } else {
            $this->module->getLogger()->log(
                "Client #$awaitingVerificationCustomer->id_customer n'a pas pu être mis en attente de validation."
            );
        }
    }

}
