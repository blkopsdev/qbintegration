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
    // $json = '{"acenda":{"store":{"id":2764,"name":"demostore"},"subscription":{"id":1965,"credentials":{"Email":"dev.royali1029@gmail.com","company_id":"193514789400954"}},"event_payload":[{"Order":{"id":2877316,"date_modified":"2018-11-05 09:04:24","order_number":"2850346","status":"open","email":"gavinm@torreycommerce.com","ip":"50.97.232.186, 50.97.232.186","shipping_first_name":"gavin","shipping_last_name":"mann","shipping_phone_number":"+18583373775","shipping_street_line1":"2385 calle del oro","shipping_street_line2":"","shipping_city":"la jolla","shipping_state":"CA","shipping_zip":"92037","shipping_country":"US","shipping_method":2830234,"shipping_rate":"5.00","shipping_rate_original":"5.00","tax_percent":7.75,"tax_shipping":false,"tax_included":false,"returns_pending":0,"returns_rma_numbers":"","returnable_items":0,"giftlist_present":false,"subtotal":"11.00","tax":"0.00","tax_original":"0.00","total":"16.00","charge_amount":"0.00","unsettled":"-16.00","transaction_status":"authorize","fulfillment_status":"pending","fraud_check":false,"fraud_results":{"maxmind":"19.01"},"marketplace_name":"webstore","marketplace_id":2877316,"review_request_sent":false,"calculate_tax":false,"card_type":"visa","card_last4":"1111","payment_method_nonce":null,"discount_collection":null,"exported":"","items":[{"id":568,"date_created":"2018-11-05 09:04:20","date_modified":"2018-11-05 09:04:20","product_id":975,"variant_id":1014,"wishlist_id":null,"registry_id":null,"status":"open","vendor":"acenda","tracking":null,"name":"Testing Product","sku":"56789","barcode":null,"quantity":1,"price":11,"ordered_quantity":1,"ordered_price":11,"backorder":"0","fulfilled_quantity":0,"fulfillment_status":"pending","marketplace_name":"","marketplace_item_id":null,"marketplace_tax_allocation":null,"marketplace_ship_allocation":null,"warnings":[]}],"billing_first_name":"gavin","billing_last_name":"mann","billing_phone_number":"+18583373775","billing_street_line1":"2385 calle del oro","billing_street_line2":null,"billing_city":"la jolla","billing_state":"CA","billing_country":"US","billing_zip":"92037","cancellation_window":"30","fraud_score":100,"payments":[{"id":3115936,"order_id":"2877316","date_created":"2018-11-05 09:04:23","date_modified":"2018-11-05 09:04:23","action":null,"platform":"PayPal","status":"authorize","amount":"16.00","charged":0,"refund_reason":""}],"coupons":[]}}]}}';
    file_put_contents('./CreatedOrder.json', $json);

    $req = json_decode($json, true);
    $realmId = $req['acenda']['subscription']['credentials']['company_id'];
    
    $tokenData = refreshToken($realmId);

    if ($tokenData) {
        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
            'accessTokenKey' => $tokenData['access_token'],
            'refreshTokenKey' => $tokenData['refresh_token'],
            'QBORealmID' => $realmId,
            'baseUrl' => "development"
        ));
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);

        $order = $req['acenda']['event_payload'][0]['Order'];
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
                    $TaxCodeRef = "TAX";
                }

            }
        } else {
            $TaxCodeRef = "NON";
        }
        
        $OrderItems = [];
        //Add Order items to Line Array
        if(is_array($order['items'])) {
            foreach($order['items'] as $item) {
                $sku = $item['sku'];
                
                $ItemSearchQuery = "SELECT * FROM Item Where Sku='" . $sku . "'";
                $entities = json_encode($dataService->Query($ItemSearchQuery));
                $entities = json_decode($entities, true);

                $Item = $entities[0];
                if (!empty($Item)) {
                    $ItemID = $Item['Id'];
                    $ItemName = $Item['Name'];
                } else {
                    $Item = Item::create([
                        "Name" => $item['name'],
                        // "Descriptoin" => "Barcode:" . $item['barcode'],
                        "Sku" => $sku,
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
                        echo $ItemID;
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
                            "value" => $TaxCodeRef
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
        array_push($OrderItems, $shippingItem); 

        // Check Customer Existance
        $email = $order['email'];

        $CustomerSearchQuery = "SELECT * FROM Customer WHERE DisplayName='" . $order['billing_first_name'] . " " . $email . "'";
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

        $OrderData = [
            "Line" => $OrderItems,
            "CustomerRef" => [
                "value" => $customerId,
            ],
            "BillEmail" => [
                "Address" => $email
            ]
        ];
        // print_r($OrderData) ;
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