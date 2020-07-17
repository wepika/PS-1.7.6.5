<?php

require_once __DIR__ . '/vendor/autoload.php';

use PrestaShop\Module\FacetedSearch\Filters\Converter;
use PrestaShop\Module\FacetedSearch\URLSerializer;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use Wpk\Classes\WpkProductSearchProvider;

/**
 * Class WpkSearchProvider
 */
class WpkSearchProvider extends Module
{
    /**
     * WpkSearchProvider constructor.
     */
    public function __construct()
    {
        $this->name = 'wpksearchprovider';
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->author = 'Wepika';
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('WpkSearchProvider');
        $this->description = $this->l('Allows to set a custom order in category pages');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->errors = array();
    }

    /**
     * @return bool
     */
    public function install()
    {
        return parent::install()
            && $this->registerHook('productSearchProvider')
            && $this->registerHook('filterProductSearch');
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * @param array $params
     */
    public function hookFilterProductSearch($params)
    {
        // Add custom sort order from this module
        $sortCustomAsc = new SortOrder('product', 'date_add', 'desc');
        $sortCustomAsc->setLabel($this->trans('New', [], 'Modules.Wpksearchprovider.Shop'));

        // Convert object so template can use it
        $order = $sortCustomAsc->toArray();
        $order['current'] = $order['urlParameter'] === (string)Tools::getValue("order");

        $current_url = (Context::getContext()->controller->getTemplateVarUrls())['current_url'];
        $order['url'] = substr($current_url, 0, strpos($current_url, '?order=')) . '?order=' . $order['urlParameter'];

        $params['searchVariables']['sort_orders'][] = $order;
    }

    /**
     * @param array $params
     * @return WpkProductSearchProvider
     */
    public function hookProductSearchProvider($params)
    {
        /* @var ProductSearchQuery */
        $query = $params['query'];

        $module_manager = ModuleManagerBuilder::getInstance()->buildRepository();
        /* @var Ps_Facetedsearch */
        $ps_facetedsearch = $module_manager->getInstanceByName('ps_facetedsearch');

        if ($ps_facetedsearch instanceof Ps_Facetedsearch) {
            if ($this->getCurrentlyUsedSortType() == 'date_add') {
                return new WpkProductSearchProvider(
                    $ps_facetedsearch,
                    new Converter(
                        $ps_facetedsearch->getContext(),
                        $ps_facetedsearch->getDatabase()
                    ),
                    new URLSerializer()
                );
            }
        }

        return null;
    }

    /**
     * @return string
     */
    protected function getCurrentlyUsedSortType()
    {
        $order = explode('.', Tools::getValue("order"));
        return isset($order[1]) ? $order[1] : '';
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return Db
     */
    public function getDatabase()
    {
        return Db::getInstance();
    }
}
