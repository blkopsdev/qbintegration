<?php
require "../../vendor/autoload.php";

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;

$config = include('../../config.php');
$token_data = json_decode(file_get_contents('../../accessToken.json'), true);

$json = file_get_contents('php://input');
// $json = '{"acenda":{"store":{"id":4617,"name":"brandsdistribution-store"},"subscription":{"id":1513,"credentials":{"email":"johna@torreycommerce.com","password":"test","api_key":"test","web_host":"test"}},"event_payload":[{"Order":{"id":"13","date_modified":"2018-03-12 18:24:17","order_number":"3708573","status":"open","email":"gavinm@torreycommerce.com","ip":"209.209.231.100, 209.209.231.100","shipping_first_name":"Gavin","shipping_last_name":"Mandelbaum","shipping_phone_number":"+18583373775","shipping_street_line1":"2385 CALLE DEL ORO","shipping_street_line2":"","shipping_city":"LA JOLLA","shipping_state":"CA","shipping_zip":"92037","shipping_country":"US","shipping_method":2830234,"shipping_rate":"0.00","shipping_rate_original":"0.00","tax_percent":7.75,"tax_shipping":false,"tax_included":false,"returns_pending":0,"returns_rma_numbers":"","returnable_items":0,"giftlist_present":false,"subtotal":"50.00","tax":"3.88","tax_original":"3.88","total":"53.88","charge_amount":"0.00","unsettled":"-53.88","transaction_status":"","fulfillment_status":"pending","fraud_check":false,"fraud_results":{"maxmind":"7.29"},"marketplace_id":"13","calculate_tax":false,"card_type":null,"payment_method_nonce":null,"discount_collection":null,"bd_order_id":"","items":[{"id":13,"date_created":"2018-03-12 18:24:16","date_modified":"2018-03-12 18:24:16","product_id":3106,"variant_id":10128,"wishlist_id":null,"registry_id":null,"status":"open","vendor":"acenda","tracking":null,"name":"BD Test Product - X - Red","sku":"252808","barcode":"Test3","quantity":-1,"price":50,"ordered_quantity":-1,"ordered_price":50,"backorder":"0","fulfilled_quantity":0,"fulfillment_status":"pending","marketplace_name":"","marketplace_item_id":null,"marketplace_tax_allocation":null,"marketplace_ship_allocation":null,"warnings":[]}],"billing_first_name":"Gavin","billing_last_name":"Mandelbaum","billing_phone_number":"+18583373775","billing_street_line1":"2385 CALLE DEL ORO","billing_street_line2":"","billing_city":"LA JOLLA","billing_state":"CA","billing_country":"US","billing_zip":"92037","cancellation_window":"30","fraud_score":0,"payments":[],"coupons":[]}}]}}';
file_put_contents('./CreatedOrder.json', $json);

$obj = json_decode($json, true);
$order_data = $obj['acenda']['event_payload'];
$order = $order_data['Order'];

// Prep Data Services
$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => $config['client_id'],
    'ClientSecret' => $config['client_secret'],
    'accessTokenKey' => $token_data['access_token'],
    'refreshTokenKey' => $token_data['refresh_token'],
    'QBORealmID' => $config['realmid'],
    'baseUrl' => "Development"
));

$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
$dataService->throwExceptionOnError(true);
//Add a new Invoice
/*$theResourceObj = Invoice::create([
    "Deposit" => 0,
    "domain" => "QBO",
    "sparse" => false,
    // "DocNumber" => "1037",
    // "TxnDate" => "2014-09-19",
    "Line" => [
        "Id" => "1",
        "LineNum" => 1,
        "Description" => "Rock Fountain",
        "Amount" => 275.0,
        "DetailType" => "SalesItemLineDetail",
        "SalesItemLineDetail" => [
            "ItemRef" => [
                "value" => "5",
                "name" => "Rock Fountain"
            ],
            "UnitPrice" => 275,
            "Qty" => 1,
            "TaxCodeRef" => [
                "value" => "TAX"
            ]
        ]
    ], [
        "Id" => "2",
        "LineNum" => 2,
        "Description" => "Fountain Pump",
        "Amount" => 12.75,
        "DetailType" => "SalesItemLineDetail",
        "SalesItemLineDetail" => [
            "ItemRef" => [
                "value" => "11",
                "name" => "Pump"
            ],
            "UnitPrice" => 12.75,
            "Qty" => 1,
            "TaxCodeRef" => [
                "value" => "TAX"
            ]
        ]
    ], [
        "Id" => "3",
        "LineNum" => 3,
        "Description" => "Concrete for fountain installation",
        "Amount" => 47.5,
        "DetailType" => "SalesItemLineDetail",
        "SalesItemLineDetail" => [
            "ItemRef" => [
                "value" => "3",
                "name" => "Concrete"
            ],
            "UnitPrice" => 9.5,
            "Qty" => 5,
            "TaxCodeRef" => [
                "value" => "TAX"
            ]
        ]
    ], [
        "Amount" => 335.25,
        "DetailType" => "SubTotalLineDetail",
        "SubTotalLineDetail" => []
    ],
    "TxnTaxDetail" => [
        "TxnTaxCodeRef" => [
            "value" => "2"
        ],
        "TotalTax" => 26.82,
        "TaxLine" => [
            "Amount" => 26.82,
            "DetailType" => "TaxLineDetail",
            "TaxLineDetail" => [
                "TaxRateRef" => [
                    "value" => "3"
                ],
                "PercentBased" => true,
                "TaxPercent" => 8,
                "NetAmountTaxable" => 335.25
            ]
        ]
    ],
    "CustomerRef" => [
        "value" => "24",
        "name" => "Sonnenschein Family Store"
    ],
    "CustomerMemo" => [
        "value" => "Thank you for your business and have a great day!"
    ],
    "BillAddr" => [
        "Id" => "95",
        "Line1" => "Russ Sonnenschein",
        "Line2" => "Sonnenschein Family Store",
        "Line3" => "5647 Cypress Hill Ave.",
        "Line4" => "Middlefield, CA  94303",
        "Lat" => "37.4238562",
        "Long" => "-122.1141681"
    ],
    "ShipAddr" => [
        "Id" => "25",
        "Line1" => "5647 Cypress Hill Ave.",
        "City" => "Middlefield",
        "CountrySubDivisionCode" => "CA",
        "PostalCode" => "94303",
        "Lat" => "37.4238562",
        "Long" => "-122.1141681"
    ],
    "SalesTermRef" => [
        "value" => "3"
    ],
    "DueDate" => "2014-10-19",
    "TotalAmt" => 362.07,
    "ApplyTaxAfterDiscount" => false,
    "PrintStatus" => "NeedToPrint",
    "EmailStatus" => "NotSet",
    "BillEmail" => [
        "Address" => "Familiystore@intuit.com"
    ],
    "Balance" => 362.07
]);
$resultingObj = $dataService->Add($theResourceObj);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
    echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
    $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
    echo $xmlBody . "\n";
}
*/
?>