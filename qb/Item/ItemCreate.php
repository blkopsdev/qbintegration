<?php
require_once "../../vendor/autoload.php";

// session_start();

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Item;

$config = include('../../config.php');
$token_data = json_decode(file_get_contents('../../accessToken.json'), true);


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
$product_name = "te1s1";
$product_desc = "Gender: man";

$dataService->setLogLocation(__DIR__ . 'logs/');
$dataService->throwExceptionOnError(true);
$theResourceObj = Item::create([
    "Name"=> $product_name,
    "Description"=> $product_desc,
    "Active"=> true,
    "FullyQualifiedName"=> $product_name,
    "Taxable" => "true",
    "Type"=> "Inventory",
    "IncomeAccountRef"=> [
        "value"=> "79",
        "name"=> "Sales of Product Income"
    ],
    "PurchaseDesc"=> $product_desc,
    "ExpenseAccountRef"=> [
        "value"=> "80",
        "name"=> "Cost of Goods Sold"
    ],
    "AssetAccountRef"=> [
        "value"=> "81",
        "name"=> "Inventory Asset"
    ],
    "TrackQtyOnHand"=> true,
    "QtyOnHand" => 1,
    "InvStartDate"=> "2018-06-26",
]);

$resultingObj = $dataService->Add($theResourceObj);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
  $id = $resultingObj->Id;
  $name = $resultingObj->Name;

    echo "Created Id={$id}. Reconstructed response body:\n\n";
    echo "Created Id={$name}. Reconstructed response body:\n\n";
    /*$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
    echo $xmlBody . "\n";*/
}
