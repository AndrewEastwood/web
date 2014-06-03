<?php

class pluginAccount extends objectPlugin {

    public function getAccountByID ($id) {
        $config = configurationCustomerDataSource::jsapiGetAccountByID($id);
        $account = $this->getCustomer()->processData($config);
        // var_dump('getAccountByID', $id);
        // var_dump($account);
        return $account;
    }

    public function getSessionAccount () {
        $account = null;
        if (isset($_SESSION['Account']) && !empty($_SESSION['Account']['ID']))
            $account = $this->getAccountByID($_SESSION['Account']['ID']);

        if (empty($account)) {
            setcookie('username', null, false, '/', $_SERVER['SERVER_NAME']);
            setcookie('password', null, false, '/', $_SERVER['SERVER_NAME']);
            unset($_SESSION['Account']);
        }

        return $account;
    }

    public function clearSessionAccount () {
        setcookie('username', null, false, '/', $_SERVER['SERVER_NAME']);
        setcookie('password', null, false, '/', $_SERVER['SERVER_NAME']);
        unset($_SESSION['Account']);
        return null;
    }

    public function isAuthenticated () {
        $account = $this->getSessionAccount();
        return !empty($account);
    }

    public function get_status (&$resp) {
        $resp['account'] = $this->getSessionAccount();
        $resp['authenticated'] = $this->isAuthenticated();
    }

    public function post_signin (&$resp) {
        $resp['account'] = 11111;
        $resp['authenticated'] = $this->isAuthenticated();
    }
}

?>