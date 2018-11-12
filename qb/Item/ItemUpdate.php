<?php
require "../../vendor/autoload.php";
include('../../AcendaApi.php');

$acenda = new Acenda\Client($client_id,$client_secret,$client_storename);

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Item;

// Prep Data Services
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

$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
$dataService->throwExceptionOnError(true);

$item = $dataService->FindbyId('item', 19);
$theResourceObj = Item::update($item , [
    "QtyOnHand" => 100
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
