<?php

namespace engine\lib;

class dataobject {

    private $_data;

    function __construct($initialData = array()) {
        $this->_data = $initialData;
    }

    // data
    public function setError($errorMessage) {
        $this->_data['error'] = $errorMessage;
        return $this;
    }
    public function getError() {
        return isset($this->_data['error']) ? $this->_data['error'] : false;
    }

    public function setData($key, $val) {
        $this->_data[$key] = $val;
        return $this;
    }

    public function overwriteData($data) {
        if (is_array($data)) {
            $this->_data = new ArrayObject($data);
            return true;
        }
        return false;
    }

    public function getData($key = null) {
        if (isset($key))
            return isset($this->_data[$key]) ? $this->_data[$key] : null;
        return $this->_data;
    }

    public function hasError() {
        return !empty($this->_data['error']);
    }

    public function hasData() {
        return count($this->_data) > 1;
    }

    public function isEmpty($key = null) {
        if (!empty($key)) {
            // var_dump($this->getData($key));
            // return true;//
            // empty($this->getData($key));
        }
        return !$this->hasData();
    }

    public function hasKey ($key) {
        return isset($this->_data[$key]);
    }

    // converters
    public function toJSON($key = null) {
        return json_encode($this->getData($key));
    }
    public function toNative() { return $this->getData();}
}

?>