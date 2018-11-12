<?php
    require_once "../../vendor/autoload.php";
    include('../../AcendaApi.php');
    
    $client_id = '291cbaae9edbd063adadb67210de4852@acenda.com';
    $client_secret = '5314959c0dc9c7718ca9e96d0a8f2745';
    $client_storename = 'brandsdistribution-store';
    
    use QuickBooksOnline\API\DataService\DataService;
    use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
    use QuickBooksOnline\API\Facades\Item;
    
    $config = include('../../config.php');
    $token_data = json_decode(file_get_contents('../../accessToken.json'), true);
    
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $config['client_id'],
        'ClientSecret' => $config['client_secret'],
        'accessTokenKey' => $token_data['access_token'],
        'refreshTokenKey' => $token_data['refresh_token'],
        'QBORealmID' => $config['realmid'],
        'baseUrl' => "Development"
    ));

    $dataService->setLogLocation(__DIR__ . 'logs/');
    $dataService->throwExceptionOnError(true);

    $acenda = new Acenda\Client($client_id,$client_secret,$client_storename);
    $i = 0;
    while(1) {
        $p_response = $acenda->get('catalog?limit=500&page=' . $i);
        if(!empty($p_response->body->result)) {
            $data = $p_response->body->result;

            foreach($data as $elem){
                $product_name = $elem->name;
                $product_desc = $elem->description;

                $date_updated = $elem->date_modified;
                $date = date_format(date_create($date_updated), 'Y-m-d');
                
                $product = $elem->product;
                foreach ($product as $array) {
                    if(!empty($array->variant)) {
                        $variants = $array->variant;
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
                                    "UnitPrice"=> $variant->price,
                                    "TrackQtyOnHand"=> true,
                                    "QtyOnHand"=> $variant->inventory_quantity,
                                    "Sku" => $variant->sku
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
                                    "UnitPrice"=> $variant->price,
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
                                    "InvStartDate"=> $date,
                                    "Sku" => $variant->sku
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
            }
        }
        $i++;
    } 
?>
