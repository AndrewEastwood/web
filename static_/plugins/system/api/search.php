<?php
namespace static_\plugins\system\api;

use \engine\objects\plugin as basePlugin;
use \engine\lib\validate as Validate;
use \engine\lib\secure as Secure;
use \engine\lib\path as Path;
use \engine\lib\request as Request;
use \engine\lib\api as API;
use Exception;
use ArrayObject;

class search extends API {

    public function search ($text) {
        return "ok";
    }

}

?>