<?php

class pluginAccount extends objectPlugin {

    public function _getAddress ($AddressID) {
        $config = configurationCustomerDataSource::jsapiGetAddress($AddressID);
        return $this->getCustomer()->fetch($config);
    }

    public function _createAccount ($reqData) {
        $result = array();
        $errors = array();


        if (!libraryValidate::eachValueIsNotEmpty($reqData)) {
            $errors[] = '';
        }

        if (strcasecmp($reqData["Password"], $reqData["ConfirmPassword"]) !== 0) {
            $errors[] = 'ConfirmationPasswordWrong'
        }

        if (count($errors)) {
            return glWrap("errors", $errors);
        }

        // create permission
        $data = array();
        if (glIsToolbox()) {

        } else {
            $data = configurationDefaultDataSource::jsapiGetNewPermission();
            $configCreatePermission = configurationDefaultDataSource::jsapiAddAccountPermissions($data);
            $PermissionID = $this->getCustomer()->fetch($configCreatePermission) ?: null;

            if (empty($PermissionID)) {
                $errors[] = 'PermissionCreateError';
                return glWrap("errors", $errors);
            }
        }

        $data = array()
        $data["CustomerID"] = $this->getCustomer()->getCustomerID();
        $data["PermissionID"] = $PermissionID;
        $data["FirstName"] = $reqData['FirstName'];
        $data["LastName"] = $reqData['LastName'];
        $data["EMail"] = $reqData['EMail'];
        $data["Phone"] = $reqData['Phone'];
        $data["Password"] = $reqData['Password'];
        $data["ValidationString"] = librarySecure::EncodeAccountPassword(time());
        $data["DateLastAccess"] = configurationDefaultDataSource::getDate();
        $data["DateCreated"] = configurationDefaultDataSource::getDate();
        $data["DateUpdated"] = configurationDefaultDataSource::getDate();

        $configCreateAccount = configurationDefaultDataSource::jsapiAddAccount($data);
        $AccountID = $this->getCustomer()->fetch($configCreatePermission) ?: null;

        if (empty($AccountID)) {
            $errors[] = 'AccountCreateError';
            return glWrap("errors", $errors);
        }

        $result = $this->_getAccountByID($AccountID);

        return $result;
    }

    public function _getAccountByID ($id) {
        $config = configurationCustomerDataSource::jsapiGetAccountByID($id);
        $account = $this->getCustomer()->fetch($config);
        // var_dump('getAccountByID', $id);
        // var_dump($account);
        // get account info
        // get account addresses
        $configAddresses = configurationCustomerDataSource::jsapiGetAccountAddresses($id);

        $account['Addresses'] = $this->getCustomer()->fetch($configAddresses) ?: array();

        // adjust values
        $account['ID'] = intval($account['ID']);
        // $account['CustomerID'] = intval($account['CustomerID']);
        // $account['PermissionID'] = intval($account['PermissionID']);
        $account['IsOnline'] = intval($account['IsOnline']) === 1;
        unset($account['CustomerID']);
        unset($account['PermissionID']);

        foreach ($account as $key => $value) {
            if (preg_match("/^Permission_/", $key) === 1) {
                $account[$key] = $value == "1";
            }
        }

        if (!empty($account['Addresses']))
            foreach ($account['Addresses'] as &$item) {
                $item['ID'] = intval($item['ID']);
                $item['CustomerID'] = intval($item['CustomerID']);
                $item['AccountID'] = intval($item['AccountID']);
            }

        return $account;
    }

    public function get_account_account (&$resp, $req) {
        $id = intval($req['id']);
        if (empty($id))
            $resp['error'] = 'The request must contain "id" parameter';
        else
            $resp = $this->_getAccountByID($id);
    }

    public function post_account_account (&$resp, $req) {
        $data = libraryRequest::getObjectFromREQUEST("FirstName", "LastName", "EMail", "Phone", "Password", "ConfirmPassword");
        $resp = $this->_createAccount($data);
    }

    public function patch_account_account () {
        
    }


    public function delete_account_account () {
        
    }

}

?>