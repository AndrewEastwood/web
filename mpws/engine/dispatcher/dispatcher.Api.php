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
    $configs = glob($DR . '/engine/system/config/config.*.php');
    foreach ($configs as $configFile)
        require_once $configFile;

    /* prepare public environment */

    // set headers
    foreach ($config['WEB']['HEADERS'] as $header)
        header($header);
    // set encoding
    foreach ($config['WEB']['ICONV-ENCODING'] as $type => $charset)
        iconv_set_encoding($type, $charset);
    // timezone
    date_default_timezone_set($config['WEB']['TIMEZONE']);

    /* controller */

    //$SESSION['MPWS-REALM'] = MPWS_REQUEST;
    //echo $_GET['action'];
    switch(strtolower($_GET['page'])) {
        case 'public': 
            $controller = new controllerPublic();
            break;
        default:
            $controller = new controllerToolbox();
            break;
    }

    // get results
    $controller->processRequests();

?>
