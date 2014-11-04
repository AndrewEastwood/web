<?php
namespace engine\object;

class api {

    private $app;
    private $customer;
    private $plugin;

    function __construct ($customer, $plugin, $pluginName, $app) {
        $this->app = $app;
        $this->customer = $customer;
        $this->plugin = $plugin;
    }

    public function getApp () {
        return $this->app;
    }
    public function getCustomer () {
        return $this->customer;
    }
    public function getCustomerDataBase () {
        return $this->customer->getDataBase();
    }
    public function getPlugin () {
        return $this->plugin;
    }
    public function getAnotherPlugin ($pluginName) {
        $anotherPlugin = $this->customer->getPlugin($pluginName);
        return $anotherPlugin;
    }
    public function getPluginConfiguration () {
        return $this->plugin->getConfiguration();
    }
    public function getCustomerConfiguration () {
        return $this->customer->getConfiguration();
    }

    // public function getList() {}
    // public function getItem() {}
    // public function saveItem() {}
    // public function updateItem() {}
    // public function deleteItem() {}
}

?>