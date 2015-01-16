<?php

namespace web\base\atlantis\config;

use \engine\objects\configuration as baseConfig;

class data extends baseConfig {

    public $Table_SystemAccounts = "mpws_accounts";

    public function jsapiGetNewPermission () {
        $perms = array(
            "CanAdmin" => 0,
            "CanCreate" => 0,
            "CanEdit" => 0,
            "CanViewReports" => 0,
            "CanAddUsers" => 0
        );
        return $perms;
    }

    public function jsapiGetCustomer ($ExternalKey) {
        return $this->createDBQuery(array(
            "source" => "mpws_customer",
            "fields" => array("*"),
            "condition" => array(
                "ExternalKey" => $this->createCondition($ExternalKey),
                "Status" => $this->createCondition("ACTIVE")
            ),
            "limit" => 1,
            "options" => array(
                "expandSingleRecord" => true
            )
        ));
    }

    public function jsapiGetAccount () {
        $config = $this->createDBQuery(array(
            "source" => "mpws_accounts",
            "fields" => array("*"),
            "limit" => 1,
            "condition" => array(),
            "options" => array(
                "expandSingleRecord" => true
            )
        ));
        return $config;
    }

    public function jsapiGetAccountByCredentials ($login, $password) {
        $config = $this->jsapiGetAccount();
        $config["condition"]["EMail"] = $this->createCondition($login);
        $config["condition"]["Password"] = $this->createCondition($password);
        return $config;
    }

    public function jsapiGetAccountByID ($id) {
        $config = $this->jsapiGetAccount();
        $config["condition"] = array(
            "ID" => $this->createCondition($id)
        );
        return $config;
    }

    public function jsapiGetAccountByEMail ($email) {
        $config = $this->jsapiGetAccount();
        $config["condition"] = array(
            "EMail" => $this->createCondition($email)
        );
        return $config;
    }


    public function jsapiGetAccountByValidationString ($ValidationString) {
        $config = $this->jsapiGetAccount();
        $config["condition"] = array(
            "ValidationString" => $this->createCondition($ValidationString)
        );
        return $config;
    }

    public function jsapiAddAccount ($data) {
        $data["DateUpdated"] = $this->getDate();
        $data["DateCreated"] = $this->getDate();
        $data["DateLastAccess"] = $this->getDate();
        return $this->createDBQuery(array(
            "source" => "mpws_accounts",
            "action" => "insert",
            "data" => $data,
            "options" => null
        ));
    }

    public function jsapiUpdateAccount ($AccountID, $data) {
        $data["DateUpdated"] = $this->getDate();
        return $this->createDBQuery(array(
            "source" => "mpws_accounts",
            "action" => "update",
            "condition" => array(
                "ID" => $this->createCondition($AccountID)
            ),
            "data" => $data,
            "options" => null
        ));
    }

    public function jsapiDisableAccount ($AccountID) {
        return $this->createDBQuery(array(
            "source" => "mpws_accounts",
            "action" => "update",
            "condition" => array(
                "ID" => $this->createCondition($AccountID)
            ),
            "data" => array(
                "Status" => 'REMOVED',
                "DateUpdated" => $this->getDate()
            ),
            "options" => null
        ));
    }

    public function jsapiActivateAccount ($ValidationString) {
        return $this->createDBQuery(array(
            "source" => "mpws_accounts",
            "action" => "update",
            "condition" => array(
                "ValidationString" => $this->createCondition($ValidationString)
            ),
            "data" => array(
                "Status" => "ACTIVE",
                "DateUpdated" => $this->getDate()
            ),
            "options" => null
        ));
    }

    public function jsapiSetOnlineAccount ($AccountID) {
        return $this->createDBQuery(array(
            "source" => "mpws_accounts",
            "action" => "update",
            "condition" => array(
                "ID" => $this->createCondition($AccountID)
            ),
            "data" => array(
                "IsOnline" => true,
                "DateUpdated" => $this->getDate()
            ),
            "options" => null
        ));
    }

    public function jsapiSetOfflineAccount ($AccountID) {
        return $this->createDBQuery(array(
            "source" => "mpws_accounts",
            "action" => "update",
            "condition" => array(
                "ID" => $this->createCondition($AccountID)
            ),
            "data" => array(
                "IsOnline" => true,
                "DateUpdated" => $this->getDate()
            ),
            "options" => null
        ));
    }

    public function jsapiGetPermissions ($AccountID) {
        return $this->createDBQuery(array(
            "source" => "mpws_permissions",
            "fields" => array("*"),
            "condition" => array(
                "AccountID" => $this->createCondition($AccountID)
            ),
            "limit" => 1,
            "options" => array(
                "expandSingleRecord" => true
            )
        ));
    }

    public function jsapiAddPermissions ($data) {
        $data["DateUpdated"] = $this->getDate();
        $data["DateCreated"] = $this->getDate();
        return $this->createDBQuery(array(
            "source" => "mpws_permissions",
            "action" => "insert",
            "data" => $data,
            "options" => null
        ));
    }

    public function jsapiUpdatePermissions ($AccountID, $data) {
        $data["DateUpdated"] = $this->getDate();
        return $this->createDBQuery(array(
            "source" => "mpws_permissions",
            "action" => "update",
            "condition" => array(
                "AccountID" => $this->createCondition($AccountID)
            ),
            "data" => $data,
            "options" => null
        ));
    }

    public function jsapiGetAccountAddresses ($AccountID, $withRemoved = false) {
        $config = $this->createDBQuery(array(
            "source" => "mpws_accountAddresses",
            "fields" => array("ID", "AccountID", "Address", "POBox", "Country", "City", "Status", "DateCreated", "DateUpdated"),
            "condition" => array(
                "AccountID" => $this->createCondition($AccountID)
            ),
            "options" => array(
                "asDict" => "ID"
            )
        ));
        if (!$withRemoved)
            $config['condition']["Status"] = $this->createCondition("ACTIVE");
        return $config;
    }

    // public function jsapiGetAccountAddress ($AccountID, $AddressID) {
    //     return $this->createDBQuery(array(
    //         "source" => "mpws_accountAddresses",
    //         "fields" => array("*"),
    //         "condition" => array(
    //             "ID" => $this->createCondition($AddressID),
    //             "AccountID" => $this->createCondition($AccountID),
    //             "Status" => $this->createCondition("ACTIVE")
    //         ),
    //         "options" => array(
    //             "expandSingleRecord" => true
    //         )
    //     ));
    // }

    public function jsapiGetAddress ($AddressID) {
        return $this->createDBQuery(array(
            "source" => "mpws_accountAddresses",
            "fields" => array("ID", "AccountID", "Address", "POBox", "Country", "City", "Status", "DateCreated", "DateUpdated"),
            "condition" => array(
                "ID" => $this->createCondition($AddressID),
            ),
            "options" => array(
                "expandSingleRecord" => true
            )
        ));
    }

    public function jsapiAddAddress ($data) {
        $data["DateUpdated"] = $this->getDate();
        $data["DateCreated"] = $this->getDate();
        return $this->createDBQuery(array(
            "source" => "mpws_accountAddresses",
            "action" => "insert",
            "data" => $data,
            "options" => null
        ));
    }

    public function jsapiUpdateAddress ($AddressID, $data) {
        $data["DateUpdated"] = $this->getDate();
        return $this->createDBQuery(array(
            "source" => "mpws_accountAddresses",
            "action" => "update",
            "condition" => array(
                "ID" => $this->createCondition($AddressID)
            ),
            "data" => $data,
            "options" => null
        ));
    }

    public function jsapiDisableAddress ($AddressID) {
        return $this->createDBQuery(array(
            "source" => "mpws_accountAddresses",
            "action" => "update",
            "condition" => array(
                "ID" => $this->createCondition($AddressID)
            ),
            "data" => array(
                "Status" => 'REMOVED',
                "DateUpdated" => $this->getDate()
            ),
            "options" => null
        ));
    }

    // >>>> Account statistics
    public function jsapiStat_AccountsOverview () {
        $config = $this->jsapiGetAccount();
        $config['fields'] = array("@COUNT(*) AS ItemsCount", "Status");
        $config['group'] = "Status";
        $config['limit'] = 0;
        $config['options'] = array(
            'asDict' => array(
                'keys' => 'Status',
                'values' => 'ItemsCount'
            )
        );
        unset($config['condition']);
        unset($config['additional']);
        return $config;
    }

    public function jsapiStat_AccountsIntensityLastMonth ($status) {
        $config = $this->jsapiGetAccount();
        $config['fields'] = array("@COUNT(*) AS ItemsCount", "@Date(DateCreated) AS IncomeDate");
        $config['condition'] = array(
            'Status' => $this->createCondition($status),
            'DateCreated' => $this->createCondition(date('Y-m-d', strtotime("-10 month")), ">")
        );
        $config['options'] = array(
            'asDict' => array(
                'keys' => 'IncomeDate',
                'values' => 'ItemsCount'
            )
        );
        $config['group'] = 'Date(DateCreated)';
        $config['limit'] = 0;
        unset($config['additional']);
        return $config;
    }
    // <<<< Account statistics


}


?>