<?php

//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
function refreshToken($realmId) {

	require(__DIR__ . "/vendor/autoload.php");

	use QuickBooksOnline\API\Core\ServiceContext;
	use QuickBooksOnline\API\DataService\DataService;
	use QuickBooksOnline\API\PlatformService\PlatformService;
	use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;

	$con = mysqli_connect('localhost', 'root', 'root', 'acenda_qb');

	if (mysqli_connect_errno()) 
    { 
        die("Connect failed: ".mysqli_connect_errno()." : ". mysqli_connect_error());
    }

    $sql = "SELECT * from users where realmid='" . $realmId . "'";
    if($result=mysqli_query($con, $sql)) {
    	while ($row=mysqli_fetch_row($result)){
            $cid = $row[0];
            $refresh_token = $row[3];
        }
    }

	$config = include('config.php');
	$dataService = DataService::Configure(array(
	  'auth_mode' => 'oauth2',
	  'ClientID' => $config['client_id'],
	  'ClientSecret' =>  $config['client_secret'],
	  'accessTokenKey' =>  '',
	  'refreshTokenKey' => $refresh_token,
	  'QBORealmID' => $realmId,
	  'baseUrl' => $config['baseUrl']
	));

	$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
	$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

	$accessToken = $OAuth2LoginHelper->refreshToken();
	$access_token  = $accessToken->getAccessToken();
	$refresh_token = $accessToken->getRefreshToken();

	print_r($accessToken);
	
	$error = $OAuth2LoginHelper->getLastError();
	if ($error) {
	    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
	    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
	    echo "The Response message is: " . $error->getResponseBody() . "\n";
	    return;
	}
	$dataService->updateOAuth2Token($accessToken);

}

?>
