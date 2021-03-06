<?php
namespace static_\plugins\shop\api;

use \engine\objects\plugin as basePlugin;
use \engine\lib\validate as Validate;
use \engine\lib\secure as Secure;
use \engine\lib\path as Path;
use \engine\lib\request as Request;
use \engine\lib\api as API;
use \static_\plugins\shop\api\shoputils as ShopUtils;
use Exception;
use ArrayObject;

class products {

    private $_listKey_Recent = 'shop:listRecent';

    public function getProductUploadInnerDir ($productID, $subDir = '') {
        $apiCustomer = API::getAPI('system:customers');
        $customer = $apiCustomer->getRuntimeCustomer();
        $path = '';
        if (empty($subDir))
            $path = Path::createDirPath($customer['HostName'], 'shop', 'products', $productID);
        else
            $path = Path::createDirPath($customer['HostName'], 'shop', 'products', $productID, $subDir);
        return $path;
    }
    public function getProductUploadInnerImagePath ($name, $productID, $subDir = false) {
        $path = $this->getProductUploadInnerDir($productID, $subDir);
        return $path . $name;
    }
    // -----------------------------------------------
    // -----------------------------------------------
    // PRODUCTS
    // -----------------------------------------------
    // -----------------------------------------------
    // product standalone item (short or full)
    // -----------------------------------------------

    private function __adjustProduct (&$product, $skipRelations = true) {
        global $app;
        // adjusting
        $productID = intval($product['ID']);
        $product['ID'] = $productID;
        $product['OriginID'] = intval($product['OriginID']);
        $product['CategoryID'] = intval($product['CategoryID']);
        $product['_category'] = API::getAPI('shop:categories')->getCategoryByID($product['CategoryID']);
        $product['_origin'] = API::getAPI('shop:origins')->getOriginByID($product['OriginID']);
        $product['Attributes'] = $this->getProductAttributes($productID);
        $product['IsPromo'] = intval($product['IsPromo']) === 1;
        $product['IsFeatured'] = intval($product['IsFeatured']) === 1;
        $product['IsOffer'] = intval($product['IsOffer']) === 1;
        $product['ShowBanner'] = intval($product['ShowBanner']) === 1;

        // create display product title
        $displayName = array();
        if (!empty($product['Name'])) {
            $displayName[] = $product['Name'];
        }
        if (!empty($product['_origin'])) {
            $displayName[] = $product['_origin']['Name'];
        }
        if (!empty($product['Model'])) {
            $displayName[] = $product['Model'];
        }
        $product['_displayNameFull'] = implode(' ', $displayName);
        $product['_displayName'] = implode(' ', array_slice($displayName, 1));

        // misc data
        if (!$skipRelations) {
            $product['Relations'] = $this->getProductRelations($productID);
        }

        // features
        $product['Features'] = $this->getProductFeatures($productID);

        // media
        $product['Images'] = $this->getProductImages($productID);
        $product['Videos'] = $this->getProductVideos($productID);
        $product['Banners'] = $this->getProductBanners($productID);

        // Utils
        $product['viewExtrasInWish'] = API::getAPI('shop:wishlists')->productIsInWishList($productID);
        $product['viewExtrasInCompare'] = API::getAPI('shop:comparelists')->productIsInCompareList($productID);
        $product['viewExtrasInCartCount'] = API::getAPI('shop:orders')->productCountInCart($productID);

        // is available
        $product['_available'] = in_array($product['Status'], dbquery::getProductStatusesWhenAvailable());
        $product['_archived'] = in_array($product['Status'], dbquery::getProductStatusesWhenDisabled());

        // promo
        $promo = API::getAPI('shop:promos')->getSessionPromo();
        $product['_promo'] = $promo;

        // prices and actual price
        $price = floatval($product['Price']);
        $prevprice = floatval($product['PrevPrice']);
        $actualPrice = 0;
        $priceHistory = $this->getProductPriceHistory($productID);
        if ($product['IsPromo'] && !empty($promo) && !empty($promo['Discount']) && $promo['Discount'] > 0) {
            $product['_promoIsApplied'] = true;
            $actualPrice = (100 - intval($promo['Discount'])) / 100 * $price;
        } else {
            $product['_promoIsApplied'] = false;
            $actualPrice = $price;
        }
        $actualPrice = floatval($actualPrice);
        $savingValue = $prevprice - $actualPrice;
        unset($product['Price']);
        unset($product['PrevPrice']);

        // apply currencies
        $convertedPrices = API::getAPI('shop:exchangerates')->convertToRates($actualPrice);
        $convertedPrevPrices = API::getAPI('shop:exchangerates')->convertToRates($prevprice);
        $convertedSavings = API::getAPI('shop:exchangerates')->convertToRates($savingValue);

        // create product prices object
        $product['_prices'] = array(
            'price' => $price,
            'previous' => $prevprice,
            'actual' => $actualPrice,
            'others' => $convertedPrices,
            'history' => $priceHistory,
            'previousothers' => $convertedPrevPrices,
            'savings' => $savingValue,
            'savingsothers' => $convertedSavings
        );

        $product['ShopDiscount'] = $prevprice > 0 ? 100 - intval($price * 100 / $prevprice) : 0;
        $product['IsBigSavings'] = $product['ShopDiscount'] > 5;
        $product['GoodToShowPreviousPrice'] = $savingValue > 10;

        if (!empty($product['Attributes']['PROMO_TEXT'])) {
            $product['Attributes']['PROMO_TEXT'] = str_replace('[DisplayName]', $product['_displayName'], $product['Attributes']['PROMO_TEXT']);
        }

        // // save product into recently viewed list
        // $isDirectRequestToProduct = Request::hasInGet('id') || Request::hasInGet('params');
        // if (Request::isGET() && !$app->isToolbox() && !empty($isDirectRequestToProduct)) {
        //     $recentProducts = isset($_SESSION[$this->_listKey_Recent]) ? $_SESSION[$this->_listKey_Recent] : array();
        //     $recentProducts[] = $productID;
        //     $_SESSION[$this->_listKey_Recent] = array_unique($recentProducts);
        // }

        // var_dump($product);
        return $product;
    }

    private function _addProductIntoLastViewedList ($productID) {
        $recentProducts = isset($_SESSION[$this->_listKey_Recent]) ? $_SESSION[$this->_listKey_Recent] : array();
        $recentProducts[] = $productID;
        $_SESSION[$this->_listKey_Recent] = array_unique($recentProducts);
    }

    public function getProductByID ($productID) {
        global $app;
        if (empty($productID) || !is_numeric($productID))
            return null;
        $config = dbquery::shopGetProductItem($productID);
        $product = $app->getDB()->query($config);
        if (empty($product))
            return null;
        return $this->__adjustProduct($product);
    }

    public function getProductByExternalKey ($productExternalKey) {
        global $app;
        $config = dbquery::shopGetProductItemByExternalKey($productExternalKey);
        $product = $app->getDB()->query($config);
        if (empty($product)) {
            return null;
        }
        return $this->__adjustProduct($product);
    }

    public function getProductByName ($productName) {
        global $app;
        $config = dbquery::shopGetProductItemByName($productName);
        $product = $app->getDB()->query($config);
        if (empty($product))
            return null;
        return $this->__adjustProduct($product);
    }

    public function getProductByModel ($productModel) {
        global $app;
        $config = dbquery::shopGetProductItemByModel($productModel);
        $product = $app->getDB()->query($config);
        if (empty($product))
            return null;
        return $this->__adjustProduct($product);
    }

    public function getProductByModelAndOriginName ($productModel, $originName) {
        global $app;
        $config = dbquery::shopGetProductItemByModelAndOriginName($productModel, $originName);
        $product = $app->getDB()->query($config);
        if (empty($product))
            return null;
        return $this->__adjustProduct($product);
    }

    public function getProductIDByModelAndOriginName ($productModel, $originName) {
        global $app;
        $config = dbquery::shopGetProductIDByModelAndOriginName($productModel, $originNam);
        $product = $app->getDB()->query($config);
        if (empty($product))
            return null;
        return intval($product['ID']);
    }

    public function getProductIDByExternalKey ($productExternalKey) {
        global $app;
        $config = dbquery::shopGetProductItemByExternalKey($productExternalKey);
        $product = $app->getDB()->query($config);
        if (empty($product))
            return null;
        return intval($product['ID']);
    }

    public function verifyProductByID ($productID) {
        global $app;
        $config = dbquery::shopGetProductIDbyID();
        $product = $app->getDB()->query($config);
        if (empty($product))
            return null;
        return intval($product['ID']);
    }

    public function getProductImages ($productID) {
        global $app;
        $images = array();
        $config = dbquery::shopGetProductAttributes($productID, 'IMAGE');
        $data = $app->getDB()->query($config);
        // var_dump($data);
        if (!empty($data)) {
            foreach ($data as $item) {
                if (!empty($item['Value'])) {
                    $images[] = array(
                        'name' => $item['Value'],
                        'normal' => '/' . Path::getUploadDirectory() . $this->getProductUploadInnerImagePath($item['Value'], $productID),
                        'md' => '/' . Path::getUploadDirectory() . $this->getProductUploadInnerImagePath($item['Value'], $productID, 'md'),
                        'sm' => '/' . Path::getUploadDirectory() . $this->getProductUploadInnerImagePath($item['Value'], $productID, 'sm'),
                        'xs' => '/' . Path::getUploadDirectory() . $this->getProductUploadInnerImagePath($item['Value'], $productID, 'xs'),
                        'micro' => '/' . Path::getUploadDirectory() . $this->getProductUploadInnerImagePath($item['Value'], $productID, 'micro')
                    );
                }
            }
        }
        return $images;
    }

    public function getProductBanners ($productID) {
        global $app;
        $banners = array();
        $config = dbquery::shopGetProductAttributes($productID, dbquery::getProductBannerTypes());
        $data = $app->getDB()->query($config);
        if (!empty($data)) {
            foreach ($data as $item) {
                if (!empty($item['Value'])) {
                    $banners[$item['Attribute']] = array(
                        'name' => $item['Value'],
                        'banner' => '/' . Path::getUploadDirectory() . $this->getProductUploadInnerImagePath($item['Value'], $productID)
                    );
                }
            }
        }
        // get banner texts
        // $config = dbquery::shopGetProductAttributes($productID, array('BANNER_TEXT_LINE1', 'BANNER_TEXT_LINE2'));
        // $config['options']['asDict'] = 'Attribute';
        // $data = $app->getDB()->query($config);
        // // var_dump($data);
        // foreach ($banners as $type => $value) {
        //     if (isset($data['BANNER_TEXT_LINE1'])) {
        //         $banners[$type]['text1'] = $data['BANNER_TEXT_LINE1'];
        //     }
        //     if (isset($data['BANNER_TEXT_LINE2'])) {
        //         $banners[$type]['text2'] = $data['BANNER_TEXT_LINE2'];
        //     }
        // }
        return $banners;
    }

    public function getProductVideos ($productID) {
        global $app;
        $videos = array();
        $config = dbquery::shopGetProductAttributes($productID, 'VIDEO');
        $data = $app->getDB()->query($config);
        if (!empty($data)) {
            foreach ($data as $item) {
                $videos[] = $item['Value'];
            }
        }
        return $videos;
    }

    public function getProductAttributes ($productID) {
        global $app;
        $attr = array();
        $config = dbquery::shopGetProductAttributes($productID);
        $data = $app->getDB()->query($config);
        if (!empty($data)) {
            foreach ($data as $item) {
                if ($item['Attribute'] === 'IMAGE' || $item['Attribute'] === 'VIDEO') {
                    continue;
                }
                $attr[$item['Attribute']] = $item['Value'];
            }
        }
        return $attr;
    }

    public function getProductFeatures ($productID) {
        global $app;
        $featuresGroups = array();
        $config = dbquery::shopGetProductFeatures($productID);
        $data = $app->getDB()->query($config);
        if (!empty($data)) {
            foreach ($data as $value) {
                if (!isset($featuresGroups[$value['GroupName']])) {
                    $featuresGroups[$value['GroupName']] = array();
                }
                $featuresGroups[$value['GroupName']][$value['ID']] = $value['FieldName'];
            }
        }
        return $featuresGroups;
    }

    public function getProductPrice ($productID) {
        global $app;
        $price = null;
        $config = dbquery::shopGetProductPrice($productID);
        $data = $app->getDB()->query($config);
        if (isset($data['Price'])) {
            $price = floatval($data['Price']);
        }
        return $price;
    }

    public function getProductPriceHistory ($productID) {
        global $app;
        $prices = array();
        $config = dbquery::shopGetProductPriceStats($productID);
        $data = $app->getDB()->query($config);
        if (!empty($data)) {
            foreach ($data as $item) {
                $prices[] = array($item['DateCreated'], floatval($item['Price']));
            }
        }
        return $prices;
    }

    public function getProductRelations ($productID) {
        global $app;
        $relations = array();
        $configProductsRelations = dbquery::shopGetProductRelations($productID);
        $relatedItemsIDs = $app->getDB()->query($configProductsRelations);
        if (isset($relatedItemsIDs)) {
            foreach ($relatedItemsIDs as $relationItem) {
                $relatedProductID = intval($relationItem['ProductB_ID']);
                if ($relatedProductID === $productID)
                    continue;
                $relatedProduct = $this->getProductByID($relatedProductID);
                if (isset($relatedProduct))
                    $relations[] = $relatedProduct;
            }
        }
        return $relations;
    }

    public function getProductShortInfo ($productID) {
        global $app;
        $config = dbquery::shopGetProductShortInfo($productID);
        $productInfo = $app->getDB()->query($config);
        if (empty($productInfo))
            return null;
        return $productInfo;
    }

    // TODO: optimmize list query
    public function getProducts_List (array $options = array(), $saveIntoRecent = false) {
        global $app;
        $config = dbquery::shopGetProductList($options);
        if (empty($config))
            return null;
        $self = $this;
        $callbacks = array(
            "parse" => function ($items) use($self, $saveIntoRecent) {
                $_items = array();
                foreach ($items as $key => $productRawItem) {
                    $_items[] = $self->getProductByID($productRawItem['ID']);
                }
                return $_items;
            }
        );
        $dataList = $app->getDB()->getDataList($config, $options, $callbacks);
        return $dataList;
    }

    // TODO: optimmize list query
    public function getNewProducts_List (array $options = array()) {
        global $app;
        // var_dump($options);
        $config = dbquery::shopGetProductList_NewItems($options);
        if (empty($config))
            return null;
        $self = $this;
        $callbacks = array(
            "parse" => function ($items) use($self) {
                $_items = array();
                foreach ($items as $key => $orderRawItem) {
                    $_items[] = $self->getProductByID($orderRawItem['ID']);
                }
                return $_items;
            }
        );
        $dataList = $app->getDB()->getDataList($config, $options, $callbacks);
        return $dataList;
    }

    // TODO: optimmize list query
    public function getTopProducts_List (array $options = array()) {
        global $app;
        $config = dbquery::shopStat_PopularProducts();
        if (empty($config))
            return null;
        $self = $this;
        $callbacks = array(
            "parse" => function ($items) use($self) {
                $_items = array();
                foreach ($items as $key => $orderRawItem) {
                    $_items[] = $self->getProductByID($orderRawItem['ProductID']);
                }
                return $_items;
            }
        );
        $dataList = $app->getDB()->getDataList($config, $options, $callbacks);
        return $dataList;
    }

    // TODO: optimmize list query
    public function getViewedProducts_List () {
        global $app;
        $_items = array();
        $viewedProductsIDs = isset($_SESSION[$this->_listKey_Recent]) ? $_SESSION[$this->_listKey_Recent] : array();
        foreach ($viewedProductsIDs as $productID) {
            $_items[] = $this->getProductByID($productID);
        }
        $dataList = $app->getDB()->getDataListFromArray($_items);
        return $dataList;
    }

    // TODO: optimize list quer
    public function getOnSaleProducts_List (array $listOptions = array()) {
        // global $app;
        // $options['sort'] = 'shop_products.DateUpdated';
        // $options['order'] = 'DESC';
        // $options['_fshop_products.Status'] = join(',', dbquery::getProductStatusesWhenAvailable()) . ':IN';
        // $options['_fshop_products.Price'] = 'PrevPrice:>';
        // $options['_fshop_products.Status'] = 'DISCOUNT';
        // $config = dbquery::shopGetProductList_NewItems($options);
        // if (empty($config))
        //     return null;
        // $self = $this;
        // $callbacks = array(
        //     "parse" => function ($items) use($self) {
        //         $_items = array();
        //         foreach ($items as $key => $orderRawItem) {
        //             $_items[] = $self->getProductByID($orderRawItem['ID']);
        //         }
        //         return $_items;
        //     }
        // );
        // $dataList = $app->getDB()->getDataList($config, $options, $callbacks);
        // return $dataList;


        $self = $this;
        $params = array();
        $params['list'] = $listOptions;
        $params['callbacks'] = array(
            'parse' => function ($dbRawItem) use ($self) {
                return $self->getProductByID($dbRawItem['ID']);
            }
        );

        return dbquery::fetchOnSaleProducts_List($params);
    }

    // TODO: optimmize list query
    public function getFeaturedProducts_List (array $options = array()) {
        global $app;
        $options['sort'] = 'shop_products.DateUpdated';
        $options['order'] = 'DESC';
        $options['_fshop_products.Status'] = join(',', dbquery::getProductStatusesWhenAvailable()) . ':IN';
        $options['_fIsFeatured'] = true;
        // var_dump($options);
        $config = dbquery::shopGetProductList($options);
        if (empty($config))
            return null;
        $self = $this;
        $callbacks = array(
            "parse" => function ($items) use($self) {
                $_items = array();
                foreach ($items as $key => $orderRawItem) {
                    $_items[] = $self->getProductByID($orderRawItem['ID']);
                }
                return $_items;
            }
        );
        $dataList = $app->getDB()->getDataList($config, $options, $callbacks);
        return $dataList;
    }

    // TODO: optimmize list query
    public function getOffersProducts_List (array $options = array()) {
        global $app;
        $options['sort'] = 'shop_products.DateUpdated';
        $options['order'] = 'DESC';
        $options['_fshop_products.Status'] = join(',', dbquery::getProductStatusesWhenAvailable()) . ':IN';
        $options['_fIsOffer'] = true;
        // $options['_fPrevPrice'] = 'Price:>';
        $config = dbquery::shopGetProductList($options);
        if (empty($config))
            return null;
        $self = $this;
        $callbacks = array(
            "parse" => function ($items) use($self) {
                $_items = array();
                foreach ($items as $key => $orderRawItem) {
                    $_items[] = $self->getProductByID($orderRawItem['ID']);
                }
                return $_items;
            }
        );
        $dataList = $app->getDB()->getDataList($config, $options, $callbacks);
        return $dataList;
    }

    // TODO: optimmize list query
    public function getSearchProducts_List ($text) {
        return API::getAPI('shop:search')->search($text);
    }

    public function createProduct ($reqData) {
        global $app;
        $result = array();
        $errors = array();
        $success = false;
        $ProductID = null;

        // adjust verify/create category
        $adjustedRes = $this->adjustCategoryAndOriginIDs($reqData);
        $reqData['CategoryID'] = $adjustedRes['CategoryID'];
        $reqData['OriginID'] = $adjustedRes['OriginID'];
        $errors += $adjustedRes['errors'];

        $validatedDataObj = Validate::getValidData($reqData, array(
            'CategoryID' => array('int'),
            'OriginID' => array('int'),
            'Name' => array('string', 'notEmpty', 'min' => 1, 'max' => 300),
            'Description' => array('string', 'skipIfEmpty', 'max' => 10000, 'defaultValueIfEmpty' => ''),
            'Synopsis' => array('string', 'skipIfEmpty', 'max' => 350),
            'Model' => array('skipIfEmpty', 'max' => 300),
            'SKU' => array('skipIfUnset', 'max' => 300),
            'Price' => array('numeric', 'notEmpty'),
            'IsPromo' => array('sqlbool', 'skipIfUnset'),
            'IsOffer' => array('sqlbool', 'skipIfUnset'),
            'IsFeatured' => array('sqlbool', 'skipIfUnset'),
            'ShowBanner' => array('sqlbool', 'skipIfUnset'),
            'Status' => array('string', 'skipIfEmpty'),
            'Tags' => array('skipIfEmpty'),
            'ISBN' => array('skipIfEmpty'),
            'WARRANTY' => array('skipIfEmpty', 'defaultValueIfEmpty' => ''),
            'Features' =>  array('array', 'notEmpty'),
            'file1' => array('string', 'skipIfEmpty'),
            'file2' => array('string', 'skipIfEmpty'),
            'file3' => array('string', 'skipIfEmpty'),
            'file4' => array('string', 'skipIfEmpty'),
            'file5' => array('string', 'skipIfEmpty'),
            'promoText' => array('skipIfUnset'),
            'fileBannerLarge' => array('string', 'skipIfUnset'),
            'fileBannerMedium' => array('string', 'skipIfUnset'),
            'fileBannerSmall' => array('string', 'skipIfUnset'),
            'fileBannerMicro' => array('string', 'skipIfUnset'),
            'bannerTextLine1' => array('skipIfUnset'),
            'bannerTextLine2' => array('skipIfUnset'),
            'relatedProductIds' => array('array', 'skipIfUnset')
        ));

        if ($validatedDataObj["totalErrors"] == 0)
            try {
                $validatedValues = $validatedDataObj['values'];
                $CustomerID = $app->getSite()->getRuntimeCustomerID();
                $attributes = array();
                $attributes["IMAGE"] = array();
                $attributes["BANNER"] = array();
                $features = array();
                $productFeaturesIDs = array();
                $relatedIDs = array();

                // $keyToStripQuotes = array('Name', 'Description', 'Model', 'SKU', 'ISBN');
                // foreach ($keyToStripQuotes as $key) {
                //     if (isset($validatedValues[$key]))
                //         $validatedValues[$key] = mysqli_real_escape_string($validatedValues[$key]);
                // }

                // var_dump('::::: create products with values:::::::');
                // var_dump($validatedValues);

                // extract attributes
                if (isset($validatedValues['Tags'])) {
                    $attributes["TAGS"] = $validatedValues['Tags'];
                }
                if (isset($validatedValues['ISBN'])) {
                    $attributes["ISBN"] = $validatedValues['ISBN'];
                }
                if (isset($validatedValues['SKU'])) {
                    $attributes["SKU"] = $validatedValues['SKU'];
                }
                if (isset($validatedValues['WARRANTY'])) {
                    $attributes["WARRANTY"] = $validatedValues['WARRANTY'];
                }
                if (isset($validatedValues['relatedProductIds'])) {
                    $relatedIDs = $validatedValues['relatedProductIds'];
                }
                // promo and banners
                if (isset($validatedValues['promoText'])) {
                    $attributes['PROMO_TEXT'] = $validatedValues['promoText'];
                }
                if (isset($validatedValues['bannerTextLine1'])) {
                    $attributes['BANNER_TEXT_LINE1'] = $validatedValues['bannerTextLine1'];
                }
                if (isset($validatedValues['bannerTextLine2'])) {
                    $attributes['BANNER_TEXT_LINE2'] = $validatedValues['bannerTextLine2'];
                }
                // extract features
                if (isset($validatedValues['Features'])) {
                    $features = $validatedValues['Features'];
                }
                // I don't think loop for 5 items is better for perfomance
                if (!empty($validatedValues['file1'])) {
                    $attributes["IMAGE"][] = $validatedValues['file1'];
                }
                if (!empty($validatedValues['file2'])) {
                    $attributes["IMAGE"][] = $validatedValues['file2'];
                }
                if (!empty($validatedValues['file3'])) {
                    $attributes["IMAGE"][] = $validatedValues['file3'];
                }
                if (!empty($validatedValues['file4'])) {
                    $attributes["IMAGE"][] = $validatedValues['file4'];
                }
                if (!empty($validatedValues['file5'])) {
                    $attributes["IMAGE"][] = $validatedValues['file5'];
                }
                // I don't think loop for 5 items is better for perfomance
                if (!empty($validatedValues['fileBannerLarge'])) {
                    $attributes["BANNER"]["BANNER_LARGE"] = $validatedValues['fileBannerLarge'];
                }
                if (!empty($validatedValues['fileBannerMedium'])) {
                    $attributes["BANNER"]["BANNER_MEDIUM"] = $validatedValues['fileBannerMedium'];
                }
                if (!empty($validatedValues['fileBannerSmall'])) {
                    $attributes["BANNER"]["BANNER_SMALL"] = $validatedValues['fileBannerSmall'];
                }
                if (!empty($validatedValues['fileBannerMicro'])) {
                    $attributes["BANNER"]["BANNER_MICRO"] = $validatedValues['fileBannerMicro'];
                }

                // cleanup fields
                unset($validatedValues['Tags']);
                unset($validatedValues['ISBN']);
                unset($validatedValues['SKU']);
                unset($validatedValues['WARRANTY']);
                unset($validatedValues['Features']);
                unset($validatedValues['file1']);
                unset($validatedValues['file2']);
                unset($validatedValues['file3']);
                unset($validatedValues['file4']);
                unset($validatedValues['file5']);
                unset($validatedValues['promoText']);
                unset($validatedValues['fileBannerLarge']);
                unset($validatedValues['fileBannerMedium']);
                unset($validatedValues['fileBannerSmall']);
                unset($validatedValues['fileBannerMicro']);
                unset($validatedValues['bannerTextLine1']);
                unset($validatedValues['bannerTextLine2']);
                unset($validatedValues['relatedProductIds']);

                $app->getDB()->beginTransaction();

                // adjust features
                foreach ($features as $groupName => $value) {
                    $features[$groupName] = explode(',', $value);
                }

                // add new features
                $featureMap = API::getAPI('shop:productfeatures')->getFeatures();
                foreach ($features as $groupName => $featureList) {
                    if (isset($featureMap[$groupName])) {
                        foreach ($featureList as $featureName) {
                            $featureID = array_search($featureName, $featureMap[$groupName]);
                            if ($featureID === false) {
                                $data = array();
                                $data["CustomerID"] = $CustomerID;
                                $data["FieldName"] = substr($featureName, 0, 200);
                                $data["GroupName"] = substr($groupName, 0, 100);
                                $featureID = API::getAPI('shop:productfeatures')->createFeature($data);
                                $productFeaturesIDs[] = intval($featureID);
                            } else {
                                $productFeaturesIDs[] = $featureID;
                            }
                        }
                    } else {
                        foreach ($featureList as $featureName) {
                            $data = array();
                            $data["CustomerID"] = $CustomerID;
                            $data["FieldName"] = substr($featureName, 0, 200);
                            $data["GroupName"] = substr($groupName, 0, 100);
                            $featureID = API::getAPI('shop:productfeatures')->createFeature($data);
                            $productFeaturesIDs[] = $featureID;
                        }
                    }
                }

                // // add new features
                // foreach ($features as $value) {
                //     if (is_numeric($value)) {
                //         $productFeaturesIDs[] = $value;
                //     } else {
                //         $data["FieldName"] = $value;
                //         $data["CustomerID"] = $CustomerID;
                //         $config = dbquery::shopCreateFeature($data);
                //         $featureID = $app->getDB()->query($config) ?: null;
                //         if (isset($featureID) && $featureID >= 0) {
                //             $productFeaturesIDs[] = $featureID;
                //             // $this->_getOrSetCachedState('changed:features', true);
                //         }
                //     }
                // }

                // create product
                $validatedValues["CustomerID"] = $CustomerID;
                // if (isset($validatedValues["IsPromo"])) {
                //     $validatedValues["IsPromo"] = $validatedValues["IsPromo"] ? 1 : 0;
                // }
                // if (isset($validatedValues["IsOffer"])) {
                //     $validatedValues["IsOffer"] = $validatedValues["IsOffer"] ? 1 : 0;
                // }
                // if (isset($validatedValues["IsFeatured"])) {
                //     $validatedValues["IsFeatured"] = $validatedValues["IsFeatured"] ? 1 : 0;
                // }
                
                $validatedValues["PrevPrice"] = 0;
                $validatedValues["SearchText"] = $validatedValues["Name"] . ' ' . $validatedValues["Model"];
                // var_dump($validatedValues);
                $config = dbquery::shopCreateProduct($validatedValues);
                // var_dump($config);
                $ProductID = null;
                try {
                    $ProductID = $app->getDB()->query($config) ?: null;
                } catch (Exception $ep) {
                    $errors[] = $ep->getMessage();
                }
                // var_dump($productID);
                // var_dump($app->getDB()->get_last_query());
                if (empty($ProductID)) {
                    throw new Exception('ProductCreateError');
                }

                // set new features (actually this condition must return always true)
                if (count($productFeaturesIDs)) {
                    $featureData['ProductID'] = $ProductID;
                    $featureData['CustomerID'] = $CustomerID;
                    foreach ($productFeaturesIDs as $value) {
                        $featureData['FeatureID'] = $value;
                        $config = dbquery::shopAddFeatureToProduct($featureData);
                        $app->getDB()->query($config);
                    }
                }

                // add attributes
                if (!empty($attributes)) {
                    $initAttrData = new ArrayObject(array(
                        'ProductID' => $ProductID,
                        'CustomerID' => $CustomerID
                    ));
                    // -- IMAGE
                    if (isset($attributes["IMAGE"])) {
                        foreach ($attributes["IMAGE"] as $fileName) {
                            $newFileName = $ProductID . uniqid(time());
                            $mdImagePath = 'md' . Path::getDirectorySeparator() . $fileName;
                            $smImagePath = 'sm' . Path::getDirectorySeparator() . $fileName;
                            $xsImagePath = 'xs' . Path::getDirectorySeparator() . $fileName;
                            $microImagePath = 'micro' . Path::getDirectorySeparator() . $fileName;
                            $normalImagePath = $fileName;

                            $uploadInfo = Path::moveTemporaryFile($mdImagePath, $this->getProductUploadInnerDir($ProductID, 'md'), $newFileName);
                            $uploadInfo = Path::moveTemporaryFile($smImagePath, $this->getProductUploadInnerDir($ProductID, 'sm'), $newFileName);
                            $uploadInfo = Path::moveTemporaryFile($xsImagePath, $this->getProductUploadInnerDir($ProductID, 'xs'), $newFileName);
                            $uploadInfo = Path::moveTemporaryFile($microImagePath, $this->getProductUploadInnerDir($ProductID, 'micro'), $newFileName);
                            $uploadInfo = Path::moveTemporaryFile($normalImagePath, $this->getProductUploadInnerDir($ProductID), $newFileName);

                            $attrData = $initAttrData->getArrayCopy();
                            $attrData['Attribute'] = 'IMAGE';
                            $attrData['Value'] = $uploadInfo['filename'];
                            $config = dbquery::shopAddAttributeToProduct($attrData);
                            $app->getDB()->query($config);
                        }
                    }
                    // -- BANNER_XXXX
                    $bannerTypes = dbquery::getProductBannerTypes();
                    foreach ($bannerTypes as $bannerType) {
                        if (!empty($attributes["BANNER"][$bannerType])) {
                            $uploadInfo = Path::moveTemporaryFile($attributes["BANNER"][$bannerType],
                                $this->getProductUploadInnerDir($ProductID), strtolower($bannerType));
                            $attrData = $initAttrData->getArrayCopy();
                            $attrData['Attribute'] = $bannerType;
                            $attrData['Value'] = $uploadInfo['filename'];
                            $config = dbquery::shopAddAttributeToProduct($attrData);
                            $app->getDB()->query($config);
                        }
                    }
                    // -- ISBN
                    // -- EXPIRE
                    // -- TAGS
                    // -- WARRANTY
                    // -- BANNER_TEXT_LINE1
                    // -- BANNER_TEXT_LINE2
                    // -- PROMO_TEXT
                    $commonAttributeKeys = array('ISBN', 'SKU', 'EXPIRE', 'TAGS', 'WARRANTY',
                        'BANNER_TEXT_LINE1', 'BANNER_TEXT_LINE2', 'PROMO_TEXT');
                    foreach ($commonAttributeKeys as $key) {
                        if (!isset($attributes[$key])) {
                            continue;
                        }
                        $attrData = $initAttrData->getArrayCopy();
                        $attrData['Attribute'] = $key;
                        $attrData['Value'] = $attributes[$key];
                        $config = dbquery::shopAddAttributeToProduct($attrData);
                        $app->getDB()->query($config);
                    }
                }

                // update related products
                if (isset($reqData['relatedProductIds'])) {
                    $config = dbquery::shopClearProductRelations($ProductID);
                    $app->getDB()->query($config);
                    foreach ($relatedIDs as $relatedID) {
                        $app->getDB()->query(dbquery::shopSetRelatedProduct($CustomerID, $ProductID, $relatedID));
                    }
                }

                $app->getDB()->commit();

                $success = true;
            } catch (Exception $e) {
                $app->getDB()->rollBack();
                $errors[] = $e->getMessage();
            }
        else
            $errors += $validatedDataObj["errors"];

        if ($success && !empty($ProductID)) {
            $result = $this->getProductByID($ProductID);
        }
        $result['errors'] = $errors;
        $result['success'] = $success;

        $this->updateProductSearchTextByID($ProductID);
        $this->refreshProductExternalKeyByID($ProductID);

        return $result;
    }

    public function updateProduct ($ProductID, $reqData) {
        global $app;
        $result = array();
        $errors = array();
        $success = false;

        // adjust verify/create category
        $adjustedRes = $this->adjustCategoryAndOriginIDs($reqData, true);
        if (isset($adjustedRes['CategoryID'])) {
            $reqData['CategoryID'] = $adjustedRes['CategoryID'];
        }
        if (isset($adjustedRes['OriginID'])) {
            $reqData['OriginID'] = $adjustedRes['OriginID'];
        }
        $errors += $adjustedRes['errors'];

        $validatedDataObj = Validate::getValidData($reqData, array(
            'CategoryID' => array('int', 'skipIfUnset'),
            'OriginID' => array('int', 'skipIfUnset'),
            'Name' => array('string', 'notEmpty', 'min' => 1, 'max' => 300, 'skipIfUnset'),
            'Description' => array('string', 'null', 'skipIfUnset', 'max' => 10000, 'defaultValueIfEmpty' => ''),
            'Synopsis' => array('string', 'skipIfEmpty', 'max' => 350),
            'Model' => array('skipIfUnset', 'max' => 50),
            'SKU' => array('skipIfUnset', 'max' => 50),
            'Price' => array('numeric', 'notEmpty', 'skipIfUnset'),
            'IsPromo' => array('sqlbool', 'skipIfUnset'),
            'IsOffer' => array('sqlbool', 'skipIfUnset'),
            'IsFeatured' => array('sqlbool', 'skipIfUnset'),
            'ShowBanner' => array('sqlbool', 'skipIfUnset'),
            'Status' => array('string', 'skipIfEmpty', 'skipIfUnset'),
            'Tags' => array('skipIfUnset'),
            'ISBN' => array('skipIfUnset'),
            'WARRANTY' => array('skipIfUnset', 'defaultValueIfEmpty' => ''),
            'Features' =>  array('array', 'notEmpty', 'skipIfUnset'),
            'file1' => array('string', 'skipIfUnset'),
            'file2' => array('string', 'skipIfUnset'),
            'file3' => array('string', 'skipIfUnset'),
            'file4' => array('string', 'skipIfUnset'),
            'file5' => array('string', 'skipIfUnset'),
            'promoText' => array('skipIfUnset'),
            'fileBannerLarge' => array('string', 'skipIfUnset'),
            'fileBannerMedium' => array('string', 'skipIfUnset'),
            'fileBannerSmall' => array('string', 'skipIfUnset'),
            'fileBannerMicro' => array('string', 'skipIfUnset'),
            'bannerTextLine1' => array('skipIfUnset'),
            'bannerTextLine2' => array('skipIfUnset'),
            'relatedProductIds' => array('array', 'skipIfUnset')
        ));

        if ($validatedDataObj["totalErrors"] == 0)
            try {

                $validatedValues = $validatedDataObj['values'];
                $CustomerID = $app->getSite()->getRuntimeCustomerID();
                $attributes = array();
                $attributes["IMAGE"] = array();
                $attributes["BANNER"] = array();
                $features = array();
                $productFeaturesIDs = array();
                $relatedIDs = array();


                // $keyToStripQuotes = array('Name', 'Description', 'Model', 'SKU', 'ISBN');
                // foreach ($keyToStripQuotes as $key) {
                //     if (isset($validatedValues[$key]))
                //         $validatedValues[$key] = mysqli_real_escape_string($validatedValues[$key]);
                // }

                // extract attributes
                if (isset($validatedValues['Tags'])) {
                    $attributes["TAGS"] = $validatedValues['Tags'];
                }
                if (isset($validatedValues['ISBN'])) {
                    $attributes["ISBN"] = $validatedValues['ISBN'];
                }
                if (isset($validatedValues['SKU'])) {
                    $attributes["SKU"] = $validatedValues['SKU'];
                }
                if (isset($validatedValues['WARRANTY'])) {
                    $attributes["WARRANTY"] = $validatedValues['WARRANTY'];
                }
                if (isset($validatedValues['relatedProductIds'])) {
                    $relatedIDs = $validatedValues['relatedProductIds'];
                }
                // promo and banners
                if (isset($validatedValues['promoText'])) {
                    $attributes['PROMO_TEXT'] = $validatedValues['promoText'];
                }
                if (isset($validatedValues['bannerTextLine1'])) {
                    $attributes['BANNER_TEXT_LINE1'] = $validatedValues['bannerTextLine1'];
                }
                if (isset($validatedValues['bannerTextLine2'])) {
                    $attributes['BANNER_TEXT_LINE2'] = $validatedValues['bannerTextLine2'];
                }
                // extract features
                if (isset($validatedValues['Features'])) {
                    $features = $validatedValues['Features'];
                }

                $updateImages = isset($reqData['file1']) || isset($reqData['file2'])
                    || isset($reqData['file3']) || isset($reqData['file4']) || isset($reqData['file5']);

                // I don't think loop for 5 items is better for perfomance
                if (!empty($validatedValues['file1'])) {
                    $attributes["IMAGE"][] = $validatedValues['file1'];
                }
                if (!empty($validatedValues['file2'])) {
                    $attributes["IMAGE"][] = $validatedValues['file2'];
                }
                if (!empty($validatedValues['file3'])) {
                    $attributes["IMAGE"][] = $validatedValues['file3'];
                }
                if (!empty($validatedValues['file4'])) {
                    $attributes["IMAGE"][] = $validatedValues['file4'];
                }
                if (!empty($validatedValues['file5'])) {
                    $attributes["IMAGE"][] = $validatedValues['file5'];
                }

                $updateBanners = isset($reqData['fileBannerLarge']) || isset($reqData['fileBannerMedium'])
                    || isset($reqData['fileBannerSmall']) || isset($reqData['fileBannerMicro']);

                // I don't think loop for 5 items is better for perfomance
                if (!empty($validatedValues['fileBannerLarge'])) {
                    $attributes["BANNER"]["BANNER_LARGE"] = $validatedValues['fileBannerLarge'];
                }
                if (!empty($validatedValues['fileBannerMedium'])) {
                    $attributes["BANNER"]["BANNER_MEDIUM"] = $validatedValues['fileBannerMedium'];
                }
                if (!empty($validatedValues['fileBannerSmall'])) {
                    $attributes["BANNER"]["BANNER_SMALL"] = $validatedValues['fileBannerSmall'];
                }
                if (!empty($validatedValues['fileBannerMicro'])) {
                    $attributes["BANNER"]["BANNER_MICRO"] = $validatedValues['fileBannerMicro'];
                }

                // cleanup fields
                unset($validatedValues['Tags']);
                unset($validatedValues['ISBN']);
                unset($validatedValues['SKU']);
                unset($validatedValues['WARRANTY']);
                unset($validatedValues['Features']);
                unset($validatedValues['file1']);
                unset($validatedValues['file2']);
                unset($validatedValues['file3']);
                unset($validatedValues['file4']);
                unset($validatedValues['file5']);
                unset($validatedValues['promoText']);
                unset($validatedValues['fileBannerLarge']);
                unset($validatedValues['fileBannerMedium']);
                unset($validatedValues['fileBannerSmall']);
                unset($validatedValues['fileBannerMicro']);
                unset($validatedValues['bannerTextLine1']);
                unset($validatedValues['bannerTextLine2']);
                unset($validatedValues['relatedProductIds']);

                $app->getDB()->beginTransaction();

                // adjust features
                foreach ($features as $groupName => $value) {
                    if (is_array($value))
                        $features[$groupName] = array_values($value);
                    else
                        $features[$groupName] = explode(',', $value);
                }

                // add new features
                $featureMap = API::getAPI('shop:productfeatures')->getFeatures();
                foreach ($features as $groupName => $featureList) {
                    if (isset($featureMap[$groupName])) {
                        foreach ($featureList as $featureName) {
                            $featureID = array_search($featureName, $featureMap[$groupName]);
                            if ($featureID === false) {
                                $data = array();
                                $data["CustomerID"] = $CustomerID;
                                $data["FieldName"] = substr($featureName, 0, 200);
                                $data["GroupName"] = substr($groupName, 0, 100);
                                $featureID = API::getAPI('shop:productfeatures')->createFeature($data);
                                $productFeaturesIDs[] = intval($featureID);
                            } else {
                                $productFeaturesIDs[] = $featureID;
                            }
                        }
                    } else {
                        foreach ($featureList as $featureName) {
                            $data = array();
                            $data["CustomerID"] = $CustomerID;
                            $data["FieldName"] = substr($featureName, 0, 200);
                            $data["GroupName"] = substr($groupName, 0, 100);
                            $featureID = API::getAPI('shop:productfeatures')->createFeature($data);
                            $productFeaturesIDs[] = $featureID;
                        }
                    }
                }

                // var_dump($features);
                // var_dump($featureMap);
                // var_dump($productFeaturesIDs);

                // update product
                $validatedValues["CustomerID"] = $CustomerID;
                // if (isset($validatedValues["IsPromo"])) {
                //     $validatedValues["IsPromo"] = $validatedValues["IsPromo"] ? 1 : 0;
                // }
                // if (isset($validatedValues["IsOffer"])) {
                //     $validatedValues["IsOffer"] = $validatedValues["IsOffer"] ? 1 : 0;
                // }
                // if (isset($validatedValues["IsFeatured"])) {
                //     $validatedValues["IsFeatured"] = $validatedValues["IsFeatured"] ? 1 : 0;
                // }

                if (isset($validatedValues['Price'])) {
                    $oldPrice = $this->getProductPrice($ProductID);
                    $newPrice = floatval($validatedValues['Price']);
                    // move current price into previous
                    if ($oldPrice != $newPrice) {
                        $validatedValues["PrevPrice"] = $oldPrice;
                    }
                    // mark product as offer when new price is better than current
                    if (!isset($validatedValues['IsOffer'])) {
                        if ($newPrice < $oldPrice) {
                            $validatedValues['IsOffer'] = $app->getDB()->getSqlBooleanValue(true);
                        } else {
                            $validatedValues['IsOffer'] = $app->getDB()->getSqlBooleanValue(false);
                        }
                    }
                }

                $config = dbquery::shopUpdateProduct($ProductID, $validatedValues);
                try {
                    $app->getDB()->query($config);
                } catch (Exception $ep) {
                    $errors[] = $ep->getMessage();
                }

                // set new features
                if (count($productFeaturesIDs)) {
                    // clear existed features before adding new
                    $config = dbquery::shopClearProductFeatures($ProductID);
                    $app->getDB()->query($config);
                    $featureData['ProductID'] = $ProductID;
                    $featureData['CustomerID'] = $CustomerID;
                    foreach ($productFeaturesIDs as $value) {
                        $featureData['FeatureID'] = $value;
                        // var_dump($featureData);
                        $config = dbquery::shopAddFeatureToProduct($featureData);
                        $app->getDB()->query($config);
                    }
                }

                if ($updateImages) {
                    // get previous product data
                    // we need this to re-adjust images for the product
                    $currentImages = $this->getProductImages($ProductID);
                    $filesUploaded = array();
                    $filesToDelete = array();
                    $filesToKeep = array();
                    $filesToUpload = array();

                    foreach ($currentImages as $currentImageItem) {
                        $filesUploaded[] = $currentImageItem['name'];
                    }

                    $filesToKeep = array_intersect($filesUploaded, $attributes["IMAGE"]);
                    $filesToDelete = array_diff($filesUploaded, $attributes["IMAGE"]);
                    $filesToUpload = array_diff($attributes["IMAGE"], $filesUploaded);

                    // // var_dump($previousAttributesImages);
                    // var_dump($attributes["IMAGE"]);

                    // foreach ($currentImages as $currentImageItem) {
                    //     if (in_array($currentImageItem['name'], $attributes["IMAGE"])) {
                    //         $filesToKeep[] = $currentImageItem['name'];
                    //     } else {
                    //         $filesToDelete[] = $currentImageItem['name'];
                    //     }
                    // }

                    // var_dump('current>>>>>>>');
                    // var_dump($currentImages);
                    // var_dump('delete>>>>>>>');
                    // var_dump($filesToDelete);
                    // var_dump('keep>>>>>>>');
                    // var_dump($filesToKeep);
                    // var_dump('upload>>>>>>>');
                    // var_dump($filesToUpload);

                    $uploadedFileNames = array();
                    foreach ($filesToUpload as $fileName) {

                        $newFileName = $ProductID . uniqid(time());
                        $mdImagePath = 'md' . Path::getDirectorySeparator() . $fileName;
                        $smImagePath = 'sm' . Path::getDirectorySeparator() . $fileName;
                        $xsImagePath = 'xs' . Path::getDirectorySeparator() . $fileName;
                        $microImagePath = 'micro' . Path::getDirectorySeparator() . $fileName;
                        $normalImagePath = $fileName;

                        $uploadInfo = Path::moveTemporaryFile($mdImagePath, $this->getProductUploadInnerDir($ProductID, 'md'), $newFileName);
                        $uploadInfo = Path::moveTemporaryFile($smImagePath, $this->getProductUploadInnerDir($ProductID, 'sm'), $newFileName);
                        $uploadInfo = Path::moveTemporaryFile($xsImagePath, $this->getProductUploadInnerDir($ProductID, 'xs'), $newFileName);
                        $uploadInfo = Path::moveTemporaryFile($microImagePath, $this->getProductUploadInnerDir($ProductID, 'micro'), $newFileName);
                        $uploadInfo = Path::moveTemporaryFile($normalImagePath, $this->getProductUploadInnerDir($ProductID), $newFileName);

                        // var_dump($uploadInfo);
                        // $attrData = $initAttrData->getArrayCopy();
                        // $attrData['Attribute'] = 'IMAGE';
                        // $attrData['Value'] = $uploadInfo['filename'];
                        // $config = dbquery::shopAddAttributeToProduct($attrData);
                        // $app->getDB()->query($config);

                        // $newFileName = $ProductID . uniqid(time());
                        // $uploadInfo = $this->saveOwnTemporaryUploadedFile('sm' . Path::getDirectorySeparator() . $fileName, $this->getProductUploadInnerDir($ProductID, 'sm'), $newFileName);
                        // $this->saveOwnTemporaryUploadedFile('xs' . Path::getDirectorySeparator() . $fileName, $this->getProductUploadInnerDir($ProductID, 'xs'), $newFileName);
                        // $this->saveOwnTemporaryUploadedFile($fileName, $this->getProductUploadInnerDir($ProductID), $newFileName);
                        $uploadedFileNames[] = $uploadInfo['filename'];
                    }
                    foreach ($filesToDelete as $fileName) {

                        Path::deleteUploadedFile($this->getProductUploadInnerImagePath($fileName, $ProductID, 'md'));
                        Path::deleteUploadedFile($this->getProductUploadInnerImagePath($fileName, $ProductID, 'sm'));
                        Path::deleteUploadedFile($this->getProductUploadInnerImagePath($fileName, $ProductID, 'xs'));
                        Path::deleteUploadedFile($this->getProductUploadInnerImagePath($fileName, $ProductID, 'micro'));
                        Path::deleteUploadedFile($this->getProductUploadInnerImagePath($fileName, $ProductID));

                        // $this->deleteOwnUploadedFile($fileName, $this->getProductUploadInnerDir($ProductID, 'sm'));
                        // $this->deleteOwnUploadedFile($fileName, $this->getProductUploadInnerDir($ProductID, 'xs'));
                        // $this->deleteOwnUploadedFile($fileName, $this->getProductUploadInnerDir($ProductID));
                    }

                    $attributes["IMAGE"] = array_merge($filesToKeep, $uploadedFileNames);
                } else {
                    unset($attributes["IMAGE"]);
                }

                // update product banners
                if ($updateBanners) {
                    $currentBanners = $this->getProductBanners($ProductID);
                    $bannerTypes = dbquery::getProductBannerTypes();

                    foreach ($bannerTypes as $bannerType) {
                        // if current typ is already set for product
                        if (isset($currentBanners[$bannerType])) {
                            $currentBannerFileName = $currentBanners[$bannerType];
                            // if new on si empty we just delete existent
                            if (empty($attributes["BANNER"][$bannerType])) {
                                Path::deleteUploadedFile($this->getProductUploadInnerImagePath($currentBannerFileName, $ProductID));
                                $attributes["BANNER"][$bannerType] = null;
                            } elseif ($currentBanners[$bannerType]['name'] != $attributes["BANNER"][$bannerType]) {
                                Path::deleteUploadedFile($this->getProductUploadInnerImagePath($currentBannerFileName, $ProductID));
                                $uploadInfo = Path::moveTemporaryFile($attributes["BANNER"][$bannerType],
                                    $this->getProductUploadInnerDir($ProductID), strtolower($bannerType));
                                $attributes["BANNER"][$bannerType] = $uploadInfo['filename'];
                            }
                        } elseif (!empty($attributes["BANNER"][$bannerType])) {
                            $uploadInfo = Path::moveTemporaryFile($attributes["BANNER"][$bannerType],
                                $this->getProductUploadInnerDir($ProductID), strtolower($bannerType));
                            $attributes["BANNER"][$bannerType] = $uploadInfo['filename'];
                        }
                    }
                } else {
                    unset($attributes["BANNER"]);
                }

                // throw new Exception("Error Processing Request", 1);
                // set new attributes
                if (!empty($attributes)) {
                    $initAttrData = new ArrayObject(array(
                        'ProductID' => $ProductID,
                        'CustomerID' => $CustomerID
                    ));
                    // -- IMAGE
                    if (isset($attributes["IMAGE"])) {
                        $config = dbquery::shopClearProductAttributes($ProductID, 'IMAGE');
                        $app->getDB()->query($config);
                        foreach ($attributes["IMAGE"] as $imageName) {
                            $attrData = $initAttrData->getArrayCopy();
                            $attrData['Attribute'] = 'IMAGE';
                            $attrData['Value'] = $imageName;
                            $config = dbquery::shopAddAttributeToProduct($attrData);
                            $app->getDB()->query($config);
                        }
                    }
                    // -- BANNER_XXXX
                    $bannerTypes = dbquery::getProductBannerTypes();
                    foreach ($bannerTypes as $bannerType) {
                        $config = dbquery::shopClearProductAttributes($ProductID, $bannerType);
                        $app->getDB()->query($config);
                        if (!empty($attributes["BANNER"][$bannerType])) {
                            $attrData = $initAttrData->getArrayCopy();
                            $attrData['Attribute'] = $bannerType;
                            $attrData['Value'] = $attributes["BANNER"][$bannerType];
                            $config = dbquery::shopAddAttributeToProduct($attrData);
                            $app->getDB()->query($config);
                        }
                    }
                    // -- ISBN
                    // -- EXPIRE
                    // -- TAGS
                    // -- WARRANTY
                    // -- BANNER_TEXT_LINE1
                    // -- BANNER_TEXT_LINE2
                    // -- PROMO_TEXT
                    $commonAttributeKeys = array('ISBN', 'SKU', 'EXPIRE', 'TAGS', 'WARRANTY',
                        'BANNER_TEXT_LINE1', 'BANNER_TEXT_LINE2', 'PROMO_TEXT');
                    foreach ($commonAttributeKeys as $key) {
                        if (!isset($attributes[$key])) {
                            continue;
                        }
                        // clear existed tags before adding new ones
                        $config = dbquery::shopClearProductAttributes($ProductID, $key);
                        $app->getDB()->query($config);
                        $attrData = $initAttrData->getArrayCopy();
                        $attrData['Attribute'] = $key;
                        $attrData['Value'] = $attributes[$key];
                        $config = dbquery::shopAddAttributeToProduct($attrData);
                        $app->getDB()->query($config);
                    }
                }

                // update related products
                if (isset($reqData['relatedProductIds'])) {
                    $config = dbquery::shopClearProductRelations($ProductID);
                    $app->getDB()->query($config);
                    foreach ($relatedIDs as $relatedID) {
                        $app->getDB()->query(dbquery::shopSetRelatedProduct($CustomerID, $ProductID, $relatedID));
                    }
                }

                $app->getDB()->commit();

                $success = true;
            } catch (Exception $e) {
                $app->getDB()->rollBack();
                $errors[] = $e->getMessage();
            }
        else
            $errors += $validatedDataObj["errors"];

        $result = $this->getProductByID($ProductID);
        $result['errors'] = $errors;
        $result['success'] = $success;

        $this->updateProductSearchTextByID($ProductID);
        $this->refreshProductExternalKeyByID($ProductID);

        return $result;
    }

    private function adjustCategoryAndOriginIDs ($data, $skipIfEmpty = false) {
        $category = null;
        $origin = null;
        $errors = array();
        $res = array();

        // verify/create origin
        $originID = null;
        $originName = null;
        if (isset($data['OriginID'])) {
            if (is_numeric($data['OriginID'])) {
                $origin = API::getAPI('shop:origins')->getOriginByID($data['OriginID']);
            } else {
                $origin = API::getAPI('shop:origins')->getOriginByName($data['OriginID']);
            }
            $originName = $data['OriginID'];
        } elseif (isset($data['OriginName'])) {
            $origin = API::getAPI('shop:origins')->getOriginByName($data['OriginName']);
            $originName = $data['OriginName'];
        }
        // create new origin
        if ($origin === null) {
            if (!$skipIfEmpty) {
                $origin = API::getAPI('shop:origins')->getOriginByName('EmptyOrigin');
                if (empty($origin)) {
                    $origin = API::getAPI('shop:origins')->createOrigin(array(
                        'Name' => empty($originName) ? 'EmptyOrigin' : $originName
                    ));
                    $originID = $origin['ID'];
                    $errors += $origin['errors'];
                } else {
                    $originID = $origin['ID'];
                }
            }
        } else {
            $originID = $origin['ID'];
        }

        // verify/create category
        $categoryID = null;
        $categoryName = null;
        if (isset($data['CategoryID'])) {
            if (is_numeric($data['CategoryID'])) {
                $category = API::getAPI('shop:categories')->getCategoryByID($data['CategoryID']);
            } else {
                $category = API::getAPI('shop:categories')->getCategoryByName($data['CategoryID']);
            }
            $categoryName = $data['CategoryID'];
        } elseif (isset($data['CategoryName'])) {
            $category = API::getAPI('shop:categories')->getCategoryByName($data['CategoryName']);
            $categoryName = $data['CategoryName'];
        }
        // create new origin
        if ($category === null) {
            if (!$skipIfEmpty) {
                $category = API::getAPI('shop:categories')->getCategoryByName('Uncategorized');
                if (empty($category)) {
                    $category = API::getAPI('shop:categories')->createCategory(array(
                        'Name' => empty($categoryName) ? 'Uncategorized' : $categoryName
                    ));
                    $categoryID = $category['ID'];
                    $errors += $category['errors'];
                } else {
                    $categoryID = $category['ID'];
                }
            }
        } else {
            $categoryID = $category['ID'];
        }

        $res['errors'] = $errors;
        if (!is_null($categoryID))
            $res['CategoryID'] = $categoryID;
        if (!is_null($originID))
            $res['OriginID'] = $originID;

        return $res;

        // return array(
        //     'errors' => ,
        //     'OriginID' => $originID,
        //     'CategoryID' => $categoryID
        // );
    }

    public function updateOrInsertProduct ($data) {
        global $app;
        $result = array();
        $errors = array();
        $product = null;
        $productID = null;

        // we have the product item already in db
        if (isset($data['ID'])) {
            //-- echo "[INFO] using product ID " . $data['ID'] . PHP_EOL;
            $productID = $this->verifyProductByID($data['ID']);
            // try to get product item by name and model
        } elseif (isset($data['Model']) && isset($data['OriginName'])) {
            //-- echo "[INFO] using product Model and OriginName " . $data['Model'] . ' + ' . $data['OriginName'] . PHP_EOL;
            $exKey = ShopUtils::createProductExternalKey($data);
            $productIDByExternalKey = $this->getProductIDByExternalKey($exKey, true);
            $productIDByModelAndOrigin = $this->getProductIDByModelAndOriginName($data['Model'], $data['OriginName']);
            // echo '# ... productIDByExternalKey = ' . $productIDByExternalKey . PHP_EOL;
            // echo '# ... productIDByModelAndOrigin = ' . $productIDByModelAndOrigin . PHP_EOL;
            // try to get duplicated product and mark them as removed
            // $result = $this->markProductAsRemovedByModelAndOrigin(str_replace(' ', '%', $data['Model']), $data['OriginName']);
            // $result = $this->markProductAsRemovedByModelAndOrigin(str_replace(' ', '%', $data['Model']), strtolower($data['OriginName']));
            // $result = $this->markProductAsRemovedByModelAndOrigin(str_replace(' ', '%', $data['Model']), strtoupper($data['OriginName']));
            // $result = $this->markProductAsRemovedByModelAndOrigin(str_replace(' ', '', $data['Model']), $data['OriginName']);
            // $result = $this->markProductAsRemovedByModelAndOrigin(str_replace(' ', '', $data['Model']), strtolower($data['OriginName']));
            // $result = $this->markProductAsRemovedByModelAndOrigin(str_replace(' ', '', $data['Model']), strtoupper($data['OriginName']));
            $result = $this->markProductAsRemovedByModelAndOrigin(implode("%", str_split(str_replace(' ', '', $data['Model']))), $data['OriginName']);

            if ($productIDByExternalKey === null) {
                $productID = $productIDByModelAndOrigin;
            } else {
                $productID = $productIDByExternalKey;
            }
        }

        // var_dump($data);
        // echo "# ... current key " . $exKey . PHP_EOL;
        if ($productID === null) {
            $result = $this->createProduct($data);
        } else {
            // echo "# ... updating [" . $productID . "] " . $exKey . PHP_EOL;
            $result = $this->updateProduct($productID, $data);
        }

        $result['created'] = $result['success'] && $productID === null;
        $result['updated'] = $result['success'] && $productID !== null;
        return $result;
    }

    public function refreshProductExternalKeyByID ($productID) {
        global $app;
        $result = array();
        $errors = array();
        $success = false;
        try {

            $productInfo = $this->getProductShortInfo($productID);
            $exKey = ShopUtils::createProductExternalKey($productInfo);

            // echo 'refreshProductExternalKeyByID(' . $productID . '); >>';
            // var_dump($productInfo);
            // echo $exKey;
            // echo PHP_EOL;

            $app->getDB()->beginTransaction();

            $config = dbquery::updateProductExternalKeyByID($productID, $exKey);
            $app->getDB()->query($config);

            $app->getDB()->commit();

            $success = true;
        } catch (Exception $e) {
            $app->getDB()->rollBack();
            $errors[] = $e->getMessage();
        }

        $result['errors'] = $errors;
        $result['success'] = $success;

        return $result;
    }

    public function updateProductSearchTextByID ($productID) {
        global $app;
        $result = array();
        $errors = array();
        $success = false;
        try {

            $app->getDB()->beginTransaction();

            $config = dbquery::updateProductSearchTextByID($productID);
            $app->getDB()->query($config);

            $app->getDB()->commit();

            $success = true;
        } catch (Exception $e) {
            $app->getDB()->rollBack();
            $errors[] = $e->getMessage();
        }

        $result['errors'] = $errors;
        $result['success'] = $success;

        return $result;
    }

    public function updateProductSearchTextByOriginID ($originID) {
        global $app;
        $result = array();
        $errors = array();
        $success = false;
        try {

            $app->getDB()->beginTransaction();

            $config = dbquery::updateProductSearchTextByOriginID($originID);
            $app->getDB()->query($config);

            $app->getDB()->commit();

            $success = true;
        } catch (Exception $e) {
            $app->getDB()->rollBack();
            $errors[] = $e->getMessage();
        }

        $result['errors'] = $errors;
        $result['success'] = $success;

        return $result;
    }

    public function archiveProduct ($ProductID) {
        global $app;
        $result = array();
        $errors = array();
        $success = false;
        try {

            // $CustomerID = $app->getSite()->getRuntimeCustomerID();

            $app->getDB()->beginTransaction();

            // $data = array(
            //     'CustomerID' => $CustomerID,
            //     'Status' => 'ARCHIVED'
            // );

            $config = dbquery::shopArchiveProduct($ProductID);
            $app->getDB()->query($config);

            $app->getDB()->commit();

            $success = true;
        } catch (Exception $e) {
            $app->getDB()->rollBack();
            $errors[] = $e->getMessage();
        }

        $result = $this->getProductByID($ProductID);
        $result['errors'] = $errors;
        $result['success'] = $success;

        return $result;
    }

    public function archiveAllProducts () {
        global $app;
        $result = array();
        $errors = array();
        $success = false;
        try {

            // $CustomerID = $app->getSite()->getRuntimeCustomerID();

            $app->getDB()->beginTransaction();

            // $data = array(
            //     'CustomerID' => $CustomerID,
            //     'Status' => 'ARCHIVED'
            // );

            $config = dbquery::shopArchiveProduct(null);
            // $config = dbquery::shopUpdateProduct(null, $data);
            // $config['condition'] = null;
            $app->getDB()->query($config);

            $app->getDB()->commit();

            $success = true;
        } catch (Exception $e) {
            $app->getDB()->rollBack();
            $errors[] = $e->getMessage();
        }

        $result['errors'] = $errors;
        $result['success'] = $success;

        return $result;
    }

    public function markProductAsRemoved ($ProductID) {
        global $app;
        $result = array();
        $errors = array();
        $success = false;
        try {

            $CustomerID = $app->getSite()->getRuntimeCustomerID();

            $app->getDB()->beginTransaction();

            $data = array(
                'CustomerID' => $CustomerID,
                'Status' => 'REMOVED'
            );

            $config = dbquery::shopUpdateProduct($ProductID, $data);
            $app->getDB()->query($config);

            $app->getDB()->commit();

            $success = true;
        } catch (Exception $e) {
            $app->getDB()->rollBack();
            $errors[] = $e->getMessage();
        }

        $result = $this->getProductByID($ProductID);
        $result['errors'] = $errors;
        $result['success'] = $success;

        return $result;
    }

    public function markProductAsRemovedByModelAndOrigin ($model, $originName) {
        global $app;
        $result = array();
        $errors = array();
        $success = false;
        try {

            $app->getDB()->beginTransaction();

            $config = dbquery::shopMarkProductAsRemovedByModelAndOrigin($model, $originName);
            $app->getDB()->query($config);

            $app->getDB()->commit();

            $success = true;
        } catch (Exception $e) {
            $app->getDB()->rollBack();
            $errors[] = $e->getMessage();
        }

        $result['errors'] = $errors;
        $result['success'] = $success;

        return $result;
    }



    public function getProductsArray_TopNonPopular () {
        global $app;
        // get non-popuplar 15 products
        $config = dbquery::shopStat_NonPopularProducts();
        $productIDs = $app->getDB()->query($config);
        $data = array();
        if (!empty($productIDs)) {
            foreach ($productIDs as $val) {
                $data[] = $this->getProductByID($val['ID']);
            }
        }
        return $data;
    }

    public function getProductsArray_TopPopular () {
        global $app;
        // get top 15 products
        $config = dbquery::shopStat_PopularProducts();
        $productIDs = $app->getDB()->query($config);
        $data = array();
        if (!empty($productIDs)) {
            foreach ($productIDs as $val) {
                $product = $this->getProductByID($val['ProductID']);
                $product['SoldTotal'] = floatval($val['SoldTotal']);
                $product['SumTotal'] = floatval($val['SumTotal']);
                $data[] = $product;
            }
        }
        return $data;
    }

    public function getStats_ProductsOverview ($filter = null) {
        global $app;
        if (!API::getAPI('system:auth')->ifYouCan('Admin')) {
            return null;
        }
        // get shop products overview:
        $config = dbquery::shopStat_ProductsOverview($filter);
        $data = $app->getDB()->query($config) ?: array();
        $total = 0;
        $res = array();
        $availableStatuses = dbquery::getProductStatuses();
        foreach ($availableStatuses as $key) {
            if (isset($data[$key])) {
                $res[$key] = intval($data[$key]);
                $total += $data[$key];
            } else
                $res[$key] = 0;
        }
        // get total
        $res['TOTAL'] = $total;
        return $res;
    }

    public function getStats_ProductsIntensityActiveLastMonth () {
        global $app;
        if (!API::getAPI('system:auth')->ifYouCan('Admin') &&
            !API::getAPI('system:auth')->ifYouCan('Maintain')) {
            return null;
        }
        $config = dbquery::shopStat_ProductsIntensityLastMonth('ACTIVE');
        $data = $app->getDB()->query($config) ?: array();
        return $data;
    }
    public function getStats_ProductsIntensityPreorderLastMonth () {
        global $app;
        if (!API::getAPI('system:auth')->ifYouCan('Admin') &&
            !API::getAPI('system:auth')->ifYouCan('Maintain')) {
            return null;
        }
        $config = dbquery::shopStat_ProductsIntensityLastMonth('PREORDER');
        $data = $app->getDB()->query($config) ?: array();
        return $data;
    }
    public function getStats_ProductsIntensityDiscountLastMonth () {
        global $app;
        if (!API::getAPI('system:auth')->ifYouCan('Admin') &&
            !API::getAPI('system:auth')->ifYouCan('Maintain')) {
            return null;
        }
        $config = dbquery::shopStat_ProductsIntensityLastMonth('DISCOUNT');
        $data = $app->getDB()->query($config) ?: array();
        return $data;
    }

    public function get (&$resp, $req) {
        // for specific product item
        // by id
        if (Request::hasRequestedID()) {
            $resp = $this->getProductByID($req->id);
            return;
        }
        // or by ExternalKey
        if (Request::hasRequestedExternalKey()) {
            $this->getProductByExternalKey($req->externalKey);
        }
        // for the case when we have to fecth list with products
        if (Request::noRequestedItem()) {
            if (isset($req->get['type'])) {
                switch ($req->get['type']) {
                    case 'new': {
                        $resp = $this->getNewProducts_List($req->get);
                        break;
                    }
                    case 'top': {
                        $resp = $this->getTopProducts_List($req->get);
                        break;
                    }
                    case 'viewed': {
                        $resp = $this->getViewedProducts_List();
                        break;
                    }
                    case 'onsale': {
                        $resp = $this->getOnSaleProducts_List($req->get);
                        break;
                    }
                    case 'featured': {
                        $resp = $this->getFeaturedProducts_List($req->get);
                        break;
                    }
                    case 'offers': {
                        $resp = $this->getOffersProducts_List($req->get);
                        break;
                    }
                    case 'search': {
                        $resp = $this->getSearchProducts_List($req->get['text']);
                        break;
                    }
                }
            } else {
                $resp = $this->getProducts_List($req->get);
            }

        }
        // TODO: cleanup
        // if (!empty($req->id)) {
        //     if (is_numeric($req->id)) {
        //         $ProductID = intval($req->id);
        //         $resp = $this->getProductByID($ProductID);
        //     } else {
        //         $resp = $this->getProductByExternalKey($req->id);
        //     }
        // } else {
        // }
    }

    public function post (&$resp, $req) {
        if (!API::getAPI('system:auth')->ifYouCan('Maintain') ||
            (!API::getAPI('system:auth')->ifYouCan('Admin') &&
                !API::getAPI('system:auth')->ifYouCan('shop_CREATE_PRODUCT'))) {
            $resp['error'] = "AccessDenied";
            return;
        }
        $resp = $this->createProduct($req->data);
    }

    public function put (&$resp, $req) {
        if (!API::getAPI('system:auth')->ifYouCan('Maintain') ||
            (!API::getAPI('system:auth')->ifYouCan('Admin') &&
                !API::getAPI('system:auth')->ifYouCan('shop_EDIT_PRODUCT'))) {
            $resp['error'] = "AccessDenied";
            return;
        }
        if (empty($req->id)) {
            $resp['error'] = 'MissedParameter_id';
        } else {
            $ProductID = intval($req->id);
            $resp = $this->updateProduct($ProductID, $req->data);
        }
    }

    public function delete (&$resp, $req) {
        if (!API::getAPI('system:auth')->ifYouCan('Maintain') ||
            (!API::getAPI('system:auth')->ifYouCan('Admin') &&
                !API::getAPI('system:auth')->ifYouCan('shop_EDIT_PRODUCT'))) {
            $resp['error'] = "AccessDenied";
            return;
        }
        $resp = $this->archiveProduct($req->data);
    }

}

?>