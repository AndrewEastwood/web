<?php
namespace static_\plugins\system\api;

use \engine\lib\api as API;
use \engine\lib\validate as Validate;
use \engine\lib\path as Path;
use \engine\lib\request as Request;
use Exception;

class customers extends API {

    var $customersCache = array();


    public function setCustomerSessionID ($id) {
        $_SESSION['site_id'] = $id;
        return $id;
    }

    public function getCustomerSessionID () {
        return isset($_SESSION['site_id']) ? $_SESSION['site_id'] : null;
    }

    public function loadActiveCustomer () {
        global $app;
        // echo 'loadActiveCustomer';
        // try to load session customer
        $isSwitched = false;
        $sessionID = $this->getCustomerSessionID();
        if (!is_null($sessionID))
            $isSwitched = $this->switchToCustomerByID($sessionID);
        if (!$isSwitched) {
            // otherwise try to load default customer
            if (!$this->switchToDefaultCustomer()) {
                throw new Exception("Unable to load default customer", 1);
            }
        }
    }

    public function switchToDefaultCustomer () {
        global $app;
        return $this->switchToCustomerByName($app->customerName());
    }

    public function switchToCustomerByName ($customerName) {
        if (isset($this->customersCache[$customerName])) {
            $this->setCustomerSessionID($this->customersCache[$customerName]['ID']);
            // $_SESSION['site_id'] = ;
            return $this->customersCache[$customerName];
        }
        if (empty($customerName)) {
            return false;
        }
        $customer = $this->data->fetchCustomerByName($customerName);
        if (!isset($customer)) {
            return false;
        }
        $ID = $customer['ID'];
        // $_SESSION['site_id'] = $ID;
        $this->setCustomerSessionID($ID);
        $this->customersCache[$ID] = $customer;
        $this->customersCache[$customer['HostName']] = $customer;
        return $customer;
    }

    public function switchToCustomerByID ($ID) {
        if (isset($this->customersCache[$ID])) {
            $this->setCustomerSessionID($ID);
            // $_SESSION['site_id'] = $ID;
            return $this->customersCache[$ID];
        }
        if (empty($ID)) {
            return false;
        }
        // try to load customer by given ID
        $customer = $this->data->fetchCustomerByID($ID);
        if (!isset($customer)) {
            return false;
        }
        $this->setCustomerSessionID($ID);
        // $_SESSION['site_id'] = $ID;
        $this->customersCache[$ID] = $customer;
        $this->customersCache[$customer['HostName']] = $customer;
        return $customer;
    }

    public function getDefaultCustomer () {
        global $app;
        $customerName = $app->customerName();
        // QDfe6#(9
        if (isset($this->customersCache[$customerName])) {
            return $this->customersCache[$customerName];
        }
        $customer = $this->data->fetchCustomerByName($customerName);
        if (!isset($customer)) {
            return false;
        }
        $ID = $customer['ID'];
        $this->customersCache[$ID] = $customer;
        $this->customersCache[$customer['HostName']] = $customer;
        return $customer;
    }

    public function getRuntimeCustomer () {
        // echo 'getRuntimeCustomer';
        global $app;
        $customer = null;
        // we can access to all customer via toolbox only
        if ($app->isToolbox()) {
            // ability to switch customers
            $ID = $this->getCustomerSessionID();// $_SESSION['site_id'];
            if (!isset($this->customersCache[$ID])) {
                throw new Exception("Cannot get runtime customer by given id: " . $ID, 1);
            }
            $customer = $this->customersCache[$ID];
            // if ($activeCustomerID >= 0)
            //     $customer = $this->data->fetchCustomerByID($activeCustomerID);
            // else {
            //     $customer = $this->data->fetchCustomerByName($app->customerName());
            //     if (isset($customer['ID'])) {
            //         $this->setCustomerSessionID($customers['ID']);
            //         // $_SESSION['site_id'] = $customers['ID'];
            //     }
            // }
        } else {
            $customer = $this->data->fetchCustomerByName($app->customerName());
        }
        return $customer;
    }

    public function getRuntimeCustomerID () {
        $ID = $this->getCustomerSessionID();
        if (isset($this->customersCache[$ID])) {
            return $ID;
        }
        unset($this->customersCache[$ID]);
        throw new Exception("Exception at getRuntimeCustomerID. Cannot find customer by session id=" . $ID, 1);
    }

    public function isRunningCustomerDefault () {
        $defCustomer = $this->getDefaultCustomer();
        $runtimeID = $this->getRuntimeCustomerID();
        // var_dump($defCustomer);
        // var_dump($runtimeID);
        return $runtimeID === $defCustomer['ID'];
    }

    public function getCustomerSettings () {
        global $app;

        $customer = $this->getRuntimeCustomer();
        $urls = $app->getSettings('urls');
        $staticPath = $urls['static'];
        $staticPathCustomer = $staticPath . Path::createPath(Path::getDirNameCustomer(), $app->displayCustomer());
        $logoUrl = $staticPathCustomer . '/img/logo.png';
        if (!empty($customer['Logo'])) {
            $logoUrl = $customer['Logo']['normal'];
        }
        $settings = array(
            'lang' => $customer['Lang'],
            'locale' => $customer['Locale'],
            'plugins' => $customer['Plugins'],
            'homepage' => $customer['HomePage'],
            'host' => $customer['HostName'],
            'scheme' => $customer['Protocol'],
            'title' => $customer['Title'],
            'staticPathCustomer' => $staticPathCustomer,
            'logoUrl' => $logoUrl
        );
        return (object)$settings;
    }

    // private function __adjustCustomer (&$customer) {
    //     // adjusting
    //     $ID = intval($customer['ID']);
    //     $customer['ID'] = $ID;
    //     // $customer['Settings'] = API::getAPI('system:settings')->getSettingsByCustomerID($ID);
    //     $customer['isBlocked'] = $customer['Status'] != 'ACTIVE';
    //     $customer['Plugins'] = explode(",", $customer['Plugins']);
    //     // var_dump($customer);
    //     if (!empty($customer['Logo'])) {
    //         $customer['Logo'] = array(
    //             'name' => $customer['Logo'],
    //             'normal' => '/' . Path::getUploadDirectory() . $this->data->getCustomerUploadInnerImagePath($customer['HostName'], $customer['Logo']),
    //             'sm' => '/' . Path::getUploadDirectory() . $this->data->getCustomerUploadInnerImagePath($customer['HostName'], $customer['Logo'], 'sm'),
    //             'xs' => '/' . Path::getUploadDirectory() . $this->data->getCustomerUploadInnerImagePath($customer['HostName'], $customer['Logo'], 'xs')
    //         );
    //     }
    //     return $customer;
    // }

    // public function getCustomerByID ($ID) {
    //     return $this->data->fetchCustomerByID($ID);
    //     // global $app;
    //     // $config = $this->data->getCustomer($ID);
    //     // $customer = $app->getDB()->query($config, false);
    //     // return $this->__adjustCustomer($customer);
    // }

    // public function getCustomerByName ($customerName) {
    //     return $this->data->fetchCustomerByName($customerName);
    //     // global $app;
    //     // $config = $this->data->getCustomer();
    //     // $config['condition']['HostName'] = $app->getDB()->createCondition($customerName);
    //     // $customer = $app->getDB()->query($config, false);
    //     // // echo 2121212;
    //     // // var_dump($customer);
    //     // if (empty($customer)) {
    //     //     return null;
    //     // }
    //     // return $this->__adjustCustomer($customer);
    // }

    // public function getCustomers_List (array $options = array()) {
    //     return $this->data->fetchCustomerDataList($options);
    //     // global $app;
    //     // $config = $this->data->getCustomerList($options);
    //     // $self = $this;
    //     // $callbacks = array(
    //     //     "parse" => function ($items) use($self) {
    //     //         $_items = array();
    //     //         foreach ($items as $key => $orderRawItem) {
    //     //             $_items[] = $this->data->fetchCustomerByID($orderRawItem['ID']);
    //     //         }
    //     //         return $_items;
    //     //     }
    //     // );
    //     // $options['useCustomerID'] = false;
    //     // $dataList = $app->getDB()->queryMatchedIDs($config, $options, $callbacks);
    //     // return $dataList;
    // }

    public function createCustomer ($reqData) {
        // global $app;
        // $result = array();
        // $errors = array();
        // $success = false;
        // $customerID = null;
        $r = null;

        $validatedDataObj = Validate::getValidData($reqData, array(
            'HostName' => array('string', 'notEmpty', 'max' => 100),
            'HomePage' => array('string', 'skipIfUnset', 'max' => 200, 'defaultValueIfEmpty' => 'localhost'),
            'Title' => array('string', 'skipIfUnset', 'max' => 200, 'defaultValueIfEmpty' => 'Happy Site :)'),
            'AdminTitle' => array('string', 'skipIfUnset', 'max' => 200, 'defaultValueIfEmpty' => 'MPWS Admin'),
            'file1' => array('string', 'skipIfEmpty'),
            'Lang' => array('string', 'skipIfUnset', 'max' => 50, 'defaultValueIfEmpty' => 'en'),
            'Locale' => array('string', 'skipIfUnset', 'max' => 10, 'defaultValueIfEmpty' => 'en_us'),
            'Protocol' => array('string', 'skipIfUnset', 'max' => 10, 'defaultValueIfEmpty' => 'http'),
            'SnapshotURL' => array('string', 'skipIfUnset', 'max' => 300),
            'SitemapURL' => array('string', 'skipIfUnset', 'max' => 500),
            'Plugins' => array('string', 'skipIfUnset', 'max' => 500, 'defaultValueIfEmpty' => 'system')
        ));

        if ($validatedDataObj->errorsCount == 0) {
            // try {

                $validatedValues = $validatedDataObj->validData;

                // set logo
                if (!empty($validatedValues['Logo'])) {
                    $newFileName = uniqid(time());
                    $fileName = $validatedValues['Logo'];
                    $smImagePath = 'sm' . Path::getDirectorySeparator() . $fileName;
                    $xsImagePath = 'xs' . Path::getDirectorySeparator() . $fileName;
                    $normalImagePath = $fileName;
                    $uploadInfo = Path::moveTemporaryFile($smImagePath, $this->data->getCustomerUploadInnerDir('sm'), $newFileName);
                    $uploadInfo = Path::moveTemporaryFile($xsImagePath, $this->data->getCustomerUploadInnerDir('xs'), $newFileName);
                    $uploadInfo = Path::moveTemporaryFile($normalImagePath, $this->data->getCustomerUploadInnerDir(), $newFileName);
                    $validatedValues['Logo'] = $uploadInfo['filename'];
                }
                unset($validatedValues['file1']);

                // adjust plugins
                $pList = array('system');
                if (isset($validatedValues['Plugins']) && !empty($validatedValues['Plugins'])) {
                    $reqPluginsList = explode(',', strtolower(trim($validatedValues['Plugins'])));
                    foreach ($reqPluginsList as $key => $value) {
                        $value = trim($value);
                        if (!empty($value) && $value !== 'system') {
                            $pList[] = $value;
                        }
                    }
                }
                $validatedValues['Plugins'] = implode(',', $pList);

                // $app->getDB()->beginTransaction();

                $r = $this->data->createCustomer($validatedValues);
                // $app->getDB()->query($configCreateCustomer, false) ?: null;

                if ($r->isEmptyResult()) {
                    $r->addError("CustomerCreateError");
                }

                // $app->getDB()->commit();

                // $success = true;
            // } catch (Exception $e) {
            //     // $app->getDB()->rollBack();
            //     // $errors[] = $e->getMessage();
            //     $r->addError($e->getMessage());
            // }
        } else {
            $r->addErrors($validatedDataObj->errorMessages);
        }

        if ($r->isSuccess() && $r->hasResult()) {
            $item = $this->data->fetchAddress($r->getResult());
            $r->setResult($item);
        }
        return $r->toArray();
    }

    public function updateCustomer ($customerID, $reqData, $isPatch = false) {
        $r = null;

        $validatedDataObj = Validate::getValidData($reqData, array(
            'HostName' => array('string', 'skipIfUnset', 'max' => 100),
            'HomePage' => array('string', 'skipIfUnset', 'max' => 200),
            'Title' => array('string', 'skipIfUnset', 'max' => 200),
            'AdminTitle' => array('string', 'skipIfUnset', 'max' => 200),
            'Status' => array('string', 'skipIfEmpty'),
            'file1' => array('string', 'skipIfUnset'),
            'Lang' => array('string', 'skipIfUnset', 'max' => 50),
            'Locale' => array('string', 'skipIfUnset', 'max' => 10),
            'Protocol' => array('string', 'skipIfUnset', 'max' => 10),
            'SnapshotURL' => array('string', 'skipIfUnset', 'max' => 300),
            'SitemapURL' => array('string', 'skipIfUnset', 'max' => 500),
            'Plugins' => array('string', 'skipIfUnset', 'max' => 500)
        ));

        if ($validatedDataObj->errorsCount == 0)
            try {

                $validatedValues = $validatedDataObj->validData;

                // update logo
                if (isset($reqData['file1'])) {
                    $customer = $this->data->fetchCustomerByID($customerID);

                    $currentFileName = empty($customer['Logo']) ? "" : $customer['Logo']['name'];
                    $newFileName = null;

                    if (!empty($validatedValues['file1'])) {
                        $newFileName = $validatedValues['file1'];
                    }

                    if ($newFileName !== $currentFileName) {
                        if (empty($newFileName) && !empty($currentFileName)) {
                            Path::deleteUploadedFile($this->data->getCustomerUploadInnerImagePath($customer['HostName'], $currentFileName, 'sm'));
                            Path::deleteUploadedFile($this->data->getCustomerUploadInnerImagePath($customer['HostName'], $currentFileName, 'xs'));
                            Path::deleteUploadedFile($this->data->getCustomerUploadInnerImagePath($customer['HostName'], $currentFileName));
                            $validatedValues['Logo'] = null;
                        }
                        if (!empty($newFileName)) {
                            $currentFileName = $newFileName;
                            $newFileName = uniqid(time());
                            $smImagePath = 'sm' . Path::getDirectorySeparator() . $currentFileName;
                            $xsImagePath = 'xs' . Path::getDirectorySeparator() . $currentFileName;
                            $normalImagePath = $currentFileName;
                            $uploadInfo = Path::moveTemporaryFile($smImagePath, $this->data->getCustomerUploadInnerDir($customer['HostName'], 'sm'), $newFileName);
                            $uploadInfo = Path::moveTemporaryFile($xsImagePath, $this->data->getCustomerUploadInnerDir($customer['HostName'], 'xs'), $newFileName);
                            $uploadInfo = Path::moveTemporaryFile($normalImagePath, $this->data->getCustomerUploadInnerDir($customer['HostName']), $newFileName);
                            $validatedValues['Logo'] = $uploadInfo['filename'];
                        }
                    }
                }

                // adjust fields
                if (array_key_exists('file1', $validatedValues)) {
                    unset($validatedValues['file1']);
                }

                // adjust plugins
                if (isset($validatedValues['Plugins'])) {
                    if (API::getAPI('system:auth')->ifYouCan('Maintain')) {
                        $pList = array('system');
                        if (!empty($validatedValues['Plugins'])) {
                            $reqPluginsList = explode(',', strtolower(trim($validatedValues['Plugins'])));
                            foreach ($reqPluginsList as $key => $value) {
                                $value = trim($value);
                                if (!empty($value) && $value !== 'system') {
                                    $pList[] = $value;
                                }
                            }
                        }
                        $validatedValues['Plugins'] = implode(',', $pList);
                    } else {
                        unset($validatedValues['Plugins']);
                    }
                }

                // $app->getDB()->beginTransaction();

                // $configCreateCustomer = 
                $r = $this->data->updateCustomer($customerID, $validatedValues);
                // $app->getDB()->query($configCreateCustomer, false) ?: null;

                // $app->getDB()->commit();

                // $success = true;
            } catch (Exception $e) {
                $r->fail()
                    ->addError($e->getMessage());
                // $app->getDB()->rollBack();
                // $errors[] = $e->getMessage();
            }
        else {            $r->addErrors($validatedDataObj->errorMessages);
        }

        $item = $this->data->fetchCustomerByID($customerID);
        $r->setResult($item);
        // $result['errors'] = $errors;
        // $result['success'] = $success;

        return $r->toArray();

        // $result = $this->data->fetchCustomerByID($customerID);
        // $result['errors'] = $errors;
        // $result['success'] = $success;

        // return $result;
    }

    public function archiveCustomer ($customerID) {
        // global $app;
        // $errors = array();
        // $success = false;


        $r = $this->data->archiveCustomer($customerID);
        $item = $this->data->fetchCustomerByID($customerID);
        $r->setResult($item);
        return $r->toArray();


        // try {
        //     $app->getDB()->beginTransaction();

        //     $config = $this->data->archiveCustomer($customerID);
        //     $app->getDB()->query($config, false);

        //     $app->getDB()->commit();

        //     $success = true;
        // } catch (Exception $e) {
        //     $app->getDB()->rollBack();
        //     $errors[] = 'CustomerArchiveError';
        // }

        // $result = $this->data->fetchCustomerByID($customerID);
        // $result['errors'] = $errors;
        // $result['success'] = $success;
        // return $result;
    }

    public function get ($req, $resp) {
        // var_dump($req);

        // for specific customer item
        // by id
        if (Request::hasRequestedID()) {
            $resp->setResponse($this->data->fetchCustomerByID($req->id));
            return;
        }
        // or by ExternalKey
        if (Request::hasRequestedExternalKey()) {
            $resp->setResponse($this->data->fetchCustomerByName($req->externalKey));
            return;
        }
        // for the case when we have to fecth list with customers
        if (Request::noRequestedItem()) {
            $resp->setResponse($this->data->fetchCustomerDataList($req->get));
        }
        // if (!empty($req->id)) {
        //     if (is_numeric($req->id)) {
        //         $customerID = intval($req->id);
        //         $resp->setResponse($this->data->fetchCustomerByID($customerID));
        //     } else {
        //         $resp->setResponse($this->data->fetchCustomerByName($req->id));
        //     }
        // } else {
        //     $resp->setResponse($this->data->fetchCustomerDataList($req->get));
        // }
    }

    public function post ($req, $resp) {
        if (API::getAPI('system:auth')->ifYouCanCreate()) {
            return $resp->setAccessError();
        }
        if (isset($req->data['switchto'])) {
            if (API::getAPI('system:auth')->ifYouCanDoAnythingYouWant()) {
                if (is_numeric($req->data['switchto'])) {
                    $customerID = intval($req->data['switchto']);
                    $resp->setResponse($this->switchToCustomerByID($customerID));
                } else {
                    $resp->setWrongItemIdError();
                    return;
                }
            } else {
                $resp->setAccessError();
                return;
            }
        } else {
            $resp->setResponse($this->createCustomer($req->data));
        }
        // $this->_getOrSetCachedState('changed:product', true);
    }

    public function put ($req, $resp) {
        // if (!API::getAPI('system:auth')->ifYouCan('Maintain') &&
        //     !API::getAPI('system:auth')->ifYouCanAll('Admin', 'Edit')) {
        //     return $resp->setAccessError();
        //     return;
        // }
        if (API::getAPI('system:auth')->ifYouCanEdit()) {
            return $resp->setAccessError();
        }

        // for specific customer item
        // by id
        if (Request::hasRequestedID()) {
            $resp->setResponse($this->updateCustomer($req->id, $req->data));
            return;
        }
        $resp->setWrongItemIdError();
        // // for the case when we have to fecth list with customers
        // if (Request::noRequestedItem()) {
        //     $resp->setWrongItemIdError();
        //     return;
        // }

        // if (empty($req->id)) {
        //     $resp->setWrongItemIdError();
        // } else {
        //     if (is_numeric($req->id)) {
        //         $customerID = intval($req->id);
        //         $resp->setResponse($this->updateCustomer($customerID, $req->data));
        //     } else {
        //         $resp->setWrongItemIdError();
        //     }
        //     // $this->_getOrSetCachedState('changed:product', true);
        // }
    }

    public function patch ($req, $resp) {
        if (API::getAPI('system:auth')->ifYouCanEdit()) {
            return $resp->setAccessError();
        }
        // if (!API::getAPI('system:auth')->ifYouCan('Maintain') &&
        //     !API::getAPI('system:auth')->ifYouCanAll('Admin', 'Edit')) {
        //     return $resp->setAccessError();
        //     return;
        // }
        // for specific customer item
        // by id
        if (Request::hasRequestedID()) {
            $resp->setResponse($this->updateCustomer($req->id, $req->data, true));
            return;
        }
        // for the case when we have to fecth list with customers
        // if (Request::noRequestedItem()) {
        //     $resp->setWrongItemIdError();
        //     return;
        // }

        $resp->setWrongItemIdError();
        // if (empty($req->id)) {
        //     $resp->setWrongItemIdError();
        // } else {
        //     if (is_numeric($req->id)) {
        //         $customerID = intval($req->id);
        //         $resp->setResponse($this->updateCustomer($customerID, $req->data, true));
        //     } else {
        //         $resp->setWrongItemIdError();
        //     }
        // }
    }

    public function delete ($req, $resp) {
        if (!API::getAPI('system:auth')->ifYouCan('Maintain') ||
            !API::getAPI('system:auth')->ifYouCanAll('Admin', 'Edit')) {
            return $resp->setAccessError();
            return;
        }
        // for specific customer item
        // by id
        if (Request::hasRequestedID()) {
            $resp->setResponse($this->archiveCustomer($req->id));
            return;
        }
        // for the case when we have to fecth list with customers
        // if (Request::noRequestedItem()) {
        //     $resp->setWrongItemIdError();
        //     return;
        // }

        $resp->setWrongItemIdError();
        // if (empty($req->id)) {
        //     $resp->setWrongItemIdError();
        // } else {
        //     if (is_numeric($req->id)) {
        //         $customerID = intval($req->id);
        //         $resp->setResponse($this->archiveCustomer($customerID));
        //     } else {
        //         $resp->setWrongItemIdError();
        //     }
        // }
    }

/*    public function get ($req, $resp) {
        if (!empty($req->id)) {
            if (is_numeric($req->id)) {
                $ProductID = intval($req->id);
                $resp->setResponse($this->getProductByID($ProductID));
            } else {
                $resp->setResponse($this->getProductByExternalKey($req->id));
            }
        } else {
            if (isset($req->get['type'])) {
                switch ($req->get['type']) {
                    case 'latest': {
                        $resp->setResponse($this->getProducts_List_Latest($req->get));
                    }
                }
            } else {
                $resp->setResponse($this->getProducts_List($req->get));
            }
        }
    }

*/
}

?>