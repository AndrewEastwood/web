<?php


class librarySecurity {
    
    public static function wwwAuth ($realm = 'Restricted area') {
        
        if ($_GET['do'] === 'logout') {
            Header('HTTP/1.0 401 Unauthorized');
            exit;
        }

        
        //var_dump($users);
        

        //user => password
        $mode = array('service' => true);

        //var_dump($_SERVER);

        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="'.$realm.
                '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');

            die('You are not authorized user.');
        }
        
        //return false;


        // analyze the PHP_AUTH_DIGEST variable
        if (!($data = self::http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) ||
            !isset($mode[$data['username']])) {
            //$_SERVER['PHP_AUTH_DIGEST'] = false;
            header('HTTP/1.1 401 Unauthorized');
            Header('Location: /');
            //die('Wrong Credentials!');
        }


        // generate the valid response
        $A1 = md5($data['username'] . ':' . $realm . ':' . date('Y-m-d'));
        $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
        $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

        if ($data['response'] != $valid_response) {
            Header('Location: /');
            //die('Wrong Credentials!');
        }

        // ok, valid username & password
        //echo 'You are logged in as: ' . $data['username'];
        
        return true;
    }

    // function to parse the http auth header
    public static function http_digest_parse($txt) {
        // protect against missing data
        $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
    }
    
}


?>