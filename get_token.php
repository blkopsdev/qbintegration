<?php

    require ('vendor/autoload.php');
    // require(__DIR__ . '/Error.php');
    use QuickBooksOnline\API\DataService\DataService;

    $con=mysqli_connect('localhost','root','root','acenda_qb');

    if (mysqli_connect_errno()) 
    { 
        die("Connect failed: ".mysqli_connect_errno()." : ". mysqli_connect_error());
    }

    function processCode() 
    {
        // session_start();
        $config = include('config.php');
        if(isset($_GET['code'])) {

            $dataService = DataService::Configure(array(
                'auth_mode' => 'oauth2',
                'ClientID' => $config['client_id'],
                'ClientSecret' =>  $config['client_secret'],
                'RedirectURI' => $config['oauth_redirect_uri'],
                'scope' => $config['oauth_scope'],
                'baseUrl' => "development"
            ));

            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $parseUrl = parseAuthRedirectUrl($_SERVER['QUERY_STRING']);

            $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'], $parseUrl['realmId']);
            $dataService->updateOAuth2Token($accessToken);

            $accessTokenJson = array('token_type' => 'bearer',
                'access_token' => $accessToken->getAccessToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
                'expires_in' => $accessToken->getAccessTokenExpiresAt(),
                'realmId' => $parseUrl['realmId']
            );
            $data = json_encode($accessTokenJson);
            file_put_contents('./accessToken.json', $data);
            
            return $accessTokenJson;
            
        } else {
            $url = "https://appcenter.intuit.com/connect/oauth2";
            $params = array(
                "response_type" => "code",
                "client_id" => $config['client_id'],
                "redirect_uri" => $config['oauth_redirect_uri'],
                'scope' => $config['oauth_scope'],
                "state" => $config['state']
            );
            
            $request_to = $url . '?' . http_build_query($params);
            header("Location: " . $request_to);
        }
    }

    function parseAuthRedirectUrl($url)
    {
        parse_str($url,$qsArray);
        return array(
            'code' => $qsArray['code'],
            'realmId' => $qsArray['realmId']
        );
    }

    $access_data = processCode();
    if ($access_data) {
        $access_token = $access_data['access_token'];
        $refresh_token = $access_data['refresh_token'];
        $realmId = $access_data['realmId'];

        $sql="SELECT * From users Where realmid='" . $realmId . "'";
        
        if ($result=mysqli_query($con,$sql)){
            // Fetch one and one row
            while ($row=mysqli_fetch_row($result)){
                $cid = $row[0];
                $refresh_token = $row[3];
            }
        }
        
        if($cid) {
            $query = "UPDATE users SET access_token='" . $access_token . "', refresh_token='" . $refresh_token . "' WHERE id=" . $cid;
        } else {
            $query = "INSERT into users (realmid, access_token, refresh_token) values ('$realmId','$access_token','$refresh_token')";
        }

        $result=mysqli_query($con,$query);

        mysqli_close($con);
    }

?>