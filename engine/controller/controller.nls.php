<?php

    header('Content-Type: application/json; charset=utf-8');

    include $_SERVER['DOCUMENT_ROOT'] . '/engine/bootstrap.php';

    $customer = libraryRequest::fromGET('customer');
    $langfile = libraryRequest::fromGET('lang');

    if (MPWS_ENV === 'DEV')
        $layoutCustomer = glGetFullPath('web', 'customer', $customer, 'static', 'nls', $langfile);
    else
        $layoutCustomer = glGetFullPath('web', 'build', 'customer', $customer, 'static', 'nls', $langfile);

    if (file_exists($layoutCustomer))
        echo file_get_contents($layoutCustomer);
    else
        echo "{}";

?>