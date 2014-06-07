<?php

class libraryResponse {

    static $_RESPONSE = array();

    static function setError ($errorMsg, $headerMsg = false) {
        if (!empty($errorMsg))
            self::$_RESPONSE['error'] = $errorMsg;
        if (!empty($headerMsg))
            header($headerMsg);
    }

    static function sendResponse () {
        $output = new libraryDataObject(self::$_RESPONSE);
        $_out = $output->toJSON();
        if ($_out === "null")
            echo "{}";
        else
            echo $_out;
    }

}

?>