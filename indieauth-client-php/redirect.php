<?php

require '/usr/local/lib/indieauth-client-php/vendor/autoload.php';
$issuer = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];

session_start();
IndieAuth\Client::$clientID = $issuer.'/'.getenv('CLIENT_PATH').'/';
IndieAuth\Client::$redirectURL = $issuer.'/'.getenv('CLIENT_PATH').'/redirect.php';

$stderr = fopen( 'php://stderr', 'w' );
list($response, $error) = IndieAuth\Client::complete($_GET);

if($error) {
  echo "<p>Error: ".$error['error']."</p>";
  echo "<p>".$error['error_description']."</p>";
  trigger_error($error['error'], E_USER_WARNING);
  fwrite($stderr, $error['error_description']."\n");
  fwrite($stderr, $error['debug']['response_details']['url']."\n");
  fwrite($stderr, $error['debug']['response_details']['header']."\n");
} else {
  // Login succeeded!
  // The library will return the user's profile URL in the property "me"
  $_SESSION['user'] = $response['me'];
  setcookie('me', $response['me'], 0, '/'.getenv('CLIENT_PATH').'/', $_SERVER['HTTP_HOST'], false, false);
  if(isset($response['response']['access_token'])) {
    // It will also return the full response from the authorization or token endpoint, as well as debug info
    $_SESSION['token'] = $response['response']['access_token'];
	setcookie('oauth_token', $response['response']['access_token'], 0, '/'.getenv('CLIENT_PATH').'/', $_SERVER['HTTP_HOST'], false, false);
    $_SESSION['scope'] = $response['response']['scope'];
	setcookie('oauth_scope', $response['response']['scope'], 0, '/'.getenv('CLIENT_PATH').'/', $_SERVER['HTTP_HOST'], false, false);
  }

  // The full parsed response from the endpoint will be available as:
  // $response['response']

  // The raw response:
  // $response['raw_response']

  // The HTTP response code:
  // $response['response_code']

  $metadataendpoint = IndieAuth\Client::discoverMetadataEndpoint($response['me']);
  if (strpos(file_get_contents("./.htaccess"), $metadataendpoint) === false) {
    $oauth_token_verify = 'OAuth2TokenVerify metadata '.$metadataendpoint.' introspect.auth=client_secret_basic&client_id='.IndieAuth\Client::$clientID.'&client_secret=_'."\n";
    file_put_contents("./.htaccess", $oauth_token_verify, FILE_APPEND);
  }

  $auth_redirect = $_COOKIE['auth_redirect'];
  setcookie('auth_redirect', '', -1, '/'.getenv('CLIENT_PATH').'/', $_SERVER['HTTP_HOST'], false, false);
  header('Location: '.$auth_redirect);
}
?>
