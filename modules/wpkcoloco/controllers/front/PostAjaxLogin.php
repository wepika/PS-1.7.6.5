<?php

use WpkColoco\Model\AwaitingVerificationCustomer;
use WpkColoco\Model\ColocoCustomer;
use WpkColoco\Model\PostAjaxLoginApplication;
use WpkColoco\Service\AccountVerificationKeyService;
use WpkColoco\Service\ColocoCustomer\ColocoCustomerHydrationService;
use WpkColoco\Wepika\Curl\Exception\CurlErrorException;
use WpkColoco\Wepika\OrkardApi\Exception\OrkardApiErrorException;

/**
 * Class WpkColocoPostAjaxLoginModuleFrontController
 */
class WpkColocoPostAjaxLoginModuleFrontController extends ModuleFrontController
{
    /**
     * WpkColocoPostAjaxLoginModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // This controller is only used through ajax
        if (!$this->ajax) {
            Tools::redirect('index');
        }
    }

    /**
     * @throws \PrestaShopException
     */
    public function displayAjaxPostLoginAjaxCall()
    {
        // If customer is logged
        if (Validate::isLoadedObject(Context::getContext()->customer) && Context::getContext()->customer->isLogged()) {
            // Get customer login application
            $postAjaxLoginApplication = PostAjaxLoginApplication::getByIdCustomer(
                Context::getContext()->customer->id
            );

            // If there is an application and application is not fulfilled yet
            if (Validate::isLoadedObject($postAjaxLoginApplication)
                && !$postAjaxLoginApplication->is_request_fulfilled) {
                /* @var \WpkColoco $module */
                $module = $this->module;

                // Get colocoCustomer (registered in PS)
                $colocoCustomer = ColocoCustomer::getByIdCustomer(Context::getContext()->customer->id);

                // If customer is already registered in PS
                if (Validate::isLoadedObject($colocoCustomer) && $colocoCustomer->idcli) {
                    try {
                        // Get his coloco informations by stored id
                        $orkardCustomer = $module->getColocoApiHandler()->getClientWithAttributes(
                            $colocoCustomer->idcli
                        );

                        // Refresh colocoCustomer object with api informations
                        $colocoCustomerHydrationService = new ColocoCustomerHydrationService();
                        $colocoCustomerHydrationService->hydrateFromOrkardCustomer(
                            $colocoCustomer,
                            $orkardCustomer
                        );

                        // If customer does not have a coloco address yet, create a new instance
                        if (!Validate::isLoadedObject($address = new Address($colocoCustomer->id_address))) {
                            $address = new Address();
                        }

                        // Hydrate address with api informations
                        $colocoCustomerHydrationService->hydrateAddressFromOrkardCustomer(
                            $address,
                            $orkardCustomer
                        );

                        // If address can be created, bind it to colocoCustomer
                        if ($address->validateFields(false) && $address->save()) {
                            $colocoCustomer->id_address = $address->id;
                        }

                        // Update PS informations
                        $colocoCustomer->save();

                        // The ajax request is now fulfilled
                        $postAjaxLoginApplication->is_request_fulfilled = true;
                        $postAjaxLoginApplication->save();

                        // Process should end here
                        die();
                    } catch (CurlErrorException $e) {
                        $module->getLogger()->log($e);
                    } catch (OrkardApiErrorException $e) {
                        $module->getLogger()->log($e);
                    }
                } else {
                    // If this is the first connection
                    if ($postAjaxLoginApplication->connection_count == 1) {
                        try {
                            // Get Coloco client by email (Api call)
                            $orkardCustomer = $module->getColocoApiHandler()->getClientByEmailWithAttributes(
                                Context::getContext()->customer->email
                            );

                            // Sometimes the api call is taking too long and the user not even on the page that ran this process anymore
                            // So check if connection was aborted or not during the api call (because this the process that takes the most time)
                            // This will prevent postAjaxLogin request completion, email sending, etc

                            // See https://stackoverflow.com/a/12046054

                            // Ca ne marche pas :/ + le <br> vient polluer la réponse ajax

//                            echo "<br>";
//                            flush();
//                            ob_flush();
//
//                            if (connection_aborted()) {
//                                die();
//                            }

                            // If we got a card id
                            if ($orkardCustomer->getIdcli()) {
                                // Customer is in awaiting verification state
                                $awaitingVerificationCustomer = AwaitingVerificationCustomer::getAwaitingVerificationCustomerByIdCustomer(
                                    Context::getContext()->customer->id
                                );
                                if ($awaitingVerificationCustomer == null) {
                                    $awaitingVerificationCustomer = new AwaitingVerificationCustomer();
                                    $awaitingVerificationCustomer->id_customer = (int)Context::getContext()->customer->id;
                                }
                                $awaitingVerificationCustomer->request_count = ((int)$awaitingVerificationCustomer->request_count) + 1;

                                if ($awaitingVerificationCustomer->save()) {

                                    if (!PostAjaxLoginApplication::isRequestFullfiled($postAjaxLoginApplication->id)) {
                                        // Send email
                                        $isMailSent = self::sendVerificationEmail($awaitingVerificationCustomer, $module);
                                        if (!$isMailSent) {
                                            $module->getLogger()->log(
                                                "Email de vérification n'a pas pû être envoyé au client #$awaitingVerificationCustomer->id_customer"
                                            );
                                        }

                                        // The ajax request is now fulfilled
                                        $postAjaxLoginApplication->is_request_fulfilled = true;
                                        $postAjaxLoginApplication->save();
                                    }

                                    // Email is sent, inform customer that an email has been sent
                                    // show popup that says an email has been sent
                                    die(
                                    json_encode(
                                        array(
                                            'action' => 'awaiting_email',
                                        )
                                    )
                                    );
                                } else {
                                    $module->getLogger()->log(
                                        "Client #$awaitingVerificationCustomer->id_customer n'a pas pu être mis en attente de validation."
                                    );
                                }
                            }
                        } catch (CurlErrorException $e) {
                            $module->getLogger()->log($e);
                        } catch (OrkardApiErrorException $e) {
                            $module->getLogger()->log($e);
                        } catch (Exception $e) {
                            $module->getLogger()->log($e);
                        }
                    } else {
                        $awaitingVerificationCustomer = AwaitingVerificationCustomer::getAwaitingVerificationCustomerByIdCustomer(
                            $postAjaxLoginApplication->id_customer
                        );
                        if (Validate::isLoadedObject($awaitingVerificationCustomer)) {
                            // The ajax request is now fulfilled
                            $postAjaxLoginApplication->is_request_fulfilled = true;
                            $postAjaxLoginApplication->save();

                            // show popup that says an email has been sent
                            die(
                            json_encode(
                                array(
                                    'action' => 'awaiting_email',
                                )
                            )
                            );
                        }
                    }
                }

                // The ajax request is now fulfilled
                $postAjaxLoginApplication->is_request_fulfilled = true;
                $postAjaxLoginApplication->save();

                // show popup with a link that redirects to bind a card page
                die(
                json_encode(
                    array(
                        'action' => 'bind_card',
                    )
                )
                );
            }
        }
    }

    /**
     * @param AwaitingVerificationCustomer $awaitingVerificationCustomer
     * @param WpkColoco $module
     * @return bool
     */
    public static function sendVerificationEmail($awaitingVerificationCustomer, $module)
    {
        if (Validate::isLoadedObject($awaitingVerificationCustomer)) {
            $customer = new Customer((int)$awaitingVerificationCustomer->id_customer);

            if (Validate::isLoadedObject($customer)) {
                $accountVerificationKeyService = new AccountVerificationKeyService();
                $iso = Context::getContext()->language->iso_code;
                $language_email = new Language(Context::getContext()->language->id);
                $id_shop = Context::getContext()->shop->id;

                $wte_header_img = 'https://estore.tomandco.com/img/mails/'.strtoupper($language_email->iso_code).'/mail-header.jpg';
                $wte_footer_img = 'https://estore.tomandco.com/img/mails/'.strtoupper($language_email->iso_code).'/mail-footer.jpg';
                

                $vars = array(
                    '{verification_link}' => Context::getContext()->link->getModuleLink(
                        $module->name,
                        "ColocoAccountVerification",
                        array(
                            'key' => $accountVerificationKeyService->generateKey($awaitingVerificationCustomer)
                        )
                    ),
                    '{firstname}' => $customer->firstname,
                    '{lastname}' => $customer->lastname,
                    '{shop_name}' => Context::getContext()->context->shop->name,
                    '{shop_url}' => Context::getContext()->link->getPageLink('index', true),
                    'wte_base_url' => Tools::getShopDomainSsl(),
                    'wte_img_dir' => Tools::getProtocol(true) . Tools::getShopDomainSsl() . _THEME_IMG_DIR_,
                    'wte_subject' => isset($params['subject']) ? $params['subject'] : false,
                    'wte_lang_iso' => strtolower($iso),
                    'wte_header_img' => $wte_header_img,
                    'wte_footer_img' => $wte_footer_img,
            );


                $module->getLogger()->log($vars['{verification_link}']);

                return (bool)Mail::send(
                    (int)Context::getContext()->language->id,
                    'verification_email',
                    Context::getContext()->getTranslator()->trans('Account verification', array(), 'Modules.Wpkcoloco.Postajaxlogin'),
                    $vars,
                    $customer->email,
                    $customer->firstname . " " . $customer->lastname,
                    null,
                    null,
                    null,
                    null,
                    _PS_MODULE_DIR_ . $module->name . '/mails/'
                );
            }
        }

        return false;
    }
}