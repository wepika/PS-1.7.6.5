<?php

namespace Wpk\Classes;

use Configuration;
use Module;
use PrestaShop\Module\FacetedSearch\Filters\Block;
use PrestaShop\Module\FacetedSearch\Filters\Converter;
use PrestaShop\Module\FacetedSearch\Product\Search;
use PrestaShop\Module\FacetedSearch\URLSerializer;
use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;
use PrestaShop\PrestaShop\Core\Product\Search\FacetCollection;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchProviderInterface;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchResult;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Core\Product\Search\URLFragmentSerializer;
use PrestaShopDatabaseException;
use Tools;
use Validate;
use WpkSearchProvider;

/**
 * Class WpkProductSearchProvider
 * @package Wpk\Classes
 */
class WpkProductSearchProvider implements ProductSearchProviderInterface
{
    /**
     * @var WpkSearchProvider
     */
    private $module;

    /**
     * @var Converter
     */
    private $filtersConverter;

    /**
     * @var URLSerializer
     */
    private $facetsSerializer;

    /**
     * WpkProductSearchProvider constructor.
     * @param Module $module
     * @param Converter $converter
     * @param URLSerializer $urlSerialiser
     */
    public function __construct($module, $converter, $urlSerialiser)
    {
        $this->module = $module;
        $this->filtersConverter = $converter;
        $this->facetsSerializer = $urlSerialiser;
    }

    /**
     * @param ProductSearchContext $context
     * @param ProductSearchQuery $query
     * @return ProductSearchResult
     * @throws PrestaShopDatabaseException
     * @throws LocalizationException
     */
    public function runQuery(ProductSearchContext $context, ProductSearchQuery $query)
    {
        $result = new ProductSearchResult();

        // Stuffs to make facets work
        // extract the filter array from the Search query
        $facetedSearchFilters = $this->filtersConverter->createFacetedSearchFiltersFromQuery($query);

        $context = $this->module->getContext();
        $facetedSearch = new Search($context);
        // init the search with the initial population associated with the current filters
        $facetedSearch->initSearch($facetedSearchFilters);
        // End stuffs to make facets work

        // Query products part, use the searchAdapter to build query
        $orderBy = $query->getSortOrder()->toLegacyOrderBy(false);
        $orderWay = $query->getSortOrder()->toLegacyOrderWay();
        $orderWay = Validate::isOrderWay($orderWay) ? $orderWay : 'ASC';
        $orderBy = Validate::isOrderBy($orderBy) ? $orderBy : 'position';

        $searchAdapter = $facetedSearch->getSearchAdapter();

        $searchAdapter->setLimit((int)$query->getResultsPerPage(), ((int)$query->getPage() - 1) * $query->getResultsPerPage());
        $searchAdapter->setOrderField($orderBy);
        $searchAdapter->setOrderDirection($orderWay);

        $searchAdapter->addGroupBy('id_product');
        if (isset($selectedFilters['price']) || $orderBy === 'price') {
            $searchAdapter->addSelectField('id_product');
            $searchAdapter->addSelectField('price');
            $searchAdapter->addSelectField('price_min');
            $searchAdapter->addSelectField('price_max');
        }

        $matchingProductList = $searchAdapter->execute();

        $nbrProducts = $searchAdapter->count();

        if (empty($nbrProducts)) {
            $matchingProductList = [];
        }
        // End query products part

        // Sort order part
        $sortByDateAdd = (new SortOrder('product', 'date_add', 'desc'))->setLabel(
            $this->module->getTranslator()->trans('Custom', array(), 'Shop.Theme.Catalog')
        );

        if (!Tools::getValue('order', 0)) {
            $query->setSortOrder($sortByDateAdd);
        }
        // End sort order part

        $bso = $this->getBasicSortOrders();
        $result
            ->setProducts($matchingProductList)
            ->setTotalProductsCount($nbrProducts)
            ->setAvailableSortOrders(array_merge($bso, array($sortByDateAdd)));

        // Now that the query is done, rebuild filters
        // now get the filter blocks associated with the current search
        $filterBlockSearch = new Block(
            $facetedSearch->getSearchAdapter(),
            $context,
            $this->module->getDatabase()
        );
        // End now that the query is done, rebuild filters

        $filterHash = md5(
            sprintf(
                '%d-%d-%d-%d-%d-%s',
                (int)$context->shop->id,
                (int)$context->language->id,
                (int)$context->currency->id,
                (int)$context->country->id,
                (int)$query->getIdCategory(),
                serialize($facetedSearchFilters)
            )
        );

        $filterBlock = $filterBlockSearch->getFromCache($filterHash);
        if (empty($filterBlock)) {
            $filterBlock = $filterBlockSearch->getFilterBlock($nbrProducts, $facetedSearchFilters);
            $filterBlockSearch->insertIntoCache($filterHash, $filterBlock);
        }

        $facets = $this->filtersConverter->getFacetsFromFilterBlocks(
            $filterBlock['filters']
        );

        $this->labelRangeFilters($facets);
        $this->addEncodedFacetsToFilters($facets);
        $this->hideUselessFacets($facets, (int)$result->getTotalProductsCount());

        $facetCollection = new FacetCollection();
        $nextMenu = $facetCollection->setFacets($facets);
        $result->setFacetCollection($nextMenu);
        $result->setEncodedFacets($this->facetsSerializer->serialize($facets));

        return $result;
    }

//    /**
//     * @param ProductSearchContext $context
//     * @param ProductSearchQuery $query
//     *
//     * @return ProductSearchResult
//     */
//    public function runQuery(
//        ProductSearchContext $context,
//        ProductSearchQuery $query
//    ) {
//        $result = new ProductSearchResult();
//        // extract the filter array from the Search query
//        $facetedSearchFilters = $this->filtersConverter->createFacetedSearchFiltersFromQuery($query);
//
//        $context = $this->module->getContext();
//        $facetedSearch = new Search($context);
//        // init the search with the initial population associated with the current filters
//        $facetedSearch->initSearch($facetedSearchFilters);
//
//        $orderBy = $query->getSortOrder()->toLegacyOrderBy(false);
//        $orderWay = $query->getSortOrder()->toLegacyOrderWay();
//
//        $filterProductSearch = new Products($facetedSearch);
//
//        // get the product associated with the current filter
//        $productsAndCount = $filterProductSearch->getProductByFilters(
//            $query->getResultsPerPage(),
//            $query->getPage(),
//            $orderBy,
//            $orderWay,
//            $facetedSearchFilters
//        );
//
//        $sortByDateAdd = (new SortOrder('product', 'date_add', 'desc'))->setLabel(
//            $this->module->getTranslator()->trans('Custom', array(), 'Shop.Theme.Catalog')
//        );
//
//        $result
//            ->setProducts($productsAndCount['products'])
//            ->setTotalProductsCount($productsAndCount['count'])
//            ->setAvailableSortOrders(array_merge($this->getBasicSortOrders(), $sortByDateAdd));
//
//        // now get the filter blocks associated with the current search
//        $filterBlockSearch = new Block(
//            $facetedSearch->getSearchAdapter(),
//            $context,
//            $this->module->getDatabase()
//        );
//
//        $idShop = (int) $context->shop->id;
//        $idLang = (int) $context->language->id;
//        $idCurrency = (int) $context->currency->id;
//        $idCountry = (int) $context->country->id;
//        $idCategory = (int) $query->getIdCategory();
//
//        $filterHash = md5(
//            sprintf(
//                '%d-%d-%d-%d-%d-%s',
//                $idShop,
//                $idCurrency,
//                $idLang,
//                $idCategory,
//                $idCountry,
//                serialize($facetedSearchFilters)
//            )
//        );
//
//        $filterBlock = $filterBlockSearch->getFromCache($filterHash);
//        if (empty($filterBlock)) {
//            $filterBlock = $filterBlockSearch->getFilterBlock($productsAndCount['count'], $facetedSearchFilters);
//            $filterBlockSearch->insertIntoCache($filterHash, $filterBlock);
//        }
//
//        $facets = $this->filtersConverter->getFacetsFromFilterBlocks(
//            $filterBlock['filters']
//        );
//
//        $this->labelRangeFilters($facets);
//        $this->addEncodedFacetsToFilters($facets);
//        $this->hideUselessFacets($facets, (int) $result->getTotalProductsCount());
//
//        $facetCollection = new FacetCollection();
//        $nextMenu = $facetCollection->setFacets($facets);
//        $result->setFacetCollection($nextMenu);
//        $result->setEncodedFacets($this->facetsSerializer->serialize($facets));
//
//        return $result;
//    }

    /**
     * @return array
     */
    private function getBasicSortOrders()
    {
        /// Basic sorts
        $sortPosAsc = new SortOrder('product', 'position', 'asc');
        $sortNameAsc = new SortOrder('product', 'name', 'asc');
        $sortNameDesc = new SortOrder('product', 'name', 'desc');
        $sortPriceAsc = new SortOrder('product', 'price', 'asc');
        $sortPriceDesc = new SortOrder('product', 'price', 'desc');

        $translator = $this->module->getTranslator();

        return [
            $sortPosAsc->setLabel(
                $translator->trans('Relevance', [], 'Modules.Facetedsearch.Shop')
            ),
            $sortNameAsc->setLabel(
                $translator->trans('Name, A to Z', [], 'Shop.Theme.Catalog')
            ),
            $sortNameDesc->setLabel(
                $translator->trans('Name, Z to A', [], 'Shop.Theme.Catalog')
            ),
            $sortPriceAsc->setLabel(
                $translator->trans('Price, low to high', [], 'Shop.Theme.Catalog')
            ),
            $sortPriceDesc->setLabel(
                $translator->trans('Price, high to low', [], 'Shop.Theme.Catalog')
            ),
        ];
    }

    /**
     * Add a label associated with the facets
     *
     * @param array $facets
     * @throws LocalizationException
     */
    private function labelRangeFilters(array $facets)
    {
        foreach ($facets as $facet) {
            if (!in_array($facet->getType(), Converter::RANGE_FILTERS)) {
                continue;
            }

            foreach ($facet->getFilters() as $filter) {
                $filterValue = $filter->getValue();
                $min = empty($filterValue[0]) ? $facet->getProperty('min') : $filterValue[0];
                $max = empty($filterValue[1]) ? $facet->getProperty('max') : $filterValue[1];
                if ($facet->getType() === 'weight') {
                    $unit = Configuration::get('PS_WEIGHT_UNIT');
                    $filter->setLabel(
                        sprintf(
                            '%1$s%2$s - %3$s%4$s',
                            Tools::displayNumber($min),
                            $unit,
                            Tools::displayNumber($max),
                            $unit
                        )
                    );
                } elseif ($facet->getType() === 'price') {
                    $filter->setLabel(
                        sprintf(
                            '%1$s - %2$s',
                            Tools::displayPrice($min),
                            Tools::displayPrice($max)
                        )
                    );
                }
            }
        }
    }

    /**
     * This method generates a URL stub for each filter inside the given facets
     * and assigns this stub to the filters.
     * The URL stub is called 'nextEncodedFacets' because it is used
     * to generate the URL of the search once a filter is activated.
     * @param array $facets
     */
    private function addEncodedFacetsToFilters(array $facets)
    {
        // first get the currently active facetFilter in an array
        $originalFacetFilters = $this->facetsSerializer->getActiveFacetFiltersFromFacets($facets);
        $urlSerializer = new URLFragmentSerializer();

        foreach ($facets as $facet) {
            $activeFacetFilters = $originalFacetFilters;
            // If only one filter can be selected, we keep track of
            // the current active filter to disable it before generating the url stub
            // and not select two filters in a facet that can have only one active filter.
            if (!$facet->isMultipleSelectionAllowed() && !$facet->getProperty('range')) {
                foreach ($facet->getFilters() as $filter) {
                    if ($filter->isActive()) {
                        // we have a currently active filter is the facet, remove it from the facetFilter array
                        $activeFacetFilters = $this->facetsSerializer->removeFilterFromFacetFilters(
                            $originalFacetFilters,
                            $filter,
                            $facet
                        );
                        break;
                    }
                }
            }

            foreach ($facet->getFilters() as $filter) {
                // toggle the current filter
                if ($filter->isActive() || $facet->getProperty('range')) {
                    $facetFilters = $this->facetsSerializer->removeFilterFromFacetFilters(
                        $activeFacetFilters,
                        $filter,
                        $facet
                    );
                } else {
                    $facetFilters = $this->facetsSerializer->addFilterToFacetFilters(
                        $activeFacetFilters,
                        $filter,
                        $facet
                    );
                }

                // We've toggled the filter, so the call to serialize
                // returns the "URL" for the search when user has toggled
                // the filter.
                $filter->setNextEncodedFacets(
                    $urlSerializer->serialize($facetFilters)
                );
            }
        }
    }

    /**
     * Remove the facet when there's only 1 result.
     * Keep facet status when it's a slider
     *
     * @param array $facets
     * @param int $totalProducts
     */
    private function hideUselessFacets(array $facets, $totalProducts)
    {
        foreach ($facets as $facet) {
            if ($facet->getWidgetType() === 'slider') {
                $facet->setDisplayed(
                    $facet->getProperty('min') != $facet->getProperty('max')
                );
                continue;
            }

            $totalFacetProducts = 0;
            $usefulFiltersCount = 0;
            foreach ($facet->getFilters() as $filter) {
                if ($filter->getMagnitude() > 0 && $filter->isDisplayed()) {
                    $totalFacetProducts += $filter->getMagnitude();
                    ++$usefulFiltersCount;
                }
            }

            $facet->setDisplayed(
            // There are two filters displayed
                $usefulFiltersCount > 1
                ||
                /*
                 * There is only one fitler and the
                 * magnitude is different than the
                 * total products
                 */
                (
                    count($facet->getFilters()) === 1
                    && $totalFacetProducts < $totalProducts
                    && $usefulFiltersCount > 0
                )
            );
        }
    }
}