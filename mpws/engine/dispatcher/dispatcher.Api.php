<?php

    /* dispatcher init */
    // set request realm
    define ('MPWS_REQUEST', 'API');
    // start session
    session_start();
    // document root path reference
    $DR = $_SERVER['DOCUMENT_ROOT'];
    // bootstrap
    include $DR . '/engine/bootstrap.php';
    // include global files
    $globals = glob($DR . '/engine/global/global.*.php');
    foreach ($globals as $globalFile)
        require_once $globalFile;
    // include all configs
    //$configs = glob($DR . '/engine/system/config/config.*.php');
    //foreach ($configs as $configFile)
    //    require_once $configFile;

    /* prepare public environment */

    // set headers
    //foreach ($config['WEB']['HEADERS'] as $header)
    //    header($header);
    // set encoding
    //foreach ($config['WEB']['ICONV-ENCODING'] as $type => $charset)
    //    iconv_set_encoding($type, $charset);
    // timezone
    //date_default_timezone_set($config['WEB']['TIMEZONE']);

    /* controller */
    $controller = new controllerJsApi();
    $controller->processRequests();

?>
