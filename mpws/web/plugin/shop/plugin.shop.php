<?php

class pluginShop extends objectPlugin {

    private $_listKey_Wish = 'shop:wishList';
    private $_listKey_Recent = 'shop:listRecent';
    private $_listKey_Compare = 'shop:listCompare';
    private $_listKey_Cart = 'shop:cart';
    private $_listKey_Promo = 'shop:promo';

    // product standalone item (short or full)
    // -----------------------------------------------
    private function _getProductByID ($productID, $saveIntoRecent = false, $skipRelations = false) {
        if (empty($productID) || !is_numeric($productID))
            return null;

        $config = configurationShopDataSource::jsapiShopProductItemGet($productID);
        $product = $this->getCustomer()->fetch($config);

        if (empty($product))
            return null;

        $configProductsAttr = configurationShopDataSource::jsapiShopProductAttributesGet($productID);
        $configProductsPrice = configurationShopDataSource::jsapiShopProductPriceStatsGet($productID);
        $configProductsFeatures = configurationShopDataSource::jsapiShopGetProductFeatures($productID);
        $configProductsRelations = configurationShopDataSource::jsapiShopProductRelations($productID);

        $product['Attributes'] = $this->getCustomer()->fetch($configProductsAttr);
        $product['Prices'] = $this->getCustomer()->fetch($configProductsPrice);
        $product['Features'] = $this->getCustomer()->fetch($configProductsFeatures);

        // adjusting
        $product['ID'] = intval($product['ID']);
        $product['CategoryID'] = intval($product['CategoryID']);
        $product['OriginID'] = intval($product['OriginID']);
        $product['Attributes'] = $product['Attributes']['ProductAttributes'];
        $product['IsPromo'] = intval($product['IsPromo']) === 1;
        $product['Price'] = floatval($product['Price']);
        $product['Prices'] = array_map(function($price) { return floatval($price); }, $product['Prices']['PriceArchive'] ?: array());

        if (!is_array($product['Features']))
            $product['Features'] = array();

        $relations = array();
        if (!$skipRelations) {
            $relatedItemsIDs = $this->getCustomer()->fetch($configProductsRelations);
            if (isset($relatedItemsIDs)) {
                foreach ($relatedItemsIDs as $relationItem) {
                    $relatedProductID = intval($relationItem['ProductB_ID']);
                    if ($relatedProductID === $productID)
                        continue;
                    $relatedProduct = $this->_getProductByID($relatedProductID, $saveIntoRecent, true);
                    if (isset($relatedProduct))
                        $relations[] = $relatedProduct;
                }
            }
        }
        $product['Relations'] = $relations;

        // Utils
        $product['_viewExtras'] = array();
        $product['_viewExtras']['InWish'] = $this->__productIsInWishList($productID);
        $product['_viewExtras']['InCompare'] = $this->__productIsInCompareList($productID);
        $product['_viewExtras']['InCartCount'] = $this->__productCountInCart($productID);

        // promo
        $promo = $this->_getSessionPromo();
        $product['_promoIsApplied'] = false;
        if ($product['IsPromo'] && !empty($promo) && !empty($promo['Discount'])&& $promo['Discount'] > 0) {
            $product['_promoIsApplied'] = true;
            $product['DiscountPrice'] = (100 - intval($promo['Discount'])) / 100 * $product['Price'];
            $product['promo'] = $promo;
        }

        $product['SellingPrice'] = isset($product['DiscountPrice']) ? $product['DiscountPrice'] : $product['Price'];
        $product['SellingPrice'] = floatval($product['SellingPrice']);

        // is available
        $product['_available'] = in_array($product['Status'], array("ACTIVE", "DISCOUNT", "PREORDER", "DEFECT"));

        // save product into recently viewed list
        if ($saveIntoRecent && !glIsToolbox()) {
            $recentProducts = isset($_SESSION[$this->_listKey_Recent]) ? $_SESSION[$this->_listKey_Recent] : array();
            $recentProducts[$productID] = $product;
            $_SESSION[$this->_listKey_Recent] = $recentProducts;
        }
        return $product;
    }

    // products list sorted by date added
    // -----------------------------------------------
    private function _getProducts_TopNonPopular () {
        // get non-popuplar 50 products
        $config = configurationShopDataSource::jsapiShopStat_NonPopularProducts();
        $productIDs = $this->getCustomer()->fetch($config);
        $data = array();
        if (!empty($productIDs))
            foreach ($productIDs as $val)
                $data[] = $this->_getProductByID($val['ID']);
        return $data;
    }

    private function _getProducts_TopPopular () {
        // get top 50 products
        $config = configurationShopDataSource::jsapiShopStat_PopularProducts();
        $productIDs = $this->getCustomer()->fetch($config);
        $data = array();
        if (!empty($productIDs))
            foreach ($productIDs as $val) {
                $product = $this->_getProductByID($val['ProductID']);
                $product['SoldTotal'] = $val['SoldTotal'];
                $product['SumTotal'] = $val['SumTotal'];
                $data[] = $product;
            }
        return $data;
    }

    private function _getProducts_Latest () {
        // get expired orders
        $config = configurationShopDataSource::jsapiShopProductListLatest();
        $productIDs = $this->getCustomer()->fetch($config);
        $data = array();
        if (!empty($productIDs))
            foreach ($productIDs as $val)
                $data[] = $this->_getProductByID($val['ID']);
        return $data;
    }

    private function _getProducts_ByStatus ($status) {
        $config = configurationShopDataSource::jsapiShopProductListByStatus($status);
        $productIDs = $this->getCustomer()->fetch($config);
        $data = array();
        if (!empty($productIDs))
            foreach ($productIDs as $val)
                $data[] = $this->_getProductByID($val['ID']);
        return $data;
    }

    private function _getProducts_Sale () {
        $config = configurationShopDataSource::jsapiShopProductListByStatus(array('DISCOUNT', 'DEFECT'), 'IN');
        $productIDs = $this->getCustomer()->fetch($config);
        $data = array();
        if (!empty($productIDs))
            foreach ($productIDs as $val)
                $data[] = $this->_getProductByID($val['ID']);
        return $data;
    }

    private function _getProducts_Uncompleted () {
        $config = configurationShopDataSource::jsapiShopProductListUncompleted();
        $productIDs = $this->getCustomer()->fetch($config);
        $data = array();
        if (!empty($productIDs))
            foreach ($productIDs as $val)
                $data[] = $this->_getProductByID($val['ID']);
        return $data;
    }

    private function _getProducts_Todays () {
        $config = configurationShopDataSource::jsapiShopProductListByStatus('ARCHIVED', '!=');
        $config['condition']['shop_products.DateCreated'] = configurationShopDataSource::jsapiCreateDataSourceCondition(date('Y-m-d'), ">");
        $productIDs = $this->getCustomer()->fetch($config);
        // var_dump($config);
        $data = array();
        if (!empty($productIDs))
            foreach ($productIDs as $val)
                $data[] = $this->_getProductByID($val['ID']);
        return $data;
    }

    private function _createProduct ($reqData) {
        if (!$this->ifYou('CanAdmin') && !$this->ifYou('CanCreate')) {
            return glWrap("AccessDenied");
        }

    }

    private function _updateProduct ($reqData) {
        if (!$this->ifYou('CanAdmin') && !$this->ifYou('CanEdit')) {
            return glWrap("AccessDenied");
        }

    }

    // origin
    // -----------------------------------------------

    private function _getOriginByID ($originID) {

    }

    private function _createOrigin ($reqData) {
        if (!$this->ifYou('CanAdmin') && !$this->ifYou('CanCreate')) {
            return glWrap("AccessDenied");
        }

    }

    private function _updateOrigin ($reqData) {
        if (!$this->ifYou('CanAdmin') && !$this->ifYou('CanEdit')) {
            return glWrap("AccessDenied");
        }

    }

    // category
    // -----------------------------------------------
    private function _getCategoryByID ($categoryID) {

    }

    private function _createCategory ($reqData) {
        if (!$this->ifYou('CanAdmin') && !$this->ifYou('CanCreate')) {
            return glWrap("AccessDenied");
        }

    }

    private function _updateCategory ($reqData) {
        if (!$this->ifYou('CanAdmin') && !$this->ifYou('CanEdit')) {
            return glWrap("AccessDenied");
        }

    }

    // orders
    // -----------------------------------------------
    private function _getOrderByID ($orderID) {
        $config = configurationShopDataSource::jsapiGetShopOrderByID($orderID);
        $order = null;
        // if ($this->ifYou('CanAdmin')) {
            $order = $this->getCustomer()->fetch($config);
        // } else {
        //     $config['condition']['AccountID'] = configurationShopDataSource::jsapiCreateDataSourceCondition($this->getSessionAccountID());
        //     $order = $this->getCustomer()->fetch($config);
        // }

        if (empty($order)) {
            return glWrap('error', 'OrderDoesNotExist');
        }

        $order['ID'] = intval($order['ID']);
        $this->__attachOrderDetails($order);
        return $order;
    }

    private function _getOrderByHash ($orderHash) {
        $config = configurationShopDataSource::jsapiGetShopOrderByHash($orderHash);
        $order = $this->getCustomer()->fetch($config);

        if (empty($order)) {
            return glWrap('error', 'OrderDoesNotExist');
        }

        $order['ID'] = intval($order['ID']);
        $this->__attachOrderDetails($order);
        return $order;
    }

    private function _getOrderTemp () {
        $order['temp'] = true;
        $this->__attachOrderDetails($order);
        return $order;
    }

    private function _resetOrderTemp () {
        $this->_resetSessionPromo();
        $this->_resetSessionOrderProducts();
    }

    private function _getOrders_Expired () {
        // get expired orders
        $config = configurationShopDataSource::jsapiGetShopOrderIDs();
        $config['condition']['Status'] = configurationShopDataSource::jsapiCreateDataSourceCondition("SHOP_CLOSED", "!=");
        $config['condition']['DateCreated'] = configurationShopDataSource::jsapiCreateDataSourceCondition(date('Y-m-d', strtotime("-1 week")), "<");

        // check permissions
        $orderIDs = array();
        if ($this->ifYou('CanAdmin')) {
            $orderIDs = $this->getCustomer()->fetch($config);
        } else {
            $config['condition']['AccountID'] = configurationShopDataSource::jsapiCreateDataSourceCondition($this->getSessionAccountID());
            $orderIDs = $this->getCustomer()->fetch($config);
        }

        $data = array();
        if (!empty($orderIDs))
            foreach ($orderIDs as $val)
                $data[] = $this->_getOrderByID($val['ID']);
        return $data;
    }

    private function _getOrders_Todays () {
        // get todays orders
        $config = configurationShopDataSource::jsapiGetShopOrderIDs();
        $config['condition']['Status'] = configurationShopDataSource::jsapiCreateDataSourceCondition("NEW");
        $config['condition']['DateCreated'] = configurationShopDataSource::jsapiCreateDataSourceCondition(date('Y-m-d'), ">");

        // set permissions
        $orderIDs = array();
        if ($this->ifYou('CanAdmin')) {
            $orderIDs = $this->getCustomer()->fetch($config);
        } else {
            $config['condition']['AccountID'] = configurationShopDataSource::jsapiCreateDataSourceCondition($this->getSessionAccountID());
            $orderIDs = $this->getCustomer()->fetch($config);
        }

        $data = array();
        if (!empty($orderIDs))
            foreach ($orderIDs as $val)
                $data[] = $this->_getOrderByID($val['ID']);
        return $data;
    }

    private function _getOrders_ByStatus ($status) {
        // get expired orders
        $config = configurationShopDataSource::jsapiGetShopOrderIDs();
        $config['condition']['Status'] = configurationShopDataSource::jsapiCreateDataSourceCondition($status);

        // check permissions
        $orderIDs = array();
        if ($this->ifYou('CanAdmin')) {
            $orderIDs = $this->getCustomer()->fetch($config);
        } else {
            $config['condition']['AccountID'] = configurationShopDataSource::jsapiCreateDataSourceCondition($this->getSessionAccountID());
            $orderIDs = $this->getCustomer()->fetch($config);
        }

        $data = array();
        if (!empty($orderIDs))
            foreach ($orderIDs as $val)
                $data[] = $this->_getOrderByID($val['ID']);
        return $data;
    }

    private function _getOrders_Browse ($req) {
        // get all orders
        $config = configurationShopDataSource::jsapiGetShopOrderIDs();

        // pagination
        $page = isset($req->get['page']) ? $req->get['page'] : false;
        $per_page = isset($req->get['per_page']) ? $req->get['per_page'] : false;

        if (!empty($per_page)) {
            $config['limit'] = $per_page;
            if (!empty($page)) {
                $config['offset'] = ($page - 1) * $per_page;
            }
        }

        // sorting
        $sort = isset($req->get['sort']) ? $req->get['sort'] : false;
        $order = isset($req->get['order']) ? $req->get['order'] : false;
        if (!empty($sort) && !empty($order)) {
            $config["order"] = array(
                "field" =>  'shop_orders' . DOT . $sort,
                "ordering" => strtoupper($order)
            );
        }

        // check permissions
        $orderIDs = array();
        if ($this->ifYou('CanAdmin')) {
            $orderIDs = $this->getCustomer()->fetch($config);
        } else {
            $config['condition']['AccountID'] = configurationShopDataSource::jsapiCreateDataSourceCondition($this->getSessionAccountID());
            $orderIDs = $this->getCustomer()->fetch($config);
        }

        // var_dump($orderIDs);
        $data = array();
        if (!empty($orderIDs))
            foreach ($orderIDs as $val)
                $data[$val['ID']] = $this->_getOrderByID($val['ID']);
        return $data;
    }

    private function _createOrder ($reqData) {

        $result = array();
        $errors = array();
        $success = false;

        // var_dump($order);
        // var_dump($reqData);

        $accountToken =  "";
        $formAccountToken = "";
        $formAddressID = "";

        if (!empty($reqData['account']))
            $accountToken = $reqData['account']['ValidationString'];

        if (!empty($reqData['form']['shopCartAccountValidationString']))
            $formAccountToken = $reqData['form']['shopCartAccountValidationString'];

        if (!empty($reqData['form']['shopCartAccountValidationString']))
            $formAddressID = $reqData['form']['shopCartAccountAddressID'];

        $pluginAccount = $this->getPlugin('account');

        try {
            $this->getCustomerDataBase()->beginTransaction();
            $this->getCustomerDataBase()->disableTransactions();

            // check if matches
            if ($accountToken !== $formAccountToken)
                throw new Exception("WrongTokensOccured", 1);

            // create new profile
            if (empty($accountToken) && empty($formAccountToken)) {

                // reset address id becuase empty account is occured
                $formAddressID = null;

                // create new account
                $new_password = librarySecure::generateStrongPassword();

                $account = $pluginAccount->createAccount(array(
                    "FirstName" => $reqData['form']['shopCartUserName'],
                    "EMail" => $reqData['form']['shopCartUserEmail'],
                    "Phone" => $reqData['form']['shopCartUserPhone'],
                    "Password" => $new_password,
                    "ConfirmPassword" => $new_password
                ));

                if (count($account['errors']))
                    $errors['Account'] = $account['errors'];

                if ($account['success'] === false)
                    throw new Exception("AccountCreateError", 1);

            } else {

                // get account by token string (ValidationString)
                $account = $pluginAccount->getAccountByValidationString($formAccountToken);

                if (empty($account))
                    throw new Exception("WrongAccount", 1);

                if ($account['Status'] !== "ACTIVE")
                    throw new Exception("AccountIsBlocked", 1);

                // need to validate account
                // if account exits
                // if account is active
                if ($account["FirstName"] !== $reqData["form"]["shopCartAccountFirstName"] ||
                    $account["LastName"] !== $reqData["form"]["shopCartAccountLastName"] ||
                    $account["EMail"] !== $reqData["form"]["shopCartAccountEMail"] ||
                    $account["Phone"] !== $reqData["form"]["shopCartAccountPhone"])
                    throw new Exception("AccountDataMismatch", 1);

                // at this point we have a valid account
            }

            $accountID = $account['ID'];

            // create new address for account
            if (empty($formAddressID) || empty($formAccountToken)) {

                // create account address
                $accountAddress = $pluginAccount->createAddress($accountID, array(
                    "Address" => $reqData['form']['shopCartUserAddress'],
                    "POBox" => $reqData['form']['shopCartUserPOBox'],
                    "Country" => $reqData['form']['shopCartUserCountry'],
                    "City" => $reqData['form']['shopCartUserCity']
                ), true); // <= this allows creating unliked addresses or add new address to account when it's possible

                if (count($accountAddress['errors']))
                    $errors['Account'] = $accountAddress['errors'];

                if ($accountAddress['success'] === false)
                    throw new Exception("AccountAddressCreateError", 1);

            } else {
                // validate provided address id for account
                // we must check it if the authenticated account has this address id
                if (!isset($account['Addresses'][$formAddressID]))
                    throw new Exception("WrongAccountAddressID", 1);
                else
                    $accountAddress = $account['Addresses'][$formAddressID];
            }

            $addressID = $accountAddress['ID'];

            $order = $this->_getOrderTemp();
            // $sessionOrderProducts = $this->_getSessionOrderProducts();

            // var_dump($this->_getSessionOrderProducts());
            if (empty($order['items']))
                 throw new Exception("NoProudctsToPurchase", 1);

            $orderPromoID = empty($order['promo']) ? null : $order['promo']['ID'];

            // start creating order
            $dataOrder = array();
            $dataOrder["AccountID"] = $accountID;
            $dataOrder["AccountAddressesID"] = $addressID;
            $dataOrder["CustomerID"] = $this->getCustomer()->getCustomerID();
            $dataOrder["Shipping"] = $reqData['form']['shopCartLogistic'];
            $dataOrder["Warehouse"] = $reqData['form']['shopCartWarehouse'];
            $dataOrder["Comment"] = $reqData['form']['shopCartComment'];
            $dataOrder["PromoID"] = $orderPromoID;

            $configOrder = configurationShopDataSource::jsapiShopOrderCreate($dataOrder);
            $orderID = $this->getCustomer()->fetch($configOrder);

            if (empty($orderID))
                throw new Exception("OrderCreateError", 1);

            // save products
            // -----------------------
            // ProductID
            // OrderID
            // ProductPrice
            // _orderQuantity
            foreach ($order['items'] as $productItem) {
                $dataBought = array();
                $dataBought["CustomerID"] = $this->getCustomer()->getCustomerID();
                $dataBought["ProductID"] = $productItem["ID"];
                $dataBought["OrderID"] = $orderID;
                $dataBought["Price"] = $productItem["Price"];
                $dataBought["SellingPrice"] = $productItem["SellingPrice"];
                $dataBought["Quantity"] = $productItem["_orderQuantity"];
                $dataBought["IsPromo"] = $productItem["IsPromo"];
                $configBought = configurationShopDataSource::jsapiShopOrderBoughtCreate($dataBought);
                $boughtID = $this->getCustomer()->fetch($configBought);

                // check for created bought
                if (empty($boughtID))
                    throw new Exception("BoughtCreateError", 1);
            }

            // if (empty($accountID) || empty($addressID))
            //     throw new Exception("UnableToLinkAccountOrAddress", 1);

            $this->getCustomerDataBase()->enableTransactions();
            $this->getCustomerDataBase()->commit();

            $success = true;
        } catch (Exception $e) {
            $this->getCustomerDataBase()->enableTransactions();
            $this->getCustomerDataBase()->rollBack();
            $errors['Order'][] = $e->getMessage();
            $success = false;
        }

        if ($success) {
            // reset temp order
            $this->_resetOrderTemp();
            // get created order

            $result = $this->_getOrderByID($orderID);
        }

        $result['errors'] = $errors;
        $result['success'] = $success;

        return $result;
    }

    private function _updateOrder ($reqData) {
        // only admin can update orders
        if (!$this->ifYou('CanAdmin') || !$this->ifYou('CanEdit')) {
            return glWrap("AccessDenied");
        }
    }

    private function _disableOrderByID ($OrderID) {
        // check permissions
        if ($this->ifYou('CanAdmin') && $this->ifYou('CanEdit')) {
            $config = configurationShopDataSource::jsapiDisableOrder($OrderID);
            $this->getCustomer()->fetch($config);
            return glWrap("ok", true);
        } else {
            return glWrap("error", "AccessDenied");
        }
    }

    // stats
    // -----------------------------------------------
    private function _getStats_OrdersOverview () {
        if (!$this->ifYou('CanAdmin')) {
            return null;
        }
        $config = configurationShopDataSource::jsapiShopStat_OrdersOverview();
        $data = $this->getCustomer()->fetch($config);
        return $data;
    }

    private function _getStats_ProductsOverview () {
        if (!$this->ifYou('CanAdmin')) {
            return null;
        }
        // get shop products overview:
        $config = configurationShopDataSource::jsapiShopStat_ProductsOverview();
        $data = $this->getCustomer()->fetch($config);
        return $data;
    }

    // breadcrumb
    // -----------------------------------------------
    private function _getCatalogLocation ($productID = null, $categoryID = null) {
        $location = null;

        if (empty($productID) && empty($categoryID))
            return $location;

        if ($productID) {
            // get product entry
            $configProduct = configurationShopDataSource::jsapiShopProductSingleInfoGet($productID);
            $productDataEntry = $this->getCustomer()->fetch($configProduct);
            if (isset($productDataEntry['CategoryID'])) {
                $configLocation = configurationShopDataSource::jsapiShopCategoryLocationGet($productDataEntry['CategoryID']);
                $location['items'] = $this->getCustomer()->fetch($configLocation);
                $location['product'] = $productDataEntry;
            }
        } else {
            $configLocation = configurationShopDataSource::jsapiShopCategoryLocationGet($categoryID);
            $location['items'] = $this->getCustomer()->fetch($configLocation);
        }
        return $location;
    }

    // shop catalog tree
    // -----------------------------------------------
    private function _getCatalogTree () {

        function getTree (array &$elements, $parentId = null) {
            $branch = array();
            // echo "#######Looking for element where parentid ==", $parentId, PHP_EOL;
            foreach ($elements as $key => $element) {
                // echo "~~~Current element ID = ", $element['ParentID'], PHP_EOL;
                if ($element['ParentID'] == $parentId) {
                    // echo "Element is found".PHP_EOL;
                    // echo "Looking for element child nodes wherer ParentID = ", $key,PHP_EOL;
                    $element['childNodes'] = getTree($elements, $key);
                    $branch[$key] = $element;
                    // unset($elements[$key]);
                }
            }
            // echo PHP_EOL . "-=-=-=-=-=-=--=--Results for element where parentid ==", $parentId. PHP_EOL;
            // var_dump($branch);
            return $branch;
        }

        $config = configurationShopDataSource::jsapiShopCatalogTree();
        $categories = $this->getCustomer()->fetch($config);
        $map = array();
        foreach ($categories as $key => $value)
            $map[$value['ID']] = $value;

        $tree = getTree($map);

        return $tree;
    }

    private function _getCatalogBrowse () {

        $data = array();
        $categoryID = libraryRequest::fromGET('id', null);

        if (!is_numeric($categoryID)) {
            $data['error'] = '"id" parameter is missed';
            return $data;
        }

        $categoryID = intval($categoryID);

        $filterOptions = array(
            /* common options */
            "id" => $categoryID,
            "filter_viewSortBy" => null,
            "filter_viewItemsOnPage" => 16,
            "filter_viewPageNum" => 1,
            "filter_commonPriceMax" => null,
            "filter_commonPriceMin" => 0,
            "filter_commonStatus" => array(),
            "filter_commonFeatures" => array(),

            /* category based */
            "filter_categoryBrands" => array(),
            "filter_categorySubCategories" => array(),
        );

        // filtering
        $filterOptionsApplied = new ArrayObject($filterOptions);
        $filterOptionsAvailable = new ArrayObject($filterOptions);

        // get all product available statuses
        $filterOptionsAvailable['filter_commonStatus'] = $this->getCustomerDataBase()->getTableStatusFieldOptions(configurationShopDataSource::$Table_ShopProducts);

        // init filter
        foreach ($filterOptionsApplied as $key => $value) {
            $filterOptionsApplied[$key] = libraryRequest::fromGET($key, $filterOptions[$key]);
            if ($key == "filter_viewItemsOnPage" || $key == "filter_viewPageNum")
                $filterOptionsApplied[$key] = intval($filterOptionsApplied[$key]);
            if ($key === "filter_commonPriceMax" || $key == "filter_commonPriceMin")
                $filterOptionsApplied[$key] = floatval($filterOptionsApplied[$key]);
            if (is_string($filterOptionsApplied[$key])) {
                if ($key == "filter_commonStatus" || $key == "filter_categoryBrands")
                    $filterOptionsApplied[$key] = explode(',', $filterOptionsApplied[$key]);
                if ($key == "filter_categorySubCategories" || $key == "filter_commonFeatures")
                    $filterOptionsApplied[$key] = explode(',', $filterOptionsApplied[$key]);
            }
            // var_dump($filterOptionsApplied[$key]);
        }

        $dataConfigCategoryPriceEdges = configurationShopDataSource::jsapiShopCategoryPriceEdgesGet($categoryID);
        $dataConfigCategoryAllSubCategories = configurationShopDataSource::jsapiShopCategoryAllSubCategoriesGet($categoryID);

        // get category sub-categories and origins
        $dataCategoryPriceEdges = $this->getCustomer()->fetch($dataConfigCategoryPriceEdges);
        $dataCategoryAllSubCategories = $this->getCustomer()->fetch($dataConfigCategoryAllSubCategories);

        $cetagorySubIDs = array($categoryID);
        if (!empty($dataCategoryAllSubCategories))
            foreach ($dataCategoryAllSubCategories as $value)
                $cetagorySubIDs[] = $value['ID'];

        //filter: get category price edges
        $filterOptionsAvailable['filter_commonPriceMax'] = floatval($dataCategoryPriceEdges['PriceMax'] ?: 0);
        $filterOptionsAvailable['filter_commonPriceMin'] = floatval($dataCategoryPriceEdges['PriceMin'] ?: 0);

        // get all brands for both current category and sub-categories
        $dataConfigCategoryAllBrands = configurationShopDataSource::jsapiShopCategoryAndSubCategoriesAllBrandsGet(implode(',', $cetagorySubIDs));
        $dataCategoryAllBrands = $this->getCustomer()->fetch($dataConfigCategoryAllBrands);

        // set categories and brands
        $filterOptionsAvailable['filter_categoryBrands'] = $dataCategoryAllBrands ?: array();
        $filterOptionsAvailable['filter_categorySubCategories'] = $dataCategoryAllSubCategories ?: array();

        // set data source
        // ---
        $dataConfigCategoryInfo = configurationShopDataSource::jsapiGetShopCategoryProductInfo($cetagorySubIDs);
        $dataConfigProducts = configurationShopDataSource::jsapiGetShopCategoryProductList($cetagorySubIDs);

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
            $dataConfigProducts['condition']['Price'][] = configurationShopDataSource::jsapiCreateDataSourceCondition($filterOptionsApplied['filter_commonPriceMax'], '<=');
        else
            $filterOptionsApplied['filter_commonPriceMax'] = $filterOptionsAvailable['filter_commonPriceMax'];

        if ($filterOptionsApplied['filter_commonPriceMax'] > $filterOptionsApplied['filter_commonPriceMin'] && $filterOptionsApplied['filter_commonPriceMin'] >= $filterOptionsAvailable['filter_commonPriceMin'])
            $dataConfigProducts['condition']['Price'][] = configurationShopDataSource::jsapiCreateDataSourceCondition($filterOptionsApplied['filter_commonPriceMin'], '>=');
        else
            $filterOptionsApplied['filter_commonPriceMin'] = $filterOptionsAvailable['filter_commonPriceMin'];

        // var_dump($filterOptionsApplied);
        if (count($filterOptionsApplied['filter_commonFeatures']))
            $dataConfigProducts['condition']["FeatureID"] = configurationShopDataSource::jsapiCreateDataSourceCondition($filterOptionsApplied['filter_commonFeatures'], 'in');

        if (count($filterOptionsApplied['filter_commonStatus']))
            $dataConfigProducts['condition']["shop_products.Status"] = configurationShopDataSource::jsapiCreateDataSourceCondition($filterOptionsApplied['filter_commonStatus'], 'in');

        // filter: brands
        if (count($filterOptionsApplied['filter_categoryBrands']))
            $dataConfigProducts['condition']['OriginID'] = configurationShopDataSource::jsapiCreateDataSourceCondition($filterOptionsApplied['filter_categoryBrands'], 'in');

        // var_dump($dataConfigProducts);
        // get products
        $dataProducts = $this->getCustomer()->fetch($dataConfigProducts);
        // get category info according to product filter
        if (isset($dataConfigProducts['condition']['Price']))
            $dataConfigCategoryInfo['condition']['Price'] = $dataConfigProducts['condition']['Price'];
        // $dataConfigCategoryInfo['condition'] = new ArrayObject($dataConfigProducts['condition']);
        $dataCategoryInfo = $this->getCustomer()->fetch($dataConfigCategoryInfo);

        $products = array();
        if (!empty($dataProducts))
            foreach ($dataProducts as $val)
                $products[] = $this->_getProductByID($val['ID']);

        $productsInfo = array();
        if (!empty($dataCategoryInfo))
            foreach ($dataCategoryInfo as $val)
                $productsInfo[] = $this->_getProductByID($val['ID']);

        // adjust brands, categories and features
        $brands = array();
        $categories = array();
        $statuses = array();//$this->getCustomerDataBase()->getTableStatusFieldOptions(configurationShopDataSource::$Table_ShopProducts);
        $features = array();
        foreach ($filterOptionsAvailable['filter_categoryBrands'] as $brand) {
            $brands[$brand['ID']] = $brand;
            $brands[$brand['ID']]['ProductCount'] = 0;
        }
        foreach ($filterOptionsAvailable['filter_categorySubCategories'] as $category) {
            $categories[$category['ID']] = $category;
            $categories[$category['ID']]['ProductCount'] = 0;
        }
        foreach ($filterOptionsAvailable['filter_commonStatus'] as $status) {
            $statuses[$status]['ID'] = $status;
            $statuses[$status]['ProductCount'] = 0;
        }

        if ($productsInfo)
            foreach ($productsInfo as $obj) {
                $OriginID = $obj['OriginID'];
                $CategoryID = $obj['CategoryID'];
                $status = $obj['Status'];
                if (isset($statuses[$status]))
                    $statuses[$status]['ProductCount']++;
                if (isset($brands[$OriginID]))
                    $brands[$OriginID]['ProductCount']++;
                if (isset($categories[$CategoryID]))
                    $categories[$CategoryID]['ProductCount']++;
                foreach ($obj['Features'] as $featureKey => $feature) {
                    if (isset($features[$featureKey]))
                        $features[$featureKey]['ProductCount']++;
                    else {
                        $features[$featureKey]['Name'] = $feature;
                        $features[$featureKey]['ProductCount'] = 1;
                    }
                }
            }

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
                "count" => count($dataCategoryInfo)
            )
        );
        // return data object
        return $data;
    }

    // promo
    // -----------------------------------------------
    private function _getPromoByID ($promoID) {
        $config = configurationShopDataSource::jsapiShopGetPromoByID($promoID);
        $data = $this->getCustomer()->fetch($config);
        $data['ID'] = intval($data['ID']);
        $data['Discount'] = floatval($data['Discount']);
        return $data;
    }

    private function _getPromoByHash ($hash, $activeOnly = false) {
        $config = configurationShopDataSource::jsapiShopGetPromoByHash($hash, $activeOnly);
        $data = $this->getCustomer()->fetch($config);
        $data['ID'] = intval($data['ID']);
        $data['Discount'] = floatval($data['Discount']);
        return $data;
    }

    private function _createPromo ($reqData) {
        if (!$this->ifYou('CanAdmin') && !$this->ifYou('CanCreate')) {
            return glWrap("AccessDenied");
        }
    }

    private function _updatePromo ($reqData) {
        if (!$this->ifYou('CanAdmin') && !$this->ifYou('CanEdit')) {
            return glWrap("AccessDenied");
        }
    }

    // session data
    // -----------------------------------------------
    private function _setSessionPromo ($promo) {
        $_SESSION[$this->_listKey_Promo] = $promo;
    }

    private function _getSessionPromo () {
        if (!isset($_SESSION[$this->_listKey_Promo]))
            $_SESSION[$this->_listKey_Promo] = null;
        return $_SESSION[$this->_listKey_Promo];
    }

    private function _resetSessionPromo () {
        $_SESSION[$this->_listKey_Promo] = null;
    }

    private function _setSessionOrderProducts ($order) {
        $_SESSION[$this->_listKey_Cart] = $order;
    }

    private function _getSessionOrderProducts () {
        if (!isset($_SESSION[$this->_listKey_Cart]))
            $_SESSION[$this->_listKey_Cart] = array();
        return $_SESSION[$this->_listKey_Cart];
    }

    private function _resetSessionOrderProducts () {
        $_SESSION[$this->_listKey_Cart] = array();
    }


    // ----------------------------------------
    // requests
    // ----------------------------------------

    public function get_shop_product (&$resp, $req) {
        $resp = $this->_getProductByID($req->get['id']);
    }

    public function get_shop_overview (&$resp) {
        $resp['all_products'] = $this->_getStats_ProductsOverview();
        $resp['all_orders'] = $this->_getStats_OrdersOverview();
        $resp['products_todays'] = $this->_getProducts_Todays();
        $resp['products_popular'] = $this->_getProducts_TopPopular();
        $resp['products_non_popular'] = $this->_getProducts_TopNonPopular();
        $resp['orders_all_new'] = $this->_getOrders_ByStatus('NEW');
        $resp['orders_todays'] = $this->_getOrders_Todays();
        $resp['orders_expired'] = $this->_getOrders_Expired();
    }

    public function get_shop_location (&$resp, $req) {
        if (!isset($req->get['productID']) && !isset($req->get['categoryID'])) {
            $resp['error'] = 'The request must contain at least one of parameters: "productID" or "categoryID"';
            return;
        }
        $resp['location'] = $this->_getCatalogLocation(libraryRequest::fromGET('productID'), libraryRequest::fromGET('categoryID'));
    }

    public function get_shop_products (&$resp, $req) {
        if (!empty($req->get['status'])) {
            $resp = $this->_getProducts_ByStatus($req['status']);
            return;
        }
        if (!empty($req->get['type'])) {
            switch ($req->get['type']) {
                case "latest":
                    $resp["items"] = $this->_getProducts_Latest();
                    break;
                case "popular":
                    $resp["items"] = $this->_getProducts_TopPopular();
                    break;
                case "non_popular":
                    $resp["items"] = $this->_getProducts_TopNonPopular();
                    break;
                case "sale":
                    $resp["items"] = $this->_getProducts_Sale();
                    break;
                case "uncompleted":
                    $resp["items"] = $this->_getProducts_Uncompleted();
                    break;
            }
            return;
        }

        $resp['error'] = '"type" or "status" is missed in the request';
    }

    public function get_shop_catalog (&$resp, $req) {
        if (!empty($req->get['type'])) {
            switch ($req->get['type']) {
                case "tree":
                    $resp['tree'] = $this->_getCatalogTree();
                    break;
                case "browse":
                    $resp['browse'] = $this->_getCatalogBrowse();
                    break;
            }
            return;
        }

        $resp['error'] = "MissedParameter_type";
    }

    public function get_shop_wish (&$resp) {
        $resp['items'] = isset($_SESSION[$this->_listKey_Wish]) ? $_SESSION[$this->_listKey_Wish] : array();
    }

    public function post_shop_wish (&$resp, $req) { 
        $resp['items'] = isset($_SESSION[$this->_listKey_Wish]) ? $_SESSION[$this->_listKey_Wish] : array();
        if (isset($req->data['productID'])) {
            $productID = $req->data['productID'];
            if (!isset($resp['items'][$productID])) {
                $product = $this->_getProductByID($productID);
                $resp['items'][$productID] = $product;
                $_SESSION[$this->_listKey_Wish] = $resp['items'];
            }
        } else
            $resp['error'] = "MissedParameter_productID";
        // $resp['req'] = $req;
    }

    public function delete_shop_wish (&$resp, $req) {
        $resp['items'] = isset($_SESSION[$this->_listKey_Wish]) ? $_SESSION[$this->_listKey_Wish] : array();
        if (isset($req->get['productID'])) {
            $productID = $req->get['productID'];
            if ($productID === "*") {
                $resp['items'] = array();
            } elseif (isset($resp['items'][$productID])) {
                unset($resp['items'][$productID]);
            }
            $_SESSION[$this->_listKey_Wish] = $resp['items'];
        }
    }

    private function __productIsInWishList ($id) {
        $list = array();
        $this->get_shop_wish($list);
        return isset($list['items'][$id]);
    }

    public function get_shop_compare (&$resp) {
        $resp['items'] = isset($_SESSION[$this->_listKey_Compare]) ? $_SESSION[$this->_listKey_Compare] : array();
        $resp['limit'] = 10;
    }

    public function post_shop_compare (&$resp, $req) {
        $resp['items'] = isset($_SESSION[$this->_listKey_Compare]) ? $_SESSION[$this->_listKey_Compare] : array();
        if (count($resp['items']) >= 10) {
            $resp['error'] = "ProductLimitExceeded";
            return;
        }
        if (isset($req->data['productID'])) {
            $productID = $req->data['productID'];
            if (!isset($resp['items'][$productID])) {
                $product = $this->_getProductByID($productID);
                $resp['items'][$productID] = $product;
                $_SESSION[$this->_listKey_Compare] = $resp['items'];
            }
        }
    }

    public function delete_shop_compare (&$resp, $req) {
        $resp['items'] = isset($_SESSION[$this->_listKey_Compare]) ? $_SESSION[$this->_listKey_Compare] : array();
        if (isset($req->get['productID'])) {
            $productID = $req->get['productID'];
            if ($productID === "*") {
                $resp['items'] = array();
            } elseif (isset($resp['items'][$productID])) {
                unset($resp['items'][$productID]);
            }
            $_SESSION[$this->_listKey_Compare] = $resp['items'];
        }
    }

    private function __productIsInCompareList ($id) {
        $list = array();
        $this->get_shop_compare($list);
        return isset($list['items'][$id]);
    }













    public function get_shop_orders (&$resp, $req) {
        if (!empty($req->get['status'])) {
            $resp = $this->_getOrders_ByStatus($req->get['status']);
            return;
        }
        if (!empty($req->get['type'])) {
            switch ($req->get['type']) {
                case "expired":
                    $resp = $this->_getOrders_Expired();
                    break;
                case "todays":
                    $resp = $this->_getOrders_Todays();
                    break;
                case "browse":
                    $resp = $this->_getOrders_Browse($req);
                    break;
            }
            return;
        }

        $resp['error'] = '"type" or "status" is missed in the request';
    }

    public function get_shop_order (&$resp, $req) {
        if (isset($req->get['id']) && $req->get['id'] !== "temp") {
            if ($this->ifYou('CanAdmin'))
                $resp = $this->_getOrderByID($req->get['id']);
            else
                $resp['error'] = 'AccessDenied';
            return;
        } else if (isset($req->get['hash'])) {
            $resp = $this->_getOrderByHash($req->get['hash']);
            return;
        } else {
            $resp = $this->_getOrderTemp();
        }
        // $resp['error'] = '"id" or "hash" is missed in the request';
    }

    // create new product in the shopping cart list
    public function post_shop_order (&$resp, $req) {
        $resp = $this->_createOrder($req->data);
    }

    // modify existed product quantity in the shopping cart list
    public function patch_shop_order (&$resp, $req) {
        // var_dump($req);
        // var_dump($_SERVER['REQUEST_METHOD']);
        // var_dump($_POST);
        // var_dump(file_get_contents('php://input'));
        // $options = array();
        if (isset($req->data['productID'])) {
            $sessionOrderProducts = $this->_getSessionOrderProducts();
            // $items = empty($order['items']) ? array() : $order['items'];
            $productID = $req->data['productID'];
            $newQuantity = floatval($req->data['_orderQuantity']);
            if (isset($sessionOrderProducts[$productID])) {
                $sessionOrderProducts[$productID]['_orderQuantity'] = $newQuantity;
                if ($sessionOrderProducts[$productID]['_orderQuantity'] <= 0)
                    unset($sessionOrderProducts[$productID]);
                $this->_setSessionOrderProducts($sessionOrderProducts);
            } elseif ($newQuantity > 0) {
                // $product = $this->_getProductByID($productID);
                $product['ID'] = $productID;
                $product['_orderQuantity'] = $newQuantity;
                $sessionOrderProducts[$productID] = $product;
                $this->_setSessionOrderProducts($sessionOrderProducts);
            } elseif ($req->data['productID'] === "*") {
                $this->_resetSessionOrderProducts();
            }
            // $order['items'] = $items;
        } elseif (isset($req->data['promo'])) {
            if (empty($req->data['promo']))
                $this->_resetSessionPromo();
            else
                $this->_setSessionPromo($this->_getPromoByHash($req->data['promo'], true));
            // if ($req->data['promo'] === false)
            //     $options['promo'] = array();
            // else
            //     $options['promo'] = $this->_getPromoByHash($req->data['promo'], true);
        }
        // else {
        //     $options['useBackup'] = true;
        // }
        // var_dump($sessionOrder);
        $resp = $this->_getOrderTemp();
    }

    // removes particular product or clears whole shopping cart
    public function delete_shop_cart (&$resp, $req) {
        if (!glIsToolbox()) {
            $resp['error'] = 'AccessDenied';
            return;
        }
        // global $PHP_INPUT;
        // var_dump($req);
        // var_dump($PHP_INPUT);
        if (!empty($req->get['id'])) {
            $OrderID = intval($req->get['id']);
            $resp = $this->_disableOrderByID($OrderID);
            return;
        }
        $resp['error'] = 'MissedParameter_id';
    }

    private function __productCountInCart ($id) {
        // $order = $this->_getOrderTemp();
        $sessionOrderProducts = $this->_getSessionOrderProducts();
        return isset($sessionOrderProducts[$id]) ? $sessionOrderProducts[$id]['_orderQuantity'] : 0;
    }

    private function __attachOrderDetails (&$order) {
        // echo "__attachOrderDetails";
        if (empty($order))
            return;

        $orderID = isset($order['ID']) ? $order['ID'] : null;
        $order['promo'] = null;
        $order['account'] = null;
        $order['address'] = null;
        $productItems = array();
        // var_dump($order);
        // if orderID is set then the order is saved
        if (isset($orderID) && !isset($order['temp'])) {
            // attach account and address
            if ($this->getCustomer()->hasPlugin('account')) {
                if (isset($order['AccountAddressesID']))
                    $order['address'] = $this->getCustomer()->getPlugin('account')->getAddressByID($order['AccountAddressesID']);
                if (isset($order['AccountID']))
                    $order['account'] = $this->getCustomer()->getPlugin('account')->getAccountByID($order['AccountID']);
                unset($order['AccountID']);
                unset($order['AccountAddressesID']);
            }
            // get promo
            if (!empty($order['PromoID']))
                $order['promo'] = $this->_getPromoByID($order['PromoID']);
            // $order['items'] = array();
            $configBoughts = configurationShopDataSource::jsapiShopBoughtsGet($orderID);
            $boughts = $this->getCustomer()->fetch($configBoughts) ?: array();
            if (!empty($boughts))
                foreach ($boughts as $soldItem) {
                    $product = $this->_getProductByID($soldItem['ProductID']);
                    // save current product info
                    $product["CurrentIsPromo"] = $product['IsPromo'];
                    $product["CurrentPrice"] = $product['Price'];
                    $product["CurrentSellingPrice"] = $product['SellingPrice'];
                    // restore product info at purchase moment
                    $product["Price"] = floatval($soldItem['Price']);
                    $product["SellingPrice"] = floatval($soldItem['SellingPrice']);
                    $product["IsPromo"] = intval($soldItem['IsPromo']) === 1;
                    // get purchased product quantity
                    $product["_orderQuantity"] = floatval($soldItem['Quantity']);
                    // actual price (with discount if promo is active)
                    // $price = isset($product['DiscountPrice']) ? $product['DiscountPrice'] : $product['Price'];
                    // set product gross and net totals
                    $product["_orderProductSubTotal"] = $product['Price'] * $product['_orderQuantity'];
                    $product["_orderProductTotal"] = $product['SellingPrice'] * $product['_orderQuantity'];
                    // add into list
                    $productItems[$product['ID']] = $product;
                }
        } else {
            // $productItems = !empty($order['items']) ? $order['items'] : array();
            $sessionPromo = $this->_getSessionPromo();
            $sessionOrderProducts = $this->_getSessionOrderProducts();
            // re-validate promo
            if (!empty($sessionPromo) && isset($sessionPromo['Code'])) {
                $sessionPromo = $this->_getPromoByHash($sessionPromo['Code'], true);
                if (!empty($sessionPromo) && isset($sessionPromo['Code'])) {
                    $this->_setSessionPromo($sessionPromo);
                    $order['promo'] = $sessionPromo;
                } else {
                    $this->_resetSessionPromo();
                    $order['promo'] = null;
                }
            }
            // get prodcut items
            foreach ($sessionOrderProducts as $purchasingProduct) {
                $product = $this->_getProductByID($purchasingProduct['ID']);
                // actual price (with discount if promo is active)
                // set product gross and net totals
                // get purchased product quantity
                $product["_orderQuantity"] = $purchasingProduct['_orderQuantity'];
                $product["_orderProductSubTotal"] = $product['Price'] * $purchasingProduct['_orderQuantity'];
                $product["_orderProductTotal"] = $product['SellingPrice'] * $purchasingProduct['_orderQuantity'];
                // add into list
                $productItems[$product['ID']] = $product;
            }
        }
        // append info
        $info = array(
            "subTotal" => 0.0,
            "total" => 0.0,
            "productCount" => 0,
            "productUniqueCount" => count($productItems),
            "hasPromo" => isset($order['promo']['Discount']) && $order['promo']['Discount'] > 0,
            "allProductsWithPromo" => true
        );
        // calc order totals
        foreach ($productItems as $product) {
            // update order totals
            $info["total"] += floatval($product['_orderProductTotal']);
            $info["subTotal"] += floatval($product['_orderProductSubTotal']);
            $info["productCount"] += intval($product['_orderQuantity']);
            $info["allProductsWithPromo"] = $info["allProductsWithPromo"] && $product['IsPromo'];
        }
        $order['items'] = $productItems;
        $order['info'] = $info;
    }
}

?>