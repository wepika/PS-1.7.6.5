<?php

use WpkColoco\Wepika\Curl\Exception\CurlErrorException;
use WpkColoco\Wepika\Formatter\AddressFormatter;
use WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException;
use WpkColoco\Wepika\OrkardApi\OrkardApiCredential;
use WpkColoco\Wepika\OrkardApi\OrkardApiHandler;
use WpkColoco\Model\AwaitingVerificationCustomer;
use WpkColoco\Model\ColocoCustomer;
use WpkColoco\Model\PostAjaxLoginApplication;
use WpkColoco\Service\ColocoCustomer\ColocoCustomerHydrationService;
use WpkColoco\Service\AccountVerificationKeyService;
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Class WpkColoco
 */
class WpkColoco extends Module
{
    /**
     * @var string
     */
    public static $module_name = "wpkcoloco";
    /**
     * @var string[]
     */
    private $hooks = array(
        "actionAuthentication",
        "validateCustomerFormFields",
        "actionFrontControllerSetMedia",
        "displayCustomerAccount",
        "actionCustomerLogoutBefore",
        "actionCustomerAccountUpdate",
        "header",
        "actionCustomerAccountAdd",
        "actionSubmitAccountAfter",
    );
    /**
     * @var \WpkColoco\Wepika\OrkardApi\OrkardApiCredential
     */
    private $colocoApiCredentials;
    /**
     * @var \WpkColoco\Wepika\OrkardApi\OrkardApiHandler
     */
    private $colocoApiHandler;
    /**
     * @var \WpkColoco\Wepika\Log\Logger
     */
    private $logger;
    /**
     * @var bool
     */
    private $postAjaxLoginApplicationAlreadyMade = false;
    /**
     * @var bool
     */
    private $hookActionCustomerAccountUpdateSuccessful = false;
    /**
     * @var bool
     */
    const COLOCO_PROD_MODE = true;

    /**
     * WpkColoco constructor.
     */
    public function __construct()
    {
        $this->name = self::$module_name;
        $this->version = '1.0.0';
        $this->author = 'Wepika';
        $this->id_lang = (int)Language::getIdByIso('fr');

        parent::__construct();

        $this->displayName = $this->l('WpkColoco');
        $this->description = $this->l('Manage Coloco in PS');

        $this->ps_versions_compliancy = array(
            'min' => '1.7.6.0',
            'max' => _PS_VERSION_,
        );

        // Set tools
        if (self::COLOCO_PROD_MODE) {
            // Ip de prod
            $ip = "10.128.202.101";
        } else {
            // Ip de dev
            $ip = "10.128.135.101";
        }
        $port = 6660;
        $user = "demo";
        $password = "fe01ce2a7fbac8fafaed7c982a04e229";

        $this->context = Context::getContext();
        $this->colocoApiCredentials = new OrkardApiCredential($ip, $port, $user, $password);

        $this->colocoApiHandler = new OrkardApiHandler($this->colocoApiCredentials);

        $this->logger = new \WpkColoco\Wepika\Log\Logger(_PS_MODULE_DIR_ . $this->name . '/logs/');
    }

    /**
     *
     */
    public function getContent()
    {
        // I only exist to access translations from the configuration page of the module
    }

    /**
     * @return bool
     */
    public function isHookActionCustomerAccountUpdateSuccessful()
    {
        return $this->hookActionCustomerAccountUpdateSuccessful;
    }

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function install()
    {
        return parent::install()
            && $this->installSql()
            && $this->installHooks()
            && $this->installModuleTab($this->getModuleTabs());
    }

    /**
     * @return bool
     */
    private function installSql()
    {
        $res = PostAjaxLoginApplication::createTables();
        $res &= ColocoCustomer::createTables();
        $res &= AwaitingVerificationCustomer::createTables();

        return $res;
    }

    /**
     * @return bool
     */
    private function installHooks()
    {
        /* @var $this \ModuleCore */
        $res = true;

        foreach ($this->hooks as $hook) {
            $res &= $this->registerHook($hook);
        }

        return $res;
    }

    /**
     * @param array $tabs
     * @param int $id_parent
     * @return bool|mixed
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function installModuleTab($tabs, $id_parent = 0)
    {
        $res = true;

        $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        foreach ($tabs as $tab) {
            $tab_id = Tab::getIdFromClassName($tab['class']);

            $o_tab = new Tab($tab_id);

            if ($tab_id) {
                $o_tab->active = 1;
                $o_tab->name[$id_lang] = $tab['name'];
            } else {
                $o_tab->name[$id_lang] = $tab['name'];
                $o_tab->class_name = $tab['class'];
                $o_tab->id_parent = $id_parent;
                $o_tab->module = $this->name;
            }

            $res &= $o_tab->save();

            if (isset($tab['children']) && count($tab['children'])) {
                $res &= $this->installModuleTab($tab['children'], $o_tab->id);
            }
        }

        return $res;
    }

    /**
     * @return array
     */
    protected function getModuleTabs()
    {
        return array(
            array(
                'name' => $this->l('Coloco'),
                'class' => 'WPKCOLOCO',
                'children' => array(
                    array(
                        'name' => $this->l('Coloco'),
                        'class' => 'AdminWpkColoco',
                    ),
                ),
            ),
        );
    }

    /**
     * @param array $params
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookHeader($params)
    {
        if (Tools::getValue("controller") == "identity") {
            // If we are on identity controller

            // Inject custom js
            Context::getContext()->controller->addJS(
                _PS_MODULE_DIR_ . $this->name . '/views/js/wpk_account_informations.js'
            );

            // Prepare data for store locator
            Media::addJsDef(
                array(
                    'loyaltyController' => $this->context->link->getModuleLink('wpkcoloco','LoyaltyCard'),
                )
            );

            Context::getContext()->smarty->assign(
                array(
                    'countries' => Country::getCountries(Context::getContext()->language->id, true),
                )
            );

            // Fetch api informations if possible

            // Get coloco customer stored in PS
            $colocoCustomer = ColocoCustomer::getByIdCustomer(Context::getContext()->customer->id);

            // Check if valid object and if it contains an idcli
            if (Validate::isLoadedObject($colocoCustomer) && $colocoCustomer->idcli) {
                try {
                    // Fetch from api
                    $orkardCustomer = $this->getColocoApiHandler()->getClientWithAttributes($colocoCustomer->idcli);

                    // Hydrate colocoCustomer
                    $colocoCustomerHydrationService = new ColocoCustomerHydrationService();
                    $colocoCustomerHydrationService->hydrateFromOrkardCustomer($colocoCustomer, $orkardCustomer);

                    // Hydrate PS address
                    $address = $colocoCustomerHydrationService->hydrateAddressFromOrkardCustomer(
                        new Address($colocoCustomer->id_address),
                        $orkardCustomer
                    );
                    // Check if address new informations can be stored
                    if ($address->validateFields(false)) {
                        // Assign it to customer and save it
                        $address->id_customer = $colocoCustomer->id_customer;
                        $address->save();

                        $colocoCustomer->id_address = $address->id;
                    }

                    // Save colocoCustomer
                    $colocoCustomer->save();
                } catch (CurlErrorException $e) {
                    $this->getLogger()->log($e);
                } catch (OrkardApiErrorException $e) {
                    $this->getLogger()->log($e);
                } catch (PrestaShopException $e) {
                    $this->getLogger()->log($e);
                }
            }
        }

        return $this->display(__FILE__, 'views/templates/hook/header.tpl');
    }

    /**
     * @return \WpkColoco\Wepika\OrkardApi\OrkardApiHandler
     */
    public function getColocoApiHandler()
    {
        return $this->colocoApiHandler;
    }

    /**
     * @return \WpkColoco\Wepika\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param array $params
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookActionCustomerAccountUpdate($params)
    {
        /* @var Customer $customer */
        $customer = $params['customer'];

        /* @var ColocoCustomer $colocoCustomer */
        $colocoCustomer = ColocoCustomer::getByIdCustomer($customer->id);

        if (Validate::isLoadedObject($colocoCustomer)) {
            // Validate user inputs

            // If coloco_email is submitted (it can be not submitted when emails are equals)
            if (Tools::getValue("coloco_email")) {
                $colocoCustomer->email = strtolower(trim(Tools::getValue("coloco_email")));
                if (!Validate::isEmail($colocoCustomer->email)) {
                    Context::getContext()->controller->errors[] = $this->trans('Invalid coloco email');
                }
            } else {
                // If no coloco_email were submitted, use the PS email instead
                $colocoCustomer->email = strtolower(trim(Tools::getValue("email")));
                if (!Validate::isEmail($colocoCustomer->email)) {
                    Context::getContext()->controller->errors[] = $this->trans('Invalid email');
                }
            }

            $birthdate = DateTime::createFromFormat('d/m/Y', Tools::getValue("birthday"));
            if ($birthdate) {
                $colocoCustomer->dtnai = $birthdate->format("Y-m-d");
            }
            if (!empty($colocoCustomer->dtnai) && !Validate::isBirthDate($colocoCustomer->dtnai)) {
                Context::getContext()->controller->errors[] = $this->trans('Invalid Birthdate');
            }

            $idStore = (int)Tools::getValue("wpkpickup_selected_store");
            if (Validate::isLoadedObject($store = new Store($idStore))) {
                $idColocoStore = (int)((explode('@', $store->email))[0]);

                $idColocoStores = $this->getStoreIdList();
                $colocoCustomer->idm = $idColocoStore;
                if (!in_array($colocoCustomer->idm, $idColocoStores)) {
                    Context::getContext()->controller->errors[] = $this->trans('Invalid preferred store');
                }
            }

            $colocoCustomer->adr1 = Tools::getValue("address1");
            $colocoCustomer->adr2 = Tools::getValue("street_number");
            $colocoCustomer->adr3 = Tools::getValue("street_box");
            $testAddr = $colocoCustomer->adr1 . ', ' . $colocoCustomer->adr2 . ' ' . $colocoCustomer->adr3;
            if (!empty($testAddr) && !Validate::isAddress($testAddr)) {
                Context::getContext()->controller->errors[] = $this->trans('Invalid address');
            }

            $colocoCustomer->cp = Tools::getValue("postcode");
            if (!empty($colocoCustomer->cp) && !Validate::isPostCode($colocoCustomer->cp)) {
                Context::getContext()->controller->errors[] = $this->trans('Invalid postcode');
            }

            $colocoCustomer->ville = Tools::getValue("city");
            if (!empty($colocoCustomer->ville) && !Validate::isCityName($colocoCustomer->ville)) {
                Context::getContext()->controller->errors[] = $this->trans('Invalid city');
            }

            $idCountries = array_map(
                static function ($row) {
                    return (int)$row['id_country'];
                },
                Country::getCountries(Context::getContext()->language->id, true)
            );
            $id_lang_en = (int)Language::getIdByIso('fr');
            $countryName = Country::getNameById($id_lang_en, (int)Tools::getValue("country"));
            $colocoCustomer->pays = $countryName;
            if (!in_array(Tools::getValue("country"), $idCountries)) {
                Context::getContext()->controller->errors[] = $this->trans('Invalid country');
            }

            $colocoCustomer->gsm = Tools::getValue("phone_mobile");
            if (!Validate::isPhoneNumber($colocoCustomer->gsm)) {
                Context::getContext()->controller->errors[] = $this->trans('Invalid phone number');
            }

            $colocoCustomer->nom = Tools::getValue("lastname");
            if (!empty($colocoCustomer->nom) && !Validate::isName($colocoCustomer->nom)) {
                Context::getContext()->controller->errors[] = $this->trans('Invalid lastname');
            }

            $colocoCustomer->prenom = Tools::getValue("firstname");
            if (!empty($colocoCustomer->prenom) && !Validate::isName($colocoCustomer->prenom)) {
                Context::getContext()->controller->errors[] = $this->trans('Invalid firstname');
            }

            // TODO manage optin
            $allOptins = (bool)Tools::getValue("all_optins");

            if (!$colocoCustomer->validateFields(false)) {
                Context::getContext()->controller->errors[] = $this->trans(
                    'An unknown error occurred, please contact an administrator'
                );
            }

            // If no errors, make process
            if (empty(Context::getContext()->controller->errors)) {
                // First, update api
                try {
                    // Fetch api informations
                    $fetchedOrkardCustomer = $this->getColocoApiHandler()->getClientWithAttributes(
                        $colocoCustomer->idcli
                    );

                    // Update object
                    $fetchedOrkardCustomer->setEmail($colocoCustomer->email);
                    $fetchedOrkardCustomer->setDtnai($colocoCustomer->dtnai);
                    $fetchedOrkardCustomer->setIdm((int)$colocoCustomer->idm);
                    $fetchedOrkardCustomer->setAdr1($colocoCustomer->adr1);
                    $fetchedOrkardCustomer->setAdr2($colocoCustomer->adr2);
                    $fetchedOrkardCustomer->setAdr3($colocoCustomer->adr3);
                    $fetchedOrkardCustomer->setAdr4($colocoCustomer->adr4);
                    $fetchedOrkardCustomer->setCp($colocoCustomer->cp);
                    $fetchedOrkardCustomer->setVille($colocoCustomer->ville);
                    $fetchedOrkardCustomer->setGsm($colocoCustomer->gsm);
                    $fetchedOrkardCustomer->setPays($colocoCustomer->pays);
                    $fetchedOrkardCustomer->setNom($colocoCustomer->nom);
                    $fetchedOrkardCustomer->setPrenom($colocoCustomer->prenom);

                    // Send it back
                    $this->getColocoApiHandler()->updateClientWithAttributes($fetchedOrkardCustomer);

                    // There are no errors if we are here, so update colocoCustomer
                    if ($colocoCustomer->save()) {
                        // Try to create/update an address from informations
                        $colocoCustomerHydrationService = new ColocoCustomerHydrationService();

                        // Hydrate PS address
                        $address = $colocoCustomerHydrationService->hydrateAddressFromOrkardCustomer(
                            new Address($colocoCustomer->id_address),
                            $fetchedOrkardCustomer
                        );

                        // Check if address new informations can be stored (technically yes)
                        if ($address->validateFields(false)) {
                            // Assign it to customer and save it
                            $address->id_customer = $colocoCustomer->id_customer;
                            $address->save();

                            $colocoCustomer->id_address = $address->id;

                            // Save colocoCustomer
                            $colocoCustomer->save();
                        }
                    } else {
                        Context::getContext()->controller->errors[] = $this->trans(
                            'An error occurred while saving the informations'
                        );
                        $this->getLogger()->log("ColocoCustomer #$colocoCustomer->id could not be saved.");
                    }
                } catch (CurlErrorException $e) {
                    $this->getLogger()->log($e);
                    Context::getContext()->controller->errors[] = $this->trans(
                        'An error occurred while saving the informations'
                    );
                } catch (OrkardApiErrorException $e) {
                    $this->getLogger()->log($e);
                    Context::getContext()->controller->errors[] = $this->trans(
                        'An error occurred while saving the informations'
                    );
                }
            }
        }

        // Was hook successful
        $this->hookActionCustomerAccountUpdateSuccessful = empty(Context::getContext()->controller->errors);
    }

    /**
     * @return array
     */
    private function getStoreIdList()
    {
        $stores = Store::getStores(Context::getContext()->language->id);

        $storeIdList = array();
        foreach ($stores as $store) {
            if (isset($store['email']) && $store['email'] != "") {
                $explodedEmail = explode('@', $store['email']);

                if ($explodedEmail) {
                    $storeIdList[] = (int)$explodedEmail[0];
                }
            }
        }

        return $storeIdList;
    }

    /**
     * @param $params
     * @throws \PrestaShopException
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        $this->context->controller->registerStylesheet(
            'module-wpkcoloco-default-css',
            'modules/' . $this->name . '/views/css/wpkcoloco.css',
            [
                'media' => 'all',
                'priority' => 200,
            ]
        );


        if (isset($this->context->controller->php_name) && $this->context->controller->php_name == 'tickets') {
            $this->context->controller->registerJavascript(
                'module-wpkcoloco-tickets',
                'modules/' . $this->name . '/views/js/wpk_tickets.js',
                [
                    'media' => 'all',
                    'priority' => 200,
                ]
            );
            $this->context->controller->registerJavascript(
                'module-wpkcoloco-print',
                'modules/' . $this->name . '/views/js/print.js',
                [
                    'media' => 'all',
                    'priority' => 200,
                ]
            );
        }
        if (isset($this->context->controller->php_name) && $this->context->controller->php_name == 'loyalty_card') {
            $this->context->controller->registerJavascript(
                'module-wpkcoloco-loyalty_card',
                'modules/' . $this->name . '/views/js/loyalty_card.js',
                [
                    'media' => 'all',
                    'priority' => 200,
                ]
            );
        }

        if ($this->context->controller->php_self == 'identity') {
            $this->context->controller->registerJavascript(
                'module-wpkcoloco-customer-data',
                'modules/' . $this->name . '/views/js/customer-data.js',
                [
                    'media' => 'all',
                    'priority' => 200,
                ]
            );
        } else {
            $this->context->controller->addJS($this->_path . 'views/js/customer-data.js', 'all');
        }

        $isPostAjaxLoginCallable = $this->isPostLoginAjaxCallable(Context::getContext()->customer);
        if ($isPostAjaxLoginCallable) {
            Media::addJsDef(
                array(
                    "isPostAjaxLoginCallable" => $isPostAjaxLoginCallable,
                    "controllerUrl" => Context::getContext()->link->getModuleLink(
                        'wpkcoloco',
                        'PostAjaxLogin',
                        array(
                            'action' => 'PostLoginAjaxCall',
                            'ajax' => true,
                        )
                    ),
                )
            );

            Context::getContext()->controller->addJS($this->_path . 'views/js/wpk_post_ajax_login.js');
        }
    }

    /**
     * @param Customer $customer
     * @return bool
     * @throws \PrestaShopException
     */
    private function isPostLoginAjaxCallable($customer)
    {
        if (Validate::isLoadedObject($customer) && $customer->isLogged()) {
            $postAjaxLoginApplication = PostAjaxLoginApplication::getByIdCustomer(
                $customer->id
            );

            // If there is an application (it should always be true) and application is not fulfilled yet
            if (Validate::isLoadedObject($postAjaxLoginApplication)
                && !$postAjaxLoginApplication->is_request_fulfilled) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $params
     * @throws \PrestaShopException
     */
    public function hookActionAuthentication($params)
    {
        $customer = $params['customer'];

        // Just to be sure customer is well loaded
        if ($this->postAjaxLoginApplicationAlreadyMade == false && Validate::isLoadedObject($customer)) {
            // Retrieve previous application and use it, this way there can't be any duplicates applications for a single customer, and it preserves database storage
            $postAjaxLoginApplication = PostAjaxLoginApplication::getByIdCustomer($customer->id);

            // If no previous application, create one
            if (!$postAjaxLoginApplication) {
                $postAjaxLoginApplication = new PostAjaxLoginApplication();
                $postAjaxLoginApplication->id_customer = (int)$customer->id;
            }

            $postAjaxLoginApplication->connection_count = ((int)$postAjaxLoginApplication->connection_count) + 1;

            // Used to check on the next page if an ajax call is needed
            $postAjaxLoginApplication->is_request_fulfilled = false;

            // Save application
            $postAjaxLoginApplication->save();

            $this->postAjaxLoginApplicationAlreadyMade = true;
        }
    }

    /**
     * @param array $params
     * @throws \PrestaShopException
     */
    public function hookActionCustomerAccountAdd($params)
    {
        $customer = $params['newCustomer'];

        if (Validate::isLoadedObject($customer)) {
            if(Tools::getValue('coloco')){
                Tools::redirect($this->getConfirmLink($customer, Tools::getValue('cartid')));
            }else {
                $postAjaxLoginApplication = new PostAjaxLoginApplication();
                $postAjaxLoginApplication->id_customer = (int)$customer->id;
                $postAjaxLoginApplication->connection_count = 1;
                $postAjaxLoginApplication->is_request_fulfilled = false;
                $postAjaxLoginApplication->save();
            }

        }
    }


    /**
     * Add blocks on my account page
     *
     * @return false|\HTML|string
     */
    public function hookDisplayCustomerAccount()
    {
        $colocoCustomer = ColocoCustomer::getByIdCustomer($this->context->customer->id);

        $has_card = (bool)Validate::isLoadedObject($colocoCustomer);

        $this->context->smarty->assign(
            array(
                //TODO see what we do with it
               /* 'link_to_pets_controller' => Context::getContext()->link->getModuleLink($this->name, 'Pets'),*/
                'link_to_loyalty_card_controller' => Context::getContext()->link->getModuleLink(
                    $this->name,
                    'LoyaltyCard'
                ),
                'link_to_tickets_controller' => $has_card ? Context::getContext()->link->getModuleLink(
                    $this->name,
                    'Tickets'
                ) : false,
            )
        );

        return $this->display(__FILE__, 'views/templates/hook/my-account.tpl');
    }

    /**
     * @return bool
     * @throws \PrestaShopException
     */
    public function uninstall()
    {
        return $this->uninstallModuleTab($this->getModuleTabs())
            && $this->uninstallSql()
            && parent::uninstall();
    }

    /**
     * @param array $tabs
     * @return bool|mixed
     * @throws \PrestaShopException
     */
    protected function uninstallModuleTab($tabs)
    {
        $res = true;

        foreach ($tabs as $i => $tab) {
            if (isset($tab['children']) && count($tab['children'])) {
                $res &= $this->uninstallModuleTab($tab['children']);
            }

            $tab_id = Tab::getIdFromClassName($tab['class']);
            if ($tab_id) {
                $tab = new Tab($tab_id);
                $tab->active = 0;
                $res &= $tab->save();
            }
        }

        return $res;
    }

    /**
     * @return bool
     */
    private function uninstallSql()
    {
        $res = ColocoCustomer::dropTables();
        $res &= PostAjaxLoginApplication::dropTables();
        $res &= AwaitingVerificationCustomer::dropTables();

        return $res;
    }


    public static function getConfirmLink($customer, $idcli)
    {
        // Customer is in awaiting verification state

        $awaitingVerificationCustomer = AwaitingVerificationCustomer::getAwaitingVerificationCustomerByIdCustomer(
            $customer->id
        );

        if (Validate::isLoadedObject($awaitingVerificationCustomer)) {
            $awaitingVerificationCustomer->delete();
        }

        $awaitingVerificationCustomer = new AwaitingVerificationCustomer();
        $awaitingVerificationCustomer->id_customer = (int)$customer->id;
        $awaitingVerificationCustomer->idcli = $idcli;
        $awaitingVerificationCustomer->request_count = ((int)$awaitingVerificationCustomer->request_count) + 1;

        if ($awaitingVerificationCustomer->save()) {
            $accountVerificationKeyService = new AccountVerificationKeyService();

            return Context::getContext()->link->getModuleLink(
                'wpkcoloco',
                "ColocoAccountVerification",
                array(
                    'key' => $accountVerificationKeyService->generateKey($awaitingVerificationCustomer)
                ));
        }
    }
}