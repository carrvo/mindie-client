<?php

require __DIR__ . '/../vendor/autoload.php';
$issuer = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];

if(!isset($_POST['url'])) {
  die('Missing URL');
}

// Start a session for the library to be able to save state between requests.
session_start();

// You'll need to set up two pieces of information before you can use the client,
// the client ID and and the redirect URL.

// The client ID should be the home page of your app.
IndieAuth\Client::$clientID = $issuer.getenv('CLIENT_PATH').'/oauth-client-server';

// The redirect URL is where the user will be returned to after they approve the request.
IndieAuth\Client::$redirectURL = $issuer.getenv('CLIENT_PATH').'/redirect';

// Ensure the URL includes the scheme.
// Assume HTTPS if missing.
$user_url = $_POST['url'];
if (parse_url($user_url, PHP_URL_SCHEME) === null) {
  $user_url = 'https://' . $user_url;
}

// Pass the user's URL and your requested scope to the client.
// If you are writing a Micropub client, you should include at least the "create" scope.
// If you are just trying to log the user in, you can omit the second parameter.

try {
  $scope = getenv('CLIENT_SCOPE');
  if (empty($scope)) {
    list($authorizationURL, $error) = IndieAuth\Client::begin($user_url);
  }
  else {
    list($authorizationURL, $error) = IndieAuth\Client::begin($user_url, $scope);
  }
} catch (Exception $e) {
	//echo "<p>".$e->getMessage()."</p>";
	//echo "<p>".$e->getTraceAsString()."</p>";
    header('HTTP/1.1 500 Internal Server Error', true, 500);
    exit();
}

// Check whether the library was able to discover the necessary endpoints
if($error) {
  echo "<p>Error: ".$error['error']."</p>";
  echo "<p>".$error['error_description']."</p>";
} else {
  // Redirect the user to their authorization endpoint
  header('Location: '.$authorizationURL);
}
?>
