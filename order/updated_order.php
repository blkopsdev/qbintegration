<?php
    
    require_once "../vendor/autoload.php";
    require ("../refreshToken.php");

    use QuickBooksOnline\API\Core\ServiceContext;
    use QuickBooksOnline\API\DataService\DataService;
    use QuickBooksOnline\API\PlatformService\PlatformService;
    use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
    use QuickBooksOnline\API\Facades\Invoice;
    use QuickBooksOnline\API\Facades\Customer;
    use QuickBooksOnline\API\Facades\Item;
    use QuickBooksOnline\API\Facades\TaxService;
    use QuickBooksOnline\API\Facades\TaxRate;

    $config = include('../config.php');
    $json = file_get_contents('php://input');
    // $json = '{"acenda":{"store":{"id":2764,"name":"demostore"},"subscription":{"id":1965,"credentials":{"Email":"dev.royali1029@gmail.com","company_id":"193514789400954"}},"event_payload":[{"Order":{"id":2877312,"date_modified":"2018-11-13 06:43:57","date_created":"2018-09-17 20:59:59","order_number":"1019014","status":"open","email":"utachi68@gmail.com","ip":"104.151.6.52, 104.151.6.52","shipping_first_name":"bob","shipping_last_name":"smith","shipping_phone_number":"8585551212","shipping_street_line1":"8400 MIRAMAR RD","shipping_street_line2":"STE 290","shipping_city":"SAN DIEGO","shipping_state":"CA","shipping_zip":"92126","shipping_country":"US","shipping_method":2830234,"shipping_rate":"0.00","shipping_rate_original":"0.00","tax_percent":7.75,"tax_shipping":false,"tax_included":false,"returns_pending":0,"returns_rma_numbers":"","returnable_items":0,"giftlist_present":false,"subtotal":"52.00","tax":"4.03","tax_original":"4.03","total":"56.03","charge_amount":"0.00","unsettled":"-56.03","transaction_status":"authorize","fulfillment_status":"pending","fraud_check":true,"fraud_results":{"maxmind":"94.89"},"marketplace_name":"webstore","marketplace_id":2877312,"review_request_sent":false,"calculate_tax":false,"card_type":"","exported":false,"billing_first_name":"bob","billing_last_name":"smith","billing_phone_number":"8585551212","billing_street_line1":"8400 MIRAMAR RD","billing_street_line2":"STE 290","billing_city":"SAN DIEGO","billing_state":"CA","billing_country":"US","billing_zip":"92126","card_last4":"","cancellation_window":"30","fraud_score":100,"iscancellable":false,"billing_payments":[],"items":[{"id":564,"date_created":"2018-09-17 21:00:02","date_modified":"2018-11-13 06:43:57","product_id":193,"variant_id":667,"wishlist_id":null,"registry_id":null,"status":"open","vendor":"acenda","tracking":null,"name":"Chaz Kangeroo Hoodie-M-Gray","sku":"MH01-M-Gray","barcode":null,"quantity":1,"price":52,"ordered_quantity":1,"ordered_price":52,"backorder":"0","fulfilled_quantity":0,"fulfillment_status":"pending","marketplace_name":"","marketplace_item_id":null,"marketplace_tax_allocation":null,"marketplace_ship_allocation":null,"warnings":[]}],"coupons":[],"payments":[{"id":3115932,"order_id":"2877312","date_created":"2018-09-17 21:00:02","date_modified":"2018-09-17 21:00:02","action":null,"platform":"PayPal","status":"authorize","currency":null,"amount":"56.03","charged":0,"address":{"first_name":"bob","last_name":"smith","street_line1":"8400 MIRAMAR RD","street_line2":"STE 290","zip":"92126","city":"SAN DIEGO","state":"CA","country":"US","phone_number":"8585551212"},"card_last4":"1111","card_type":"visa","refund_reason":""}],"card_number":null,"card_exp_month":null,"card_exp_year":null,"card_cvv2":null,"payment_method_nonce":null,"discount_collection":null}}]}}';
    file_put_contents('./UpdatedOrder.json', $json);

?>