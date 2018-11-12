<?php
    require_once "../vendor/autoload.php";
    // include('../AcendaApi.php');
    
    use QuickBooksOnline\API\DataService\DataService;
    use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
    use QuickBooksOnline\API\Facades\Invoice;
    use QuickBooksOnline\API\Facades\Customer;
    use QuickBooksOnline\API\Facades\Item;

    // Prep Data Services
    $config = include('../config.php');
    $token_data = json_decode(file_get_contents('../accessToken.json'), true);

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
    
    $json = file_get_contents('php://input');
    // $json = '{"acenda":{"store":{"id":4617,"name":"brandsdistribution-store"},"subscription":{"id":1513,"credentials":{"email":"johna@torreycommerce.com","password":"test","api_key":"test","web_host":"test"}},"event_payload":[{"Order":{"id":60,"date_modified":"2018-07-02 07:18:13","date_created":"2018-06-27 09:03:10","order_number":"3724875","status":"open","email":"blkopsdev@gmail.com","ip":"8.46.76.44, 8.46.76.44","shipping_first_name":"Gavin","shipping_last_name":"Mandelbaum","shipping_phone_number":"+18583373775","shipping_street_line1":"2385 CALLE DEL ORO","shipping_street_line2":"","shipping_city":"LA JOLLA","shipping_state":"CA","shipping_zip":"92037","shipping_country":"US","shipping_method":2830234,"shipping_rate":"12.00","shipping_rate_original":"12.00","tax_percent":8,"tax_shipping":true,"tax_included":true,"returns_pending":0,"returns_rma_numbers":"","returnable_items":0,"giftlist_present":false,"subtotal":"50.00","tax":"0.00","tax_original":"0.00","total":"62.00","charge_amount":"62.00","unsettled":"-62.00","transaction_status":"sale","fulfillment_status":"pending","fraud_check":false,"fraud_results":{"maxmind":"5.36"},"marketplace_name":"ebay","marketplace_id":"fake_order","calculate_tax":false,"card_type":null,"discount_collection":null,"bd_order_id":"","billing_first_name":"Jurick","billing_last_name":"Joling","billing_phone_number":"+18583373775","billing_street_line1":"2385 CALLE DEL ORO","billing_street_line2":"","billing_city":"LA JOLLA","billing_state":"CA","billing_country":"US","billing_zip":"92037","cancellation_window":"30","fraud_score":0,"iscancellable":false,"card_number":null,"card_exp_month":null,"card_exp_year":null,"card_cvv2":null,"payment_method_nonce":null,"items":[{"id":57,"date_created":"2018-06-27 09:03:14","date_modified":"2018-07-02 07:18:13","product_id":14437,"variant_id":3126751,"wishlist_id":null,"registry_id":null,"status":"open","vendor":"acenda","tracking":null,"name":"01882_TABACCO-brown-M","sku":"265269","barcode":"8554321557436","quantity":1,"price":50,"ordered_quantity":1,"ordered_price":50,"backorder":"0","fulfilled_quantity":0,"fulfillment_status":"pending","marketplace_name":"","marketplace_item_id":null,"marketplace_tax_allocation":null,"marketplace_ship_allocation":null,"warnings":[]}],"coupons":[],"payments":[{"id":21,"order_id":"60","date_created":"2018-06-27 09:03:13","date_modified":"2018-06-27 09:03:13","action":null,"platform":"Braintree","status":"sale","amount":"62.00","charged":"62.00","refund_reason":""}]}}]}}';
    file_put_contents('./CreatedOrder.json', $json);

    $obj = json_decode($json, true);
    $order_data = $obj['acenda']['event_payload'];
    foreach ($order_data as $Order) {
        $order = $Order['Order'];
        if ($order['tax_shipping'] == true && $order['tax_included'] == true) {
            if($order['tax_percent'] != 0) {
                $taxRate = $order['tax_percent'];
                /*$TaxRate = json_encode($dataService->query("SELECT * FROM TaxRate where RateValue='" . $taxRate . "'"));
                $TaxRate = json_decode($TaxRate, true);
                $TaxName = $TaxRate[0]['Name'];*/
                $TaxCodeRef = json_decode(json_encode($dataService->query("SELECT * FROM TaxCode where Name='TaxRate_" . $taxRate . "'")), true);
                $TaxCodeRefId = $TaxCodeRef[0]['Id'];
                if ($TaxCodeRefId == null) {
                    $TaxRateDetails = array();
                    $currentTaxServiceDetail = TaxRate::create([
                        "TaxRateName" => "TaxRate_" . $taxRate,
                        "RateValue" =>  $taxRate,
                        "TaxAgencyId" => "1",
                        "TaxApplicableOn" => "Sales"
                    ]);
                    $TaxRateDetails[] = $currentTaxServiceDetail;

                    $TaxService = TaxService::create([
                        "TaxCode" => "TaxRate_" . $taxRate,
                        "TaxRateDetails" => $TaxRateDetails
                    ]);
                    $result = $dataService->Add($TaxService);
                    $error = $dataService->getLastError();
                    if ($error) {
                        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                        echo "The Response message is: " . $error->getResponseBody() . "\n";
                    }
                    $TaxCodeRefId = $result->TaxService->TaxCodeId;
                }

            }
        } else {
            $TaxCodeRefId = "TAX";
        }
        
        $OrderItems = [];
        //Add Order items to Line Array
        if(is_array($order['items'])) {
            foreach($order['items'] as $item) {
                $sku = $item['sku'];
                $ItemSearchQuery = "SELECT * FROM Item Where Sku='" . $sku . "'";
                $entities = $dataService->Query($ItemSearchQuery);
                $entities = json_decode(json_encode($entities), true);
                $Item = $entities[0];
                if (!empty($Item)) {
                    $ItemID = $Item['Id'];
                    $ItemName = $Item['Name'];
                } else {
                    $Item = Item::create([
                        "Name" => $item['name'],
                        "Descriptoin" => "Barcode:" . $item['barcode'],
                        "Active" => true,
                        "FullyQualifiedName" => $item['name'],
                        "Type" => "Inventory",
                        "IncomeAccountRef"=> [
                            "value"=> 79,
                            "name" => "Sales of Product Income"
                        ],
                        "ExpenseAccountRef"=> [
                            "value"=> 80,
                            "name"=> "Cost of Goods Sold"
                        ],
                        "AssetAccountRef"=> [
                            "value"=> 81,
                            "name"=> "Inventory Asset"
                        ],
                        "TrackQtyOnHand" => true,
                        "UnitPrice" => $item['price'],
                        "QtyOnHand"=> 1,
                        "InvStartDate"=> date('Y-m-d')
                    ]);
                    $theResourceObj = $dataService->Add($Item);
                    $error = $dataService->getLastError();
                    if ($error) {
                        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                        echo "The Response message is: " . $error->getResponseBody() . "\n";
                    }
                    else {
                        $ItemID = $theResourceObj->Id;
                        $ItemName = $theResourceObj->Name;
                    }
                }

                $orderItem = [
                    "Amount" => $item['ordered_price'],
                    "DetailType" => "SalesItemLineDetail",
                    "SalesItemLineDetail" => [
                        "ItemRef" => [
                            "value" => $ItemID,
                            "name" =>  $ItemName
                        ],
                        "Qty" => $item['ordered_quantity'],
                        "TaxCodeRef" => [
                            "value" => $TaxCodeRefId
                        ]
                    ]
                ];
                array_push($OrderItems, $orderItem);
            }
        }
        // Add Shipping Price to Line
        $shipping_item = json_encode($dataService->Query("SELECT * FROM Item Where Name='Shipping Price'"));
        $shipping_item = json_decode($shipping_item, true);
        $shipping_item = $shipping_item[0];
        if(!empty($shipping_item)) {
            $shippingItemId = $shipping_item['Id'];
            $item = $dataService->FindbyId('item', $shippingItemId);
            $theResourceObj = Item::update($item , [
                "UnitPrice" => $order['shipping_rate'],
                "QtyOnHand" => 1
            ]);

            $resultingObj = $dataService->Add($theResourceObj);
            $error = $dataService->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
            }
            else {
                $shippingItem = [
                    "Amount" => $theResourceObj->UnitPrice,
                    "DetailType" => "SalesItemLineDetail",
                    "SalesItemLineDetail" => [
                        "ItemRef" => [
                            "value" => $theResourceObj->Id,
                            "name" =>  $theResourceObj->Name
                        ],
                        "Qty" => 1,
                    ]
                ];
            }
        } else {
            $Item = Item::create([
                "Name" => "Shipping Price",
                "Description" => "This is Shipping Price",
                "Active" => true,
                "FullyQualifiedName" => "Shipping",
                "Type" => "Inventory",
                "IncomeAccountRef"=> [
                    "value"=> 79,
                    "name" => "Sales of Product Income"
                ],
                "ExpenseAccountRef"=> [
                    "value"=> 80,
                    "name"=> "Cost of Goods Sold"
                ],
                "AssetAccountRef"=> [
                    "value"=> 81,
                    "name"=> "Inventory Asset"
                ],
                "TrackQtyOnHand" => true,
                "UnitPrice" => $order['shipping_rate'],
                "QtyOnHand"=> 1,
                "InvStartDate"=> date('Y-m-d')
            ]);
            $theResourceObj = $dataService->Add($Item);
            $error = $dataService->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
            }
            else {
                $shippingItem = [
                    "Amount" => $theResourceObj->UnitPrice,
                    "DetailType" => "SalesItemLineDetail",
                    "SalesItemLineDetail" => [
                        "ItemRef" => [
                            "value" => $theResourceObj->Id,
                            "name" =>  $theResourceObj->Name
                        ],
                        "Qty" => 1,
                    ]
                ];
            }
        }
        array_push($OrderItems, $shippingItem);     //Now $OrderItems contains order items and shipping

        // Check Customer Existance
        $email = $order['email'];

        $CustomerSearchQuery = "SELECT * FROM Customer WHERE DisplayName='Dear " . $order['billing_first_name'] . " " . $order['billing_last_name'] . "'";
        $customer = json_encode($dataService->Query($CustomerSearchQuery));
        $customer = json_decode($customer, true);
        $customer = $customer[0];
        if(!empty($customer)) {
            $customerId = $customer['Id'];
            $customer = $dataService->FindById('customer', $customerId);
            $theResourceObj = Customer::update($customer , [
                "BillAddr" => [
                    "Line1" => $order['billing_street_line1'],
                    "Line2" => $order['billing_street_line2'],
                    "City" => $order['billing_city'],
                    "Country" => $order['billing_country'],
                    "CountrySubDivisionCode" => $order['billing_state'],
                    "PostalCode" => $order['billing_zip']
                ],
                "ShipAddr" => [
                    "Line1" => $order['shipping_street_line1'],
                    "Line2" => $order['shipping_street_line2'],
                    "City" => $order['shipping_city'],
                    "Country" => $order['shipping_country'],
                    "CountrySubDivisionCode" => $order['shipping_state'],
                    "PostalCode" => $order['shipping_zip']
                ]
            ]);

            $resultingObj = $dataService->Add($theResourceObj);
            $error = $dataService->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
            }
        } else{
            // Create Customer
            $customer_data = [
                "BillAddr" => [
                    "Line1" => $order['billing_street_line1'],
                    "Line2" => $order['billing_street_line2'],
                    "City" => $order['billing_city'],
                    "Country" => $order['billing_country'],
                    "CountrySubDivisionCode" => $order['billing_state'],
                    "PostalCode" => $order['billing_zip']
                ],
                "ShipAddr" => [
                    "Line1" => $order['shipping_street_line1'],
                    "Line2" => $order['shipping_street_line2'],
                    "City" => $order['shipping_city'],
                    "Country" => $order['shipping_country'],
                    "CountrySubDivisionCode" => $order['shipping_state'],
                    "PostalCode" => $order['shipping_zip']
                ],
                "Title" => "Dear",
                "GivenName" => $order['billing_first_name'],
                "FamilyName" => $order['billing_last_name'],
                "DisplayName" => $order['billing_first_name'] . " " . $email,
                "Mobile" => $order['billing_phone_number'],
                "PrimaryEmailAddr" => [
                    "Address" => $email
                ]
            ];
            $theResourceObj = Customer::create($customer_data);

            $resultingObj = $dataService->Add($theResourceObj);
            $error = $dataService->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
            }
            else {
                $customerId = $resultingObj->Id;
            }
        }

        /**
         * TAX Calculation
         * @return tax code id and percent
         */
        // $taxRate = $dataService->query("SELECT * FROM TaxRate where RateValue='" . );
        $OrderData = [
            "Line" => $OrderItems,
            "CustomerRef" => [
                "value" => $customerId,
            ],
            "BillEmail" => [
                "Address" => $email
            ]
        ];
        
        $theResourceObj = Invoice::create($OrderData);
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
    }
?>