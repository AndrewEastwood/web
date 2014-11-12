<?php
namespace web\plugin\shop\api;

use \engine\objects\plugin as basePlugin;
use \engine\lib\validate as Validate;
use \engine\lib\secure as Secure;
use \engine\lib\path as Path;
use \engine\lib\request as Request;
use \engine\lib\utils as Utils;
use Exception;
use ArrayObject;

class catalog extends \engine\objects\api {

    private function getCategoriesFromCategoryTree ($categoryTree, $selectedCategoryID, &$list = array(), $inSelectedNode = false) {
        foreach ($categoryTree as $key => $node) {
            $this->getCategoriesFromCategoryTree($node['childNodes'], $selectedCategoryID, $list, $key === $selectedCategoryID);
            if ($inSelectedNode || $key === $selectedCategoryID) {
                $list[] = $node;
            }
        }
        return $list;
    }

    public function getCatalogBrowse () {

        $data = array();
        $categoryID = Request::fromGET('id', null);

        if (!is_numeric($categoryID)) {
            $data['error'] = '"id" parameter is missed';
            return $data;
        }

        $categoryID = intval($categoryID);

        $filterOptions = array(
            /* common options */
            "filter_viewSortBy" => null,
            "filter_viewItemsOnPage" => 16,
            "filter_viewPageNum" => 1,
            "filter_commonPriceMax" => null,
            "filter_commonPriceMin" => 0,
            "filter_commonStatus" => array(),
            "filter_commonFeatures" => array(),

            /* category based */
            "filter_categoryBrands" => array(),
            "filter_categorySubCategories" => array()
        );

        // filtering
        $filterOptionsApplied = new ArrayObject($filterOptions);
        $filterOptionsAvailable = new ArrayObject($filterOptions);

        // get all product available statuses
        $filterOptionsAvailable['filter_commonStatus'] = $this->getAPI()->products->getProductStatuses();

        // init filter
        foreach ($filterOptionsApplied as $key => $value) {
            $filterOptionsApplied[$key] = Request::fromGET($key, $filterOptions[$key]);
            if ($key == "filter_viewItemsOnPage" || $key == "filter_viewPageNum")
                $filterOptionsApplied[$key] = intval($filterOptionsApplied[$key]);
            if ($key === "filter_commonPriceMax" || $key == "filter_commonPriceMin")
                $filterOptionsApplied[$key] = floatval($filterOptionsApplied[$key]);
            if (is_string($filterOptionsApplied[$key])) {
                if ($key == "filter_commonStatus")
                    $filterOptionsApplied[$key] = explode(',', $filterOptionsApplied[$key]);
                if ($key == "filter_categorySubCategories" || $key == "filter_commonFeatures" || $key == "filter_categoryBrands") {
                    $IDs = explode(',', $filterOptionsApplied[$key]);
                    $filterOptionsApplied[$key] = array();
                    foreach ($IDs as $filterOptionID) {
                        $filterOptionsApplied[$key][] = intval($filterOptionID);
                    }
                }
            }
            // var_dump($filterOptionsApplied[$key]);
        }
        // var_dump($filterOptionsApplied);

        $filterOptionsApplied["id"] = $categoryID;
        $filterOptionsAvailable["id"] = $categoryID;

        // var_dump($filterOptionsApplied['filter_commonFeatures']);


        $activeTree = $this->getAPI()->categories->getCatalogTree();
        $cetegories = $this->getCategoriesFromCategoryTree($activeTree, $categoryID);
        $cetegoriesIDs = array();
        $cetegoriesNodes = array();

        // var_dump($cetegories);

        foreach ($cetegories as $categoryItem) {
            $cetegoriesIDs[] = intval($categoryItem['ID']);
            $cetegoriesNodes[$categoryItem['ID']] = array(
                'ID' => intval($categoryItem['ID']),
                'Name' => $categoryItem['Name']
            );
        }

        // var_dump($activeTree);
        $dataConfigCategoryPriceEdges = $this->getPluginConfiguration()->data->jsapiGetShopCatalogPriceEdges(implode(',', $cetegoriesIDs));
        $dataCategoryPriceEdges = $this->getCustomer()->fetch($dataConfigCategoryPriceEdges);
        // var_dump($dataConfigCategoryPriceEdges);

        // get all brands for both current category and sub-categories
        $dataConfigCategoryAllBrands = $this->getPluginConfiguration()->data->jsapiShopCatalogBrands(implode(',', $cetegoriesIDs));
        $dataCategoryAllBrands = $this->getCustomer()->fetch($dataConfigCategoryAllBrands);
        // var_dump($dataCategoryPriceEdges);
        // var_dump($dataCategoryAllBrands);

        // var_dump($this->getCustomerDataBase()->get_last_query());
        // return;

        foreach ($dataCategoryAllBrands as $key => $brandItem) {
            $dataCategoryAllBrands[$key]['ID'] = intval($brandItem['ID']);
        }


        // $dataConfigCategoryAllSubCategories = $this->getPluginConfiguration()->data->jsapiShopCategoryAllSubCategoriesGet($categoryID);

        // $dataConfigCategoryAllSubCategories = array();
        // get category sub-categories and origins
        // $dataCategoryAllSubCategories = $this->getCustomer()->fetch($dataConfigCategoryAllSubCategories);

        // var_dump($dataCategoryAllSubCategories);

        // $cetagorySubIDs = array($categoryID);
        // if (!empty($dataCategoryAllSubCategories))
        //     foreach ($dataCategoryAllSubCategories as $value)
        //         $cetagorySubIDs[] = $value['ID'];

        //filter: get category price edges
        $filterOptionsAvailable['filter_commonPriceMax'] = floatval($dataCategoryPriceEdges['PriceMax'] ?: 0) + 10;
        $filterOptionsAvailable['filter_commonPriceMin'] = floatval($dataCategoryPriceEdges['PriceMin'] ?: 0) - 10;
        if ($filterOptionsAvailable['filter_commonPriceMin'] < 0) {
            $filterOptionsAvailable['filter_commonPriceMin'] = 0;
        }


        // set categories and brands
        $filterOptionsAvailable['filter_categoryBrands'] = $dataCategoryAllBrands ?: array();
        $filterOptionsAvailable['filter_categorySubCategories'] = $cetegoriesNodes ?: array();

        // set data source
        // ---
        $dataConfigProducts = $this->getPluginConfiguration()->data->jsapiGetShopCatalogProductList($cetegoriesIDs);

        // filter: display intems count
        if (!empty($filterOptionsApplied['filter_viewItemsOnPage']))
            $dataConfigProducts['limit'] = $filterOptionsApplied['filter_viewItemsOnPage'];
        else
            $filterOptionsApplied['filter_viewItemsOnPage'] = $dataConfigProducts['limit'];

        if (!empty($filterOptionsApplied['filter_viewPageNum']))
            $dataConfigProducts['offset'] = ($filterOptionsApplied['filter_viewPageNum'] - 1) * $dataConfigProducts['limit'];
        else
            $filterOptionsApplied['filter_viewPageNum'] = $filterOptionsAvailable['filter_viewPageNum'];

        // filter: items sorting
        $_filterSorting = explode('_', strtolower($filterOptionsApplied['filter_viewSortBy']));
        if (count($_filterSorting) === 2 && !empty($_filterSorting[0]) && ($_filterSorting[1] === 'asc' || $_filterSorting[1] === 'desc'))
            $dataConfigProducts['order'] = array('field' => $dataConfigProducts['source'] . '.' . ucfirst($_filterSorting[0]), 'ordering' => strtoupper($_filterSorting[1]));
        else
            $filterOptionsApplied['filter_viewSortBy'] = null;

        // filter: price 
        if ($filterOptionsApplied['filter_commonPriceMax'] > $filterOptionsApplied['filter_commonPriceMin'] && $filterOptionsApplied['filter_commonPriceMax'] <= $filterOptionsAvailable['filter_commonPriceMax'])
            $dataConfigProducts['condition']['Price'][] = $this->getPluginConfiguration()->data->jsapiCreateDataSourceCondition($filterOptionsApplied['filter_commonPriceMax'], '<=');
        else
            $filterOptionsApplied['filter_commonPriceMax'] = $filterOptionsAvailable['filter_commonPriceMax'];

        if ($filterOptionsApplied['filter_commonPriceMax'] > $filterOptionsApplied['filter_commonPriceMin'] && $filterOptionsApplied['filter_commonPriceMin'] >= $filterOptionsAvailable['filter_commonPriceMin'])
            $dataConfigProducts['condition']['Price'][] = $this->getPluginConfiguration()->data->jsapiCreateDataSourceCondition($filterOptionsApplied['filter_commonPriceMin'], '>=');
        else
            $filterOptionsApplied['filter_commonPriceMin'] = $filterOptionsAvailable['filter_commonPriceMin'];

        // var_dump($filterOptionsApplied);
        if (count($filterOptionsApplied['filter_commonFeatures']))
            $dataConfigProducts['condition']["FeatureID"] = $this->getPluginConfiguration()->data->jsapiCreateDataSourceCondition($filterOptionsApplied['filter_commonFeatures'], 'in');

        if (count($filterOptionsApplied['filter_commonStatus']))
            $dataConfigProducts['condition']["shop_products.Status"] = $this->getPluginConfiguration()->data->jsapiCreateDataSourceCondition($filterOptionsApplied['filter_commonStatus'], 'in');

        // filter: brands
        if (count($filterOptionsApplied['filter_categoryBrands']))
            $dataConfigProducts['condition']['OriginID'] = $this->getPluginConfiguration()->data->jsapiCreateDataSourceCondition($filterOptionsApplied['filter_categoryBrands'], 'in');

        // var_dump($dataConfigProducts);
        // get products
        $dataProducts = $this->getCustomer()->fetch($dataConfigProducts);

        // get total count for current filter
        $dataConfigAllMatchedProducts = $this->getPluginConfiguration()->data->jsapiGetShopCategoryProductInfo();
        $dataConfigAllMatchedProducts['condition'] = new ArrayObject($dataConfigProducts['condition']);
        $dataProductsMatches = $this->getCustomer()->fetch($dataConfigAllMatchedProducts);
        $currentProductCount = 0;
        $currentProductsIDs = array();

        foreach ($dataProductsMatches as $key => $value) {
            $currentProductsIDs[] = intval($value['ID']);
            // $dataProductsTotalCount['ItemsCount']);
        }
        $currentProductsIDs = array_unique($currentProductsIDs);
        $currentProductCount = count($currentProductsIDs);

        // var_dump($dataProductsTotalCount);
        // get category info according to product filter
        // if (isset($dataConfigProducts['condition']['Price']))
        //     $dataConfigCategoryInfo['condition']['Price'] = $dataConfigProducts['condition']['Price'];
        // // $dataConfigCategoryInfo['condition'] = new ArrayObject($dataConfigProducts['condition']);
        // $dataCategoryInfo = $this->getCustomer()->fetch($dataConfigCategoryInfo);

        // TODO smth with this
        $products = array();
        if (!empty($dataProducts))
            foreach ($dataProducts as $val)
                $products[] = $this->getAPI()->products->getProductByID($val['ID'], false, false);


        // var_dump($currentProductCount);

        // $productsInfo = array();
        // if (!empty($dataCategoryInfo))
        //     foreach ($dataCategoryInfo as $val)
        //         $productsInfo[] = $this->getAPI()->products->getProductByID($val['ID'], false, false);

        // adjust brands, categories and features
        $brands = array();
        $categories = array();
        $statuses = array();//$this->getCustomerDataBase()->getTableStatusFieldOptions($this->getPluginConfiguration()->data->Table_ShopProducts);
        $features = array();
        // var_dump($filterOptionsApplied['filter_categoryBrands']);
        foreach ($filterOptionsAvailable['filter_categoryBrands'] as $brand) {
            $count = 0;
            $dataConfigCategoryInfo = $this->getPluginConfiguration()->data->jsapiGetShopCategoryProductInfo();
            $dataConfigCategoryInfo['condition'] = new ArrayObject($dataConfigProducts['condition']);
            $arrValues = array($brand['ID']);
            if (!empty($filterOptionsApplied['filter_categoryBrands'])) {
                $arrValues = array_merge($filterOptionsApplied['filter_categoryBrands'], $arrValues);
            }
            $arrValues = array_unique($arrValues);
            // var_dump($arrValues);
            $dataConfigCategoryInfo['condition']['OriginID'] = $this->getPluginConfiguration()->data->jsapiCreateDataSourceCondition($arrValues, 'IN');
            $filterData = $this->getCustomer()->fetch($dataConfigCategoryInfo);
            // if ($brand['Name'] === 'SONY') {
                // var_dump($brand);
                // var_dump($filterData);
                // var_dump($dataConfigCategoryInfo);
                // var_dump($this->getCustomerDataBase()->get_last_query());
                // echo PHP_EOL;
                // echo PHP_EOL;
            // }
            if (isset($filterData) && isset($filterData['ItemsCount'])) {
                $count = $filterData['ItemsCount'];
            }
            $brands[$brand['ID']] = $brand;
            $brands[$brand['ID']]['ProductCount'] = intval($count);
            $brands[$brand['ID']]['Active'] = false;
            if (!empty($filterOptionsApplied['filter_categoryBrands'])) {
                $brands[$brand['ID']]['ProductCount'] -= $currentProductCount;
                $brands[$brand['ID']]['Active'] = true;
            }
        }
        foreach ($filterOptionsAvailable['filter_categorySubCategories'] as $category) {
            $count = 0;
            $dataConfigCategoryInfo = $this->getPluginConfiguration()->data->jsapiGetShopCategoryProductInfo();
            $dataConfigCategoryInfo['condition'] = new ArrayObject($dataConfigProducts['condition']);
            $arrValues = array($category['ID']);
            if (!empty($filterOptionsApplied['filter_categorySubCategories'])) {
                $arrValues = array_merge($filterOptionsApplied['filter_categorySubCategories'], $arrValues);
            }
            $arrValues = array_unique($arrValues);
            // var_dump(">>> values >>>>>>");
            // var_dump($arrValues);
            $dataConfigCategoryInfo['condition']['CategoryID'] = $this->getPluginConfiguration()->data->jsapiCreateDataSourceCondition($arrValues, 'IN');
            $filterData = $this->getCustomer()->fetch($dataConfigCategoryInfo);
            // var_dump(">>>>results>>>>>>>");
            // var_dump($filterData);
            // var_dump("-=-=-=-=-=-=-=-=-=-=-=-=-");
            if (isset($filterData) && isset($filterData['ItemsCount'])) {
                $count = $filterData['ItemsCount'];
            }
            $categories[$category['ID']] = $category;
            $categories[$category['ID']]['ProductCount'] = intval($count);
            $categories[$category['ID']]['Active'] = false;
            if (!empty($filterOptionsApplied['filter_categorySubCategories'])) {
                $categories[$category['ID']]['ProductCount'] -= $currentProductCount;
                $categories[$category['ID']]['Active'] = true;
            }
        }
        foreach ($filterOptionsAvailable['filter_commonStatus'] as $status) {
            $count = 0;
            $dataConfigCategoryInfo = $this->getPluginConfiguration()->data->jsapiGetShopCategoryProductInfo();
            $dataConfigCategoryInfo['condition'] = new ArrayObject($dataConfigProducts['condition']);
            $arrValues = array($status);
            if (!empty($filterOptionsApplied['filter_commonStatus'])) {
                $arrValues = array_merge($filterOptionsApplied['filter_commonStatus'], $arrValues);
            }
            $arrValues = array_unique($arrValues);
            // var_dump($arrValues);
            $dataConfigCategoryInfo['condition']['shop_products.Status'] = $this->getPluginConfiguration()->data->jsapiCreateDataSourceCondition($arrValues, 'IN');
            $filterData = $this->getCustomer()->fetch($dataConfigCategoryInfo);
            if (isset($filterData) && isset($filterData['ItemsCount'])) {
                $count = $filterData['ItemsCount'];
            }
            // var_dump($filterData);
            // var_dump($dataConfigCategoryInfo);
            // var_dump('-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=');
            // var_dump('-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=');
            // var_dump('-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=');
            // var_dump('-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=');
            $statuses[$status]['ID'] = $status;
            $statuses[$status]['ProductCount'] = intval($count);
            $statuses[$status]['Active'] = false;
            // var_dump($filterData);
            if (!empty($filterOptionsApplied['filter_commonStatus'])) {
                $statuses[$status]['ProductCount'] -= $currentProductCount;
                $statuses[$status]['Active'] = true;
            }
        }

        // if ($products)
        //     foreach ($products as $obj) {
        //         $OriginID = $obj['OriginID'];
        //         $CategoryID = $obj['CategoryID'];
        //         $status = $obj['Status'];
        //         if (isset($statuses[$status]))
        //             $statuses[$status]['ProductCount']++;
        //         if (isset($brands[$OriginID]))
        //             $brands[$OriginID]['ProductCount']++;
        //         if (isset($categories[$CategoryID]))
        //             $categories[$CategoryID]['ProductCount']++;
        //         foreach ($obj['Features'] as $featureGroup => $featureList) {
        //             if (!isset($features[$featureGroup])) {
        //                 $features[$featureGroup] = array();
        //             }
        //             foreach ($featureList as $key => $featureName) {
        //                 if (!isset($features[$featureGroup][$key]['Count'])) {
        //                     $features[$featureGroup][$key] = array(
        //                         'Name' => $featureName,
        //                         'Count' => 1,
        //                         'ID' => $key
        //                     );
        //                 }
        //                 else {
        //                     // $features[$featureGroup][$key]['Name'] = $featureName
        //                     $features[$featureGroup][$key]['Count']++;
        //                     // $features[$featureGroup][$key]['ID'] = $featureID;
        //                 }
        //             }
        //         }
        //     }

        $filterOptionsAvailable['filter_categoryBrands'] = $brands;
        $filterOptionsAvailable['filter_categorySubCategories'] = $categories;
        $filterOptionsAvailable['filter_commonStatus'] = $statuses;
        $filterOptionsAvailable['filter_commonFeatures'] = $features;

        // store data
        $data['items'] = $products;
        $data['filter'] = array(
            'filterOptionsAvailable' => $filterOptionsAvailable,
            'filterOptionsApplied' => $filterOptionsApplied,
            'info' => array(
                "count" => $currentProductCount
            )
        );
        $data['_location'] = $this->getAPI()->categories->getCategoryLocation($categoryID);
        // return data object
        return $data;
    }

    public function get (&$resp, $req) {
        $resp = $this->getCatalogBrowse($req->data);
    }

}

?>