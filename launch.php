<?php
  $headers = apache_request_headers();

  if (strpos($headers['referer'], 'qbo.intuit.com/app/')) {
?>

  <html class=" js flexbox flexboxlegacy canvas canvastext webgl no-touch geolocation postmessage websqldatabase indexeddb hashchange history draganddrop websockets rgba hsla multiplebgs backgroundsize borderimage borderradius boxshadow textshadow opacity cssanimations csscolumns cssgradients cssreflections csstransforms csstransforms3d csstransitions fontface generatedcontent video audio localstorage sessionstorage webworkers applicationcache svg inlinesvg smil svgclippaths" lang="en" style="">
  <head>
    <meta class="foundation-mq-small">
    <meta class="foundation-mq-small-only">
    <meta class="foundation-mq-medium">
    <meta class="foundation-mq-medium-only">
    <meta class="foundation-mq-large">
    <meta class="foundation-mq-large-only">
    <meta class="foundation-mq-xlarge">
    <meta class="foundation-mq-xlarge-only">
    <meta class="foundation-mq-xxlarge">
    <meta class="foundation-data-attribute-namespace">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Log In - Acenda</title>

    <link rel="stylesheet" media="screen" href="./views/style.css">
    <!-- <script src="https://cdn.bigcommerce.com/login/assets/vendor/modernizr-4acdf69dfb61855f7bf52c0e8df714b19a8d07185726649e16d0669659008db5.js"></script> -->

    <link rel="shortcut icon" href="https://www.acenda.com/favicon.ico">
    <style></style><meta class="foundation-mq-topbar">
  </head>

  <body class="rebrand" style="">
    <div class="app-access-login">
      <div class="login-form-logo"></div>
      <h3 class="app-access-heading app-name">QuickBooks Online</h3>
      <h3 class="app-access-heading">To install please select one of the options below</h3>
      <div class="row bui-module">
        <div class="small-8 columns">
          <h4>Existing Acenda store</h4>
          <a href="https://admin.acenda.com" class="button">Log in</a>
        </div>
        <div class="small-8 columns">
          <h4>New to Acenda?</h4>
          <a href="https://www.acenda.com" class="button">Sign up</a>
        </div>
      </div>
    </div>
  </body>
  </html>

<?php
  } else {
    echo "404 Error";
  }
?>