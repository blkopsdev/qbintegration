<?php
    $headers = apache_request_headers();

    if ($hearders['referer'] && strpos($headers['referer'], 'qbo.intuit.com/app/')) {
        include('./index.php');
    } else{
        include('./Error.php');
    }
?>