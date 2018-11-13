<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

$config = include('config.php');

session_start();

$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => $config['client_id'],
    'ClientSecret' =>  $config['client_secret'],
    'RedirectURI' => $config['oauth_redirect_uri'],
    'scope' => $config['oauth_scope'],
    'baseUrl' => "development"
));

$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();


// Store the url in PHP Session Object;
$_SESSION['authUrl'] = $authUrl;

//set the access token using the auth object
if (isset($_SESSION['sessionAccessToken'])) {

    $accessToken = $_SESSION['sessionAccessToken'];
    $accessTokenJson = array('token_type' => 'bearer',
        'access_token' => $accessToken->getAccessToken(),
        'refresh_token' => $accessToken->getRefreshToken(),
        'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
        'expires_in' => $accessToken->getAccessTokenExpiresAt()
    );
    $dataService->updateOAuth2Token($accessToken);
    $oauthLoginHelper = $dataService -> getOAuth2LoginHelper();
    $CompanyInfo = $dataService->getCompanyInfo();
}

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="apple-touch-icon icon shortcut" type="image/png" href="https://plugin.intuitcdn.net/sbg-web-shell-ui/6.3.0/shell/harmony/images/QBOlogo.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="views/common.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script>

        var url = '<?php echo $authUrl; ?>';

        var OAuthCode = function(url) {

            this.loginPopup = function () {
                // this.loginPopupUri(parameter);
                var win = window.open(url, 'connectPopup', parameters);
                var pollOAuth = window.setInterval(function () {
                    try {

                        if (win.document.URL.indexOf("code") != -1) {
                            window.clearInterval(pollOAuth);
                            // win.close();
                            location.reload();
                        }
                    } catch (e) {
                        console.log(e)
                    }
                }, 100);
            }

            this.loginPopupUri = function (parameter) {

                // Launch Popup
                var parameters = "location=1,width=800,height=650";
                parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

                var win = window.open(url, 'connectPopup', parameters);
                var pollOAuth = window.setInterval(function () {
                    try {

                        if (win.document.URL.indexOf("code") != -1) {
                            window.clearInterval(pollOAuth);
                            // win.close();
                            location.reload();
                        }
                    } catch (e) {
                        console.log(e)
                    }
                }, 100);
            }
        }


        var apiCall = function() {
            this.getCompanyInfo = function() {
                /*
                AJAX Request to retrieve getCompanyInfo
                 */
                $.ajax({
                    type: "GET",
                    url: "apiCall.php",
                }).done(function( msg ) {
                    $( '#apiCall' ).html( msg );
                });
            }

            this.refreshToken = function() {

                $.ajax({
                    type: "POST",
                    url: "refreshToken.php",
                }).done(function( msg ) {
                    console.log('success');
                });
            }
        }

        var oauth = new OAuthCode(url);
        var apiCall = new apiCall();
    </script>
    <style type="text/css">
        body{
            height: 100%;
        }
        .footer {
            width: 100%;
            position: absolute;
            bottom: 0;
            border-top: #eeeeee 1px solid;
            padding-top: 20px;
            padding-bottom: 10px;
        }
        .connect-image {
            width:80%;
            max-width: 250px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="header container">
        <h1>
            <a href="http://developer.intuit.com">
                <img src="views/quickbooks_logo_horz.png" id="headerLogo">
            </a>
        </h1>
        <hr>
    </div>

    <div class="main container">
        <div class="well text-center">
            <h1>Connect To QuickBooks Online</h1>
            <br>
        </div>
        <div style="text-align: center; padding: 50px 0;">
            <a class="imgLink" href="https://adneca.com/quickbooks/get_token.php" style="text-align: center;">
                <img class="connect-image img-responsive" src="views/C2QB_green_btn_lg_default.png" />
            </a>
        </div>
        <!-- <button  type="button" class="btn btn-success" onclick="apiCall.refreshToken()">Refresh Token</button> -->
        
        <!-- <button  type="button" class="btn btn-success" onclick="apiCall.getCompanyInfo()">Get Company Info</button> -->
    </div>
    <div class="footer ">
        <div class="container">
            <p class="text-left text-muted">
                &copy; 2018 Intuit&trade;, Inc. All rights reserved. Intuit and QuickBooks are registered trademarks of Intuit Inc.
            </p>
        </div>
    </div>
</body>
</html>
