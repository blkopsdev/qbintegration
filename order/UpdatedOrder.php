<?php
	require("../Client.php");
    require_once "../vendor/autoload.php";
    require ("../refreshToken.php");
    use QuickBooksOnline\API\DataService\DataService;
    use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
    use QuickBooksOnline\API\Facades\Invoice;
    use QuickBooksOnline\API\Facades\Customer;
    use QuickBooksOnline\API\Facades\Item;

    // Prep Data Services
    $config = include('../config.php');
    
    // $json = file_get_contents('php://input');
    $json = '{"acenda":{"store":{"id":2764,"name":"demostore"},"subscription":{"id":1965,"credentials":{"Email":"dev.royali1029@gmail.com","company_id":"193514832571624"}},"event_payload":[{"Order":{"id":2877316,"date_modified":"2018-11-05 09:04:24","order_number":"2850346","status":"open","email":"gavinm@torreycommerce.com","ip":"50.97.232.186, 50.97.232.186","shipping_first_name":"gavin","shipping_last_name":"mann","shipping_phone_number":"+18583373775","shipping_street_line1":"2385 calle del oro","shipping_street_line2":"","shipping_city":"la jolla","shipping_state":"CA","shipping_zip":"92037","shipping_country":"US","shipping_method":2830234,"shipping_rate":"5.00","shipping_rate_original":"5.00","tax_percent":7.75,"tax_shipping":false,"tax_included":false,"returns_pending":0,"returns_rma_numbers":"","returnable_items":0,"giftlist_present":false,"subtotal":"11.00","tax":"0.00","tax_original":"0.00","total":"16.00","charge_amount":"0.00","unsettled":"-16.00","transaction_status":"authorize","fulfillment_status":"pending","fraud_check":false,"fraud_results":{"maxmind":"19.01"},"marketplace_name":"webstore","marketplace_id":2877316,"review_request_sent":false,"calculate_tax":false,"card_type":"visa","card_last4":"1111","payment_method_nonce":null,"discount_collection":null,"exported":"","items":[{"id":568,"date_created":"2018-11-05 09:04:20","date_modified":"2018-11-05 09:04:20","product_id":975,"variant_id":1014,"wishlist_id":null,"registry_id":null,"status":"open","vendor":"acenda","tracking":null,"name":"Default Title","sku":"123456789","barcode":null,"quantity":1,"price":11,"ordered_quantity":1,"ordered_price":11,"backorder":"0","fulfilled_quantity":0,"fulfillment_status":"pending","marketplace_name":"","marketplace_item_id":null,"marketplace_tax_allocation":null,"marketplace_ship_allocation":null,"warnings":[]}],"billing_first_name":"gavin","billing_last_name":"mann","billing_phone_number":"+18583373775","billing_street_line1":"2385 calle del oro","billing_street_line2":null,"billing_city":"la jolla","billing_state":"CA","billing_country":"US","billing_zip":"92037","cancellation_window":"30","fraud_score":100,"payments":[{"id":3115936,"order_id":"2877316","date_created":"2018-11-05 09:04:23","date_modified":"2018-11-05 09:04:23","action":null,"platform":"PayPal","status":"authorize","amount":"16.00","charged":0,"refund_reason":""}],"coupons":[]}}]}}';
    file_put_contents('./CreatedOrder.json', $json);

    $req = json_decode($json, true);
    $realmId = $req['acenda']['subscription']['credentials']['company_id'];
    
    refreshToken($realmId);
    $con = mysqli_connect('localhost', 'root', 'root', 'acenda_qb');
    if (mysqli_connect_errno()) 
    { 
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
?>