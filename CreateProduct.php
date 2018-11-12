<?php
    require_once "./vendor/autoload.php";
    include('./AcendaApi.php');
    
    $client_id = '291cbaae9edbd063adadb67210de4852@acenda.com';
    $client_secret = '5314959c0dc9c7718ca9e96d0a8f2745';
    $client_storename = 'brandsdistribution-store';
    
    use QuickBooksOnline\API\DataService\DataService;
    use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
    use QuickBooksOnline\API\Facades\Item;
    
    $config = include('./config.php');
    $token_data = json_decode(file_get_contents('./accessToken.json'), true);
    
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $config['client_id'],
        'ClientSecret' => $config['client_secret'],
        'accessTokenKey' => 'eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..FNGnn8mJ6ULyQh0QutWeGg.bk-qi-1FIFZLPFp7tFHFIM_MGV_GSdU6p1QkjpmJdABNZP8LmLI2GVmbLskieaybwpfZCX2xKviVr-eNJuE0TW9bzQj4469DNyHOFtebWKPdcykt2NfAw7Oyluh3_gyfsnEBTcZRqoRSc_2FiTYBXbFcx0SaDWlwFG1R4rKkYV180PouJDKFe8abPvsbUztFIIermbyOmfBjVR4s3YB_qnouQQYcR4kVilA-bi2iC6IFd7f8FS3Syv3rH795qa-9QESJWdsxArwrZrspwHL4FqNxWFzZIJ3wuyZqF0WK-HGUAmwPOwyfipPqymcki-PiwZ0Ei2_lplTQWbF_Aw70ep9toQq6bhw1b9t6iYftjDzmmN1rSpu4ncBV4dAbANKvBNa8yGcehF7SINf16QrDLVPVsVrOWlTvjfTJqD9Ec2_WW1vlHmKI4r5bqi9cX5vK-ZqDuG2xd2C5pVpfpXbABh_5nPLFqdAFepK62T1MkHrwkVQo-Pdkj-lklQXixQwnTIUSEcDORPOYFniCW0GaetpJeXB4UhdK8lgBEpQ_wL-qy-A3h6D63jUqgX33trZyDOZ-7FfpYecz3x_wTUU5ROf35w-hII8OTMUkr1B_mARcM14BhB8mfeTmJcEKrGrOzzDsvgE9HoL3oDSbCxRQsHQ5YlSdS2zMGae8Sq8dqeBFedWTN1UFtgIGGmpw1jmm.lqen5eZ9F4cI_taEYeXZ0g',
        'refreshTokenKey' => 'Q011549786285vISjI1UcKT9VPUNKKb3Ffn7IlqeSl22I4rH1I',
        'QBORealmID' => '193514832571624',
        'baseUrl' => "Development"
    ));

    $dataService->setLogLocation(__DIR__ . 'logs/');
    $dataService->throwExceptionOnError(true);

    $acenda = new Acenda\Client($client_id,$client_secret,$client_storename);
    $json = file_get_contents('php://input');
    // $json = '{"acenda":{"store":{"id":4617,"name":"brandsdistribution-store"},"subscription":{"id":1513,"credentials":{"email":"johna@torreycommerce.com","password":"test","api_key":"test","web_host":"test"}},"event_payload":[{"Product":{"id":1018,"group":"product","status":"active","slug":"vrbs61-70129-409-lightpink","name":"VRBS61 70129 409 LIGHTPINK","category_id":[526,687],"popularity":0,"brand":"Versace Jeans","type":"","tags":[],"description":"Collection: Spring\/Summer   Gender: Woman   Type: Flip flops   Upper: fabric   Insole: fabric   Internal lining: synthetic material   Sole: rubber   Heel height cm: 2.5   Details: studsankle strapround toe   ","cross_sellers":[],"options":["color","size"],"images":[{"id":"65b1f188bd8991","date_created":"2018-08-14 14:11:21","date_modified":"2018-08-14 14:11:21","name":"","type":"default","url":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/thumbnail\/250x250\/6\/65b1f188bd8991.jpg","mini":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/mini\/30x30\/6\/65b1f188bd8991.jpg","thumbnail":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/thumbnail\/250x250\/6\/65b1f188bd8991.jpg","standard":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/standard\/450x450\/6\/65b1f188bd8991.jpg","large":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/large\/900x900\/6\/65b1f188bd8991.jpg","retina":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/retina\/1500x1500\/6\/65b1f188bd8991.jpg","original":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/original\/6\/65b1f188bd8991.jpg"},{"id":"35b1f18916ebb8","date_created":"2018-08-14 14:11:21","date_modified":"2018-08-14 14:11:21","name":"","type":"alternate","url":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/thumbnail\/250x250\/3\/35b1f18916ebb8.jpg","mini":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/mini\/30x30\/3\/35b1f18916ebb8.jpg","thumbnail":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/thumbnail\/250x250\/3\/35b1f18916ebb8.jpg","standard":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/standard\/450x450\/3\/35b1f18916ebb8.jpg","large":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/large\/900x900\/3\/35b1f18916ebb8.jpg","retina":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/retina\/1500x1500\/3\/35b1f18916ebb8.jpg","original":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/original\/3\/35b1f18916ebb8.jpg"},{"id":"15b1f18979085a","date_created":"2018-08-14 14:11:21","date_modified":"2018-08-14 14:11:21","name":"","type":"alternate","url":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/thumbnail\/250x250\/1\/15b1f18979085a.jpg","mini":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/mini\/30x30\/1\/15b1f18979085a.jpg","thumbnail":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/thumbnail\/250x250\/1\/15b1f18979085a.jpg","standard":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/standard\/450x450\/1\/15b1f18979085a.jpg","large":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/large\/900x900\/1\/15b1f18979085a.jpg","retina":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/retina\/1500x1500\/1\/15b1f18979085a.jpg","original":"https:\/\/images.acenda.com\/brandsdistribution-store\/product\/original\/1\/15b1f18979085a.jpg"}],"dynamic_attributes":[],"date_modified":"2018-08-19 21:27:14","date_created":"2018-06-07 19:44:22","bd_id":"91526","ebay_enabled":null,"ebay_posting_template":null,"bullet1":null,"bullet2":null,"bullet3":null,"bullet4":null,"bullet5":null,"amazon_enabled":null,"amazon_posting_template":null}}]}}';
    file_put_contents('./CreatedProduct.json', $json);

    $obj = json_decode($json, true);

    // Get Product ID
    $data = $obj['acenda']['event_payload'];
    foreach ($data as $product_data) {
        $product = $product_data['Product'];
        $id = $product['id'];
    }

    // Get Variant Data using Product ID
    $p_response = $acenda->get('product/' . $id);
    
    if(!empty($p_response->body->result)) {
        $data = $p_response->body->result;

        $product_desc = $data->description;

        $date_updated = $data->date_created;
        $date = date_format(date_create($date_updated), 'Y-m-d');
        
        if(!empty($data->variants)) {
            $variants = $data->variants;
            foreach ($variants as $variant) {

                // Run a query
                $entities = $dataService->Query("SELECT * FROM Item where Name='" . $variant->name . "'");
                $error = $dataService->getLastError();
                if ($error) {
                    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                    echo "The Response message is: " . $error->getResponseBody() . "\n";
                    exit();
                }
                
                if ($entities) {
                    // Item Update
                    $entities = json_decode(json_encode($entities), true);
                    $entities = $entities[0];
                    $itemID = $entities['Id'];

                    $item = $dataService->FindbyId('item', $itemID);
                    $theResourceObj = Item::update($item , [
                        "Description"=> $product_desc,
                        "UnitPrice"=> 1000,
                        "TrackQtyOnHand"=> true,
                        "QtyOnHand"=> $variant->inventory_quantity,
                        "Sku" => 1000
                    ]);

                    $resultingObj = $dataService->Add($theResourceObj);
                    $error = $dataService->getLastError();
                    if ($error) {
                        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                        echo "The Response message is: " . $error->getResponseBody() . "\n";
                    }
                    else {
                        echo "Updated Id={$resultingObj->Id}. Reconstructed response body:\n\n";
                    }
                } else {
                    // Item Create
                    $theResourceObj = Item::create([
                        "Name"=> $variant->name,
                        "Description"=> $product_desc,
                        "Active"=> true,
                        "Taxable"=> true,
                        "UnitPrice"=> 1000,
                        "Type"=> "Inventory",
                        "IncomeAccountRef"=> [
                            "value"=> "79",
                            "name"=> "Sales of Product Income"
                        ],
                        "ExpenseAccountRef"=> [
                            "value"=> "80",
                            "name"=> "Cost of Goods Sold"
                        ],
                        "AssetAccountRef"=> [
                            "value"=> "81",
                            "name"=> "Inventory Asset"
                        ],
                        "TrackQtyOnHand"=> true,
                        "QtyOnHand"=> $variant->inventory_quantity,
                        "InvStartDate"=> date('Y-m-d'),
                        "Sku" => 1000
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
                }
            }
        }
    }
?>
